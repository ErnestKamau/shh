from fastapi import APIRouter, BackgroundTasks
from typing import Dict, Any, List, Optional
from pydantic import BaseModel
import os

from py_etl.services.etl_index_state_service import etl_index_state_service
from ai_service.core.etl_engine import ETLEngine

class SyncRequest(BaseModel):
    tables: Optional[List[str]] = None

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

def run_sync_background(table_keys: Optional[List[str]] = None):
    """Worker function for background sync."""
    try:
        # Resolve config correctly relative to the python root
        base_dir = os.path.dirname(os.path.dirname(os.path.dirname(__file__)))
        config_path = os.path.join(base_dir, "py_etl", "config", "table_config.yaml")

        engine = ETLEngine(config_path=config_path)
        engine.sync_all(table_keys=table_keys)
    except Exception as e:
        # logging is already handled inside ETLEngine via loguru
        pass

@router.post("/sync", response_model=Dict[str, Any])
async def trigger_etl_sync(
    background_tasks: BackgroundTasks,
    request: SyncRequest
) -> Dict[str, Any]:
    """
    Trigger an asynchronous ETL sync for specified tables or all tables.
    """
    background_tasks.add_task(run_sync_background, request.tables)
    return {
        "status": "accepted",
        "message": "ETL sync started in background",
        "tables": request.tables or "all"
    }
