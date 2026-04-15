import time
import os
import pandas as pd
from fastapi import APIRouter
from fastapi.responses import PlainTextResponse
from sqlalchemy import text
from python.py_etl.core.database import db_manager

router = APIRouter(tags=["Health"])

_request_counter = 0

@router.get("/health/live")
def liveness_check():
    """Liveness check: returns 200 if the process is running."""
    return {"status": "live", "timestamp": str(pd.Timestamp.now())}

@router.get("/health/ready")
def readiness_check():
    """Readiness check: verifies critical dependencies."""
    checks = {
        "database": False,
        "ollama": False
    }
    
    # Check Database
    try:
        with db_manager.postgres_connection() as conn:
            conn.execute(text("SELECT 1"))
        checks["database"] = True
    except Exception:
        pass
    
    # Check Ollama (Simple reachability)
    try:
        import httpx
        host = os.getenv("OLLAMA_HOST", "http://localhost:11434")
        resp = httpx.get(f"{host}/api/tags", timeout=2.0)
        if resp.status_code == 200:
            checks["ollama"] = True
    except Exception:
        pass
    
    status = "ready" if all(checks.values()) else "degraded"
    return {
        "status": status,
        "checks": checks,
        "timestamp": str(pd.Timestamp.now())
    }

@router.get("/metrics")
def prometheus_metrics():
    """Unified Prometheus scrape endpoint."""
    ts = int(time.time() * 1000)
    lines = [
        "# HELP ai_service_up 1 when the service is running",
        "# TYPE ai_service_up gauge",
        f"ai_service_up 1 {ts}",
    ]
    return PlainTextResponse(
        "\n".join(lines) + "\n",
        media_type="text/plain; version=0.0.4; charset=utf-8",
    )
