#!/usr/bin/env python3
"""Build or refresh the FAISS product index from MySQL.

Runnable at any time, independently of the API process:

    python scripts/build_index.py                 # incremental (fast)
    python scripts/build_index.py --force         # re-embed everything
    python scripts/build_index.py --no-prune      # keep vectors for removed products
    python scripts/build_index.py --stats         # inspect without changing anything

Incremental is the default: products whose image bytes are unchanged are skipped,
so a nightly cron over a large catalogue costs seconds. Use ``--force`` after
changing the CLIP model, since old embeddings are then meaningless.

Note: if the API service is running, it holds the index file in memory and will
overwrite what this script writes when it next saves. Either stop the API first,
or trigger the rebuild through ``POST /api/v1/index/rebuild`` instead.
"""

from __future__ import annotations

import argparse
import json
import sys
import time
from pathlib import Path

# Allow running as a plain script from the service root.
sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from app.config import get_settings  # noqa: E402
from app.logging_config import configure_logging, get_logger  # noqa: E402
from app.services.engine import SearchEngine  # noqa: E402
from app.services.product_source import ProductSource  # noqa: E402
from app.services.rebuild import run_rebuild  # noqa: E402

logger = get_logger("build_index")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Build the CLIP + FAISS product image index from MySQL.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Clear the index and re-embed every product (needed after a model change)",
    )
    parser.add_argument(
        "--no-prune",
        dest="prune",
        action="store_false",
        help="Do not remove vectors for products that are no longer eligible",
    )
    parser.add_argument(
        "--stats",
        action="store_true",
        help="Print index statistics and exit without modifying anything",
    )
    parser.add_argument(
        "--check-db",
        action="store_true",
        help="Verify the MySQL connection and product count, then exit",
    )
    parser.add_argument("--json", action="store_true", help="Emit machine-readable output")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    settings = get_settings()
    configure_logging(settings.log_level, settings.log_json)

    if args.check_db:
        source = ProductSource(settings)
        if not source.health():
            print(f"Cannot connect to MySQL at {settings.mysql_dsn}", file=sys.stderr)
            return 1
        total = source.count()
        print(f"Connected to {settings.mysql_dsn}")
        print(f"Eligible products: {total}")
        return 0

    logger.info("Initialising engine (this loads the CLIP model)")
    engine = SearchEngine(settings)

    try:
        engine.startup()

        if args.stats:
            stats = engine.stats()
            print(json.dumps(stats, indent=2, default=str))
            return 0

        started = time.perf_counter()
        snapshot = run_rebuild(engine, settings, force=args.force, prune=args.prune)
        elapsed = round(time.perf_counter() - started, 2)

        if args.json:
            print(json.dumps({**snapshot, "took_seconds": elapsed, "vectors": engine.index.size}))
        else:
            print()
            print("=" * 58)
            print(" Index rebuild complete" if not snapshot["error"] else " Index rebuild FAILED")
            print("=" * 58)
            print(f"  Eligible products : {snapshot['total']}")
            print(f"  Newly embedded    : {snapshot['indexed']}")
            print(f"  Skipped unchanged : {snapshot['skipped']}")
            print(f"  Failed            : {snapshot['failed']}")
            print(f"  Pruned            : {snapshot['removed']}")
            print(f"  Vectors in index  : {engine.index.size}")
            print(f"  Elapsed           : {elapsed}s")
            print(f"  Index file        : {engine.index.path}")
            print("=" * 58)

            if snapshot["error"]:
                print(f"\n  Error: {snapshot['error']}", file=sys.stderr)

        return 1 if snapshot["error"] else 0

    except KeyboardInterrupt:
        logger.warning("Interrupted; saving progress")
        engine.index.save(force=True)
        return 130
    except Exception as exc:
        logger.exception("Rebuild aborted")
        print(f"Rebuild aborted: {exc}", file=sys.stderr)
        return 1
    finally:
        engine.metadata.close()


if __name__ == "__main__":
    sys.exit(main())
