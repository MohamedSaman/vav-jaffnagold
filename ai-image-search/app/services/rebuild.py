"""Full index rebuild, shared by the HTTP endpoint and the CLI script.

The rebuild is *incremental by default*: products whose image bytes are unchanged
are skipped, so a nightly run over 100k products costs seconds instead of hours.
Pass ``force=True`` to re-embed everything (needed after a model change).
"""

from __future__ import annotations

import time

from ..config import Settings
from ..logging_config import get_logger
from .engine import SearchEngine
from .product_source import ProductSource

logger = get_logger(__name__)


def run_rebuild(
    engine: SearchEngine,
    settings: Settings,
    *,
    force: bool = False,
    prune: bool = True,
) -> dict:
    """Walk the catalogue and bring the index up to date.

    Returns the final progress snapshot. Progress is also published on
    ``engine.rebuild_progress`` so ``GET /api/v1/index/status`` can be polled
    while this runs.
    """
    progress = engine.rebuild_progress
    source = ProductSource(settings)
    started = time.perf_counter()

    try:
        total = source.count()
        progress.start(total)
        logger.info(
            "Rebuild started", extra={"total": total, "force": force, "prune": prune}
        )

        if force:
            # A forced rebuild re-embeds everything, so start from a clean index
            # rather than upserting over the old vectors.
            engine.clear()

        for chunk in source.iter_products():
            for payload in chunk:
                payload.force = force

            # Defer the index save until the end: writing a 200 MB file after
            # every 200-product chunk would dominate the runtime.
            results = engine.index_products(chunk, save=False)

            indexed = sum(1 for r in results if r.status == "indexed")
            skipped = sum(1 for r in results if r.status == "skipped")
            failed = sum(1 for r in results if r.status == "failed")
            progress.bump(indexed=indexed, skipped=skipped, failed=failed)

            snapshot = progress.snapshot()
            logger.info(
                "Rebuild progress",
                extra={
                    "processed": snapshot["processed"],
                    "total": snapshot["total"],
                    "indexed": snapshot["indexed"],
                    "skipped": snapshot["skipped"],
                    "failed": snapshot["failed"],
                },
            )

        removed = 0
        if prune and not force:
            removed = _prune_stale(engine, source)

        engine.index.save(force=True)
        progress.finish(removed=removed)

        snapshot = progress.snapshot()
        logger.info(
            "Rebuild finished",
            extra={
                **{k: v for k, v in snapshot.items() if k != "error"},
                "vectors": engine.index.size,
                "took_seconds": round(time.perf_counter() - started, 2),
            },
        )
        return snapshot

    except Exception as exc:
        logger.exception("Rebuild failed")
        progress.finish(error=str(exc))
        # Persist whatever was embedded before the failure so the work is not lost.
        try:
            engine.index.save(force=True)
        except Exception:
            logger.exception("Could not persist partial index after rebuild failure")
        return progress.snapshot()


def _prune_stale(engine: SearchEngine, source: ProductSource) -> int:
    """Drop vectors for products that are no longer eligible.

    Catches products deleted straight from the database, or unpublished while the
    Python service was unreachable and the sync job gave up.
    """
    eligible = source.eligible_ids()
    indexed = engine.metadata.all_ids()
    stale = sorted(indexed - eligible)

    if not stale:
        return 0

    logger.info("Pruning stale vectors", extra={"count": len(stale)})
    engine.delete_products(stale, save=False)
    return len(stale)
