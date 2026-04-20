"""
Transformer: tat_captured → reporting.tat_captured
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import (
    to_bool, to_timestamp, to_string,
)


class TatCapturedTransformer(BaseTransformer):
    table_key = "tat_captured"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":           row["id"],
                "captured_result_id":  self._get(row, "captured_result_id"),
                "analysis_type_id":    self._get(row, "analysis_type_id"),
                "analyte_id":          self._get(row, "analyte_id"),
                "sample_type_id":      self._get(row, "sample_type_id"),
                "sample_detail_id":    self._get(row, "sample_detail_id"),
                "analyst_id":          self._get(row, "analyst_id"),
                "sample_header_id":    self._get(row, "sample_header_id"),
                "result_value":        to_string(self._get(row, "result")),
                "tat_overdue_days":    self._get(row, "tat_overdue_days") or 0,
                "tat_date":            to_timestamp(self._get(row, "tat_date")),
                "finished_date":       to_timestamp(self._get(row, "finished_date")),
                "is_complete":         to_bool(self._get(row, "is_complete", False)),
                "tat_remark":          self._get(row, "tat_remark"),
                "source_created_at":   to_timestamp(self._get(row, "created_at")),
                "source_updated_at":   to_timestamp(self._get(row, "updated_at")),
                "synced_at":           row["_synced_at"],
                "payload":             row["_raw_payload"],
            })
        return pd.DataFrame(records)
