from enum import Enum
from typing import List, Dict, Any, Optional
from pydantic import BaseModel, Field

class QueryClass(str, Enum):
    AGGREGATE = "aggregate"
    SEMANTIC = "semantic"
    LOOKUP = "lookup"
    HYBRID = "hybrid"
    PROCEDURAL = "procedural"

class OrchestrationMode(str, Enum):
    RAG_ONLY = "rag_only"
    LIVE_DATA_ONLY = "live_data_only"
    BALANCED = "balanced"

class ReasoningStep(BaseModel):
    id: str = "step_1"
    goal: str = ""
    domain: Optional[str] = "all"
    sub_query: str
    sql_params: Optional[Dict[str, Any]] = {}
    type: str = "rag"  # rag, sql, abstain, synthesis
    depends_on: Optional[List[str]] = []
    output_fields: Optional[List[str]] = []
    allowed_modes: Optional[List[OrchestrationMode]] = [OrchestrationMode.BALANCED, OrchestrationMode.RAG_ONLY]

class ReasoningGraph(BaseModel):
    is_multihop: bool = False
    query_class: QueryClass = QueryClass.SEMANTIC
    requires_authoritative_source: bool = False
    mode_policy: Optional[OrchestrationMode] = OrchestrationMode.BALANCED
    steps: List[ReasoningStep] = []
    rationale: str = ""

class HopResult(BaseModel):
    step_id: str
    chunks: List[Dict[str, Any]] = []
    data_results: Optional[List[Dict[str, Any]]] = []
    extracted_state: Dict[str, Any] = {}
    success: bool = True
    error: Optional[str] = None
