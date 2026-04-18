from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_timestamp

class UsersTransformer(BaseTransformer):
    """Transformer for users -> reporting.users (Security Filtered)."""
    table_key = "users"
    required_columns = ["id", "name"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":          row["id"],
                "name":               self._get(row, "name"),
                "email":              self._get(row, "email"),
                "department_id":      self._get(row, "department_id"),
                "lab_section_id":     self._get(row, "lab_section_id"),
                "position":           self._get(row, "position"),
                "designation":        self._get(row, "designation"),
                "active":             bool(self._get(row, "active", 1)),
                "source_created_at":  to_timestamp(self._get(row, "created_at")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        return pd.DataFrame(records)
