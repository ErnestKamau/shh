from __future__ import annotations

from datetime import datetime
from pathlib import Path
from typing import Any, Dict, Optional

import joblib
import numpy as np
from loguru import logger
from sklearn.ensemble import RandomForestClassifier
from sklearn.feature_extraction import DictVectorizer
from sklearn.metrics import accuracy_score, roc_auc_score

from python.ai_service.core.ai_database_service import AIDataService
from python.ai_service.core.model_registry_service import ModelRegistryService


ARTIFACT_DIR = Path(__file__).resolve().parents[3] / "models"


class DecisionModelService:
    """Train and serve lightweight decision-scoring model."""

    MODEL_TYPE = "decision_action"
    MODEL_NAME = "decision_action_random_forest"

    def __init__(self) -> None:
        self.registry = ModelRegistryService()
        self.data = AIDataService()
        ARTIFACT_DIR.mkdir(parents=True, exist_ok=True)
        self._cache: Dict[str, Any] = {"path": None, "bundle": None}

    def train(
        self,
        module: str = "samples",
        days: int = 90,
        min_rows: int = 30,
        version: Optional[str] = None,
        activate: bool = True,
    ) -> Dict[str, Any]:
        dataset = self.data.build_decision_training_dataset(module=module, days=days, min_rows=min_rows)
        rows = dataset.get("rows", [])
        if not dataset.get("min_rows_met"):
            raise ValueError(
                f"Insufficient training rows for module={module}: found {len(rows)}, require at least {min_rows}"
            )

        X_dict = [r["features"] for r in rows if r.get("features")]
        y = np.array([int(r["label"]) for r in rows if r.get("features") is not None], dtype=int)

        if len(X_dict) < min_rows:
            raise ValueError(f"Insufficient usable feature rows: {len(X_dict)} < {min_rows}")
        if len(set(y.tolist())) < 2:
            raise ValueError("Decision model training requires at least two classes in labels")

        vectorizer = DictVectorizer(sparse=False)
        X = vectorizer.fit_transform(X_dict)

        model = RandomForestClassifier(
            n_estimators=200,
            max_depth=8,
            min_samples_leaf=2,
            random_state=42,
            class_weight="balanced",
            n_jobs=-1,
        )
        model.fit(X, y)

        probs = model.predict_proba(X)[:, 1]
        preds = (probs >= 0.5).astype(int)

        metrics: Dict[str, float] = {
            "accuracy": float(accuracy_score(y, preds)),
            "positive_rate": float(y.mean()),
        }
        if len(set(y.tolist())) > 1:
            metrics["roc_auc"] = float(roc_auc_score(y, probs))

        chosen_version = version or datetime.utcnow().strftime("%Y%m%d%H%M%S")
        artifact_path = ARTIFACT_DIR / f"decision_action_{chosen_version}.joblib"

        bundle = {
            "model": model,
            "vectorizer": vectorizer,
            "module": module,
            "version": chosen_version,
            "feature_names": vectorizer.feature_names_,
        }
        joblib.dump(bundle, artifact_path)

        model_id = self.registry.register(
            model_name=self.MODEL_NAME,
            model_type=self.MODEL_TYPE,
            version=chosen_version,
            framework="sklearn",
            artifact_path=str(artifact_path),
            training_rows=int(len(X_dict)),
            hyperparameters={
                "n_estimators": 200,
                "max_depth": 8,
                "min_samples_leaf": 2,
                "class_weight": "balanced",
            },
            metrics=metrics,
            is_active=activate,
        )

        if activate:
            self.registry.activate(model_id)

        self._cache = {"path": str(artifact_path), "bundle": bundle}
        payload = {
            "status": "success",
            "model_type": self.MODEL_TYPE,
            "model_name": self.MODEL_NAME,
            "module": module,
            "version": chosen_version,
            "artifact_path": str(artifact_path),
            "training_rows": int(len(X_dict)),
            "metrics": metrics,
            "model_registry_id": model_id,
        }
        logger.info(f"Decision model trained: {payload}")
        return payload

    def predict_score(self, features: Dict[str, Any]) -> Optional[float]:
        bundle = self._load_active_bundle()
        if not bundle:
            return None

        vectorizer: DictVectorizer = bundle["vectorizer"]
        model: RandomForestClassifier = bundle["model"]

        numeric_features: Dict[str, float] = {}
        for key, value in (features or {}).items():
            if isinstance(value, bool):
                numeric_features[key] = 1.0 if value else 0.0
            elif isinstance(value, (int, float)):
                numeric_features[key] = float(value)

        if not numeric_features:
            return None

        X = vectorizer.transform([numeric_features])
        score = float(model.predict_proba(X)[0][1])
        return max(0.0, min(1.0, score))

    def get_active_model_metadata(self) -> Optional[Dict[str, Any]]:
        return self.registry.get_active(self.MODEL_TYPE)

    def _load_active_bundle(self) -> Optional[Dict[str, Any]]:
        active = self.registry.get_active(self.MODEL_TYPE)
        if not active:
            return None

        artifact_path = active.get("artifact_path")
        if not artifact_path:
            return None

        if self._cache.get("path") == artifact_path and self._cache.get("bundle") is not None:
            return self._cache["bundle"]

        path = Path(artifact_path)
        if not path.exists():
            logger.warning(f"Decision model artifact not found: {artifact_path}")
            return None

        bundle = joblib.load(path)
        self._cache = {"path": artifact_path, "bundle": bundle}
        return bundle
