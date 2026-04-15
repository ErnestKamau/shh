# Canonical Intent Registry — single source of truth for all valid intents
CANONICAL_INTENTS = {
    "sample_count_total":              {"type": "data", "version": "v1"},
    "sample_count_pending_review":     {"type": "data", "version": "v1"},
    "sample_count_in_reception":       {"type": "data", "version": "v1"},
    "sample_count_en_route":           {"type": "data", "version": "v1"},
    "sample_count_in_lab":             {"type": "data", "version": "v1"},
    "sample_count_today":              {"type": "data", "version": "v1"},
    "sample_count_this_month":         {"type": "data", "version": "v1"},
    "samples_by_status":               {"type": "data", "version": "v1"},
    "batch_count_total":               {"type": "data", "version": "v1"},
    "batch_count_in_lab":              {"type": "data", "version": "v1"},
    "lab_repeat_batches":              {"type": "data", "version": "v1"},
    "lab_subcontracted_pending":       {"type": "data", "version": "v1"},
    "lab_section_performance":         {"type": "data", "version": "v1"},
    "lab_tat_trend_analysis":          {"type": "data", "version": "v1"},
    "capa_pending":                    {"type": "data", "version": "v1"},
    "sop_search":                      {"type": "data", "version": "v1"},
    "sample_process_status":           {"type": "data", "version": "v1"},
    "report_generation":               {"type": "data", "version": "v1"},
    "training_records":                {"type": "data", "version": "v1"},
    "audit_status":                    {"type": "data", "version": "v1"},
    "inventory_low_stock":             {"type": "data", "version": "v1"},
    "inventory_supplier_performance":  {"type": "data", "version": "v1"},
    "inventory_order_status":          {"type": "data", "version": "v1"},
    "inventory_by_department":         {"type": "data", "version": "v1"},
    "inventory_store_capacity":        {"type": "data", "version": "v1"},
    "inventory_cost_allocation":       {"type": "data", "version": "v1"},
    "inventory_receiving_pending":     {"type": "data", "version": "v1"},
    "inventory_dead_stock_financials": {"type": "data", "version": "v1"},
    "equipment_utilization":           {"type": "data", "version": "v1"},
    "equipment_downtime_summary":      {"type": "data", "version": "v1"},
    "equipment_maintenance_schedule":  {"type": "data", "version": "v1"},
    "equipment_calibration_schedule":  {"type": "data", "version": "v1"},
    "equipment_warranty_expiry":       {"type": "data", "version": "v1"},
    "equipment_by_department":         {"type": "data", "version": "v1"},
    "equipment_parts_history":         {"type": "data", "version": "v1"},
    "equipment_operator_certified":    {"type": "data", "version": "v1"},
    "board_equipment_maintenance_overdue": {"type": "data", "version": "v1"},
    "board_equipment_calibration_overdue": {"type": "data", "version": "v1"},
    "board_equipment_general_overdue":     {"type": "data", "version": "v1"},
    "board_inventory_expiry_risk":         {"type": "data", "version": "v1"},
    "board_lab_tat_overdue_results":       {"type": "data", "version": "v1"},
    "board_qc_ooc_events":                 {"type": "data", "version": "v1"},
    "board_qc_kpi_summary":                {"type": "data", "version": "v1"},
    "board_qc_pareto_failures":            {"type": "data", "version": "v1"},
    "board_qc_drifting_analytes":          {"type": "data", "version": "v1"},
    "board_analyte_performance_lookup":    {"type": "data", "version": "v1"},
    "board_sla_unresponded_tickets":       {"type": "data", "version": "v1"},
    "board_inventory_dead_stock":          {"type": "data", "version": "v1"},
    "board_equipment_verification":        {"type": "data", "version": "v1"},
    "action_email_sop":        {"type": "action", "version": "v1", "risk_level": "low"},
    "action_retest_sample":    {"type": "action", "version": "v1", "risk_level": "medium"},
    "clarification_needed":    {"type": "meta", "version": "v1"},
    "fallback_rag":            {"type": "meta", "version": "v1"},
    "unknown_intent":          {"type": "meta", "version": "v1"},
}

_CLASSIFIER_SYSTEM_PROMPT = """You are a strict intent classifier for Imara, a laboratory information management (LIMS) AI assistant.

Your ONLY job is to classify the user's message into exactly ONE canonical intent and extract minimal entities.

RULES:
1. Return ONLY valid JSON. No explanation, no markdown, no extra text.
2. The "intent" MUST be one of the canonical keys listed below, or "fallback_rag" if nothing matches.
3. The "type" MUST be "data" for read-only queries, "action" for operational requests, or "meta" for fallback/clarification.
4. "confidence" MUST be a float between 0.0 and 1.0 reflecting how certain you are.
5. "entities" should contain ONLY the minimal parameters the intent needs. Use empty object {} if none.
6. DO NOT answer the user's question. DO NOT generate conversational text.

CANONICAL INTENT KEYS:
""" + "\n".join(f"- {k} ({v['type']})" for k, v in sorted(CANONICAL_INTENTS.items()) if v["type"] != "meta") + """

OUTPUT FORMAT (strict JSON, nothing else):
{"intent": "<key>", "confidence": <float>, "type": "<data|action|meta>", "entities": {}, "confidence_reason": "<brief reason>"}

EXAMPLES:
User: "How is TAT this month compared to last quarter?"
{"intent": "lab_tat_trend_analysis", "confidence": 0.93, "type": "data", "entities": {"period_current": "this_month", "period_compare": "last_quarter"}, "confidence_reason": "Explicit comparison of TAT across two time windows."}

User: "Send the SOP for sample logging to the lab manager"
{"intent": "action_email_sop", "confidence": 0.90, "type": "action", "entities": {"document_name": "sample logging", "recipient_role": "lab manager"}, "confidence_reason": "Explicit request to email a specific SOP to a role."}

User: "What is the weather like?"
{"intent": "fallback_rag", "confidence": 0.95, "type": "meta", "entities": {}, "confidence_reason": "Not a LIMS operational query."}
"""
