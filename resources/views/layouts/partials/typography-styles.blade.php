<style>
	html {
		font-size: 16px;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	body {
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		color: var(--color-text);
	}

	/* Utility classes */
	.text-caption {
		font-size: var(--text-caption);
		line-height: var(--leading-normal);
	}

	.text-ui {
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.text-section {
		font-size: var(--text-base);
		line-height: var(--leading-normal);
	}

	.text-subtitle {
		font-size: var(--text-lg);
		line-height: var(--leading-tight);
	}

	.text-page-title {
		font-size: var(--text-xl);
		line-height: var(--leading-tight);
	}

	.text-metric {
		font-size: var(--text-metric);
		line-height: var(--leading-tight);
		font-weight: var(--font-bold);
	}

	.font-normal { font-weight: var(--font-normal); }
	.font-medium { font-weight: var(--font-medium); }
	.font-semibold { font-weight: var(--font-semibold); }
	.font-bold { font-weight: var(--font-bold); }

	.text-muted-theme {
		color: var(--color-muted) !important;
	}

	.label-caps {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--color-muted);
	}

	/* Bootstrap heading reset inside main content */
	#main-container-body h1 {
		font-size: var(--text-xl);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
	}

	#main-container-body h2 {
		font-size: var(--text-lg);
		font-weight: var(--font-semibold);
		line-height: var(--leading-tight);
	}

	#main-container-body h3 {
		font-size: var(--text-base);
		font-weight: var(--font-semibold);
		line-height: var(--leading-tight);
	}

	#main-container-body h4,
	#main-container-body h5,
	#main-container-body h6 {
		font-size: var(--text-sm);
		font-weight: var(--font-semibold);
		line-height: var(--leading-normal);
	}

	/* Forms */
	.form-control,
	.custom-select,
	.select2-container .select2-selection--single,
	.select2-container .select2-selection--multiple {
		font-size: var(--text-sm) !important;
		line-height: var(--leading-normal);
	}

	.form-group label,
	.col-form-label,
	label.control-label {
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
		color: var(--color-text);
	}

	.form-text,
	.invalid-feedback,
	.valid-feedback {
		font-size: var(--text-caption);
	}

	/* Buttons */
	.btn {
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
	}

	.btn-sm {
		font-size: var(--text-sm);
	}

	/* Breadcrumbs */
	.breadcrumb {
		font-size: var(--text-caption);
	}

	/* Metric card pattern (dashboard, workflow, billing KPIs) */
	.metric-card {
		padding: var(--space-md);
		border-radius: var(--radius-md);
	}

	.metric-card__label {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--color-muted);
		margin-bottom: 0.35rem;
	}

	.metric-card__value {
		font-size: var(--text-metric);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
		color: var(--color-text);
	}

	.metric-card__value-secondary {
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
		color: var(--color-muted);
	}

	.metric-card__subtext {
		font-size: var(--text-caption);
		color: var(--color-muted);
		margin-top: 0.35rem;
	}

	/* Modal / wizard typography */
	.modal-title,
	.acc-wizard-title {
		font-size: var(--text-xl) !important;
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
	}

	.acc-wizard-eyebrow {
		font-size: var(--text-caption) !important;
		font-weight: var(--font-semibold);
		letter-spacing: 0.06em;
		text-transform: uppercase;
	}

	.acc-wizard-step {
		font-size: var(--text-sm);
		font-weight: var(--font-semibold);
	}

	.acc-wizard-step-index {
		font-size: var(--text-caption);
	}

	.acc-wizard-section-title {
		font-size: var(--text-caption) !important;
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: var(--color-muted) !important;
	}

	.acc-label {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		color: var(--color-muted);
	}

	.acc-wizard-hint {
		font-size: var(--text-caption);
		color: var(--color-muted);
	}

	/* Tables */
	.table thead th,
	.workflow-table thead th,
	.smart-table th {
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
	}

	.table tbody td,
	.workflow-table tbody td,
	.smart-table td {
		font-size: var(--text-sm);
	}

	/* Sidebar nav text — denser than main UI */
	#sidebar-container .list-group a,
	#sidebar-container .list-group .sidebar-submenu a {
		font-size: var(--text-sidebar);
	}

	#sidebar-container .list-group .sidebar-submenu a .badge,
	#sidebar-container .list-group .sidebar-submenu a .floating-badge {
		font-size: 0.6875rem;
	}

	.sidebar-module-div span {
		font-size: var(--text-sidebar) !important;
		font-weight: var(--font-semibold) !important;
		letter-spacing: 0.06em;
		text-transform: uppercase;
	}
</style>
