<style>
	.batch-header-bar {
		background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
		border: none;
		border-radius: 12px;
		box-shadow: 0 4px 14px var(--color-primary-shadow);
		overflow: visible;
	}

	.workflow-board-header .batch-header-bar {
		padding: 18px 22px 14px;
		margin-bottom: 0;
		overflow: visible;
	}

	.workflow-board-header .batch-header-top {
		overflow: visible;
		position: relative;
		z-index: 20;
	}

	.workflow-board-header .workflow-header-actions {
		position: relative;
		z-index: 30;
	}

	.batch-stage-pill {
		border-radius: var(--radius-pill);
		padding: 3px 12px;
		font-size: var(--text-caption);
		font-weight: var(--font-semibold);
	}

	.workflow-board-header .batch-code-label,
	.submission-requests-hero .batch-code-label,
	.batch-show-page .batch-code-label {
		color: #fff;
		font-size: var(--text-xl);
		font-weight: var(--font-bold);
	}

	.workflow-board-header .batch-title-group > .mdi,
	.batch-show-page .batch-title-group > .mdi {
		color: rgba(255, 255, 255, 0.85) !important;
	}

	.workflow-board-header .batch-stage-pill,
	.request-view-page .batch-stage-pill,
	.submission-requests-hero .batch-stage-pill,
	.batch-show-page .batch-stage-pill {
		background: rgba(255, 255, 255, 0.15);
		color: #fff;
		border: 1px solid rgba(255, 255, 255, 0.35);
	}

	.workflow-board-header .btn-outline-secondary.btn-action-sm,
	.workflow-board-header .btn-outline-primary.btn-action-sm,
	.request-view-page .batch-header-actions .btn-outline-secondary,
	.submission-requests-hero .batch-header-actions .btn-outline-secondary,
	.submission-requests-hero .batch-header-actions .btn-outline-primary,
	.batch-show-page .batch-header-actions .btn-outline-secondary,
	.batch-show-page .batch-header-actions .btn-outline-info {
		background: #fff;
		border-color: #fff;
		color: var(--color-text);
		font-weight: 600;
		border-radius: 8px;
	}

	.batch-show-page .batch-header-actions .btn-outline-info {
		color: var(--color-info) !important;
	}

	.batch-show-page .batch-header-actions .btn-outline-secondary:hover,
	.batch-show-page .batch-header-actions .btn-outline-info:hover {
		background: #f8fafc;
		border-color: #f8fafc;
	}

	.batch-show-page .batch-header-actions .btn-outline-secondary:hover {
		color: var(--color-primary);
	}

	.batch-show-page .batch-header-actions .btn-outline-info:hover {
		color: var(--color-info) !important;
	}

	.workflow-board-header .btn-outline-secondary.btn-action-sm:hover,
	.workflow-board-header .btn-outline-primary.btn-action-sm:hover,
	.request-view-page .batch-header-actions .btn-outline-secondary:hover,
	.submission-requests-hero .batch-header-actions .btn-outline-secondary:hover,
	.submission-requests-hero .batch-header-actions .btn-outline-primary:hover {
		background: #f8fafc;
		border-color: #f8fafc;
		color: var(--color-primary);
	}

	.workflow-board-header .btn-outline-secondary.btn-action-sm:focus,
	.workflow-board-header .btn-outline-primary.btn-action-sm:focus,
	.workflow-board-header .btn-outline-secondary.btn-action-sm:active,
	.workflow-board-header .btn-outline-primary.btn-action-sm:active,
	.workflow-board-header .btn-outline-secondary.btn-action-sm.show,
	.workflow-board-header .btn-outline-primary.btn-action-sm.show {
		background: #f8fafc !important;
		border-color: #f8fafc !important;
		color: var(--color-primary) !important;
		box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.35) !important;
	}

	.workflow-board-header .workflow-header-actions .btn-group {
		position: relative;
	}

	.workflow-board-header .workflow-header-actions .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		z-index: 1050;
	}

	.submission-requests-hero .batch-header-actions .btn-primary {
		background: #fff;
		border-color: #fff;
		color: var(--color-primary);
	}

	.request-view-page .batch-header-bar {
		padding: 18px 22px;
		overflow: visible;
	}

	.request-view-page .batch-header-top {
		overflow: visible;
		position: relative;
		z-index: 20;
	}

	.request-view-page .batch-header-actions {
		position: relative;
		z-index: 30;
	}

	.request-view-page .request-view-title {
		color: #fff;
	}

	.request-view-page .request-view-form-name,
	.request-view-page .request-view-meta .text-muted {
		color: rgba(255, 255, 255, 0.82) !important;
	}

	.batch-show-page .batch-header-bar {
		padding: 14px 20px 0 20px;
		overflow: visible;
	}

	.batch-show-page .batch-header-top {
		overflow: visible;
		position: relative;
		z-index: 20;
	}

	.batch-show-page .batch-header-actions {
		position: relative;
		z-index: 30;
		gap: 6px;
	}

	.batch-show-page .batch-header-actions .btn-group {
		position: relative;
	}

	.batch-show-page .batch-header-actions .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		float: none;
		margin-top: 0.35rem;
		min-width: 14rem;
		max-width: 20rem;
		max-height: min(70vh, 480px);
		overflow-y: auto;
		padding: 0.35rem 0;
		border: 1px solid var(--color-border);
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
		z-index: 1050;
	}

	.batch-show-page .batch-header-actions .dropdown-item,
	.batch-show-page .batch-header-actions .dropdown-menu .btn-link {
		display: flex;
		align-items: center;
		width: 100%;
		padding: 0.5rem 0.95rem;
		font-size: var(--text-sm);
		font-weight: var(--font-medium);
		line-height: 1.35;
		color: var(--color-text);
		border: none;
		border-radius: 0;
		background: transparent;
		text-align: left;
		white-space: nowrap;
		text-decoration: none;
	}

	.batch-show-page .batch-header-actions .dropdown-item:hover,
	.batch-show-page .batch-header-actions .dropdown-item:focus,
	.batch-show-page .batch-header-actions .dropdown-menu .btn-link:hover,
	.batch-show-page .batch-header-actions .dropdown-menu .btn-link:focus {
		background: var(--color-light-gray);
		color: var(--color-text);
		text-decoration: none;
	}

	.batch-show-page .batch-header-actions .dropdown-item:active,
	.batch-show-page .batch-header-actions .dropdown-menu .btn-link:active {
		background: #e5e7eb;
		color: var(--color-text);
	}

	.batch-show-page .batch-header-actions .btn-outline-secondary.dropdown-toggle:focus,
	.batch-show-page .batch-header-actions .btn-outline-secondary.dropdown-toggle:active,
	.batch-show-page .batch-header-actions .btn-outline-secondary.dropdown-toggle.show,
	.batch-show-page .batch-header-actions .btn-outline-info.dropdown-toggle:focus,
	.batch-show-page .batch-header-actions .btn-outline-info.dropdown-toggle:active {
		background: #f8fafc !important;
		border-color: #f8fafc !important;
		box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.35) !important;
	}

	.batch-show-page .batch-priority-pill {
		background: rgba(255, 255, 255, 0.12) !important;
		color: #fff !important;
		border: 1px solid rgba(255, 255, 255, 0.35) !important;
		border-radius: 20px;
		padding: 3px 12px;
	}

	.batch-show-page .batch-meta-bar {
		border-top-color: rgba(255, 255, 255, 0.2);
		padding: 8px 0 12px;
	}

	.batch-date-pill,
	.batch-show-page .batch-date-pill {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		border-radius: 20px;
		padding: 3px 10px;
		font-size: var(--text-caption);
		background: rgba(255, 255, 255, 0.12);
		border: 1px solid rgba(255, 255, 255, 0.25);
		color: rgba(255, 255, 255, 0.9);
	}

	.batch-date-pill .pill-label,
	.batch-show-page .batch-date-pill .pill-label {
		color: rgba(255, 255, 255, 0.7);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		font-size: var(--text-caption);
		letter-spacing: 0.04em;
	}

	.batch-date-pill .pill-val,
	.batch-show-page .batch-date-pill .pill-val {
		color: #fff;
		font-weight: var(--font-semibold);
		font-size: var(--text-caption);
	}

	.batch-show-page .batch-meta-bar .text-muted {
		color: rgba(255, 255, 255, 0.75) !important;
	}

	.batch-show-page .batch-meta-bar .meta-divider {
		color: rgba(255, 255, 255, 0.4);
	}

	.batch-show-page .batch-related-pill {
		background: rgba(255, 255, 255, 0.15);
		border: 1px solid rgba(255, 255, 255, 0.3);
		border-radius: 20px;
		padding: 3px 10px;
		color: #fff;
	}

	.batch-show-page .batch-show-shell {
		border: 1px solid var(--color-border);
		border-radius: 12px;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
		background: var(--color-surface);
	}

	.batch-show-page .nav-tabs .nav-link.active {
		color: var(--color-primary);
		border-bottom-color: var(--color-primary);
	}

	.workflow-header-actions .workflow-actions-dropdown > .dropdown-menu {
		border-radius: 10px;
		border-color: var(--color-border);
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-item:hover,
	.workflow-header-actions .workflow-actions-dropdown .dropdown-item:focus {
		background: var(--color-primary-soft);
		color: var(--color-primary);
	}
</style>
