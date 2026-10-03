"""Image loading, path resolution and upload validation."""

from __future__ import annotations

from pathlib import Path

import pytest

from app.config import Settings
from app.exceptions import ImageUnavailableError, InvalidImageError, PayloadTooLargeError
from app.services.image_loader import ImageLoader


@pytest.fixture
def loader(settings: Settings) -> ImageLoader:
    return ImageLoader(settings)


# ------------------------------------------------------------------- resolution
def test_resolves_public_images_path(
    loader: ImageLoader, settings: Settings, png_bytes: bytes
) -> None:
    (settings.laravel_public_path / "images" / "ring.png").write_bytes(png_bytes)

    image, fingerprint = loader.load_product_image("images/ring.png")

    assert image.mode == "RGB"
    assert len(fingerprint) == 64  # sha256 hex


def test_resolves_storage_symlink_style_path(
    loader: ImageLoader, settings: Settings, png_bytes: bytes
) -> None:
    """'storage/images/x' maps to storage/app/public/images/x on disk."""
    target = settings.laravel_storage_path / "app" / "public" / "images" / "chain.png"
    target.write_bytes(png_bytes)

    image, _ = loader.load_product_image("storage/images/chain.png")

    assert image.size == (128, 128)


def test_resolves_bare_filename(
    loader: ImageLoader, settings: Settings, png_bytes: bytes
) -> None:
    """Older records store just the filename."""
    (settings.laravel_public_path / "images" / "legacy.png").write_bytes(png_bytes)

    image, _ = loader.load_product_image("legacy.png")

    assert image.size == (128, 128)


def test_missing_image_raises(loader: ImageLoader) -> None:
    with pytest.raises(ImageUnavailableError):
        loader.load_product_image("images/does-not-exist.png")


def test_placeholder_image_is_refused(loader: ImageLoader) -> None:
    """The default placeholder must never occupy a slot in the index."""
    with pytest.raises(ImageUnavailableError):
        loader.load_product_image("images/product.jpg")


def test_empty_source_raises(loader: ImageLoader) -> None:
    with pytest.raises(ImageUnavailableError):
        loader.load_product_image("")


def test_path_traversal_is_blocked(
    loader: ImageLoader, settings: Settings, png_bytes: bytes
) -> None:
    """A '..' escape must not read files outside the Laravel roots."""
    outside = settings.laravel_public_path.parent.parent / "secret.png"
    outside.write_bytes(png_bytes)

    with pytest.raises(ImageUnavailableError):
        loader.load_product_image("images/../../../secret.png")


def test_remote_fetch_disabled(loader: ImageLoader) -> None:
    with pytest.raises(ImageUnavailableError):
        loader.load_product_image("https://example.com/ring.jpg")


def test_fingerprint_changes_with_content(
    loader: ImageLoader, settings: Settings, png_bytes: bytes
) -> None:
    """The fingerprint is what makes incremental rebuilds correct."""
    path = settings.laravel_public_path / "images" / "p.png"
    path.write_bytes(png_bytes)
    _, first = loader.load_product_image("images/p.png")

    from PIL import Image
    import io

    buffer = io.BytesIO()
    Image.new("RGB", (128, 128), color=(10, 10, 10)).save(buffer, format="PNG")
    path.write_bytes(buffer.getvalue())
    _, second = loader.load_product_image("images/p.png")

    assert first != second


# ----------------------------------------------------------------- upload rules
def test_valid_upload_decodes(loader: ImageLoader, png_bytes: bytes) -> None:
    image = loader.load_upload(png_bytes, filename="photo.png")

    assert image.mode == "RGB"
    assert image.size == (128, 128)


def test_empty_upload_rejected(loader: ImageLoader) -> None:
    with pytest.raises(InvalidImageError):
        loader.load_upload(b"")


def test_oversized_upload_rejected(loader: ImageLoader, settings: Settings) -> None:
    payload = b"\x00" * (settings.max_upload_bytes + 1)

    with pytest.raises(PayloadTooLargeError):
        loader.load_upload(payload)


def test_non_image_bytes_rejected(loader: ImageLoader) -> None:
    """A PHP payload renamed to .png must not get through."""
    with pytest.raises(InvalidImageError):
        loader.load_upload(b"<?php system($_GET['c']); ?>", filename="shell.png")


def test_disallowed_format_rejected(tmp_path: Path, laravel_root: Path, png_bytes: bytes) -> None:
    settings = Settings(
        data_dir=tmp_path / "data",
        laravel_public_path=laravel_root / "public",
        laravel_storage_path=laravel_root / "storage",
        allowed_image_formats=["JPEG"],
    )

    with pytest.raises(InvalidImageError) as exc_info:
        ImageLoader(settings).load_upload(png_bytes)

    assert "PNG" in str(exc_info.value.details.get("detected", ""))


def test_tiny_image_rejected(loader: ImageLoader) -> None:
    """A 1x1 tracking pixel carries no visual signal."""
    import io

    from PIL import Image

    buffer = io.BytesIO()
    Image.new("RGB", (4, 4)).save(buffer, format="PNG")

    with pytest.raises(InvalidImageError):
        loader.load_upload(buffer.getvalue())


def test_transparency_is_flattened_to_white(
    loader: ImageLoader, transparent_png_bytes: bytes
) -> None:
    """Transparent cut-outs must not become black blobs before CLIP sees them."""
    image = loader.load_upload(transparent_png_bytes)

    assert image.mode == "RGB"
    assert image.getpixel((0, 0)) == (255, 255, 255)
