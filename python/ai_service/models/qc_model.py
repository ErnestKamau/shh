from __future__ import annotations

from pathlib import Path
from typing import Any, Optional

import pandas as pd
from loguru import logger

try:
    import joblib
except Exception:  # pragma: no cover - optional dependency at runtime
    joblib = None


class QCModel:
    MODEL_TYPE = "qc_anomaly"
    DEFAULT_MODEL_NAME = "qc_heuristic"
    DEFAULT_VERSION = "heuristic-1.0"
    FEATURE_COLUMNS = [
        "cv_percent",
        "z_score",
        "moving_avg_10",
        "moving_std_10",
        "within_control_limits",
        "westgard_violation",
        "outlier_flag",
    ]

    def __init__(self) -> None:
        self._loaded_path: Optional[str] = None
        self._loaded_model: Any = None

    def predict(self, features: dict[str, Any], registry_record: Optional[dict[str, Any]] = None) -> dict[str, Any]:
        clean = self._normalise_features(features)
        model = self._load_model(registry_record)

        if model is not None:
            anomaly_score = self._predict_with_model(model, clean)
            degraded_mode = False
        else:
            anomaly_score = self._heuristic_score(clean)
            degraded_mode = True

        risk_level = self._risk_level(anomaly_score)
        anomaly_flag = anomaly_score >= 0.6

        model_name = registry_record.get("model_name") if registry_record else self.DEFAULT_MODEL_NAME
        model_version = registry_record.get("version") if registry_record else self.DEFAULT_VERSION
        if degraded_mode:
            model_name = model_name or self.DEFAULT_MODEL_NAME
            model_version = model_version or self.DEFAULT_VERSION

        return {
            "prediction": {
                "anomaly_score": round(float(anomaly_score), 4),
                "anomaly_flag": anomaly_flag,
            },
            "confidence": round(self._estimate_confidence(clean, degraded_mode), 2),
            "risk_level": risk_level,
            "explanation": self.explain(clean, anomaly_score, degraded_mode),
            "model_name": model_name,
            "model_version": model_version,
            "degraded_mode": degraded_mode,
            "recommendation": self.recommend(anomaly_score, clean),
        }

    def explain(self, features: dict[str, Any], anomaly_score: float, degraded_mode: bool) -> list[str]:
        explanation = [
            f"CV%: {features['cv_percent']:.3f}",
            f"Z-score: {features['z_score']:.3f}",
            f"Moving std (10): {features['moving_std_10']:.3f}",
            f"Westgard violation: {bool(features['westgard_violation'])}",
            f"Outlier flag: {bool(features['outlier_flag'])}",
            f"Computed anomaly score: {anomaly_score:.2f}",
        ]
        if degraded_mode:
            explanation.append("No trained QC model available; used deterministic fallback scoring.")
        return explanation

    def recommend(self, anomaly_score: float, features: dict[str, Any]) -> str:
        if anomaly_score >= 0.8:
            return "Immediate QC review required: hold releases and run control rechecks."
        if anomaly_score >= 0.6:
            return "Investigate analyzer drift and verify controls before next batch."
        return "QC trend currently stable; continue routine monitoring."

    def _predict_with_model(self, model: Any, features: dict[str, Any]) -> float:
        frame = pd.DataFrame([features], columns=self.FEATURE_COLUMNS)
        if hasattr(model, "decision_function"):
            score = float(model.decision_function(frame)[0])
            # IsolationForest: higher is more normal; invert and squash to [0,1]
            return max(0.0, min(1.0, 1.0 / (1.0 + pow(2.71828, score))))
        return self._heuristic_score(features)

    def _heuristic_score(self, features: dict[str, Any]) -> float:
        score = 0.0
        score += min(features["cv_percent"] / 10.0, 0.3)
        score += min(abs(features["z_score"]) / 5.0, 0.3)
        score += min(features["moving_std_10"] / 5.0, 0.2)
        score += 0.15 if features["westgard_violation"] else 0.0
        score += 0.15 if features["outlier_flag"] else 0.0
        if not features["within_control_limits"]:
            score += 0.1
        return max(0.0, min(1.0, score))

    def _estimate_confidence(self, features: dict[str, Any], degraded_mode: bool) -> float:
        base = 0.7 if degraded_mode else 0.85
        if features["westgard_violation"] or features["outlier_flag"]:
            base += 0.05
        if abs(features["z_score"]) > 3.0:
            base += 0.03
        return max(0.4, min(0.98, base))

    @staticmethod
    def _risk_level(anomaly_score: float) -> str:
        if anomaly_score >= 0.8:
            return "high"
        if anomaly_score >= 0.6:
            return "medium"
        return "low"

    def _load_model(self, registry_record: Optional[dict[str, Any]]) -> Any:
        if joblib is None or not registry_record:
            return None

        path = registry_record.get("artifact_path")
        if not path:
            return None

        try:
            if self._loaded_model is not None and self._loaded_path == path:
                return self._loaded_model

            model_path = Path(path)
            if not model_path.exists():
                logger.warning(f"QC model artifact missing at {path}")
                return None

            self._loaded_model = joblib.load(model_path)
            self._loaded_path = path
            return self._loaded_model
        except Exception as exc:
            logger.warning(f"Failed to load QC model artifact {path}: {exc}")
            return None

    def _normalise_features(self, features: dict[str, Any]) -> dict[str, float | int | bool]:
        def f(name: str, default: float = 0.0) -> float:
            try:
                value = features.get(name, default)
                if value is None:
                    return float(default)
                return float(value)
            except Exception:
                return float(default)

        def b(name: str, default: bool = False) -> bool:
            value = features.get(name, default)
            return bool(value)

        return {
            "cv_percent": f("cv_percent", 0.0),
            "z_score": f("z_score", 0.0),
            "moving_avg_10": f("moving_avg_10", 0.0),
            "moving_std_10": f("moving_std_10", 0.0),
            "within_control_limits": b("within_control_limits", True),
            "westgard_violation": b("westgard_violation", False),
            "outlier_flag": b("outlier_flag", False),
        }
