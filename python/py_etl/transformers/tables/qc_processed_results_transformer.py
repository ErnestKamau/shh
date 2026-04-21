"""
Transformer: qc_processed_result → reporting.qc_processed_results
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp


class QcProcessedResultsTransformer(BaseTransformer):
    table_key = "qc_processed_result"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                  row["id"],
                "sample_type_id":             self._get(row, "sample_type_id"),
                "analysis_type_id":           self._get(row, "analysis_type_id"),
                "analyte_id":                 self._get(row, "analyte_id"),
                "method_id":                  self._get(row, "method_id"),
                "standard_id":                self._get(row, "standard_id"),
                "standard_value_id":          self._get(row, "standard_value_id"),
                "robust_standard_deviation":  self._get(row, "robust_standard_deviation"),
                "robust_mean":                self._get(row, "robust_mean"),
                "robust_median":              self._get(row, "robust_median"),
                "robust_cv":                  self._get(row, "robust_cv"),
                "robust_cv_percentage":       self._get(row, "robust_cv_percentage"),
                "source_created_at":          to_timestamp(self._get(row, "created_at")),
                "source_updated_at":          to_timestamp(self._get(row, "updated_at")),
                "synced_at":                  row["_synced_at"],
                "payload":                    row["_raw_payload"],
            })
        return pd.DataFrame(records)
