"""
ETL Metadata Tracker (PHASE 0 ENHANCED)
Logs ETL run start / completion / failure into reporting.sync_runs —
the same PostgreSQL table already used by the PHP ETL service, so all
historical run data is preserved and visible in the same place.

PHASE 0 Enhancements:
- Track duration_seconds (total runtime)
- Track rows_failed and rows_quarantined separately
- Track current stage (init → extract → transform → load → finalize)
- Track chunk_count and failed_chunks
"""
from __future__ import annotations

import json
from datetime import datetime
from time import time

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager
from python.py_etl.config.config import settings


_RUNS_TABLE = f"{settings.ai_reporting_schema}.sync_runs"


class ETLTracker:
    """Records ETL job execution metadata into ``reporting.sync_runs`` with PHASE 0 enhancements."""

    def __init__(self):
        """Initialize tracker with instance state for duration tracking."""
        self.current_run_id = None
        self.start_time = None
        self.chunk_count = 0
        self.failed_chunks = []

    # ── Lifecycle ─────────────────────────────────────────────────────────

    def start_run(
        self,
        sync_scope: str,
        source_table: str,
        target_table: str,
        chunk_size: int | None = None,
    ) -> int:
        """
        Insert a *running* record and return its auto-generated ``id``.
        
        PHASE 0: Initializes instance state for duration tracking.

        Args:
            sync_scope:   Logical table key (e.g. ``"sample_headers"``).
            source_table: MySQL source table name.
            target_table: PostgreSQL target table name (schema-qualified).
            chunk_size:   Chunk size used for this run.

        Returns:
            ``run_id`` – the primary-key of the newly created row.
        """
        sql = f"""
            INSERT INTO {_RUNS_TABLE}
                (sync_scope, source_table, target_table,
                 status, rows_synced, rows_failed, rows_quarantined,
                 stage, chunk_count, started_at, metadata,
                 created_at, updated_at)
            VALUES
                (:sync_scope, :source_table, :target_table,
                 :status, 0, 0, 0,
                 :stage, 0, :started_at, :metadata,
                 :now, :now)
            RETURNING id
        """
        metadata = json.dumps({
            "engine": "python",
            "chunk_size": chunk_size or settings.etl_batch_size,
            "phase_0": True,  # Mark as PHASE 0 enhanced run
        })

        try:
            with db_manager.postgres_connection() as conn:
                result = conn.execute(
                    text(sql),
                    {
                        "sync_scope": sync_scope,
                        "source_table": source_table,
                        "target_table": target_table,
                        "status": "running",
                        "stage": "init",  # PHASE 0: Track stage
                        "started_at": datetime.now(),
                        "metadata": metadata,
                        "now": datetime.now(),
                    },
                )
                conn.commit()
                run_id: int = result.fetchone()[0]
                
                # PHASE 0: Store instance state for duration tracking
                self.current_run_id = run_id
                self.start_time = time()
                self.chunk_count = 0
                self.failed_chunks = []
                
                logger.info(f"[{sync_scope}] ETL run #{run_id} started")
                return run_id
        except Exception as exc:
            logger.error(f"Failed to start ETL run for '{sync_scope}': {exc}")
            raise

    def update_stage(self, stage: str) -> None:
        """
        PHASE 0: Update current pipeline stage.
        
        Args:
            stage: Current stage (init|extract|transform|load|finalize)
        """
        if not self.current_run_id:
            logger.warning("Cannot update stage: no active run")
            return
        
        sql = f"""
            UPDATE {_RUNS_TABLE}
            SET stage = :stage, updated_at = :now
            WHERE id = :run_id
        """
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(
                    text(sql),
                    {
                        "run_id": self.current_run_id,
                        "stage": stage,
                        "now": datetime.now(),
                    },
                )
                conn.commit()
                logger.debug(f"ETL run #{self.current_run_id} stage updated: {stage}")
        except Exception as exc:
            logger.warning(f"Failed to update stage for run #{self.current_run_id}: {exc}")

    def record_chunk(
        self,
        chunk_id: int,
        rows_inserted: int = 0,
        rows_quarantined: int = 0,
        rows_failed: int = 0,
    ) -> None:
        """
        PHASE 0: Record chunk-level metrics.
        
        Args:
            chunk_id: Chunk identifier
            rows_inserted: Count of successfully inserted rows
            rows_quarantined: Count of quarantined (rejected) rows
            rows_failed: Count of failed rows
        """
        if not self.current_run_id:
            logger.warning("Cannot record chunk: no active run")
            return
        
        self.chunk_count += 1
        
        sql = f"""
            UPDATE {_RUNS_TABLE}
            SET rows_synced = rows_synced + :rows_inserted,
                rows_quarantined = rows_quarantined + :rows_quarantined,
                rows_failed = rows_failed + :rows_failed,
                chunk_count = :chunk_count,
                updated_at = :now
            WHERE id = :run_id
        """
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(
                    text(sql),
                    {
                        "run_id": self.current_run_id,
                        "rows_inserted": rows_inserted,
                        "rows_quarantined": rows_quarantined,
                        "rows_failed": rows_failed,
                        "chunk_count": self.chunk_count,
                        "now": datetime.now(),
                    },
                )
                conn.commit()
                logger.debug(
                    f"ETL run #{self.current_run_id} chunk {chunk_id}: "
                    f"+{rows_inserted} rows, {rows_quarantined} quarantined"
                )
        except Exception as exc:
            logger.warning(f"Failed to record chunk metrics for run #{self.current_run_id}: {exc}")

    def record_chunk_failure(self, chunk_id: int) -> None:
        """
        PHASE 0: Record failed chunk ID for later inspection.
        
        Args:
            chunk_id: Chunk that failed
        """
        if not self.current_run_id:
            return
        
        self.failed_chunks.append(chunk_id)
        
        sql = f"""
            UPDATE {_RUNS_TABLE}
            SET failed_chunks = :failed_chunks,
                updated_at = :now
            WHERE id = :run_id
        """
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(
                    text(sql),
                    {
                        "run_id": self.current_run_id,
                        "failed_chunks": json.dumps(self.failed_chunks),
                        "now": datetime.now(),
                    },
                )
                conn.commit()
        except Exception as exc:
            logger.warning(f"Failed to record chunk failure for run #{self.current_run_id}: {exc}")

    def complete_run(self, rows_synced: int = 0) -> None:
        """
        Mark *run_id* as **completed**.
        
        PHASE 0: Calculates and stores duration_seconds.

        Args:
            rows_synced:  Total rows processed.
        """
        if not self.current_run_id or not self.start_time:
            logger.warning("Cannot complete run: no active run or missing start_time")
            return
        
        duration_seconds = int(time() - self.start_time)
        
        sql = f"""
            UPDATE {_RUNS_TABLE}
            SET status = 'completed',
                stage = 'finalize',
                rows_synced = :rows_synced,
                duration_seconds = :duration_seconds,
                finished_at = :finished_at,
                updated_at = :now
            WHERE id = :run_id
        """
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(
                    text(sql),
                    {
                        "run_id": self.current_run_id,
                        "rows_synced": rows_synced,
                        "duration_seconds": duration_seconds,
                        "finished_at": datetime.now(),
                        "now": datetime.now(),
                    },
                )
                conn.commit()
                logger.info(
                    f"ETL run #{self.current_run_id} completed – "
                    f"{rows_synced:,} rows synced in {duration_seconds}s"
                )
        except Exception as exc:
            logger.error(f"Failed to mark ETL run #{self.current_run_id} as completed: {exc}")

    def fail_run(self, error_message: str, stage: str = None) -> None:
        """
        Mark *run_id* as **failed** and store *error_message*.
        
        PHASE 0: Calculates and stores duration_seconds, updates stage.

        Args:
            error_message: Human-readable description of the failure.
            stage: Stage where failure occurred (optional override)
        """
        if not self.current_run_id or not self.start_time:
            logger.warning("Cannot fail run: no active run or missing start_time")
            return
        
        duration_seconds = int(time() - self.start_time)
        
        sql = f"""
            UPDATE {_RUNS_TABLE}
            SET status = 'failed',
                stage = COALESCE(:stage, stage),
                duration_seconds = :duration_seconds,
                finished_at = :finished_at,
                error_message = :error_message,
                failed_chunks = :failed_chunks,
                updated_at = :now
            WHERE id = :run_id
        """
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(
                    text(sql),
                    {
                        "run_id": self.current_run_id,
                        "stage": stage,
                        "duration_seconds": duration_seconds,
                        "finished_at": datetime.now(),
                        "error_message": error_message,
                        "failed_chunks": json.dumps(self.failed_chunks) if self.failed_chunks else None,
                        "now": datetime.now(),
                    },
                )
                conn.commit()
                logger.error(
                    f"ETL run #{self.current_run_id} failed after {duration_seconds}s: {error_message}"
                )
        except Exception as exc:
            logger.error(f"Failed to record failure for ETL run #{self.current_run_id}: {exc}")

    # ── Queries ───────────────────────────────────────────────────────────

    @staticmethod
    def get_last_run(sync_scope: str) -> dict | None:
        """
        Return the most recent run record for *sync_scope*, or ``None``.

        Args:
            sync_scope: Logical table key.

        Returns:
            Dict with keys matching the ``sync_runs`` columns, or ``None``.
        """
        sql = f"""
            SELECT id, sync_scope, source_table, target_table,
                   status, rows_synced, rows_failed, rows_quarantined,
                   stage, duration_seconds, started_at, finished_at,
                   error_message, metadata
            FROM {_RUNS_TABLE}
            WHERE sync_scope = :sync_scope
            ORDER BY started_at DESC
            LIMIT 1
        """
        try:
            with db_manager.postgres_connection() as conn:
                row = conn.execute(text(sql), {"sync_scope": sync_scope}).fetchone()
                if row:
                    keys = [
                        "id", "sync_scope", "source_table", "target_table",
                        "status", "rows_synced", "rows_failed", "rows_quarantined",
                        "stage", "duration_seconds", "started_at", "finished_at",
                        "error_message", "metadata",
                    ]
                    return dict(zip(keys, row))
                return None
        except Exception as exc:
            logger.error(f"Failed to fetch last run for '{sync_scope}': {exc}")
            return None

    @staticmethod
    def get_run_history(sync_scope: str | None = None, limit: int = 20) -> list[dict]:
        """
        Return up to *limit* recent run records, optionally filtered by *sync_scope*.

        Args:
            sync_scope: Optional filter.
            limit:      Maximum rows to return.

        Returns:
            List of dicts (newest first).
        """
        if sync_scope:
            sql = f"""
                SELECT id, sync_scope, source_table, target_table,
                       status, rows_synced, rows_failed, rows_quarantined,
                       stage, duration_seconds, started_at, finished_at,
                       error_message
                FROM {_RUNS_TABLE}
                WHERE sync_scope = :sync_scope
                ORDER BY started_at DESC
                LIMIT :limit
            """
            params: dict = {"sync_scope": sync_scope, "limit": limit}
        else:
            sql = f"""
                SELECT id, sync_scope, source_table, target_table,
                       status, rows_synced, rows_failed, rows_quarantined,
                       stage, duration_seconds, started_at, finished_at,
                       error_message
                FROM {_RUNS_TABLE}
                ORDER BY started_at DESC
                LIMIT :limit
            """
            params = {"limit": limit}

        try:
            with db_manager.postgres_connection() as conn:
                rows = conn.execute(text(sql), params).fetchall()
                keys = [
                    "id", "sync_scope", "source_table", "target_table",
                    "status", "rows_synced", "rows_failed", "rows_quarantined",
                    "stage", "duration_seconds", "started_at", "finished_at",
                    "error_message",
                ]
                return [dict(zip(keys, r)) for r in rows]
        except Exception as exc:
            logger.error(f"Failed to retrieve run history: {exc}")
            return []


# Shared singleton instance (PHASE 0: Now an instance for state tracking)
etl_tracker = ETLTracker()
