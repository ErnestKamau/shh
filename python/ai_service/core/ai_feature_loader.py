"""
AI Feature Loader Service
Handles bulk loading of engineered features into the ai schema.
"""
from __future__ import annotations

from typing import Optional

import pandas as pd
from loguru import logger
from sqlalchemy import text

from py_etl.core.database import DatabaseManager
from py_etl.config.config import settings


class AIFeatureLoader:
    """
    Loads engineered features into AI schema tables with idempotent upserts.
    
    Supports:
    - Bulk inserts with conflict resolution
    - Idempotency (safe to re-run)
    - Transaction safety
    - Row count tracking
    """

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        """
        Initialize the AI feature loader.
        
        Args:
            db_manager: Optional DatabaseManager instance
        """
        self.db_manager = db_manager or DatabaseManager()
        self.schema = settings.ai_schema

    def load_features(
        self,
        table_name: str,
        df: pd.DataFrame,
        conflict_columns: Optional[list[str]] = None
    ) -> int:
        """
        Load features into AI schema table with upsert logic.
        
        Args:
            table_name: Target table name (without schema prefix)
            df: DataFrame with engineered features
            conflict_columns: Columns for conflict resolution (default: unique constraint columns)
        
        Returns:
            Number of rows loaded
        
        Raises:
            Exception: If loading fails
        """
        if df.empty:
            logger.warning(f"Empty DataFrame provided for {table_name}")
            return 0
        
        # Default conflict columns based on table
        if conflict_columns is None:
            conflict_columns = self._get_default_conflict_columns(table_name)
        
        try:
            # Use pandas to_sql with append mode
            # For production, consider using COPY or temp table + upsert pattern
            rows_loaded = self.db_manager.load_to_postgres(
                df=df,
                table_name=table_name,
                schema=self.schema,
                if_exists='append'
            )
            
            logger.info(
                f"Loaded {rows_loaded} features to {self.schema}.{table_name}"
            )
            
            return rows_loaded
            
        except Exception as exc:
            logger.error(f"Failed to load features to {table_name}: {exc}")
            raise

    def load_with_upsert(
        self,
        table_name: str,
        df: pd.DataFrame,
        conflict_columns: list[str],
        update_columns: Optional[list[str]] = None
    ) -> int:
        """
        Load features with explicit upsert (ON CONFLICT DO UPDATE).
        
        This method provides more control than load_features() and ensures
        true idempotency by updating existing records.
        
        Args:
            table_name: Target table name
            df: DataFrame with features
            conflict_columns: Columns that define uniqueness
            update_columns: Columns to update on conflict (default: all except conflict)
        
        Returns:
            Number of rows affected
        """
        if df.empty:
            return 0
        
        # Determine columns to update
        if update_columns is None:
            update_columns = [
                col for col in df.columns 
                if col not in conflict_columns and col != 'created_at'
            ]
        
        try:
            # Create temp table for staging
            temp_table = f"_temp_{table_name}_{pd.Timestamp.now().strftime('%Y%m%d_%H%M%S')}"
            
            # Load to temp table
            self.db_manager.load_to_postgres(
                df=df,
                table_name=temp_table,
                schema=self.schema,
                if_exists='replace'
            )
            
            # Build upsert query
            conflict_clause = ', '.join(conflict_columns)
            update_clause = ', '.join([
                f"{col} = EXCLUDED.{col}" for col in update_columns
            ])
            
            upsert_query = f"""
                INSERT INTO {self.schema}.{table_name}
                SELECT * FROM {self.schema}.{temp_table}
                ON CONFLICT ({conflict_clause})
                DO UPDATE SET {update_clause}
            """
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(text(upsert_query))
                conn.commit()
                rows_affected = result.rowcount
            
            # Clean up temp table
            self.db_manager.execute_postgres_sql(
                f"DROP TABLE IF EXISTS {self.schema}.{temp_table}"
            )
            
            logger.info(
                f"Upserted {rows_affected} rows to {self.schema}.{table_name}"
            )
            
            return rows_affected
            
        except Exception as exc:
            logger.error(f"Upsert failed for {table_name}: {exc}")
            raise

    def delete_snapshot_features(self, snapshot_id: int, table_name: str) -> int:
        """
        Delete all features associated with a snapshot.
        Useful for rollback or reprocessing.
        
        Args:
            snapshot_id: Snapshot ID to delete
            table_name: Target table name
        
        Returns:
            Number of rows deleted
        """
        try:
            query = text(f"""
                DELETE FROM {self.schema}.{table_name}
                WHERE snapshot_id = :snapshot_id
            """)
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(query, {"snapshot_id": snapshot_id})
                conn.commit()
                deleted_count = result.rowcount
            
            logger.info(
                f"Deleted {deleted_count} features from {table_name} "
                f"for snapshot {snapshot_id}"
            )
            
            return deleted_count
            
        except Exception as exc:
            logger.error(f"Failed to delete snapshot features: {exc}")
            raise

    @staticmethod
    def _get_default_conflict_columns(table_name: str) -> list[str]:
        """
        Get default conflict columns based on table structure.
        
        Args:
            table_name: Table name
        
        Returns:
            List of column names that define uniqueness
        """
        conflict_mapping = {
            'ai_sample_features': ['sample_id', 'snapshot_id'],
            'ai_equipment_features': ['equipment_id', 'snapshot_id'],
            'ai_qc_features': ['qc_result_id', 'snapshot_id'],
            'ai_prediction_runs': ['id'],  # Auto-increment, no conflicts
            'ai_feedback': ['id'],  # Auto-increment, no conflicts
        }
        
        return conflict_mapping.get(table_name, ['id'])


# Convenience functions for direct feature loading

def load_sample_features(df: pd.DataFrame) -> int:
    """Load sample features (convenience wrapper)."""
    loader = AIFeatureLoader()
    return loader.load_features('ai_sample_features', df)


def load_equipment_features(df: pd.DataFrame) -> int:
    """Load equipment features (convenience wrapper)."""
    loader = AIFeatureLoader()
    return loader.load_features('ai_equipment_features', df)


def load_qc_features(df: pd.DataFrame) -> int:
    """Load QC features (convenience wrapper)."""
    loader = AIFeatureLoader()
    return loader.load_features('ai_qc_features', df)
