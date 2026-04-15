"""
AI Feature Engineering Celery Tasks
Async tasks for AI feature generation and model training.
"""
from typing import Optional, List, Dict, Any

from celery import Task
from loguru import logger

from config.celery_config import celery_app
from python.py_etl.pipelines.ai_pipeline_orchestrator import AIPipelineOrchestrator
from python.py_etl.pipelines.ai_feature_pipeline import (
    run_sample_features,
    run_equipment_features,
    run_qc_features,
)
from python.ai_service.training import ModelTrainer


class AIFeatureTask(Task):
    """Base task class for AI feature engineering with error handling."""

    def on_failure(self, exc, task_id, args, kwargs, einfo):
        logger.error(f"AI task {self.name} failed", extra={"task_id": task_id, "error": str(exc)})

    def on_success(self, retval, task_id, args, kwargs):
        logger.info(f"AI task {self.name} succeeded", extra={"task_id": task_id, "result": retval})


@celery_app.task(
    name="app.tasks.ai_tasks.run_ai_feature_engineering",
    base=AIFeatureTask,
    bind=True,
)
def run_ai_feature_engineering(self, limit: Optional[int] = None) -> Dict[str, Any]:
    """
    Run all AI feature engineering pipelines.

    Orchestrates:
    - Sample feature extraction (TAT, workflow)
    - Equipment feature extraction (maintenance, risk)
    - QC feature extraction (trends, anomalies)

    Args:
        limit: Optional row limit for testing

    Returns:
        Dict with execution results
    """
    logger.info("Celery: Starting AI feature engineering")
    try:
        orchestrator = AIPipelineOrchestrator()
        result = orchestrator.run_all(limit=limit)
        logger.info(
            f"Celery: AI feature engineering completed — "
            f"Status: {result['status']}, Rows: {result.get('total_rows_loaded', 0)}"
        )
        return result
    except Exception as exc:
        logger.error(f"Celery: AI feature engineering failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.run_sample_features_only",
    base=AIFeatureTask,
)
def run_sample_features_only(
    snapshot_id: Optional[int] = None,
    limit: Optional[int] = None,
) -> Dict[str, Any]:
    """Run only the sample feature engineering pipeline."""
    logger.info("Celery: Starting sample feature engineering")
    try:
        result = run_sample_features(snapshot_id=snapshot_id, limit=limit)
        logger.info(f"Celery: Sample features completed — Rows: {result.get('rows_loaded', 0)}")
        return result
    except Exception as exc:
        logger.error(f"Celery: Sample feature engineering failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.run_equipment_features_only",
    base=AIFeatureTask,
)
def run_equipment_features_only(
    snapshot_id: Optional[int] = None,
    limit: Optional[int] = None,
) -> Dict[str, Any]:
    """Run only the equipment feature engineering pipeline."""
    logger.info("Celery: Starting equipment feature engineering")
    try:
        result = run_equipment_features(snapshot_id=snapshot_id, limit=limit)
        logger.info(f"Celery: Equipment features completed — Rows: {result.get('rows_loaded', 0)}")
        return result
    except Exception as exc:
        logger.error(f"Celery: Equipment feature engineering failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.run_qc_features_only",
    base=AIFeatureTask,
)
def run_qc_features_only(
    snapshot_id: Optional[int] = None,
    limit: Optional[int] = None,
) -> Dict[str, Any]:
    """Run only the QC feature engineering pipeline."""
    logger.info("Celery: Starting QC feature engineering")
    try:
        result = run_qc_features(snapshot_id=snapshot_id, limit=limit)
        logger.info(f"Celery: QC features completed — Rows: {result.get('rows_loaded', 0)}")
        return result
    except Exception as exc:
        logger.error(f"Celery: QC feature engineering failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.run_specific_pipelines",
    base=AIFeatureTask,
)
def run_specific_pipelines(
    pipeline_names: List[str],
    snapshot_id: Optional[int] = None,
    limit: Optional[int] = None,
) -> Dict[str, Any]:
    """
    Run a specific subset of AI pipelines.

    Args:
        pipeline_names: List from ['sample', 'equipment', 'qc']
        snapshot_id: Optional snapshot ID (creates new if None)
        limit: Optional row limit

    Returns:
        Dict with execution results
    """
    logger.info(f"Celery: Running specific pipelines: {pipeline_names}")
    orchestrator = AIPipelineOrchestrator()
    return orchestrator.run_specific(
        pipeline_names=pipeline_names,
        snapshot_id=snapshot_id,
        limit=limit,
    )


@celery_app.task(
    name="app.tasks.ai_tasks.rebuild_features_from_snapshot",
    base=AIFeatureTask,
)
def rebuild_features_from_snapshot(
    snapshot_id: int,
    limit: Optional[int] = None,
) -> Dict[str, Any]:
    """
    Rebuild features reusing an existing snapshot ID (reproducibility).

    Args:
        snapshot_id: Snapshot ID to reuse
        limit: Optional row limit

    Returns:
        Dict with execution results
    """
    logger.info(f"Celery: Rebuilding features from snapshot {snapshot_id}")
    try:
        orchestrator = AIPipelineOrchestrator()
        result = orchestrator.run_all(limit=limit, create_snapshot=False)
        result["snapshot_id"] = snapshot_id
        result["rebuild"] = True
        logger.info(f"Celery: Rebuild completed — Rows: {result.get('total_rows_loaded', 0)}")
        return result
    except Exception as exc:
        logger.error(f"Celery: Rebuild failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.cleanup_old_snapshots",
    base=AIFeatureTask,
)
def cleanup_old_snapshots(retention_days: int = 90) -> Dict[str, Any]:
    """
    Delete feature snapshots older than the retention window.

    Cascades to delete all associated feature rows.

    Args:
        retention_days: Keep snapshots from the last N days (default: 90)

    Returns:
        Dict with deletion results
    """
    logger.info(f"Celery: Cleaning up snapshots older than {retention_days} days")
    try:
        from python.ai_service.core.feature_snapshot_service import FeatureSnapshotService
        service = FeatureSnapshotService()
        deleted_count = service.delete_old_snapshots(retention_days=retention_days)
        logger.info(f"Celery: Deleted {deleted_count} old snapshots")
        return {
            "status": "success",
            "deleted_count": deleted_count,
            "retention_days": retention_days,
        }
    except Exception as exc:
        logger.error(f"Celery: Cleanup failed: {exc}")
        raise


@celery_app.task(
    name="app.tasks.ai_tasks.train_tat_model",
    base=AIFeatureTask,
)
def train_tat_model(version: Optional[str] = None, activate: bool = True) -> Dict[str, Any]:
    """Train TAT prediction model and register artifact in model registry."""
    logger.info("Celery: Starting TAT model training")
    trainer = ModelTrainer()
    result = trainer.train_tat_model(version=version, activate=activate)
    payload = {
        "status": "success",
        "model_type": result.model_type,
        "model_name": result.model_name,
        "version": result.version,
        "artifact_path": result.artifact_path,
        "training_rows": result.training_rows,
        "feature_snapshot_id": result.feature_snapshot_id,
        "metrics": result.metrics,
        "model_registry_id": result.model_registry_id,
    }
    logger.info(f"Celery: TAT model training completed: {payload}")
    return payload


@celery_app.task(
    name="app.tasks.ai_tasks.train_equipment_model",
    base=AIFeatureTask,
)
def train_equipment_model(version: Optional[str] = None, activate: bool = True) -> Dict[str, Any]:
    """Train equipment maintenance model and register artifact in model registry."""
    logger.info("Celery: Starting equipment model training")
    trainer = ModelTrainer()
    result = trainer.train_equipment_model(version=version, activate=activate)
    payload = {
        "status": "success",
        "model_type": result.model_type,
        "model_name": result.model_name,
        "version": result.version,
        "artifact_path": result.artifact_path,
        "training_rows": result.training_rows,
        "feature_snapshot_id": result.feature_snapshot_id,
        "metrics": result.metrics,
        "model_registry_id": result.model_registry_id,
    }
    logger.info(f"Celery: Equipment model training completed: {payload}")
    return payload


@celery_app.task(
    name="app.tasks.ai_tasks.train_qc_model",
    base=AIFeatureTask,
)
def train_qc_model(version: Optional[str] = None, activate: bool = True) -> Dict[str, Any]:
    """Train QC anomaly model and register artifact in model registry."""
    logger.info("Celery: Starting QC model training")
    trainer = ModelTrainer()
    result = trainer.train_qc_model(version=version, activate=activate)
    payload = {
        "status": "success",
        "model_type": result.model_type,
        "model_name": result.model_name,
        "version": result.version,
        "artifact_path": result.artifact_path,
        "training_rows": result.training_rows,
        "feature_snapshot_id": result.feature_snapshot_id,
        "metrics": result.metrics,
        "model_registry_id": result.model_registry_id,
    }
    logger.info(f"Celery: QC model training completed: {payload}")
    return payload


@celery_app.task(
    name="app.tasks.ai_tasks.train_all_predictive_models",
    base=AIFeatureTask,
)
def train_all_predictive_models(version: Optional[str] = None, activate: bool = True) -> Dict[str, Any]:
    """Train and register both TAT and equipment predictive models."""
    logger.info("Celery: Starting full predictive model training")
    trainer = ModelTrainer()
    results = trainer.train_all(version=version, activate=activate)
    payload = {
        "status": "success",
        "models": {
            model_type: {
                "model_type": result.model_type,
                "model_name": result.model_name,
                "version": result.version,
                "artifact_path": result.artifact_path,
                "training_rows": result.training_rows,
                "feature_snapshot_id": result.feature_snapshot_id,
                "metrics": result.metrics,
                "model_registry_id": result.model_registry_id,
            }
            for model_type, result in results.items()
        },
    }
    logger.info("Celery: Full predictive model training completed")
    return payload
