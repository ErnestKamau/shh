"""
Transformer: inventory_orders → reporting.inventory_orders
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_timestamp


class InventoryOrdersTransformer(BaseTransformer):
    table_key = "inventory_orders"
    required_columns = ["id", "order_number"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":         row["id"],
                "order_number":      self._get(row, "order_number"),
                "supplier_id":       self._get(row, "supplier_id"),
                "status":            self._get(row, "status"),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
