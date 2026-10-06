<style>
	:root {
		--color-primary: {{ $themeVars['primary'] }};
		--color-primary-hover: {{ $themeVars['secondary'] }};
		--color-accent: {{ $themeVars['accent'] }};
		--color-sidebar-bg: {{ $themeVars['sidebar_bg'] }};
		--color-sidebar-hover: {{ $themeVars['sidebar_hover'] ?? '#1a1a1a' }};
		--color-sidebar-link-bg: {{ $themeVars['sidebar_link_bg'] }};
		--color-sidebar-text: {{ $themeVars['sidebar_text'] ?? 'rgba(255, 255, 255, 0.95)' }};
		--color-sidebar-text-muted: {{ $themeVars['sidebar_text_muted'] ?? 'rgba(255, 255, 255, 0.6)' }};
		--color-surface: #ffffff;
		--color-border: #e2e8f0;
		--color-muted: #64748b;
		--color-text: #111827;
		--color-text-secondary: #64748b;
		--color-bg: #f8fafc;
		--color-bg-app: #f8fafc;
		--color-light-gray: #f3f4f6;
		--color-primary-soft: {{ $themeVars['primary_soft'] }};
		--color-primary-soft-10: {{ $themeVars['primary_soft_10'] }};
		--color-primary-tint: {{ $themeVars['primary_tint'] }};
		--color-primary-focus: {{ $themeVars['primary_focus'] }};
		--color-primary-border-soft: {{ $themeVars['primary_border_soft'] }};
		--color-primary-shadow: {{ $themeVars['primary_shadow'] }};
		--color-primary-soft-light: {{ $themeVars['primary_soft_light'] }};
		--color-primary-soft-medium: {{ $themeVars['primary_soft_medium'] }};
		--color-primary-highlight: {{ $themeVars['primary_highlight'] }};
		--color-primary-glow: {{ $themeVars['primary_glow'] }};
		--color-success: #22c55e;
		--color-success-strong: #16a34a;
		--color-warning: #f59e0b;
		--color-error: #dc2626;
		--color-info: var(--color-primary);
		--color-btn-secondary: #596273;
		--workflow-accent: var(--color-primary);
		--workflow-accent-soft: var(--color-primary-soft);
		--sys-primary-color: var(--color-primary);
		--sys-secondary-color: var(--color-primary-hover);
		--sys-accent-color: var(--color-accent);
		--sys-sidebar-bg: var(--color-sidebar-bg);
		--sys-sidebar-link-bg: var(--color-sidebar-link-bg);
		--sys-sidebar-text: var(--color-sidebar-text);
		--sys-sidebar-text-muted: var(--color-sidebar-text-muted);
		/* Type scale — aligned to lab-surface (Receiving / Request View / RFT) density */
		--text-xs: 0.6875rem;     /* 11px */
		--text-caption: 0.75rem;  /* 12px */
		--text-sidebar: 0.8rem;
		--text-sm: 0.8125rem;     /* 13px — default UI body (was 14px) */
		--text-md: 0.875rem;      /* 14px */
		--text-base: 0.8125rem;   /* alias of body; prefer --text-sm for UI */
		--text-lg: 0.95rem;
		--text-xl: 1.05rem;
		--text-2xl: 1.25rem;
		--text-metric: 1.5rem;
		--font-normal: 400;
		--font-medium: 500;
		--font-semibold: 600;
		--font-bold: 700;
		--leading-tight: 1.25;
		--leading-normal: 1.5;
		--leading-relaxed: 1.625;
		--space-xs: 0.5rem;
		--space-sm: 0.75rem;
		--space-md: 1rem;
		--space-lg: 1.25rem;
		--space-xl: 1.5rem;
		--radius-sm: 6px;
		--radius-md: 8px;
		--radius-lg: 10px;
		--radius-xl: 12px;
		--radius-pill: 999px;
		--control-h: 34px;
		--btn-h: 34px;
		--btn-h-sm: 30px;
		--touch-min: 44px;
		--touch-input-font: 16px;
		--page-max-width: 1280px;
		--page-pad-x: 1.5rem;
		--page-pad-x-md: 2rem;
		--card-pad-y: 0.85rem;
		--card-pad-x: 1rem;
		--table-cell-y: 0.65rem;
		--table-cell-x: 0.7rem;
		--shadow-sm: 0 1px 2px rgb(0 0 0 / 0.05);
		--shadow-md: 0 1px 3px 0 rgb(0 0 0 / 0.08), 0 1px 2px -1px rgb(0 0 0 / 0.06);
	}

	@media (min-width: 768px) {
		:root {
			--page-pad-x: var(--page-pad-x-md);
		}
	}

	html {
		font-size: 16px;
	}

	body,
	#main-container-body {
		background-color: var(--color-bg-app);
		color: var(--color-text);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
	}

	.tag-select-input:hover {
		border-color: var(--color-primary);
	}

	.tag-select-input:focus-within {
		border-color: var(--color-primary);
		box-shadow: 0 0 0 0.2rem var(--color-primary-focus);
		outline: none;
	}

	/* Soft chips — match Process Enquiry acc-param-tags (not solid primary slabs) */
	.tag-badge {
		background-color: var(--color-primary-soft);
		color: var(--color-primary);
		border: 1px solid var(--color-primary-border-soft);
	}

	.tag-dropdown {
		border: 1px solid var(--color-border);
	}

	.text-primary {
		color: var(--color-primary) !important;
	}

	.bg-primary {
		background-color: var(--color-primary) !important;
		color: #ffffff !important;
	}

	.badge-primary {
		background-color: var(--color-primary) !important;
		color: #ffffff !important;
	}

	.btn-primary {
		background-color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
		color: #ffffff !important;
	}

	.btn-primary:hover,
	.btn-primary:focus {
		background-color: var(--color-primary-hover) !important;
		border-color: var(--color-primary-hover) !important;
		color: #ffffff !important;
	}

	.nav-tabs .nav-link.active,
	.nav-pills .nav-link.active {
		color: var(--color-primary) !important;
		border-bottom: 3px solid var(--color-primary) !important;
		font-weight: bold;
	}

	.nav-tabs .nav-link:hover {
		color: var(--color-primary-hover) !important;
		border-bottom-color: var(--color-primary-border-soft) !important;
	}

	.search-btn:hover {
		background: var(--color-primary-soft) !important;
		color: var(--color-primary) !important;
	}

	.search-input-group:focus-within .search-btn::after {
		background: var(--color-primary) !important;
	}

	#toggle-main-sidebar:hover {
		color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
	}
</style>
