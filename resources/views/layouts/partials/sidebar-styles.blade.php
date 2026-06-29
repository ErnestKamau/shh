<style>
	@keyframes sidebarGlowShift {
		0% { background-position: 0% 50%; }
		50% { background-position: 100% 50%; }
		100% { background-position: 0% 50%; }
	}

	#sidebar-container {
		background-color: var(--color-sidebar-bg) !important;
	}

	.copyright-lims {
		background-color: rgba(0, 0, 0, 0.15) !important;
		color: var(--color-sidebar-text-muted) !important;
		border: 1px solid rgba(255, 255, 255, 0.05) !important;
	}

	.copyright-lims span.text-red {
		color: #ffffff !important;
		font-weight: bold;
	}

	.sidebar-module-div {
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
		border: 1px solid rgba(255, 255, 255, 0.2) !important;
		color: var(--color-sidebar-text) !important;
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
		color: var(--color-sidebar-text-muted) !important;
		margin: 2px 0;
		border-radius: 8px;
		background: var(--color-sidebar-link-bg) !important;
		border: 1px solid transparent !important;
		box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		position: relative;
		overflow: hidden;
	}

	#sidebar-container .list-group a::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 100%;
		height: 100%;
		background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
		transition: left 0.6s ease;
		z-index: 0;
	}

	#sidebar-container .list-group a:hover,
	#sidebar-container .list-group a.active {
		background: var(--color-sidebar-hover) !important;
		transform: translateX(5px) scale(1.02);
		box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1), 0 6px 20px rgba(255, 255, 255, 0.12) !important;
		border: 1px solid rgba(255, 255, 255, 0.35) !important;
		color: var(--color-sidebar-text) !important;
	}

	#sidebar-container .list-group a:hover::before,
	#sidebar-container .list-group a.active::before {
		left: 100%;
	}

	#sidebar-container .list-group .sidebar-submenu a {
		height: 42px;
		margin: 2px 0 2px 20px;
		border-radius: 12px;
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%) !important;
		border: 1px solid rgba(255, 255, 255, 0.1) !important;
		box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.05), inset 0 -1px 2px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		position: relative;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: space-between;
		color: var(--color-sidebar-text-muted) !important;
	}

	#sidebar-container .list-group .sidebar-submenu a::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 100%;
		height: 100%;
		background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18), transparent);
		transition: left 0.6s ease;
		z-index: 0;
	}

	#sidebar-container .list-group .sidebar-submenu a::after {
		content: '';
		position: absolute;
		left: 0;
		top: 50%;
		transform: translateY(-50%);
		width: 3px;
		height: 0;
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(200, 200, 200, 0.6));
		border-radius: 0 2px 2px 0;
		transition: all 0.3s ease;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover,
	#sidebar-container .list-group .sidebar-submenu a.active {
		background: var(--color-sidebar-hover) !important;
		transform: translateX(1px);
		border-color: rgba(255, 255, 255, 0.22) !important;
		color: #ffffff !important;
		box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.08), 0 2px 10px rgba(255, 255, 255, 0.08) !important;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover::before,
	#sidebar-container .list-group .sidebar-submenu a.active::before {
		left: 100%;
	}

	#sidebar-container .list-group .sidebar-submenu a:hover::after,
	#sidebar-container .list-group .sidebar-submenu a.active::after {
		height: 30%;
		box-shadow: 0 0 6px rgba(255, 255, 255, 0.45);
	}

	#sidebar-container .list-group .sidebar-submenu a:hover .mdi,
	#sidebar-container .list-group .sidebar-submenu a.active .mdi {
		color: #ffffff !important;
		text-shadow: 0 0 8px rgba(255, 255, 255, 0.45);
	}

	#sidebar-container .list-group .sidebar-submenu a:hover span,
	#sidebar-container .list-group .sidebar-submenu a.active span {
		color: #ffffff !important;
		text-shadow: 0 0 6px rgba(255, 255, 255, 0.35);
		font-weight: var(--font-semibold);
	}

	#sidebar-container .list-group a:hover .mdi,
	#sidebar-container .list-group a.active .mdi {
		transform: scale(1.1) rotate(5deg);
		opacity: 1;
		text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
	}

	#sidebar-container .list-group a:hover span,
	#sidebar-container .list-group a.active span {
		text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
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
