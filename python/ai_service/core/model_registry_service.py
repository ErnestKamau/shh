"""
Model Registry Service
Manages versioned ML model artifacts for the model types:
  - tat_prediction      : turnaround-time regression
  - equipment_maintenance: binary classification (maintenance due)
  - qc_anomaly          : anomaly / Westgard violation detection
    - decision_action     : decision support scoring classifier
"""
from __future__ import annotations

import json
from datetime import datetime
from typing import Any, Dict, List, Optional

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager


# Valid model types that the system understands
MODEL_TYPES = ("tat_prediction", "equipment_maintenance", "qc_anomaly", "decision_action", "llm")


class ModelRegistryService:
    """
    CRUD layer for ai.ai_model_registry.

    Usage
    -----
    service = ModelRegistryService()

    # Register a freshly trained model
    model_id = service.register(
        model_name="tat_rf_v2",
        model_type="tat_prediction",
        version="2.0.0",
        framework="sklearn",
        artifact_path="/app/models/tat_rf_v2.joblib",
        metrics={"rmse": 1.4, "r2": 0.87},
        training_rows=12500,
        feature_snapshot_id=42,
    )

    # Promote it to active
    service.activate(model_id)

    # Retrieve the active model path
    rec = service.get_active("tat_prediction")
    """

    # ------------------------------------------------------------------
    # Registration
    # ------------------------------------------------------------------

    def register(
        self,
        model_name: str,
        model_type: str,
        version: str,
        framework: Optional[str] = None,
        artifact_path: Optional[str] = None,
        feature_snapshot_id: Optional[int] = None,
        training_rows: Optional[int] = None,
        training_duration_seconds: Optional[float] = None,
        hyperparameters: Optional[Dict[str, Any]] = None,
        metrics: Optional[Dict[str, Any]] = None,
        is_active: bool = False,
    ) -> int:
        """
        Register a new model version.

        Returns
        -------
        int
            The new record's primary key.
        """
        if model_type not in MODEL_TYPES:
            raise ValueError(f"Unknown model_type '{model_type}'. Must be one of {MODEL_TYPES}")

        sql = text("""
            INSERT INTO ai.ai_model_registry (
                model_name, model_type, version, framework,
                artifact_path, feature_snapshot_id,
                training_rows, training_duration_seconds,
                hyperparameters, metrics,
                is_active, is_deprecated, created_at, updated_at
            ) VALUES (
                :model_name, :model_type, :version, :framework,
                :artifact_path, :feature_snapshot_id,
                :training_rows, :training_duration_seconds,
                :hyperparameters, :metrics,
                :is_active, FALSE, NOW(), NOW()
            )
            ON CONFLICT (model_type, version) DO UPDATE SET
                model_name                = EXCLUDED.model_name,
                framework                 = EXCLUDED.framework,
                artifact_path             = EXCLUDED.artifact_path,
                feature_snapshot_id       = EXCLUDED.feature_snapshot_id,
                training_rows             = EXCLUDED.training_rows,
                training_duration_seconds = EXCLUDED.training_duration_seconds,
                hyperparameters           = EXCLUDED.hyperparameters,
                metrics                   = EXCLUDED.metrics,
                is_active                 = CASE WHEN EXCLUDED.is_active THEN TRUE ELSE ai.ai_model_registry.is_active END,
                updated_at                = NOW()
            RETURNING id
        """)

        with db_manager.postgres_connection() as conn:
            row = conn.execute(
                sql,
                {
                    "model_name": model_name,
                    "model_type": model_type,
                    "version": version,
                    "framework": framework,
                    "artifact_path": artifact_path,
                    "feature_snapshot_id": feature_snapshot_id,
                    "training_rows": training_rows,
                    "training_duration_seconds": training_duration_seconds,
                    "hyperparameters": json.dumps(hyperparameters) if hyperparameters else None,
                    "metrics": json.dumps(metrics) if metrics else None,
                    "is_active": is_active,
                },
            ).fetchone()
            model_id: int = row[0]
            conn.commit()

        logger.info(f"Registered model '{model_name}' v{version} (id={model_id}, type={model_type})")
        return model_id

    # ------------------------------------------------------------------
    # Lifecycle management
    # ------------------------------------------------------------------

    def activate(self, model_id: int) -> None:
        """
        Promote a model to active, deactivating any previously active model
        of the same type.  Only one active model per model_type at a time.
        """
        with db_manager.postgres_connection() as conn:
            # Fetch model_type for this id
            row = conn.execute(
                text("SELECT model_type FROM ai.ai_model_registry WHERE id = :id"),
                {"id": model_id},
            ).fetchone()
            if not row:
                raise ValueError(f"Model id={model_id} not found in registry")

            model_type = row[0]

            # Deactivate all other models of same type
            conn.execute(
                text("""
                    UPDATE ai.ai_model_registry
                    SET is_active = FALSE, updated_at = NOW()
                    WHERE model_type = :model_type AND id != :id
                """),
                {"model_type": model_type, "id": model_id},
            )

            # Activate this one and record deploy timestamp
            conn.execute(
                text("""
                    UPDATE ai.ai_model_registry
                    SET is_active = TRUE, deployed_at = NOW(), updated_at = NOW()
                    WHERE id = :id
                """),
                {"id": model_id},
            )
            conn.commit()

        logger.info(f"Activated model id={model_id} (type={model_type})")

    def deprecate(self, model_id: int) -> None:
        """Mark a model as deprecated (will not be served)."""
        with db_manager.postgres_connection() as conn:
            conn.execute(
                text("""
                    UPDATE ai.ai_model_registry
                    SET is_deprecated = TRUE, is_active = FALSE, updated_at = NOW()
                    WHERE id = :id
                """),
                {"id": model_id},
            )
            conn.commit()
        logger.info(f"Deprecated model id={model_id}")

    # ------------------------------------------------------------------
    # Retrieval
    # ------------------------------------------------------------------

    def get_active(self, model_type: str) -> Optional[Dict[str, Any]]:
        """Return the currently active model record for a given type, or None."""
        with db_manager.postgres_connection() as conn:
            row = conn.execute(
                text("""
                    SELECT id, model_name, model_type, version, framework,
                           artifact_path, feature_snapshot_id,
                           training_rows, metrics, hyperparameters,
                           is_active, deployed_at, created_at, updated_at
                    FROM ai.ai_model_registry
                    WHERE model_type = :model_type
                      AND is_active = TRUE
                      AND is_deprecated = FALSE
                    ORDER BY deployed_at DESC NULLS LAST
                    LIMIT 1
                """),
                {"model_type": model_type},
            ).fetchone()
        return dict(row._mapping) if row else None

    def get_by_id(self, model_id: int) -> Optional[Dict[str, Any]]:
        """Fetch a single model record by primary key."""
        with db_manager.postgres_connection() as conn:
            row = conn.execute(
                text("SELECT * FROM ai.ai_model_registry WHERE id = :id"),
                {"id": model_id},
            ).fetchone()
        return dict(row._mapping) if row else None

    def list_by_type(self, model_type: str, include_deprecated: bool = False) -> List[Dict[str, Any]]:
        """List all model versions for a given type, newest first."""
        sql = """
            SELECT id, model_name, model_type, version, framework,
                   artifact_path, training_rows, metrics,
                   is_active, is_deprecated, deployed_at, created_at
            FROM ai.ai_model_registry
            WHERE model_type = :model_type
        """
        if not include_deprecated:
            sql += " AND is_deprecated = FALSE"
        sql += " ORDER BY created_at DESC"

        with db_manager.postgres_connection() as conn:
            rows = conn.execute(text(sql), {"model_type": model_type}).fetchall()
        return [dict(r._mapping) for r in rows]

    def list_all(self, include_deprecated: bool = False) -> List[Dict[str, Any]]:
        """List all model records."""
        sql = "SELECT * FROM ai.ai_model_registry"
        if not include_deprecated:
            sql += " WHERE is_deprecated = FALSE"
        sql += " ORDER BY model_type, created_at DESC"

        with db_manager.postgres_connection() as conn:
            rows = conn.execute(text(sql)).fetchall()
        return [dict(r._mapping) for r in rows]
