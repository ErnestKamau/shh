"""
Transformer: complaints → reporting.complaints
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_bool, to_timestamp


class ComplaintsTransformer(BaseTransformer):
    table_key = "complaints"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                   row["id"],
                "ticket_no":                   self._get(row, "ticket_no"),
                "complaint_id":                self._get(row, "complaint_id"),
                "priority":                    self._get(row, "priority"),
                "sla_level":                   self._get(row, "sla_level"),
                "complaint_workflow":           self._get(row, "complaint_workflow"),
                "is_closed":                   to_bool(self._get(row, "is_closed", False)),
                "date_received":               to_timestamp(self._get(row, "date")),
                "time_created":                to_timestamp(self._get(row, "time_created")),
                "first_response_at":           to_timestamp(self._get(row, "first_response_at")),
                "first_response_sla_status":   self._get(row, "first_response_sla_status"),
                "resolution_sla_status":       self._get(row, "resolution_sla_status"),
                "resolved_time":               to_timestamp(self._get(row, "resolved_time")),
                "assigned_to":                 self._get(row, "assigned_to"),
                "escalated_to_user_id":        self._get(row, "escalated_to_user_id"),
                "escalated_from_user_id":      self._get(row, "escalated_from_user_id"),
                "client_id":                   self._get(row, "client_id"),
                "current_department":          self._get(row, "current_department"),
                "submitted_from":              self._get(row, "submitted_from"),
                "source_created_at":           to_timestamp(self._get(row, "created_at")),
                "source_updated_at":           to_timestamp(self._get(row, "updated_at")),
                "synced_at":                   row["_synced_at"],
                "payload":                     row["_raw_payload"],
            })
        return pd.DataFrame(records)
