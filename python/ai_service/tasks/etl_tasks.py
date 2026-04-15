"""
Celery tasks for ETL pipeline.
"""
from __future__ import annotations

import os
from celery import Task
from loguru import logger

from python.celery_config import app as celery_app
from python.ai_service.core.etl_engine import ETLEngine
from python.py_etl.services.etl_index_state_service import etl_index_state_service


class ETLTask(Task):
    def on_failure(self, exc, task_id, args, kwargs, einfo):
        logger.error(f"ETL task {self.name} failed", extra={"task_id": task_id, "error": str(exc)})

    def on_success(self, retval, task_id, args, kwargs):
        logger.info(f"ETL task {self.name} succeeded", extra={"task_id": task_id, "result": retval})


@celery_app.task(
    name="tasks.etl.sync_table",
    base=ETLTask,
    queue="etl",
    bind=True,
    max_retries=2
)
def sync_table(self, table_key: str) -> dict:
    """
    ETL one table from MySQL → reporting.*.
    On success, enqueue a RAG indexing task for the same domain.
    """
    try:
        # Resolve config correctly based on current CWD
        base_dir = os.path.dirname(os.path.dirname(os.path.dirname(__file__)))
        config_path = os.path.join(base_dir, "py_etl", "config", "table_config.yaml")

        engine = ETLEngine(config_path=config_path)
        rows_synced = engine.sync_table(table_key)
        
        etl_index_state_service.upsert_etl_success(table_key, rows_synced)
        
        # Enqueue generic RAG domain reindex using the same table_key
        # Note: We must import late or at the top level to avoid circular imports.
        from python.ai_service.tasks.rag_tasks import reindex_domain
        
        reindex_domain.apply_async(
            args=[table_key],
            queue="rag",
            countdown=2  # Give PG time to flush
        )
        
        return {
            "status": "success",
            "table_key": table_key,
            "rows_synced": rows_synced
        }
        
    except Exception as exc:
        etl_index_state_service.upsert_etl_failure(table_key, str(exc))
        raise self.retry(exc=exc, countdown=30)
