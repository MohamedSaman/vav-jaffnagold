"""Customer-facing search endpoints."""

from __future__ import annotations

from anyio import to_thread
from fastapi import APIRouter, Depends, File, Form, Query, Request, UploadFile

from ..config import SearchStrategy, get_settings
from ..dependencies import get_engine
from ..exceptions import InvalidImageError, PayloadTooLargeError
from ..logging_config import get_logger
from ..schemas import SearchResponse, TextSearchRequest
from ..services.engine import SearchEngine

logger = get_logger(__name__)
router = APIRouter(prefix="/api/v1", tags=["search"])

# Read the upload in bounded chunks so an oversized body is rejected without
# ever being fully buffered.
_CHUNK_SIZE = 256 * 1024


async def _read_capped(upload: UploadFile, limit: int) -> bytes:
    buffer = bytearray()

    while True:
        chunk = await upload.read(_CHUNK_SIZE)
        if not chunk:
            break
        buffer.extend(chunk)
        if len(buffer) > limit:
            raise PayloadTooLargeError(
                f"Image exceeds the {limit / (1024 * 1024):.0f} MB limit",
                details={"limit_bytes": limit},
            )

    return bytes(buffer)


@router.post(
    "/search",
    response_model=SearchResponse,
    summary="Find visually similar products from an uploaded image",
)
async def search_by_image(
    request: Request,
    image: UploadFile = File(..., description="JPEG, PNG, WEBP, GIF or BMP, max 5 MB"),
    top_k: int | None = Form(None, description="How many products to return (default 20)"),
    category_id: int | None = Form(None, description="Restrict results to one shop category"),
    min_similarity: float | None = Form(None, description="Cosine similarity floor, -1..1"),
    strategy: SearchStrategy | None = Form(None, description="boost | partition | strict"),
    detect_type: bool = Form(True, description="Run jewelry type detection on the query"),
    engine: SearchEngine = Depends(get_engine),
) -> SearchResponse:
    """The main entry point Laravel calls when a customer uploads a photo.

    Rejects anything that is not a decodable image of an allowed format. The
    bytes are only ever decoded by Pillow — nothing is written to a web-served
    directory and nothing is executed.
    """
    settings = get_settings()

    # Cheap pre-check before reading the body at all.
    declared_length = request.headers.get("content-length")
    if declared_length and declared_length.isdigit():
        # Allow slack for multipart boundaries and the other form fields.
        if int(declared_length) > settings.max_upload_bytes + 64 * 1024:
            raise PayloadTooLargeError(
                f"Request body exceeds the {settings.max_upload_bytes / (1024 * 1024):.0f} MB limit",
                details={"content_length": int(declared_length)},
            )

    data = await _read_capped(image, settings.max_upload_bytes)
    if not data:
        raise InvalidImageError("No image data was received")

    def _run() -> dict:
        # Decoding and the CLIP forward pass are CPU-bound; run them off the
        # event loop so concurrent requests are not serialised by it.
        decoded = engine.loader.load_upload(data, filename=image.filename)
        return engine.search_by_image(
            decoded,
            top_k=top_k,
            category_id=category_id,
            min_similarity=min_similarity,
            strategy=strategy,
            detect_type=detect_type,
        )

    result = await to_thread.run_sync(_run)

    logger.info(
        "Search request served",
        extra={
            "query_id": result["query_id"],
            # 'filename' is a reserved LogRecord attribute (the source .py file
            # of the log call) — using it as an extra= key raises KeyError.
            "upload_filename": image.filename,
            "size_bytes": len(data),
            "returned": result["returned"],
        },
    )

    return SearchResponse(**result)


@router.post(
    "/search/text",
    response_model=SearchResponse,
    summary="Find products from a text description",
)
async def search_by_text(
    payload: TextSearchRequest,
    engine: SearchEngine = Depends(get_engine),
) -> SearchResponse:
    """Search the same vectors with words instead of a picture.

    CLIP embeds text and images into one shared space, so this needs no extra
    index — "thick gold bridal necklace" is matched against product photos
    directly. Useful as a fallback when a customer has no image to upload.
    """
    result = await to_thread.run_sync(
        lambda: engine.search_by_text(
            payload.query,
            top_k=payload.top_k,
            category_id=payload.category_id,
            min_similarity=payload.min_similarity,
        )
    )

    return SearchResponse(**result)


@router.get("/search/types", summary="List detectable jewelry types")
def list_types(engine: SearchEngine = Depends(get_engine)) -> dict:
    """The jewelry vocabulary, with how many indexed products carry each type."""
    return {
        "types": engine.classifier.types,
        "indexed_breakdown": engine.metadata.type_breakdown(),
    }
