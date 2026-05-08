"""AI tasks package."""

from ai_service.tasks import ai_tasks, ingestion_tasks, monitoring_tasks, rag_tasks

__all__ = [
    "ai_tasks",
    "ingestion_tasks",
    "monitoring_tasks",
    "rag_tasks",
]
