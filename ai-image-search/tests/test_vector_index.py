"""FAISS wrapper behaviour, verified with synthetic unit vectors."""

from __future__ import annotations

from pathlib import Path

import numpy as np
import pytest

from app.services.vector_index import VectorIndex
from tests.conftest import requires_faiss

pytestmark = requires_faiss

DIM = 32


def unit_vectors(count: int, seed: int = 0) -> np.ndarray:
    """Random L2-normalised rows — the same shape of data CLIP produces."""
    rng = np.random.default_rng(seed)
    matrix = rng.standard_normal((count, DIM)).astype(np.float32)
    matrix /= np.linalg.norm(matrix, axis=1, keepdims=True)
    return matrix


@pytest.fixture
def index(tmp_path: Path) -> VectorIndex:
    return VectorIndex(dim=DIM, path=tmp_path / "test.faiss")


def test_starts_empty(index: VectorIndex) -> None:
    assert index.size == 0
    assert index.search(unit_vectors(1)[0], 5)[0].size == 0


def test_upsert_and_search_finds_exact_match(index: VectorIndex) -> None:
    vectors = unit_vectors(10)
    index.upsert(list(range(100, 110)), vectors)

    scores, ids = index.search(vectors[3], 5)

    assert index.size == 10
    assert ids[0] == 103
    # Cosine similarity of a unit vector with itself is 1.
    assert scores[0] == pytest.approx(1.0, abs=1e-5)


def test_upsert_is_idempotent(index: VectorIndex) -> None:
    """Re-indexing the same product must replace, not duplicate."""
    vectors = unit_vectors(3)
    index.upsert([1, 2, 3], vectors)
    index.upsert([1, 2, 3], vectors)

    assert index.size == 3


def test_upsert_replaces_the_vector(index: VectorIndex) -> None:
    original = unit_vectors(1, seed=1)
    replacement = unit_vectors(1, seed=2)

    index.upsert([7], original)
    index.upsert([7], replacement)

    scores, ids = index.search(replacement[0], 1)

    assert index.size == 1
    assert ids[0] == 7
    assert scores[0] == pytest.approx(1.0, abs=1e-5)


def test_remove(index: VectorIndex) -> None:
    index.upsert([1, 2, 3], unit_vectors(3))

    assert index.remove([2]) == 1
    assert index.size == 2
    assert 2 not in index.search(unit_vectors(1)[0], 3)[1]


def test_remove_unknown_id_is_safe(index: VectorIndex) -> None:
    index.upsert([1], unit_vectors(1))

    assert index.remove([999]) == 0
    assert index.size == 1


def test_contains(index: VectorIndex) -> None:
    index.upsert([5], unit_vectors(1))

    assert index.contains(5) is True
    assert index.contains(6) is False


def test_reset(index: VectorIndex) -> None:
    index.upsert([1, 2], unit_vectors(2))
    index.reset()

    assert index.size == 0


def test_search_k_is_clamped_to_index_size(index: VectorIndex) -> None:
    """Asking for more neighbours than exist must not return -1 padding."""
    index.upsert([1, 2], unit_vectors(2))

    _scores, ids = index.search(unit_vectors(1)[0], 50)

    assert len(ids) == 2
    assert -1 not in ids


def test_dimension_mismatch_raises(index: VectorIndex) -> None:
    with pytest.raises(ValueError):
        index.upsert([1], np.zeros((1, DIM + 5), dtype=np.float32))


def test_id_count_mismatch_raises(index: VectorIndex) -> None:
    with pytest.raises(ValueError):
        index.upsert([1, 2], unit_vectors(3))


def test_save_and_load_roundtrip(tmp_path: Path) -> None:
    path = tmp_path / "persist.faiss"
    vectors = unit_vectors(20)

    writer = VectorIndex(dim=DIM, path=path)
    writer.upsert(list(range(20)), vectors)
    assert writer.save() is True

    reader = VectorIndex(dim=DIM, path=path)
    reader.load()

    assert reader.size == 20
    _scores, ids = reader.search(vectors[11], 1)
    assert ids[0] == 11


def test_save_is_a_noop_when_unchanged(index: VectorIndex) -> None:
    index.upsert([1], unit_vectors(1))
    index.save()

    assert index.save() is False
    assert index.save(force=True) is True


def test_load_ignores_index_with_wrong_dimension(tmp_path: Path) -> None:
    """A stale index from a different model must not be trusted."""
    path = tmp_path / "wrong-dim.faiss"
    writer = VectorIndex(dim=DIM, path=path)
    writer.upsert([1], unit_vectors(1))
    writer.save()

    reader = VectorIndex(dim=DIM * 2, path=path)
    reader.load()

    assert reader.size == 0


def test_ranking_is_by_descending_similarity(index: VectorIndex) -> None:
    index.upsert(list(range(50)), unit_vectors(50))

    scores, _ids = index.search(unit_vectors(1, seed=99)[0], 10)

    assert list(scores) == sorted(scores, reverse=True)
