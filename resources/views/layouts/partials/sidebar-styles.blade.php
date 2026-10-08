<style>
	/*
		Primary-nav sidebar chrome — unified across every module to match the
		Lab module's flat-glass treatment (see layouts/lab/partials/lab-chrome-slice1-styles.blade.php):
		mixed-depth brand background, glass hover/active rows with a single
		accent bar, no scale/rotate/shimmer theatrics.
	*/
	#sidebar-container {
		background:
			linear-gradient(180deg, color-mix(in srgb, var(--sys-sidebar-blue-soft) 10%, transparent) 0%, transparent 32%),
			var(--sys-sidebar-bg-mixed) !important;
	}

	.copyright-lims {
		background:
			linear-gradient(0deg, rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.18)),
			var(--sys-sidebar-bg-mixed) !important;
		color: var(--sys-sidebar-text-muted) !important;
		border: none !important;
		border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
	}

	.copyright-lims span.text-red {
		color: #ffffff !important;
		font-weight: bold;
	}

	.sidebar-module-div {
		position: sticky;
		top: 0;
		z-index: 3;
		background: linear-gradient(135deg, var(--sys-sidebar-glass) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
		border: 1px solid rgba(255, 255, 255, 0.22) !important;
		box-shadow:
			inset 3px 0 0 0 var(--sys-sidebar-accent),
			inset 0 1px 0 rgba(255, 255, 255, 0.2),
			0 4px 14px rgba(0, 0, 0, 0.2);
		color: var(--sys-sidebar-text) !important;
	}

	.sidebar-module-div i,
	.sidebar-module-div:hover i {
		color: #ffffff !important;
		text-shadow: 0 0 20px rgba(255, 255, 255, 0.45) !important;
	}

	.sidebar-module-div span {
		color: #ffffff !important;
		font-size: var(--text-sidebar) !important;
	}

	#sidebar-container .list-group a {
		height: 50px;
		color: rgba(255, 255, 255, 0.88) !important;
		margin: 2px 0;
		border-radius: 8px;
		background: transparent !important;
		border: 1px solid transparent !important;
		transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
		position: relative;
		overflow: hidden;
	}

	/* Menu labels — explicit size/weight so they read clearly against the
	   burgundy chrome instead of inheriting a thin, ambiguous default. */
	#sidebar-container .list-group .menu-collapsed {
		font-size: var(--text-sidebar);
		font-weight: var(--font-medium);
		letter-spacing: 0.01em;
		color: inherit;
	}

	#sidebar-container .list-group > .list-group-item.sidebar-module-div .menu-collapsed,
	#sidebar-container .list-group > div.sidebar-module-div span.text-bold {
		font-weight: var(--font-semibold);
	}

	#sidebar-container .list-group > a[aria-expanded] .mdi,
	#sidebar-container .list-group > a[aria-expanded] .fa {
		color: #ffffff !important;
	}

	#sidebar-container .list-group > a[aria-expanded] .submenu-icon {
		color: #ffffff !important;
	}

	#sidebar-container .list-group a:hover,
	#sidebar-container .list-group a.active,
	#sidebar-container .list-group a[aria-expanded="true"] {
		background: linear-gradient(135deg, var(--sys-sidebar-glass) 0%, rgba(255, 255, 255, 0.04) 100%) !important;
		box-shadow: inset 3px 0 0 0 var(--sys-sidebar-accent), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
		border: 1px solid rgba(255, 255, 255, 0.16) !important;
		color: #ffffff !important;
	}

	#sidebar-container .list-group .sidebar-submenu a {
		height: 42px;
		margin: 2px 0 2px 20px;
		border-radius: 8px;
		background: transparent !important;
		border: 1px solid transparent !important;
		transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
		position: relative;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: space-between;
		color: rgba(255, 255, 255, 0.8) !important;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover,
	#sidebar-container .list-group .sidebar-submenu a.active {
		background: var(--sys-sidebar-glass) !important;
		border-color: rgba(255, 255, 255, 0.16) !important;
		color: #ffffff !important;
		box-shadow: inset 3px 0 0 0 var(--sys-sidebar-accent) !important;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover .mdi,
	#sidebar-container .list-group .sidebar-submenu a.active .mdi {
		color: #ffffff !important;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover span,
	#sidebar-container .list-group .sidebar-submenu a.active span {
		color: #ffffff !important;
		font-weight: var(--font-semibold);
	}

	#sidebar-container .list-group a:hover span,
	#sidebar-container .list-group a.active span {
		font-weight: var(--font-semibold);
	}

	#sidebar-container:hover::-webkit-scrollbar-thumb {
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.35) 0%, rgba(200, 200, 200, 0.25) 100%);
	}

	#sidebar-container:hover::-webkit-scrollbar-thumb:hover {
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(200, 200, 200, 0.35) 100%);
	}

	.sidebar-separator-title,
	.sidebar-separator,
	.logo-separator {
		background-color: rgba(255, 255, 255, 0.06);
	}
</style>
