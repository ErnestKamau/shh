import asyncio
import logging
from typing import Dict, Any, List
from fastapi import APIRouter, Depends, HTTPException, Query
from python.ai_service.schemas.prediction import (
    PredictionRequest, PredictionResponse, 
    BatchPredictionRequest, BatchPredictionResponse,
    ClassifyRequest, ClassifyResponse
)
from python.ai_service.services.prediction_service import PredictionService
from python.ai_service.services.reasoning_service import ReasoningService
from python.ai_service.services.ollama_service import OllamaService

router = APIRouter(tags=["Inference"], prefix="/v1")
logger = logging.getLogger(__name__)

# Semaphores for limiting heavy LLM reasoning concurrently in the same process
_reasoning_semaphore = asyncio.Semaphore(2)

def get_prediction_service():
    return PredictionService()

def get_reasoning_service():
    ollama = OllamaService()
    return ReasoningService(ollama)

@router.post("/predict/tat", response_model=PredictionResponse)
async def predict_tat(
    request: PredictionRequest,
    service: PredictionService = Depends(get_prediction_service)
):
    features = service.fetch_latest_features("ai_sample_features", "sample_id", request.entity_id)
    # Offload potentially CPU-bound scikit-learn model to a thread
    return await asyncio.to_thread(
        service.predict, "tat_prediction", request.entity_id, features, request.metadata
    )

@router.post("/predict/maintenance", response_model=PredictionResponse)
async def predict_maintenance(
    request: PredictionRequest,
    service: PredictionService = Depends(get_prediction_service)
):
    features = service.fetch_latest_features("ai_equipment_features", "equipment_id", request.entity_id)
    return await asyncio.to_thread(
        service.predict, "equipment_maintenance", request.entity_id, features, request.metadata
    )

@router.post("/predict/qc", response_model=PredictionResponse)
async def predict_qc(
    request: PredictionRequest,
    service: PredictionService = Depends(get_prediction_service)
):
    features = service.fetch_latest_features("ai_qc_features", "qc_result_id", request.entity_id)
    return await asyncio.to_thread(
        service.predict, "qc_anomaly", request.entity_id, features, request.metadata
    )

@router.post("/predict/batch", response_model=BatchPredictionResponse)
async def predict_batch(
    request: BatchPredictionRequest,
    service: PredictionService = Depends(get_prediction_service)
):
    import time
    from concurrent.futures import ThreadPoolExecutor, as_completed

    start_ms = int(time.time() * 1000)

    def _run_single(item):
        try:
            cfg = {
                "tat_prediction": ("ai_sample_features", "sample_id"),
                "equipment_maintenance": ("ai_equipment_features", "equipment_id"),
                "qc_anomaly": ("ai_qc_features", "qc_result_id")
            }.get(item.model_type)
            
            if not cfg:
                raise ValueError(f"Unknown item model_type: {item.model_type}")
                
            features = service.fetch_latest_features(cfg[0], cfg[1], item.entity_id)
            resp = service.predict(item.model_type, item.entity_id, features, item.metadata)
            
            from python.ai_service.schemas.prediction import BatchPredictionItemResult
            return BatchPredictionItemResult(
                entity_id=item.entity_id,
                model_type=item.model_type,
                **resp
            )
        except Exception as exc:
            from python.ai_service.schemas.prediction import BatchPredictionItemResult
            return BatchPredictionItemResult(
                entity_id=item.entity_id,
                model_type=item.model_type,
                error=str(exc)
            )

    results = []
    with ThreadPoolExecutor(max_workers=min(10, len(request.items))) as pool:
        future_map = {pool.submit(_run_single, item): i for i, item in enumerate(request.items)}
        temp_results = {}
        for future in as_completed(future_map):
            temp_results[future_map[future]] = future.result()
        results = [temp_results[i] for i in range(len(request.items))]

    elapsed_ms = int(time.time() * 1000) - start_ms
    succeeded = sum(1 for r in results if r.error is None)

    return BatchPredictionResponse(
        results=results,
        total=len(results),
        succeeded=succeeded,
        failed=len(results) - succeeded,
        processing_time_ms=elapsed_ms
    )

@router.post("/classify", response_model=ClassifyResponse)
async def classify_intent(
    request: ClassifyRequest,
    service: ReasoningService = Depends(get_reasoning_service)
):
    async with _reasoning_semaphore:
        from python.ai_service.constants import CANONICAL_INTENTS, _CLASSIFIER_SYSTEM_PROMPT
        result = await asyncio.to_thread(
            service.classify_intent, request.message, CANONICAL_INTENTS, _CLASSIFIER_SYSTEM_PROMPT
        )
        
    if not result:
        raise HTTPException(status_code=500, detail="Classification failed")
    
    return ClassifyResponse(**result, latency_ms=0)

@router.get("/intents")
def list_intents():
    from python.ai_service.constants import CANONICAL_INTENTS
    return {
        "intents": {
            k: {
                "type": v["type"],
                "version": v["version"],
                "risk_level": v.get("risk_level"),
            }
            for k, v in CANONICAL_INTENTS.items()
        }
    }
