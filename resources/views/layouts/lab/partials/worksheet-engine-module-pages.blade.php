<style type="text/css">
	/* Module accent overrides */
	.lab-panel-theme.module-accent-purple {
		--workflow-accent: #6f42c1;
		--workflow-accent-soft: #f3eef9;
	}
	.lab-panel-theme.module-accent-success {
		--workflow-accent: #28a745;
		--workflow-accent-soft: #e8f5e9;
	}
	.lab-panel-theme.module-accent-info {
		--workflow-accent: #17a2b8;
		--workflow-accent-soft: #e7f6f8;
	}
	.lab-panel-theme.module-accent-primary {
		--workflow-accent: #3b5fc0;
		--workflow-accent-soft: #f0f4ff;
	}

	.lab-panel-theme .workflow-board-panel-header .btn-primary,
	.lab-panel-theme .btn-primary.btn-action-sm {
		background-color: var(--workflow-accent);
		border-color: var(--workflow-accent);
	}
	.lab-panel-theme .workflow-board-panel-header .btn-primary:hover,
	.lab-panel-theme .btn-primary.btn-action-sm:hover {
		background-color: color-mix(in srgb, var(--workflow-accent) 85%, #000);
		border-color: color-mix(in srgb, var(--workflow-accent) 85%, #000);
	}

	.lab-panel-theme .workflow-board-filter-nested .form-label,
	.lab-panel-theme .workflow-board-panel-body .control-label,
	.lab-panel-theme .workflow-board-panel-body label.font-weight-bold {
		color: var(--workflow-text-main);
		font-weight: 600;
	}

	/* Modals inside worksheet engine pages */
	.lab-panel-theme .modal-content {
		border: 1px solid var(--workflow-border);
		border-radius: 12px;
		overflow: hidden;
	}
	.lab-panel-theme .modal-header {
		background: #fff;
		border-bottom: 1px solid var(--workflow-border);
	}
	.lab-panel-theme .modal-footer {
		background: var(--workflow-bg);
		border-top: 1px solid var(--workflow-border);
	}
	.lab-panel-theme .step-nav.active {
		color: var(--workflow-accent) !important;
		border-bottom: 3px solid var(--workflow-accent) !important;
	}
	.lab-panel-theme .modal .card {
		border: 1px solid var(--workflow-border) !important;
		border-radius: 10px !important;
		box-shadow: var(--card-shadow) !important;
	}
	.lab-panel-theme .modal .card-body {
		padding: 1.25rem;
	}

	/* DataTables inside themed panels */
	.lab-panel-theme .dataTables_wrapper .dataTables_length,
	.lab-panel-theme .dataTables_wrapper .dataTables_filter,
	.lab-panel-theme .dataTables_wrapper .dataTables_info,
	.lab-panel-theme .dataTables_wrapper .dataTables_paginate {
		padding: 12px 16px;
		color: var(--workflow-text-muted);
	}
	.lab-panel-theme table.dataTable thead th {
		background: #f8fafc !important;
		color: var(--workflow-text-muted) !important;
		font-size: 0.75rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		border-bottom: 1px solid var(--workflow-border) !important;
	}
	.lab-panel-theme table.dataTable tbody td {
		color: var(--workflow-text-main);
		border-bottom: 1px solid var(--workflow-border);
		vertical-align: middle;
	}
	.lab-panel-theme table.dataTable.table-hover tbody tr:hover {
		background-color: #f1f5f9;
	}

	.lab-panel-theme .badge-accent {
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
		border: 1px solid color-mix(in srgb, var(--workflow-accent) 25%, transparent);
	}

	/* Legacy Bootstrap tables inside themed pages */
	.lab-panel-theme .table.table-striped thead th,
	.lab-panel-theme .table.table-hover thead th {
		background: #f8fafc !important;
		color: var(--workflow-text-muted) !important;
		font-size: 0.75rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		border-bottom: 1px solid var(--workflow-border) !important;
	}
	.lab-panel-theme .table.table-striped tbody td,
	.lab-panel-theme .table.table-hover tbody td {
		color: var(--workflow-text-main);
		border-bottom: 1px solid var(--workflow-border);
		vertical-align: middle;
	}
	.lab-panel-theme .table.table-hover tbody tr:hover {
		background-color: #f1f5f9;
	}
</style>
