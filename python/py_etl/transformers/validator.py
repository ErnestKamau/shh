"""
Data Validator (PHASE 0 ENHANCED)
Pre-load quality checks executed after extraction and before loading.

PHASE 0 Enhancements:
- Quarantine mode: separate clean records from bad records (instead of warnings)
- Validation thresholds: fail entire batch if >X% invalid
- Critical vs non-critical columns: enforce strict validation for critical fields
"""
from __future__ import annotations

from dataclasses import dataclass, field
from typing import Any

import pandas as pd
from loguru import logger


@dataclass
class ValidationResult:
    """Summary of validation checks for a single DataFrame chunk (PHASE 0 Enhanced)."""

    table_key: str
    total_rows: int
    warnings: list[str] = field(default_factory=list)
    errors: list[str] = field(default_factory=list)
    
    # PHASE 0: Quarantine tracking
    clean_records: pd.DataFrame = field(default_factory=pd.DataFrame)
    quarantine_records: list[dict] = field(default_factory=list)
    invalid_ratio: float = 0.0

    @property
    def is_valid(self) -> bool:
        return len(self.errors) == 0

    @property
    def has_warnings(self) -> bool:
        return len(self.warnings) > 0
    
    @property
    def has_quarantine(self) -> bool:
        """PHASE 0: Check if any records were quarantined."""
        return len(self.quarantine_records) > 0

    def add_warning(self, msg: str) -> None:
        self.warnings.append(msg)
        logger.warning(f"[{self.table_key}] Validation warning: {msg}")

    def add_error(self, msg: str) -> None:
        self.errors.append(msg)
        logger.error(f"[{self.table_key}] Validation error: {msg}")

    def summary(self) -> str:
        parts = [f"{self.total_rows} rows"]
        if self.errors:
            parts.append(f"{len(self.errors)} error(s)")
        if self.warnings:
            parts.append(f"{len(self.warnings)} warning(s)")
        # PHASE 0: Add quarantine info
        if self.has_quarantine:
            parts.append(f"{len(self.quarantine_records)} quarantined ({self.invalid_ratio*100:.1f}%)")
        return ", ".join(parts)


class CriticalValidationError(Exception):
    """PHASE 0: Raised when validation violates a critical threshold."""
    pass


class DataValidator:
    """Runs configurable validation rules against a raw-source DataFrame (PHASE 0 Enhanced)."""

    def validate(
        self,
        df: pd.DataFrame,
        table_key: str,
        required_columns: list[str] | None = None,
        not_null_columns: list[str] | None = None,
        critical_columns: list[str] | None = None,
        positive_columns: list[str] | None = None,
    ) -> ValidationResult:
        """
        Execute all enabled checks and return a :class:`ValidationResult`.

        Args:
            df:                Raw source DataFrame (one chunk or full table).
            table_key:         Logical ETL table identifier.
            required_columns:  Column names that **must** exist in *df*.
            not_null_columns:  Column names that should not have NULLs (warnings).
            critical_columns:  Column names that MUST NOT be NULL (hard block).
            positive_columns:  Numeric columns whose values must be >= 0.

        Returns:
            :class:`ValidationResult` instance.
        """
        result = ValidationResult(table_key=table_key, total_rows=len(df))

        # 1 – Empty DataFrame
        if df.empty:
            result.add_warning("Source returned 0 rows – nothing to sync")
            return result

        # 2 – Required columns present
        for col in (required_columns or []):
            if col not in df.columns:
                result.add_error(f"Required column '{col}' is missing from source data")

        # 3 – Critical columns (PHASE 0: Hard block on NULL)
        for col in (critical_columns or []):
            if col not in df.columns:
                result.add_error(f"Critical column '{col}' is missing from source data")
                continue
            null_count = df[col].isna().sum()
            if null_count:
                result.add_error(
                    f"Critical column '{col}' has {null_count} NULL value(s) "
                    f"({null_count / len(df) * 100:.1f}%) – BLOCKING"
                )

        # 4 – Not-null columns (non-critical, warnings only)
        for col in (not_null_columns or []):
            if col not in df.columns:
                continue
            null_count = df[col].isna().sum()
            if null_count:
                result.add_warning(
                    f"Column '{col}' has {null_count} NULL value(s) "
                    f"({null_count / len(df) * 100:.1f}%)"
                )

        # 5 – Non-negative numeric columns
        for col in (positive_columns or []):
            if col not in df.columns:
                continue
            neg_count = (pd.to_numeric(df[col], errors="coerce").fillna(0) < 0).sum()
            if neg_count:
                result.add_warning(
                    f"Column '{col}' has {neg_count} negative value(s)"
                )

        # 6 – Duplicate primary-key check (generic 'id' column)
        if "id" in df.columns:
            dup_count = df["id"].duplicated().sum()
            if dup_count:
                result.add_warning(f"{dup_count} duplicate 'id' value(s) in source chunk")

        return result

    def validate_and_quarantine(
        self,
        df: pd.DataFrame,
        table_key: str,
        critical_columns: list[str] | None = None,
        threshold: float = 0.1,
        **kwargs: Any,
    ) -> ValidationResult:
        """
        PHASE 0: Validate and separate clean records from quarantine.
        
        If invalid_ratio > threshold, raises CriticalValidationError.
        
        Args:
            df:                Raw source DataFrame.
            table_key:         Logical ETL table identifier.
            critical_columns:  Columns that MUST NOT be NULL.
            threshold:         Fail if invalid_ratio exceeds this (0.1 = 10%).
            **kwargs:          Additional validation parameters.
            
        Returns:
            ValidationResult with clean_records and quarantine_records separated.
            
        Raises:
            CriticalValidationError: If invalid_ratio > threshold.
        """
        result = self.validate(df, table_key, critical_columns=critical_columns, **kwargs)
        
        if df.empty:
            result.clean_records = df
            return result
        
        # Separate clean from bad records
        clean_indices = []
        quarantine_list = []
        
        for idx, row in df.iterrows():
            errors = []
            
            # Check critical columns (NULL = bad)
            for col in (critical_columns or []):
                if col in df.columns and pd.isna(row[col]):
                    errors.append(f"NULL in critical field: {col}")
            
            # Check for missing primary key
            if "id" in df.columns and pd.isna(row["id"]):
                errors.append("Missing primary key (id)")
            
            if errors:
                quarantine_list.append({
                    'row_data': row.to_dict(),
                    'errors': errors,
                    'reason': errors[0]
                })
            else:
                clean_indices.append(idx)
        
        # Separate DataFrames
        result.clean_records = df.iloc[clean_indices] if clean_indices else pd.DataFrame()
        result.quarantine_records = quarantine_list
        
        # Calculate invalid ratio
        invalid_count = len(quarantine_list)
        result.invalid_ratio = invalid_count / len(df) if len(df) > 0 else 0.0
        
        # PHASE 0: Check threshold
        if result.invalid_ratio > threshold:
            error_msg = (
                f"[{table_key}] Invalid ratio {result.invalid_ratio*100:.1f}% "
                f"exceeds threshold {threshold*100:.1f}% – BLOCKING"
            )
            logger.error(error_msg)
            raise CriticalValidationError(error_msg)
        
        # Log results
        if result.has_quarantine:
            logger.warning(
                f"[{table_key}] Validation with quarantine – {result.summary()}"
            )
        else:
            logger.debug(f"[{table_key}] Validation passed – {result.summary()}")
        
        return result

    def validate_and_log(
        self,
        df: pd.DataFrame,
        table_key: str,
        **kwargs: Any,
    ) -> ValidationResult:
        """Convenience wrapper that validates and logs the summary."""
        result = self.validate(df, table_key, **kwargs)
        if result.is_valid:
            logger.debug(f"[{table_key}] Validation passed – {result.summary()}")
        else:
            logger.error(f"[{table_key}] Validation FAILED – {result.summary()}")
        return result


# Shared instance
validator = DataValidator()
