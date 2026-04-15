from __future__ import annotations

from pathlib import Path
from typing import Any, Optional

import pandas as pd
from loguru import logger

try:
    import joblib
except Exception:  # pragma: no cover - optional dependency at runtime
    joblib = None


class EquipmentModel:
    MODEL_TYPE = "equipment_maintenance"
    DEFAULT_MODEL_NAME = "equipment_heuristic"
    DEFAULT_VERSION = "heuristic-1.0"
    FEATURE_COLUMNS = [
        "days_since_service",
        "days_until_due",
        "failure_rate",
        "usage_frequency",
        "maintenance_count",
        "calibration_count",
        "verification_count",
        "risk_score",
    ]

    def __init__(self) -> None:
        self._loaded_path: Optional[str] = None
        self._loaded_model: Any = None

    def predict(self, features: dict[str, Any], registry_record: Optional[dict[str, Any]] = None) -> dict[str, Any]:
        clean = self._normalise_features(features)
        model = self._load_model(registry_record)

        if model is not None:
            probability = self._predict_probability(model, clean)
            degraded_mode = False
        else:
            probability = self._heuristic_probability(clean)
            degraded_mode = True

        days_to_failure = self._estimate_days_to_failure(clean, probability)
        risk_level = self._risk_level(probability)

        model_name = registry_record.get("model_name") if registry_record else self.DEFAULT_MODEL_NAME
        model_version = registry_record.get("version") if registry_record else self.DEFAULT_VERSION
        if degraded_mode:
            model_name = model_name or self.DEFAULT_MODEL_NAME
            model_version = model_version or self.DEFAULT_VERSION

        return {
            "prediction": {
                "failure_probability": round(probability, 4),
                "days_to_failure": days_to_failure,
            },
            "confidence": round(self._estimate_confidence(clean, degraded_mode), 2),
            "risk_level": risk_level,
            "explanation": self.explain(clean, probability, days_to_failure, degraded_mode),
            "model_name": model_name,
            "model_version": model_version,
            "degraded_mode": degraded_mode,
            "recommendation": self.recommend(probability, clean),
        }

    def explain(
        self,
        features: dict[str, Any],
        probability: float,
        days_to_failure: int,
        degraded_mode: bool,
    ) -> list[str]:
        explanation = [
            f"Days since service: {features['days_since_service']}",
            f"Days until due: {features['days_until_due']}",
            f"Usage frequency: {features['usage_frequency']:.2f}",
            f"Observed failure rate: {features['failure_rate']:.2f}",
            f"Estimated days to failure: {days_to_failure}",
        ]
        if features["risk_score"] > 0:
            explanation.append(f"Existing engineered risk score: {features['risk_score']:.2f}")
        if degraded_mode:
            explanation.append("Fallback heuristic used because no active model artifact was available")
        explanation.append(f"Predicted failure probability: {probability:.2%}")
        return explanation

    def recommend(self, probability: float, features: dict[str, Any]) -> str:
        if probability >= 0.75 or features["days_until_due"] <= 0:
            return "Schedule maintenance immediately and suspend non-essential usage."
        if probability >= 0.45:
            return "Plan preventive maintenance in the next available service window."
        return "Continue routine monitoring and keep maintenance calendar current."

    def _load_model(self, registry_record: Optional[dict[str, Any]]) -> Any:
        artifact_path = (registry_record or {}).get("artifact_path")
        if not artifact_path or joblib is None:
            return None

        resolved_path = self._resolve_artifact_path(artifact_path)
        if resolved_path is None or not resolved_path.exists():
            logger.warning(f"EquipmentModel: artifact path not found: {artifact_path}")
            return None

        if self._loaded_model is not None and self._loaded_path == str(resolved_path):
            return self._loaded_model

        try:
            self._loaded_model = joblib.load(resolved_path)
            self._loaded_path = str(resolved_path)
            return self._loaded_model
        except Exception as exc:  # pragma: no cover - runtime dependency path
            logger.warning(f"EquipmentModel: unable to load artifact '{resolved_path}': {exc}")
            return None

    def _predict_probability(self, model: Any, features: dict[str, Any]) -> float:
        frame = pd.DataFrame([{column: features[column] for column in self.FEATURE_COLUMNS}])
        if hasattr(model, "predict_proba"):
            return float(model.predict_proba(frame)[0][-1])
        raw = model.predict(frame)[0]
        return max(0.0, min(float(raw), 1.0))

    def _heuristic_probability(self, features: dict[str, Any]) -> float:
        score = 0.0
        score += min(features["days_since_service"] / 365.0, 1.0) * 0.25
        score += 0.2 if features["days_until_due"] <= 0 else max(0.0, 0.15 - (features["days_until_due"] / 365.0))
        score += min(features["failure_rate"], 1.0) * 0.25
        score += min(features["usage_frequency"] / 100.0, 1.0) * 0.15
        score += min(features["risk_score"] / 10.0, 1.0) * 0.2
        return max(0.02, min(score, 0.98))

    def _estimate_days_to_failure(self, features: dict[str, Any], probability: float) -> int:
        if features["days_until_due"] > 0:
            baseline = min(features["days_until_due"], 120)
        else:
            baseline = 30
        return max(0, int(round(baseline * (1.0 - probability))))

    def _estimate_confidence(self, features: dict[str, Any], degraded_mode: bool) -> float:
        confidence = 0.84 if not degraded_mode else 0.64
        if features["risk_score"] > 0:
            confidence += 0.06
        if features["days_until_due"] <= 0:
            confidence += 0.04
        return max(0.35, min(confidence, 0.96))

    def _risk_level(self, probability: float) -> str:
        if probability >= 0.75:
            return "High"
        if probability >= 0.45:
            return "Medium"
        return "Low"

    def _normalise_features(self, features: dict[str, Any]) -> dict[str, Any]:
        return {
            "days_since_service": max(0, int(features.get("days_since_service") or 0)),
            "days_until_due": int(features.get("days_until_due") or 0),
            "failure_rate": float(features.get("failure_rate") or 0.0),
            "usage_frequency": float(features.get("usage_frequency") or 0.0),
            "maintenance_count": max(0, int(features.get("maintenance_count") or 0)),
            "calibration_count": max(0, int(features.get("calibration_count") or 0)),
            "verification_count": max(0, int(features.get("verification_count") or 0)),
            "risk_score": float(features.get("risk_score") or 0.0),
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
