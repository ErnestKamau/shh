"""
Transformer: captured_results → reporting.captured_results
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_timestamp, to_string


class CapturedResultsTransformer(BaseTransformer):
    table_key = "captured_results"
    required_columns = ["id"]
    not_null_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":           row["id"],
                "sample_header_id":    self._get(row, "sample_header_id"),
                "sample_detail_id":    self._get(row, "sample_detail_id"),
                "analysis_type_id":    self._get(row, "analysis_type_id"),
                "analyte_id":          self._get(row, "analyte_id"),
                "operator_id":         self._get(row, "operator_id"),
                "equipment_id":        self._get(row, "equipment_id"),
                "result_value":        to_string(self._get(row, "result")),
                "analyte_code":        self._get(row, "analyte_code"),
                "sample_detail_code":  self._get(row, "sample_detail_code"),
                "machine_update_date": to_timestamp(self._get(row, "machine_update_date")),
                "source_created_at":   to_timestamp(self._get(row, "created_at")),
                "source_updated_at":   to_timestamp(self._get(row, "updated_at")),
                "synced_at":           row["_synced_at"],
                "payload":             row["_raw_payload"],
            })
        return pd.DataFrame(records)
