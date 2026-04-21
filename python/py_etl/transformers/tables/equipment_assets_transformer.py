"""
Transformer: equipment → reporting.equipment_assets
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import (
    to_bool, to_date, to_timestamp,
)


class EquipmentAssetsTransformer(BaseTransformer):
    table_key = "equipment"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                           row["id"],
                "name":                                self._get(row, "name"),
                "equipment_number":                    self._get(row, "equipment_number"),
                "status":                              self._get(row, "status"),
                "assigned_department":                 self._get(row, "assigned_department"),
                "assigned_employee_id":                self._get(row, "assigned_employee_id"),
                "date_purchased":                      to_date(self._get(row, "date_purchased")),
                "maintainance_days":                   self._get(row, "maintainance_days"),
                "maintainance_notification_in_days":   self._get(row, "maintainance_notification_in_days"),
                "calibration_days":                    self._get(row, "calibration_days"),
                "calibration_notification_in_days":    self._get(row, "calibration_notification_in_days"),
                "verification_days":                   self._get(row, "verification_days"),
                "warranty_date":                       to_date(self._get(row, "warranty_date")),
                "is_disposal":                         to_bool(self._get(row, "is_disposal", False)),
                "active":                              to_bool(self._get(row, "active", True)),
                "asset_type_id":                       self._get(row, "asset_type_id"),
                "asset_location_id":                   self._get(row, "asset_location_id"),
                "source_created_at":                   to_timestamp(self._get(row, "created_at")),
                "source_updated_at":                   to_timestamp(self._get(row, "updated_at")),
                "synced_at":                           row["_synced_at"],
                "payload":                             row["_raw_payload"],
            })
        return pd.DataFrame(records)
