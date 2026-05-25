"""
schema_catalog.py — Reporting Schema Metadata for Dynamic SQL Generation

Provides a structured, pruned view of the reporting schema that the
DynamicSqlWorker uses to generate safe, accurate PostgreSQL queries.

Rules for this catalog:
  - Only tables in the `reporting` schema are exposed.
  - Sensitive tables (users with passwords, auth tokens, system configs)
    are explicitly excluded.
  - Each table has clear human-readable descriptions of columns so the
    LLM understands semantics, not just column names.
  - Relationships (JOINs) are explicitly defined to prevent bad cartesian
    products or wrong key usage.
"""

from typing import Dict, List, Any, Optional
from python.ai_service.core import mode_registry

# ─────────────────────────────────────────────────────────────────────────────
# Full schema catalog
# ─────────────────────────────────────────────────────────────────────────────

_FULL_CATALOG: Dict[str, Dict[str, Any]] = {

    # ── SAMPLES ────────────────────────────────────────────────────────────────
    "public.sample_headers": {
        "domain": "samples",
        "description": (
            "One record per sample batch (customer submission). The top-level tracking unit. "
            "Use this table for sample/batch counts, workflow stage counts, and status questions."
        ),
        "columns": {
            "id": "Primary key (uuid). Use this for JOINs.",
            "batch_code": "Human-readable batch identifier (e.g. GCLA-2026-0001).",
            "status": (
                "Current workflow stage. Values: 'Sample Received', 'Samples In Lab', "
                "'Sample Verification', 'Sample Approval', 'Completed', 'Rejected'. "
                "Use this column for wording such as in lab, verification, approval, completed, rejected."
            ),
            "crm_customer_id": "FK to public.crm_customers.id. Links batch to its client.",
            "verify_user_id": "FK to public.users.id. Analyst who verified.",
            "approve_user_id": "FK to public.users.id. Analyst who approved.",
            "sample_tracking_stage": "FK to public.sample_analysis_stages.id.",
            "isactive": "Boolean. True = record is live; False = soft-deleted. Always filter WHERE isactive = true.",
            "created_at": "Timestamp when the batch was received. Cast to timestamptz for date math.",
            "updated_at": "Timestamp of last status change.",
            "approval_date": "Timestamp of final approval. NULL if not yet approved.",
        },
        "common_filters": [
            "isactive = true",
            "created_at::timestamptz >= (NOW() - INTERVAL '30 days')",
        ],
        "note": (
            "Workflow/status filters belong here. For 'how many samples in verification', use "
            "public.sample_headers.status = 'Sample Verification'. Do not use sample_details for workflow status."
        ),
    },

    "public.sample_details": {
        "domain": "samples",
        "description": (
            "Individual sample items (aliquots) within a batch. One batch = many items. "
            "Use this table only when the user explicitly asks for individual sample items, aliquots, or tests."
        ),
        "columns": {
            "id": "Primary key (uuid).",
            "sample_header_id": "FK to public.sample_headers.id.",
            "analyte_id": "FK to public.analytes.id.",
        },
        "joins": {
            "public.sample_headers": "public.sample_details.sample_header_id = public.sample_headers.id",
        },
        "note": (
            "This table does not contain workflow status, sample_tracking_stage, created_at, or isactive. "
            "Join to public.sample_headers before filtering by workflow/status or active records."
        ),
    },

    "public.sample_analysis_stages": {
        "domain": "samples",
        "description": "Workflow stage lookup. Maps stage ID to a named workflow step.",
        "columns": {
            "id": "Primary key (uuid).",
            "sample_workflow": "Stage name. Key values: 'Samples In Lab', 'Quality Control'.",
        },
        "joins": {
            "public.sample_headers": "public.sample_headers.sample_tracking_stage = public.sample_analysis_stages.id",
        },
    },

    "public.analytes": {
        "domain": "samples",
        "description": "Master list of laboratory analytes (tests).",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Analyte name (e.g. 'Escherichia coli (E. Coli)', 'Lead Content (Pb)').",
        },
    },

    "public.sample_types": {
        "domain": "samples",
        "description": "Specimen/matrix types (e.g. 'Drinking Water', 'Blood Plasma').",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Sample type label.",
        },
        "joins": {
            "public.sample_headers": "public.sample_headers.sample_type_id = public.sample_types.id",
        },
        "note": "For sample type volume/distribution, join sample_headers to sample_types and display sample_types.name, not sample_type_id.",
    },

    # ── TAT ────────────────────────────────────────────────────────────────────
    "public.tat_captured": {
        "domain": "tat",
        "description": "Turnaround time records. One record per sample/analyte combination.",
        "columns": {
            "id": "Primary key (uuid).",
            "sample_header_id": "FK to public.sample_headers.id.",
            "analyte_id": "FK to public.analytes.id.",
            "sample_type_id": "FK to public.sample_types.id.",
            "tat_overdue_days": "Days over the SLA deadline. Negative means finished early; 0 = on-time; positive = late.",
            "is_complete": "Boolean. True if the sample has been fully processed.",
            "created_at": "When the TAT record was captured.",
        },
        "joins": {
            "public.sample_headers": "public.tat_captured.sample_header_id = public.sample_headers.id",
            "public.analytes": "public.tat_captured.analyte_id = public.analytes.id",
            "public.sample_types": "public.tat_captured.sample_type_id = public.sample_types.id",
        },
    },

    # ── QUALITY ────────────────────────────────────────────────────────────────
    "public.corrective_actions": {
        "domain": "quality",
        "description": "CAPA (Corrective and Preventive Actions) log.",
        "columns": {
            "id": "Primary key (uuid).",
            "status": "CAPA status.",
            "created_at": "Date CAPA was opened.",
        },
    },

    "public.audits": {
        "domain": "quality",
        "description": "Audit findings and non-conformances.",
        "columns": {
            "id": "Primary key.",
            "event": "Action type (e.g. 'created', 'updated').",
            "auditable_type": "Associated entity class.",
            "created_at": "Audit timestamp.",
        },
    },

    "public.v_qc_stability_metrics": {
        "domain": "quality",
        "description": "Pre-computed QC stability view. Shows analytes with drift or instability.",
        "columns": {
            "analyte_name": "Name of the analyte.",
            "robust_cv_pct": "Coefficient of variation %. > 15 = warning; > 25 = critical.",
            "pass_rate_pct": "QC pass rate for this analyte (0–100).",
        },
    },

    # ── SUPPORT / COMPLAINTS ────────────────────────────────────────────────────
    "public.complaints": {
        "domain": "support",
        "description": "Customer complaints and support tickets.",
        "columns": {
            "id": "Primary key (uuid).",
            "priority": "Ticket priority (e.g. 'High', 'Medium', 'Low').",
            "is_closed": "Boolean. True = resolved.",
            "complaint_workflow": "Workflow stage/status.",
            "created_at": "When the complaint was logged.",
        },
    },

    # ── CRM / CLIENTS ────────────────────────────────────────────────────────────
    "public.crm_customers": {
        "domain": "crm",
        "description": "Customer and client master list. Use this table to count, list, or query customers, clients, organisations, accounts registered in the system.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Client/customer/organization name.",
        },
        "safe_columns": ["id", "name"],
        "note": "This table has NO isactive, is_active, active, or status column. Do NOT filter by those.",
    },

    # ── EQUIPMENT ────────────────────────────────────────────────────────────────
    "public.equipment": {
        "domain": "equipment",
        "description": "Master list of laboratory instruments/equipment assets.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Instrument name (e.g. Agilent 8890 GC-MS).",
            "equipment_number": "Unique instrument identifier (e.g. EQ-GCMS-001).",
            "make": "Brand/manufacturer.",
            "model": "Model identifier.",
            "active": "Boolean. True = active and in service; False = decommissioned/inactive.",
            "date_purchased": "Date when instrument was purchased.",
            "maintainance_days": "Interval in days between routine preventive maintenance.",
            "calibration_days": "Interval in days between routine calibrations.",
        },
    },

    "public.maintainance_calibration_logs": {
        "domain": "equipment",
        "description": "Equipment log for calibrations, maintenance, and verification runs.",
        "columns": {
            "id": "Primary key (uuid).",
            "equipment_id": "FK to public.equipment.id.",
            "type": "Event type. Values: 'calibration', 'maintenance', 'verification'.",
            "date": "Date when the event was performed.",
            "notes": "Operator remarks/findings.",
            "operator_approve": "Boolean. True if operator approved the log.",
            "proccess_owner_approve": "Boolean. True if process owner approved the log.",
        },
        "joins": {
            "public.equipment": "public.maintainance_calibration_logs.equipment_id = public.equipment.id",
        },
    },

    # ── INVENTORY ────────────────────────────────────────────────────────────────
    "public.inventory_categories": {
        "domain": "inventory",
        "description": "Top-level stock groupings (e.g. Reagents, Consumables).",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Category name.",
            "description": "Category overview.",
        },
    },

    "public.inventory_sub_categories": {
        "domain": "inventory",
        "description": "Subcategories of inventory containing min level threshold specs.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Item brand/name specification.",
            "minimum_level": "Minimum quantity target. If stock is lower, item is low.",
            "unit_type": "Measurement unit (e.g. Liters, Pieces).",
            "inventory_category_id": "FK to public.inventory_categories.id.",
        },
        "joins": {
            "public.inventory_categories": "public.inventory_sub_categories.inventory_category_id = public.inventory_categories.id",
        },
    },

    "public.inventory_items": {
        "domain": "inventory",
        "description": "Actual batch stock instances in the warehouse.",
        "columns": {
            "id": "Primary key (uuid).",
            "inventory_category_id": "FK to public.inventory_categories.id.",
            "inventory_sub_category_id": "FK to public.inventory_sub_categories.id.",
            "stock_in": "Current stock quantity on-hand.",
            "batch_code": "Lot/batch designation.",
            "expiry": "Expiration date.",
            "price": "Unit cost price.",
            "supplier_id": "FK to public.suppliers.id.",
        },
        "joins": {
            "public.inventory_categories": "public.inventory_items.inventory_category_id = public.inventory_categories.id",
            "public.inventory_sub_categories": "public.inventory_items.inventory_sub_category_id = public.inventory_sub_categories.id",
            "public.suppliers": "public.inventory_items.supplier_id = public.suppliers.id",
        },
    },

    "public.suppliers": {
        "domain": "inventory",
        "description": "Authorized LIMS vendors and supply partners.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Vendor company name.",
            "email": "Contact email address.",
        },
    },

    "public.inventory_orders": {
        "domain": "inventory",
        "description": "Supply purchase orders.",
        "columns": {
            "id": "Primary key (uuid).",
            "order_number": "PO number reference.",
            "supplier_id": "FK to public.suppliers.id.",
            "status": "Order fulfillment. Values: 'fulfilled', 'not_fulfilled'.",
        },
        "joins": {
            "public.suppliers": "public.inventory_orders.supplier_id = public.suppliers.id",
        },
    },

    "public.inventory_order_items": {
        "domain": "inventory",
        "description": "Line items in supply purchase orders.",
        "columns": {
            "id": "Primary key (uuid).",
            "inventory_order_id": "FK to public.inventory_orders.id.",
            "inventory_category_id": "FK to public.inventory_categories.id.",
            "inventory_sub_category_id": "FK to public.inventory_sub_categories.id.",
            "quantity": "Ordered amount.",
            "fulfilled": "Boolean. True = delivered.",
        },
        "joins": {
            "public.inventory_orders": "public.inventory_order_items.inventory_order_id = public.inventory_orders.id",
            "public.inventory_categories": "public.inventory_order_items.inventory_category_id = public.inventory_categories.id",
            "public.inventory_sub_categories": "public.inventory_order_items.inventory_sub_category_id = public.inventory_sub_categories.id",
        },
    },

    # ── PERSONNEL ────────────────────────────────────────────────────────────────
    "public.users": {
        "domain": "personnel",
        "description": (
            "LIMS staff/analysts. Use this table for analyst, staff, personnel, team, and performance ranking questions. "
            "NOTE: Only name and active status are safe to expose."
        ),
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Analyst full name.",
            "active": "Integer flag. 1 = currently employed/active.",
            "designation": "Job title or designation, if available.",
            "lab_section_id": "Lab section identifier assigned to the user.",
            "lab_id": "Lab identifier assigned to the user.",
            "company_id": "Company/tenant identifier. Use only with :company_id parameter when needed.",
        },
        "sensitive": True,
        "safe_columns": ["id", "name", "active", "designation", "lab_section_id", "lab_id", "company_id"],
        "joins": {
            "public.sample_headers": (
                "public.users.id = public.sample_headers.verify_user_id "
                "OR public.users.id = public.sample_headers.approve_user_id"
            ),
            "public.sample_captured_test_stages_track": (
                "public.users.id = public.sample_captured_test_stages_track.user_id "
                "OR public.users.id = public.sample_captured_test_stages_track.ended_by "
                "OR public.users.id = public.sample_captured_test_stages_track.read_by "
                "OR public.users.id = public.sample_captured_test_stages_track.results_posted_by"
            ),
            "public.track_sample_results": "public.users.id = public.track_sample_results.analyst_id",
            "public.track_control_results": "public.users.id = public.track_control_results.analyst_id",
            "public.track_media_results": "public.users.id = public.track_media_results.analyst_id",
        },
        "note": (
            "For generic analyst performance/ranking, combine verified batches, approved batches, and result-entry activity. "
            "For explicit verification or approval performance, use that specific sample_headers user column. "
            "For bench/result-entry productivity, join to track_sample_results, track_control_results, track_media_results, "
            "or sample_captured_test_stages_track. Do not use public.analytes for analyst performance."
        ),
    },

    "public.sample_captured_test_stages_track": {
        "domain": "personnel",
        "description": "Tracks analyst activity on test stages, including who started, ended, read, and posted results.",
        "columns": {
            "id": "Primary key (uuid).",
            "sample_detail_id": "FK to public.sample_details.id.",
            "user_id": "FK to public.users.id. Analyst assigned/started this test stage.",
            "ended_by": "FK to public.users.id. Analyst who ended/completed this test stage.",
            "read_by": "FK to public.users.id. Analyst who read the result.",
            "results_posted_by": "FK to public.users.id. Analyst who posted the result.",
            "status": "Stage status, e.g. pending or completed.",
            "started_at": "Timestamp when the stage started.",
            "ended_at": "Timestamp when the stage ended.",
            "reading_date": "Timestamp when result reading occurred.",
            "results_posted_at": "Timestamp when results were posted.",
            "created_at": "Record creation timestamp.",
            "updated_at": "Record update timestamp.",
        },
        "joins": {
            "public.users": (
                "public.users.id = public.sample_captured_test_stages_track.user_id "
                "OR public.users.id = public.sample_captured_test_stages_track.ended_by "
                "OR public.users.id = public.sample_captured_test_stages_track.read_by "
                "OR public.users.id = public.sample_captured_test_stages_track.results_posted_by"
            ),
            "public.sample_details": "public.sample_captured_test_stages_track.sample_detail_id = public.sample_details.id",
        },
        "note": "Use this for analyst workload by test-stage actions, completion counts, reads, and posted results.",
    },

    "public.track_sample_results": {
        "domain": "personnel",
        "description": "Sample result entries recorded by analysts.",
        "columns": {
            "id": "Primary key (uuid).",
            "track_id": "FK to public.sample_captured_test_stages_track.id.",
            "captured_result_id": "FK to captured result.",
            "sample_code": "Human-readable sample code.",
            "parameter": "Test parameter/analyte name recorded for this result.",
            "method": "Method used, if available.",
            "analyst_id": "FK to public.users.id. Analyst who recorded this sample result.",
            "recorded_at": "Timestamp when the result was recorded.",
            "created_at": "Record creation timestamp.",
        },
        "safe_columns": ["id", "track_id", "captured_result_id", "sample_code", "parameter", "method", "analyst_id", "recorded_at", "created_at"],
        "joins": {
            "public.users": "public.track_sample_results.analyst_id = public.users.id",
            "public.sample_captured_test_stages_track": "public.track_sample_results.track_id = public.sample_captured_test_stages_track.id",
        },
        "note": "Use this for analyst result-entry counts and productivity by recorded sample results. Do not expose raw result values unless specifically needed.",
    },

    "public.track_control_results": {
        "domain": "personnel",
        "description": "Control result entries recorded by analysts.",
        "columns": {
            "id": "Primary key (uuid).",
            "track_id": "FK to public.sample_captured_test_stages_track.id.",
            "analyst_id": "FK to public.users.id. Analyst who recorded this control result.",
            "recorded_at": "Timestamp when the control result was recorded.",
            "created_at": "Record creation timestamp.",
        },
        "joins": {
            "public.users": "public.track_control_results.analyst_id = public.users.id",
            "public.sample_captured_test_stages_track": "public.track_control_results.track_id = public.sample_captured_test_stages_track.id",
        },
    },

    "public.track_media_results": {
        "domain": "personnel",
        "description": "Media result entries recorded by analysts.",
        "columns": {
            "id": "Primary key (uuid).",
            "track_id": "FK to public.sample_captured_test_stages_track.id.",
            "analyst_id": "FK to public.users.id. Analyst who recorded this media result.",
            "recorded_at": "Timestamp when the media result was recorded.",
            "created_at": "Record creation timestamp.",
        },
        "joins": {
            "public.users": "public.track_media_results.analyst_id = public.users.id",
            "public.sample_captured_test_stages_track": "public.track_media_results.track_id = public.sample_captured_test_stages_track.id",
        },
    },
}

# ─────────────────────────────────────────────────────────────────────────────
# Blocked tables — never exposed, regardless of mode
# ─────────────────────────────────────────────────────────────────────────────

BLOCKED_TABLES = {
    "users",            # auth user table (passwords, tokens)
    "sessions",
    "oauth_tokens",
    "password_resets",
    "personal_access_tokens",
    "ai_request_logs",  # internal telemetry
}

BLOCKED_SCHEMAS = {"information_schema", "pg_catalog"}


# ─────────────────────────────────────────────────────────────────────────────
# Public accessor
# ─────────────────────────────────────────────────────────────────────────────

import re

# ─────────────────────────────────────────────────────────────────────────────
# Fix 2: Domain Alias Dictionary
# Maps each domain to NL synonyms. A query word matching any alias gives
# ALL tables in that domain a +10 score boost — making them unambiguous winners.
# ─────────────────────────────────────────────────────────────────────────────

_DOMAIN_ALIASES: Dict[str, set] = {
    "crm":       {"client", "clients", "customer", "customers", "account",
                  "accounts", "organisation", "organisations", "organization",
                  "organizations", "company", "companies", "partner", "register"},
    "equipment": {"equipment", "instrument", "instruments", "machine", "machines",
                  "calibrat", "maintena", "downtime", "asset", "assets"},
    "quality":   {"qc", "capa", "complaint", "complaints", "audit", "audits",
                  "corrective", "finding", "nonconform"},
    "tat":       {"tat", "turnaround", "overdue", "sla", "deadline", "delay"},
    "inventory": {"inventory", "stock", "reagent", "reagents", "supplier",
                  "procurement", "purchase", "expir"},
    "personnel": {"analyst", "analysts", "staff", "personnel", "verif", "approv"},
}

_DOMINANT_SCORE_THRESHOLD = 8   # if top table scores >= this, skip baseline padding
_MAX_SCORE_GAP = 8              # drop tables scoring more than this below the top scorer

# ─────────────────────────────────────────────────────────────────────────────
# Fix A: TAT Qualifier Map
# TAT is a cross-cutting concern (analyst TAT, lab TAT, equipment TAT).
# When the query contains a TAT trigger word + a qualifier, return the exact
# table set required for that specific TAT sub-type — bypassing the scorer.
# ─────────────────────────────────────────────────────────────────────────────

_TAT_TRIGGERS = {"tat", "turnaround", "overdue", "sla", "delay", "deadline"}

_TAT_QUALIFIER_TABLES: Dict[str, List[str]] = {
    # Analyst/user-level TAT: how long each analyst takes to verify/approve
    "analyst":    ["public.users", "public.tat_captured", "public.sample_headers"],
    "user":       ["public.users", "public.tat_captured", "public.sample_headers"],
    "personnel":  ["public.users", "public.tat_captured", "public.sample_headers"],
    "staff":      ["public.users", "public.tat_captured", "public.sample_headers"],

    # Lab/sample-level TAT: how long samples spend in the lab pipeline
    "lab":        ["public.sample_headers", "public.tat_captured", "public.sample_analysis_stages"],
    "sample":     ["public.sample_headers", "public.tat_captured", "public.sample_analysis_stages"],
    "batch":      ["public.sample_headers", "public.tat_captured"],
    "stage":      ["public.sample_headers", "public.tat_captured", "public.sample_analysis_stages"],
    "department": ["public.sample_headers", "public.tat_captured", "public.sample_analysis_stages"],

    # Analyte-level TAT: how long specific tests take
    "analyte":    ["public.tat_captured", "public.analytes", "public.sample_headers"],
    "test":       ["public.tat_captured", "public.analytes", "public.sample_headers"],

    # Equipment/calibration TAT: how long since last calibration/maintenance
    "equipment":  ["public.equipment", "public.maintainance_calibration_logs"],
    "instrument": ["public.equipment", "public.maintainance_calibration_logs"],
    "calibrat":   ["public.equipment", "public.maintainance_calibration_logs"],
    "maintenan":  ["public.equipment", "public.maintainance_calibration_logs"],

    # Complaint/support TAT: resolution time for tickets
    "complaint":  ["public.complaints"],
    "ticket":     ["public.complaints"],
    "support":    ["public.complaints"],
}

_PERSONNEL_PERFORMANCE_ACTORS = {"analyst", "analysts", "staff", "personnel", "team", "user", "users"}
_PERSONNEL_PERFORMANCE_METRICS = {"performance", "rank", "ranking", "top", "best", "workload", "productivity"}
_PERSONNEL_PERFORMANCE_TABLES = [
    "public.users",
    "public.sample_headers",
    "public.sample_captured_test_stages_track",
    "public.track_sample_results",
    "public.track_control_results",
    "public.track_media_results",
]

def get_catalog_for_mode(mode_key: Optional[str]) -> Dict[str, Dict[str, Any]]:
    """
    Return the schema catalog filtered to the domains allowed by the given mode.
    For 'general' mode (*), returns the full catalog.
    """
    allowed_domains = mode_registry.get_allowed_domains(mode_key)
    unrestricted = "*" in allowed_domains

    return {
        table: meta
        for table, meta in _FULL_CATALOG.items()
        if unrestricted or meta.get("domain") in allowed_domains
    }


def prune_catalog_for_query(query: str, mode_key: Optional[str]) -> Dict[str, Dict[str, Any]]:
    """
    Dynamically prune the schema catalog to keep only the tables relevant
    to the user's natural language question based on keyword matching scores.
    """
    catalog = get_catalog_for_mode(mode_key)

    # 1. Clean and tokenize the user query into lowercase words
    query_words = set(re.findall(r"\w+", query.lower()))
    if not query_words:
        return catalog  # Return full catalog if query is empty

    if query_words & _PERSONNEL_PERFORMANCE_ACTORS and query_words & _PERSONNEL_PERFORMANCE_METRICS:
        return {
            table: catalog[table]
            for table in _PERSONNEL_PERFORMANCE_TABLES
            if table in catalog
        }

    scored_tables = []
    for table_name, meta in catalog.items():
        score = 0

        # ── Fix 1: Exclude FK columns from scoring text ───────────────────────
        # FK columns (e.g. crm_customer_id) mention other table names in their
        # descriptions, causing unrelated tables to score for those queries.
        # We only score on non-FK semantic columns.
        semantic_cols_text = " ".join(
            f"{col} {desc}"
            for col, desc in meta.get("columns", {}).items()
            if not col.endswith("_id") and not desc.startswith("FK to")
        )
        searchable_text = f"{table_name} {meta.get('description', '')} {semantic_cols_text}".lower()

        # Tokenize the schema text for bidirectional prefix matching
        schema_words = set(re.findall(r"\w+", searchable_text))

        # Bidirectional prefix match: handles plurals (clients↔client)
        def _prefix_match(w: str, candidates: set) -> bool:
            stem = w[:5]
            return any(c.startswith(stem) or w.startswith(c[:5]) for c in candidates if len(c) >= 4)

        # Check keyword presence with bidirectional prefix/stem matching
        for word in query_words:
            if len(word) <= 2 or word in {
                "the", "and", "for", "with", "that", "this", "from", "how",
                "what", "many", "show", "list", "view", "total", "system",
                "give", "number", "latest", "recent", "last", "get"
            }:
                continue

            if word in table_name or _prefix_match(word, set(re.findall(r"\w+", table_name))):
                score += 5
            if _prefix_match(word, set(re.findall(r"\w+", meta.get("domain", "")))):
                score += 3
            if word in searchable_text or _prefix_match(word, schema_words):
                score += 1

        # ── Fix 2: Domain alias boost ─────────────────────────────────────────
        # If any query word clearly targets this table's domain, give +10.
        table_domain = meta.get("domain", "")
        domain_synonyms = _DOMAIN_ALIASES.get(table_domain, set())
        for word in query_words:
            if len(word) < 4:
                continue
            stem = word[:6]
            if any(
                alias.startswith(stem) or stem.startswith(alias[:6])
                for alias in domain_synonyms if len(alias) >= 4
            ):
                score += 10
                break  # one domain hit per table is enough

        # Proactively baseline primary workflow tables to ensure joins are complete
        if "sample_header" in table_name and (
            "sample" in query_words or "batch" in query_words
            or "tat" in query_words or "turnaround" in query_words
        ):
            score += 8

        scored_tables.append((score, table_name, meta))

    # Sort descending by relevance score
    scored_tables.sort(key=lambda x: x[0], reverse=True)

    # ── Hybrid Fix A: TAT Qualifier Fast Path ────────────────────────────────
    # TAT is cross-cutting (analyst TAT, lab TAT, equipment TAT).
    # If the query contains a TAT trigger word + a known qualifier, bypass
    # the scorer entirely and return the exact predetermined table set.
    is_tat_query = any(trigger in query_words or
                       any(w.startswith(trigger[:4]) for w in query_words)
                       for trigger in _TAT_TRIGGERS)

    if is_tat_query:
        for qualifier, table_list in _TAT_QUALIFIER_TABLES.items():
            qual_stem = qualifier[:6]
            if any(
                w == qualifier or w.startswith(qual_stem) or qualifier.startswith(w[:6])
                for w in query_words if len(w) >= 4
            ):
                # Found a TAT qualifier match — return the exact table set
                full_catalog = get_catalog_for_mode(mode_key)
                return {
                    t: full_catalog[t]
                    for t in table_list
                    if t in full_catalog
                }

    # ── Hybrid Fix B: Score Gap Threshold ────────────────────────────────────
    # For all other queries:
    # 1. If top table is dominant (>= threshold), only keep tables within
    #    _MAX_SCORE_GAP points of the top scorer — drops low-scoring noise.
    # 2. If no dominant match exists, fall back to 1 baseline table.
    top_score = scored_tables[0][0] if scored_tables else 0
    pruned_catalog = {}
    for i, (score, table_name, meta) in enumerate(scored_tables):
        if score > 0:
            # Score gap filter: drop noise tables far below the top scorer
            if top_score >= _DOMINANT_SCORE_THRESHOLD and (top_score - score) > _MAX_SCORE_GAP:
                continue  # too far below top — noise, skip it
            pruned_catalog[table_name] = meta
        elif top_score < _DOMINANT_SCORE_THRESHOLD and i < 1:
            # No dominant match — include one baseline table as fallback
            pruned_catalog[table_name] = meta

    return pruned_catalog


def build_prompt_schema(mode_key: Optional[str], query: Optional[str] = None) -> str:
    """
    Build a compact, human-readable schema description string suitable for
    injecting into an LLM prompt for Text-to-SQL generation.
    Supports dynamic table pruning if a query is provided.
    """
    if query:
        catalog = prune_catalog_for_query(query, mode_key)
    else:
        catalog = get_catalog_for_mode(mode_key)

    lines = ["Available Tables (PostgreSQL public schema):"]

    lines.extend([
        "",
        "Global SQL construction rules:",
        "- For sample workflow/status questions, use public.sample_headers.status.",
        "- For active sample/batch records, apply public.sample_headers.isactive = true.",
        "- Do not place isactive, status, or sample_tracking_stage filters on public.sample_details.",
        "- Join sample details to headers with public.sample_details.sample_header_id = public.sample_headers.id.",
        "- Workflow wording mapping: verification -> 'Sample Verification'; approval -> 'Sample Approval'; in lab -> 'Samples In Lab'; rejected -> 'Rejected'; completed -> 'Completed'.",
        "- For analyst/staff/personnel performance or ranking, use public.users joined to public.sample_headers by verify_user_id and/or approve_user_id; do not use public.analytes.",
        "- For sample type volume/distribution, join public.sample_headers to public.sample_types and display public.sample_types.name, not sample_type_id.",
    ])

    for table_name, meta in catalog.items():
        lines.append(f"\n### {table_name}")
        lines.append(f"Description: {meta['description']}")
        lines.append("Columns:")
        for col, desc in meta.get("columns", {}).items():
            # Skip sensitive columns that aren't in safe_columns
            safe = meta.get("safe_columns")
            if safe and col not in safe:
                continue
            lines.append(f"  - {col}: {desc}")
        if "joins" in meta:
            lines.append("JOIN conditions:")
            for join_table, condition in meta["joins"].items():
                lines.append(f"  - JOIN {join_table} ON {condition}")
        if "common_filters" in meta:
            lines.append("Always apply filters:")
            for f in meta["common_filters"]:
                lines.append(f"  - WHERE {f}")
        if "note" in meta:
            lines.append(f"⚠️  Note: {meta['note']}")

    return "\n".join(lines)
