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
import difflib
from typing import Optional, Tuple, List, Dict
from python.py_pipeline.core.database import db_manager
from sqlalchemy import text

logger = logging.getLogger(__name__)


# ── Conversational short-circuit patterns ────────────────────────────────
GREETING_PATTERNS = re.compile(
    r"^(hi|hello|hey|good\s*(morning|afternoon|evening)|"
    r"thanks|thank\s*you|bye|goodbye|how\s*are\s*you|"
    r"what\'?s\s*up|cheers|greetings|howdy|yo)\b",
    re.IGNORECASE,
)


# ── Group A: Deterministic keyword routes ────────────────────────────────
# Structured by domain to support module-based isolation.
_GROUP_A_RULES: Dict[str, List[Tuple[List[str], str]]] = {
    "samples": [
        (["samples in the lab", "sample count in lab", "how many samples in lab",
          "how many samples are in the lab", "how many samples are in lab",
          "samples currently in lab", "individual samples in lab", "samples in lab"],
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
        (["samples request review", "request review", "review request",
          "samples requesting review", "sample request review", "request review stage",
          "how many samples request review", "how many samples are request review"],
         "sample_count_request_review"),
        (["samples approved", "approved samples", "how many approved",
          "how many samples are approved", "samples are approved",
          "approval count", "approved batches", "batches approved"],
         "sample_count_approved"),
        (["samples verified", "verified samples", "how many verified",
          "how many samples are verified", "samples are verified",
          "verification count", "verified batches", "batches verified"],
         "sample_count_verified"),
        (["samples by status", "sample status breakdown", "status breakdown",
          "sample distribution", "status distribution"],
         "samples_by_status"),
        (["total samples", "sample count total", "total sample count",
          "number of samples", "all samples"],
         "sample_count_total"),
        (["total samples since start of system", "total samples since start",
          "how many samples since start of system", "how many samples since start",
          "sample count since start of system", "samples since start of system",
          "samples since start", "all time samples", "absolute samples count"],
         "sample_count_absolute_all_time"),
        (["individual sample count", "individual samples", "aliquots",
          "sample items count", "total individual"],
         "individual_sample_count"),
        (["total batches", "batch count", "number of batches", "all batches"],
         "batch_count_total"),
        (["latest batch", "latest received", "recent batch",
          "last received batch", "newest batch", "recently received"],
         "latest_received_batches"),
        (["daily ingestion", "ingestion trend", "registration volume",
          "daily registration", "batch registration trend"],
         "daily_ingestion_trend"),
        (["sample type", "specimen type", "matrix type",
          "sample type distribution", "types of samples", "what samples are in lab",
          "what samples are in the lab", "list samples", "show samples"],
         "sample_type_distribution"),
    ],
    "inventory": [
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
    ],
    "equipment": [
        (["equipment utilization", "instrument usage", "equipment usage",
          "most used equipment", "instrument utilization"],
         "equipment_utilization"),
        (["maintenance schedule", "upcoming maintenance", "maintenance due",
          "scheduled maintenance", "next maintenance"],
         "equipment_maintenance_schedule"),
        (["equipment downtime", "equipment down", "non-operational",
          "overdue equipment", "broken equipment", "equipment overdue"],
         "equipment_downtime_summary"),
        (["active equipment", "active instruments", "active equipment count",
          "active instruments count", "how many active instruments", "how many active equipment",
          "how many active equipments", "number of active equipment", "number of active equipments",
          "total active equipment", "total active equipments"],
         "equipment_count_active"),
        (["total equipment", "total equipments", "total equipment count",
          "number of equipment", "number of equipments", "equipment count",
          "how many equipment", "how many equipments", "how many instruments",
          "total instruments", "all equipment", "all equipments", "all instruments"],
         "equipment_count_total"),
        (["inactive equipment", "inactive equipments", "inactive equipment count",
          "decommissioned equipment", "how many inactive equipment", "how many inactive equipments",
          "number of inactive equipment", "number of inactive equipments", "total inactive equipment",
          "total inactive equipments", "decommissioned instruments", "inactive instruments"],
         "equipment_count_inactive"),
        (["maintenance health", "maintenance status", "instrument maintenance state",
          "equipment maintenance state"],
         "equipment_maintenance_health"),
        (["equipment verification", "verification status",
          "instrument verification", "last verification"],
         "equipment_verification_status"),
    ],
    "quality": [
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
    ],
    "tat": [
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
    ],
    "support": [
        (["open complaint", "open ticket", "complaint count",
          "active complaint", "unresolved complaint", "complaints open"],
         "complaint_count_open"),
        (["ticket backlog", "ticket priority", "support backlog",
          "open tickets by priority"],
         "ticket_backlog_priority"),
        (["resolution time", "ticket resolution", "average resolution",
          "how long to resolve"],
         "ticket_resolution_time"),
        (["tickets by department", "department ticket", "department backlog",
          "department handling", "which department"],
         "tickets_by_department"),
        (["my last submission", "last submission", "latest submission",
          "my submissions", "recent submissions", "submission status",
          "my latest submission"],
         "portal_latest_submissions"),
        (["submission stats", "submission summary", "how many samples did i send",
          "how many samples have i sent", "how many samples sent",
          "my sample counts", "status of my samples", "my sample status"],
         "portal_submission_stats"),
        (["rejected samples", "failed batches", "my rejected samples",
          "was my sample rejected", "samples rejected", "rejected sample"],
         "portal_rejected_samples"),
        (["my average tat", "my turnaround time", "how long do my tests take",
          "average completion time", "average turnaround time", "average tat"],
         "portal_average_tat"),
        (["pending approval", "awaiting approval", "what is awaiting review",
          "pending verification", "my pending batches"],
         "portal_pending_approval"),
    ],
    "personnel": [
        (["analyst verification", "batches verified", "who verified",
          "verification count", "analyst verified"],
         "analyst_verifications"),
        (["analyst approval", "batches approved", "who approved",
          "approval count", "analyst approved"],
         "analyst_approvals"),
        (["analyst workload", "analyst activity today", "workload today",
          "who is working"],
         "analyst_workload_today"),
    ],
    "billing": [
        (["outstanding balance", "how much do i owe", "unpaid invoices",
          "my balance", "account balance"],
         "portal_outstanding_balance"),
        (["my invoices", "show my last invoices", "recent invoices",
          "billing history"],
         "portal_recent_invoices"),
        (["payment status", "has my payment been received", "check payment",
          "paid invoices"],
         "portal_payment_status"),
    ],
    "crm": [
        (["top client", "top customer", "biggest client",
          "most samples client", "client volume", "customer ranking"],
         "top_clients_by_volume"),
        (["inactive client", "dormant client", "churn risk",
          "client no submission", "inactive customer"],
         "inactive_clients"),
        (["client submission today", "customer submission today",
          "submissions today by client"],
         "client_submission_today"),
    ],
    "system": [
        (["system health", "system status", "how is the system", "lims health",
          "operational summary", "how is the lims system", "lims status",
          "how is everything", "system summary", "lab status", "overall status"],
         "system_overall_health"),
    ]
}


# ── Group B: Rule-first, LLM fallback ────────────────────────────────────
_GROUP_B_RULES: Dict[str, List[Tuple[List[str], str]]] = {
    "reliability": [
        (["reliability", "equipment reliability"], "equipment_maintenance_health"),
    ],
    "personnel": [
        (["personnel performance", "staff performance", "team performance"], "analyst_verifications"),
    ],
    "support": [
        (["support sla", "sla rate", "support compliance"], "sla_compliance"),
    ]
}


class ManifestIntentRouter:
    """
    Deterministic rule-based router for SQL manifest intents.
    Supports domain-based filtering for module isolation.
    """

    def __init__(self):
        self._group_a, self._group_b = self._load_rules()

    def _load_rules(self) -> Tuple[Dict[str, List[Tuple[List[str], str]]], Dict[str, List[Tuple[List[str], str]]]]:
        try:
            logger.info("Loading AI manifest intent routing rules from PostgreSQL database...")
            with db_manager.postgres_connection() as conn:
                rows = conn.execute(text("""
                    SELECT i.id as intent_id, i.domain, i.group_type, p.pattern 
                    FROM ai.manifest_intents i
                    JOIN ai.manifest_intent_patterns p ON i.id = p.intent_id
                    WHERE i.active = true
                """)).fetchall()

            # Group patterns by (domain, intent_id, group_type)
            grouped = {}
            for r in rows:
                intent_id = r[0]
                domain = r[1]
                group_type = r[2]
                pattern = r[3]
                
                key = (domain, intent_id, group_type)
                grouped.setdefault(key, []).append(pattern)

            group_a = {}
            group_b = {}
            for (domain, intent_id, group_type), patterns in grouped.items():
                target_group = group_a if group_type == 'A' else group_b
                target_group.setdefault(domain, []).append((patterns, intent_id))

            logger.info(f"Successfully loaded rules from DB. Group A domains: {list(group_a.keys())}, Group B domains: {list(group_b.keys())}")
            return group_a, group_b

        except Exception as e:
            logger.error(f"Failed to load routing rules from PostgreSQL: {e}. Falling back to static rules.")
            return _GROUP_A_RULES, _GROUP_B_RULES

    def _get_active_rules(self, domain_whitelist: Optional[List[str]] = None) -> Tuple[List[Tuple[List[str], str]], List[Tuple[List[str], str]]]:
        """Filter rules based on whitelist. If None or contains '*', return all."""
        unrestricted = not domain_whitelist or "*" in domain_whitelist
        
        a_rules = []
        for domain, rules in self._group_a.items():
            if unrestricted or domain in domain_whitelist:
                a_rules.extend(rules)
                
        b_rules = []
        for domain, rules in self._group_b.items():
            if unrestricted or domain in domain_whitelist:
                b_rules.extend(rules)
                
        return a_rules, b_rules

    def match_all(self, query: str, domain_whitelist: Optional[List[str]] = None) -> List[Tuple[str, str, float]]:
        """
        Find ALL matching intents in the query, filtered by domain.
        Uses a hybrid Exact -> Fuzzy matching strategy.
        """
        q = query.lower().strip()
        matches = []
        
        a_active, b_active = self._get_active_rules(domain_whitelist)
        
        # 1. Exact Match Pass (Fastest with Word Boundaries)
        for patterns, intent in a_active:
            if any(re.search(rf"\b{re.escape(p)}\b", q) for p in patterns):
                matches.append((intent, "keyword", 1.0))
                
        for patterns, intent in b_active:
            if any(re.search(rf"\b{re.escape(p)}\b", q) for p in patterns):
                if not any(m[0] == intent for m in matches):
                    matches.append((intent, "keyword_loose", 0.8))

        # 2. Fuzzy Match Pass (Only if no exact matches found)
        if not matches:
            words = q.split()
            # Only attempt fuzzy if query is short enough to be a specific command
            if len(words) <= 12:
                for patterns, intent in a_active + b_active:
                    for p in patterns:
                        # Check if pattern (often multi-word) is close to any window in query
                        p_words = p.split()
                        n = len(p_words)
                        for i in range(len(words) - n + 1):
                            window = " ".join(words[i:i+n])
                            # 0.85 ratio allows for 1-2 typos in a medium string
                            if difflib.SequenceMatcher(None, p, window).ratio() >= 0.85:
                                # Word-by-word safety check: reject if any word is completely different (ratio < 0.7)
                                w_words = window.split()
                                if any(difflib.SequenceMatcher(None, pw, ww).ratio() < 0.7 for pw, ww in zip(p_words, w_words)):
                                    continue
                                # Only add if this intent hasn't been added yet
                                if not any(m[0] == intent for m in matches):
                                    tier = "keyword_fuzzy" if patterns in [r[0] for r in a_active] else "keyword_loose_fuzzy"
                                    conf = 0.9 if tier == "keyword_fuzzy" else 0.7
                                    matches.append((intent, tier, conf))
                                break
                    # Removed 'break' here to allow matching multiple DIFFERENT intents in fuzzy pass if they exist

        return matches

    def match(self, query: str, domain_whitelist: Optional[List[str]] = None) -> Tuple[Optional[str], Optional[str], float]:
        """
        Attempt to match query to a manifest intent, filtered by domain.

        Returns:
            (intent_name, routing_tier, confidence) or (None, None, 0.0)
        """
        q = query.lower().strip()
        a_active, b_active = self._get_active_rules(domain_whitelist)

        # Find best match in Group A (based on longest matching pattern)
        best_intent = None
        best_pattern_len = 0

        for patterns, intent in a_active:
            for p in patterns:
                if p in q:
                    if len(p) > best_pattern_len:
                        best_pattern_len = len(p)
                        best_intent = intent

        if best_intent:
            logger.info(
                f"ManifestIntentRouter: MATCHED '{best_intent}' via keyword (Group A, len={best_pattern_len})"
            )
            return best_intent, "keyword", 1.0

        # Group B: looser rules, medium confidence
        for patterns, intent in b_active:
            for p in patterns:
                if p in q:
                    if len(p) > best_pattern_len:
                        best_pattern_len = len(p)
                        best_intent = intent

        if best_intent:
            logger.info(
                f"ManifestIntentRouter: MATCHED '{best_intent}' via keyword_loose (Group B, len={best_pattern_len})"
            )
            return best_intent, "keyword_loose", 0.8

        return None, None, 0.0

    def is_greeting(self, query: str) -> bool:
        """Check if the query is a simple greeting/pleasantry."""
        q = query.strip()
        # Short messages that are just greetings
        if len(q.split()) <= 6 and GREETING_PATTERNS.search(q):
            return True
        return False
