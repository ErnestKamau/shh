import logging
from typing import Optional, Dict, Any, List
from sqlalchemy import text
from python.ai_service.core.model_registry_service import ModelRegistryService
from python.ai_service.core.inference_logger import InferenceLogger
from python.py_pipeline.core.database import db_manager
from python.ai_service.models import EquipmentModel, QCModel, TATModel

logger = logging.getLogger(__name__)

class PredictionService:
    def __init__(self):
        self.registry = ModelRegistryService()
        self.audit_log = InferenceLogger()
        self.models = {
            "tat": TATModel(),
            "equipment": EquipmentModel(),
            "qc": QCModel()
        }

    def predict(self, 
                model_type: str, 
                entity_id: int, 
                features: Optional[Dict[str, Any]] = None, 
                metadata: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """Orchestrate a single prediction: registry lookup -> predictor -> audit log."""
        
        # 1. Map canonical model type to predictor
        predictor_map = {
            "tat_prediction": "tat",
            "equipment_maintenance": "equipment",
            "qc_anomaly": "qc"
        }
        internal_type = predictor_map.get(model_type, model_type.split('_')[0])
        predictor = self.models.get(internal_type)
        
        if not predictor:
            raise ValueError(f"Unknown model type: {model_type}")

        # 2. Lookup active model in registry
        active_model = self._safe_registry_lookup(model_type)
        
        # 3. Resolve context
        input_features = features or {}
        feature_snapshot_id = input_features.get("snapshot_id")
        requested_by = self._resolve_requested_by(metadata)
        
        model_name = active_model.get("model_name") if active_model else predictor.DEFAULT_MODEL_NAME
        model_version = active_model.get("version") if active_model else predictor.DEFAULT_VERSION
        model_registry_id = active_model.get("id") if active_model else None

        # 4. Execute with Audit Logging
        with self.audit_log.timed_inference(
            model_name=model_name,
            model_version=model_version,
            entity_type=internal_type,
            entity_id=entity_id,
            input_features=input_features,
            input_data=metadata or {},
            request_source="fastapi_unified",
            requested_by=requested_by,
            model_registry_id=model_registry_id,
            feature_snapshot_id=feature_snapshot_id,
            actor=requested_by,
        ) as audit_ctx:
            result = predictor.predict(input_features, active_model)
            
            if not features:
                result["degraded_mode"] = True
                result["explanation"] = list(result.get("explanation", [])) + [
                    "No engineered feature snapshot was found; prediction used limited fallback inputs."
                ]

            audit_ctx["prediction"] = result["prediction"]
            audit_ctx["confidence"] = result["confidence"]
            audit_ctx["recommendation"] = result.get("recommendation")

        # Return standardized result
        return {
            "prediction": result["prediction"],
            "confidence": result["confidence"],
            "risk_level": result["risk_level"],
            "explanation": result["explanation"],
            "model_version": result["model_version"],
            "model_name": result.get("model_name"),
            "degraded_mode": result.get("degraded_mode", False),
            "recommendation": result.get("recommendation"),
            "feature_snapshot_id": feature_snapshot_id,
            "reasoning": result.get("reasoning"),
        }

    def _safe_registry_lookup(self, model_type: str) -> Optional[Dict[str, Any]]:
        try:
            return self.registry.get_active(model_type)
        except Exception as exc:
            logger.warning(f"Model registry lookup failed for {model_type}: {exc}")
            return None

    def _resolve_requested_by(self, metadata: Optional[Dict[str, Any]]) -> Optional[str]:
        if not metadata:
            return None
        for key in ("requested_by", "actor", "user", "user_id", "actor_id"):
            val = metadata.get(key)
            if val is not None:
                return str(val)
        return None

    def fetch_latest_features(self, table_name: str, entity_column: str, entity_id: int) -> Optional[Dict[str, Any]]:
        """Fetch the most recent feature snapshot for an entity."""
        with db_manager.postgres_connection() as conn:
            query = text(f"""
                SELECT * FROM ai.{table_name}
                WHERE {entity_column} = :id
                ORDER BY snapshot_id DESC, created_at DESC
                LIMIT 1
            """)
            row = conn.execute(query, {"id": entity_id}).fetchone()
        return dict(row._mapping) if row else None
