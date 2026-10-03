"""HTTP contract tests.

The engine is replaced with a stub on ``app.state``, so these run fast and assert
status codes, validation and auth rather than model quality.
"""

from __future__ import annotations

from pathlib import Path

import pytest

from tests.conftest import requires_faiss, requires_torch

pytestmark = [requires_torch, requires_faiss]


@pytest.fixture
def client(tmp_path: Path, monkeypatch):
    from fastapi.testclient import TestClient

    from app import config as config_module
    from app.main import create_app
    from app.services.engine import SearchEngine

    # get_settings() is `@lru_cache`d and imported by name into half a dozen
    # modules (security, health, search, index, main) — patching one module's
    # copy of the name leaves the others reading real values from disk. Driving
    # it through real env vars + a cache clear means every call site, including
    # FastAPI's `Depends(get_settings)`, resolves to the exact same instance.
    monkeypatch.setenv("DATA_DIR", str(tmp_path / "data"))
    monkeypatch.setenv("EMBEDDING_DIM", "8")
    monkeypatch.setenv("API_KEY", "test-secret")
    monkeypatch.setenv("REQUIRE_API_KEY", "true")
    monkeypatch.setenv("WARMUP_ON_START", "false")
    config_module.get_settings.cache_clear()
    settings = config_module.get_settings()

    app = create_app()
    engine = SearchEngine(settings)

    # Pretend the model is loaded; no real inference happens in these tests.
    engine.embedder._model = object()
    engine.embedder._processor = object()

    app.dependency_overrides[__import__("app.dependencies", fromlist=["get_engine"]).get_engine] = (
        lambda: engine
    )

    with TestClient(app) as test_client:
        test_client.app.state.engine = engine
        yield test_client

    engine.metadata.close()
    # Prevent this test's settings from leaking into whatever runs next via the
    # shared lru_cache singleton (monkeypatch only reverts the env vars).
    config_module.get_settings.cache_clear()


# ------------------------------------------------------------------ health
def test_ping(client) -> None:
    response = client.get("/ping")

    assert response.status_code == 200
    assert response.json() == {"status": "ok"}


def test_root_lists_service_metadata(client) -> None:
    response = client.get("/")

    assert response.status_code == 200
    assert "version" in response.json()


def test_openapi_schema_is_published(client) -> None:
    response = client.get("/openapi.json")

    assert response.status_code == 200
    paths = response.json()["paths"]
    assert "/api/v1/search" in paths
    assert "/api/v1/index/rebuild" in paths


# -------------------------------------------------------------------- auth
def test_index_write_requires_api_key(client) -> None:
    response = client.post(
        "/api/v1/index/products",
        json={"product_id": 1, "image": "images/x.png"},
    )

    assert response.status_code == 401
    assert response.json()["error"] == "unauthorized"


def test_index_write_rejects_wrong_api_key(client) -> None:
    response = client.post(
        "/api/v1/index/products",
        json={"product_id": 1, "image": "images/x.png"},
        headers={"X-API-Key": "wrong"},
    )

    assert response.status_code == 401


def test_index_status_accepts_valid_api_key(client) -> None:
    response = client.get("/api/v1/index/status", headers={"X-API-Key": "test-secret"})

    assert response.status_code == 200
    body = response.json()
    assert body["total_indexed"] == 0
    assert body["rebuild"]["state"] == "idle"


# -------------------------------------------------------------- validation
def test_search_without_a_file_is_rejected(client) -> None:
    response = client.post("/api/v1/search")

    assert response.status_code == 422
    assert response.json()["error"] == "validation_failed"


def test_search_rejects_a_non_image_upload(client) -> None:
    response = client.post(
        "/api/v1/search",
        files={"image": ("shell.png", b"<?php echo 1; ?>", "image/png")},
    )

    assert response.status_code == 422
    assert response.json()["error"] == "invalid_image"


def test_search_rejects_an_oversized_upload(client, monkeypatch) -> None:
    from app import config as config_module

    settings = config_module.get_settings()
    monkeypatch.setattr(settings, "max_upload_bytes", 1024)

    response = client.post(
        "/api/v1/search",
        files={"image": ("big.png", b"\x00" * 8192, "image/png")},
    )

    assert response.status_code == 413
    assert response.json()["error"] == "payload_too_large"


def test_index_payload_requires_an_image_source(client) -> None:
    response = client.post(
        "/api/v1/index/products",
        json={"product_id": 1},
        headers={"X-API-Key": "test-secret"},
    )

    assert response.status_code == 422


def test_index_payload_rejects_a_bad_product_id(client) -> None:
    response = client.post(
        "/api/v1/index/products",
        json={"product_id": 0, "image": "images/x.png"},
        headers={"X-API-Key": "test-secret"},
    )

    assert response.status_code == 422


def test_text_search_rejects_a_too_short_query(client) -> None:
    response = client.post("/api/v1/search/text", json={"query": "a"})

    assert response.status_code == 422


# ------------------------------------------------------------------- index
def test_deleting_an_unindexed_product_returns_404(client) -> None:
    response = client.delete(
        "/api/v1/index/products/12345", headers={"X-API-Key": "test-secret"}
    )

    assert response.status_code == 404
    assert response.json()["error"] == "product_not_indexed"


def test_indexing_reports_a_missing_image_as_failed(client) -> None:
    """A missing file is a per-item failure, not a 500 for the whole request."""
    response = client.post(
        "/api/v1/index/products",
        json={"product_id": 1, "image": "images/missing.png"},
        headers={"X-API-Key": "test-secret"},
    )

    assert response.status_code == 201
    item = response.json()["items"][0]
    assert item["status"] == "failed"
    assert item["product_id"] == 1


def test_bulk_index_enforces_a_batch_ceiling(client) -> None:
    payload = {
        "products": [{"product_id": i, "image": f"images/{i}.png"} for i in range(1, 502)]
    }

    response = client.post(
        "/api/v1/index/products/bulk", json=payload, headers={"X-API-Key": "test-secret"}
    )

    assert response.status_code == 422


def test_clear_index_is_authenticated_and_reports_count(client) -> None:
    response = client.delete("/api/v1/index/products", headers={"X-API-Key": "test-secret"})

    assert response.status_code == 200
    assert response.json()["total_indexed"] == 0


def test_list_types_exposes_the_vocabulary(client) -> None:
    response = client.get("/api/v1/search/types")

    assert response.status_code == 200
    assert "ring" in response.json()["types"]
