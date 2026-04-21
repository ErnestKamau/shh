"""
Transformer: analytes → reporting.analytes
Syncs the analyte dimension table from MySQL to Postgres.
Used as lookup by the QC Stability Board dashboard views.
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp


class AnalytesTransformer(BaseTransformer):
    table_key = "analytes"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            is_active = self._get(row, "is_active")
            records.append({
                "source_id":          row["id"],
                "code":               self._get(row, "code"),
                "name":               self._get(row, "name"),
                "reporting_unit":     self._get(row, "reporting_unit"),
                "decimal_places":     self._get(row, "decimal_places"),
                "is_active":          bool(is_active) if is_active is not None else True,
                "source_created_at":  to_timestamp(self._get(row, "created_at")),
                "source_updated_at":  to_timestamp(self._get(row, "updated_at")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        return pd.DataFrame(records)
