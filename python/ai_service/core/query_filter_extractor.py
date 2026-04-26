"""
QueryFilterExtractor — Lightweight Filter Detection

Extracts simple date, status, and limit filters from user queries
using pattern matching. No NLP, no LLM — just regex.

Supported filters:
    Dates:   today, yesterday, last 7 days, this week, this month, this quarter
    Status:  pending, completed, rejected, approved, overdue, open, closed
    Limits:  top 5, latest 10, last 3, first 20
"""

import re
import logging
from typing import Dict, Any, Optional
from datetime import date, timedelta

logger = logging.getLogger(__name__)


# ── Date patterns ─────────────────────────────────────────────────────────

_DATE_PATTERNS: list[tuple[re.Pattern, str]] = [
    (re.compile(r"\btoday\b", re.I), "today"),
    (re.compile(r"\byesterday\b", re.I), "yesterday"),
    (re.compile(r"\blast\s*(?:7|seven)\s*days?\b", re.I), "last_7_days"),
    (re.compile(r"\bthis\s*week\b", re.I), "this_week"),
    (re.compile(r"\blast\s*week\b", re.I), "last_week"),
    (re.compile(r"\bthis\s*month\b", re.I), "this_month"),
    (re.compile(r"\blast\s*month\b", re.I), "last_month"),
    (re.compile(r"\bthis\s*quarter\b", re.I), "this_quarter"),
    (re.compile(r"\blast\s*(?:30|thirty)\s*days?\b", re.I), "last_30_days"),
    (re.compile(r"\blast\s*(?:90|ninety)\s*days?\b", re.I), "last_90_days"),
]

# ── Status patterns ───────────────────────────────────────────────────────

_STATUS_KEYWORDS = {
    "pending": ["pending", "awaiting", "waiting"],
    "completed": ["completed", "complete", "finished", "done"],
    "rejected": ["rejected", "cancelled", "canceled", "failed"],
    "approved": ["approved", "signed off", "sign-off"],
    "overdue": ["overdue", "past due", "late", "exceeded"],
    "open": ["open", "active", "unresolved", "outstanding"],
    "closed": ["closed", "resolved", "settled"],
    "in_progress": ["in progress", "processing", "in-progress", "ongoing"],
}

# ── Limit patterns ────────────────────────────────────────────────────────

_LIMIT_PATTERN = re.compile(
    r"\b(?:top|latest|last|first|bottom|recent)\s+(\d{1,3})\b", re.I
)


def _resolve_date_range(label: str) -> tuple[str, str]:
    """Convert a date label to (start_date, end_date) ISO strings."""
    today = date.today()

    if label == "today":
        return str(today), str(today)
    if label == "yesterday":
        y = today - timedelta(days=1)
        return str(y), str(y)
    if label == "last_7_days":
        return str(today - timedelta(days=7)), str(today)
    if label == "this_week":
        start = today - timedelta(days=today.weekday())  # Monday
        return str(start), str(today)
    if label == "last_week":
        end = today - timedelta(days=today.weekday() + 1)
        start = end - timedelta(days=6)
        return str(start), str(end)
    if label == "this_month":
        return str(today.replace(day=1)), str(today)
    if label == "last_month":
        first_this = today.replace(day=1)
        last_prev = first_this - timedelta(days=1)
        first_prev = last_prev.replace(day=1)
        return str(first_prev), str(last_prev)
    if label == "this_quarter":
        q_start_month = ((today.month - 1) // 3) * 3 + 1
        return str(today.replace(month=q_start_month, day=1)), str(today)
    if label == "last_30_days":
        return str(today - timedelta(days=30)), str(today)
    if label == "last_90_days":
        return str(today - timedelta(days=90)), str(today)

    return str(today - timedelta(days=30)), str(today)


class QueryFilterExtractor:
    """
    Extract lightweight filters from a user query.

    Returns a dict with optional keys:
        date_label:  str   — e.g. "today", "this_month"
        date_start:  str   — ISO date
        date_end:    str   — ISO date
        status:      str   — e.g. "pending", "completed"
        limit:       int   — e.g. 5, 10
    """

    def extract(self, query: str) -> Dict[str, Any]:
        filters: Dict[str, Any] = {}
        q = query.lower().strip()

        # Date extraction (first match wins)
        for pattern, label in _DATE_PATTERNS:
            if pattern.search(q):
                filters["date_label"] = label
                start, end = _resolve_date_range(label)
                filters["date_start"] = start
                filters["date_end"] = end
                break

        # Status extraction (first match wins)
        for status, keywords in _STATUS_KEYWORDS.items():
            if any(kw in q for kw in keywords):
                filters["status"] = status
                break

        # Limit extraction
        limit_match = _LIMIT_PATTERN.search(q)
        if limit_match:
            filters["limit"] = min(int(limit_match.group(1)), 100)

        if filters:
            logger.info(f"QueryFilterExtractor: Extracted filters: {filters}")

        return filters
