"""FAISS index wrapper.

Design notes
------------
* Vectors are L2-normalised before insertion, so ``IndexFlatIP`` (inner product)
  computes exact **cosine similarity**.
* The index is wrapped in ``IndexIDMap2``, which lets us address vectors by the
  MySQL ``product_details.id`` directly. No separate id mapping file to keep in
  sync, and single-product add/update/delete becomes trivial.
* ``IndexFlatIP`` is exact and brute-force. At 100k x 512 float32 that is ~205 MB
  of RAM and roughly 10-20 ms per query on a modern CPU core — comfortably inside
  the 100 ms budget, with no recall loss. An IVF option is provided for when the
  catalogue outgrows brute force.
* Saves are atomic (write to a temp file, then ``os.replace``) so a crash mid-save
  cannot leave a truncated index behind.
"""

from __future__ import annotations

import os
import threading
from pathlib import Path
from typing import Sequence

import faiss
import numpy as np

from ..logging_config import get_logger

logger = get_logger(__name__)


class VectorIndex:
    def __init__(
        self,
        dim: int,
        path: Path,
        *,
        index_type: str = "flat",
        nlist: int = 256,
        nprobe: int = 16,
    ) -> None:
        self._dim = dim
        self._path = path
        self._index_type = index_type.lower()
        self._nlist = nlist
        self._nprobe = nprobe
        # Guards every mutation and every search. FAISS reads are thread-safe,
        # but a concurrent remove/add would invalidate an in-flight search.
        self._lock = threading.RLock()
        self._dirty = False
        self._index: faiss.Index = self._new_index()

        path.parent.mkdir(parents=True, exist_ok=True)

    # -------------------------------------------------------------- construction
    def _new_index(self) -> faiss.Index:
        if self._index_type == "ivf":
            quantizer = faiss.IndexFlatIP(self._dim)
            base = faiss.IndexIVFFlat(
                quantizer, self._dim, self._nlist, faiss.METRIC_INNER_PRODUCT
            )
            base.nprobe = self._nprobe
        else:
            base = faiss.IndexFlatIP(self._dim)

        return faiss.IndexIDMap2(base)

    def load(self) -> None:
        """Read the index from disk, or start empty if this is a first run."""
        with self._lock:
            if not self._path.exists():
                logger.info("No existing index found; starting empty", extra={"path": str(self._path)})
                return

            try:
                index = faiss.read_index(str(self._path))
            except Exception:
                logger.exception(
                    "Failed to read index; starting empty. Run a rebuild to restore it.",
                    extra={"path": str(self._path)},
                )
                return

            if index.d != self._dim:
                logger.error(
                    "Index dimension mismatch; discarding on-disk index. Run a rebuild.",
                    extra={"index_dim": index.d, "expected": self._dim},
                )
                return

            self._index = index
            self._apply_runtime_params()
            logger.info(
                "Index loaded", extra={"vectors": index.ntotal, "path": str(self._path)}
            )

    def _apply_runtime_params(self) -> None:
        """Re-apply nprobe after a load (it is a search-time parameter)."""
        try:
            inner = faiss.downcast_index(self._index.index)
            if isinstance(inner, faiss.IndexIVFFlat):
                inner.nprobe = self._nprobe
        except Exception:  # pragma: no cover - best effort only
            pass

    # ------------------------------------------------------------------ mutation
    def upsert(self, product_ids: Sequence[int], vectors: np.ndarray) -> None:
        """Insert or replace vectors for the given product ids.

        FAISS has no update primitive, so this is remove-then-add. Removing first
        is what keeps a re-index from producing duplicate entries for one product.
        """
        if len(product_ids) == 0:
            return

        ids = np.asarray(product_ids, dtype=np.int64)
        matrix = np.ascontiguousarray(np.asarray(vectors, dtype=np.float32))

        if matrix.ndim == 1:
            matrix = matrix.reshape(1, -1)
        if matrix.shape[0] != ids.shape[0]:
            raise ValueError(
                f"Got {ids.shape[0]} ids but {matrix.shape[0]} vectors"
            )
        if matrix.shape[1] != self._dim:
            raise ValueError(f"Expected {self._dim}-dim vectors, got {matrix.shape[1]}")

        with self._lock:
            self._remove_ids_unlocked(ids)
            self._ensure_trained(matrix)
            self._index.add_with_ids(matrix, ids)
            self._dirty = True

    def _ensure_trained(self, sample: np.ndarray) -> None:
        """IVF indexes need training before the first add; flat indexes do not."""
        if self._index.is_trained:
            return

        if sample.shape[0] < self._nlist:
            logger.warning(
                "Not enough vectors to train the IVF index; falling back to a flat index",
                extra={"available": sample.shape[0], "required": self._nlist},
            )
            self._index_type = "flat"
            self._index = self._new_index()
            return

        logger.info("Training IVF index", extra={"vectors": sample.shape[0]})
        self._index.train(sample)

    def remove(self, product_ids: Sequence[int]) -> int:
        ids = np.asarray(product_ids, dtype=np.int64)
        if ids.size == 0:
            return 0

        with self._lock:
            removed = self._remove_ids_unlocked(ids)
            if removed:
                self._dirty = True
            return removed

    def _remove_ids_unlocked(self, ids: np.ndarray) -> int:
        """Remove by id, tolerating ids that are not present."""
        if self._index.ntotal == 0:
            return 0

        try:
            # IDSelectorBatch copies the ids into an internal set, so the numpy
            # buffer does not need to outlive this call.
            selector = faiss.IDSelectorBatch(ids.size, faiss.swig_ptr(ids))
            return int(self._index.remove_ids(selector))
        except RuntimeError:
            logger.exception("remove_ids failed", extra={"count": int(ids.size)})
            return 0

    def reset(self) -> None:
        """Drop every vector — used at the start of a forced full rebuild."""
        with self._lock:
            self._index = self._new_index()
            self._dirty = True

    def contains(self, product_id: int) -> bool:
        with self._lock:
            if self._index.ntotal == 0:
                return False
            try:
                self._index.reconstruct(int(product_id))
                return True
            except RuntimeError:
                return False

    # -------------------------------------------------------------------- search
    def search(self, vector: np.ndarray, k: int) -> tuple[np.ndarray, np.ndarray]:
        """Return ``(similarities, product_ids)`` for the nearest ``k`` vectors.

        Missing slots come back as id ``-1`` and are filtered by the caller.
        """
        query = np.ascontiguousarray(np.asarray(vector, dtype=np.float32).reshape(1, -1))

        with self._lock:
            if self._index.ntotal == 0:
                return np.empty((0,), dtype=np.float32), np.empty((0,), dtype=np.int64)

            k = max(1, min(k, self._index.ntotal))
            scores, ids = self._index.search(query, k)

        return scores[0], ids[0]

    # ---------------------------------------------------------------- persistence
    def save(self, *, force: bool = False) -> bool:
        """Atomically write the index to disk. No-op when nothing changed."""
        with self._lock:
            if not self._dirty and not force:
                return False

            tmp_path = self._path.with_suffix(self._path.suffix + ".tmp")
            try:
                faiss.write_index(self._index, str(tmp_path))
                os.replace(tmp_path, self._path)
                self._dirty = False
                logger.info(
                    "Index saved", extra={"vectors": self._index.ntotal, "path": str(self._path)}
                )
                return True
            except Exception:
                logger.exception("Failed to save index", extra={"path": str(self._path)})
                tmp_path.unlink(missing_ok=True)
                raise

    # ----------------------------------------------------------------- introspect
    @property
    def size(self) -> int:
        with self._lock:
            return int(self._index.ntotal)

    @property
    def dim(self) -> int:
        return self._dim

    @property
    def index_type(self) -> str:
        return self._index_type

    @property
    def path(self) -> Path:
        return self._path
