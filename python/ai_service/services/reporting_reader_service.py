"""
Reporting Reader Service
Extracts fresh indexed rows from reporting.* for chunking.
Provides idempotent chunk IDs.
"""
from __future__ import annotations

import pandas as pd
from sqlalchemy import text

from python.py_etl.core.database import db_manager


class ReportingReaderService:
    """Reads reporting data for RAG feature engineering and chunks it."""

    # SQL queries for fetching rows incrementally
    DOMAIN_QUERIES = {
        "sample_headers": """
            SELECT source_id, batch_code, status, is_qc_batch, isactive,
                   processing_date, approval_date_at, synced_at
            FROM reporting.sample_headers
            WHERE synced_at > :watermark
            ORDER BY source_id ASC
            LIMIT 5000
        """,
        "sample_details": """
            SELECT sd.source_id, sd.sample_code,
                   sd.analysis_type_id, sd.sample_condition_id, sd.barcode,
                   sh.batch_code, sh.status AS batch_status
            FROM reporting.sample_details sd
            JOIN reporting.sample_headers sh ON sh.source_id = sd.sample_header_id
            WHERE sd.synced_at > :watermark
            ORDER BY sd.source_id ASC
            LIMIT 5000
        """,
        "inventory_items": """
            SELECT ii.source_id, ii.item_name, ii.stock_in, ii.active,
                   sc.category_name
            FROM reporting.inventory_items ii
            LEFT JOIN reporting.inventory_sub_categories sc 
                ON sc.source_id = ii.inventory_sub_category_id
            WHERE ii.synced_at > :watermark
            ORDER BY ii.source_id ASC
            LIMIT 5000
        """,
        "equipment": """
            SELECT source_id, equipment_name, asset_code, active, status,
                   next_maintenance_date, next_calibration_date, install_date
            FROM reporting.equipment_assets
            WHERE synced_at > :watermark
            ORDER BY source_id ASC
            LIMIT 5000
        """,
        "corrective_actions": """
            SELECT source_id, description, status_name, ca_date, close_date,
                   closure_remarks
            FROM reporting.corrective_actions
            WHERE synced_at > :watermark
            ORDER BY source_id ASC
            LIMIT 5000
        """
    }

    def fetch_fresh_rows(self, table_key: str, since_timestamp: str) -> pd.DataFrame:
        """Fetch rows for `table_key` that were synced after `since_timestamp`."""
        if table_key not in self.DOMAIN_QUERIES:
            return pd.DataFrame()
            
        sql = self.DOMAIN_QUERIES[table_key]
        
        # Fallback to epoch if no prior sync
        if not since_timestamp:
            since_timestamp = '1970-01-01 00:00:00+00:00'
            
        with db_manager.postgres_connection() as conn:
            df = pd.read_sql(text(sql), conn, params={"watermark": since_timestamp})
            
        return df

    def chunk_rows(self, table_key: str, df: pd.DataFrame) -> list[dict]:
        """Generic JSON chunking strategy."""
        if df.empty:
            return []
            
        chunks = []
        # Fallback simplistic chunking: encode rows as JSON str.
        # Idempotent IDs built via `table_key:source_id:chunk_index`
        for i, row in df.iterrows():
            content = ", ".join(f"{k}: {v}" for k, v in row.items() if not pd.isna(v) and k != 'source_id')
            chunks.append({
                "chunk_id": f"{table_key}:{row['source_id']}:0",
                "table_key": table_key,
                "source_id": row['source_id'],
                "content": content
            })
            
        return chunks


reporting_reader_service = ReportingReaderService()
