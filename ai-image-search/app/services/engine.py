"""The search engine — the single object the API routers talk to.

It owns the CLIP embedder, the FAISS index, the metadata store and the image
loader, and exposes the three things the outside world cares about: search,
index writes, and statistics.
"""

from __future__ import annotations

import threading
import time
import uuid
from dataclasses import dataclass, field
from datetime import datetime, timezone

import numpy as np
from PIL import Image

from ..config import SearchStrategy, Settings
from ..exceptions import ImageSearchError
from ..logging_config import get_logger
from ..schemas import DetectedType, IndexedProduct, ProductPayload, SearchMatch
from .classifier import UNKNOWN_TYPE, JewelryClassifier
from .embedder import ClipEmbedder
from .image_loader import ImageLoader
from .metadata import MetadataStore, ProductRecord
from .vector_index import VectorIndex

logger = get_logger(__name__)


@dataclass
class RebuildProgress:
    """Mutable progress record for the long-running rebuild task."""

    state: str = "idle"
    started_at: str | None = None
    finished_at: str | None = None
    processed: int = 0
    indexed: int = 0
    skipped: int = 0
    failed: int = 0
    removed: int = 0
    total: int = 0
    error: str | None = None
    _lock: threading.Lock = field(default_factory=threading.Lock, repr=False)

    def start(self, total: int) -> None:
        with self._lock:
            self.state = "running"
            self.started_at = datetime.now(timezone.utc).isoformat(timespec="seconds")
            self.finished_at = None
            self.processed = self.indexed = self.skipped = self.failed = self.removed = 0
            self.total = total
            self.error = None

    def bump(self, *, indexed: int = 0, skipped: int = 0, failed: int = 0) -> None:
        with self._lock:
            self.indexed += indexed
            self.skipped += skipped
            self.failed += failed
            self.processed += indexed + skipped + failed

    def finish(self, *, removed: int = 0, error: str | None = None) -> None:
        with self._lock:
            self.removed = removed
            self.error = error
            self.state = "failed" if error else "completed"
            self.finished_at = datetime.now(timezone.utc).isoformat(timespec="seconds")

    @property
    def is_running(self) -> bool:
        with self._lock:
            return self.state == "running"

    def snapshot(self) -> dict:
        with self._lock:
            return {
                "state": self.state,
                "started_at": self.started_at,
                "finished_at": self.finished_at,
                "processed": self.processed,
                "indexed": self.indexed,
                "skipped": self.skipped,
                "failed": self.failed,
                "removed": self.removed,
                "total": self.total,
                "error": self.error,
            }


class SearchEngine:
    def __init__(self, settings: Settings) -> None:
        self._settings = settings
        self._started_at = time.monotonic()

        self.embedder = ClipEmbedder(settings)
        self.classifier = JewelryClassifier(self.embedder)
        self.loader = ImageLoader(settings)
        self.metadata = MetadataStore(settings.metadata_path)
        self.index = VectorIndex(
            dim=settings.embedding_dim,
            path=settings.index_path,
            index_type=settings.index_type,
            nlist=settings.ivf_nlist,
            nprobe=settings.ivf_nprobe,
        )
        self.rebuild_progress = RebuildProgress()

    # ----------------------------------------------------------------- lifecycle
    def startup(self) -> None:
        """Load the model and index once, at process start."""
        self.embedder.load()

        if self.embedder.dim != self._settings.embedding_dim:
            # Trust the model over the config file and rebuild the index shell
            # at the real dimension.
            self.index = VectorIndex(
                dim=self.embedder.dim,
                path=self._settings.index_path,
                index_type=self._settings.index_type,
                nlist=self._settings.ivf_nlist,
                nprobe=self._settings.ivf_nprobe,
            )

        self.classifier.build()
        self.index.load()
        self._reconcile()

        logger.info(
            "Search engine ready",
            extra={"vectors": self.index.size, "metadata_rows": self.metadata.count()},
        )

    def _reconcile(self) -> None:
        """Warn when the index and metadata store disagree.

        They are separate files, so a partial restore or a manual deletion can
        leave them inconsistent. Metadata rows without a vector are harmless
        (they just never match), so this only reports rather than repairs.
        """
        vector_count = self.index.size
        metadata_count = self.metadata.count()

        if vector_count != metadata_count:
            logger.warning(
                "Index and metadata counts differ; a rebuild will resynchronise them",
                extra={"vectors": vector_count, "metadata_rows": metadata_count},
            )

    def shutdown(self) -> None:
        try:
            self.index.save()
        except Exception:
            logger.exception("Failed to persist index during shutdown")
        self.metadata.close()

    @property
    def uptime_seconds(self) -> float:
        return round(time.monotonic() - self._started_at, 2)

    # -------------------------------------------------------------------- search
    def search_by_image(
        self,
        image: Image.Image,
        *,
        top_k: int | None = None,
        category_id: int | None = None,
        min_similarity: float | None = None,
        strategy: SearchStrategy | None = None,
        detect_type: bool = True,
    ) -> dict:
        embedding = self.embedder.embed_images([image])[0]

        detected: DetectedType | None = None
        if detect_type and self.classifier.ready:
            jewelry_type, confidence, scores = self.classifier.classify(embedding)
            detected = DetectedType(
                jewelry_type=jewelry_type, confidence=confidence, scores=scores
            )

        return self._rank(
            embedding,
            detected=detected,
            top_k=top_k,
            category_id=category_id,
            min_similarity=min_similarity,
            strategy=strategy,
        )

    def search_by_text(
        self,
        query: str,
        *,
        top_k: int | None = None,
        category_id: int | None = None,
        min_similarity: float | None = None,
    ) -> dict:
        """Text search over the same vectors — free, because CLIP is multi-modal."""
        embedding = self.embedder.embed_texts([query])[0]
        detected_type = self.classifier.type_from_category(query)
        detected = (
            DetectedType(jewelry_type=detected_type, confidence=1.0, scores={})
            if detected_type
            else None
        )

        return self._rank(
            embedding,
            detected=detected,
            top_k=top_k,
            category_id=category_id,
            min_similarity=min_similarity,
            strategy=None,
        )

    def _rank(
        self,
        embedding: np.ndarray,
        *,
        detected: DetectedType | None,
        top_k: int | None,
        category_id: int | None,
        min_similarity: float | None,
        strategy: SearchStrategy | None,
    ) -> dict:
        """Search, then reorder using category and jewelry-type signals."""
        started = time.perf_counter()
        settings = self._settings
        query_id = uuid.uuid4().hex[:12]

        top_k = min(top_k or settings.default_top_k, settings.max_top_k)
        threshold = settings.min_similarity if min_similarity is None else min_similarity
        strategy = strategy or settings.search_strategy

        # Over-fetch: the category logic can only reorder candidates it has seen,
        # so ask FAISS for more than we intend to return.
        candidate_k = min(top_k * settings.candidate_multiplier, settings.max_candidates)
        similarities, product_ids = self.index.search(embedding, candidate_k)

        if product_ids.size == 0:
            return self._empty_result(query_id, detected, strategy, started)

        valid = product_ids >= 0  # FAISS pads short result sets with -1
        product_ids = product_ids[valid]
        similarities = similarities[valid]

        records = self.metadata.get_many([int(pid) for pid in product_ids])

        detected_type = detected.jewelry_type if detected else None
        type_is_confident = bool(
            detected
            and detected_type not in (None, UNKNOWN_TYPE)
            and detected.confidence >= settings.type_confidence_threshold
        )

        matches: list[SearchMatch] = []
        for raw_id, raw_similarity in zip(product_ids, similarities):
            product_id = int(raw_id)
            similarity = float(raw_similarity)

            if similarity < threshold:
                continue

            record = records.get(product_id)

            # An explicit category filter from the UI is a hard constraint —
            # the customer asked for it.
            if category_id is not None and (record is None or record.category_id != category_id):
                continue

            same_type = bool(
                type_is_confident and record is not None and record.jewelry_type == detected_type
            )

            score = similarity
            if same_type:
                score += settings.jewelry_type_boost
            if category_id is not None and record is not None and record.category_id == category_id:
                score += settings.category_boost

            matches.append(
                SearchMatch(
                    product_id=product_id,
                    score=round(score, 6),
                    similarity=round(similarity, 6),
                    code=record.code if record else None,
                    name=record.name if record else None,
                    category_id=record.category_id if record else None,
                    category_name=record.category_name if record else None,
                    jewelry_type=record.jewelry_type if record else None,
                    same_type=same_type,
                )
            )

        matches = self._apply_strategy(matches, strategy, type_is_confident, top_k)

        took_ms = round((time.perf_counter() - started) * 1000, 2)
        logger.info(
            "Image search completed",
            extra={
                "query_id": query_id,
                "detected_type": detected_type,
                "strategy": strategy.value,
                "candidates": int(product_ids.size),
                "returned": len(matches),
                "took_ms": took_ms,
            },
        )

        return {
            "query_id": query_id,
            "detected_type": detected,
            "strategy": strategy.value,
            "total_indexed": self.index.size,
            "returned": len(matches),
            "took_ms": took_ms,
            "results": matches,
        }

    def _apply_strategy(
        self,
        matches: list[SearchMatch],
        strategy: SearchStrategy,
        type_is_confident: bool,
        top_k: int,
    ) -> list[SearchMatch]:
        """Turn scored candidates into the final ordered page.

        ``boost`` is the default because it keeps a near-perfect visual match at
        the top even if the type guess disagrees — a misclassified query should
        degrade ranking slightly, not hide the right product. ``partition`` and
        ``strict`` honour the category more aggressively for callers that want it.
        """
        if strategy is SearchStrategy.STRICT and type_is_confident:
            same_type = [m for m in matches if m.same_type]
            # Only enforce the filter when it still leaves a usable page;
            # otherwise a customer photographing an unusual piece gets nothing.
            if len(same_type) >= min(self._settings.strict_min_results, top_k):
                matches = same_type

        if strategy is SearchStrategy.PARTITION and type_is_confident:
            same_type = sorted(
                (m for m in matches if m.same_type), key=lambda m: m.score, reverse=True
            )
            others = sorted(
                (m for m in matches if not m.same_type), key=lambda m: m.score, reverse=True
            )
            return (same_type + others)[:top_k]

        matches.sort(key=lambda m: m.score, reverse=True)
        return matches[:top_k]

    def _empty_result(
        self,
        query_id: str,
        detected: DetectedType | None,
        strategy: SearchStrategy,
        started: float,
    ) -> dict:
        return {
            "query_id": query_id,
            "detected_type": detected,
            "strategy": strategy.value,
            "total_indexed": self.index.size,
            "returned": 0,
            "took_ms": round((time.perf_counter() - started) * 1000, 2),
            "results": [],
        }

    # -------------------------------------------------------------- index writes
    def index_products(
        self, payloads: list[ProductPayload], *, save: bool = True
    ) -> list[IndexedProduct]:
        """Embed and store a batch of products.

        One bad image must not sink the whole batch, so failures are collected
        per product and reported back rather than raised.
        """
        results: list[IndexedProduct] = []
        images: list[Image.Image] = []
        pending: list[tuple[ProductPayload, str]] = []

        known_fingerprints = self.metadata.fingerprints()

        for payload in payloads:
            source = payload.source()
            try:
                image, fingerprint = self.loader.load_product_image(source)
            except ImageSearchError as exc:
                logger.warning(
                    "Skipping product image",
                    extra={"product_id": payload.product_id, "reason": exc.message},
                )
                results.append(
                    IndexedProduct(
                        product_id=payload.product_id, status="failed", message=exc.message
                    )
                )
                continue
            except Exception as exc:  # pragma: no cover - unexpected decode failure
                logger.exception("Unexpected error loading image", extra={"product_id": payload.product_id})
                results.append(
                    IndexedProduct(
                        product_id=payload.product_id, status="failed", message=str(exc)
                    )
                )
                continue

            unchanged = (
                not payload.force
                and known_fingerprints.get(payload.product_id) == fingerprint
                and self.index.contains(payload.product_id)
            )
            if unchanged:
                results.append(IndexedProduct(product_id=payload.product_id, status="skipped"))
                continue

            images.append(image)
            pending.append((payload, fingerprint))

        if not pending:
            return results

        embeddings = self.embedder.embed_images(images)

        ids: list[int] = []
        records: list[ProductRecord] = []
        for (payload, fingerprint), embedding in zip(pending, embeddings):
            jewelry_type = self.classifier.resolve_product_type(
                embedding, payload.category_name, payload.name
            )
            ids.append(payload.product_id)
            records.append(
                ProductRecord(
                    product_id=payload.product_id,
                    code=payload.code,
                    name=payload.name,
                    category_id=payload.category_id,
                    category_name=payload.category_name,
                    jewelry_type=jewelry_type,
                    image_source=payload.source(),
                    image_fingerprint=fingerprint,
                )
            )
            results.append(
                IndexedProduct(
                    product_id=payload.product_id, status="indexed", jewelry_type=jewelry_type
                )
            )

        self.index.upsert(ids, embeddings)
        self.metadata.upsert_many(records)

        if save and self._settings.autosave:
            self.index.save()

        return results

    def delete_product(self, product_id: int, *, save: bool = True) -> bool:
        removed_vectors = self.index.remove([product_id])
        removed_metadata = self.metadata.delete(product_id)

        if save and self._settings.autosave and removed_vectors:
            self.index.save()

        return bool(removed_vectors or removed_metadata)

    def delete_products(self, product_ids: list[int], *, save: bool = True) -> int:
        if not product_ids:
            return 0

        removed = self.index.remove(product_ids)
        self.metadata.delete_many(product_ids)

        if save and self._settings.autosave and removed:
            self.index.save()

        return removed

    def clear(self) -> None:
        self.index.reset()
        self.metadata.clear()
        self.index.save(force=True)

    # --------------------------------------------------------------------- stats
    def stats(self) -> dict:
        return {
            "total_indexed": self.index.size,
            "metadata_rows": self.metadata.count(),
            "embedding_dim": self.index.dim,
            "index_type": self.index.index_type,
            "index_path": str(self.index.path),
            "last_indexed_at": self.metadata.last_indexed_at(),
            "types": self.metadata.type_breakdown(),
            "rebuild": self.rebuild_progress.snapshot(),
        }
