"""CLIP embedding service.

The model is loaded exactly once, during FastAPI startup, and reused for every
request. Loading it lazily per-request would add ~2 s and hundreds of MB of
allocations to each call.
"""

from __future__ import annotations

import threading
import time
from typing import Sequence

import numpy as np
import torch
from PIL import Image
from transformers import CLIPModel, CLIPProcessor

from ..config import Settings
from ..exceptions import ModelNotReadyError
from ..logging_config import get_logger

logger = get_logger(__name__)


class ClipEmbedder:
    """Thin, thread-safe wrapper around ``openai/clip-vit-base-patch32``."""

    def __init__(self, settings: Settings) -> None:
        self._settings = settings
        self._model: CLIPModel | None = None
        self._processor: CLIPProcessor | None = None
        self._device = "cpu"
        self._dim = settings.embedding_dim
        # PyTorch modules are not guaranteed re-entrant across threads; FastAPI
        # runs sync work in a thread pool, so serialise the forward passes.
        self._lock = threading.Lock()

    # ------------------------------------------------------------------ lifecycle
    def load(self) -> None:
        """Download (first run) and materialise the model. Called once at startup."""
        if self._model is not None:
            return

        started = time.perf_counter()
        self._device = self._settings.resolve_device()

        if self._settings.torch_threads > 0:
            torch.set_num_threads(self._settings.torch_threads)

        logger.info(
            "Loading CLIP model",
            extra={"model": self._settings.clip_model_name, "device": self._device},
        )

        model = CLIPModel.from_pretrained(self._settings.clip_model_name)
        model.eval()
        model.to(self._device)

        self._model = model
        self._processor = CLIPProcessor.from_pretrained(self._settings.clip_model_name)
        self._dim = int(model.config.projection_dim)

        if self._dim != self._settings.embedding_dim:
            logger.warning(
                "Configured EMBEDDING_DIM does not match the model; using the model value",
                extra={"configured": self._settings.embedding_dim, "actual": self._dim},
            )

        logger.info(
            "CLIP model ready",
            extra={
                "device": self._device,
                "embedding_dim": self._dim,
                "load_seconds": round(time.perf_counter() - started, 2),
            },
        )

        if self._settings.warmup_on_start:
            self._warmup()

    def _warmup(self) -> None:
        """Run one dummy forward pass so the first real request is not the slow one."""
        try:
            blank = Image.new("RGB", (224, 224), color=(200, 170, 90))
            self.embed_images([blank])
            self.embed_texts(["a gold ring"])
            logger.info("Warmup pass complete")
        except Exception:  # pragma: no cover - warmup must never block startup
            logger.exception("Warmup pass failed; continuing anyway")

    @property
    def ready(self) -> bool:
        return self._model is not None

    @property
    def dim(self) -> int:
        return self._dim

    @property
    def device(self) -> str:
        return self._device

    def _require_ready(self) -> tuple[CLIPModel, CLIPProcessor]:
        if self._model is None or self._processor is None:
            raise ModelNotReadyError("The CLIP model is still loading; retry shortly")
        return self._model, self._processor

    # ----------------------------------------------------------------- embedding
    def embed_images(self, images: Sequence[Image.Image]) -> np.ndarray:
        """Return L2-normalised float32 embeddings, shape ``(len(images), dim)``.

        Normalising here means a FAISS inner-product search *is* cosine
        similarity, so no per-query division is needed at search time.
        """
        if not images:
            return np.zeros((0, self._dim), dtype=np.float32)

        model, processor = self._require_ready()
        batch_size = max(1, self._settings.embed_batch_size)
        chunks: list[np.ndarray] = []

        with self._lock, torch.inference_mode():
            for start in range(0, len(images), batch_size):
                batch = list(images[start : start + batch_size])
                inputs = processor(images=batch, return_tensors="pt")
                inputs = {key: value.to(self._device) for key, value in inputs.items()}
                features = model.get_image_features(**inputs)
                chunks.append(self._normalise(features))

        return np.vstack(chunks)

    def embed_texts(self, texts: Sequence[str]) -> np.ndarray:
        """Embed text into the same space as images (CLIP is trained jointly)."""
        if not texts:
            return np.zeros((0, self._dim), dtype=np.float32)

        model, processor = self._require_ready()

        with self._lock, torch.inference_mode():
            inputs = processor(
                text=list(texts), return_tensors="pt", padding=True, truncation=True
            )
            inputs = {key: value.to(self._device) for key, value in inputs.items()}
            features = model.get_text_features(**inputs)
            return self._normalise(features)

    @staticmethod
    def _normalise(features: Any) -> np.ndarray:
        """L2-normalise rows and hand back a contiguous float32 array for FAISS."""
        if not isinstance(features, torch.Tensor):
            pooler = getattr(features, "pooler_output", None)
            last_hidden = getattr(features, "last_hidden_state", None)
            if pooler is not None:
                features = pooler
            elif last_hidden is not None:
                features = last_hidden
            else:
                features = features[0]
        normalised = features / features.norm(dim=-1, keepdim=True).clamp_min(1e-12)
        return np.ascontiguousarray(normalised.detach().cpu().numpy().astype(np.float32))
