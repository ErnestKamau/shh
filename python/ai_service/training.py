from __future__ import annotations

from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, Optional

import joblib
import pandas as pd
from loguru import logger
from sklearn.ensemble import IsolationForest
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score, roc_auc_score
from sqlalchemy import text

from python.ai_service.core.model_registry_service import ModelRegistryService
from python.py_pipeline.core.database import db_manager


ARTIFACT_DIR = Path(__file__).resolve().parents[2] / "models"


@dataclass
class TrainingResult:
    model_type: str
    model_name: str
    version: str
    artifact_path: str
    training_rows: int
    feature_snapshot_id: Optional[int]
    metrics: Dict[str, Any]
    model_registry_id: int


class ModelTrainer:
    def __init__(self) -> None:
        self.registry = ModelRegistryService()
        ARTIFACT_DIR.mkdir(parents=True, exist_ok=True)

    def train_tat_model(self, version: Optional[str] = None, activate: bool = True) -> TrainingResult:
        query = text(
            """
            SELECT sample_id, snapshot_id, stage_count, priority_score,
                   processing_delay_days, approval_delay_days,
                   rework_flag, is_qc_batch, tat_days
            FROM ai.ai_sample_features
            WHERE tat_days IS NOT NULL
            """
        )
        with db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)

        if df.empty:
            raise ValueError("No training rows found in ai.ai_sample_features with tat_days")

        features = [
            "stage_count",
            "priority_score",
            "processing_delay_days",
            "approval_delay_days",
            "rework_flag",
            "is_qc_batch",
        ]

        train_df = df.copy()
        train_df["rework_flag"] = train_df["rework_flag"].fillna(False).astype(int)
        train_df["is_qc_batch"] = train_df["is_qc_batch"].fillna(False).astype(int)
        train_df["stage_count"] = train_df["stage_count"].fillna(0)
        train_df["priority_score"] = train_df["priority_score"].fillna(0)
        train_df["processing_delay_days"] = train_df["processing_delay_days"].fillna(0.0)
        train_df["approval_delay_days"] = train_df["approval_delay_days"].fillna(0.0)

        X = train_df[features]
        y = train_df["tat_days"]

        model = RandomForestRegressor(
            n_estimators=250,
            max_depth=12,
            min_samples_leaf=2,
            random_state=42,
            n_jobs=-1,
        )
        model.fit(X, y)

        preds = model.predict(X)
        rmse = float(mean_squared_error(y, preds) ** 0.5)
        mae = float(mean_absolute_error(y, preds))
        r2 = float(r2_score(y, preds))

        chosen_version = version or datetime.utcnow().strftime("%Y%m%d%H%M%S")
        filename = f"tat_prediction_{chosen_version}.joblib"
        artifact_path = ARTIFACT_DIR / filename
        joblib.dump(model, artifact_path)

        snapshot_id = int(train_df["snapshot_id"].max()) if "snapshot_id" in train_df.columns else None

        model_name = "tat_random_forest"
        model_registry_id = self.registry.register(
            model_name=model_name,
            model_type="tat_prediction",
            version=chosen_version,
            framework="sklearn",
            artifact_path=str(artifact_path),
            feature_snapshot_id=snapshot_id,
            training_rows=int(len(train_df)),
            hyperparameters={
                "n_estimators": 250,
                "max_depth": 12,
                "min_samples_leaf": 2,
                "random_state": 42,
            },
            metrics={
                "rmse": rmse,
                "mae": mae,
                "r2": r2,
            },
        )

        if activate:
            self._activate_registry_model(model_registry_id=model_registry_id, model_type="tat_prediction")

        logger.info(
            "Trained and registered TAT model",
            model_registry_id=model_registry_id,
            version=chosen_version,
            artifact_path=str(artifact_path),
            rows=len(train_df),
        )

        return TrainingResult(
            model_type="tat_prediction",
            model_name=model_name,
            version=chosen_version,
            artifact_path=str(artifact_path),
            training_rows=int(len(train_df)),
            feature_snapshot_id=snapshot_id,
            metrics={"rmse": rmse, "mae": mae, "r2": r2},
            model_registry_id=model_registry_id,
        )

    def train_equipment_model(self, version: Optional[str] = None, activate: bool = True) -> TrainingResult:
        query = text(
            """
            SELECT equipment_id, snapshot_id,
                   days_since_service, days_until_due,
                   failure_rate, usage_frequency,
                   maintenance_count, calibration_count, verification_count,
                   risk_score
            FROM ai.ai_equipment_features
            """
        )
        with db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)

        if df.empty:
            raise ValueError("No training rows found in ai.ai_equipment_features")

        train_df = df.copy()
        numeric_columns = [
            "days_since_service",
            "days_until_due",
            "failure_rate",
            "usage_frequency",
            "maintenance_count",
            "calibration_count",
            "verification_count",
            "risk_score",
        ]
        for column in numeric_columns:
            train_df[column] = pd.to_numeric(train_df[column], errors="coerce").fillna(0.0)

        train_df["failure_flag"] = (
            (train_df["failure_rate"] >= 0.20)
            | (train_df["days_until_due"] <= 0)
            | (train_df["risk_score"] >= 7.5)
        ).astype(int)

        if train_df["failure_flag"].nunique() < 2:
            raise ValueError("Equipment training requires at least two classes in derived failure_flag")

        features = [
            "days_since_service",
            "days_until_due",
            "failure_rate",
            "usage_frequency",
            "maintenance_count",
            "calibration_count",
            "verification_count",
            "risk_score",
        ]

        X = train_df[features]
        y = train_df["failure_flag"]

        model = LogisticRegression(max_iter=500)
        model.fit(X, y)

        probabilities = model.predict_proba(X)[:, 1]
        auc = float(roc_auc_score(y, probabilities))
        positive_rate = float(y.mean())

        chosen_version = version or datetime.utcnow().strftime("%Y%m%d%H%M%S")
        filename = f"equipment_maintenance_{chosen_version}.joblib"
        artifact_path = ARTIFACT_DIR / filename
        joblib.dump(model, artifact_path)

        snapshot_id = int(train_df["snapshot_id"].max()) if "snapshot_id" in train_df.columns else None

        model_name = "equipment_logistic_regression"
        model_registry_id = self.registry.register(
            model_name=model_name,
            model_type="equipment_maintenance",
            version=chosen_version,
            framework="sklearn",
            artifact_path=str(artifact_path),
            feature_snapshot_id=snapshot_id,
            training_rows=int(len(train_df)),
            hyperparameters={"max_iter": 500},
            metrics={
                "roc_auc": auc,
                "positive_rate": positive_rate,
            },
        )

        if activate:
            self._activate_registry_model(model_registry_id=model_registry_id, model_type="equipment_maintenance")

        logger.info(
            "Trained and registered equipment model",
            model_registry_id=model_registry_id,
            version=chosen_version,
            artifact_path=str(artifact_path),
            rows=len(train_df),
        )

        return TrainingResult(
            model_type="equipment_maintenance",
            model_name=model_name,
            version=chosen_version,
            artifact_path=str(artifact_path),
            training_rows=int(len(train_df)),
            feature_snapshot_id=snapshot_id,
            metrics={"roc_auc": auc, "positive_rate": positive_rate},
            model_registry_id=model_registry_id,
        )

    def train_qc_model(self, version: Optional[str] = None, activate: bool = True) -> TrainingResult:
        query = text(
            """
            SELECT qc_result_id, snapshot_id,
                   cv_percent, z_score, moving_avg_10, moving_std_10,
                   within_control_limits, westgard_violation, outlier_flag
            FROM ai.ai_qc_features
            """
        )
        with db_manager.postgres_connection() as conn:
            df = pd.read_sql(query, conn)

        if df.empty:
            raise ValueError("No training rows found in ai.ai_qc_features")

        train_df = df.copy()
        numeric_columns = ["cv_percent", "z_score", "moving_avg_10", "moving_std_10"]
        for column in numeric_columns:
            train_df[column] = pd.to_numeric(train_df[column], errors="coerce").fillna(0.0)

        bool_columns = ["within_control_limits", "westgard_violation", "outlier_flag"]
        for column in bool_columns:
            train_df[column] = train_df[column].fillna(False).astype(int)

        features = [
            "cv_percent",
            "z_score",
            "moving_avg_10",
            "moving_std_10",
            "within_control_limits",
            "westgard_violation",
            "outlier_flag",
        ]

        X = train_df[features]

        model = IsolationForest(
            n_estimators=300,
            contamination=0.08,
            random_state=42,
        )
        model.fit(X)

        anomaly_preds = model.predict(X)  # -1 anomaly, 1 normal
        anomaly_rate = float((anomaly_preds == -1).mean())

        chosen_version = version or datetime.utcnow().strftime("%Y%m%d%H%M%S")
        filename = f"qc_anomaly_{chosen_version}.joblib"
        artifact_path = ARTIFACT_DIR / filename
        joblib.dump(model, artifact_path)

        snapshot_id = int(train_df["snapshot_id"].max()) if "snapshot_id" in train_df.columns else None

        model_name = "qc_isolation_forest"
        model_registry_id = self.registry.register(
            model_name=model_name,
            model_type="qc_anomaly",
            version=chosen_version,
            framework="sklearn",
            artifact_path=str(artifact_path),
            feature_snapshot_id=snapshot_id,
            training_rows=int(len(train_df)),
            hyperparameters={
                "n_estimators": 300,
                "contamination": 0.08,
                "random_state": 42,
            },
            metrics={
                "estimated_anomaly_rate": anomaly_rate,
            },
        )

        if activate:
            self._activate_registry_model(model_registry_id=model_registry_id, model_type="qc_anomaly")

        logger.info(
            "Trained and registered QC model",
            model_registry_id=model_registry_id,
            version=chosen_version,
            artifact_path=str(artifact_path),
            rows=len(train_df),
        )

        return TrainingResult(
            model_type="qc_anomaly",
            model_name=model_name,
            version=chosen_version,
            artifact_path=str(artifact_path),
            training_rows=int(len(train_df)),
            feature_snapshot_id=snapshot_id,
            metrics={"estimated_anomaly_rate": anomaly_rate},
            model_registry_id=model_registry_id,
        )

    def train_all(self, version: Optional[str] = None, activate: bool = True) -> Dict[str, TrainingResult]:
        return {
            "tat_prediction": self.train_tat_model(version=version, activate=activate),
            "equipment_maintenance": self.train_equipment_model(version=version, activate=activate),
            "qc_anomaly": self.train_qc_model(version=version, activate=activate),
        }

    def _activate_registry_model(self, model_registry_id: int, model_type: str) -> None:
        try:
            self.registry.activate(model_registry_id)
            return
        except Exception as exc:
            logger.warning(
                "ModelRegistryService.activate failed for id=%s (%s), falling back to direct activation: %s",
                model_registry_id,
                model_type,
                exc,
            )

        with db_manager.postgres_connection() as conn:
            conn.execute(
                text(
                    """
                    UPDATE ai.ai_model_registry
                    SET is_active = FALSE, updated_at = NOW()
                    WHERE model_type = :model_type
                    """
                ),
                {"model_type": model_type},
            )
            result = conn.execute(
                text(
                    """
                    UPDATE ai.ai_model_registry
                    SET is_active = TRUE, is_deprecated = FALSE, deployed_at = NOW(), updated_at = NOW()
                    WHERE id = :id
                    """
                ),
                {"id": model_registry_id},
            )

            if result.rowcount == 0:
                raise ValueError(f"Unable to activate model id={model_registry_id}: row not found")

            conn.commit()
