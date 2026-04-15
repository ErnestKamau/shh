from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List

class PredictionRequest(BaseModel):
    entity_id: int
    metadata: Optional[Dict[str, Any]] = None

class PredictionResponse(BaseModel):
    prediction: Any
    confidence: float
    risk_level: str
    explanation: List[str]
    model_version: str
    model_name: Optional[str] = None
    degraded_mode: bool = False
    recommendation: Optional[str] = None
    feature_snapshot_id: Optional[int] = None
    reasoning: Optional[Dict[str, Any]] = None

class BatchPredictionItem(BaseModel):
    """A single item within a batch prediction request."""
    entity_id: int
    model_type: str = Field(..., pattern="^(tat_prediction|equipment_maintenance|qc_anomaly)$")
    metadata: Optional[Dict[str, Any]] = None

class BatchPredictionItemResult(BaseModel):
    """Result for one item in a batch prediction response."""
    entity_id: int
    model_type: str
    prediction: Optional[Any] = None
    confidence: Optional[float] = None
    risk_level: Optional[str] = None
    explanation: Optional[List[str]] = None
    model_version: Optional[str] = None
    model_name: Optional[str] = None
    degraded_mode: bool = False
    recommendation: Optional[str] = None
    reasoning: Optional[Dict[str, Any]] = None
    error: Optional[str] = None

class BatchPredictionRequest(BaseModel):
    """Request for parallel batch prediction across multiple entities."""
    items: List[BatchPredictionItem] = Field(..., min_length=1, max_length=50)
    include_reasoning: bool = False

class BatchPredictionResponse(BaseModel):
    """Response carrying per-item results plus aggregate summary."""
    results: List[BatchPredictionItemResult]
    total: int
    succeeded: int
    failed: int
    processing_time_ms: int

class ClassifyRequest(BaseModel):
    message: str = Field(min_length=1, max_length=2000)

class ClassifyResponse(BaseModel):
    intent: str
    intent_version: str = "v1"
    confidence: float
    type: str  # "data", "action", or "meta"
    entities: Dict[str, Any] = {}
    confidence_reason: str = ""
    risk_level: Optional[str] = None
    latency_ms: int = 0
