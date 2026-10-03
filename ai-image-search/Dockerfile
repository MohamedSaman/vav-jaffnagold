# Multi-stage build: compile wheels in the builder, ship only the runtime layer.
FROM python:3.12-slim AS builder

ENV PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

WORKDIR /build

RUN apt-get update \
    && apt-get install -y --no-install-recommends build-essential \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .

# CPU-only torch: ~200 MB instead of ~2.5 GB with the bundled CUDA runtime.
RUN pip install --prefix=/install --extra-index-url https://download.pytorch.org/whl/cpu \
    -r requirements.txt


FROM python:3.12-slim AS runtime

ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    HF_HOME=/models \
    DATA_DIR=/data

RUN apt-get update \
    && apt-get install -y --no-install-recommends libgomp1 curl \
    && rm -rf /var/lib/apt/lists/*

# Run unprivileged: this process handles untrusted customer uploads.
RUN useradd --create-home --uid 10001 appuser \
    && mkdir -p /models /data \
    && chown -R appuser:appuser /models /data

COPY --from=builder /install /usr/local
WORKDIR /app
COPY --chown=appuser:appuser app ./app
COPY --chown=appuser:appuser scripts ./scripts

USER appuser

# Bake the model weights into the image so container start does not depend on
# reaching huggingface.co, and startup stays a few seconds instead of a minute.
RUN python -c "\
from transformers import CLIPModel, CLIPProcessor; \
name='openai/clip-vit-base-patch32'; \
CLIPModel.from_pretrained(name); \
CLIPProcessor.from_pretrained(name); \
print('model cached')"

EXPOSE 8001
VOLUME ["/data"]

# Reports 503 until the model is loaded, so orchestrators wait for readiness.
HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8001/health || exit 1

# One worker by design — each would load its own copy of the model and index.
# Scale with replicas behind a load balancer, not with --workers.
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "1"]
