"""
Transformer: sample_headers → reporting.sample_headers
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import (
    to_bool, to_timestamp, to_string, to_int
)

class SampleHeadersTransformer(BaseTransformer):
    table_key = "sample_headers"
    required_columns = ["id"]
    not_null_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":          row["id"],
                "batch_code":         self._get(row, "batch_code"),
                "status":             self._get(row, "status"),
                "crm_customer_id":    to_int(self._get(row, "crm_customer_id")),
                "verify_user_id":     to_int(self._get(row, "verify_user_id")),
                "approve_user_id":    to_int(self._get(row, "approve_user_id")),
                "created_by":         to_int(self._get(row, "created_by")),
                "is_qc_batch":        to_bool(self._get(row, "is_qc_batch", False)),
                "isactive":           to_bool(self._get(row, "isactive", True)),
                "processing_date":    to_timestamp(self._get(row, "processing_date")),
                "approval_date_at":   to_timestamp(self._get(row, "approval_date")),
                "approval_date_raw":  to_string(self._get(row, "approval_date")),
                "sample_tracking_stage": to_int(self._get(row, "sample_tracking_stage")),
                "source_created_at":  to_timestamp(self._get(row, "created_at")),
                "source_updated_at":  to_timestamp(self._get(row, "updated_at")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        df_res = pd.DataFrame(records)
        for col in ["crm_customer_id", "verify_user_id", "approve_user_id", "created_by"]:
            if col in df_res.columns:
                df_res[col] = pd.to_numeric(df_res[col], errors='coerce').astype('Int64')
        for col in ["processing_date", "approval_date_at", "source_created_at", "source_updated_at", "synced_at"]:
            if col in df_res.columns:
                df_res[col] = pd.to_datetime(df_res[col], errors='coerce')
        return df_res
