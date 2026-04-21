"""
Transformer: sample_analysis_stages → reporting.sample_analysis_stages
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_string


class SampleAnalysisStagesTransformer(BaseTransformer):
    table_key = "sample_analysis_stages"
    required_columns = ["id"]
    not_null_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":          row["id"],
                "sample_workflow":    to_string(self._get(row, "sample_workflow")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        df_res = pd.DataFrame(records)
        if "synced_at" in df_res.columns:
            df_res["synced_at"] = pd.to_datetime(df_res["synced_at"], errors='coerce')
        return df_res
