{{--
  Shared mobile / tablet responsivity for Sample Workflow surfaces.
  Breakpoints follow Bootstrap: sm 576 / md 768 / lg 992.
  Below lg the lab sidebar is an off-canvas overlay; content must stay full-bleed and wrap.
--}}
<style>
	.sample-workflow-responsive,
	.workflow-board-page,
	.receive-sample-modal-body,
	.acc-wizard-root,
	.request-view-page {
		max-width: 100%;
	}

	.sample-workflow-responsive img,
	.workflow-board-page img,
	.request-view-page img {
		max-width: 100%;
		height: auto;
	}

	/* Touch-friendly controls */
	@media (max-width: 991.98px) {
		/* RFT controls override the compact desktop theme on touch devices. */
		.rft-theme .btn,
		.rft-theme .btn.btn-sm,
		.rft-theme .btn-action-sm,
		.rft-theme .form-control,
		.rft-theme .custom-select,
		.rft-theme select.form-control,
		.rft-theme .select2-container .select2-selection {
			min-height: 44px;
		}

		.rft-theme .form-control,
		.rft-theme .custom-select,
		.rft-theme select.form-control {
			font-size: 16px !important;
		}

		.rft-theme .select2-container {
			width: 100% !important;
			max-width: 100%;
		}

		.rft-theme .select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: 42px;
		}

		.rft-theme .select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 42px;
		}

		.workflow-board-page .btn,
		.workflow-board-page .btn-action-sm,
		.workflow-board-page .rm-act-btn,
		.receive-sample-modal-body .btn,
		.acc-wizard-root .btn,
		.request-view-page .btn {
			min-height: 44px;
		}

		.workflow-board-page .rm-act-btn.equipment-action-btn,
		.workflow-board-page .rm-act-btn {
			min-width: 44px;
			min-height: 44px;
		}

		.workflow-board-page .form-control,
		.workflow-board-page .custom-select,
		.receive-sample-modal-body .form-control,
		.acc-wizard-root .form-control,
		.acc-wizard-root .acc-input,
		.request-view-page .form-control {
			min-height: 44px;
			font-size: 16px; /* prevent iOS zoom on focus */
		}

		.workflow-board-header .batch-header-top,
		.workflow-board-header .workflow-header-actions,
		.workflow-board-panel-header,
		.workflow-board-panel-header .workflow-panel-header-row,
		.request-view-page .rv-header-top {
			flex-direction: column;
			align-items: stretch !important;
		}

		.workflow-board-header .workflow-header-actions,
		.workflow-board-panel-header .workflow-panel-selection-actions,
		.request-view-page .rv-actions-dropdown {
			width: 100%;
			justify-content: flex-start !important;
		}

		.workflow-board-header .workflow-header-actions .btn-group,
		.workflow-board-header .workflow-actions-dropdown,
		.request-view-page .rv-actions-dropdown > .btn {
			width: 100%;
		}

		.workflow-board-header .workflow-header-actions .btn-group > .btn,
		.workflow-board-header .workflow-actions-dropdown > .btn {
			flex: 1 1 auto;
		}

		.workflow-board-header .workflow-actions-dropdown > .dropdown-menu,
		.request-view-page .rv-actions-dropdown > .dropdown-menu {
			left: 0 !important;
			right: 0 !important;
			min-width: 0;
			max-width: none;
			width: 100%;
		}

		.workflow-board-page .workflow-filters-primary-row,
		.workflow-board-page .workflow-receiving-tabs {
			display: flex;
			flex-wrap: wrap;
			gap: 0.5rem;
		}

		.workflow-board-page .workflow-receiving-tab {
			flex: 1 1 auto;
			justify-content: center;
			min-height: 44px;
		}

		.workflow-board-page .table-responsive,
		.request-view-page .table-responsive,
		#main-container-body .table-responsive {
			margin-left: -0.25rem;
			margin-right: -0.25rem;
			padding-bottom: 0.25rem;
			-webkit-overflow-scrolling: touch;
			max-width: 100%;
		}

		.workflow-board-page .container-fluid,
		.request-view-page .container-fluid {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		.workflow-board-page .form-summary-cell {
			min-width: 0;
		}

		.workflow-board-page .modal-dialog,
		#main-container-body .modal-dialog {
			margin: 0.5rem auto;
			max-width: calc(100vw - 1rem);
		}

		.workflow-board-page .modal-body {
			padding: 0.85rem;
		}

		/* Batch detail / legacy sample-workflow forms */
		#main-container-body .row > [class*="col-md-"],
		#main-container-body .row > [class*="col-lg-"] {
			min-width: 0;
		}

		#main-container-body .btn-group {
			flex-wrap: wrap;
		}

		#main-container-body .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: thin;
		}

		#main-container-body .nav-tabs .nav-link {
			white-space: nowrap;
		}
	}

	@media (max-width: 767.98px) {
		/* Keep only the useful end of long breadcrumbs on narrow screens. */
		#main-container-body > main > .breadcrumb-container .breadcrumb-modern {
			width: 100%;
			justify-content: flex-start;
		}

		#main-container-body > main > .breadcrumb-container .breadcrumb-item-modern {
			display: none;
		}

		#main-container-body > main > .breadcrumb-container .breadcrumb-item-modern:nth-last-child(-n + 2) {
			display: flex;
			min-width: 0;
		}

		#main-container-body > main > .breadcrumb-container .breadcrumb-link,
		#main-container-body > main > .breadcrumb-container .breadcrumb-current {
			min-width: 0;
			padding: 6px 8px;
		}

		#main-container-body > main > .breadcrumb-container .breadcrumb-text {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		#main-container-body > main > .breadcrumb-container .breadcrumb-item-modern:not(:last-child)::after {
			flex: 0 0 auto;
			margin: 0 4px;
		}

		.rft-page-shell {
			padding-left: 0.5rem !important;
			padding-right: 0.5rem !important;
		}

		.rft-theme .workflow-board-panel-header,
		.rft-theme .workflow-board-panel-body {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		.rft-theme .submission-instance-actions {
			width: 100%;
			margin-left: 0 !important;
			margin-top: 0.75rem;
		}

		.rft-theme .submission-instance-actions .rft-wizard-nav {
			width: 100%;
			flex-wrap: nowrap !important;
		}

		.rft-theme .submission-instance-actions .rft-wizard-nav .btn {
			flex: 1 1 0;
			white-space: nowrap;
		}

		.workflow-board-page .batch-title-group h3,
		.workflow-board-page .batch-title-group h4,
		.workflow-board-panel-header h5 {
			font-size: 1.05rem;
			line-height: 1.35;
		}

		.workflow-board-page .btn .btn-label-desktop {
			display: none;
		}

		.workflow-board-page .dropdown-item,
		.request-view-page .dropdown-item {
			min-height: 44px;
			display: flex;
			align-items: center;
		}

		#main-container-body .card-body,
		#main-container-body .tab-card {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}
	}

	/* Wide data tables: horizontal scroll, never blow out the page */
	.receive-sample-modal-body .walk-in-trf-rows-table,
	.receive-sample-modal-body .table-responsive,
	.acc-wizard-root .table-responsive,
	.acc-wizard-root .acc-pricing-table-wrap,
	.acc-wizard-root .acc-pricing-table-wrap--scroll {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		max-width: 100%;
	}

	@media (max-width: 991.98px) {
		.receive-sample-modal-body .walk-in-trf-rows-grid {
			min-width: 960px;
		}

		/* PR1 — Workflow board: stack filters / KPI strip / stage tabs */
		.workflow-board-page .workflow-stat-strip {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0.5rem;
		}

		.workflow-board-page .workflow-stat-strip__item {
			min-width: 0;
		}

		.workflow-board-page .workflow-filters-primary-row {
			display: flex;
			flex-direction: column;
			align-items: stretch !important;
			gap: 0.65rem;
		}

		.workflow-board-page .workflow-filters-primary-row > [class*="col-"] {
			max-width: 100%;
			flex: 0 0 100%;
			padding-left: 0;
			padding-right: 0;
		}

		.workflow-board-page .workflow-receiving-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: thin;
			padding-bottom: 0.15rem;
		}

		.workflow-board-page .workflow-receiving-tab {
			flex: 0 0 auto;
			min-height: var(--touch-min, 44px);
		}

		.workflow-board-page .workflow-panel-selection-actions {
			width: 100%;
			display: flex;
			flex-wrap: wrap;
			gap: 0.4rem;
		}

		.workflow-board-page .workflow-panel-selection-actions .btn {
			flex: 1 1 auto;
			min-height: var(--touch-min, 44px);
		}
	}

	@media (max-width: 767.98px) {
		.workflow-board-page .workflow-stat-strip {
			grid-template-columns: 1fr;
		}
	}
</style>
