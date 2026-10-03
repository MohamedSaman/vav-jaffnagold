"""Index management endpoints — called by Laravel, never by a browser.

Every route here is guarded by the shared-secret ``X-API-Key`` header.
"""

from __future__ import annotations

from anyio import to_thread
from fastapi import APIRouter, BackgroundTasks, Depends, status

from ..config import Settings, get_settings
from ..dependencies import get_engine
from ..exceptions import ProductNotIndexedError, RebuildInProgressError
from ..logging_config import get_logger
from ..schemas import (
    BulkProductPayload,
    DeleteResponse,
    IndexStatsResponse,
    IndexWriteResponse,
    ProductPayload,
    RebuildRequest,
    RebuildResponse,
    RebuildState,
)
from ..security import require_api_key
from ..services.engine import SearchEngine
from ..services.rebuild import run_rebuild

logger = get_logger(__name__)

router = APIRouter(
    prefix="/api/v1/index",
    tags=["index"],
    dependencies=[Depends(require_api_key)],
)


@router.post(
    "/products",
    response_model=IndexWriteResponse,
    status_code=status.HTTP_201_CREATED,
    summary="Add or replace a single product in the index",
)
async def add_product(
    payload: ProductPayload,
    engine: SearchEngine = Depends(get_engine),
) -> IndexWriteResponse:
    """Idempotent upsert — calling it twice for one product does not duplicate it.

    Products whose image bytes are unchanged come back as ``skipped`` unless
    ``force`` is set, which keeps the observer hook cheap on non-image edits.
    """
    items = await to_thread.run_sync(lambda: engine.index_products([payload]))

    return IndexWriteResponse(total_indexed=engine.index.size, items=items)


@router.post(
    "/products/bulk",
    response_model=IndexWriteResponse,
    summary="Add or replace many products in one call",
)
async def add_products_bulk(
    payload: BulkProductPayload,
    engine: SearchEngine = Depends(get_engine),
) -> IndexWriteResponse:
    """Batching amortises the CLIP forward pass; far faster than N single calls."""
    items = await to_thread.run_sync(lambda: engine.index_products(payload.products))

    return IndexWriteResponse(total_indexed=engine.index.size, items=items)


@router.put(
    "/products/{product_id}",
    response_model=IndexWriteResponse,
    summary="Update an indexed product",
)
async def update_product(
    product_id: int,
    payload: ProductPayload,
    engine: SearchEngine = Depends(get_engine),
) -> IndexWriteResponse:
    """Same operation as the POST, with the id taken from the path.

    ``force`` defaults to true here: an explicit update means the caller believes
    something changed, so re-embed rather than trusting the fingerprint.
    """
    payload.product_id = product_id
    payload.force = True

    items = await to_thread.run_sync(lambda: engine.index_products([payload]))

    return IndexWriteResponse(total_indexed=engine.index.size, items=items)


@router.delete(
    "/products/{product_id}",
    response_model=DeleteResponse,
    summary="Remove a product from the index",
)
async def delete_product(
    product_id: int,
    engine: SearchEngine = Depends(get_engine),
) -> DeleteResponse:
    """Deleting an id that was never indexed is a 404, not an error condition
    worth retrying — the caller can treat it as already gone."""
    removed = await to_thread.run_sync(lambda: engine.delete_product(product_id))

    if not removed:
        raise ProductNotIndexedError(
            f"Product {product_id} is not in the index",
            details={"product_id": product_id},
        )

    return DeleteResponse(
        product_id=product_id, removed=True, total_indexed=engine.index.size
    )


@router.post(
    "/rebuild",
    response_model=RebuildResponse,
    status_code=status.HTTP_202_ACCEPTED,
    summary="Rebuild the whole index from MySQL",
)
def rebuild_index(
    payload: RebuildRequest,
    background_tasks: BackgroundTasks,
    engine: SearchEngine = Depends(get_engine),
    settings: Settings = Depends(get_settings),
) -> RebuildResponse:
    """Kick off a rebuild and return immediately.

    A full catalogue walk can take minutes, which is far longer than any sane
    HTTP timeout, so the work runs in the background and progress is polled via
    ``GET /api/v1/index/status``.
    """
    if engine.rebuild_progress.is_running:
        raise RebuildInProgressError(
            "A rebuild is already running; poll /api/v1/index/status for progress"
        )

    background_tasks.add_task(
        run_rebuild, engine, settings, force=payload.force, prune=payload.prune
    )

    logger.info("Rebuild queued", extra={"force": payload.force, "prune": payload.prune})

    return RebuildResponse(
        message="Rebuild started. Poll /api/v1/index/status for progress.",
        rebuild=RebuildState(**engine.rebuild_progress.snapshot()),
    )


@router.get("/status", response_model=IndexStatsResponse, summary="Index statistics")
def index_status(engine: SearchEngine = Depends(get_engine)) -> IndexStatsResponse:
    stats = engine.stats()
    stats["rebuild"] = RebuildState(**stats["rebuild"])
    return IndexStatsResponse(**stats)


@router.post("/save", summary="Force-persist the index to disk")
def save_index(engine: SearchEngine = Depends(get_engine)) -> dict:
    """Only needed when ``AUTOSAVE=false``; otherwise writes happen on mutation."""
    saved = engine.index.save(force=True)
    return {"success": True, "saved": saved, "total_indexed": engine.index.size}


@router.delete(
    "/products",
    summary="Clear the entire index",
    status_code=status.HTTP_200_OK,
)
def clear_index(engine: SearchEngine = Depends(get_engine)) -> dict:
    """Destructive: drops every vector and all metadata. Follow with a rebuild."""
    previous = engine.index.size
    engine.clear()

    logger.warning("Index cleared", extra={"removed": previous})

    return {"success": True, "removed": previous, "total_indexed": engine.index.size}
