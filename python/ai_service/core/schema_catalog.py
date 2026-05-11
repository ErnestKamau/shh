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
    "reporting.sample_headers": {
        "domain": "samples",
        "description": "One record per sample batch (customer submission). The top-level tracking unit.",
        "columns": {
            "source_id": "Primary key (bigint). Use this for JOINs.",
            "batch_code": "Human-readable batch identifier (e.g. BT-2024-001).",
            "status": "Current workflow stage. Values: 'Sample Received', 'Samples In Lab', "
                      "'Sample Verification', 'Sample Approval', 'Completed', 'Rejected', "
                      "'Cancelled', 'Samples Request Review'.",
            "crm_customer_id": "FK to reporting.clients.source_id. Links batch to its client.",
            "verify_user_id": "FK to reporting.users.source_id. Analyst who verified.",
            "approve_user_id": "FK to reporting.users.source_id. Analyst who approved.",
            "sample_tracking_stage": "FK (cast to bigint) to reporting.sample_analysis_stages.source_id.",
            "isactive": "Boolean. True = record is live; False = soft-deleted. Always filter WHERE isactive = true.",
            "source_created_at": "Timestamp when the batch was received. Cast to timestamptz for date math.",
            "source_updated_at": "Timestamp of last status change.",
            "approval_date_at": "Timestamp of final approval. NULL if not yet approved.",
        },
        "common_filters": [
            "isactive = true",
            "source_created_at::timestamptz >= (NOW() - INTERVAL '30 days')",
        ],
    },

    "reporting.sample_details": {
        "domain": "samples",
        "description": "Individual sample items (aliquots) within a batch. One batch = many items.",
        "columns": {
            "source_id": "Primary key (bigint).",
            "sample_header_id": "FK to reporting.sample_headers.source_id.",
            "analyte_id": "FK to reporting.analytes.source_id.",
        },
        "joins": {
            "reporting.sample_headers": "reporting.sample_details.sample_header_id = reporting.sample_headers.source_id",
        },
    },

    "reporting.sample_analysis_stages": {
        "domain": "samples",
        "description": "Workflow stage lookup. Maps stage ID to a named workflow step.",
        "columns": {
            "source_id": "Primary key (bigint).",
            "sample_workflow": "Stage name. Key values: 'Samples In Lab', 'Quality Control'.",
        },
        "joins": {
            "reporting.sample_headers": "CAST(NULLIF(reporting.sample_headers.sample_tracking_stage, '') AS BIGINT) = reporting.sample_analysis_stages.source_id",
        },
    },

    "reporting.analytes": {
        "domain": "samples",
        "description": "Master list of laboratory analytes (tests).",
        "columns": {
            "source_id": "Primary key.",
            "name": "Analyte name (e.g. 'Creatinine', 'Glucose').",
        },
    },

    "reporting.sample_types": {
        "domain": "samples",
        "description": "Specimen/matrix types (e.g. 'Serum', 'Urine', 'Whole Blood').",
        "columns": {
            "source_id": "Primary key.",
            "name": "Sample type label.",
        },
    },

    # ── TAT ────────────────────────────────────────────────────────────────────
    "reporting.tat_captured": {
        "domain": "tat",
        "description": "Turnaround time records. One record per sample/analyte combination.",
        "columns": {
            "source_id": "Primary key.",
            "sample_header_id": "FK to reporting.sample_headers.source_id.",
            "analyte_id": "FK to reporting.analytes.source_id.",
            "sample_type_id": "FK to reporting.sample_types.source_id.",
            "tat_overdue_days": "Days over the SLA deadline. Negative means finished early; 0 = on-time; positive = late.",
            "is_complete": "Boolean. True if the sample has been fully processed.",
            "source_created_at": "When the TAT record was captured.",
        },
        "joins": {
            "reporting.sample_headers": "reporting.tat_captured.sample_header_id = reporting.sample_headers.source_id",
            "reporting.analytes": "reporting.tat_captured.analyte_id = reporting.analytes.source_id",
            "reporting.sample_types": "reporting.tat_captured.sample_type_id = reporting.sample_types.source_id",
        },
    },

    # ── QUALITY ────────────────────────────────────────────────────────────────
    "reporting.corrective_actions": {
        "domain": "quality",
        "description": "CAPA (Corrective and Preventive Actions) log.",
        "columns": {
            "source_id": "Primary key.",
            "status": "CAPA status. Closed values: 'closed', 'completed', 'cancelled'.",
            "source_created_at": "Date CAPA was opened.",
        },
    },

    "reporting.audit_findings": {
        "domain": "quality",
        "description": "Audit findings and non-conformances.",
        "columns": {
            "source_id": "Primary key.",
            "category": "Finding category (e.g. 'Documentation', 'Process').",
            "status": "Resolved values: 'closed', 'resolved'. Open otherwise.",
        },
    },

    "reporting.v_qc_stability_metrics": {
        "domain": "quality",
        "description": "Pre-computed QC stability view. Shows analytes with drift or instability.",
        "columns": {
            "analyte_name": "Name of the analyte.",
            "robust_cv_pct": "Coefficient of variation %. > 15 = warning; > 25 = critical.",
            "pass_rate_pct": "QC pass rate for this analyte (0–100).",
        },
    },

    # ── SUPPORT / COMPLAINTS ────────────────────────────────────────────────────
    "reporting.complaints": {
        "domain": "support",
        "description": "Customer complaints and support tickets.",
        "columns": {
            "source_id": "Primary key.",
            "priority": "Ticket priority (e.g. 'High', 'Medium', 'Low').",
            "is_closed": "Boolean. True = resolved.",
            "current_department": "Department currently handling the ticket.",
            "resolution_sla_status": "SLA outcome. 'Achieved' = met SLA target.",
            "resolved_time": "Timestamp when the ticket was closed.",
            "source_created_at": "When the complaint was logged.",
        },
    },

    # ── EQUIPMENT ────────────────────────────────────────────────────────────────
    "reporting.equipment_assets": {
        "domain": "equipment",
        "description": "Master list of laboratory instruments.",
        "columns": {
            "source_id": "Primary key.",
            "name": "Instrument name.",
            "active": "Boolean. True = currently in service.",
            "assigned_department": "Lab section the instrument belongs to.",
        },
    },

    "reporting.equipment_logs": {
        "domain": "equipment",
        "description": "Equipment activity log (usage events, verifications, calibrations).",
        "columns": {
            "source_id": "Primary key.",
            "equipment_id": "FK to reporting.equipment_assets.source_id.",
            "event_type": "Type of event (e.g. 'used', 'verification', 'calibration').",
            "event_date": "Date of the event.",
        },
        "joins": {
            "reporting.equipment_assets": "reporting.equipment_logs.equipment_id = reporting.equipment_assets.source_id",
        },
    },

    "reporting.equipment_due_status": {
        "domain": "equipment",
        "description": "Maintenance status view per instrument. Pre-joined, safe to query directly.",
        "columns": {
            "source_id": "Instrument ID.",
            "name": "Instrument name.",
            "maintenance_status": "Values: 'healthy', 'pending', 'overdue'.",
            "maintenance_due_date": "Next maintenance due date.",
            "assigned_department": "Owning department.",
        },
    },

    # ── INVENTORY ────────────────────────────────────────────────────────────────
    "reporting.inventory_items": {
        "domain": "inventory",
        "description": "Physical stock items (reagents, consumables).",
        "columns": {
            "source_id": "Primary key.",
            "inventory_sub_category_id": "FK to reporting.inventory_sub_categories.source_id.",
            "stock_in": "Current on-hand quantity.",
            "payload": "JSONB column. Use ->> operator. Key 'expiry_date' and 'batch_code'.",
        },
        "joins": {
            "reporting.inventory_sub_categories": "reporting.inventory_items.inventory_sub_category_id = reporting.inventory_sub_categories.source_id",
        },
    },

    "reporting.inventory_sub_categories": {
        "domain": "inventory",
        "description": "Subcategories of inventory items. Contains the minimum_level threshold.",
        "columns": {
            "source_id": "Primary key.",
            "name": "Item name.",
            "minimum_level": "Minimum required stock. If stock_in < minimum_level, item is low.",
            "inventory_category_id": "FK to reporting.inventory_categories.source_id.",
        },
    },

    "reporting.inventory_categories": {
        "domain": "inventory",
        "description": "Top-level inventory groupings (e.g. 'Reagents', 'PPE', 'Consumables').",
        "columns": {
            "source_id": "Primary key.",
            "name": "Category name.",
        },
    },

    "reporting.inventory_orders": {
        "domain": "inventory",
        "description": "Purchase orders placed with suppliers.",
        "columns": {
            "source_id": "Primary key.",
            "order_number": "PO number.",
            "supplier_id": "FK to reporting.suppliers.source_id.",
            "status": "Fulfillment status: 'fulfilled', 'not_fulfilled', 'partially_fulfilled'.",
            "source_created_at": "Order date.",
        },
        "joins": {
            "reporting.suppliers": "reporting.inventory_orders.supplier_id = reporting.suppliers.source_id",
        },
    },

    "reporting.suppliers": {
        "domain": "inventory",
        "description": "Supplier/vendor master list.",
        "columns": {
            "source_id": "Primary key.",
            "name": "Supplier name.",
        },
    },

    # ── BILLING / FINANCIALS ──────────────────────────────────────────────────
    "reporting.customer_invoice": {
        "domain": "billing",
        "description": "Customer invoices for lab services.",
        "columns": {
            "source_id": "Primary key.",
            "invoice_number": "Human-readable invoice ID (e.g. INV-2024-001).",
            "total": "Total invoice amount.",
            "total_tax": "Tax amount.",
            "due_date": "Date payment is due.",
            "customer_id": "FK to reporting.clients.source_id.",
            "source_created_at": "Invoice date.",
        },
    },

    "reporting.invoice_payment_details": {
        "domain": "billing",
        "description": "Payment records linked to invoices.",
        "columns": {
            "source_id": "Primary key.",
            "invoice_id": "FK to reporting.customer_invoice.source_id.",
            "transaction_no": "Bank/mobile transaction reference.",
            "source_created_at": "Payment date.",
        },
        "joins": {
            "reporting.customer_invoice": "reporting.invoice_payment_details.invoice_id = reporting.customer_invoice.source_id",
        },
    },

    # ── CRM / CLIENTS ────────────────────────────────────────────────────────────
    "reporting.clients": {
        "domain": "crm",
        "description": "Customer/client master list.",
        "columns": {
            "source_id": "Primary key.",
            "name": "Client/organization name.",
        },
    },

    # ── PERSONNEL ────────────────────────────────────────────────────────────────
    "reporting.users": {
        "domain": "personnel",
        "description": "LIMS staff/analysts. NOTE: Only name and active status are safe to expose.",
        "columns": {
            "source_id": "Primary key.",
            "name": "Analyst full name.",
            "active": "Boolean. True = currently employed/active.",
        },
        "sensitive": True,
        "safe_columns": ["source_id", "name", "active"],
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
    lines = ["Available Tables (PostgreSQL reporting schema):"]

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
