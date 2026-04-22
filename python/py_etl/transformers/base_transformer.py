"""
Base Transformer
Abstract template for all table-specific transformers.
Each concrete subclass implements :meth:`transform` to map a raw-source
DataFrame chunk to the target PostgreSQL schema.
"""
from __future__ import annotations

from abc import ABC, abstractmethod
from datetime import datetime

import pandas as pd
from loguru import logger

from py_etl.transformers.type_converters import to_json


class BaseTransformer(ABC):
    """
    Abstract base for IMARA ETL table transformers.

    Subclasses must implement :meth:`transform`.  The default :meth:`run`
    method adds ``synced_at`` and ``payload`` columns automatically so
    concrete classes do not have to repeat that logic.
    """

    # Subclasses can override these to enable automatic validation
    required_columns: list[str] = []
    not_null_columns: list[str] = []
    positive_columns: list[str] = []

    # The table key this transformer handles (e.g. "sample_headers")
    table_key: str = ""

    @abstractmethod
    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Map *df* (raw source columns) to a target-schema DataFrame.

        Args:
            df: One chunk of raw source data from MySQL.

        Returns:
            Transformed DataFrame ready for PostgreSQL upsert.
        """

    def run(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Orchestrate the full transform pipeline for a single chunk:

        1. Add ``synced_at`` timestamp.
        2. Add ``payload`` JSON column (full raw row).
        3. Call :meth:`transform` for table-specific mapping.

        Args:
            df: Raw source chunk.

        Returns:
            Transformed DataFrame.
        """
        if df.empty:
            return df

        # Attach auditing columns to the raw frame before transformation
        from datetime import timezone
        now_str = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S%z")
        df = df.copy()
        df["_synced_at"] = now_str
        df["_raw_payload"] = df.apply(lambda row: to_json(row), axis=1)

        transformed = self.transform(df)

        logger.debug(
            f"[{self.table_key}] Transformed {len(df)} → {len(transformed)} rows"
        )
        return transformed

    # ── Shared helpers ────────────────────────────────────────────────────

    @staticmethod
    def _get(row: pd.Series, col: str, default=None):
        """Safe column access that returns *default* for missing/NaN fields."""
        val = row.get(col, default)
        if val is None:
            return default
        if isinstance(val, float) and pd.isna(val):
            return default
        return val
