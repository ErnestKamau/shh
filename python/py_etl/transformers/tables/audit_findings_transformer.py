"""
Transformer: audit_module_findings → reporting.audit_findings
"""
from __future__ import annotations
import pandas as pd
from python.py_etl.transformers.base_transformer import BaseTransformer
from python.py_etl.transformers.type_converters import to_date, to_timestamp


class AuditFindingsTransformer(BaseTransformer):
    table_key = "audit_findings"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":         row["id"],
                "audit_id":          self._get(row, "audit_id"),
                "finding_number":    self._get(row, "finding_number"),
                "category":          self._get(row, "finding_category_name"),
                "iso_clause":        self._get(row, "iso_clause"),
                "risk_level":        self._get(row, "risk_level_name"),
                "status":            self._get(row, "status_name"),
                "responsible_person": self._get(row, "responsible_person"),
                "due_date":          to_date(self._get(row, "response_due_date")),
                "source_created_at": to_timestamp(self._get(row, "created_at")),
                "source_updated_at": to_timestamp(self._get(row, "updated_at")),
                "synced_at":         row["_synced_at"],
                "payload":           row["_raw_payload"],
            })
        return pd.DataFrame(records)
