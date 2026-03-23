<style>
	.lab-panel-theme {
		--workflow-accent: #3b5fc0;
		--workflow-accent-soft: #f0f4ff;
		--workflow-border: #e9ecef;
		--workflow-muted: #64748b;
		--workflow-surface: #fafbfc;
	}

	.lab-panel-theme .workflow-board-panel {
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		margin-bottom: 1rem;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.lab-panel-theme .workflow-board-panel-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 10px;
		padding: 12px 18px;
		border-bottom: 1px solid #f1f5f9;
		background: var(--workflow-surface);
		border-radius: 10px 10px 0 0;
	}

	.lab-panel-theme .workflow-board-panel-header h5,
	.lab-panel-theme .workflow-board-panel-header h6 {
		margin: 0;
		font-size: 0.95rem;
		font-weight: 600;
		color: #334155;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.lab-panel-theme .workflow-board-panel-header h5 .mdi,
	.lab-panel-theme .workflow-board-panel-header h6 .mdi {
		color: var(--workflow-muted);
		font-size: 1.1rem;
	}

	.lab-panel-theme .workflow-board-panel-body {
		padding: 18px 20px;
	}

	.lab-panel-theme .workflow-board-panel-body.flush-top {
		padding-top: 12px;
	}

	.lab-panel-theme .workflow-board-section-label {
		font-size: 0.9rem;
		font-weight: 600;
		color: #475569;
		margin-bottom: 12px;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.lab-panel-theme .workflow-board-section-label .mdi {
		color: var(--workflow-muted);
	}

	.lab-panel-theme .workflow-board-filter-nested {
		background: #fff;
		border: 1px solid #f1f5f9;
		border-radius: 8px;
		padding: 16px 18px;
		margin-bottom: 16px;
	}

	.lab-panel-theme .workflow-board-legend {
		border-top: 1px solid #f1f5f9;
		padding-top: 16px;
		margin-top: 8px;
	}

	.lab-panel-theme .workflow-board-legend .text-muted {
		color: #64748b !important;
		font-size: 0.85rem;
	}

	.lab-panel-theme .workflow-table thead th {
		background: #f8fafc !important;
		color: var(--workflow-muted) !important;
		font-size: 0.75rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		border-bottom: 1px solid var(--workflow-border) !important;
		border-top: none !important;
		vertical-align: middle;
	}

	.lab-panel-theme .workflow-table.table-hover tbody tr:hover {
		background-color: #f8fafc;
	}

	.lab-panel-theme .workflow-status-chip {
		display: inline-block;
		background-color: #f8fafc;
		padding: 6px 12px 6px 14px;
		border-radius: 6px;
		font-weight: 500;
		color: #475569;
		font-size: 0.85rem;
		border: 1px solid var(--workflow-border);
		border-left: 4px solid var(--chip-accent, #6c757d);
	}

	.lab-panel-theme .workflow-empty-state .mdi {
		color: #cbd5e1 !important;
	}

	.lab-panel-theme .workflow-empty-state p,
	.lab-panel-theme .workflow-empty-state h5 {
		color: var(--workflow-muted) !important;
	}

	.lab-panel-theme .btn-action-sm {
		height: 32px;
		padding: 0 14px;
		font-size: 0.82rem;
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-weight: 500;
	}

	.lab-panel-theme .workflow-board-panel-body .control-label,
	.lab-panel-theme .workflow-board-filter-nested .control-label {
		font-size: 0.8rem;
		font-weight: 600;
		color: #64748b;
		margin-bottom: 0.35rem;
	}

	.lab-panel-theme .workflow-board-panel-body .form-label {
		font-size: 0.8rem;
		font-weight: 600;
		color: #64748b;
	}

	.lab-panel-theme .workflow-board-panel-body .form-control,
	.lab-panel-theme .workflow-board-filter-nested .form-control {
		border-radius: 6px;
		border-color: var(--workflow-border);
	}

	.lab-panel-theme .workflow-board-panel-body .form-control:focus,
	.lab-panel-theme .workflow-board-filter-nested .form-control:focus {
		border-color: var(--workflow-accent);
		box-shadow: 0 0 0 0.2rem rgba(59, 95, 192, 0.12);
	}

	.lab-panel-theme .batch-nav-tabs {
		border-bottom: 2px solid var(--workflow-border);
		justify-content: flex-start;
		flex-wrap: wrap;
		gap: 2px;
	}

	.lab-panel-theme .batch-nav-tabs .nav-item {
		margin-bottom: -2px;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link {
		color: var(--workflow-muted);
		background: transparent;
		border: none;
		border-bottom: 2px solid transparent;
		border-radius: 0;
		padding: 10px 14px;
		font-size: 0.82rem;
		font-weight: 500;
		white-space: nowrap;
		transition: color 0.2s, border-color 0.2s;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link i {
		margin-right: 5px;
		font-size: 0.9em;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link:hover {
		color: var(--workflow-accent);
		border-bottom-color: #c7d7fc;
		background: transparent;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link.active {
		color: var(--workflow-accent);
		border-bottom-color: var(--workflow-accent);
		background: transparent;
		font-weight: 600;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link .badge {
		font-size: 0.65rem;
		padding: 2px 6px;
		border-radius: 20px;
		margin-left: 4px;
		background: #e2e8f0;
		color: #475569;
		font-weight: 600;
	}

	.lab-panel-theme .batch-nav-tabs .nav-link.active .badge {
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
	}

	.lab-panel-theme .batch-show-alerts .alert {
		border-radius: 8px;
		border: 1px solid transparent;
	}
</style>