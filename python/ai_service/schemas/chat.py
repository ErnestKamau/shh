from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List

class ChatRequest(BaseModel):
    # NEW contract only: [{role, content}, ...]
    messages: List[Dict[str, str]]
    model: Optional[str] = None
    session_id: Optional[str] = None
    user_id: Optional[int] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    company_id: Optional[Any] = None
    use_visuals: bool = True
    module_context: Optional[str] = None
    mode: Optional[str] = None          # e.g. 'general' | 'support' | 'lab' | 'inventory' | 'audit' | 'crm'
    portal_user_id: Optional[int] = None
    crm_customer_id: Optional[int] = None
    user_data_snapshot: Optional[Dict[str, Any]] = None
    generation_options: Optional[Dict[str, Any]] = None

class ChatResponse(BaseModel):
    reply: str
    sources: List[Dict[str, Any]] = []
    model_version: str = "imara-rag-v1"
    retrieval_count: int = 0
    # NEW: Decision engine fields
    decision: Optional[Dict[str, Any]] = None
    decision_trace: Optional[Dict[str, Any]] = None

class ChatStreamRequest(BaseModel):
    """Request body for chat streaming endpoint - new format only"""
    messages: List[Dict[str, str]]
    company_id: Any = 1
    user_id: Optional[int] = None
    model: Optional[str] = None
    session_id: Optional[str] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    use_visuals: bool = True
    module_context: Optional[str] = None
    mode: Optional[str] = None          # e.g. 'general' | 'support' | 'lab' | 'inventory' | 'audit' | 'crm'
    portal_user_id: Optional[int] = None
    crm_customer_id: Optional[int] = None
    user_data_snapshot: Optional[Dict[str, Any]] = None
    generation_options: Optional[Dict[str, Any]] = None

class ChatCancelRequest(BaseModel):
    trace_id: str
