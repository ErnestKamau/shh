"""
Transformer: documents → reporting.documents
"""
from __future__ import annotations
import pandas as pd
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import (
    to_bool, to_date, to_timestamp, to_json_value,
)


class DocumentsTransformer(BaseTransformer):
    table_key = "documents"
    required_columns = ["id"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        records = []
        for _, row in df.iterrows():
            records.append({
                "source_id":                          row["id"],
                "name":                               self._get(row, "name"),
                "document_type_id":                   self._get(row, "document_type_id"),
                "department_id":                      self._get(row, "department_id"),
                "version":                            self._get(row, "version"),
                "validity_period":                    to_date(self._get(row, "validity_period")),
                "status":                             self._get(row, "status"),
                "is_current_version":                 to_bool(self._get(row, "is_current_version", True)),
                "is_active":                          to_bool(self._get(row, "is_active", True)),
                "is_published":                       to_bool(self._get(row, "is_published", False)),
                "publish_scope":                      self._get(row, "publish_scope"),
                "publish_targets":                    to_json_value(self._get(row, "publish_targets")),
                "created_by":                         self._get(row, "created_by"),
                "approved_by":                        self._get(row, "approved_by"),
                "approved_at":                        to_timestamp(self._get(row, "approved_at")),
                "published_by":                       self._get(row, "published_by"),
                "published_at":                       to_timestamp(self._get(row, "published_at")),
                "notification_frequency_id":          self._get(row, "notification_frequency_id"),
                "notification_days_before_expiry":    self._get(row, "notification_days_before_expiry"),
                "last_notification_sent":             to_timestamp(self._get(row, "last_notification_sent")),
                "notifications_enabled":              to_bool(self._get(row, "notifications_enabled", False)),
                "source_created_at":                  to_timestamp(self._get(row, "created_at")),
                "source_updated_at":                  to_timestamp(self._get(row, "updated_at")),
                "synced_at":                          row["_synced_at"],
                "payload":                            row["_raw_payload"],
            })
        return pd.DataFrame(records)
