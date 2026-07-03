<style>
	.lab-dashboard-page {
		--accent-blue: var(--color-primary);
		--accent-primary: var(--color-primary);
	}

	.lab-dashboard-page .dashboard-welcome-hero {
		background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
		border-radius: var(--radius-md);
		padding: var(--space-lg) var(--space-xl);
		margin-bottom: 1.5rem;
		box-shadow: 0 4px 14px var(--color-primary-highlight);
		color: #fff;
	}

	.lab-dashboard-page .dashboard-welcome-hero h3 {
		color: #fff !important;
		margin-bottom: 0.35rem;
		font-size: var(--text-xl);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
	}

	.lab-dashboard-page .dashboard-welcome-hero .dashboard-welcome-subtitle {
		font-size: var(--text-sm);
		color: rgba(255, 255, 255, 0.82) !important;
	}

	.lab-dashboard-page .dashboard-welcome-hero .dashboard-hero-chip {
		font-size: var(--text-sm);
	}

	.lab-dashboard-page .stat-value-secondary {
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
		color: var(--color-muted);
	}

	.lab-dashboard-page .dashboard-welcome-hero .text-muted {
		color: rgba(255, 255, 255, 0.82) !important;
	}

	.lab-dashboard-page .dashboard-welcome-hero .bento-card {
		background: rgba(255, 255, 255, 0.12);
		border: 1px solid rgba(255, 255, 255, 0.2);
		box-shadow: none;
	}

	.lab-dashboard-page .dashboard-welcome-hero .bento-card:hover {
		transform: none;
		background: rgba(255, 255, 255, 0.18);
	}

	.lab-dashboard-page .dashboard-welcome-hero .font-weight-bold,
	.lab-dashboard-page .dashboard-welcome-hero .text-danger,
	.lab-dashboard-page .dashboard-welcome-hero .text-success,
	.lab-dashboard-page .dashboard-welcome-hero .text-muted {
		color: #fff !important;
	}

	.lab-dashboard-page .pipeline-card .stat-label,
	.lab-dashboard-page .pipeline-card .stat-label.text-info,
	.lab-dashboard-page .pipeline-card .stat-label.text-warning,
	.lab-dashboard-page .pipeline-card .stat-label.text-primary,
	.lab-dashboard-page .pipeline-card .stat-label.text-success {
		color: var(--color-primary) !important;
	}

	.lab-dashboard-page .nav-tabs.modern-tabs .nav-link.active {
		color: var(--color-primary);
	}

	.lab-dashboard-page .nav-tabs.modern-tabs .nav-link.active::after {
		background: var(--color-primary);
	}

	.lab-dashboard-page .method-bar-fill {
		background-color: var(--color-primary);
	}

	.lab-dashboard-page .smart-table th {
		background: #f8fafc;
	}

	.lab-dashboard-page .bento-card {
		border: 1px solid var(--color-border);
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
	}
</style>
