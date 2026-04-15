from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List

class ReasoningRequest(BaseModel):
    prediction: Dict[str, Any]
    features: Optional[Dict[str, Any]] = None
    context: Optional[List[Dict[str, Any]]] = None
    kind: str = Field(default="tat", pattern="^(tat|maintenance|qc)$")

class ReasoningResponse(BaseModel):
    reasoning: Dict[str, Any]
