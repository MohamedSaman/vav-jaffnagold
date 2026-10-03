"""Shared test fixtures.

The heavy tests (anything that touches CLIP) are skipped automatically when
torch is not installed, so the fast suite still runs in CI without a 2 GB image.
"""

from __future__ import annotations

import io
import sys
from pathlib import Path

import pytest

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from app.config import Settings  # noqa: E402


def _has(module: str) -> bool:
    from importlib.util import find_spec

    try:
        return find_spec(module) is not None
    except (ImportError, ValueError):
        return False


requires_torch = pytest.mark.skipif(
    not _has("torch") or not _has("transformers"),
    reason="torch/transformers not installed",
)
requires_faiss = pytest.mark.skipif(not _has("faiss"), reason="faiss not installed")


@pytest.fixture
def laravel_root(tmp_path: Path) -> Path:
    """A fake Laravel tree with both image locations the app writes to."""
    (tmp_path / "public" / "images").mkdir(parents=True)
    (tmp_path / "storage" / "app" / "public" / "images").mkdir(parents=True)
    return tmp_path


@pytest.fixture
def settings(tmp_path: Path, laravel_root: Path) -> Settings:
    return Settings(
        data_dir=tmp_path / "data",
        laravel_public_path=laravel_root / "public",
        laravel_storage_path=laravel_root / "storage",
        require_api_key=False,
        warmup_on_start=False,
        max_upload_bytes=1024 * 1024,
        allow_remote_images=False,
    )


@pytest.fixture
def png_bytes() -> bytes:
    """A small valid PNG."""
    from PIL import Image

    buffer = io.BytesIO()
    Image.new("RGB", (128, 128), color=(212, 175, 55)).save(buffer, format="PNG")
    return buffer.getvalue()


@pytest.fixture
def transparent_png_bytes() -> bytes:
    """RGBA PNG, to check the alpha-flattening path."""
    from PIL import Image

    buffer = io.BytesIO()
    Image.new("RGBA", (64, 64), color=(212, 175, 55, 0)).save(buffer, format="PNG")
    return buffer.getvalue()
