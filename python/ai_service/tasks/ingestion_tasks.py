from __future__ import annotations

from typing import Dict, Any

from celery import Task
from loguru import logger

from config.celery_config import celery_app
from python.ai_service.core.document_processor import DocumentProcessor


class IngestionTask(Task):
    def on_failure(self, exc, task_id, args, kwargs, einfo):
        logger.error(f"Ingestion task {self.name} failed", extra={"task_id": task_id, "error": str(exc)})

    def on_success(self, retval, task_id, args, kwargs):
        logger.info(f"Ingestion task {self.name} succeeded", extra={"task_id": task_id, "result": retval})


@celery_app.task(
    name="app.tasks.ingestion_tasks.process_document",
    base=IngestionTask,
    queue="ingestion",
)
def process_document(content: str, document_id: str, base_metadata: Dict[str, Any] | None = None) -> Dict[str, Any]:
    processor = DocumentProcessor()
    chunks = processor.process_document(content=content, document_id=document_id, base_metadata=base_metadata or {})
    return {
        "status": "success",
        "document_id": document_id,
        "chunk_count": len(chunks),
        "chunks": chunks,
    }
