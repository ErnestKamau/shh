"""
Type Converters
Python equivalents of the PHP helper methods found in
OperationalFoundationSyncService (toBool, toTimestamp, toDate,
toString, toJson, toJsonValue).

All functions are pure – they accept a single raw value and return a
cleanly typed result or ``None`` when the input is absent / unparseable.
"""
from __future__ import annotations

import json
from datetime import datetime, date
from typing import Any

import pandas as pd


# ── Sentinel for "missing" ─────────────────────────────────────────────────


def _is_empty(value: Any) -> bool:
    """Return True when *value* should be treated as NULL."""
    if value is None:
        return True
    # Covers float NaN, pd.NaT, pd.NA, numpy NaN in a single call
    try:
        if pd.isna(value):
            return True
    except (TypeError, ValueError):
        pass
    if isinstance(value, str) and value.strip() == "":
        return True
    return False


# ── Individual converters ──────────────────────────────────────────────────


def to_bool(value: Any) -> bool | None:
    """
    Coerce *value* to a Python bool.

    Mirrors PHP ``filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $v``.

    Returns:
        ``True`` / ``False``, or ``None`` if *value* is empty/None.
    """
    if _is_empty(value):
        return None

    if isinstance(value, bool):
        return value

    if isinstance(value, (int, float)):
        return bool(value)

    truthy = {"1", "true", "yes", "on"}
    falsy = {"0", "false", "no", "off"}
    normalized = str(value).strip().lower()
    if normalized in truthy:
        return True
    if normalized in falsy:
        return False
    return bool(value)


def to_timestamp(value: Any) -> str | None:
    """
    Parse *value* to a ``'YYYY-MM-DD HH:MM:SS'`` string.

    Mirrors PHP ``Carbon::parse($v)->format('Y-m-d H:i:s')``.

    Returns:
        Formatted datetime string, or ``None`` on failure.
    """
    if _is_empty(value):
        return None

    # Already a datetime / date object
    if isinstance(value, datetime):
        return value.strftime("%Y-%m-%d %H:%M:%S")
    if isinstance(value, date):
        return datetime(value.year, value.month, value.day).strftime("%Y-%m-%d %H:%M:%S")

    # pandas Timestamp
    if isinstance(value, pd.Timestamp):
        if pd.isna(value):
            return None
        return value.strftime("%Y-%m-%d %H:%M:%S")

    # String – let pandas parse it
    try:
        ts = pd.to_datetime(str(value), errors="raise")
        return ts.strftime("%Y-%m-%d %H:%M:%S")
    except Exception:
        return None


def to_date(value: Any) -> str | None:
    """
    Parse *value* to a ``'YYYY-MM-DD'`` date string.

    Mirrors PHP ``Carbon::parse($v)->toDateString()``.

    Returns:
        Date string, or ``None`` on failure.
    """
    ts = to_timestamp(value)
    if ts is None:
        return None
    return ts[:10]


def to_string(value: Any) -> str | None:
    """
    Cast *value* to a stripped string, or ``None`` when empty.

    Mirrors PHP ``(string) $value``.
    """
    if _is_empty(value):
        return None
    return str(value)

def to_int(value: Any) -> int | None:
    """
    Cast *value* to an int, or ``None`` when empty or invalid.
    """
    if _is_empty(value):
        return None
    try:
        return int(float(value))
    except (ValueError, TypeError):
        return None



def to_json(row: Any) -> str:
    """
    Serialise *row* (a dict, pandas Series, or object with ``__dict__``) to a
    JSON string.  This stores the full raw source payload alongside the mapped
    fields – mirrors PHP ``json_encode((array) $row)``.

    Returns:
        JSON string (never ``None``).
    """
    if isinstance(row, dict):
        data = row
    elif isinstance(row, pd.Series):
        data = row.where(pd.notnull(row), None).to_dict()
    elif hasattr(row, "__dict__"):
        data = vars(row)
    else:
        data = {"value": to_string(row)}

    # Make sure dates / datetimes are serialisable
    def _default(obj: Any) -> Any:
        if isinstance(obj, (datetime, date)):
            return obj.isoformat()
        if isinstance(obj, pd.Timestamp):
            return obj.isoformat() if not pd.isna(obj) else None
        raise TypeError(f"Object of type {type(obj)} is not JSON serialisable")

    return json.dumps(data, default=_default, ensure_ascii=False)


def to_json_value(value: Any) -> str | None:
    """
    Ensure *value* is a valid JSON string.

    - If already valid JSON → return as-is.
    - If a plain string → wrap in JSON string encoding.
    - If a dict/list → serialise.
    - Empty / None → ``None``.

    Mirrors PHP ``toJsonValue()``.
    """
    if _is_empty(value):
        return None

    if isinstance(value, (dict, list)):
        return json.dumps(value, ensure_ascii=False)

    if isinstance(value, str):
        try:
            json.loads(value)
            return value          # Already valid JSON
        except json.JSONDecodeError:
            return json.dumps(value)

    return json.dumps(to_string(value))


# ── Batch DataFrame helpers ────────────────────────────────────────────────


def coerce_bool_series(series: pd.Series) -> pd.Series:
    """Apply :func:`to_bool` element-wise to a pandas Series."""
    return series.map(to_bool)


def coerce_timestamp_series(series: pd.Series) -> pd.Series:
    """Apply :func:`to_timestamp` element-wise to a pandas Series."""
    return series.map(to_timestamp)


def coerce_date_series(series: pd.Series) -> pd.Series:
    """Apply :func:`to_date` element-wise to a pandas Series."""
    return series.map(to_date)


def coerce_string_series(series: pd.Series) -> pd.Series:
    """Apply :func:`to_string` element-wise to a pandas Series."""
    return series.map(to_string)
