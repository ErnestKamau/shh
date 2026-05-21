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
        "description": "One record per sample batch (customer submission). The top-level tracking unit.",
        "columns": {
            "id": "Primary key (uuid). Use this for JOINs.",
            "batch_code": "Human-readable batch identifier (e.g. GCLA-2026-0001).",
            "status": "Current workflow stage. Values: 'Sample Received', 'Samples In Lab', "
                      "'Sample Verification', 'Sample Approval', 'Completed', 'Rejected'.",
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
    },

    "public.sample_details": {
        "domain": "samples",
        "description": "Individual sample items (aliquots) within a batch. One batch = many items.",
        "columns": {
            "id": "Primary key (uuid).",
            "sample_header_id": "FK to public.sample_headers.id.",
            "analyte_id": "FK to public.analytes.id.",
        },
        "joins": {
            "public.sample_headers": "public.sample_details.sample_header_id = public.sample_headers.id",
        },
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
        "description": "Customer/client master list.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Client/organization name.",
        },
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
        "description": "LIMS staff/analysts. NOTE: Only name and active status are safe to expose.",
        "columns": {
            "id": "Primary key (uuid).",
            "name": "Analyst full name.",
            "active": "Boolean. True = currently employed/active.",
        },
        "sensitive": True,
        "safe_columns": ["id", "name", "active"],
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


def build_prompt_schema(mode_key: Optional[str]) -> str:
    """
    Build a compact, human-readable schema description string suitable for
    injecting into an LLM prompt for Text-to-SQL generation.
    """
    catalog = get_catalog_for_mode(mode_key)
    lines = ["Available Tables (PostgreSQL public schema):"]

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

    return "\n".join(lines)
