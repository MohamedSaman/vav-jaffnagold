"""Request and response models — these double as the OpenAPI contract."""

from __future__ import annotations

from typing import Literal

from pydantic import BaseModel, Field, model_validator


# --------------------------------------------------------------------- results
class SearchMatch(BaseModel):
    product_id: int = Field(..., description="MySQL product_details.id")
    score: float = Field(..., description="Final ranking score after category adjustment")
    similarity: float = Field(..., description="Raw cosine similarity in [-1, 1]")
    code: str | None = None
    name: str | None = None
    category_id: int | None = None
    category_name: str | None = None
    jewelry_type: str | None = None
    same_type: bool = Field(False, description="True when the product matches the detected type")


class DetectedType(BaseModel):
    jewelry_type: str
    confidence: float
    scores: dict[str, float] = Field(
        default_factory=dict, description="Confidence for every candidate type"
    )


class SearchResponse(BaseModel):
    success: bool = True
    query_id: str = Field(..., description="Correlation id, also written to the service log")
    detected_type: DetectedType | None = None
    strategy: str
    total_indexed: int
    returned: int
    took_ms: float
    results: list[SearchMatch]


class TextSearchRequest(BaseModel):
    query: str = Field(..., min_length=2, max_length=200)
    top_k: int | None = Field(None, ge=1)
    category_id: int | None = None
    min_similarity: float | None = Field(None, ge=-1.0, le=1.0)


# ----------------------------------------------------------------- index input
class ProductPayload(BaseModel):
    """One product to embed and store in the index."""

    product_id: int = Field(..., ge=1)
    # Either a filesystem-relative path (as stored in product_details.image)
    # or an absolute http(s) URL.
    image: str | None = Field(
        None, description="Value of product_details.image, or an absolute URL"
    )
    image_url: str | None = Field(None, description="Explicit absolute URL override")
    code: str | None = None
    name: str | None = None
    category_id: int | None = None
    category_name: str | None = None
    force: bool = Field(
        False, description="Re-embed even when the image fingerprint is unchanged"
    )

    @model_validator(mode="after")
    def _require_a_source(self) -> "ProductPayload":
        if not (self.image or self.image_url):
            raise ValueError("either 'image' or 'image_url' must be provided")
        return self

    def source(self) -> str:
        return self.image_url or self.image  # type: ignore[return-value]


class BulkProductPayload(BaseModel):
    products: list[ProductPayload] = Field(..., min_length=1, max_length=500)


class IndexedProduct(BaseModel):
    product_id: int
    status: Literal["indexed", "skipped", "failed"]
    jewelry_type: str | None = None
    message: str | None = None


class IndexWriteResponse(BaseModel):
    success: bool = True
    total_indexed: int = Field(..., description="Vector count after the operation")
    items: list[IndexedProduct]


class DeleteResponse(BaseModel):
    success: bool = True
    product_id: int
    removed: bool
    total_indexed: int


# ---------------------------------------------------------------- index status
class RebuildRequest(BaseModel):
    force: bool = Field(
        False, description="Re-embed every product, ignoring unchanged fingerprints"
    )
    prune: bool = Field(
        True, description="Drop vectors for products no longer eligible for the website"
    )


class RebuildState(BaseModel):
    state: Literal["idle", "running", "completed", "failed"]
    started_at: str | None = None
    finished_at: str | None = None
    processed: int = 0
    indexed: int = 0
    skipped: int = 0
    failed: int = 0
    removed: int = 0
    total: int = 0
    error: str | None = None

    @property
    def progress(self) -> float:
        return round(self.processed / self.total, 4) if self.total else 0.0


class RebuildResponse(BaseModel):
    success: bool = True
    message: str
    rebuild: RebuildState


class IndexStatsResponse(BaseModel):
    total_indexed: int
    metadata_rows: int
    embedding_dim: int
    index_type: str
    index_path: str
    last_indexed_at: str | None = None
    types: dict[str, int] = Field(default_factory=dict)
    rebuild: RebuildState


class HealthResponse(BaseModel):
    status: Literal["ok", "degraded"]
    service: str
    version: str
    environment: str
    model_ready: bool
    clip_model: str
    device: str
    embedding_dim: int
    total_indexed: int
    uptime_seconds: float


class ErrorResponse(BaseModel):
    success: bool = False
    error: str
    message: str
    details: dict = Field(default_factory=dict)
