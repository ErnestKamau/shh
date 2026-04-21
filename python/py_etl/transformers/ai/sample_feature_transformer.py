"""
Sample Feature Transformer
Engineers features from sample_headers for TAT prediction and process optimization.
"""
from __future__ import annotations

from datetime import datetime
from typing import Optional

import pandas as pd
from loguru import logger

from py_etl.core.database import DatabaseManager
from py_etl.config.config import settings


class SampleFeatureTransformer:
    """
    Transforms sample_headers data into engineered features for ML models.
    
    Key Features:
    - TAT (turnaround time) in days
    - Workflow stage encoding
    - Priority scoring
    - Rework detection
    - Processing/approval delays
    """

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        """Initialize the transformer with database access."""
        self.db_manager = db_manager or DatabaseManager()
        self.reporting_schema = settings.ai_reporting_schema

    def extract_source_data(self, limit: Optional[int] = None) -> pd.DataFrame:
        """
        Extract sample headers from the reporting schema.
        
        Args:
            limit: Optional row limit for testing
        
        Returns:
            DataFrame with sample header data
        """
        limit_clause = f"LIMIT {limit}" if limit else ""
        
        query = f"""
            SELECT 
                source_id,
                batch_code,
                status,
                crm_customer_id,
                is_qc_batch,
                processing_date,
                approval_date_at,
                source_created_at,
                source_updated_at
            FROM {self.reporting_schema}.sample_headers
            WHERE LOWER(TRIM(COALESCE(isactive, ''))) IN ('1', 't', 'true', 'yes', 'y')
            {limit_clause}
        """
        
        with self.db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)
        
        logger.info(f"Extracted {len(df)} samples for feature engineering")
        return df

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Transform sample data into engineered features.
        
        Args:
            df: Raw sample headers DataFrame
        
        Returns:
            DataFrame with engineered features matching ai.ai_sample_features schema
        """
        if df.empty:
            logger.warning("Empty DataFrame provided for transformation")
            return pd.DataFrame()
        
        logger.info(f"Engineering features for {len(df)} samples")
        
        features = []
        now = pd.Timestamp.now()
        
        for _, row in df.iterrows():
            try:
                # Calculate TAT (days from created to now or approval)
                created_date = self._parse_date(row.get('source_created_at'))
                approval_date = self._parse_date(row.get('approval_date_at'))
                processing_date = self._parse_date(row.get('processing_date'))
                
                if created_date:
                    if approval_date:
                        tat_days = (approval_date - created_date).days
                    else:
                        tat_days = (now - created_date).days
                else:
                    tat_days = None
                
                # Calculate processing delay
                processing_delay = None
                if created_date and processing_date:
                    processing_delay = (processing_date - created_date).days
                
                # Calculate approval delay
                approval_delay = None
                if processing_date and approval_date:
                    approval_delay = (approval_date - processing_date).days
                
                # Encode workflow stage (from status)
                status = str(row.get('status', '')).lower()
                stage_count = self._encode_stage(status)
                
                # Priority score (would come from priority field if available)
                # For now, infer from status
                priority_score = self._infer_priority(status)
                
                # Detect rework
                rework_flag = status in ['rework', 'repeat', 'rejected', 'failed']
                
                features.append({
                    'sample_id': row['source_id'],
                    'tat_days': tat_days,
                    'stage_count': stage_count,
                    'priority_score': priority_score,
                    'rework_flag': rework_flag,
                    'is_qc_batch': self._parse_bool(row.get('is_qc_batch')),
                    'processing_delay_days': processing_delay,
                    'approval_delay_days': approval_delay,
                    'customer_id': row.get('crm_customer_id'),
                    'feature_version': 'v1.0',
                })
                
            except Exception as exc:
                logger.warning(
                    f"Failed to transform sample {row.get('source_id')}: {exc}"
                )
                continue
        
        result_df = pd.DataFrame(features)
        logger.info(f"Engineered {len(result_df)} sample features")
        
        return result_df

    @staticmethod
    def _parse_date(date_str) -> Optional[pd.Timestamp]:
        """Parse date string to pandas Timestamp."""
        if pd.isna(date_str) or not date_str:
            return None
        try:
            return pd.to_datetime(date_str)
        except Exception:
            return None

    @staticmethod
    def _parse_bool(value) -> bool:
        """Parse truthy text/number values from reporting marts."""
        if value is None or pd.isna(value):
            return False
        return str(value).strip().lower() in {'1', 't', 'true', 'yes', 'y'}

    @staticmethod
    def _encode_stage(status: str) -> int:
        """
        Encode workflow stage as integer.
        Higher numbers = later stages.
        """
        stage_mapping = {
            'registered': 1,
            'received': 2,
            'in_progress': 3,
            'testing': 4,
            'analysis': 5,
            'review': 6,
            'approved': 7,
            'completed': 8,
            'rework': 3,  # Back to middle stage
            'repeat': 3,
            'rejected': 2,
            'cancelled': 0,
        }
        return stage_mapping.get(status, 0)

    @staticmethod
    def _infer_priority(status: str) -> int:
        """
        Infer priority score from status.
        1=low, 2=medium, 3=high
        
        In production, this should come from an actual priority field.
        """
        if status in ['urgent', 'stat', 'high']:
            return 3
        elif status in ['normal', 'in_progress', 'testing']:
            return 2
        else:
            return 1


def engineer_sample_features(
    snapshot_id: int,
    limit: Optional[int] = None
) -> pd.DataFrame:
    """
    Convenience function to extract and engineer sample features.
    
    Args:
        snapshot_id: The feature snapshot ID to associate with
        limit: Optional row limit
    
    Returns:
        DataFrame ready for loading into ai.ai_sample_features
    """
    transformer = SampleFeatureTransformer()
    df = transformer.extract_source_data(limit=limit)
    features = transformer.transform(df)
    
    # Add snapshot_id column
    features['snapshot_id'] = snapshot_id
    
    return features
