"""
Transformer: inventory_sub_categories → reporting.inventory_sub_categories
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp


class InventorySubCategoriesTransformer(BaseTransformer):
    table_key = "inventory_sub_categories"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                                        row["id"],
                "name":                                             self._get(row, "name"),
                "inventory_category_id":                            self._get(row, "inventory_category_id"),
                "minimum_level":                                    self._get(row, "minimum_level"),
                "unit_type":                                        self._get(row, "unit_type"),
                "unit_price":                                       self._get(row, "unit_price"),
                "code":                                             self._get(row, "code"),
                "annual_consumption":                               self._get(row, "annual_consumption"),
                "working_days":                                     self._get(row, "working_days"),
                "internal_lead_time":                               self._get(row, "internal_lead_time"),
                "external_lead_time":                               self._get(row, "external_lead_time"),
                "estimated_variation_in_demand_average_consumption": self._get(row, "estimated_variation_in_demand_average_consumption"),
                "maximum_order_quantity":                           self._get(row, "maximum_order_quantity"),
                "source_created_at":                                to_timestamp(self._get(row, "created_at")),
                "source_updated_at":                                to_timestamp(self._get(row, "updated_at")),
                "synced_at":                                        row["_synced_at"],
                "payload":                                          row["_raw_payload"],
            })
        return pd.DataFrame(records)
