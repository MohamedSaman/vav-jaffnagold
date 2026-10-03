"""Safe loading of product images and customer uploads.

Two rules govern everything here:

1. Bytes are only ever *decoded* as an image, never written to an executable
   location and never handed to a shell. There is no code path that runs an
   upload.
2. Every source is size-capped before decoding, so a hostile file cannot
   exhaust memory (the classic decompression-bomb attack).
"""

from __future__ import annotations

import hashlib
import io
from pathlib import Path
from urllib.parse import urlparse

import requests
from PIL import Image, UnidentifiedImageError

from ..config import Settings
from ..exceptions import ImageUnavailableError, InvalidImageError, PayloadTooLargeError
from ..logging_config import get_logger

logger = get_logger(__name__)

# Pillow refuses images above this pixel count outright, which blocks
# "small file, enormous canvas" decompression bombs.
Image.MAX_IMAGE_PIXELS = 50_000_000


class ImageLoader:
    """Resolves ``product_details.image`` values and validates raw uploads."""

    def __init__(self, settings: Settings) -> None:
        self._settings = settings

    # -------------------------------------------------------------- upload path
    def load_upload(self, data: bytes, *, filename: str | None = None) -> Image.Image:
        """Validate and decode a customer upload.

        The declared Content-Type is ignored on purpose: it is attacker
        controlled. What matters is whether Pillow can decode the bytes and what
        format it reports.
        """
        if not data:
            raise InvalidImageError("The uploaded file is empty")

        if len(data) > self._settings.max_upload_bytes:
            limit_mb = self._settings.max_upload_bytes / (1024 * 1024)
            raise PayloadTooLargeError(
                f"Image exceeds the {limit_mb:.0f} MB limit",
                details={"size_bytes": len(data), "limit_bytes": self._settings.max_upload_bytes},
            )

        return self._decode(data, origin=filename or "upload")

    # ------------------------------------------------------- product image path
    def load_product_image(self, source: str) -> tuple[Image.Image, str]:
        """Load a product image and return it with a content fingerprint.

        The fingerprint lets the indexer skip products whose picture has not
        changed, which is what makes an incremental rebuild fast.
        """
        source = (source or "").strip()
        if not source:
            raise ImageUnavailableError("Product has no image path")

        if source in self._settings.default_image_names:
            raise ImageUnavailableError(
                "Product uses the placeholder image and cannot be indexed",
                details={"source": source},
            )

        if source.startswith(("http://", "https://")):
            data = self._fetch_remote(source)
        else:
            data = self._read_local(source)

        image = self._decode(data, origin=source)
        return image, hashlib.sha256(data).hexdigest()

    # ------------------------------------------------------------------ helpers
    def _read_local(self, source: str) -> bytes:
        path = self._resolve_local_path(source)
        if path is None:
            raise ImageUnavailableError(
                "Product image not found on disk", details={"source": source}
            )

        size = path.stat().st_size
        if size == 0:
            raise ImageUnavailableError("Product image file is empty", details={"path": str(path)})
        if size > self._settings.max_upload_bytes * 4:
            # Product images come from our own admin, so allow a larger ceiling
            # than customer uploads while still refusing absurd files.
            raise PayloadTooLargeError(
                "Product image is too large to index", details={"path": str(path), "size": size}
            )

        return path.read_bytes()

    def _resolve_local_path(self, source: str) -> Path | None:
        """Mirror the storage layout the Laravel app writes to.

        ``product_details.image`` may hold ``images/<file>`` (public dir),
        ``storage/images/<file>`` (public disk via symlink) or a bare filename
        from older records, so all three are tried.
        """
        relative = source.lstrip("/")
        public = self._settings.laravel_public_path
        storage = self._settings.laravel_storage_path

        candidates: list[Path] = []

        if relative.startswith("storage/"):
            # 'storage/images/x.webp' -> storage/app/public/images/x.webp
            tail = relative[len("storage/") :]
            candidates.append(storage / "app" / "public" / tail)
            candidates.append(public / relative)
        elif relative.startswith("images/"):
            candidates.append(public / relative)
            candidates.append(storage / "app" / "public" / relative)
        elif "/" in relative:
            candidates.append(public / relative)
            candidates.append(storage / "app" / "public" / relative)
        else:
            # Bare filename.
            candidates.append(public / "images" / relative)
            candidates.append(storage / "app" / "public" / "images" / relative)

        for candidate in candidates:
            resolved = self._safe_resolve(candidate, (public, storage))
            if resolved is not None and resolved.is_file():
                return resolved

        return None

    @staticmethod
    def _safe_resolve(candidate: Path, roots: tuple[Path, ...]) -> Path | None:
        """Reject any path that escapes the allowed roots via ``..`` or a symlink."""
        try:
            resolved = candidate.resolve(strict=False)
        except (OSError, RuntimeError):
            return None

        for root in roots:
            try:
                resolved.relative_to(root.resolve(strict=False))
                return resolved
            except ValueError:
                continue

        logger.warning("Rejected image path outside allowed roots", extra={"path": str(candidate)})
        return None

    def _fetch_remote(self, url: str) -> bytes:
        if not self._settings.allow_remote_images:
            raise ImageUnavailableError(
                "Remote image fetching is disabled", details={"url": url}
            )

        parsed = urlparse(url)
        if parsed.scheme not in {"http", "https"} or not parsed.hostname:
            raise ImageUnavailableError("Unsupported image URL", details={"url": url})

        limit = self._settings.max_upload_bytes * 4

        try:
            with requests.get(
                url, timeout=self._settings.image_fetch_timeout, stream=True
            ) as response:
                response.raise_for_status()

                declared = response.headers.get("Content-Length")
                if declared and declared.isdigit() and int(declared) > limit:
                    raise PayloadTooLargeError(
                        "Remote image is too large", details={"url": url, "size": int(declared)}
                    )

                # Stream with a hard cap so a lying Content-Length cannot
                # make us buffer an unbounded response.
                buffer = bytearray()
                for chunk in response.iter_content(chunk_size=64 * 1024):
                    buffer.extend(chunk)
                    if len(buffer) > limit:
                        raise PayloadTooLargeError(
                            "Remote image exceeded the size limit while streaming",
                            details={"url": url},
                        )
                return bytes(buffer)
        except requests.RequestException as exc:
            raise ImageUnavailableError(
                f"Could not fetch product image: {exc}", details={"url": url}
            ) from exc

    def _decode(self, data: bytes, *, origin: str) -> Image.Image:
        """Decode bytes to an RGB image, enforcing format and dimension policy."""
        try:
            # verify() consumes the file object, so probe on a throwaway handle.
            probe = Image.open(io.BytesIO(data))
            probe.verify()
            image_format = (probe.format or "").upper()

            if image_format not in self._settings.allowed_image_formats:
                raise InvalidImageError(
                    f"Unsupported image format '{image_format or 'unknown'}'",
                    details={
                        "allowed": self._settings.allowed_image_formats,
                        "detected": image_format or None,
                    },
                )

            image = Image.open(io.BytesIO(data))
            width, height = image.size

            if width * height > self._settings.max_image_pixels:
                raise InvalidImageError(
                    "Image resolution is too large to process",
                    details={"width": width, "height": height},
                )

            min_side = self._settings.min_image_side
            if width < min_side or height < min_side:
                raise InvalidImageError(
                    f"Image must be at least {min_side}x{min_side} pixels",
                    details={"width": width, "height": height},
                )

            # CLIP expects 3-channel input; flatten alpha onto white so
            # transparent PNG cut-outs do not become black blobs.
            if image.mode in ("RGBA", "LA", "P"):
                image = image.convert("RGBA")
                background = Image.new("RGBA", image.size, (255, 255, 255, 255))
                image = Image.alpha_composite(background, image).convert("RGB")
            elif image.mode != "RGB":
                image = image.convert("RGB")

            image.load()
            return image

        except InvalidImageError:
            raise
        except (UnidentifiedImageError, OSError, ValueError) as exc:
            logger.warning("Failed to decode image", extra={"origin": origin, "error": str(exc)})
            raise InvalidImageError(
                "The file could not be read as an image", details={"origin": origin}
            ) from exc
