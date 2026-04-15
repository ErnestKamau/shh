"""
Inference Audit Logger
Writes every prediction call to ai.ai_inference_audit for compliance
(ISO 17025 traceability) and drift monitoring.

Designed to be non-blocking: errors are logged but never raise to the caller,
so a logging failure cannot interrupt an active prediction.
"""
from __future__ import annotations

import json
import time
from contextlib import contextmanager
from datetime import datetime, date
from typing import Any, Dict, List, Optional

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager


class DateTimeEncoder(json.JSONEncoder):
    """Custom JSON encoder that handles datetime and date objects."""
    def default(self, obj):
        if isinstance(obj, (datetime, date)):
            return obj.isoformat()
        if hasattr(obj, '__dict__'):
            return str(obj)
        return super().default(obj)


def safe_json_dumps(obj: Any) -> Optional[str]:
    """Safely serialize objects to JSON, handling datetime and other types."""
    if obj is None:
        return None
    try:
        return json.dumps(obj, cls=DateTimeEncoder, default=str)
    except Exception:
        return None


def normalize_dict_for_jsonb(obj: Any) -> Any:
    """
    Convert a dict to be JSONB-safe by converting datetime objects to ISO strings.
    SQLAlchemy's JSONB adapter will serialize this dict automatically.
    """
    if isinstance(obj, dict):
        return {k: normalize_dict_for_jsonb(v) for k, v in obj.items()}
    elif isinstance(obj, (list, tuple)):
        return [normalize_dict_for_jsonb(item) for item in obj]
    elif isinstance(obj, (datetime, date)):
        return obj.isoformat()
    else:
        return obj


class InferenceLogger:
    """
    Audit logger for ML inference calls.

    Usage — manual logging
    ----------------------
    log = InferenceLogger()
    log.record(
        model_name="tat_rf_v2",
        model_version="2.0.0",
        entity_type="sample",
        entity_id=12345,
        input_features={"tat_days": 3.5, "stage_count": 4},
        prediction={"predicted_tat": 4.1, "risk_band": "medium"},
        confidence=0.82,
        latency_ms=12,
        request_source="api",
        requested_by="user:42",
    )

    Usage — context manager (auto-timing)
    --------------------------------------
    with log.timed_inference(
        model_name="equip_maintenance_rf",
        model_version="1.0.0",
        entity_type="equipment",
        entity_id=7,
        input_features=feature_dict,
        request_source="celery",
    ) as ctx:
        result = model.predict(X)
        ctx["prediction"] = result
        ctx["confidence"] = max(result["probabilities"])
    """

    # ------------------------------------------------------------------
    # Core write
    # ------------------------------------------------------------------

    def record(
        self,
        model_name: str,
        model_version: Optional[str],
        entity_type: str,
        entity_id: Optional[int],
        input_features: Optional[Dict[str, Any]] = None,
        input_data: Optional[Dict[str, Any]] = None,
        prediction: Optional[Any] = None,
        confidence: Optional[float] = None,
        latency_ms: Optional[int] = None,
        request_source: Optional[str] = None,
        requested_by: Optional[str] = None,
        model_registry_id: Optional[int] = None,
        feature_snapshot_id: Optional[int] = None,
        recommendation: Optional[str] = None,
        user_override: Optional[bool] = None,
        actor: Optional[str] = None,
    ) -> Optional[int]:
        """
        Persist one inference audit record.

        Returns the new row id, or None if the write fails (non-fatal).
        """
        try:
            extended_sql = text("""
                INSERT INTO ai.ai_inference_audit (
                    model_registry_id, model_name, model_version,
                    entity_type, entity_id,
                    feature_snapshot_id,
                    input_features, input_data,
                    prediction, confidence,
                    latency_ms, request_source, requested_by,
                    recommendation, user_override, actor,
                    created_at
                ) VALUES (
                    :model_registry_id, :model_name, :model_version,
                    :entity_type, :entity_id,
                    :feature_snapshot_id,
                    :input_features, :input_data,
                    :prediction, :confidence,
                    :latency_ms, :request_source, :requested_by,
                    :recommendation, :user_override, :actor,
                    NOW()
                ) RETURNING id
            """)

            legacy_sql = text("""
                INSERT INTO ai.ai_inference_audit (
                    model_registry_id, model_name, model_version,
                    entity_type, entity_id,
                    input_features, prediction, confidence,
                    latency_ms, request_source, requested_by,
                    created_at
                ) VALUES (
                    :model_registry_id, :model_name, :model_version,
                    :entity_type, :entity_id,
                    :input_features, :prediction, :confidence,
                    :latency_ms, :request_source, :requested_by,
                    NOW()
                ) RETURNING id
            """)

            # For JSONB columns, SQLAlchemy's text() requires JSON strings, not dicts
            # We use the custom DateTimeEncoder to handle datetime objects
            params = {
                "model_registry_id": model_registry_id,
                "model_name": model_name,
                "model_version": model_version,
                "entity_type": entity_type,
                "entity_id": entity_id,
                "feature_snapshot_id": feature_snapshot_id,
                "input_features": json.dumps(normalize_dict_for_jsonb(input_features), cls=DateTimeEncoder) if input_features else None,
                "input_data": json.dumps(normalize_dict_for_jsonb(input_data), cls=DateTimeEncoder) if input_data else None,
                "prediction": json.dumps(normalize_dict_for_jsonb(prediction), cls=DateTimeEncoder) if prediction else None,
                "confidence": confidence,
                "latency_ms": latency_ms,
                "request_source": request_source,
                "requested_by": requested_by,
                "recommendation": recommendation,
                "user_override": user_override,
                "actor": actor,
            }

            with db_manager.postgres_connection() as conn:
                try:
                    row = conn.execute(extended_sql, params).fetchone()
                    conn.commit()
                except Exception as exc:
                    logger.warning(
                        "InferenceLogger: extended audit insert failed, retrying with legacy schema: "
                        f"{exc}"
                    )
                    row = conn.execute(
                        legacy_sql,
                        {
                            "model_registry_id": model_registry_id,
                            "model_name": model_name,
                            "model_version": model_version,
                            "entity_type": entity_type,
                            "entity_id": entity_id,
                            "input_features": json.dumps(normalize_dict_for_jsonb(input_features), cls=DateTimeEncoder) if input_features else None,
                            "prediction": json.dumps(normalize_dict_for_jsonb(prediction), cls=DateTimeEncoder) if prediction else None,
                            "confidence": confidence,
                            "latency_ms": latency_ms,
                            "request_source": request_source,
                            "requested_by": requested_by,
                        },
                    ).fetchone()
                    conn.commit()
            return row[0] if row else None

        except Exception as exc:
            # Never raise — logging must not break the prediction path
            logger.warning(f"InferenceLogger: failed to persist audit record: {exc}")
            return None

    # ------------------------------------------------------------------
    # Context manager (auto-timing)
    # ------------------------------------------------------------------

    @contextmanager
    def timed_inference(
        self,
        model_name: str,
        model_version: Optional[str],
        entity_type: str,
        entity_id: Optional[int] = None,
        input_features: Optional[Dict[str, Any]] = None,
        input_data: Optional[Dict[str, Any]] = None,
        request_source: Optional[str] = None,
        requested_by: Optional[str] = None,
        model_registry_id: Optional[int] = None,
        feature_snapshot_id: Optional[int] = None,
        user_override: Optional[bool] = None,
        actor: Optional[str] = None,
    ):
        """
        Context manager that times the inference block and auto-writes the
        audit record on exit.  The caller fills ctx['prediction'] and
        optionally ctx['confidence'] inside the with-block.

        Example
        -------
        with inference_logger.timed_inference(...) as ctx:
            ctx['prediction'] = model.predict(X)
            ctx['confidence'] = 0.91
        """
        ctx: Dict[str, Any] = {
            "prediction": None,
            "confidence": None,
            "recommendation": None,
            "user_override": user_override,
        }
        t0 = time.perf_counter()
        try:
            yield ctx
        finally:
            latency_ms = int((time.perf_counter() - t0) * 1000)
            self.record(
                model_name=model_name,
                model_version=model_version,
                entity_type=entity_type,
                entity_id=entity_id,
                input_features=input_features,
                input_data=input_data,
                prediction=ctx.get("prediction"),
                confidence=ctx.get("confidence"),
                latency_ms=latency_ms,
                request_source=request_source,
                requested_by=requested_by,
                model_registry_id=model_registry_id,
                feature_snapshot_id=feature_snapshot_id,
                recommendation=ctx.get("recommendation"),
                user_override=ctx.get("user_override"),
                actor=actor,
            )

    # ------------------------------------------------------------------
    # Query helpers
    # ------------------------------------------------------------------

    def get_recent(
        self,
        entity_type: Optional[str] = None,
        model_name: Optional[str] = None,
        limit: int = 100,
    ) -> List[Dict[str, Any]]:
        """Fetch recent audit records, optionally filtered."""
        conditions = []
        params: Dict[str, Any] = {"limit": limit}

        if entity_type:
            conditions.append("entity_type = :entity_type")
            params["entity_type"] = entity_type
        if model_name:
            conditions.append("model_name = :model_name")
            params["model_name"] = model_name

        where = ("WHERE " + " AND ".join(conditions)) if conditions else ""
        sql = text(f"""
            SELECT id, model_name, model_version, entity_type, entity_id,
                   prediction, confidence, latency_ms, request_source,
                   requested_by, created_at
            FROM ai.ai_inference_audit
            {where}
            ORDER BY created_at DESC
            LIMIT :limit
        """)

        with db_manager.postgres_connection() as conn:
            rows = conn.execute(sql, params).fetchall()
        return [dict(r._mapping) for r in rows]

    def get_latency_stats(self, model_name: str) -> Dict[str, Any]:
        """Return p50/p95/p99 latency stats for a given model."""
        sql = text("""
            SELECT
                count(*)                                            AS total_calls,
                round(avg(latency_ms))                             AS avg_ms,
                round(percentile_cont(0.50) WITHIN GROUP (ORDER BY latency_ms)) AS p50_ms,
                round(percentile_cont(0.95) WITHIN GROUP (ORDER BY latency_ms)) AS p95_ms,
                round(percentile_cont(0.99) WITHIN GROUP (ORDER BY latency_ms)) AS p99_ms
            FROM ai.ai_inference_audit
            WHERE model_name = :model_name
        """)
        with db_manager.postgres_connection() as conn:
            row = conn.execute(sql, {"model_name": model_name}).fetchone()
        return dict(row._mapping) if row else {}


# Module-level singleton — import this directly
inference_logger = InferenceLogger()
