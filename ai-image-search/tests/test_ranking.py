"""Result ordering: the category and jewelry-type re-ranking rules.

No model is loaded — the engine is built with stub components so the ranking
logic can be tested in isolation. (torch is still needed for the import chain.)
"""

from __future__ import annotations

from pathlib import Path

import numpy as np
import pytest

from app.config import SearchStrategy, Settings
from tests.conftest import requires_faiss, requires_torch

pytestmark = [requires_torch, requires_faiss]


DIM = 8


def make_engine(tmp_path: Path, **overrides):
    """A real engine with a real FAISS index but a stubbed CLIP model."""
    from app.services.engine import SearchEngine

    settings = Settings(
        data_dir=tmp_path / "data",
        embedding_dim=DIM,
        require_api_key=False,
        min_similarity=0.0,
        default_top_k=5,
        candidate_multiplier=10,
        **overrides,
    )

    engine = SearchEngine(settings)

    # Stub the classifier so the "detected type" is deterministic.
    engine.classifier._types = ["ring", "necklace"]
    engine.classifier._centroids = np.eye(2, DIM, dtype=np.float32)

    return engine


def add_product(engine, product_id: int, vector: np.ndarray, jewelry_type: str, category_id=1):
    from app.services.metadata import ProductRecord

    normalised = vector / np.linalg.norm(vector)
    engine.index.upsert([product_id], normalised.reshape(1, -1).astype(np.float32))
    engine.metadata.upsert(
        ProductRecord(
            product_id=product_id,
            code=f"JG-{product_id}",
            name=f"Product {product_id}",
            category_id=category_id,
            category_name="Rings" if jewelry_type == "ring" else "Necklaces",
            jewelry_type=jewelry_type,
        )
    )


def basis(index: int) -> np.ndarray:
    vector = np.zeros(DIM, dtype=np.float32)
    vector[index] = 1.0
    return vector


@pytest.fixture
def engine(tmp_path: Path):
    engine = make_engine(tmp_path)
    yield engine
    engine.metadata.close()


def test_empty_index_returns_no_results(engine) -> None:
    result = engine._rank(
        basis(0),
        detected=None,
        top_k=None,
        category_id=None,
        min_similarity=None,
        strategy=None,
    )

    assert result["returned"] == 0
    assert result["results"] == []
    assert result["total_indexed"] == 0


def test_results_are_ordered_by_similarity(engine) -> None:
    query = basis(0)
    # Progressively less similar to the query.
    add_product(engine, 1, query, "ring")
    add_product(engine, 2, query + 0.5 * basis(1), "ring")
    add_product(engine, 3, query + 2.0 * basis(1), "ring")

    result = engine._rank(
        query, detected=None, top_k=None, category_id=None, min_similarity=None, strategy=None
    )

    assert [m.product_id for m in result["results"]] == [1, 2, 3]
    assert result["results"][0].similarity == pytest.approx(1.0, abs=1e-5)


def test_min_similarity_filters_weak_matches(engine) -> None:
    add_product(engine, 1, basis(0), "ring")
    add_product(engine, 2, basis(1), "ring")  # orthogonal, similarity 0

    result = engine._rank(
        basis(0),
        detected=None,
        top_k=None,
        category_id=None,
        min_similarity=0.5,
        strategy=None,
    )

    assert [m.product_id for m in result["results"]] == [1]


def test_top_k_limits_results(engine) -> None:
    for product_id in range(1, 11):
        add_product(engine, product_id, basis(0) + 0.01 * product_id * basis(1), "ring")

    result = engine._rank(
        basis(0), detected=None, top_k=3, category_id=None, min_similarity=None, strategy=None
    )

    assert result["returned"] == 3


def test_category_filter_is_a_hard_constraint(engine) -> None:
    """An explicit category from the UI must exclude everything else."""
    add_product(engine, 1, basis(0), "ring", category_id=1)
    add_product(engine, 2, basis(0), "necklace", category_id=2)

    result = engine._rank(
        basis(0), detected=None, top_k=None, category_id=2, min_similarity=None, strategy=None
    )

    assert [m.product_id for m in result["results"]] == [2]


def test_boost_promotes_same_type_on_a_near_tie(engine) -> None:
    from app.schemas import DetectedType

    query = basis(0)
    # The necklace is marginally closer visually, but the query is a ring.
    add_product(engine, 1, query + 0.30 * basis(2), "ring")
    add_product(engine, 2, query + 0.28 * basis(3), "necklace")

    detected = DetectedType(jewelry_type="ring", confidence=0.9, scores={})
    result = engine._rank(
        query,
        detected=detected,
        top_k=None,
        category_id=None,
        min_similarity=None,
        strategy=SearchStrategy.BOOST,
    )

    assert result["results"][0].product_id == 1
    assert result["results"][0].same_type is True


def test_boost_does_not_bury_a_strong_visual_match(engine) -> None:
    """A near-exact match must stay first even if the type guess disagrees.

    This is why 'boost' is the default over 'partition': a misclassified query
    should nudge the ranking, not hide the right product.
    """
    from app.schemas import DetectedType

    query = basis(0)
    add_product(engine, 1, query, "necklace")  # exact match, wrong type
    add_product(engine, 2, query + 2.0 * basis(1), "ring")  # weak match, right type

    detected = DetectedType(jewelry_type="ring", confidence=0.95, scores={})
    result = engine._rank(
        query,
        detected=detected,
        top_k=None,
        category_id=None,
        min_similarity=None,
        strategy=SearchStrategy.BOOST,
    )

    assert result["results"][0].product_id == 1


def test_partition_lists_same_type_first(engine) -> None:
    """Partition mode honours the type even at the cost of visual similarity."""
    from app.schemas import DetectedType

    query = basis(0)
    add_product(engine, 1, query, "necklace")  # exact match, wrong type
    add_product(engine, 2, query + 2.0 * basis(1), "ring")  # weak match, right type

    detected = DetectedType(jewelry_type="ring", confidence=0.95, scores={})
    result = engine._rank(
        query,
        detected=detected,
        top_k=None,
        category_id=None,
        min_similarity=None,
        strategy=SearchStrategy.PARTITION,
    )

    assert [m.product_id for m in result["results"]] == [2, 1]


def test_strict_drops_other_types_when_enough_remain(tmp_path: Path) -> None:
    from app.schemas import DetectedType

    engine = make_engine(tmp_path, strict_min_results=2)
    try:
        query = basis(0)
        add_product(engine, 1, query, "necklace")
        for product_id in (2, 3, 4):
            add_product(engine, product_id, query + 0.1 * product_id * basis(1), "ring")

        detected = DetectedType(jewelry_type="ring", confidence=0.95, scores={})
        result = engine._rank(
            query,
            detected=detected,
            top_k=None,
            category_id=None,
            min_similarity=None,
            strategy=SearchStrategy.STRICT,
        )

        assert all(m.jewelry_type == "ring" for m in result["results"])
        assert 1 not in [m.product_id for m in result["results"]]
    finally:
        engine.metadata.close()


def test_strict_falls_back_when_too_few_same_type(tmp_path: Path) -> None:
    """A customer photographing an unusual piece must still get results."""
    from app.schemas import DetectedType

    engine = make_engine(tmp_path, strict_min_results=5)
    try:
        query = basis(0)
        add_product(engine, 1, query, "necklace")
        add_product(engine, 2, query + 0.2 * basis(1), "ring")

        detected = DetectedType(jewelry_type="ring", confidence=0.95, scores={})
        result = engine._rank(
            query,
            detected=detected,
            top_k=None,
            category_id=None,
            min_similarity=None,
            strategy=SearchStrategy.STRICT,
        )

        assert result["returned"] == 2
    finally:
        engine.metadata.close()


def test_low_confidence_type_is_ignored(engine) -> None:
    """Below the confidence threshold, the type must not influence ranking."""
    from app.schemas import DetectedType

    query = basis(0)
    add_product(engine, 1, query, "necklace")
    add_product(engine, 2, query + 0.05 * basis(1), "ring")

    detected = DetectedType(jewelry_type="ring", confidence=0.05, scores={})
    result = engine._rank(
        query,
        detected=detected,
        top_k=None,
        category_id=None,
        min_similarity=None,
        strategy=SearchStrategy.BOOST,
    )

    assert result["results"][0].product_id == 1
    assert all(m.same_type is False for m in result["results"])


def test_response_carries_metadata_for_laravel(engine) -> None:
    add_product(engine, 77, basis(0), "ring", category_id=3)

    result = engine._rank(
        basis(0), detected=None, top_k=None, category_id=None, min_similarity=None, strategy=None
    )
    match = result["results"][0]

    assert match.code == "JG-77"
    assert match.name == "Product 77"
    assert match.category_id == 3
    assert match.jewelry_type == "ring"
    assert result["query_id"]
    assert result["took_ms"] >= 0
