"""
Transformer: corrective_actions → reporting.corrective_actions
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_date, to_timestamp


class CorrectiveActionsTransformer(BaseTransformer):
    table_key = "corrective_actions"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":           row["id"],
                "capa_number":         self._get(row, "capa_number"),
                "non_conformance_id":  self._get(row, "non_conformance_id"),
                "category":            self._get(row, "capa_category_name"),
                "action_type":         self._get(row, "action_type_name"),
                "status":              self._get(row, "status_name"),
                "priority":            self._get(row, "priority_name"),
                "owner":               self._get(row, "action_owner"),
                "due_date":            to_date(self._get(row, "due_date")),
                "implementation_date": to_date(self._get(row, "implementation_date")),
                "source_created_at":   to_timestamp(self._get(row, "created_at")),
                "source_updated_at":   to_timestamp(self._get(row, "updated_at")),
                "synced_at":           row["_synced_at"],
                "payload":             row["_raw_payload"],
            })
        return pd.DataFrame(records)
