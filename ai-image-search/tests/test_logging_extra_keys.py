"""Guards against a real bug class: `extra={"message": ...}` (or `filename`,
`module`, etc.) crashes with ``KeyError`` inside the stdlib logging module,
because those names collide with attributes ``LogRecord`` always sets — the
crash happens in ``Logger.makeRecord()`` itself, before any formatter runs, so
it fires in production regardless of ``LOG_JSON``.

This is a static AST scan rather than an import-and-call test because the
failure only happens once a *specific* log line executes with real arguments —
easy to miss in review, and expensive to reach by exercising every code path.
"""

from __future__ import annotations

import ast
import logging
from pathlib import Path

SERVICE_ROOT = Path(__file__).resolve().parent.parent

RESERVED_LOGRECORD_KEYS = frozenset(
    vars(logging.LogRecord("x", 0, "x", 0, "x", (), None)).keys()
) | {"message", "asctime", "taskName"}


def _find_bad_extra_keys(source_dir: Path) -> list[tuple[str, int, str]]:
    violations: list[tuple[str, int, str]] = []

    for path in source_dir.rglob("*.py"):
        tree = ast.parse(path.read_text(), filename=str(path))

        for node in ast.walk(tree):
            if not (isinstance(node, ast.keyword) and node.arg == "extra"):
                continue
            if not isinstance(node.value, ast.Dict):
                continue

            for key_node in node.value.keys:
                if isinstance(key_node, ast.Constant) and key_node.value in RESERVED_LOGRECORD_KEYS:
                    violations.append(
                        (str(path.relative_to(SERVICE_ROOT)), key_node.lineno, key_node.value)
                    )

    return violations


def test_no_extra_log_keys_collide_with_reserved_logrecord_attributes() -> None:
    violations = _find_bad_extra_keys(SERVICE_ROOT / "app")
    violations += _find_bad_extra_keys(SERVICE_ROOT / "scripts")

    assert violations == [], (
        "extra={} keys that collide with a reserved LogRecord attribute "
        "(e.g. 'message', 'filename', 'module') crash the logger at runtime:\n"
        + "\n".join(f"  {f}:{ln}  key={k!r}" for f, ln, k in violations)
    )
