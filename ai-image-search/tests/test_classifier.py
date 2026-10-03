"""Jewelry type detection.

The category-name mapping is pure logic and always tested. The CLIP-based visual
classification is exercised only when torch is installed.
"""

from __future__ import annotations

import numpy as np
import pytest

from app.services.classifier import JEWELRY_PROMPTS, JewelryClassifier
from tests.conftest import requires_torch

type_from_category = JewelryClassifier.type_from_category


@pytest.mark.parametrize(
    ("category_name", "expected"),
    [
        ("Rings", "ring"),
        ("Gold Ring", "ring"),
        ("Wedding Bands", None),  # 'band' is not in the vocabulary
        ("Necklaces", "necklace"),
        ("Bridal Necklace Set", "necklace"),
        ("Gold Chains", "chain"),
        ("Pendants", "pendant"),
        ("Lockets", "pendant"),
        ("Earrings", "earrings"),
        ("Ear Studs", "earrings"),
        ("Bangles", "bracelet"),
        ("Bracelets", "bracelet"),
        ("Anklets", "anklet"),
        ("Nose Pins", "nose_pin"),
        ("Watches", "watch"),
        ("Gold Coins", "coin"),
        ("Gift Vouchers", None),
        ("", None),
        (None, None),
    ],
)
def test_category_name_maps_to_type(category_name: str | None, expected: str | None) -> None:
    assert type_from_category(category_name) == expected


def test_matching_is_case_and_whitespace_insensitive() -> None:
    assert type_from_category("  GOLD NECKLACE  ") == "necklace"


def test_specific_terms_win_over_generic_ones() -> None:
    """'nose ring' must classify as a nose pin, not a finger ring."""
    assert type_from_category("Nose Ring") == "nose_pin"
    assert type_from_category("Ear Rings") == "earrings"


def test_chain_wins_over_necklace() -> None:
    """A 'chain necklace' is a chain — the more specific product form."""
    assert type_from_category("Chain Necklace") == "chain"


def test_tamil_terms_are_recognised() -> None:
    """Local category names are common in this shop's data."""
    assert type_from_category("Valayal") == "bracelet"
    assert type_from_category("Mookuthi") == "nose_pin"
    assert type_from_category("Kolusu") == "anklet"


def test_classify_returns_unknown_before_build() -> None:
    """Classification must degrade gracefully, not crash, if centroids are missing."""
    classifier = JewelryClassifier.__new__(JewelryClassifier)
    classifier._centroids = None
    classifier._types = list(JEWELRY_PROMPTS)

    jewelry_type, confidence, scores = classifier.classify(np.zeros(512, dtype=np.float32))

    assert jewelry_type == "unknown"
    assert confidence == 0.0
    assert scores == {}


def test_classify_with_synthetic_centroids() -> None:
    """Verify the softmax picks the nearest centroid without loading CLIP."""
    classifier = JewelryClassifier.__new__(JewelryClassifier)
    classifier._types = ["ring", "necklace", "chain"]
    classifier._centroids = np.eye(3, dtype=np.float32)

    jewelry_type, confidence, scores = classifier.classify(np.array([0, 1, 0], dtype=np.float32))

    assert jewelry_type == "necklace"
    assert confidence > 0.9
    assert set(scores) == {"ring", "necklace", "chain"}
    assert sum(scores.values()) == pytest.approx(1.0, abs=1e-3)


def test_resolve_product_type_prefers_category_over_vision() -> None:
    """A curated shop label beats a zero-shot guess."""
    classifier = JewelryClassifier.__new__(JewelryClassifier)
    classifier._types = ["ring", "necklace"]
    # Centroids that would strongly predict 'ring' if consulted.
    classifier._centroids = np.eye(2, dtype=np.float32)

    resolved = classifier.resolve_product_type(
        np.array([1, 0], dtype=np.float32), category_name="Gold Necklaces", product_name=None
    )

    assert resolved == "necklace"


def test_resolve_product_type_falls_back_to_product_name() -> None:
    classifier = JewelryClassifier.__new__(JewelryClassifier)
    classifier._types = ["ring", "necklace"]
    classifier._centroids = np.eye(2, dtype=np.float32)

    resolved = classifier.resolve_product_type(
        np.array([1, 0], dtype=np.float32),
        category_name="Uncategorised",
        product_name="22K Bridal Necklace",
    )

    assert resolved == "necklace"


def test_resolve_product_type_falls_back_to_vision() -> None:
    classifier = JewelryClassifier.__new__(JewelryClassifier)
    classifier._types = ["ring", "necklace"]
    classifier._centroids = np.eye(2, dtype=np.float32)

    resolved = classifier.resolve_product_type(
        np.array([1, 0], dtype=np.float32), category_name="Misc", product_name="Item 4821"
    )

    assert resolved == "ring"


@requires_torch
def test_real_centroids_are_unit_length(settings) -> None:
    """Integration check: builds real CLIP text centroids (downloads the model)."""
    from app.services.embedder import ClipEmbedder

    embedder = ClipEmbedder(settings)
    embedder.load()

    classifier = JewelryClassifier(embedder)
    classifier.build()

    assert classifier.ready
    norms = np.linalg.norm(classifier._centroids, axis=1)
    assert np.allclose(norms, 1.0, atol=1e-4)
