"""
Transformer: sample_details → reporting.sample_details
Maps all meaningful columns from MySQL sample_details to the PostgreSQL
reporting schema. sample_header_id is preserved as a FK that joins to
reporting.sample_headers.source_id.
"""
from __future__ import annotations

import pandas as pd

from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp, to_string


class SampleDetailsTransformer(BaseTransformer):
    table_key = "sample_details"
    required_columns = ["id", "sample_header_id"]
    not_null_columns = ["id", "sample_header_id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":           row["id"],
                "sample_header_id":    self._get(row, "sample_header_id"),
                "sample_code":         to_string(self._get(row, "sample_code")),
                "analysis_type_id":    to_string(self._get(row, "analysis_type_id")),
                "sample_condition_id": to_string(self._get(row, "sample_condition_id")),
                "barcode":             to_string(self._get(row, "barcode")),
                "comments":            to_string(self._get(row, "comments")),
                "gps":                 to_string(self._get(row, "gps")),
                "sample_point_id":     self._get(row, "sample_point_id"),
                "company_product_id":  self._get(row, "company_product_id"),
                "main_body":           to_string(self._get(row, "main_body")),
                "header_body":         to_string(self._get(row, "header_body")),
                "lab_sub_no":          to_string(self._get(row, "lab_sub_no")),
                "ammendment_number":   self._get(row, "ammendment_number", 0),
                "main_standard":       to_string(self._get(row, "main_standard")),
                "secondary_standard":  to_string(self._get(row, "secondary_standard")),
                "notes_body":          to_string(self._get(row, "notes_body")),
                "source_created_at":   to_timestamp(self._get(row, "created_at")),
                "source_updated_at":   to_timestamp(self._get(row, "updated_at")),
                "synced_at":           row["_synced_at"],
                "payload":             row["_raw_payload"],
            })
        df_res = pd.DataFrame(records)
        for col in ["sample_header_id", "sample_point_id", "company_product_id", "ammendment_number"]:
            if col in df_res.columns:
                df_res[col] = pd.to_numeric(df_res[col], errors='coerce').astype('Int64')
        for col in ["source_created_at", "source_updated_at", "synced_at"]:
            if col in df_res.columns:
                df_res[col] = pd.to_datetime(df_res[col], errors='coerce')
        return df_res
