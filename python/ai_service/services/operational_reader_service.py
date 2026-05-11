"""
Operational Reader Service

Reads approved public operational tables for AI indexing. This service is
read-only by design; it does not write to public or reporting schemas.
"""
from __future__ import annotations

import pandas as pd
from sqlalchemy import text

from python.py_pipeline.core.database import db_manager


class OperationalReaderService:
    """Reads whitelisted public tables and converts rows into RAG chunks."""

    DOMAIN_TABLES = {
        "sample_headers": "sample_headers",
        "sample_details": "sample_details",
        "inventory_items": "inventory_items",
        "equipment": "equipment",
        "corrective_actions": "corrective_actions",
    }

    def fetch_fresh_rows(self, table_key: str, since_timestamp: str) -> pd.DataFrame:
        """Fetch rows updated after `since_timestamp` from whitelisted public tables."""
        table = self.DOMAIN_TABLES.get(table_key)
        if not table:
            return pd.DataFrame()

        if not since_timestamp:
            since_timestamp = "1970-01-01 00:00:00+00:00"

        sql = text(f"""
            SELECT
                t.id AS source_id,
                t.*
            FROM public.{table} t
            WHERE COALESCE(t.updated_at, t.created_at, '1970-01-01'::timestamp) > :watermark
            ORDER BY t.id ASC
            LIMIT 5000
        """)

        with db_manager.postgres_connection() as conn:
            return pd.read_sql(sql, conn, params={"watermark": since_timestamp})

    def chunk_rows(self, table_key: str, df: pd.DataFrame) -> list[dict]:
        """Encode each operational row as a simple text chunk."""
        if df.empty:
            return []

        chunks = []
        for _, row in df.iterrows():
            source_id = row.get("source_id") or row.get("id")
            content = ", ".join(
                f"{key}: {value}"
                for key, value in row.items()
                if key != "source_id" and not pd.isna(value)
            )
            chunks.append({
                "chunk_id": f"{table_key}:{source_id}:0",
                "table_key": table_key,
                "source_id": source_id,
                "content": content,
            })

        return chunks


operational_reader_service = OperationalReaderService()
