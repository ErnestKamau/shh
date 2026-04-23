"""
ManifestIntentRouter — Deterministic Rule-Based Intent Matching

Tier 1 routing layer for the SimpleAssistant. Maps user queries to SQL
manifest intent names using keyword/phrase patterns. Only queries that
don't match here fall through to the LLM classifier (Tier 2).

Architecture:
    SimpleAssistant
    → check exact IDs (QueryClassifier)
    → rule-based manifest routing (this module)
    → if no match → LLM classify
    → execute SQL or RAG
    → log request

Groups:
    A: Deterministic keyword routes (~25 intents)
    B: Rule-first, LLM fallback (~5-10 intents)
    C: LLM/RAG only (not in this router)
"""

import re
import logging
from typing import Optional, Tuple, List

logger = logging.getLogger(__name__)


# ── Conversational short-circuit patterns ────────────────────────────────
GREETING_PATTERNS = re.compile(
    r"^(hi|hello|hey|good\s*(morning|afternoon|evening)|"
    r"thanks|thank\s*you|bye|goodbye|how\s*are\s*you|"
    r"what\'?s\s*up|cheers|greetings|howdy|yo)\b",
    re.IGNORECASE,
)


# ── Group A: Deterministic keyword routes ────────────────────────────────
# Each entry: list of patterns → intent name
# Patterns are checked as substring matches (lowercased query).
# Order matters within a group: more specific patterns first.

_GROUP_A_RULES: list[tuple[list[str], str]] = [
    # ── Samples: counts ──
    (["samples in the lab", "sample count in lab", "how many samples in lab",
      "how many samples are in the lab", "how many samples are in lab",
      "samples currently in lab", "individual samples in lab"],
     "sample_count_in_lab"),

    (["batches in the lab", "batch count in lab", "how many batches in lab",
      "batches currently in lab"],
     "batch_count_in_lab"),

    (["samples today", "sample count today", "batches today",
      "how many samples today", "received today", "batches received today"],
     "sample_count_today"),

    (["samples this week", "sample count this week", "batches this week",
      "how many samples this week", "received this week"],
     "sample_count_this_week"),

    (["samples this month", "sample count this month", "batches this month",
      "how many samples this month", "received this month"],
     "sample_count_this_month"),

    (["rejected sample", "rejected batch", "cancelled sample",
      "samples rejected", "how many rejected"],
     "samples_rejected"),

    (["samples pending review", "pending review", "awaiting review",
      "sample review", "approval pending", "pending approval",
      "samples awaiting approval"],
     "sample_count_pending_review"),

    (["samples by status", "sample status breakdown", "status breakdown",
      "sample distribution", "status distribution"],
     "samples_by_status"),

    (["total samples", "sample count total", "how many samples",
      "total sample count", "number of samples", "all samples"],
     "sample_count_total"),

    (["individual sample count", "individual samples", "aliquots",
      "sample items count", "total individual"],
     "individual_sample_count"),

    (["total batches", "batch count", "how many batches",
      "number of batches", "all batches"],
     "batch_count_total"),

    (["latest batch", "latest received", "recent batch",
      "last received batch", "newest batch", "recently received"],
     "latest_received_batches"),

    (["daily ingestion", "ingestion trend", "registration volume",
      "daily registration", "batch registration trend"],
     "daily_ingestion_trend"),

    (["sample type", "specimen type", "matrix type",
      "sample type distribution", "types of samples"],
     "sample_type_distribution"),

    # ── Inventory ──
    (["low stock", "below minimum", "reorder level", "stock shortage",
      "items below minimum", "running low"],
     "inventory_low_stock"),

    (["order status", "purchase order", "pending delivery",
      "awaiting delivery", "order fulfillment"],
     "inventory_order_status"),

    (["supplier performance", "supplier fulfillment", "vendor performance",
      "supplier rate"],
     "supplier_order_performance"),

    (["stock by category", "inventory by category", "inventory health",
      "category stock", "inventory summary"],
     "inventory_stock_by_category"),

    (["expiring soon", "inventory expiring", "expiry risk",
      "items expiring", "reagent expiry", "expiration date"],
     "inventory_expiring_soon"),

    # ── Equipment ──
    (["equipment utilization", "instrument usage", "equipment usage",
      "most used equipment", "instrument utilization"],
     "equipment_utilization"),

    (["maintenance schedule", "upcoming maintenance", "maintenance due",
      "scheduled maintenance", "next maintenance"],
     "equipment_maintenance_schedule"),

    (["equipment downtime", "equipment down", "non-operational",
      "overdue equipment", "broken equipment", "equipment overdue"],
     "equipment_downtime_summary"),

    (["active equipment", "active instruments", "equipment count",
      "how many instruments", "total equipment"],
     "equipment_count_active"),

    (["maintenance health", "maintenance status", "instrument maintenance state",
      "equipment maintenance state"],
     "equipment_maintenance_health"),

    (["equipment verification", "verification status",
      "instrument verification", "last verification"],
     "equipment_verification_status"),

    # ── Quality / QC ──
    (["qc pass rate", "completion rate", "batch completion",
      "quality pass rate", "qc percentage"],
     "qc_pass_rate"),

    (["drifting analyte", "analyte drift", "qc drift",
      "unstable analyte", "analyte instability", "qc stability"],
     "qc_drifting_analytes"),

    (["pending capa", "open capa", "corrective action",
      "capa count", "open corrective"],
     "capa_pending"),

    (["pending qc", "samples pending qc", "awaiting qc",
      "qc review", "samples for qc"],
     "samples_pending_qc"),

    (["complaint trend", "complaint volume", "monthly complaint",
      "complaints over time"],
     "complaints_trend"),

    (["audit finding", "open audit", "audit finding open",
      "unresolved audit"],
     "audit_findings_open"),

    # ── TAT ──
    (["average tat", "turnaround time", "mean tat", "tat average",
      "lab turnaround", "overall tat"],
     "tat_overall_average"),

    (["overdue batch", "tat overdue", "overdue tat",
      "batches overdue", "past deadline"],
     "tat_overdue_batches"),

    (["tat by analyte", "analyte tat", "tat bottleneck",
      "slowest analyte", "analyte turnaround"],
     "tat_by_analyte"),

    (["tat sla", "sla compliance", "within sla", "sla target",
      "tat compliance"],
     "tat_sla_compliance"),

    # ── Support ──
    (["open complaint", "open ticket", "complaint count",
      "active complaint", "unresolved complaint", "complaints open"],
     "complaint_count_open"),

    (["ticket backlog", "ticket priority", "support backlog",
      "open tickets by priority"],
     "ticket_backlog_priority"),

    (["resolution time", "ticket resolution", "average resolution",
      "how long to resolve"],
     "ticket_resolution_time"),

    (["tickets by department", "department ticket", "department backlog"],
     "tickets_by_department"),

    # ── Personnel ──
    (["analyst verification", "batches verified", "who verified",
      "verification count", "analyst verified"],
     "analyst_verifications"),

    (["analyst approval", "batches approved", "who approved",
      "approval count", "analyst approved"],
     "analyst_approvals"),

    (["analyst workload", "analyst activity today", "workload today",
      "who is working"],
     "analyst_workload_today"),

    # ── CRM ──
    (["top client", "top customer", "biggest client",
      "most samples client", "client volume", "customer ranking"],
     "top_clients_by_volume"),

    (["inactive client", "dormant client", "churn risk",
      "client no submission", "inactive customer"],
     "inactive_clients"),

    (["client submission today", "customer submission today",
      "submissions today by client"],
     "client_submission_today"),
]


# ── Group B: Rule-first, LLM fallback ────────────────────────────────────
# These use broader/looser patterns. If matched, confidence is 'medium'.
# If not matched, the query goes to LLM classifier.

_GROUP_B_RULES: list[tuple[list[str], str]] = [
    (["reliability", "equipment reliability"],
     "equipment_maintenance_health"),

    (["personnel performance", "staff performance", "team performance"],
     "analyst_verifications"),

    (["support sla", "sla rate", "support compliance"],
     "sla_compliance"),
]


class ManifestIntentRouter:
    """
    Deterministic rule-based router for SQL manifest intents.

    Returns:
        (intent_name, tier) where tier is 'keyword' or 'keyword_loose'.
        Returns (None, None) if no rule matches.
    """

    def __init__(self):
        # Pre-compile for faster matching
        self._group_a = _GROUP_A_RULES
        self._group_b = _GROUP_B_RULES

    def match_all(self, query: str) -> List[Tuple[str, str]]:
        """
        Find ALL matching intents in the query (used for multi-query analysis).
        
        Returns:
            List of (intent_name, routing_tier)
        """
        q = query.lower().strip()
        matches = []
        
        # Group A
        for patterns, intent in self._group_a:
            if any(p in q for p in patterns):
                matches.append((intent, "keyword", 1.0))
                
        # Group B
        for patterns, intent in self._group_b:
            if any(p in q for p in patterns):
                # Only add if not already added by Group A (to prevent duplicates)
                if not any(m[0] == intent for m in matches):
                    matches.append((intent, "keyword_loose", 0.8))
                    
        return matches

    def match(self, query: str) -> Tuple[Optional[str], Optional[str], float]:
        """
        Attempt to match query to a manifest intent via keyword rules.

        Returns:
            (intent_name, routing_tier, confidence) or (None, None, 0.0)
        """
        q = query.lower().strip()

        # Group A: deterministic, high confidence
        for patterns, intent in self._group_a:
            if any(p in q for p in patterns):
                logger.info(
                    f"ManifestIntentRouter: MATCHED '{intent}' via keyword (Group A)"
                )
                return intent, "keyword", 1.0

        # Group B: looser rules, medium confidence
        for patterns, intent in self._group_b:
            if any(p in q for p in patterns):
                logger.info(
                    f"ManifestIntentRouter: MATCHED '{intent}' via keyword_loose (Group B)"
                )
                return intent, "keyword_loose", 0.8

        return None, None, 0.0

    def is_greeting(self, query: str) -> bool:
        """Check if the query is a simple greeting/pleasantry."""
        q = query.strip()
        # Short messages that are just greetings
        if len(q.split()) <= 6 and GREETING_PATTERNS.search(q):
            return True
        return False
