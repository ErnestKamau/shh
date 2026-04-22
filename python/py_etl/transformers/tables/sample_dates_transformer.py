"""
Transformer: sample_dates → reporting.sample_dates
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp, to_string


class SampleDatesTransformer(BaseTransformer):
    table_key = "sample_dates"
    required_columns = ["id"]
    not_null_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":         row["id"],
                "sample_header_id":  self._get(row, "sample_header_id"),
                "name":              self._get(row, "name"),
                "event_date":        to_timestamp(self._get(row, "date")),
                "event_date_raw":    to_string(self._get(row, "date")),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
