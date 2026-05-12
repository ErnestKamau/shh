"""
Equipment Feature Transformer
Engineers features from equipment_assets and equipment_logs for predictive maintenance.
"""
from __future__ import annotations

from datetime import datetime
from typing import Optional

import pandas as pd
from loguru import logger

from py_pipeline.core.database import DatabaseManager
from py_pipeline.config.config import settings


class EquipmentFeatureTransformer:
    """
    Transforms equipment data into engineered features for ML models.
    
    Key Features:
    - Days since last service
    - Days until next service due
    - Failure rate from logs
    - Usage frequency
    - Maintenance/calibration counts
    - Risk scoring
    """

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        """Initialize the transformer with database access."""
        self.db_manager = db_manager or DatabaseManager()
        self.reporting_schema = settings.ai_reporting_schema

    def extract_source_data(self, limit: Optional[int] = None) -> pd.DataFrame:
        """
        Extract equipment data with aggregated log statistics.
        
        Args:
            limit: Optional row limit for testing
        
        Returns:
            DataFrame with equipment and log data
        """
        limit_clause = f"LIMIT {limit}" if limit else ""
        
        query = f"""
            WITH equipment_stats AS (
                SELECT 
                    equipment_id,
                    COUNT(*) as total_logs,
                    SUM(CASE WHEN LOWER(COALESCE(event_type, '')) LIKE '%%maint%%' THEN 1 ELSE 0 END) as maintenance_count,
                    SUM(CASE WHEN LOWER(COALESCE(event_type, '')) LIKE '%%calib%%' THEN 1 ELSE 0 END) as calibration_count,
                    SUM(CASE WHEN LOWER(COALESCE(event_type, '')) LIKE '%%verif%%' THEN 1 ELSE 0 END) as verification_count,
                    MAX(event_date) as last_service_date,
                    AVG(CASE WHEN LOWER(COALESCE(event_type, '')) LIKE '%%fail%%' OR LOWER(COALESCE(event_type, '')) LIKE '%%breakdown%%' THEN 1.0 ELSE 0.0 END) as failure_rate
                FROM {self.reporting_schema}.equipment_logs
                GROUP BY equipment_id
            )
            SELECT 
                ea.source_id as equipment_id,
                ea.name as equipment_name,
                ea.status as equipment_status,
                ea.date_purchased,
                ea.maintainance_days,
                COALESCE(es.total_logs, 0) as total_logs,
                COALESCE(es.maintenance_count, 0) as maintenance_count,
                COALESCE(es.calibration_count, 0) as calibration_count,
                COALESCE(es.verification_count, 0) as verification_count,
                es.last_service_date as log_last_service_date,
                COALESCE(es.failure_rate, 0.0) as failure_rate
            FROM {self.reporting_schema}.equipment_assets ea
            LEFT JOIN equipment_stats es ON ea.source_id = es.equipment_id
            WHERE LOWER(TRIM(COALESCE(ea.active, ''))) IN ('1', 't', 'true', 'yes', 'y')
            {limit_clause}
        """
        
        with self.db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)
        
        logger.info(f"Extracted {len(df)} equipment records for feature engineering")
        return df

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Transform equipment data into engineered features.
        
        Args:
            df: Raw equipment DataFrame
        
        Returns:
            DataFrame with engineered features matching ai.ai_equipment_features schema
        """
        if df.empty:
            logger.warning("Empty DataFrame provided for transformation")
            return pd.DataFrame()
        
        logger.info(f"Engineering features for {len(df)} equipment items")
        
        features = []
        now = pd.Timestamp.now()
        
        for _, row in df.iterrows():
            try:
                # Calculate days since service
                last_service = self._parse_date(
                    row.get('log_last_service_date') or row.get('date_purchased')
                )
                days_since_service = None
                if last_service:
                    days_since_service = (now - last_service).days
                
                # Calculate days until due
                next_due = None
                purchase_date = self._parse_date(row.get('date_purchased'))
                maintenance_days = self._parse_int(row.get('maintainance_days'))
                if purchase_date and maintenance_days:
                    next_due = purchase_date + pd.Timedelta(days=maintenance_days)
                days_until_due = None
                if next_due:
                    days_until_due = (next_due - now).days
                
                # Calculate usage frequency (logs per day)
                total_logs = row.get('total_logs', 0)
                usage_frequency = 0.0
                if days_since_service and days_since_service > 0:
                    usage_frequency = total_logs / days_since_service
                
                # Calculate risk score
                risk_score = self._calculate_risk_score(
                    days_since_service=days_since_service,
                    days_until_due=days_until_due,
                    failure_rate=row.get('failure_rate', 0.0),
                    equipment_status=row.get('equipment_status', '')
                )
                
                features.append({
                    'equipment_id': row['equipment_id'],
                    'days_since_service': days_since_service,
                    'days_until_due': days_until_due,
                    'failure_rate': float(row.get('failure_rate', 0.0)),
                    'usage_frequency': usage_frequency,
                    'maintenance_count': int(row.get('maintenance_count', 0)),
                    'calibration_count': int(row.get('calibration_count', 0)),
                    'verification_count': int(row.get('verification_count', 0)),
                    'risk_score': risk_score,
                    'feature_version': 'v1.0',
                })
                
            except Exception as exc:
                logger.warning(
                    f"Failed to transform equipment {row.get('equipment_id')}: {exc}"
                )
                continue
        
        result_df = pd.DataFrame(features)
        logger.info(f"Engineered {len(result_df)} equipment features")
        
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
    def _parse_int(value) -> Optional[int]:
        """Parse integer-like values safely from text columns."""
        if value is None or pd.isna(value):
            return None
        try:
            return int(float(str(value).strip()))
        except Exception:
            return None

    @staticmethod
    def _calculate_risk_score(
        days_since_service: Optional[int],
        days_until_due: Optional[int],
        failure_rate: float,
        equipment_status: str
    ) -> float:
        """
        Calculate equipment risk score (0.0 to 1.0, higher = more risk).
        
        Factors:
        - Overdue maintenance (high risk)
        - Time since last service (moderate risk)
        - Historical failure rate (high risk)
        - Equipment status (moderate risk)
        """
        risk = 0.0
        
        # Overdue maintenance (40% weight)
        if days_until_due is not None:
            if days_until_due < 0:
                risk += 0.4  # Overdue
            elif days_until_due < 7:
                risk += 0.3  # Due soon
            elif days_until_due < 14:
                risk += 0.2  # Due within 2 weeks
            elif days_until_due < 30:
                risk += 0.1  # Due within a month
        
        # Time since service (20% weight)
        if days_since_service is not None:
            if days_since_service > 365:
                risk += 0.2
            elif days_since_service > 180:
                risk += 0.15
            elif days_since_service > 90:
                risk += 0.1
        
        # Failure rate (30% weight)
        risk += failure_rate * 0.3
        
        # Equipment status (10% weight)
        status_lower = str(equipment_status or '').lower()
        if 'down' in status_lower or 'failed' in status_lower:
            risk += 0.1
        elif 'degraded' in status_lower or 'warning' in status_lower:
            risk += 0.05
        
        # Clamp to [0, 1]
        return min(max(risk, 0.0), 1.0)


def engineer_equipment_features(
    snapshot_id: int,
    limit: Optional[int] = None
) -> pd.DataFrame:
    """
    Convenience function to extract and engineer equipment features.
    
    Args:
        snapshot_id: The feature snapshot ID to associate with
        limit: Optional row limit
    
    Returns:
        DataFrame ready for loading into ai.ai_equipment_features
    """
    transformer = EquipmentFeatureTransformer()
    df = transformer.extract_source_data(limit=limit)
    features = transformer.transform(df)
    
    # Add snapshot_id column
    features['snapshot_id'] = snapshot_id
    
    return features
