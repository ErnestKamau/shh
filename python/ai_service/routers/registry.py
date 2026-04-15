import logging
from typing import Optional, List, Dict, Any
from fastapi import APIRouter, Depends, Query, HTTPException
from python.ai_service.core.model_registry_service import ModelRegistryService
from python.ai_service.core.inference_logger import InferenceLogger

router = APIRouter(tags=["Registry & Governance"], prefix="/models")
audit_router = APIRouter(tags=["Registry & Governance"], prefix="/inference")
gov_router = APIRouter(tags=["Registry & Governance"], prefix="/v1/governance")

logger = logging.getLogger(__name__)

def get_registry_service():
    return ModelRegistryService()

def get_audit_logger():
    return InferenceLogger()

# --- Registry Endpoints ---

@router.get("")
def list_models(
    model_type: Optional[str] = None,
    include_deprecated: bool = False,
    service: ModelRegistryService = Depends(get_registry_service)
):
    if model_type:
        rows = service.list_by_type(model_type, include_deprecated=include_deprecated)
    else:
        rows = service.list_all(include_deprecated=include_deprecated)
    return {"models": rows, "count": len(rows)}

@router.get("/active/{model_type}")
def get_active_model(model_type: str, service: ModelRegistryService = Depends(get_registry_service)):
    record = service.get_active(model_type)
    if not record:
        raise HTTPException(status_code=404, detail=f"No active model for '{model_type}'")
    return record

@router.post("/{model_id}/activate")
def activate_model(model_id: int, service: ModelRegistryService = Depends(get_registry_service)):
    try:
        service.activate(model_id)
        return {"status": "activated", "model_id": model_id}
    except ValueError as e:
        raise HTTPException(status_code=404, detail=str(e))

# --- Audit Endpoints ---

@audit_router.get("/audit")
def get_inference_audit(
    entity_type: Optional[str] = None,
    model_name: Optional[str] = None,
    limit: int = 100,
    service: InferenceLogger = Depends(get_audit_logger)
):
    rows = service.get_recent(entity_type=entity_type, model_name=model_name, limit=limit)
    return {"records": rows, "count": len(rows)}

# --- Governance Endpoints ---

@gov_router.get("/drift")
def governance_drift_status():
    from python.ai_service.drift_detector import get_drift_status
    return get_drift_status()

@gov_router.post("/retrain/{model_type}")
def governance_retrain(model_type: str, triggered_by: str = "manual"):
    from python.ai_service.training import trigger_retraining
    return trigger_retraining(model_type, triggered_by)
