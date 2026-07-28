{{--
  Bridges legacy inventory markup to lab surface / workflow panel styling
  without rewriting every blade file.
--}}
<style>
	.inventory-page h2,
	.inventory-page h3 {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: var(--ls-font-semibold, 600);
		color: var(--ls-color-ink, var(--color-text));
	}

	.inventory-page h2.p-4,
	.inventory-page h3.p-4 {
		padding: 0.5rem 0 0.85rem !important;
		font-size: var(--ls-text-2xl, 1.05rem);
	}

	.inventory-page h2 .mdi,
	.inventory-page h3 .mdi {
		color: var(--color-primary);
	}

	.inventory-page > .bg-light,
	.inventory-page .bg-light:not(.modal-body):not(.dropdown-menu):not(.card-body) {
		background: var(--ls-color-surface, #fff) !important;
		border: 1px solid var(--workflow-border, var(--color-border, #e2e8f0));
		border-radius: 12px;
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.1));
		padding: 0 !important;
		overflow: hidden;
		margin-bottom: 0.85rem;
	}

	.inventory-page .bg-light > .table-responsive,
	.inventory-page .bg-light > .card-body {
		padding: 0.85rem 1rem;
	}

	.inventory-page .table.table-bordered {
		border: none;
	}

	.inventory-page .table thead.bg-light,
	.inventory-page .table thead.bg-light th,
	.inventory-page .table thead.p-2 {
		background: transparent !important;
	}

	.inventory-page .tab-card {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		box-shadow: var(--card-shadow);
		overflow: hidden;
		background: #fff;
	}

	.inventory-page .tab-card .card-header.tab-card-header {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.35rem 0.75rem 0;
	}

	.inventory-page .tab-card-header > .nav-tabs > li > a.show,
	.inventory-page .tab-card-header > .nav-tabs > li > a.active {
		border-bottom-color: var(--color-primary) !important;
		color: var(--color-primary) !important;
	}

	.inventory-page .tab-card .nav-link.active {
		background-color: var(--color-primary-soft, #f1f5f9) !important;
		border-color: var(--color-border, #e2e8f0) !important;
		color: var(--color-primary) !important;
	}

	.inventory-page .card:not(.tab-card) {
		border-radius: 12px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: var(--card-shadow);
	}

	.inventory-page .card-head-sm,
	.inventory-page .card .card-header:not(.tab-card-header) {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
	}

	.inventory-page .btn-primary {
		background-color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
	}

	.inventory-page .btn-primary:hover:not(:disabled) {
		background-color: var(--color-primary-hover) !important;
		border-color: var(--color-primary-hover) !important;
	}

	.inventory-page .btn-outline-primary {
		color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
	}

	.inventory-page .btn-outline-primary:hover {
		background-color: var(--color-primary) !important;
		color: #fff !important;
	}

	.inventory-page .text-primary {
		color: var(--color-primary) !important;
	}

	.inventory-page .badge.badge-pill.bg-white {
		background: var(--color-primary-soft, #f1f5f9) !important;
		color: var(--color-primary) !important;
		border: 1px solid var(--color-border, #e2e8f0);
		font-weight: 600;
	}
</style>
