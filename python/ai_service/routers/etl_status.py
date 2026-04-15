from fastapi import APIRouter
from typing import Dict, Any

from python.py_etl.services.etl_index_state_service import etl_index_state_service

router = APIRouter(prefix="/etl", tags=["etl"])

@router.get("/status", response_model=Dict[str, Any])
async def get_etl_status() -> Dict[str, Any]:
    """
    Returns per-table sync health from reporting.etl_index_state.
    Shows the latest watermark, etl status, and rag indexing status.
    """
    states = etl_index_state_service.get_all_states()
    result = {}
    for state in states:
        table_key = state.pop("table_key")
        result[table_key] = state
    return result
