import logging
import os
from typing import Optional, List, Dict, Any
from fastapi import APIRouter, Depends, Query, HTTPException, BackgroundTasks
from python.ai_service.schemas.features import SearchRequest, EmbeddingRequest
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.core.ai_database_service import AIDataService

router = APIRouter(tags=["AI Features"], prefix="/ai")
logger = logging.getLogger(__name__)

def get_retrieval_service():
    return RetrievalService()

def get_ai_data_service():
    return AIDataService()

@router.get("/features/snapshots")
def list_snapshots(
    limit: int = Query(default=50, ge=1, le=500),
    service: AIDataService = Depends(get_ai_data_service)
):
    return service.get_all_snapshots(limit=limit)

@router.get("/features/snapshots/latest")
def get_latest_snapshot(service: AIDataService = Depends(get_ai_data_service)):
    snapshot = service.get_latest_snapshot()
    if not snapshot:
        raise HTTPException(status_code=404, detail="No feature snapshots found.")
    return snapshot

@router.get("/features/samples")
def get_sample_features(
    snapshot_id: Optional[int] = Query(default=None),
    limit: int = Query(default=100, ge=1, le=1000),
    rework_only: bool = Query(default=False),
    min_tat: Optional[float] = Query(default=None),
    service: AIDataService = Depends(get_ai_data_service)
):
    return service.get_sample_features(snapshot_id=snapshot_id, limit=limit, rework_only=rework_only, min_tat=min_tat)

@router.get("/features/samples/{sample_id}")
def get_sample_feature(
    sample_id: int,
    snapshot_id: Optional[int] = Query(default=None),
    service: AIDataService = Depends(get_ai_data_service)
):
    result = service.get_sample_feature_by_id(sample_id, snapshot_id)
    if not result:
        raise HTTPException(status_code=404, detail=f"Feature for sample {sample_id} not found")
    return result

@router.get("/features/equipment")
def get_equipment_features(
    snapshot_id: Optional[int] = Query(default=None),
    limit: int = Query(default=100, ge=1, le=1000),
    overdue_only: bool = Query(default=False),
    service: AIDataService = Depends(get_ai_data_service)
):
    return service.get_equipment_features(snapshot_id=snapshot_id, limit=limit, overdue_only=overdue_only)

@router.get("/features/qc")
def get_qc_features(
    snapshot_id: Optional[int] = Query(default=None),
    limit: int = Query(default=100, ge=1, le=1000),
    outliers_only: bool = Query(default=False),
    service: AIDataService = Depends(get_ai_data_service)
):
    return service.get_qc_features(snapshot_id=snapshot_id, limit=limit, outliers_only=outliers_only)

@router.post("/features/engineer")
def trigger_feature_engineering(
    pipelines: str = Query(default="all"),
    limit: Optional[int] = Query(default=None),
    service: AIDataService = Depends(get_ai_data_service)
):
    try:
        from python.ai_service.tasks.ai_tasks import run_ai_feature_engineering, run_specific_pipelines
        if pipelines == "all":
            task = run_ai_feature_engineering.delay(limit=limit)
        else:
            task = run_specific_pipelines.delay(pipeline_names=pipelines.split(","), limit=limit)
        return {"status": "queued", "task_id": task.id}
    except Exception as e:
        logger.error(f"Failed to trigger feature engineering: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/feedback")
def submit_feedback(
    prediction_id: int,
    actual_value: float,
    user_feedback: Optional[str] = None,
    service: AIDataService = Depends(get_ai_data_service)
):
    rows = service.record_feedback(prediction_id=prediction_id, actual_value=actual_value, user_feedback=user_feedback)
    return {"status": "recorded", "rows": rows}

@router.post("/embeddings/generate")
def generate_embedding(
    payload: EmbeddingRequest,
    retrieval: RetrievalService = Depends(get_retrieval_service)
):
    vector = retrieval.embed_text(payload.text)
    return {"vector": vector, "dimension": len(vector)}

@router.post("/search")
def semantic_search(
    payload: SearchRequest,
    retrieval: RetrievalService = Depends(get_retrieval_service)
):
    results = retrieval.search(payload.query, limit=payload.limit, collections=payload.collections, entity_types=payload.entity_types, metadata_filters=payload.metadata)
    return {"query": payload.query, "count": len(results), "results": results}

@router.get("/metrics/summary")
def get_ai_metrics_summary(service: AIDataService = Depends(get_ai_data_service)):
    return service.get_metrics_summary()

@router.get("/metrics/risk-summary")
def get_risk_summary(snapshot_id: Optional[int] = Query(default=None), service: AIDataService = Depends(get_ai_data_service)):
    return service.get_risk_summary(snapshot_id=snapshot_id)
