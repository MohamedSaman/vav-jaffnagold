"""Zero-shot jewelry type detection.

CLIP embeds images and text into one shared space, which means we can classify a
photo without training anything: embed a set of text prompts per jewelry type,
average them into a centroid, and pick the closest centroid to the image.

The centroids are computed once at startup (a few hundred short text forward
passes) and then every classification is a single 6x512 matrix multiply —
effectively free.

Type information is used twice:
  * on the query image, to work out what the customer is looking for;
  * on each product at index time, so candidates can be compared by type.
For products we prefer the shop's own category name when it maps cleanly to a
type, because a human-curated label beats a model guess.
"""

from __future__ import annotations

import threading

import numpy as np

from ..logging_config import get_logger
from .embedder import ClipEmbedder

logger = get_logger(__name__)

UNKNOWN_TYPE = "unknown"

# Multiple phrasings per type: averaging prompts is the standard trick for
# making zero-shot CLIP classification markedly more stable than a single label.
JEWELRY_PROMPTS: dict[str, list[str]] = {
    "ring": [
        "a photo of a gold ring",
        "a jewelry ring worn on a finger",
        "a close-up of a diamond engagement ring",
        "a gold wedding band",
        "a product photo of a ring",
    ],
    "necklace": [
        "a photo of a gold necklace",
        "a jewelry necklace worn around the neck",
        "a beaded necklace on a display bust",
        "a traditional gold bridal necklace set",
        "a product photo of a necklace",
    ],
    "chain": [
        "a photo of a thin gold chain",
        "a gold link chain necklace",
        "a plain metal chain without a pendant",
        "a rope chain jewelry piece",
        "a product photo of a gold chain",
    ],
    "pendant": [
        "a photo of a gold pendant",
        "a pendant charm hanging on a chain",
        "a small decorative jewelry pendant",
        "a locket pendant",
        "a product photo of a pendant",
    ],
    "earrings": [
        "a photo of a pair of gold earrings",
        "jewelry earrings, studs",
        "dangling drop earrings",
        "a pair of hoop earrings",
        "a product photo of earrings",
    ],
    "bracelet": [
        "a photo of a gold bracelet",
        "a jewelry bracelet worn on the wrist",
        "a gold bangle",
        "a charm bracelet",
        "a product photo of a bracelet",
    ],
    "anklet": [
        "a photo of a gold anklet",
        "an ankle chain jewelry piece",
        "a product photo of an anklet",
    ],
    "nose_pin": [
        "a photo of a gold nose pin",
        "a small nose stud jewelry piece",
        "a nose ring",
    ],
    "watch": [
        "a photo of a wrist watch",
        "a gold wristwatch with a metal strap",
        "a product photo of a watch",
    ],
    "coin": [
        "a photo of a gold coin",
        "a gold bullion bar",
        "an investment gold coin",
    ],
}

# Shop category names are free text, so match on keywords. Ordering matters:
# more specific terms must be checked before the generic ones they contain
# (e.g. "nose" before "ring", "chain" before "necklace").
CATEGORY_KEYWORDS: list[tuple[tuple[str, ...], str]] = [
    (("nose pin", "nose-pin", "nosepin", "nose stud", "nose ring", "mookuthi"), "nose_pin"),
    (("earring", "ear ring", "ear-ring", "stud", "jhumka", "thodu"), "earrings"),
    (("bangle", "bracelet", "kada", "valayal"), "bracelet"),
    (("anklet", "ankle", "payal", "kolusu"), "anklet"),
    (("pendant", "locket", "charm"), "pendant"),
    (("chain",), "chain"),
    (("necklace", "haram", "thali", "mala", "choker", "collar"), "necklace"),
    (("watch", "timepiece"), "watch"),
    (("coin", "bullion", "bar", "biscuit"), "coin"),
    (("ring",), "ring"),
]


class JewelryClassifier:
    def __init__(self, embedder: ClipEmbedder) -> None:
        self._embedder = embedder
        self._types: list[str] = list(JEWELRY_PROMPTS.keys())
        self._centroids: np.ndarray | None = None
        self._lock = threading.Lock()

    # ----------------------------------------------------------------- lifecycle
    def build(self) -> None:
        """Compute one normalised centroid per jewelry type. Call after model load."""
        with self._lock:
            if self._centroids is not None:
                return

            centroids: list[np.ndarray] = []
            for jewelry_type in self._types:
                prompts = JEWELRY_PROMPTS[jewelry_type]
                embeddings = self._embedder.embed_texts(prompts)
                centroid = embeddings.mean(axis=0)
                # Re-normalise: the mean of unit vectors is not itself a unit vector.
                centroid /= max(float(np.linalg.norm(centroid)), 1e-12)
                centroids.append(centroid.astype(np.float32))

            self._centroids = np.vstack(centroids)
            logger.info("Jewelry type centroids built", extra={"types": len(self._types)})

    @property
    def ready(self) -> bool:
        return self._centroids is not None

    @property
    def types(self) -> list[str]:
        return list(self._types)

    # ------------------------------------------------------------- classification
    def classify(self, image_embedding: np.ndarray) -> tuple[str, float, dict[str, float]]:
        """Return ``(type, confidence, all_scores)`` for one image embedding.

        Confidence is a softmax over the cosine similarities, scaled by CLIP's
        learned temperature (100). It is a *relative* measure: high confidence
        means "much closer to this type than the others", not "certainly correct".
        """
        if self._centroids is None:
            return UNKNOWN_TYPE, 0.0, {}

        vector = np.asarray(image_embedding, dtype=np.float32).reshape(-1)
        similarities = self._centroids @ vector

        logits = similarities * 100.0
        logits -= logits.max()  # subtract the max for numerical stability
        exponentials = np.exp(logits)
        probabilities = exponentials / exponentials.sum()

        best = int(np.argmax(probabilities))
        scores = {
            self._types[i]: round(float(probabilities[i]), 4) for i in range(len(self._types))
        }

        return self._types[best], round(float(probabilities[best]), 4), scores

    # ------------------------------------------------------------------- mapping
    @staticmethod
    def type_from_category(category_name: str | None) -> str | None:
        """Derive a jewelry type from a shop category name, if one matches."""
        if not category_name:
            return None

        haystack = category_name.strip().lower()
        if not haystack:
            return None

        for keywords, jewelry_type in CATEGORY_KEYWORDS:
            if any(keyword in haystack for keyword in keywords):
                return jewelry_type

        return None

    def resolve_product_type(
        self,
        image_embedding: np.ndarray,
        category_name: str | None = None,
        product_name: str | None = None,
    ) -> str:
        """Best-effort type for a product being indexed.

        Preference order: shop category name, then product name, then the visual
        model. Curated text labels are more reliable than a zero-shot guess, and
        they cost nothing to check.
        """
        from_category = self.type_from_category(category_name)
        if from_category:
            return from_category

        from_name = self.type_from_category(product_name)
        if from_name:
            return from_name

        jewelry_type, _confidence, _scores = self.classify(image_embedding)
        return jewelry_type
