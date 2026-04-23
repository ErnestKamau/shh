"""
CLI entry point for Python ETL Engine.

Usage examples:
    python -m python.py_etl.cli.sync_command --test-connections
    python -m python.py_etl.cli.sync_command sample_headers sample_dates --chunk 1000
    python -m python.py_etl.cli.sync_command --all
"""
from __future__ import annotations

import argparse
import sys
from pathlib import Path

from loguru import logger

# Ensure project root is importable when executed as a script.
ROOT = Path(__file__).resolve().parents[3]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from ai_service.core.etl_engine import ETLEngine  # noqa: E402
from py_etl.transformers.tables.qc_robust_stats_transformer import QcRobustStatsTransformer  # noqa: E402


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        prog="imara-pyetl",
        description="Sync operational foundation tables from MySQL into PostgreSQL",
    )
    parser.add_argument(
        "tables",
        nargs="*",
        help="Optional table keys to sync (defaults to all configured tables)",
    )
    parser.add_argument(
        "--chunk",
        type=int,
        default=None,
        help="Override default ETL chunk size",
    )
    parser.add_argument(
        "--all",
        action="store_true",
        help="Sync all configured tables (same as omitting table args)",
    )
    parser.add_argument(
        "--run-id",
        type=int,
        default=None,
        help="Optional sync_run ID to track this execution in the database",
    )
    parser.add_argument(
        "--test-connections",
        action="store_true",
        help="Only test MySQL and PostgreSQL connectivity and exit",
    )
    return parser


def main() -> int:
    parser = build_parser()
    args = parser.parse_args()

    # PHASE 0: Initialize logging
    from py_etl.core.logging_config import setup_etl_logging
    import uuid
    run_correlation_id = str(args.run_id) if args.run_id else f"manual-{uuid.uuid4().hex[:8]}"
    setup_etl_logging(run_correlation_id)

    engine = ETLEngine()

    if args.test_connections:
        results = engine.test_connections()
        print(results)
        return 0 if results.get("mysql") and results.get("postgres") else 1

    table_keys = None if args.all or not args.tables else args.tables

    try:
        results = engine.sync_all(table_keys=table_keys, chunk_size=args.chunk)
    except Exception as exc:
        logger.exception(f"ETL execution failed: {exc}")
        return 1

    has_failure = False
    for table, result in results.items():
        status = result.get("status")
        rows = result.get("rows_synced", 0)
        target = result.get("target_table", "unknown")
        if status == "completed":
            print(f"[{table}] completed - {rows} row(s) synced to {target}")
        else:
            has_failure = True
            print(f"[{table}] failed - {result.get('error', 'Unknown error')}")

    # ── Post-sync: recompute ISO 13528 robust statistics if QC data was synced ──
    qc_tables = {"qc_results", "qc_processed_result", "analytes"}
    synced_tables = set(results.keys())
    if table_keys is None or qc_tables.intersection(synced_tables):
        try:
            updated = QcRobustStatsTransformer().run()
            print(f"[qc_robust_stats] completed - robust stats updated for {updated} analyte(s)")
        except Exception as exc:
            logger.warning(f"[qc_robust_stats] non-fatal failure: {exc}")

    return 1 if has_failure else 0


if __name__ == "__main__":
    raise SystemExit(main())
