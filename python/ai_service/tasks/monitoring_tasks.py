from sqlalchemy import text
import json
import uuid
import redis
import os
from datetime import datetime
from loguru import logger
from typing import Dict, Any, List, Optional

from celery_config import app as celery_app
from py_etl.core.database import db_manager


@celery_app.task(name="app.tasks.monitoring_tasks.check_health")
def check_health():
    mysql_ok = False
    postgres_ok = False

    try:
        mysql_ok = not db_manager.extract_from_mysql("SELECT 1 AS ok").empty
    except Exception:
        mysql_ok = False

    try:
        _ = db_manager.get_all_etl_runs(limit=1)
        postgres_ok = True
    except Exception:
        postgres_ok = False

    return {
        "mysql": mysql_ok,
        "postgres": postgres_ok,
        "status": "ok" if mysql_ok and postgres_ok else "degraded",
    }


@celery_app.task(name="app.tasks.monitoring_tasks.update_etl_heartbeat")
def update_etl_heartbeat() -> Dict[str, Any]:
    """Background task to refresh ETL health status in Redis."""
    logger.info("Heartbeat: Checking ETL synchronisation status")
    try:
        from py_etl.services.etl_index_state_service import etl_index_state_service
        
        tables_to_check = ["sample_headers", "sample_details", "inventory_items", "equipment"]
        failed_tables = []
        
        for t in tables_to_check:
            state = etl_index_state_service.get_state(t)
            if not state or state.get("etl_status") != "success":
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
        r.set("imara:ai:etl_heartbeat", json.dumps(data))
        
        logger.info(f"Heartbeat updated: {health_status} (Failed: {len(failed_tables)})")
        return data
    except Exception as e:
        logger.error(f"Heartbeat failed: {e}")
        return {"status": "error", "error": str(e)}
