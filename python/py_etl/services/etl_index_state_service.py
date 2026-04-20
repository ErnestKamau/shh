"""
ETL Index State Service
Tracks per-table sync metadata in reporting.etl_index_state.

Responsibilities:
- Record successful / failed ETL runs per table key
- Record successful / failed RAG indexing runs per table key
- Expose the last source watermark so the RAG task only processes
  rows that are newer than the previous index run
"""
from __future__ import annotations

from datetime import datetime, timezone
from typing import Any, Optional

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager

_TABLE = "reporting.etl_index_state"


class ETLIndexStateService:
    """Manages incremental sync state for the ETL → embed pipeline."""

    def upsert_etl_success(self, table_key: str, rows_synced: int) -> None:
        """Record a successful ETL completion for *table_key*."""
        sql = f"""
            INSERT INTO {_TABLE}
                (table_key, last_etl_completed_at, rows_synced_last_run,
                 etl_status, etl_error, updated_at)
            VALUES
                (:table_key, :now, :rows_synced,
                 'success', NULL, :now)
            ON CONFLICT (table_key) DO UPDATE SET
                last_etl_completed_at = EXCLUDED.last_etl_completed_at,
                rows_synced_last_run  = EXCLUDED.rows_synced_last_run,
                etl_status            = 'success',
                etl_error             = NULL,
                updated_at            = EXCLUDED.updated_at
        """
        self._execute(sql, {"table_key": table_key, "now": _now(), "rows_synced": rows_synced})
        logger.info(f"[etl_state] ETL success recorded for '{table_key}' ({rows_synced} rows)")

    def upsert_etl_failure(self, table_key: str, error: str = "") -> None:
        """Record a failed ETL run for *table_key*."""
        sql = f"""
            INSERT INTO {_TABLE}
                (table_key, etl_status, etl_error, updated_at)
            VALUES
                (:table_key, 'failed', :error, :now)
            ON CONFLICT (table_key) DO UPDATE SET
                etl_status  = 'failed',
                etl_error   = EXCLUDED.etl_error,
                updated_at  = EXCLUDED.updated_at
        """
        self._execute(sql, {"table_key": table_key, "error": error[:1000], "now": _now()})
        logger.warning(f"[etl_state] ETL failure recorded for '{table_key}'")

    def upsert_index_success(
        self,
        table_key: str,
        watermark: int,
        chunks: int,
        embeddings: int,
    ) -> None:
        """Record a successful RAG indexing completion for *table_key*."""
        sql = f"""
            INSERT INTO {_TABLE}
                (table_key, last_indexed_at, last_source_watermark,
                 chunks_produced_last, embeddings_produced_last,
                 index_status, index_error, updated_at)
            VALUES
                (:table_key, :now, :watermark, :chunks, :embeddings,
                 'success', NULL, :now)
            ON CONFLICT (table_key) DO UPDATE SET
                last_indexed_at          = EXCLUDED.last_indexed_at,
                last_source_watermark    = EXCLUDED.last_source_watermark,
                chunks_produced_last     = EXCLUDED.chunks_produced_last,
                embeddings_produced_last = EXCLUDED.embeddings_produced_last,
                index_status             = 'success',
                index_error              = NULL,
                updated_at               = EXCLUDED.updated_at
        """
        self._execute(
            sql,
            {
                "table_key": table_key,
                "now": _now(),
                "watermark": watermark,
                "chunks": chunks,
                "embeddings": embeddings,
            },
        )
        logger.info(
            f"[etl_state] Index success for '{table_key}': "
            f"watermark={watermark}, chunks={chunks}, embeddings={embeddings}"
        )

    def upsert_index_failure(self, table_key: str, error: str = "") -> None:
        """Record a failed indexing run for *table_key*."""
        sql = f"""
            INSERT INTO {_TABLE}
                (table_key, index_status, index_error, updated_at)
            VALUES
                (:table_key, 'failed', :error, :now)
            ON CONFLICT (table_key) DO UPDATE SET
                index_status = 'failed',
                index_error  = EXCLUDED.index_error,
                updated_at   = EXCLUDED.updated_at
        """
        self._execute(sql, {"table_key": table_key, "error": error[:1000], "now": _now()})
        logger.warning(f"[etl_state] Index failure recorded for '{table_key}'")

    def get_state(self, table_key: str) -> Optional[dict[str, Any]]:
        """Return full state row for *table_key*, or ``None`` if not found."""
        sql = f"SELECT * FROM {_TABLE} WHERE table_key = :table_key"
        try:
            with db_manager.postgres_connection() as conn:
                row = conn.execute(text(sql), {"table_key": table_key}).mappings().fetchone()
                return dict(row) if row else None
        except Exception as exc:
            logger.error(f"[etl_state] Failed to get state for '{table_key}': {exc}")
            return None

    def get_all_states(self) -> list[dict[str, Any]]:
        """Return all table sync states ordered by table_key."""
        sql = f"SELECT * FROM {_TABLE} ORDER BY table_key"
        try:
            with db_manager.postgres_connection() as conn:
                rows = conn.execute(text(sql)).mappings().fetchall()
                return [dict(r) for r in rows]
        except Exception as exc:
            logger.error(f"[etl_state] Failed to get all states: {exc}")
            return []

    def get_last_watermark(self, table_key: str) -> int:
        """Return the last indexed source watermark for *table_key* (0 if never indexed)."""
        state = self.get_state(table_key)
        if not state:
            return 0
        return int(state.get("last_source_watermark") or 0)

    def is_etl_healthy(self, table_key: str) -> bool:
        """Return True if the last ETL run for *table_key* was successful."""
        state = self.get_state(table_key)
        return bool(state and state.get("etl_status") == "success")

    # ── Internal ──────────────────────────────────────────────────────────────

    @staticmethod
    def _execute(sql: str, params: dict) -> None:
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(text(sql), params)
                conn.commit()
        except Exception as exc:
            logger.error(f"[etl_state] DB write failed: {exc}")
            raise


def _now() -> datetime:
    return datetime.now(tz=timezone.utc)


# Shared singleton
etl_index_state_service = ETLIndexStateService()
