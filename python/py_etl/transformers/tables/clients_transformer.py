from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp

class ClientsTransformer(BaseTransformer):
    """Transformer for crm_customers -> reporting.clients."""
    table_key = "clients"
    required_columns = ["id", "name"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":          row["id"],
                "code":               self._get(row, "code"),
                "name":               self._get(row, "name"),
                "email":              self._get(row, "email"),
                "active":             bool(self._get(row, "active", 1)),
                "country_id":         self._get(row, "country_id"),
                "zoho_id":            self._get(row, "zoho_id"),
                "source_created_at":  to_timestamp(self._get(row, "created_at")),
                "source_updated_at":  to_timestamp(self._get(row, "updated_at")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        return pd.DataFrame(records)
