"""
Feature Snapshot Service
Manages versioned snapshots of engineered features for reproducibility.
"""
from __future__ import annotations

from datetime import datetime
from typing import Optional

import pandas as pd
from loguru import logger
from sqlalchemy import text

from py_etl.core.database import DatabaseManager
from py_etl.config.config import settings


class FeatureSnapshotService:
    """
    Creates and manages feature snapshots for versioning and reproducibility.
    
    Each snapshot represents a point-in-time capture of engineered features,
    enabling A/B testing, model retraining, and reproducible experiments.
    """

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        """
        Initialize the snapshot service.
        
        Args:
            db_manager: Optional DatabaseManager instance (creates new if None)
        """
        self.db_manager = db_manager or DatabaseManager()
        self.schema = settings.ai_schema

    def create_snapshot(
        self,
        description: str = "Feature engineering run",
        metadata: Optional[dict] = None
    ) -> int:
        """
        Create a new feature snapshot record.
        
        Args:
            description: Human-readable description of this snapshot
            metadata: Optional dict with additional context (JSON serializable)
        
        Returns:
            The new snapshot ID (integer primary key)
        
        Raises:
            Exception: If snapshot creation fails
        """
        try:
            snapshot_time = datetime.now()
            
            # Convert metadata to JSON string if provided
            metadata_json = None
            if metadata:
                import json
                metadata_json = json.dumps(metadata)
            
            query = text(f"""
                INSERT INTO {self.schema}.ai_feature_snapshots 
                    (snapshot_time, description, metadata, created_at, updated_at)
                VALUES 
                    (:snapshot_time, :description, :metadata, :created_at, :updated_at)
                RETURNING id
            """)
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(
                    query,
                    {
                        "snapshot_time": snapshot_time,
                        "description": description,
                        "metadata": metadata_json,
                        "created_at": snapshot_time,
                        "updated_at": snapshot_time,
                    }
                )
                conn.commit()
                snapshot_id = result.fetchone()[0]
            
            logger.info(
                f"Created feature snapshot {snapshot_id}: {description}",
                extra={"snapshot_id": snapshot_id, "metadata": metadata}
            )
            
            return snapshot_id
            
        except Exception as exc:
            logger.error(f"Failed to create feature snapshot: {exc}")
            raise

    def get_latest_snapshot(self) -> Optional[int]:
        """
        Get the ID of the most recent feature snapshot.
        
        Returns:
            Latest snapshot ID, or None if no snapshots exist
        """
        try:
            query = text(f"""
                SELECT id 
                FROM {self.schema}.ai_feature_snapshots
                ORDER BY snapshot_time DESC
                LIMIT 1
            """)
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(query)
                row = result.fetchone()
                
                if row:
                    return row[0]
                return None
                
        except Exception as exc:
            logger.warning(f"Failed to get latest snapshot: {exc}")
            return None

    def get_snapshot_details(self, snapshot_id: int) -> Optional[dict]:
        """
        Get detailed information about a specific snapshot.
        
        Args:
            snapshot_id: The snapshot ID to query
        
        Returns:
            Dict with snapshot details, or None if not found
        """
        try:
            query = text(f"""
                SELECT 
                    id,
                    snapshot_time,
                    description,
                    metadata,
                    created_at
                FROM {self.schema}.ai_feature_snapshots
                WHERE id = :snapshot_id
            """)
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(query, {"snapshot_id": snapshot_id})
                row = result.fetchone()
                
                if row:
                    return {
                        "id": row[0],
                        "snapshot_time": row[1],
                        "description": row[2],
                        "metadata": row[3],
                        "created_at": row[4],
                    }
                return None
                
        except Exception as exc:
            logger.error(f"Failed to get snapshot details: {exc}")
            return None

    def get_all_snapshots(self, limit: int = 100) -> pd.DataFrame:
        """
        Get a list of all feature snapshots.
        
        Args:
            limit: Maximum number of snapshots to return
        
        Returns:
            DataFrame with snapshot records
        """
        try:
            query = text(f"""
                SELECT 
                    id,
                    snapshot_time,
                    description,
                    created_at
                FROM {self.schema}.ai_feature_snapshots
                ORDER BY snapshot_time DESC
                LIMIT :limit
            """)
            
            with self.db_manager.postgres_connection() as conn:
                df = pd.read_sql(query, conn, params={"limit": limit})
            
            return df
            
        except Exception as exc:
            logger.error(f"Failed to get snapshots: {exc}")
            return pd.DataFrame()

    def delete_old_snapshots(self, retention_days: int = 90) -> int:
        """
        Delete snapshots older than the retention period.
        
        Note: This will cascade delete all associated features due to
        foreign key constraints.
        
        Args:
            retention_days: Keep snapshots from the last N days
        
        Returns:
            Number of snapshots deleted
        """
        try:
            query = text(f"""
                DELETE FROM {self.schema}.ai_feature_snapshots
                WHERE snapshot_time < NOW() - INTERVAL ':days days'
                RETURNING id
            """)
            
            with self.db_manager.postgres_connection() as conn:
                result = conn.execute(query, {"days": retention_days})
                conn.commit()
                deleted_count = result.rowcount
            
            logger.info(f"Deleted {deleted_count} old feature snapshots")
            return deleted_count
            
        except Exception as exc:
            logger.error(f"Failed to delete old snapshots: {exc}")
            return 0


# Convenience function for quick snapshot creation
def create_feature_snapshot(
    description: str = "Feature engineering run",
    metadata: Optional[dict] = None
) -> int:
    """
    Create a feature snapshot (convenience wrapper).
    
    Args:
        description: Snapshot description
        metadata: Optional metadata dict
    
    Returns:
        New snapshot ID
    """
    service = FeatureSnapshotService()
    return service.create_snapshot(description, metadata)
