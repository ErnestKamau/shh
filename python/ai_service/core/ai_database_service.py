"""
AI Database Service
Provides query methods for AI feature tables.
Extends the base DatabaseService with AI-specific operations.
"""
from __future__ import annotations

from datetime import datetime, timezone
from typing import Optional, Any

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager as pyetl_db_manager
from python.ai_service.config.settings import settings
from python.ai_service.schemas.decision import DecisionFeedback


class AIDataService:
    """
    Data access layer for the AI schema.

    All queries read from ai.ai_feature_snapshots, ai.ai_sample_features,
    ai.ai_equipment_features, ai.ai_qc_features, and ai.ai_feedback.
    """

    def __init__(self) -> None:
        self._db = pyetl_db_manager
        self._schema = settings.ai_schema
        self._action_feedback_columns_cache: Optional[set[str]] = None

    # ── Snapshots ─────────────────────────────────────────────────────────

    def get_all_snapshots(self, limit: int = 50) -> list[dict]:
        sql = text(f"""
            SELECT
                s.id,
                s.snapshot_time,
                s.description,
                s.created_at,
                (SELECT COUNT(*) FROM {self._schema}.ai_sample_features    WHERE snapshot_id = s.id) AS sample_features,
                (SELECT COUNT(*) FROM {self._schema}.ai_equipment_features WHERE snapshot_id = s.id) AS equipment_features,
                (SELECT COUNT(*) FROM {self._schema}.ai_qc_features        WHERE snapshot_id = s.id) AS qc_features
            FROM {self._schema}.ai_feature_snapshots s
            ORDER BY s.snapshot_time DESC
            LIMIT :limit
        """)
        return self._query(sql, {"limit": limit})

    def get_latest_snapshot(self) -> Optional[dict]:
        sql = text(f"""
            SELECT id, snapshot_time, description, created_at
            FROM {self._schema}.ai_feature_snapshots
            ORDER BY snapshot_time DESC
            LIMIT 1
        """)
        rows = self._query(sql)
        return rows[0] if rows else None

    def get_snapshot_by_id(self, snapshot_id: int) -> Optional[dict]:
        sql = text(f"""
            SELECT id, snapshot_time, description, metadata, created_at
            FROM {self._schema}.ai_feature_snapshots
            WHERE id = :snapshot_id
        """)
        rows = self._query(sql, {"snapshot_id": snapshot_id})
        return rows[0] if rows else None

    def _resolve_snapshot_id(self, snapshot_id: Optional[int]) -> Optional[int]:
        """Use provided snapshot_id or fall back to the latest one."""
        if snapshot_id is not None:
            return snapshot_id
        snap = self.get_latest_snapshot()
        return snap["id"] if snap else None

    # ── Sample Features ───────────────────────────────────────────────────

    def get_sample_features(
        self,
        snapshot_id: Optional[int] = None,
        limit: int = 100,
        rework_only: bool = False,
        min_tat: Optional[float] = None,
        max_tat: Optional[float] = None,
    ) -> list[dict]:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return []

        filters = ["snapshot_id = :snapshot_id"]
        params: dict = {"snapshot_id": snap_id, "limit": limit}

        if rework_only:
            filters.append("rework_flag = TRUE")
        if min_tat is not None:
            filters.append("tat_days >= :min_tat")
            params["min_tat"] = min_tat
        if max_tat is not None:
            filters.append("tat_days <= :max_tat")
            params["max_tat"] = max_tat

        where = " AND ".join(filters)
        sql = text(f"""
            SELECT id, sample_id, snapshot_id, tat_days, stage_count,
                   priority_score, rework_flag, is_qc_batch,
                   processing_delay_days, approval_delay_days,
                   customer_id, feature_version, created_at
            FROM {self._schema}.ai_sample_features
            WHERE {where}
            ORDER BY created_at DESC
            LIMIT :limit
        """)
        return self._query(sql, params)

    def get_sample_feature_by_id(
        self,
        sample_id: int,
        snapshot_id: Optional[int] = None,
    ) -> Optional[dict]:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return None

        sql = text(f"""
            SELECT *
            FROM {self._schema}.ai_sample_features
            WHERE sample_id = :sample_id AND snapshot_id = :snapshot_id
            LIMIT 1
        """)
        rows = self._query(sql, {"sample_id": sample_id, "snapshot_id": snap_id})
        return rows[0] if rows else None

    # ── Equipment Features ────────────────────────────────────────────────

    def get_equipment_features(
        self,
        snapshot_id: Optional[int] = None,
        limit: int = 100,
        min_risk: Optional[float] = None,
        overdue_only: bool = False,
    ) -> list[dict]:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return []

        filters = ["snapshot_id = :snapshot_id"]
        params: dict = {"snapshot_id": snap_id, "limit": limit}

        if min_risk is not None:
            filters.append("risk_score >= :min_risk")
            params["min_risk"] = min_risk
        if overdue_only:
            filters.append("days_until_due < 0")

        where = " AND ".join(filters)
        sql = text(f"""
            SELECT id, equipment_id, snapshot_id, days_since_service, days_until_due,
                   failure_rate, usage_frequency, maintenance_count, calibration_count,
                   verification_count, risk_score, feature_version, created_at
            FROM {self._schema}.ai_equipment_features
            WHERE {where}
            ORDER BY risk_score DESC NULLS LAST
            LIMIT :limit
        """)
        return self._query(sql, params)

    def get_equipment_feature_by_id(
        self,
        equipment_id: int,
        snapshot_id: Optional[int] = None,
    ) -> Optional[dict]:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return None

        sql = text(f"""
            SELECT *
            FROM {self._schema}.ai_equipment_features
            WHERE equipment_id = :equipment_id AND snapshot_id = :snapshot_id
            LIMIT 1
        """)
        rows = self._query(sql, {"equipment_id": equipment_id, "snapshot_id": snap_id})
        return rows[0] if rows else None

    # ── QC Features ───────────────────────────────────────────────────────

    def get_qc_features(
        self,
        snapshot_id: Optional[int] = None,
        limit: int = 100,
        outliers_only: bool = False,
        violations_only: bool = False,
        analyte_id: Optional[int] = None,
    ) -> list[dict]:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return []

        filters = ["snapshot_id = :snapshot_id"]
        params: dict = {"snapshot_id": snap_id, "limit": limit}

        if outliers_only:
            filters.append("outlier_flag = TRUE")
        if violations_only:
            filters.append("westgard_violation = TRUE")
        if analyte_id is not None:
            filters.append("analyte_id = :analyte_id")
            params["analyte_id"] = analyte_id

        where = " AND ".join(filters)
        sql = text(f"""
            SELECT id, qc_result_id, snapshot_id, cv_percent, z_score,
                   moving_avg_10, moving_std_10, trend_direction,
                   within_control_limits, westgard_violation, outlier_flag,
                   analyte_id, qc_level, feature_version, created_at
            FROM {self._schema}.ai_qc_features
            WHERE {where}
            ORDER BY ABS(z_score) DESC NULLS LAST
            LIMIT :limit
        """)
        return self._query(sql, params)

    # ── Metrics & Risk ────────────────────────────────────────────────────

    def get_metrics_summary(self) -> dict:
        try:
            snap = self.get_latest_snapshot()
            snap_id = snap["id"] if snap else None

            counts: dict = {"sample": 0, "equipment": 0, "qc": 0}
            highlights: dict = {"rework_samples": 0, "high_risk_equipment": 0, "qc_violations": 0}

            if snap_id:
                for table, key in [
                    ("ai_sample_features", "sample"),
                    ("ai_equipment_features", "equipment"),
                    ("ai_qc_features", "qc"),
                ]:
                    row = self._query(
                        text(f"SELECT COUNT(*) AS n FROM {self._schema}.{table} WHERE snapshot_id = :s"),
                        {"s": snap_id},
                    )
                    counts[key] = row[0]["n"] if row else 0

                # Highlight counts
                h = self._query(
                    text(f"""
                        SELECT
                            (SELECT COUNT(*) FROM {self._schema}.ai_sample_features
                             WHERE snapshot_id = :s AND rework_flag = TRUE) AS rework_samples,
                            (SELECT COUNT(*) FROM {self._schema}.ai_equipment_features
                             WHERE snapshot_id = :s AND risk_score >= 0.7)  AS high_risk_equipment,
                            (SELECT COUNT(*) FROM {self._schema}.ai_qc_features
                             WHERE snapshot_id = :s AND westgard_violation = TRUE) AS qc_violations
                    """),
                    {"s": snap_id},
                )
                if h:
                    highlights = h[0]

            return {
                "latest_snapshot": snap,
                "feature_counts": counts,
                "risk_highlights": highlights,
            }

        except Exception as exc:
            logger.warning(f"Metrics summary error: {exc}")
            return {"latest_snapshot": None, "feature_counts": {}, "risk_highlights": {}}

    def get_risk_summary(self, snapshot_id: Optional[int] = None) -> dict:
        snap_id = self._resolve_snapshot_id(snapshot_id)
        if snap_id is None:
            return {"snapshot_id": None, "samples": [], "equipment": [], "qc": []}

        return {
            "snapshot_id": snap_id,
            "samples": self.get_sample_features(snap_id, limit=20, rework_only=True),
            "equipment": self.get_equipment_features(snap_id, limit=20, min_risk=0.7),
            "qc": self.get_qc_features(snap_id, limit=20, violations_only=True),
        }

    # ── Feedback ──────────────────────────────────────────────────────────

    def record_feedback(
        self,
        prediction_id: int,
        actual_value: float,
        user_feedback: Optional[str],
        feedback_type: str = "comment",
        user_id: Optional[int] = None,
    ) -> int:
        sql = text(f"""
            INSERT INTO {self._schema}.ai_feedback
                (prediction_id, actual_value, prediction_error, absolute_error,
                 user_feedback, feedback_type, user_id)
            VALUES
                (:prediction_id, :actual_value,
                 (SELECT predicted_value FROM {self._schema}.ai_prediction_runs
                  WHERE id = :prediction_id LIMIT 1) - :actual_value,
                 ABS((SELECT predicted_value FROM {self._schema}.ai_prediction_runs
                      WHERE id = :prediction_id LIMIT 1) - :actual_value),
                 :user_feedback, :feedback_type, :user_id)
        """)
        with self._db.postgres_connection() as conn:
            result = conn.execute(
                sql,
                {
                    "prediction_id": prediction_id,
                    "actual_value": actual_value,
                    "user_feedback": user_feedback,
                    "feedback_type": feedback_type,
                    "user_id": user_id,
                },
            )
            conn.commit()
        return result.rowcount

    def record_decision_feedback(self, feedback: DecisionFeedback) -> int:
        import json
        columns = self._get_action_feedback_columns()

        with self._db.postgres_connection() as conn:
            if "decision_id" in columns:
                sql = text(f"""
                    INSERT INTO {self._schema}.ai_action_feedback
                        (
                            trace_id,
                            decision_id,
                            entity_id,
                            module,
                            suggested_action,
                            features_snapshot,
                            model_version,
                            decision_output,
                            user_action,
                            outcome_result,
                            user_feedback_text,
                            created_at,
                            updated_at
                        )
                    VALUES
                        (
                            :trace_id,
                            :decision_id,
                            :entity_id,
                            :module,
                            :suggested_action,
                            CAST(:features_snapshot AS JSONB),
                            :model_version,
                            CAST(:decision_output AS JSONB),
                            :user_action,
                            :outcome_result,
                            :user_feedback_text,
                            :created_at,
                            :updated_at
                        )
                """)

                result = conn.execute(
                    sql,
                    {
                        "trace_id": feedback.trace_id,
                        "decision_id": feedback.decision_id,
                        "entity_id": feedback.entity_id,
                        "module": feedback.module,
                        "suggested_action": feedback.suggested_action,
                        "features_snapshot": json.dumps(feedback.features_snapshot or {}),
                        "model_version": feedback.model_version,
                        "decision_output": json.dumps(feedback.decision_output or {}),
                        "user_action": feedback.user_action,
                        "outcome_result": feedback.outcome_result,
                        "user_feedback_text": feedback.user_feedback_text,
                        "created_at": feedback.created_at,
                        "updated_at": feedback.updated_at,
                    },
                )
            else:
                sql = text(f"""
                    INSERT INTO {self._schema}.ai_action_feedback
                        (
                            module,
                            entity_id,
                            ai_action,
                            ai_priority,
                            ai_confidence,
                            ai_reason,
                            user_action,
                            user_modification,
                            actor_id,
                            feedback_timestamp,
                            reason,
                            metadata,
                            trace_id
                        )
                    VALUES
                        (
                            :module,
                            :entity_id,
                            :ai_action,
                            :ai_priority,
                            :ai_confidence,
                            :ai_reason,
                            :user_action,
                            :user_modification,
                            :actor_id,
                            :feedback_timestamp,
                            :reason,
                            CAST(:metadata AS JSONB),
                            CAST(:trace_id AS UUID)
                        )
                """)

                decision_output = feedback.decision_output or {}
                action_priority = str(decision_output.get("action_priority", "medium")).lower()
                priority_map = {"low": 1, "medium": 2, "high": 3}

                result = conn.execute(
                    sql,
                    {
                        "module": feedback.module,
                        "entity_id": feedback.entity_id,
                        "ai_action": feedback.suggested_action,
                        "ai_priority": priority_map.get(action_priority, 2),
                        "ai_confidence": decision_output.get("confidence_score"),
                        "ai_reason": decision_output.get("reason") or feedback.user_feedback_text,
                        "user_action": feedback.user_action,
                        "user_modification": feedback.user_feedback_text,
                        "actor_id": 0,
                        "feedback_timestamp": feedback.created_at,
                        "reason": feedback.outcome_result,
                        "metadata": json.dumps(
                            {
                                "features_snapshot": feedback.features_snapshot or {},
                                "decision_output": decision_output,
                                "model_version": feedback.model_version,
                            }
                        ),
                        "trace_id": feedback.trace_id,
                    },
                )
            conn.commit()
        return result.rowcount

    def get_decision_feedback(
        self,
        module: Optional[str] = None,
        limit: int = 100,
        completed_only: bool = False,
    ) -> list[dict]:
        columns = self._get_action_feedback_columns()
        filters = ["1=1"]
        params: dict[str, Any] = {"limit": limit}

        if module:
            filters.append("module = :module")
            params["module"] = module

        if completed_only:
            filters.append("user_action IS NOT NULL")
            filters.append("outcome_result IS NOT NULL")

        where = " AND ".join(filters)
        if "decision_id" in columns:
            sql = text(f"""
                SELECT
                    id,
                    trace_id,
                    decision_id,
                    entity_id,
                    module,
                    suggested_action,
                    features_snapshot,
                    model_version,
                    decision_output,
                    user_action,
                    outcome_result,
                    user_feedback_text,
                    created_at,
                    updated_at
                FROM {self._schema}.ai_action_feedback
                WHERE {where}
                ORDER BY created_at DESC
                LIMIT :limit
            """)
        else:
            if completed_only:
                filters = [f for f in filters if f != "outcome_result IS NOT NULL"]
                filters.append("reason IS NOT NULL")
                where = " AND ".join(filters)

            sql = text(f"""
                SELECT
                    id,
                    trace_id::text AS trace_id,
                    NULL::text AS decision_id,
                    entity_id,
                    module,
                    ai_action AS suggested_action,
                    COALESCE(metadata->'features_snapshot', metadata, '{{}}'::jsonb) AS features_snapshot,
                    COALESCE(metadata->>'model_version', 'legacy') AS model_version,
                    COALESCE(metadata->'decision_output', '{{}}'::jsonb) AS decision_output,
                    user_action,
                    reason AS outcome_result,
                    user_modification AS user_feedback_text,
                    feedback_timestamp AS created_at,
                    NULL::timestamp AS updated_at
                FROM {self._schema}.ai_action_feedback
                WHERE {where}
                ORDER BY feedback_timestamp DESC
                LIMIT :limit
            """)
        return self._query(sql, params)

    def build_decision_training_dataset(
        self,
        module: str,
        days: int = 30,
        min_rows: int = 20,
    ) -> dict:
        columns = self._get_action_feedback_columns()
        if "decision_id" in columns:
            sql = text(f"""
                SELECT
                    trace_id,
                    module,
                    features_snapshot,
                    user_action,
                    outcome_result,
                    created_at
                FROM {self._schema}.ai_action_feedback
                WHERE module = :module
                  AND created_at >= NOW() - (:days || ' days')::interval
                ORDER BY created_at DESC
            """)
            rows = self._query(sql, {"module": module, "days": days})
        else:
            sql = text(f"""
                SELECT
                    trace_id::text AS trace_id,
                    module,
                    COALESCE(metadata->'features_snapshot', metadata, '{{}}'::jsonb) AS features_snapshot,
                    user_action,
                    reason AS outcome_result,
                    feedback_timestamp AS created_at
                FROM {self._schema}.ai_action_feedback
                WHERE module = :module
                  AND feedback_timestamp >= NOW() - (:days || ' days')::interval
                ORDER BY feedback_timestamp DESC
            """)
            rows = self._query(sql, {"module": module, "days": days})

        training_rows = []
        for row in rows:
            label = self._derive_decision_label(
                user_action=row.get("user_action"),
                outcome_result=row.get("outcome_result"),
            )
            if label is None:
                continue

            numeric_features = self._extract_numeric_features(row.get("features_snapshot") or {})
            if not numeric_features:
                continue

            training_rows.append(
                {
                    "trace_id": row.get("trace_id"),
                    "module": row.get("module"),
                    "features": numeric_features,
                    "label": label,
                    "weight": self._compute_row_weight(row.get("created_at")),
                    "created_at": row.get("created_at"),
                }
            )

        return {
            "module": module,
            "rows": training_rows,
            "total": len(training_rows),
            "days": days,
            "min_rows_met": len(training_rows) >= min_rows,
        }

    @staticmethod
    def _derive_decision_label(user_action: Optional[str], outcome_result: Optional[str]) -> Optional[int]:
        if not user_action and not outcome_result:
            return None

        approved_actions = {"approved"}
        positive_outcomes = {"action_taken", "sample_passed", "flagged", "resolved"}
        negative_actions = {"rejected", "modified"}
        negative_outcomes = {"failed", "not_taken", "negative", "unresolved"}

        if (user_action or "").lower() in approved_actions:
            return 1
        if (outcome_result or "").lower() in positive_outcomes:
            return 1
        if (user_action or "").lower() in negative_actions:
            return 0
        if (outcome_result or "").lower() in negative_outcomes:
            return 0
        return None

    @staticmethod
    def _extract_numeric_features(features: dict[str, Any]) -> dict[str, float]:
        extracted: dict[str, float] = {}
        for key, value in features.items():
            if isinstance(value, bool):
                extracted[key] = 1.0 if value else 0.0
            elif isinstance(value, (int, float)):
                extracted[key] = float(value)
        return extracted

    @staticmethod
    def _compute_row_weight(created_at: Any) -> float:
        if not created_at:
            return 1.0

        try:
            if isinstance(created_at, str):
                parsed = datetime.fromisoformat(created_at.replace("Z", "+00:00"))
            else:
                parsed = created_at

            if parsed.tzinfo is None:
                parsed = parsed.replace(tzinfo=timezone.utc)

            age_days = max(0.0, (datetime.now(timezone.utc) - parsed).total_seconds() / 86400.0)
            return max(0.5, 1.5 - min(age_days / 90.0, 1.0))
        except Exception:
            return 1.0

    def _get_action_feedback_columns(self) -> set[str]:
        if self._action_feedback_columns_cache is not None:
            return self._action_feedback_columns_cache

        sql = text(
            """
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = :schema_name
              AND table_name = 'ai_action_feedback'
            """
        )
        rows = self._query(sql, {"schema_name": self._schema})
        self._action_feedback_columns_cache = {str(r.get("column_name")) for r in rows}
        return self._action_feedback_columns_cache

    # ── Internal helpers ──────────────────────────────────────────────────

    def _query(self, sql: text, params: Optional[dict] = None) -> list[dict]:
        """Run a SQL query and return list of dicts, handling datetime serialisation."""
        try:
            with self._db.postgres_connection() as conn:
                rows = conn.execute(sql, params or {}).mappings().all()
                return [self._serialise(dict(r)) for r in rows]
        except Exception as exc:
            logger.error(f"AI query failed: {exc}")
            return []

    @staticmethod
    def _serialise(row: dict) -> dict:
        """Convert non-JSON-serialisable types to strings."""
        import datetime
        for k, v in row.items():
            if isinstance(v, (datetime.datetime, datetime.date)):
                row[k] = v.isoformat()
        return row
