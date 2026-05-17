<style type="text/css">
	.lab-panel-theme .breadcrumb-container {
		margin-left: 0 !important;
		margin-right: 0 !important;
		margin-top: 12px;
		margin-bottom: 12px;
	}

	/* Original palette as left accent only (no fill) */
	.lab-panel-theme .formulars-stat-panel {
		margin-bottom: 0;
		border: 1px solid var(--workflow-border) !important;
		border-left: 4px solid var(--formulars-accent, #64748b) !important;
	}
	.lab-panel-theme .formulars-stat-panel .workflow-board-panel-body {
		padding: 1rem 1.25rem;
	}
	.lab-panel-theme .formulars-stat-panel.formulars-accent-success { --formulars-accent: #28a745; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-purple { --formulars-accent: #6f42c1; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-info { --formulars-accent: #17a2b8; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-warning { --formulars-accent: #ffc107; }
	.lab-panel-theme .formulars-stat-number {
		font-size: 1.35rem;
		font-weight: 700;
		color: #334155;
		line-height: 1.2;
	}
	.lab-panel-theme .formulars-stat-label {
		font-size: 0.72rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #64748b;
		margin-top: 0.35rem;
	}
	.lab-panel-theme .formulars-stat-icon {
		font-size: 1.75rem;
		margin-top: 0.5rem;
		color: var(--formulars-accent, #94a3b8);
		opacity: 0.9;
	}
	/* Link tiles: neutral card frame; color only on icons */
	.lab-panel-theme .formulars-module-tile {
		display: block;
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 1.35rem 1rem;
		text-align: center;
		text-decoration: none !important;
		color: inherit;
		height: 100%;
		transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
	}
	.lab-panel-theme .formulars-module-tile.tile-accent-primary { --tile-accent: #3b5fc0; }
	.lab-panel-theme .formulars-module-tile.tile-accent-success { --tile-accent: #28a745; }
	.lab-panel-theme .formulars-module-tile.tile-accent-info { --tile-accent: #17a2b8; }
	.lab-panel-theme .formulars-module-tile.tile-accent-purple { --tile-accent: #6f42c1; }
	.lab-panel-theme .formulars-module-tile.tile-accent-warning { --tile-accent: #ffc107; }
	.lab-panel-theme .formulars-module-tile:hover {
		border-color: #dfe3e8;
		transform: translateY(-2px);
		box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
	}
	.lab-panel-theme .formulars-module-tile .tile-icon {
		font-size: 2.5rem;
		color: var(--tile-accent, var(--workflow-accent));
		margin-bottom: 0.75rem;
		display: block;
		opacity: 0.92;
	}
	.lab-panel-theme .formulars-module-tile .tile-title {
		font-weight: 600;
		color: #334155;
		font-size: 0.95rem;
		margin-bottom: 0.35rem;
	}
	.lab-panel-theme .formulars-module-tile .tile-desc {
		font-size: 0.82rem;
		color: #64748b;
		margin-bottom: 0;
		line-height: 1.35;
	}
</style>
