"""FastAPI application factory.

The CLIP model and the FAISS index are loaded once inside the lifespan handler,
before the first request is accepted, and reused for the life of the process.
"""

from __future__ import annotations

import os
import time
from contextlib import asynccontextmanager
from typing import AsyncIterator

from fastapi import FastAPI, Request
from fastapi.encoders import jsonable_encoder
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

from .config import Settings, get_settings
from .exceptions import ImageSearchError
from .logging_config import configure_logging, get_logger
from .routers import health, index, search
from .routers.health import VERSION
from .schemas import ErrorResponse
from .services.engine import SearchEngine

logger = get_logger(__name__)

DESCRIPTION = """
AI-powered visual product search for the Jaffna Gold storefront.

Customers upload a photo they found on social media; this service returns the
most visually similar products from the shop catalogue.

**How it works**

1. OpenAI CLIP (`clip-vit-base-patch32`) turns each product photo into a
   512-dimensional embedding, normalised to unit length.
2. The embeddings live in a FAISS `IndexIDMap2(IndexFlatIP)`, where an inner
   product *is* cosine similarity, and vectors are addressed by MySQL product id.
3. An uploaded image is embedded the same way, and the nearest neighbours are
   re-ranked using a zero-shot jewelry-type match so a ring query prefers rings.

**Authentication** — the `/api/v1/index/*` routes require the `X-API-Key` header.
Search and health routes are open, on the assumption the service is not exposed
to the public internet.
"""


@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncIterator[None]:
    settings: Settings = get_settings()
    configure_logging(settings.log_level, settings.log_json)

    if settings.hf_home:
        # Point the Hugging Face cache somewhere writable and persistent, so the
        # model is downloaded once rather than on every container start.
        os.environ.setdefault("HF_HOME", str(settings.hf_home))

    logger.info(
        "Starting service",
        extra={"service": settings.app_name, "version": VERSION, "env": settings.app_env},
    )

    engine = SearchEngine(settings)
    started = time.perf_counter()

    try:
        engine.startup()
    except Exception:
        # Keep the process up so /health can report the failure and a supervisor
        # can surface it, rather than crash-looping silently.
        logger.exception("Engine startup failed; service will report as degraded")
        app.state.engine = engine
        yield
        return

    app.state.engine = engine
    logger.info(
        "Startup complete", extra={"seconds": round(time.perf_counter() - started, 2)}
    )

    try:
        yield
    finally:
        logger.info("Shutting down; persisting index")
        engine.shutdown()


def create_app() -> FastAPI:
    settings = get_settings()
    configure_logging(settings.log_level, settings.log_json)

    app = FastAPI(
        title=settings.app_name,
        description=DESCRIPTION,
        version=VERSION,
        lifespan=lifespan,
        docs_url="/docs" if settings.docs_enabled else None,
        redoc_url="/redoc" if settings.docs_enabled else None,
        openapi_url="/openapi.json" if settings.docs_enabled else None,
    )

    if settings.cors_origins:
        app.add_middleware(
            CORSMiddleware,
            allow_origins=settings.cors_origins,
            allow_credentials=False,
            allow_methods=["GET", "POST"],
            allow_headers=["*"],
        )

    # ------------------------------------------------------------ error handlers
    @app.exception_handler(ImageSearchError)
    async def handle_domain_error(_: Request, exc: ImageSearchError) -> JSONResponse:
        """Map our exception hierarchy onto proper HTTP status codes."""
        if exc.status_code >= 500:
            logger.exception("Unhandled service error", extra={"error_code": exc.error_code})
        else:
            logger.warning(
                "Request rejected",
                # 'message' is a reserved LogRecord attribute name and cannot be
                # used as an extra= key — the stdlib raises KeyError on it.
                extra={"error_code": exc.error_code, "detail": exc.message},
            )

        return JSONResponse(
            status_code=exc.status_code,
            content=ErrorResponse(
                error=exc.error_code, message=exc.message, details=exc.details
            ).model_dump(),
        )

    @app.exception_handler(RequestValidationError)
    async def handle_validation_error(
        _: Request, exc: RequestValidationError
    ) -> JSONResponse:
        # A ValueError raised inside a @model_validator ends up in errors()'s
        # ctx as the raw exception object, which pydantic's own strict
        # model_dump(mode="json") refuses to serialise. jsonable_encoder is
        # built to fall back to str() for exactly this case.
        return JSONResponse(
            status_code=422,
            content=ErrorResponse(
                error="validation_failed",
                message="The request payload is invalid",
                details={"errors": jsonable_encoder(exc.errors())},
            ).model_dump(),
        )

    @app.exception_handler(Exception)
    async def handle_unexpected_error(_: Request, exc: Exception) -> JSONResponse:
        """Never leak a stack trace to the caller; log it and return a generic 500."""
        logger.exception("Unexpected error", extra={"error_type": type(exc).__name__})

        return JSONResponse(
            status_code=500,
            content=ErrorResponse(
                error="internal_error", message="An unexpected error occurred"
            ).model_dump(),
        )

    # ------------------------------------------------------------------- routers
    app.include_router(health.router)
    app.include_router(search.router)
    app.include_router(index.router)

    @app.get("/", include_in_schema=False)
    def root() -> dict[str, str]:
        return {
            "service": settings.app_name,
            "version": VERSION,
            "docs": "/docs" if settings.docs_enabled else "disabled",
        }

    return app


app = create_app()


if __name__ == "__main__":  # pragma: no cover
    import uvicorn

    _settings = get_settings()
    # Single worker on purpose: each worker would load its own copy of the model
    # and the index. Scale with threads, or run replicas behind a load balancer.
    uvicorn.run(
        "app.main:app",
        host=_settings.host,
        port=_settings.port,
        workers=1,
        log_config=None,
    )
