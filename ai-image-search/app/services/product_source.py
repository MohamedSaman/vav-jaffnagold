"""Read products straight from the Laravel MySQL database.

The rebuild path talks to MySQL directly rather than through the Laravel API:
it avoids paginating 100k rows over HTTP, and a rebuild can then run even when
the web app is down.
"""

from __future__ import annotations

from typing import Iterator

import pymysql
from pymysql.cursors import SSDictCursor

from ..config import Settings
from ..logging_config import get_logger
from ..schemas import ProductPayload

logger = get_logger(__name__)

# Only products with a real image are worth embedding. The placeholder is
# filtered here so it never occupies a slot in the index.
_BASE_SELECT = """
    SELECT
        pd.id            AS product_id,
        pd.code          AS code,
        pd.name          AS name,
        pd.image         AS image,
        pd.category_id   AS category_id,
        cl.category_name AS category_name
    FROM product_details AS pd
    LEFT JOIN category_lists AS cl ON cl.id = pd.category_id
    WHERE pd.status = 'active'
      AND pd.image IS NOT NULL
      AND pd.image <> ''
      AND pd.image NOT IN ('images/product.jpg', 'images/products/product.jpg')
"""

_WEBSITE_LIVE_CLAUSE = " AND pd.website_live = 1"


class ProductSource:
    def __init__(self, settings: Settings) -> None:
        self._settings = settings

    def _connect(self, *, streaming: bool = False) -> pymysql.connections.Connection:
        return pymysql.connect(
            host=self._settings.db_host,
            port=self._settings.db_port,
            user=self._settings.db_username,
            password=self._settings.db_password,
            database=self._settings.db_database,
            charset=self._settings.db_charset,
            cursorclass=SSDictCursor if streaming else pymysql.cursors.DictCursor,
            connect_timeout=10,
            read_timeout=120,
        )

    def _where(self) -> str:
        sql = _BASE_SELECT
        if self._settings.index_website_live_only:
            sql += _WEBSITE_LIVE_CLAUSE
        return sql

    def count(self) -> int:
        """Total eligible products, for rebuild progress reporting."""
        sql = f"SELECT COUNT(*) AS total FROM ({self._where()}) AS eligible"
        with self._connect() as connection, connection.cursor() as cursor:
            cursor.execute(sql)
            row = cursor.fetchone()
            return int(row["total"]) if row else 0

    def iter_products(self, chunk_size: int | None = None) -> Iterator[list[ProductPayload]]:
        """Stream eligible products in chunks.

        A server-side (unbuffered) cursor is used so a 100k-row catalogue never
        has to fit in the service's memory at once.
        """
        size = chunk_size or self._settings.rebuild_chunk_size
        sql = self._where() + " ORDER BY pd.id"

        with self._connect(streaming=True) as connection, connection.cursor() as cursor:
            cursor.execute(sql)
            while True:
                rows = cursor.fetchmany(size)
                if not rows:
                    break
                yield [self._to_payload(row) for row in rows]

    def eligible_ids(self) -> set[int]:
        """Ids that *should* be in the index — used to prune stale vectors."""
        sql = f"SELECT product_id FROM ({self._where()}) AS eligible"
        with self._connect(streaming=True) as connection, connection.cursor() as cursor:
            cursor.execute(sql)
            return {int(row["product_id"]) for row in cursor.fetchall()}

    def fetch_one(self, product_id: int) -> ProductPayload | None:
        sql = self._where() + " AND pd.id = %s LIMIT 1"
        with self._connect() as connection, connection.cursor() as cursor:
            cursor.execute(sql, (product_id,))
            row = cursor.fetchone()
            return self._to_payload(row) if row else None

    def health(self) -> bool:
        try:
            with self._connect() as connection, connection.cursor() as cursor:
                cursor.execute("SELECT 1")
                cursor.fetchone()
            return True
        except pymysql.Error:
            logger.exception("MySQL health check failed", extra={"dsn": self._settings.mysql_dsn})
            return False

    @staticmethod
    def _to_payload(row: dict) -> ProductPayload:
        return ProductPayload(
            product_id=int(row["product_id"]),
            image=row["image"],
            code=row.get("code"),
            name=row.get("name"),
            category_id=int(row["category_id"]) if row.get("category_id") is not None else None,
            category_name=row.get("category_name"),
        )
