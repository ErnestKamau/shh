"""
Transformer: suppliers → reporting.suppliers
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp


class SuppliersTransformer(BaseTransformer):
    table_key = "suppliers"
    required_columns = ["id", "name"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":         row["id"],
                "name":              self._get(row, "name"),
                "email":             self._get(row, "email"),
                "phone":             self._get(row, "phone"),
                "active":            self._get(row, "active"),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
