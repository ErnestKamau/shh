<style>
	.lab-panel-theme {
		--workflow-accent: var(--color-primary, var(--color-primary));
		--workflow-accent-soft: var(--color-primary-soft, var(--color-primary-soft));
		--workflow-border: #e2e8f0;
		--workflow-muted: #64748b;
		--workflow-surface: #ffffff;
		--workflow-bg: #f8fafc;
		--workflow-text-main: #1e293b;
		--workflow-text-muted: #64748b;
		--card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
		--card-shadow-hover: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
	}

	.lab-panel-theme .workflow-board-panel {
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 12px;
		margin-bottom: 1.5rem;
		box-shadow: var(--card-shadow);
		overflow: hidden;
	}

	.lab-panel-theme .workflow-board-panel-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 12px;
		padding: 20px 24px;
		border-bottom: 1px solid var(--workflow-border);
		background: #fff;
	}

	.lab-panel-theme .workflow-board-panel-header h5,
	.lab-panel-theme .workflow-board-panel-header h6 {
		margin: 0;
		font-size: var(--text-base);
		font-weight: var(--font-semibold);
		color: var(--workflow-text-main);
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.lab-panel-theme .workflow-board-panel-header h5 .mdi,
	.lab-panel-theme .workflow-board-panel-header h6 .mdi {
		color: var(--workflow-accent);
		font-size: var(--text-lg);
	}

	/* Stat Cards */
	.lab-panel-theme .stat-cards-row {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
		gap: 16px;
		margin-bottom: 24px;
	}

	.lab-panel-theme .stat-card {
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 12px;
		padding: 20px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		box-shadow: var(--card-shadow);
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.lab-panel-theme .stat-card:hover {
		transform: translateY(-2px);
		box-shadow: var(--card-shadow-hover);
	}

	.lab-panel-theme .stat-card-label {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		color: var(--workflow-text-muted);
		text-transform: uppercase;
		letter-spacing: 0.025em;
		margin-bottom: 8px;
	}

	.lab-panel-theme .stat-card-content {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.lab-panel-theme .stat-card-value {
		font-size: var(--text-metric);
		font-weight: var(--font-bold);
		color: var(--workflow-text-main);
	}

	.lab-panel-theme .stat-card-icon {
		width: 48px;
		height: 48px;
		border-radius: 10px;
		background: var(--workflow-accent-soft);
		color: var(--workflow-accent);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: var(--text-lg);
	}

	.lab-panel-theme .workflow-board-panel-body {
		padding: 24px;
	}

	.lab-panel-theme .workflow-board-panel-body.p-0 {
		padding: 0;
	}

	.lab-panel-theme .workflow-board-filter-nested {
		background: #f8fafc;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 20px;
		margin-bottom: 20px;
	}

	.lab-panel-theme .workflow-table thead th {
		background: #f8fafc !important;
		color: var(--workflow-text-muted) !important;
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		border-bottom: 1px solid var(--workflow-border) !important;
		padding: 14px 16px;
	}

	.lab-panel-theme .workflow-table tbody td {
		padding: 16px;
		vertical-align: middle;
		font-size: var(--text-sm);
		color: var(--workflow-text-main);
		border-bottom: 1px solid var(--workflow-border);
	}

	.lab-panel-theme .workflow-table.table-hover tbody tr:hover {
		background-color: #f1f5f9;
	}

	.lab-panel-theme .workflow-status-chip {
		display: inline-flex;
		align-items: center;
		padding: 4px 10px;
		border-radius: 20px;
		font-weight: var(--font-semibold);
		font-size: var(--text-caption);
		background: #f1f5f9;
		color: #475569;
		border: 1px solid #e2e8f0;
	}

	.lab-panel-theme .btn-action-sm {
		height: 38px;
		padding: 0 18px;
		font-size: var(--text-sm);
		border-radius: 8px;
		font-weight: var(--font-semibold);
		transition: all 0.2s;
	}

	.lab-panel-theme .nav-tabs {
		border-bottom: 1px solid var(--workflow-border);
		padding: 0 24px;
		background: #fff;
	}

	.lab-panel-theme .nav-tabs .nav-link {
		border: none;
		border-bottom: 2px solid transparent;
		color: var(--workflow-text-muted);
		padding: 14px 16px;
		font-weight: var(--font-semibold);
		font-size: var(--text-sm);
	}

	.lab-panel-theme .nav-tabs .nav-link:hover {
		color: var(--workflow-accent);
		border-bottom-color: #cbd5e1;
	}

	.lab-panel-theme .nav-tabs .nav-link.active {
		color: var(--workflow-accent);
		border-bottom: 2px solid var(--workflow-accent);
		background: transparent;
	}

	.lab-panel-theme .batch-details-checkboxes-section {
		margin-top: 0.25rem;
		margin-bottom: 0.75rem;
		padding-left: 0.75rem;
		padding-right: 0.75rem;
	}

	.lab-panel-theme .batch-details-checkboxes-panel {
		background: var(--workflow-bg);
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 14px 16px;
	}

	.lab-panel-theme .batch-details-checkboxes-panel .control-label {
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
		color: var(--workflow-text-main);
		line-height: 1.45;
	}
</style>