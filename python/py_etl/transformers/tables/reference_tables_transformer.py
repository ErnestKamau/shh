from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_timestamp

class GenericReferenceTransformer(BaseTransformer):
    """Generic transformer for reference tables (id, name, created_at)."""
    
    def __init__(self, table_key: str):
        self.table_key = table_key

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":          row["id"],
                "name":               self._get(row, "name"),
                "source_created_at":  to_timestamp(self._get(row, "created_at")),
                "source_updated_at":  to_timestamp(self._get(row, "updated_at")),
                "synced_at":          row["_synced_at"],
                "payload":            row["_raw_payload"],
            })
        return pd.DataFrame(records)

class TicketPrioritiesTransformer(GenericReferenceTransformer):
    def __init__(self):
        super().__init__("ticket_priorities")

class TicketStatusesTransformer(GenericReferenceTransformer):
    def __init__(self):
        super().__init__("ticket_statuses")

class TicketCategoriesTransformer(GenericReferenceTransformer):
    def __init__(self):
        super().__init__("ticket_categories")

class DepartmentsTransformer(GenericReferenceTransformer):
    def __init__(self):
        super().__init__("departments")

class SampleTypesTransformer(GenericReferenceTransformer):
    def __init__(self):
        super().__init__("sample_types")
