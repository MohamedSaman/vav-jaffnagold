"""Logging setup shared by the API and the offline scripts."""

from __future__ import annotations

import json
import logging
import sys
from typing import Any

_CONFIGURED = False

# Attributes present on every LogRecord; anything else was added by the caller
# via ``extra=`` and belongs in the structured payload.
_RESERVED = frozenset(
    vars(logging.LogRecord("", 0, "", 0, "", (), None)).keys()
) | {"asctime", "message", "taskName"}


class JsonFormatter(logging.Formatter):
    """Emit one JSON object per line, suitable for log shippers."""

    def format(self, record: logging.LogRecord) -> str:
        payload: dict[str, Any] = {
            "timestamp": self.formatTime(record, "%Y-%m-%dT%H:%M:%S%z"),
            "level": record.levelname,
            "logger": record.name,
            "message": record.getMessage(),
        }

        for key, value in record.__dict__.items():
            if key not in _RESERVED:
                payload[key] = value

        if record.exc_info:
            payload["exception"] = self.formatException(record.exc_info)

        return json.dumps(payload, default=str)


def configure_logging(level: str = "INFO", as_json: bool = False) -> None:
    """Install a single stdout handler on the root logger (idempotent)."""
    global _CONFIGURED
    if _CONFIGURED:
        return

    handler = logging.StreamHandler(sys.stdout)
    if as_json:
        handler.setFormatter(JsonFormatter())
    else:
        handler.setFormatter(
            logging.Formatter(
                "%(asctime)s %(levelname)-8s %(name)s | %(message)s",
                datefmt="%Y-%m-%d %H:%M:%S",
            )
        )

    root = logging.getLogger()
    root.handlers = [handler]
    root.setLevel(level.upper())

    # These libraries are extremely chatty at INFO during model download.
    for noisy in ("urllib3", "filelock", "PIL", "httpx", "huggingface_hub"):
        logging.getLogger(noisy).setLevel(logging.WARNING)

    _CONFIGURED = True


def get_logger(name: str) -> logging.Logger:
    return logging.getLogger(name)
