from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List

class ChatRequest(BaseModel):
    # NEW contract only: [{role, content}, ...]
    messages: List[Dict[str, str]]
    sources: List[Dict[str, Any]] = []
    model: Optional[str] = None
    session_id: Optional[str] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    company_id: Optional[int] = None
    generation_options: Optional[Dict[str, Any]] = None

class ChatResponse(BaseModel):
    reply: str
    sources: List[Dict[str, Any]] = []
    model_version: str = "imara-rag-v1"
    retrieval_count: int = 0

class ChatStreamRequest(BaseModel):
    """Request body for chat streaming endpoint - new format only"""
    messages: List[Dict[str, str]]
    sources: List[Dict[str, Any]] = []
    company_id: int = 1
    model: Optional[str] = None
    session_id: Optional[str] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    generation_options: Optional[Dict[str, Any]] = None
