from pydantic import BaseModel, Field, field_validator
from typing import Optional, Dict, Any, List

class ChatRequest(BaseModel):
    # NEW contract only: [{role, content}, ...]
    messages: List[Dict[str, str]]
    model: Optional[str] = None
    session_id: Optional[str] = None
    user_id: Optional[int] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    company_id: int = 1
    use_visuals: bool = True
    module_context: Optional[str] = None
    mode: Optional[str] = None          # e.g. 'general' | 'support' | 'lab' | 'inventory' | 'audit' | 'crm'
    language: Optional[str] = None      # 'auto' | 'en' | 'sw'
    portal_user_id: Optional[int] = None
    crm_customer_id: Optional[int] = None
    user_data_snapshot: Optional[Dict[str, Any]] = None
    generation_options: Optional[Dict[str, Any]] = None

    @field_validator("company_id", mode="before")
    @classmethod
    def normalize_company_id(cls, value: Any) -> int:
        return _runtime_int(value, default=1)

    @field_validator("user_id", "portal_user_id", "crm_customer_id", mode="before")
    @classmethod
    def normalize_optional_int(cls, value: Any) -> Optional[int]:
        return _runtime_optional_int(value)

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
    company_id: int = 1
    user_id: Optional[int] = None
    model: Optional[str] = None
    session_id: Optional[str] = None
    trace_id: Optional[str] = None
    attachments: List[Dict[str, Any]] = []
    use_visuals: bool = True
    module_context: Optional[str] = None
    mode: Optional[str] = None          # e.g. 'general' | 'support' | 'lab' | 'inventory' | 'audit' | 'crm'
    language: Optional[str] = None      # 'auto' | 'en' | 'sw'
    portal_user_id: Optional[int] = None
    crm_customer_id: Optional[int] = None
    user_data_snapshot: Optional[Dict[str, Any]] = None
    generation_options: Optional[Dict[str, Any]] = None

    @field_validator("company_id", mode="before")
    @classmethod
    def normalize_company_id(cls, value: Any) -> int:
        return _runtime_int(value, default=1)

    @field_validator("user_id", "portal_user_id", "crm_customer_id", mode="before")
    @classmethod
    def normalize_optional_int(cls, value: Any) -> Optional[int]:
        return _runtime_optional_int(value)

class ChatCancelRequest(BaseModel):
    trace_id: str


def _runtime_int(value: Any, default: int = 1) -> int:
    if isinstance(value, bool):
        return default
    if isinstance(value, int):
        return value
    if isinstance(value, str) and value.isdigit():
        return int(value)
    return default


def _runtime_optional_int(value: Any) -> Optional[int]:
    if value is None or isinstance(value, bool):
        return None
    if isinstance(value, int):
        return value
    if isinstance(value, str) and value.isdigit():
        return int(value)
    return None
