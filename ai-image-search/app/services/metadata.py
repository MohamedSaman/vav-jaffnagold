"""SQLite-backed metadata for indexed products.

FAISS stores vectors and ids only. This store holds everything else the ranking
and the API response need: the MySQL product id (used directly as the FAISS id),
category, detected jewelry type and an image fingerprint for incremental syncs.

SQLite is deliberate — it keeps the service to a single process with no extra
infrastructure, and the access pattern (a few thousand point lookups per second
at most) is far below what it handles comfortably.
"""

from __future__ import annotations

import sqlite3
import threading
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Iterable, Sequence

from ..logging_config import get_logger

logger = get_logger(__name__)

SCHEMA_VERSION = 1

_SCHEMA = """
CREATE TABLE IF NOT EXISTS products (
    product_id        INTEGER PRIMARY KEY,
    code              TEXT,
    name              TEXT,
    category_id       INTEGER,
    category_name     TEXT,
    jewelry_type      TEXT,
    image_source      TEXT,
    image_fingerprint TEXT,
    indexed_at        TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_products_type ON products (jewelry_type);
CREATE INDEX IF NOT EXISTS idx_products_category ON products (category_id);

CREATE TABLE IF NOT EXISTS service_meta (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
"""


@dataclass(slots=True)
class ProductRecord:
    product_id: int
    code: str | None = None
    name: str | None = None
    category_id: int | None = None
    category_name: str | None = None
    jewelry_type: str | None = None
    image_source: str | None = None
    image_fingerprint: str | None = None
    indexed_at: str | None = None


class MetadataStore:
    def __init__(self, path: Path) -> None:
        self._path = path
        self._lock = threading.RLock()
        path.parent.mkdir(parents=True, exist_ok=True)

        # One long-lived connection shared across the thread pool, serialised by
        # our own lock. WAL keeps readers from blocking the indexing writer.
        self._conn = sqlite3.connect(str(path), check_same_thread=False)
        self._conn.row_factory = sqlite3.Row
        self._conn.execute("PRAGMA journal_mode=WAL")
        self._conn.execute("PRAGMA synchronous=NORMAL")
        self._conn.executescript(_SCHEMA)
        self._conn.commit()
        self.set_meta("schema_version", str(SCHEMA_VERSION))

    # ------------------------------------------------------------------- writes
    def upsert(self, record: ProductRecord) -> None:
        self.upsert_many([record])

    def upsert_many(self, records: Sequence[ProductRecord]) -> None:
        if not records:
            return

        now = datetime.now(timezone.utc).isoformat(timespec="seconds")
        rows = [
            (
                r.product_id,
                r.code,
                r.name,
                r.category_id,
                r.category_name,
                r.jewelry_type,
                r.image_source,
                r.image_fingerprint,
                r.indexed_at or now,
            )
            for r in records
        ]

        with self._lock:
            self._conn.executemany(
                """
                INSERT INTO products (
                    product_id, code, name, category_id, category_name,
                    jewelry_type, image_source, image_fingerprint, indexed_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(product_id) DO UPDATE SET
                    code              = excluded.code,
                    name              = excluded.name,
                    category_id       = excluded.category_id,
                    category_name     = excluded.category_name,
                    jewelry_type      = excluded.jewelry_type,
                    image_source      = excluded.image_source,
                    image_fingerprint = excluded.image_fingerprint,
                    indexed_at        = excluded.indexed_at
                """,
                rows,
            )
            self._conn.commit()

    def delete(self, product_id: int) -> bool:
        with self._lock:
            cursor = self._conn.execute(
                "DELETE FROM products WHERE product_id = ?", (product_id,)
            )
            self._conn.commit()
            return cursor.rowcount > 0

    def delete_many(self, product_ids: Iterable[int]) -> int:
        ids = [int(pid) for pid in product_ids]
        if not ids:
            return 0

        with self._lock:
            placeholders = ",".join("?" * len(ids))
            cursor = self._conn.execute(
                f"DELETE FROM products WHERE product_id IN ({placeholders})", ids
            )
            self._conn.commit()
            return cursor.rowcount

    def clear(self) -> None:
        with self._lock:
            self._conn.execute("DELETE FROM products")
            self._conn.commit()

    # -------------------------------------------------------------------- reads
    def get(self, product_id: int) -> ProductRecord | None:
        with self._lock:
            row = self._conn.execute(
                "SELECT * FROM products WHERE product_id = ?", (product_id,)
            ).fetchone()
        return self._to_record(row) if row else None

    def get_many(self, product_ids: Sequence[int]) -> dict[int, ProductRecord]:
        ids = [int(pid) for pid in product_ids]
        if not ids:
            return {}

        with self._lock:
            placeholders = ",".join("?" * len(ids))
            rows = self._conn.execute(
                f"SELECT * FROM products WHERE product_id IN ({placeholders})", ids
            ).fetchall()

        return {int(row["product_id"]): self._to_record(row) for row in rows}

    def all_ids(self) -> set[int]:
        with self._lock:
            rows = self._conn.execute("SELECT product_id FROM products").fetchall()
        return {int(row["product_id"]) for row in rows}

    def fingerprints(self) -> dict[int, str | None]:
        """Product id -> image fingerprint, for skip-if-unchanged logic."""
        with self._lock:
            rows = self._conn.execute(
                "SELECT product_id, image_fingerprint FROM products"
            ).fetchall()
        return {int(row["product_id"]): row["image_fingerprint"] for row in rows}

    def count(self) -> int:
        with self._lock:
            row = self._conn.execute("SELECT COUNT(*) AS total FROM products").fetchone()
        return int(row["total"])

    def type_breakdown(self) -> dict[str, int]:
        with self._lock:
            rows = self._conn.execute(
                """
                SELECT COALESCE(jewelry_type, 'unknown') AS jewelry_type, COUNT(*) AS total
                FROM products GROUP BY jewelry_type ORDER BY total DESC
                """
            ).fetchall()
        return {row["jewelry_type"]: int(row["total"]) for row in rows}

    def last_indexed_at(self) -> str | None:
        with self._lock:
            row = self._conn.execute("SELECT MAX(indexed_at) AS latest FROM products").fetchone()
        return row["latest"] if row and row["latest"] else None

    def ids_by_category(self, category_id: int) -> set[int]:
        with self._lock:
            rows = self._conn.execute(
                "SELECT product_id FROM products WHERE category_id = ?", (category_id,)
            ).fetchall()
        return {int(row["product_id"]) for row in rows}

    # --------------------------------------------------------------------- meta
    def set_meta(self, key: str, value: str) -> None:
        with self._lock:
            self._conn.execute(
                """
                INSERT INTO service_meta (key, value) VALUES (?, ?)
                ON CONFLICT(key) DO UPDATE SET value = excluded.value
                """,
                (key, value),
            )
            self._conn.commit()

    def get_meta(self, key: str) -> str | None:
        with self._lock:
            row = self._conn.execute(
                "SELECT value FROM service_meta WHERE key = ?", (key,)
            ).fetchone()
        return row["value"] if row else None

    def close(self) -> None:
        with self._lock:
            self._conn.close()

    @staticmethod
    def _to_record(row: sqlite3.Row) -> ProductRecord:
        return ProductRecord(
            product_id=int(row["product_id"]),
            code=row["code"],
            name=row["name"],
            category_id=row["category_id"],
            category_name=row["category_name"],
            jewelry_type=row["jewelry_type"],
            image_source=row["image_source"],
            image_fingerprint=row["image_fingerprint"],
            indexed_at=row["indexed_at"],
        )
