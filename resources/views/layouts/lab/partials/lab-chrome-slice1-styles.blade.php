{{--
	Lab Chrome Slice 1 — authorized playground snapshot.
	Included from lab layout (body) so it wins over head sidebar-styles.
--}}
<style>
	:root {
		--lab-chrome-burgundy: #6d0a0e;
		--lab-chrome-blue-accent: #8ac0ff;
		--lab-chrome-blue-soft: #eff6ff;
		--lab-chrome-sidebar-depth: 90%;
		--lab-chrome-glass: rgba(255, 255, 255, 0.16);
		--lab-chrome-sidebar-bg: color-mix(in srgb, var(--lab-chrome-burgundy) var(--lab-chrome-sidebar-depth), #0f172a);
	}

	#sidebar-container {
		display: flex !important;
		flex-direction: column !important;
		overflow: hidden !important;
		background:
			linear-gradient(180deg, color-mix(in srgb, var(--lab-chrome-blue-soft) 10%, transparent) 0%, transparent 32%),
			var(--lab-chrome-sidebar-bg) !important;
	}

	#sidebar-container .sidebar-module-div {
		position: sticky;
		top: 0;
		z-index: 3;
		isolation: isolate;
		padding: 1.275rem !important; /* p-4 (1.5rem) × 0.85 */
		background:
			linear-gradient(135deg, var(--lab-chrome-glass) 0%, rgba(255, 255, 255, 0.05) 100%),
			var(--lab-chrome-sidebar-bg) !important;
		border: 1px solid rgba(255, 255, 255, 0.22) !important;
		box-shadow:
			inset 3px 0 0 0 var(--lab-chrome-blue-accent),
			inset 0 1px 0 rgba(255, 255, 255, 0.2),
			0 4px 14px rgba(0, 0, 0, 0.2) !important;
		color: #fff !important;
	}

	#sidebar-container .sidebar-module-div i {
		font-size: 2.975rem !important; /* 3.5rem × 0.85 */
		margin-bottom: 6.8px !important; /* 8px × 0.85 */
		color: #fff !important;
	}

	#sidebar-container .sidebar-module-div span {
		font-size: calc(var(--text-sidebar) * 0.85) !important;
		color: #fff !important;
	}

	#sidebar-container .list-group > a.list-group-item,
	#sidebar-container .list-group > a.list-group-item-action {
		color: rgba(255, 255, 255, 0.78) !important;
	}

	#sidebar-container .list-group > a.list-group-item:hover,
	#sidebar-container .list-group > a.list-group-item.active,
	#sidebar-container .list-group > a.list-group-item-action:hover,
	#sidebar-container .list-group > a.list-group-item-action.active,
	#sidebar-container .list-group > a[aria-expanded="true"] {
		background: linear-gradient(135deg, var(--lab-chrome-glass) 0%, rgba(255, 255, 255, 0.04) 100%), var(--lab-chrome-sidebar-bg) !important;
		border: 1px solid rgba(255, 255, 255, 0.16) !important;
		color: #fff !important;
		box-shadow:
			inset 3px 0 0 0 var(--lab-chrome-blue-accent),
			inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
	}

	#sidebar-container .list-group > a.list-group-item:hover .mdi,
	#sidebar-container .list-group > a.list-group-item.active .mdi,
	#sidebar-container .list-group > a.list-group-item-action:hover .mdi,
	#sidebar-container .list-group > a.list-group-item-action.active .mdi,
	#sidebar-container .list-group > a[aria-expanded="true"] .mdi,
	#sidebar-container .list-group > a[aria-expanded="true"] .fa,
	#sidebar-container .list-group > a[aria-expanded="true"] .submenu-icon {
		color: #fff !important;
		text-shadow: 0 0 10px rgba(255, 255, 255, 0.35);
	}

	#sidebar-container .list-group .sidebar-submenu a:hover,
	#sidebar-container .list-group .sidebar-submenu a.active {
		background: var(--lab-chrome-glass) !important;
		color: #fff !important;
		box-shadow: inset 3px 0 0 0 var(--lab-chrome-blue-accent) !important;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover::after,
	#sidebar-container .list-group .sidebar-submenu a.active::after {
		background: var(--lab-chrome-blue-accent);
		box-shadow: 0 0 8px color-mix(in srgb, var(--lab-chrome-blue-accent) 55%, transparent);
	}

	/*
		Pin header + footer; only the nav list scrolls.
		Keep list-group items full-width / nowrap so rows stay a single column.
	*/
	#sidebar-container > ul.list-group {
		display: flex !important;
		flex-direction: column !important;
		flex-wrap: nowrap !important;
		flex: 1 1 auto;
		min-height: 0;
		overflow-y: auto;
		overflow-x: hidden;
		width: 100%;
		margin-bottom: 0 !important;
	}

	#sidebar-container > ul.list-group > a.list-group-item,
	#sidebar-container > ul.list-group > .list-group-item,
	#sidebar-container > ul.list-group > .collapse {
		width: 100%;
		max-width: 100%;
		flex: 0 0 auto;
		align-self: stretch;
	}

	#sidebar-container .lab-sidebar-foot {
		flex: 0 0 auto;
		width: 100%;
		padding: 0.3rem 0.35rem 0.35rem;
		border-top: 1px solid rgba(255, 255, 255, 0.12);
		background: linear-gradient(0deg, rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.18)), var(--lab-chrome-sidebar-bg);
		z-index: 2;
	}

	#sidebar-container .lab-sidebar-foot__actions {
		display: flex;
		align-items: center;
		gap: 0.35rem;
		margin-bottom: 0;
	}

	#sidebar-container .lab-sidebar-signout,
	#sidebar-container .lab-sidebar-switch {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 0.3rem;
		border: 1px solid rgba(255, 255, 255, 0.2);
		background: var(--lab-chrome-glass);
		color: #fff !important;
		border-radius: 7px;
		font-size: 0.7rem;
		font-weight: 600;
		padding: 0.38rem 0.55rem;
		cursor: pointer;
		transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
	}

	#sidebar-container .lab-sidebar-signout {
		flex: 1 1 auto;
	}

	#sidebar-container .lab-sidebar-switch {
		flex: 0 0 auto;
		position: relative;
		padding: 0.38rem 0.5rem;
	}

	#sidebar-container .lab-sidebar-signout:hover,
	#sidebar-container .lab-sidebar-switch:hover {
		background: color-mix(in srgb, var(--lab-chrome-blue-soft) 22%, var(--lab-chrome-glass));
		border-color: color-mix(in srgb, var(--lab-chrome-blue-accent) 45%, rgba(255, 255, 255, 0.25));
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--lab-chrome-blue-accent) 25%, transparent);
		color: #fff !important;
	}

	#sidebar-container .lab-sidebar-signout .mdi,
	#sidebar-container .lab-sidebar-switch .mdi {
		font-size: 0.95rem;
		color: #fff !important;
	}

	#sidebar-container .lab-sidebar-switch__tip {
		position: absolute;
		right: calc(100% + 0.4rem);
		top: 50%;
		transform: translateY(-50%);
		white-space: nowrap;
		padding: 0.25rem 0.45rem;
		border-radius: 6px;
		background: #0f172a;
		color: #fff;
		font-size: 0.65rem;
		font-weight: 600;
		opacity: 0;
		pointer-events: none;
		transition: opacity 0.15s ease;
	}

	#sidebar-container .lab-sidebar-switch:hover .lab-sidebar-switch__tip {
		opacity: 1;
	}

	#sidebar-container .lab-sidebar-foot__copy {
		text-align: center;
		font-size: 0.62rem;
		line-height: 1.2;
		padding: 0.2rem 0.25rem 0 !important;
		background: transparent !important;
		border: none !important;
		color: rgba(255, 255, 255, 0.55) !important;
	}

	#sidebar-container .lab-sidebar-foot__copy .text-red {
		color: #fff !important;
	}

	/* Top progress bar shown while navigating between lab-module pages, so a
	   full page reload reads as "loading" rather than a frozen/blank glitch. */
	#lab-page-loader {
		position: fixed;
		top: 0;
		left: 0;
		height: 3px;
		width: 0%;
		background: linear-gradient(90deg, var(--lab-chrome-blue-accent), var(--lab-chrome-burgundy));
		box-shadow: 0 0 8px color-mix(in srgb, var(--lab-chrome-blue-accent) 60%, transparent);
		z-index: 99999;
		opacity: 0;
		transition: width 0.25s ease, opacity 0.2s ease;
		pointer-events: none;
	}

	#lab-page-loader.is-active {
		opacity: 1;
	}
</style>
