{{--
  Inventory module surfaces — supplements layouts.partials.responsive-shell-styles.
--}}
<style>
	.inventory-page,
	.inventory-page .workflow-board-panel {
		max-width: 100%;
	}

	.inventory-page img {
		max-width: 100%;
		height: auto;
	}

	@media (max-width: 991.98px) {
		.inventory-page .btn,
		.inventory-page .btn-action-sm,
		.inventory-page .rm-act-btn {
			min-height: var(--touch-min, 44px);
		}

		.inventory-page .form-control,
		.inventory-page .custom-select,
		.inventory-page select.form-control {
			min-height: var(--touch-min, 44px);
			font-size: var(--touch-input-font, 16px);
		}

		.inventory-page .workflow-board-panel-header,
		.inventory-page .inventory-page-header,
		.inventory-page .inventory-dash-hero {
			flex-direction: column;
			align-items: stretch !important;
		}

		.inventory-page .workflow-board-panel-header .btn-group,
		.inventory-page .inventory-page-header .float-right,
		.inventory-page .inventory-page-header > .btn {
			float: none !important;
			width: 100%;
			margin-top: 0.35rem;
		}

		.inventory-page .table-responsive {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.inventory-page .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.inventory-page .nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
		}

		.inventory-page h2.p-4,
		.inventory-page h3.p-4 {
			padding-left: 0 !important;
			padding-right: 0 !important;
		}

		.inventory-dash-grid {
			grid-template-columns: 1fr;
		}

		.inventory-pipeline {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.inventory-pipeline-step {
			border-right: 1px solid var(--workflow-border, #e2e8f0);
			border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		}

		.inventory-quick-actions a {
			min-height: var(--touch-min, 44px);
		}
	}

	@media (max-width: 767.98px) {
		.inventory-pipeline {
			grid-template-columns: 1fr;
		}
	}
</style>
