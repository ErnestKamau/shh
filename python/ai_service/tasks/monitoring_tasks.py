from config.celery_config import celery_app
from python.ai_service.core.database import db_manager


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
