import os
from celery import Celery
from loguru import logger

# Get Redis connection details from environment
# Defaulting to localhost:6379 for standard developer setup
redis_host = os.getenv("REDIS_HOST", "127.0.0.1")
redis_port = os.getenv("REDIS_PORT", "6379")
redis_db = os.getenv("REDIS_DB", "0")
redis_password = os.getenv("REDIS_PASSWORD", None)

if redis_password and redis_password.lower() != "null":
    redis_url = f"redis://:{redis_password}@{redis_host}:{redis_port}/{redis_db}"
else:
    redis_url = f"redis://{redis_host}:{redis_port}/{redis_db}"

# Allow override via CELERY_BROKER_URL
broker_url = os.getenv("CELERY_BROKER_URL", redis_url)
result_backend = os.getenv("CELERY_RESULT_BACKEND", redis_url)

logger.info(f"Initializing Celery app with broker: {broker_url}")

app = Celery(
    "imara_ai",
    broker=broker_url,
    backend=result_backend,
    include=[
        "ai_service.tasks.rag_tasks",
        "ai_service.tasks.etl_tasks",
        "ai_service.tasks.monitoring_tasks",
        "ai_service.tasks.ai_tasks",
        "ai_service.tasks.ingestion_tasks",
    ]
)

# Optional configuration
app.conf.update(
    task_serializer="json",
    accept_content=["json"],
    result_serializer="json",
    timezone="UTC",
    enable_utc=True,
    task_track_started=True,
    task_time_limit=3600,  # 1 hour max sync time
    worker_prefetch_multiplier=1,
)

if __name__ == "__main__":
    app.start()
