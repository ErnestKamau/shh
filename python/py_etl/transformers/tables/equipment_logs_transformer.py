"""
Transformer: maintainance_calibration_logs / verification_logs → reporting.equipment_logs

Both source tables land in the same target table.  The ``source_table``
+ ``source_id`` composite pair is used as the upsert conflict target so
rows from both sources co-exist without collisions.
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_timestamp, to_string


class MaintenanceLogsTransformer(BaseTransformer):
    """Handles maintainance_calibration_logs → reporting.equipment_logs"""

    table_key = "maintainance_calibration_logs"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            # Prefer operator_id; fall back to employee_id
            performed_by = self._get(row, "operator_id") or self._get(row, "employee_id")
            records.append({
                "source_table":      "maintainance_calibration_logs",
                "source_id":         row["id"],
                "equipment_id":      self._get(row, "equipment_id"),
                "event_type":        self._get(row, "type") or "unknown",
                "event_date":        to_timestamp(self._get(row, "date")),
                "event_date_raw":    to_string(self._get(row, "date")),
                "service_provider":  self._get(row, "service_provider"),
                "supplier_id":       self._get(row, "supplier_id"),
                "performed_by":      performed_by,
                "edited_by":         self._get(row, "edit_by"),
                "reference_number":  self._get(row, "reference_number"),
                "notes":             self._get(row, "notes"),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)


class VerificationLogsTransformer(BaseTransformer):
    """Handles verification_logs → reporting.equipment_logs"""

    table_key = "verification_logs"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            notes = self._get(row, "remarks") or self._get(row, "response")
            records.append({
                "source_table":      "verification_logs",
                "source_id":         row["id"],
                "equipment_id":      self._get(row, "equipment_id"),
                "event_type":        "verification",
                "event_date":        to_timestamp(self._get(row, "verification_date")),
                "event_date_raw":    to_string(self._get(row, "verification_date")),
                "service_provider":  None,
                "supplier_id":       None,
                "performed_by":      self._get(row, "operator_id"),
                "edited_by":         self._get(row, "edit_by"),
                "reference_number":  self._get(row, "reference_standard"),
                "notes":             notes,
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
