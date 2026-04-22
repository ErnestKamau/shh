"""
RequestLogger — Lightweight AI Request Observability

Logs every AI request to `ai.ai_request_logs` in PostgreSQL.
Fire-and-forget: writes happen on a background thread and never
crash the request pipeline.

Table: fivet_imara_ai.ai.ai_request_logs
"""

import logging
import threading
import traceback
from datetime import datetime, timezone
from typing import Optional

from sqlalchemy import text
from py_etl.core.database import db_manager

logger = logging.getLogger(__name__)

_DDL = """
CREATE TABLE IF NOT EXISTS ai.ai_request_logs (
    id              BIGSERIAL PRIMARY KEY,
    trace_id        VARCHAR(64),
    query           TEXT NOT NULL,
    mode            VARCHAR(128),
    route_name      VARCHAR(128),
    routing_tier    VARCHAR(64),
    latency_ms      INT,
    success         BOOLEAN DEFAULT TRUE,
    error_message   TEXT,
    company_id      INT,
    user_id         INT,
    session_id      VARCHAR(128),
    response_preview TEXT,
    source_count    INT DEFAULT 0,
    cache_hit       BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_ai_request_logs_created
    ON ai.ai_request_logs (created_at DESC);

CREATE INDEX IF NOT EXISTS idx_ai_request_logs_mode
    ON ai.ai_request_logs (mode);

CREATE INDEX IF NOT EXISTS idx_ai_request_logs_success
    ON ai.ai_request_logs (success);
"""


class RequestLogger:
    """
    Non-blocking request logger. All writes are fire-and-forget
    on a daemon thread so they never slow down or crash a user request.
    """

    def __init__(self):
        self._table_ensured = False

    def ensure_table(self) -> None:
        """Create the logs table if it doesn't exist. Safe to call multiple times."""
        if self._table_ensured:
            return
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(text(_DDL))
                conn.commit()
            self._table_ensured = True
            logger.info("RequestLogger: ai.ai_request_logs table ensured.")
        except Exception as exc:
            logger.warning(f"RequestLogger: Could not ensure logs table: {exc}")

    def log(
        self,
        *,
        trace_id: Optional[str] = None,
        query: str,
        mode: str,
        route_name: Optional[str] = None,
        routing_tier: Optional[str] = None,
        latency_ms: int = 0,
        success: bool = True,
        error_message: Optional[str] = None,
        company_id: Optional[int] = None,
        user_id: Optional[int] = None,
        session_id: Optional[str] = None,
        response_preview: Optional[str] = None,
        source_count: int = 0,
        cache_hit: bool = False,
    ) -> None:
        """
        Fire-and-forget log entry. Runs on a daemon thread.
        Never raises — failures are logged as warnings.
        """
        # Truncate response preview to avoid bloating the table
        if response_preview and len(response_preview) > 500:
            response_preview = response_preview[:500] + "…"

        # Truncate query for safety
        if query and len(query) > 2000:
            query = query[:2000] + "…"

        # Truncate mode and routing_tier for DB safety
        if mode and len(mode) > 128:
            mode = mode[:125] + "..."
        if routing_tier and len(routing_tier) > 64:
            routing_tier = routing_tier[:61] + "..."

        t = threading.Thread(
            target=self._write,
            kwargs=dict(
                trace_id=trace_id,
                query=query,
                mode=mode,
                route_name=route_name,
                routing_tier=routing_tier,
                latency_ms=latency_ms,
                success=success,
                error_message=error_message,
                company_id=company_id,
                user_id=user_id,
                session_id=session_id,
                response_preview=response_preview,
                source_count=source_count,
                cache_hit=cache_hit,
            ),
            daemon=True,
        )
        t.start()

    def _write(self, **kwargs) -> None:
        """Actual DB write — runs off the main thread."""
        try:
            self.ensure_table()

            sql = text("""
                INSERT INTO ai.ai_request_logs
                    (trace_id, query, mode, route_name, routing_tier, latency_ms,
                     success, error_message, company_id, user_id, session_id,
                     response_preview, source_count, cache_hit, created_at)
                VALUES
                    (:trace_id, :query, :mode, :route_name, :routing_tier, :latency_ms,
                     :success, :error_message, :company_id, :user_id, :session_id,
                     :response_preview, :source_count, :cache_hit, :created_at)
            """)

            kwargs["created_at"] = datetime.now(timezone.utc)

            with db_manager.postgres_connection() as conn:
                conn.execute(sql, kwargs)
                conn.commit()

        except Exception:
            logger.warning(
                f"RequestLogger: Failed to write log entry: "
                f"{traceback.format_exc()}"
            )


# Shared singleton
request_logger = RequestLogger()
