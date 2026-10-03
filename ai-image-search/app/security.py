"""Shared-secret authentication for the index-management endpoints."""

from __future__ import annotations

import secrets

from fastapi import Header

from .config import Settings, get_settings
from .exceptions import UnauthorizedError
from .logging_config import get_logger

logger = get_logger(__name__)


def require_api_key(x_api_key: str | None = Header(default=None, alias="X-API-Key")) -> None:
    """FastAPI dependency guarding write endpoints.

    The service is expected to listen on localhost or a private network, but the
    key stops an accidentally exposed port from becoming a way to poison the
    index. Comparison is constant-time so the key cannot be probed byte by byte.
    """
    settings: Settings = get_settings()

    if not settings.require_api_key:
        return

    if not settings.api_key:
        # Fail closed: enforcement is on but no key is configured, so no caller
        # could ever present a valid one. Better to reject than to silently open up.
        logger.error("REQUIRE_API_KEY is true but API_KEY is empty; rejecting request")
        raise UnauthorizedError("Service API key is not configured")

    if not x_api_key or not secrets.compare_digest(x_api_key, settings.api_key):
        raise UnauthorizedError("Invalid or missing X-API-Key header")
