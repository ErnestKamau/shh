"""
Transformer: qc_results → reporting.qc_results
Syncs raw QC result records (pass/fail/OOC) from MySQL to Postgres
for use by the QC Stability Board dashboard.
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp


class QcResultsTransformer(BaseTransformer):
    table_key = "qc_results"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            is_processed = self._get(row, "is_qc_processed")
            records.append({
                "source_id":            row["id"],
                "analyte_id":           self._get(row, "analyte_id"),
                "analyte_code":         self._get(row, "analyte_code"),
                "analyte_processed_id": self._get(row, "analyte_processed_id"),
                "sample_header_id":     self._get(row, "sample_header_id"),
                "sample_detail_id":     self._get(row, "sample_detail_id"),
                "sample_detail_code":   self._get(row, "sample_detail_code"),
                "result":               self._get(row, "result"),
                "status_code":          self._get(row, "status_code"),
                "guide_low":            self._get(row, "guide_low"),
                "guide_high":           self._get(row, "guide_high"),
                "unit_code":            self._get(row, "unit_code"),
                "is_qc_processed":      bool(is_processed) if is_processed is not None else True,
                "source_created_at":    to_timestamp(self._get(row, "created_at")),
                "source_updated_at":    to_timestamp(self._get(row, "updated_at")),
                "synced_at":            row["_synced_at"],
                "payload":              row["_raw_payload"],
            })
        return pd.DataFrame(records)
