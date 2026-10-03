"""Domain exceptions mapped to HTTP responses by the app error handlers."""

from __future__ import annotations


class ImageSearchError(Exception):
    """Base class for every error this service raises deliberately."""

    status_code = 500
    error_code = "internal_error"

    def __init__(self, message: str, *, details: dict | None = None) -> None:
        super().__init__(message)
        self.message = message
        self.details = details or {}


class InvalidImageError(ImageSearchError):
    """Upload could not be decoded, or is not an allowed image format."""

    status_code = 422
    error_code = "invalid_image"


class PayloadTooLargeError(ImageSearchError):
    """Upload exceeded the configured byte ceiling."""

    status_code = 413
    error_code = "payload_too_large"


class ImageUnavailableError(ImageSearchError):
    """A product image could not be located on disk or fetched over HTTP."""

    status_code = 404
    error_code = "image_unavailable"


class ProductNotIndexedError(ImageSearchError):
    """Requested product has no vector in the index."""

    status_code = 404
    error_code = "product_not_indexed"


class ModelNotReadyError(ImageSearchError):
    """A request arrived before the CLIP model finished loading."""

    status_code = 503
    error_code = "model_not_ready"


class RebuildInProgressError(ImageSearchError):
    """A rebuild was requested while one is already running."""

    status_code = 409
    error_code = "rebuild_in_progress"


class UnauthorizedError(ImageSearchError):
    status_code = 401
    error_code = "unauthorized"
