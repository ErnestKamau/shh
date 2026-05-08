"""
AI index state service.

Tracks RAG indexing progress in ai.ai_index_state. It does not track ETL
state and never writes to public or reporting schemas.
"""
from __future__ import annotations

from datetime import datetime, timezone
from typing import Any, Optional

from loguru import logger
from sqlalchemy import text

from py_etl.core.database import db_manager

_TABLE = "ai.ai_index_state"


class AIIndexStateService:
    """Manages per-domain AI indexing state."""

    def upsert_index_success(
        self,
        table_key: str,
        watermark: int,
        chunks: int,
        embeddings: int,
    ) -> None:
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

    def upsert_index_failure(self, table_key: str, error: str = "") -> None:
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

    def get_state(self, table_key: str) -> Optional[dict[str, Any]]:
        sql = f"SELECT * FROM {_TABLE} WHERE table_key = :table_key"
        try:
            with db_manager.postgres_connection() as conn:
                row = conn.execute(text(sql), {"table_key": table_key}).mappings().fetchone()
                return dict(row) if row else None
        except Exception as exc:
            logger.warning(f"[ai_index_state] Failed to get state for '{table_key}': {exc}")
            return None

    def get_all_states(self) -> list[dict[str, Any]]:
        sql = f"SELECT * FROM {_TABLE} ORDER BY table_key"
        try:
            with db_manager.postgres_connection() as conn:
                rows = conn.execute(text(sql)).mappings().fetchall()
                return [dict(row) for row in rows]
        except Exception as exc:
            logger.warning(f"[ai_index_state] Failed to get all states: {exc}")
            return []

    def get_last_watermark(self, table_key: str) -> int:
        state = self.get_state(table_key)
        return int(state.get("last_source_watermark") or 0) if state else 0

    @staticmethod
    def _execute(sql: str, params: dict) -> None:
        with db_manager.postgres_connection() as conn:
            conn.execute(text(sql), params)
            conn.commit()


def _now() -> datetime:
    return datetime.now(tz=timezone.utc)


ai_index_state_service = AIIndexStateService()
