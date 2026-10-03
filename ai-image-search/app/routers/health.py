"""Liveness and readiness endpoints.

Deliberately unauthenticated so a load balancer or uptime monitor can poll them.
They expose no product data.
"""

from __future__ import annotations

from fastapi import APIRouter, Request, Response, status

from ..config import get_settings
from ..schemas import HealthResponse

router = APIRouter(tags=["health"])

VERSION = "1.0.0"


@router.get("/health", response_model=HealthResponse, summary="Service health")
def health(request: Request, response: Response) -> HealthResponse:
    """Report model and index state.

    Returns 503 with ``status: degraded`` while the model is still loading, so an
    orchestrator does not route traffic to a process that cannot answer yet.
    """
    settings = get_settings()
    engine = getattr(request.app.state, "engine", None)
    model_ready = bool(engine and engine.embedder.ready)

    if not model_ready:
        response.status_code = status.HTTP_503_SERVICE_UNAVAILABLE

    return HealthResponse(
        status="ok" if model_ready else "degraded",
        service=settings.app_name,
        version=VERSION,
        environment=settings.app_env,
        model_ready=model_ready,
        clip_model=settings.clip_model_name,
        device=engine.embedder.device if engine else "unknown",
        embedding_dim=engine.index.dim if engine else settings.embedding_dim,
        total_indexed=engine.index.size if engine else 0,
        uptime_seconds=engine.uptime_seconds if engine else 0.0,
    )


@router.get("/ping", summary="Liveness probe")
def ping() -> dict[str, str]:
    """Cheapest possible check — the process is up and serving."""
    return {"status": "ok"}
