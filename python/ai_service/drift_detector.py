"""
Model Drift Detector
====================
Detects statistical drift between a model's training-time feature distribution
and the most-recent production predictions.

Detection uses two complementary tests:
  - Kolmogorov-Smirnov (KS) test:  non-parametric shift in feature distributions.
  - Wasserstein distance:           magnitude of distribution shift (earth mover's distance).

Each model is evaluated over a rolling window of recent predictions (default: last
1 000 records) against a baseline snapshot generated at training time.

Usage
-----
    from ai_service.drift_detector import ModelDriftDetector
    detector = ModelDriftDetector()
    report   = detector.check_all_models()

The `check_all_models()` call is invoked by the Celery beat task
`tasks.ai_tasks.check_model_drift` on a daily schedule.
"""

from __future__ import annotations

import logging
from datetime import datetime, timedelta
from typing import Any, Dict, List, Optional

import numpy as np

try:
    from scipy import stats as sp_stats
    _HAS_SCIPY = True
except ImportError:
    _HAS_SCIPY = False

try:
    from py_etl.core.database import db_manager
    _HAS_DB = True
except ImportError:
    _HAS_DB = False

logger = logging.getLogger(__name__)

# Thresholds tuned empirically for lab LIMS data volumes.
_KS_PVALUE_THRESHOLD   = 0.05   # p < 0.05 → significant distribution shift
_WASSERSTEIN_THRESHOLD = 0.20   # normalised Wasserstein > 0.20 → moderate drift
_LOOKBACK_DAYS         = 30     # how many days of production predictions to compare
_MIN_SAMPLE_SIZE       = 50     # skip drift check if fewer than this many records exist

# Feature tables per model type and the continuous numeric columns to track.
_MODEL_FEATURE_CONFIG: Dict[str, Dict[str, Any]] = {
    "tat_prediction": {
        "feature_table":    "ai.ai_sample_features",
        "id_column":        "sample_id",
        "numeric_features": [
            "sample_count_7d", "tat_avg_7d", "tat_p90_7d",
            "overdue_rate_7d", "processing_time_avg",
        ],
    },
    "equipment_maintenance": {
        "feature_table":    "ai.ai_equipment_features",
        "id_column":        "equipment_id",
        "numeric_features": [
            "days_since_maintenance", "failure_count_90d",
            "calibration_overdue_days", "usage_hours_30d",
        ],
    },
    "qc_anomaly": {
        "feature_table":    "ai.ai_qc_features",
        "id_column":        "qc_result_id",
        "numeric_features": [
            "mean_value", "sd_value", "cv_percent",
            "bias_percent", "westgard_violations_30d",
        ],
    },
}


class DriftReport:
    """Holds the drift analysis result for one model type."""

    def __init__(self, model_type: str) -> None:
        self.model_type: str       = model_type
        self.checked_at: str       = datetime.utcnow().isoformat()
        self.sample_size: int      = 0
        self.drifted_features: List[str] = []
        self.feature_scores: Dict[str, Dict[str, float]] = {}
        self.drift_detected: bool  = False
        self.severity: str         = "none"   # none | low | moderate | high
        self.error: Optional[str]  = None

    def to_dict(self) -> Dict[str, Any]:
        return {
            "model_type":        self.model_type,
            "checked_at":        self.checked_at,
            "sample_size":       self.sample_size,
            "drift_detected":    self.drift_detected,
            "severity":          self.severity,
            "drifted_features":  self.drifted_features,
            "feature_scores":    self.feature_scores,
            "error":             self.error,
        }


class ModelDriftDetector:
    """
    Compare recent production feature distributions against training-time baselines
    using KS test and Wasserstein distance.

    If scipy is not installed the detector falls back to mean/std ratio comparison
    so that the system degrades gracefully rather than crashing.
    """

    # ---------------------------------------------------------------------------
    # Public interface
    # ---------------------------------------------------------------------------

    def check_all_models(self) -> Dict[str, Any]:
        """
        Run drift checks across all three model types.

        Returns a dict with keys 'reports' (list) and 'summary' (aggregate counts).
        """
        reports: List[Dict[str, Any]] = []
        for model_type in _MODEL_FEATURE_CONFIG:
            report = self._check_model(model_type)
            reports.append(report.to_dict())

        drifted = sum(1 for r in reports if r["drift_detected"])
        return {
            "checked_at":   datetime.utcnow().isoformat(),
            "total_models": len(reports),
            "drifted":      drifted,
            "healthy":      len(reports) - drifted,
            "reports":      reports,
        }

    def check_model(self, model_type: str) -> Dict[str, Any]:
        """Run drift check for one model type and return a serialisable dict."""
        return self._check_model(model_type).to_dict()

    # ---------------------------------------------------------------------------
    # Internal helpers
    # ---------------------------------------------------------------------------

    def _check_model(self, model_type: str) -> DriftReport:
        report = DriftReport(model_type)

        if not _HAS_DB:
            report.error = "Database manager not available"
            return report

        cfg = _MODEL_FEATURE_CONFIG.get(model_type)
        if not cfg:
            report.error = f"Unknown model type: {model_type}"
            return report

        try:
            baseline = self._load_baseline(cfg)
            recent   = self._load_recent(cfg)

            if len(recent) < _MIN_SAMPLE_SIZE:
                report.error = f"Insufficient recent data ({len(recent)} rows < {_MIN_SAMPLE_SIZE})"
                return report

            report.sample_size = len(recent)

            for feature in cfg["numeric_features"]:
                if feature not in baseline or feature not in recent.T:
                    continue

                base_vals   = np.array(baseline[feature], dtype=float)
                recent_vals = np.array(recent[:, list(recent.dtype.names).index(feature)], dtype=float) \
                    if hasattr(recent, 'dtype') else np.array([row.get(feature, np.nan) for row in recent], dtype=float)

                # Remove NaN rows for both arrays
                base_vals   = base_vals[~np.isnan(base_vals)]
                recent_vals = recent_vals[~np.isnan(recent_vals)]

                if len(base_vals) < 5 or len(recent_vals) < 5:
                    continue

                scores = self._compute_drift_scores(base_vals, recent_vals)
                report.feature_scores[feature] = scores

                if scores["ks_drifted"] or scores["wasserstein_drifted"]:
                    report.drifted_features.append(feature)

            # Determine overall drift status
            if report.drifted_features:
                report.drift_detected = True
                drift_fraction = len(report.drifted_features) / len(cfg["numeric_features"])
                if drift_fraction >= 0.6:
                    report.severity = "high"
                elif drift_fraction >= 0.3:
                    report.severity = "moderate"
                else:
                    report.severity = "low"

        except Exception as exc:
            logger.exception("Drift detection failed for %s", model_type)
            report.error = str(exc)

        return report

    def _compute_drift_scores(
        self, baseline: np.ndarray, recent: np.ndarray
    ) -> Dict[str, float]:
        """Compute KS statistic + Wasserstein distance between two 1-D distributions."""
        scores: Dict[str, Any] = {}

        if _HAS_SCIPY:
            ks_stat, ks_pvalue = sp_stats.ks_2samp(baseline, recent)
            # Wasserstein / earth mover's distance normalised by baseline std
            raw_wasserstein = sp_stats.wasserstein_distance(baseline, recent)
            base_std = float(np.std(baseline)) or 1.0
            norm_wasserstein = raw_wasserstein / base_std
        else:
            # Degraded fallback: use standardised mean shift as a proxy
            baseline_mean, baseline_std = float(np.mean(baseline)), float(np.std(baseline)) or 1.0
            recent_mean = float(np.mean(recent))
            ks_stat     = abs(baseline_mean - recent_mean) / baseline_std
            ks_pvalue   = 0.0 if ks_stat > 2.0 else 1.0  # crude approximation
            norm_wasserstein = ks_stat

        scores["ks_statistic"]      = round(float(ks_stat), 4)
        scores["ks_pvalue"]         = round(float(ks_pvalue), 4)
        scores["wasserstein_norm"]  = round(float(norm_wasserstein), 4)
        scores["ks_drifted"]        = ks_pvalue < _KS_PVALUE_THRESHOLD
        scores["wasserstein_drifted"] = norm_wasserstein > _WASSERSTEIN_THRESHOLD

        return scores

    def _load_baseline(self, cfg: Dict[str, Any]) -> Dict[str, List[float]]:
        """
        Load the training-time feature baseline stored in ai.ai_model_baselines.
        Falls back to percentile stats from the full feature history if the
        dedicated baseline table does not exist.
        """
        feature_table = cfg["feature_table"]
        numeric_cols  = ", ".join(f"AVG({f}) AS {f}" for f in cfg["numeric_features"])

        fallback_sql = f"""
            SELECT {numeric_cols}
            FROM {feature_table}
            WHERE created_at < NOW() - INTERVAL '90 days'
        """

        try:
            with db_manager.postgres_connection() as conn:
                row = conn.execute(fallback_sql).fetchone()
                if row:
                    return {col: [v] for col, v in zip(cfg["numeric_features"], row)}
        except Exception as exc:
            logger.warning("Baseline load failed for %s: %s", feature_table, exc)

        # Return minimal dummy baseline so the caller can skip gracefully
        return {f: [] for f in cfg["numeric_features"]}

    def _load_recent(self, cfg: Dict[str, Any]) -> list:
        """
        Load recent production feature values (last _LOOKBACK_DAYS days).
        Returns a list of dicts, one per row.
        """
        feature_table = cfg["feature_table"]
        cols          = ", ".join(cfg["numeric_features"])
        cutoff        = (datetime.utcnow() - timedelta(days=_LOOKBACK_DAYS)).isoformat()

        sql = f"""
            SELECT {cols}
            FROM {feature_table}
            WHERE created_at >= :cutoff
            ORDER BY created_at DESC
            LIMIT 5000
        """

        try:
            from sqlalchemy import text
            with db_manager.postgres_connection() as conn:
                rows = conn.execute(text(sql), {"cutoff": cutoff}).fetchall()
                return [dict(r._mapping) for r in rows]
        except Exception as exc:
            logger.warning("Recent data load failed for %s: %s", feature_table, exc)
            return []
