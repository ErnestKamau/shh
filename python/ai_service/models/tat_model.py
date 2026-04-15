from __future__ import annotations

from pathlib import Path
from typing import Any, Optional

import pandas as pd
from loguru import logger

try:
    import joblib
except Exception:  # pragma: no cover - optional dependency at runtime
    joblib = None


class TATModel:
    MODEL_TYPE = "tat_prediction"
    DEFAULT_MODEL_NAME = "tat_heuristic"
    DEFAULT_VERSION = "heuristic-1.0"
    FEATURE_COLUMNS = [
        "stage_count",
        "priority_score",
        "processing_delay_days",
        "approval_delay_days",
        "rework_flag",
        "is_qc_batch",
    ]

    def __init__(self) -> None:
        self._loaded_path: Optional[str] = None
        self._loaded_model: Any = None

    def predict(self, features: dict[str, Any], registry_record: Optional[dict[str, Any]] = None) -> dict[str, Any]:
        clean = self._normalise_features(features)
        model = self._load_model(registry_record)

        degraded_mode = model is None
        prediction_value = self._predict_with_model(model, clean) if model is not None else self._heuristic_prediction(clean)
        confidence = self._estimate_confidence(clean, degraded_mode=degraded_mode)
        risk_level = self._risk_level(prediction_value)

        model_name = registry_record.get("model_name") if registry_record else self.DEFAULT_MODEL_NAME
        model_version = registry_record.get("version") if registry_record else self.DEFAULT_VERSION
        if degraded_mode:
            model_name = model_name or self.DEFAULT_MODEL_NAME
            model_version = model_version or self.DEFAULT_VERSION

        return {
            "prediction": round(float(prediction_value), 2),
            "confidence": round(confidence, 2),
            "risk_level": risk_level,
            "explanation": self.explain(clean, prediction_value, degraded_mode),
            "model_name": model_name,
            "model_version": model_version,
            "degraded_mode": degraded_mode,
            "recommendation": self.recommend(prediction_value, clean),
        }

    def explain(self, features: dict[str, Any], prediction_value: float, degraded_mode: bool) -> list[str]:
        explanation = [
            f"Workflow stages: {int(features['stage_count'])}",
            f"Priority score: {int(features['priority_score'])}",
        ]

        if features["processing_delay_days"] > 0:
            explanation.append(f"Processing delays add {features['processing_delay_days']:.2f} days")
        if features["approval_delay_days"] > 0:
            explanation.append(f"Approval delays add {features['approval_delay_days']:.2f} days")
        if features["rework_flag"]:
            explanation.append("Rework history increases turnaround risk")
        if features["is_qc_batch"]:
            explanation.append("QC batch handling adds coordination overhead")
        if degraded_mode:
            explanation.append("Fallback heuristic used because no active model artifact was available")
        explanation.append(f"Estimated TAT: {prediction_value:.2f} days")
        return explanation

    def recommend(self, prediction_value: float, features: dict[str, Any]) -> str:
        if prediction_value >= 7:
            return "Escalate to section lead and prioritise approval bottlenecks."
        if features["rework_flag"]:
            return "Review rework source and clear repeat processing blockers."
        if prediction_value >= 4:
            return "Monitor progress daily and pre-allocate review capacity."
        return "No intervention required beyond normal monitoring."

    def _load_model(self, registry_record: Optional[dict[str, Any]]) -> Any:
        artifact_path = (registry_record or {}).get("artifact_path")
        if not artifact_path or joblib is None:
            return None

        resolved_path = self._resolve_artifact_path(artifact_path)
        if resolved_path is None or not resolved_path.exists():
            logger.warning(f"TATModel: artifact path not found: {artifact_path}")
            return None

        if self._loaded_model is not None and self._loaded_path == str(resolved_path):
            return self._loaded_model

        try:
            self._loaded_model = joblib.load(resolved_path)
            self._loaded_path = str(resolved_path)
            return self._loaded_model
        except Exception as exc:  # pragma: no cover - runtime dependency path
            logger.warning(f"TATModel: unable to load artifact '{resolved_path}': {exc}")
            return None

    def _predict_with_model(self, model: Any, features: dict[str, Any]) -> float:
        frame = pd.DataFrame([{column: features[column] for column in self.FEATURE_COLUMNS}])
        prediction = model.predict(frame)[0]
        return max(0.0, float(prediction))

    def _heuristic_prediction(self, features: dict[str, Any]) -> float:
        base_days = 1.5
        base_days += features["stage_count"] * 0.45
        base_days += features["processing_delay_days"]
        base_days += features["approval_delay_days"] * 0.8
        base_days += 1.25 if features["rework_flag"] else 0.0
        base_days += 0.5 if features["is_qc_batch"] else 0.0
        base_days -= min(features["priority_score"] * 0.2, 1.0)
        return max(0.25, base_days)

    def _estimate_confidence(self, features: dict[str, Any], degraded_mode: bool) -> float:
        confidence = 0.88 if not degraded_mode else 0.67
        if features["processing_delay_days"] == 0 and features["approval_delay_days"] == 0:
            confidence += 0.04
        if features["stage_count"] <= 0:
            confidence -= 0.08
        if features["rework_flag"]:
            confidence -= 0.05
        return max(0.35, min(confidence, 0.97))

    def _risk_level(self, prediction_value: float) -> str:
        if prediction_value >= 7:
            return "High"
        if prediction_value >= 4:
            return "Medium"
        return "Low"

    def _normalise_features(self, features: dict[str, Any]) -> dict[str, Any]:
        return {
            "stage_count": max(0, int(features.get("stage_count") or 0)),
            "priority_score": max(0, int(features.get("priority_score") or 0)),
            "processing_delay_days": float(features.get("processing_delay_days") or 0.0),
            "approval_delay_days": float(features.get("approval_delay_days") or 0.0),
            "rework_flag": bool(features.get("rework_flag") or False),
            "is_qc_batch": bool(features.get("is_qc_batch") or False),
        }

    def _resolve_artifact_path(self, artifact_path: str) -> Optional[Path]:
        candidate = Path(artifact_path)
        if candidate.is_absolute():
            return candidate

        project_root = Path(__file__).resolve().parents[3]
        candidates = [
            project_root / artifact_path,
            project_root / "models" / artifact_path,
        ]
        for item in candidates:
            if item.exists():
                return item
        return candidates[0]
