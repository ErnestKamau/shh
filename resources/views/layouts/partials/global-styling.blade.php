<style>
	:root {
		--color-primary: {{ $themeVars['primary'] }};
		--color-primary-hover: {{ $themeVars['secondary'] }};
		--color-accent: {{ $themeVars['accent'] }};
		--color-sidebar-bg: {{ $themeVars['sidebar_bg'] }};
		--color-sidebar-hover: #313846;
		--color-sidebar-link-bg: {{ $themeVars['sidebar_link_bg'] }};
		--color-sidebar-text: {{ $themeVars['sidebar_text'] ?? 'rgba(255, 255, 255, 0.95)' }};
		--color-sidebar-text-muted: {{ $themeVars['sidebar_text_muted'] ?? 'rgba(255, 255, 255, 0.6)' }};
		--color-surface: #ffffff;
		--color-border: #e5e7eb;
		--color-muted: #6b7280;
		--color-text: #111827;
		--color-text-secondary: #6b7280;
		--color-bg: #f8fafc;
		--color-bg-app: #f7f8fa;
		--color-light-gray: #f3f4f6;
		--color-primary-soft: rgba(109, 10, 14, 0.08);
		--color-success: #22c55e;
		--color-success-strong: #16a34a;
		--color-warning: #f59e0b;
		--color-error: #dc2626;
		--color-info: #2563eb;
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
		--text-caption: 0.75rem;
		--text-sidebar: 0.8rem;
		--text-sm: 0.875rem;
		--text-base: 1rem;
		--text-lg: 1.125rem;
		--text-xl: 1.25rem;
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
		--radius-sm: 8px;
		--radius-md: 12px;
		--radius-pill: 999px;
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
		box-shadow: 0 0 0 0.2rem rgba(109, 10, 14, 0.25);
		outline: none;
	}

	.tag-badge {
		background-color: var(--color-primary);
	}

	.tag-dropdown {
		border: 1px solid var(--color-primary);
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

	.nav-tabs .nav-link.active,
	.nav-pills .nav-link.active {
		color: var(--color-primary) !important;
		border-bottom: 3px solid var(--color-primary) !important;
		font-weight: bold;
	}

	.nav-tabs .nav-link:hover {
		color: var(--color-primary-hover) !important;
		border-bottom-color: rgba(109, 10, 14, 0.3) !important;
	}

	.search-btn:hover {
		background: rgba(109, 10, 14, 0.1) !important;
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
