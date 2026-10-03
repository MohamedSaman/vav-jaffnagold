"""Environment-driven configuration for the image search service."""

from __future__ import annotations

from enum import Enum
from functools import lru_cache
from pathlib import Path
from typing import Annotated

from pydantic import Field, field_validator
from pydantic_settings import BaseSettings, NoDecode, SettingsConfigDict

SERVICE_ROOT = Path(__file__).resolve().parent.parent


class SearchStrategy(str, Enum):
    """How the detected jewelry type influences result ordering.

    ``boost``     - same-type candidates get an additive score bonus (default).
    ``partition`` - same-type candidates are listed before all others.
    ``strict``    - non-matching types are dropped unless too few results remain.
    """

    BOOST = "boost"
    PARTITION = "partition"
    STRICT = "strict"


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=SERVICE_ROOT / ".env",
        env_file_encoding="utf-8",
        extra="ignore",
        case_sensitive=False,
        # Allow field names beginning with "model_" without pydantic warnings.
        protected_namespaces=(),
    )

    # ------------------------------------------------------------------ service
    app_name: str = "Jaffna Gold AI Image Search"
    app_env: str = "production"
    log_level: str = "INFO"
    log_json: bool = False
    host: str = "127.0.0.1"
    port: int = 8001
    docs_enabled: bool = True
    # NoDecode: pydantic-settings otherwise tries to JSON-parse list-typed env
    # values before our validator runs, and errors on a plain empty string.
    cors_origins: Annotated[list[str], NoDecode] = Field(default_factory=list)

    # ------------------------------------------------------------------ security
    api_key: str = ""
    require_api_key: bool = True

    # ------------------------------------------------------------------ model
    clip_model_name: str = "openai/clip-vit-base-patch32"
    device: str = "auto"  # auto | cpu | cuda | mps
    embedding_dim: int = 512
    torch_threads: int = 0  # 0 keeps the PyTorch default
    embed_batch_size: int = 16
    hf_home: Path | None = None
    warmup_on_start: bool = True

    # ------------------------------------------------------------------ storage
    data_dir: Path = SERVICE_ROOT / "storage"
    index_filename: str = "products.faiss"
    metadata_filename: str = "metadata.sqlite"
    # IndexFlatIP is exact and handles 100k x 512 float32 (~205 MB) in ~15 ms.
    # Switch to "ivf" only past a few hundred thousand vectors.
    index_type: str = "flat"
    ivf_nlist: int = 256
    ivf_nprobe: int = 16
    autosave: bool = True

    # ------------------------------------------------------------------ search
    default_top_k: int = 20
    max_top_k: int = 100
    # Over-fetch before re-ranking so category logic has candidates to work with.
    candidate_multiplier: int = 10
    max_candidates: int = 500
    min_similarity: float = 0.55
    search_strategy: SearchStrategy = SearchStrategy.BOOST
    category_boost: float = 0.08
    jewelry_type_boost: float = 0.05
    type_confidence_threshold: float = 0.28
    strict_min_results: int = 5

    # ------------------------------------------------------------------ uploads
    max_upload_bytes: int = 5 * 1024 * 1024  # 5 MB
    allowed_image_formats: Annotated[list[str], NoDecode] = Field(
        default_factory=lambda: ["JPEG", "PNG", "WEBP", "GIF", "BMP"]
    )
    max_image_pixels: int = 40_000_000
    min_image_side: int = 32

    # -------------------------------------------------------- product image I/O
    laravel_public_path: Path = Path("/home/sam/Documents/Systems/jaffnagoldpos/public")
    laravel_storage_path: Path = Path("/home/sam/Documents/Systems/jaffnagoldpos/storage")
    allow_remote_images: bool = True
    image_fetch_timeout: float = 10.0
    default_image_names: Annotated[list[str], NoDecode] = Field(
        default_factory=lambda: ["images/product.jpg", "images/products/product.jpg"]
    )

    # ------------------------------------------------------------------- mysql
    db_host: str = "127.0.0.1"
    db_port: int = 3306
    db_database: str = "jaffnagold"
    db_username: str = "root"
    db_password: str = ""
    db_charset: str = "utf8mb4"
    # Only index products the storefront actually shows.
    index_website_live_only: bool = True
    rebuild_chunk_size: int = 200

    @field_validator("cors_origins", "allowed_image_formats", "default_image_names", mode="before")
    @classmethod
    def _split_csv(cls, value: object) -> object:
        """Accept comma-separated strings from .env for list fields."""
        if isinstance(value, str):
            stripped = value.strip()
            if not stripped:
                return []
            if stripped.startswith("["):  # already JSON
                return value
            return [part.strip() for part in stripped.split(",") if part.strip()]
        return value

    @field_validator("allowed_image_formats")
    @classmethod
    def _upper_formats(cls, value: list[str]) -> list[str]:
        return [item.upper() for item in value]

    @property
    def index_path(self) -> Path:
        return self.data_dir / self.index_filename

    @property
    def metadata_path(self) -> Path:
        return self.data_dir / self.metadata_filename

    @property
    def mysql_dsn(self) -> str:
        return (
            f"mysql://{self.db_username}@{self.db_host}:{self.db_port}/{self.db_database}"
        )

    def resolve_device(self) -> str:
        """Pick the torch device, honouring an explicit override."""
        if self.device != "auto":
            return self.device

        try:
            import torch
        except ImportError:  # pragma: no cover - torch is a hard dependency at runtime
            return "cpu"

        if torch.cuda.is_available():
            return "cuda"
        if getattr(torch.backends, "mps", None) is not None and torch.backends.mps.is_available():
            return "mps"
        return "cpu"


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    """Cached settings accessor — safe to call from anywhere."""
    settings = Settings()
    settings.data_dir.mkdir(parents=True, exist_ok=True)
    return settings
