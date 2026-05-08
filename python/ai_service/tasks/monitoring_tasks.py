import json
import redis
import os
from datetime import datetime
from loguru import logger
from typing import Dict, Any, List, Optional

from config.celery_config import celery_app
from python.py_etl.core.database import db_manager


@celery_app.task(name="app.tasks.monitoring_tasks.check_health")
def check_health():
    source_ok = False

    try:
        source_ok = not db_manager.extract_from_source("SELECT 1 AS ok").empty
    except Exception:
        source_ok = False

    return {
        "source": source_ok,
        "status": "ok" if source_ok else "degraded",
    }


@celery_app.task(name="app.tasks.monitoring_tasks.update_ai_index_heartbeat")
def update_ai_index_heartbeat() -> Dict[str, Any]:
    """Background task to refresh AI indexing status in Redis."""
    logger.info("Heartbeat: Checking AI indexing status")
    try:
        from python.ai_service.services.ai_index_state_service import ai_index_state_service
        
        tables_to_check = ["sample_headers", "sample_details", "inventory_items", "equipment"]
        failed_tables = []
        
        for t in tables_to_check:
            state = ai_index_state_service.get_state(t)
            if state and state.get("index_status") == "failed":
                failed_tables.append(t)
        
        # Determine overall sync health
        health_status = "unhealthy" if failed_tables else "healthy"
        
        data = {
            "status": health_status,
            "failed_tables": failed_tables,
            "updated_at": datetime.now().isoformat()
        }
        
        # Cache in Redis for the API to read
        redis_url = os.getenv("CELERY_BROKER_URL", "redis://localhost:6379/0")
        r = redis.from_url(redis_url)
        r.set("imara:ai:index_heartbeat", json.dumps(data))
        
        logger.info(f"Heartbeat updated: {health_status} (Failed: {len(failed_tables)})")
        return data
    except Exception as e:
        logger.error(f"Heartbeat failed: {e}")
        return {"status": "error", "error": str(e)}
