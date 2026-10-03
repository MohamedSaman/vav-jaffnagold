"""Shared FastAPI dependencies.

The engine is a process-level singleton created during startup and stashed on
``app.state``; routers reach it through :func:`get_engine`.
"""

from __future__ import annotations

from fastapi import Request

from .exceptions import ModelNotReadyError
from .services.engine import SearchEngine


def get_engine(request: Request) -> SearchEngine:
    engine: SearchEngine | None = getattr(request.app.state, "engine", None)

    if engine is None or not engine.embedder.ready:
        raise ModelNotReadyError("The service is still starting up; retry in a moment")

    return engine
