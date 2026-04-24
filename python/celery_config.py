"""
Celery Configuration
====================
Creates the Celery application and registers the beat (scheduled task) schedule
used by the AI background workers.

Schedule overview:
  - check_model_drift         — daily at 03:00 UTC — detect feature distribution drift
  - retrain_tat_model         — every Sunday at 02:00 UTC — retrain TAT prediction model
  - retrain_equipment_model   — every Sunday at 02:30 UTC — retrain equipment maintenance model
  - retrain_qc_model          — every Sunday at 03:00 UTC — retrain QC anomaly model

Environment variables (all optional — defaults shown):
  CELERY_BROKER_URL   redis://localhost:6379/0
  CELERY_RESULT_URL   redis://localhost:6379/1
  CELERY_TIMEZONE     UTC
"""

import os
from celery import Celery
from celery.schedules import crontab

# ---------------------------------------------------------------------------
# Application instance
# ---------------------------------------------------------------------------

_BROKER_URL = os.getenv("CELERY_BROKER_URL", "redis://localhost:6379/0")
_RESULT_URL = os.getenv("CELERY_RESULT_URL", "redis://localhost:6379/1")
_TIMEZONE   = os.getenv("CELERY_TIMEZONE", "UTC")

app = Celery(
    "imara_ai",
    broker=_BROKER_URL,
    backend=_RESULT_URL,
    include=[
        "python.ai_service.tasks.ai_tasks",
        "python.ai_service.tasks.etl_tasks",
        "python.ai_service.tasks.rag_tasks",
        "python.ai_service.tasks.monitoring_tasks",
    ],  # task modules to auto-discover
)

# ---------------------------------------------------------------------------
# Core settings
# ---------------------------------------------------------------------------
app.conf.update(
    task_serializer         = "json",
    result_serializer       = "json",
    accept_content          = ["json"],
    timezone                = _TIMEZONE,
    enable_utc              = True,
    # Prevent a single slow task from blocking a worker thread indefinitely.
    task_soft_time_limit    = 300,   # 5 minutes — sends SoftTimeLimitExceeded
    task_time_limit         = 600,   # 10 minutes — hard kill
    # Ack the message AFTER the task function returns (safer for ML tasks).
    task_acks_late          = True,
    worker_prefetch_multiplier = 1,  # fair dispatch: one task at a time per worker
)

# ---------------------------------------------------------------------------
# Beat schedule (periodic tasks)
# ---------------------------------------------------------------------------
app.conf.beat_schedule = {
    # ── Drift detection ──────────────────────────────────────────────────
    "check_model_drift_daily": {
        "task":     "python.ai_service.tasks.ai_tasks.check_model_drift",
        "schedule": crontab(hour=3, minute=0),    # 03:00 UTC every day
        "kwargs":   {},
    },

    # ── Weekly retraining — staggered to avoid DB overload ───────────────
    "retrain_tat_model_weekly": {
        "task":     "python.ai_service.tasks.ai_tasks.retrain_model",
        "schedule": crontab(hour=2, minute=0, day_of_week="sunday"),
        "kwargs":   {"model_type": "tat_prediction"},
    },
    "retrain_equipment_model_weekly": {
        "task":     "python.ai_service.tasks.ai_tasks.retrain_model",
        "schedule": crontab(hour=2, minute=30, day_of_week="sunday"),
        "kwargs":   {"model_type": "equipment_maintenance"},
    },
    "retrain_qc_model_weekly": {
        "task":     "python.ai_service.tasks.ai_tasks.retrain_model",
        "schedule": crontab(hour=3, minute=0, day_of_week="sunday"),
        "kwargs":   {"model_type": "qc_anomaly"},
    },

    # ── ETL → RAG pipeline schedule ─────────────────────────────────
    "etl_sample_headers": {
        "task": "tasks.etl.sync_table",
        "schedule": 60.0,
        "kwargs": {"table_key": "sample_headers"},
    },
    "etl_sample_details": {
        "task": "tasks.etl.sync_table",
        "schedule": 60.0,
        "kwargs": {"table_key": "sample_details"},
    },
    "etl_inventory_items": {
        "task": "tasks.etl.sync_table",
        "schedule": 300.0,
        "kwargs": {"table_key": "inventory_items"},
    },
    "etl_equipment": {
        "task": "tasks.etl.sync_table",
        "schedule": 300.0,
        "kwargs": {"table_key": "equipment"},
    },
    "etl_corrective_actions": {
        "task": "tasks.etl.sync_table",
        "schedule": 300.0,
        "kwargs": {"table_key": "corrective_actions"},
    },
    # ── Heartbeats & Shared State ──────────────────────────────────
    "update_etl_heartbeat": {
        "task": "app.tasks.monitoring_tasks.update_etl_heartbeat",
        "schedule": 60.0,
        "kwargs": {},
    },
}
