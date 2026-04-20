"""
Transformer: inventory_items → reporting.inventory_items
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_date, to_timestamp


class InventoryItemsTransformer(BaseTransformer):
    table_key = "inventory_items"
    required_columns = ["id"]
    positive_columns = ["stock_in", "stock_out", "price"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                 row["id"],
                "inventory_sub_category_id": self._get(row, "inventory_sub_category_id"),
                "inventory_store_id":        self._get(row, "inventory_store_id"),
                "inventory_store_slot_id":   self._get(row, "inventory_store_slot_id"),
                "inventory_department_id":   self._get(row, "inventory_department_id"),
                "status":                    self._get(row, "status"),
                "stock_in":                  self._get(row, "stock_in") or 0,
                "stock_out":                 self._get(row, "stock_out") or 0,
                "expiry":                    to_date(self._get(row, "expiry")),
                "price":                     self._get(row, "price"),
                "source_created_at":         to_timestamp(self._get(row, "created_at")),
                "source_updated_at":         to_timestamp(self._get(row, "updated_at")),
                "synced_at":                 row["_synced_at"],
                "payload":                   row["_raw_payload"],
            })
        return pd.DataFrame(records)
