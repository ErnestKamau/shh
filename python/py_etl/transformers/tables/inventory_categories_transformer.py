"""
Transformer: inventory_categories → reporting.inventory_categories
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_timestamp


class InventoryCategoriesTransformer(BaseTransformer):
    table_key = "inventory_categories"
    required_columns = ["id", "name"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":         row["id"],
                "name":              self._get(row, "name"),
                "description":       self._get(row, "description"),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
