"""
AI Database Service
Provides query methods for AI feature tables.
Extends the base DatabaseService with AI-specific operations.
"""
from __future__ import annotations

from typing import Optional

from loguru import logger
from sqlalchemy import text

from python.py_etl.core.database import db_manager as pyetl_db_manager
from config.settings import settings


class AIDataService:
    """
    Data access layer for the AI schema.

    All queries read from ai.ai_feature_snapshots, ai.ai_sample_features,
    ai.ai_equipment_features, ai.ai_qc_features, and ai.ai_feedback.
    """

    def __init__(self) -> None:
        self._db = pyetl_db_manager
        self._schema = settings.ai_schema

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
