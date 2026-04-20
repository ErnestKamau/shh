"""
Transformer Registry
Maps table keys from table_config.yaml to concrete transformer classes.
"""
from __future__ import annotations

from python.py_etl.transformers.tables.sample_headers_transformer import SampleHeadersTransformer
from python.py_etl.transformers.tables.sample_details_transformer import SampleDetailsTransformer
from python.py_etl.transformers.tables.sample_dates_transformer import SampleDatesTransformer
from python.py_etl.transformers.tables.captured_results_transformer import CapturedResultsTransformer
from python.py_etl.transformers.tables.equipment_logs_transformer import (
    MaintenanceLogsTransformer,
    VerificationLogsTransformer,
)
from python.py_etl.transformers.tables.equipment_assets_transformer import EquipmentAssetsTransformer
from python.py_etl.transformers.tables.tat_captured_transformer import TatCapturedTransformer
from python.py_etl.transformers.tables.qc_processed_results_transformer import QcProcessedResultsTransformer
from python.py_etl.transformers.tables.complaints_transformer import ComplaintsTransformer
from python.py_etl.transformers.tables.documents_transformer import DocumentsTransformer
from python.py_etl.transformers.tables.inventory_items_transformer import InventoryItemsTransformer
from python.py_etl.transformers.tables.inventory_sub_categories_transformer import InventorySubCategoriesTransformer
from python.py_etl.transformers.tables.audit_findings_transformer import AuditFindingsTransformer
from python.py_etl.transformers.tables.corrective_actions_transformer import CorrectiveActionsTransformer
from python.py_etl.transformers.tables.qc_results_transformer import QcResultsTransformer
from python.py_etl.transformers.tables.analytes_transformer import AnalytesTransformer
from python.py_etl.transformers.tables.sample_analysis_stages_transformer import SampleAnalysisStagesTransformer
from python.py_etl.transformers.tables.inventory_orders_transformer import InventoryOrdersTransformer
from python.py_etl.transformers.tables.suppliers_transformer import SuppliersTransformer
from python.py_etl.transformers.tables.inventory_categories_transformer import InventoryCategoriesTransformer
from python.py_etl.transformers.tables.users_transformer import UsersTransformer
from python.py_etl.transformers.tables.clients_transformer import ClientsTransformer
from python.py_etl.transformers.tables.reference_tables_transformer import (
    TicketPrioritiesTransformer,
    TicketStatusesTransformer,
    TicketCategoriesTransformer,
    DepartmentsTransformer,
    SampleTypesTransformer,
)

TRANSFORMER_REGISTRY = {
    "sample_headers": SampleHeadersTransformer,
    "sample_details": SampleDetailsTransformer,
    "sample_dates": SampleDatesTransformer,
    "sample_analysis_stages": SampleAnalysisStagesTransformer,
    "captured_results": CapturedResultsTransformer,
    "maintainance_calibration_logs": MaintenanceLogsTransformer,
    "verification_logs": VerificationLogsTransformer,
    "equipment": EquipmentAssetsTransformer,
    "tat_captured": TatCapturedTransformer,
    "qc_processed_result": QcProcessedResultsTransformer,
    "complaints": ComplaintsTransformer,
    "documents": DocumentsTransformer,
    "inventory_items": InventoryItemsTransformer,
    "inventory_sub_categories": InventorySubCategoriesTransformer,
    "audit_findings": AuditFindingsTransformer,
    "corrective_actions": CorrectiveActionsTransformer,
    # QC Stability Board — synced to Postgres reporting schema
    "qc_results": QcResultsTransformer,
    "analytes": AnalytesTransformer,
    "inventory_orders": InventoryOrdersTransformer,
    "suppliers": SuppliersTransformer,
    "inventory_categories": InventoryCategoriesTransformer,
    # New Analytical Foundation (MAS Expansion)
    "users": UsersTransformer,
    "clients": ClientsTransformer,
    "ticket_priorities": TicketPrioritiesTransformer,
    "ticket_statuses": TicketStatusesTransformer,
    "ticket_categories": TicketCategoriesTransformer,
    "departments": DepartmentsTransformer,
    "sample_types": SampleTypesTransformer,
}
