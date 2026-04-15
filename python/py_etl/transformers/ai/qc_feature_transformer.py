"""
QC Feature Transformer
Engineers features from qc_processed_results for anomaly detection and trend analysis.
"""
from __future__ import annotations

from typing import Optional

import pandas as pd
import numpy as np
from loguru import logger

from python.py_etl.core.database import DatabaseManager
from python.py_etl.config.config import settings


class QCFeatureTransformer:
    """
    Transforms QC results into engineered features for ML models.
    
    Key Features:
    - CV% for variation analysis
    - Z-scores for outlier detection
    - Moving averages and standard deviations
    - Trend direction indicators
    - Westgard rule violations
    - Control limit checks
    """

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        """Initialize the transformer with database access."""
        self.db_manager = db_manager or DatabaseManager()
        self.reporting_schema = settings.ai_reporting_schema

    def extract_source_data(self, limit: Optional[int] = None) -> pd.DataFrame:
        """
        Extract QC processed results from the reporting schema.
        
        Args:
            limit: Optional row limit for testing
        
        Returns:
            DataFrame with QC results
        """
        limit_clause = f"LIMIT {limit}" if limit else ""
        
        query = f"""
            SELECT 
                source_id as qc_result_id,
                analyte_id,
                sample_type_id as qc_level,
                robust_mean,
                robust_standard_deviation,
                robust_median,
                robust_cv,
                robust_cv_percentage,
                source_created_at
            FROM {self.reporting_schema}.qc_processed_results
            ORDER BY analyte_id, source_created_at
            {limit_clause}
        """
        
        with self.db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)
        
        logger.info(f"Extracted {len(df)} QC results for feature engineering")
        return df

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Transform QC data into engineered features.
        
        Args:
            df: Raw QC results DataFrame
        
        Returns:
            DataFrame with engineered features matching ai.ai_qc_features schema
        """
        if df.empty:
            logger.warning("Empty DataFrame provided for transformation")
            return pd.DataFrame()
        
        logger.info(f"Engineering features for {len(df)} QC results")
        
        # Sort by analyte and date for time-series features
        df = df.sort_values(['analyte_id', 'source_created_at'])
        
        features = []
        
        # Group by analyte for time-series calculations
        for analyte_id, group in df.groupby('analyte_id'):
            try:
                # Calculate historical mean and std for z-scores
                historical_mean = group['robust_mean'].mean()
                historical_std = group['robust_mean'].std()
                
                for idx, row in group.iterrows():
                    try:
                        # CV percentage
                        cv_percent = self._safe_float(row.get('robust_cv_percentage'))
                        
                        # Calculate z-score
                        current_mean = self._safe_float(row.get('robust_mean'))
                        z_score = None
                        if current_mean is not None and historical_std > 0:
                            z_score = (current_mean - historical_mean) / historical_std
                        
                        # Calculate moving averages (last 10 results)
                        window_size = min(10, len(group))
                        position = group.index.get_loc(idx)
                        
                        if position >= window_size - 1:
                            window_data = group.iloc[position - window_size + 1:position + 1]['robust_mean']
                            moving_avg = window_data.mean()
                            moving_std = window_data.std()
                        else:
                            moving_avg = None
                            moving_std = None
                        
                        # Determine trend direction
                        trend_direction = self._determine_trend(group, position)
                        
                        # Check control limits (using CV%)
                        within_control = True
                        if cv_percent is not None:
                            within_control = cv_percent < 25.0  # Typical QC limit
                        
                        # Westgard rule violation (simplified)
                        westgard_violation = False
                        if z_score is not None:
                            westgard_violation = abs(z_score) > 2.0  # 2SD rule
                        
                        # Outlier flag (z-score > 3)
                        outlier_flag = False
                        if z_score is not None:
                            outlier_flag = abs(z_score) > 3.0
                        
                        features.append({
                            'qc_result_id': row['qc_result_id'],
                            'snapshot_id': None,  # Will be added by caller
                            'cv_percent': cv_percent,
                            'z_score': z_score,
                            'moving_avg_10': moving_avg,
                            'moving_std_10': moving_std,
                            'trend_direction': trend_direction,
                            'within_control_limits': within_control,
                            'westgard_violation': westgard_violation,
                            'outlier_flag': outlier_flag,
                            'analyte_id': row['analyte_id'],
                            'qc_level': str(row.get('qc_level', '')),
                            'feature_version': 'v1.0',
                        })
                        
                    except Exception as exc:
                        logger.warning(
                            f"Failed to transform QC result {row.get('qc_result_id')}: {exc}"
                        )
                        continue
                        
            except Exception as exc:
                logger.warning(f"Failed to process analyte {analyte_id}: {exc}")
                continue
        
        result_df = pd.DataFrame(features)
        logger.info(f"Engineered {len(result_df)} QC features")
        
        return result_df

    @staticmethod
    def _safe_float(value) -> Optional[float]:
        """Safely convert value to float."""
        if pd.isna(value) or value is None:
            return None
        try:
            return float(value)
        except (ValueError, TypeError):
            return None

    @staticmethod
    def _determine_trend(group: pd.DataFrame, position: int) -> str:
        """
        Determine trend direction based on recent data points.
        
        Returns:
            'up', 'down', or 'stable'
        """
        if position < 2:
            return 'stable'
        
        try:
            # Look at last 3 points
            window_size = min(3, position + 1)
            recent_data = group.iloc[position - window_size + 1:position + 1]['robust_mean']
            
            # Calculate linear regression slope
            x = np.arange(len(recent_data))
            y = recent_data.values
            
            if len(x) < 2 or np.isnan(y).any():
                return 'stable'
            
            slope = np.polyfit(x, y, 1)[0]
            
            # Threshold for trend detection (relative to mean)
            mean_val = np.mean(y)
            if mean_val == 0:
                return 'stable'
            
            relative_slope = slope / mean_val
            
            if relative_slope > 0.05:
                return 'up'
            elif relative_slope < -0.05:
                return 'down'
            else:
                return 'stable'
                
        except Exception:
            return 'stable'


def engineer_qc_features(
    snapshot_id: int,
    limit: Optional[int] = None
) -> pd.DataFrame:
    """
    Convenience function to extract and engineer QC features.
    
    Args:
        snapshot_id: The feature snapshot ID to associate with
        limit: Optional row limit
    
    Returns:
        DataFrame ready for loading into ai.ai_qc_features
    """
    transformer = QCFeatureTransformer()
    df = transformer.extract_source_data(limit=limit)
    features = transformer.transform(df)
    
    # Add snapshot_id column
    if not features.empty:
        features['snapshot_id'] = snapshot_id
    
    return features
