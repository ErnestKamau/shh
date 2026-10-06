{{--
  Shared mobile / tablet shell responsivity for every module that extends layouts.app.
  Breakpoints follow Bootstrap: sm 576 / md 768 / lg 992.
  Below lg the sidebar is an off-canvas overlay; content must stay full-bleed and wrap.
--}}
<style>
	/* Touch / density tokens consumed by module pages */
	:root {
		--touch-min: 44px;
		--touch-input-font: 16px;
	}

	#main-container-body,
	#main-container-body main,
	.lab-surface-theme,
	.lab-panel-theme,
	.workflow-board-page,
	.request-view-page,
	.sample-workflow-responsive,
	.inventory-page,
	.worksheets-page,
	.crm-page,
	.equipment-page {
		max-width: 100%;
	}

	#main-container-body img {
		max-width: 100%;
		height: auto;
	}

	@media (max-width: 991.98px) {
		:root {
			--control-h: var(--touch-min);
			--btn-h: var(--touch-min);
			--page-pad-x: 0.75rem;
		}

		/* Global touch targets */
		#main-container-body .btn,
		#main-container-body .btn-sm,
		#main-container-body .btn-action-sm,
		#main-container-body .rm-act-btn {
			min-height: var(--touch-min);
		}

		#main-container-body .rm-act-btn {
			min-width: var(--touch-min);
		}

		#main-container-body .form-control,
		#main-container-body .custom-select,
		#main-container-body select.form-control,
		#main-container-body .acc-input,
		#main-container-body textarea.form-control {
			min-height: var(--touch-min);
			font-size: var(--touch-input-font) !important;
		}

		#main-container-body .select2-container {
			width: 100% !important;
			max-width: 100%;
		}

		#main-container-body .select2-container--default .select2-selection--single {
			min-height: var(--touch-min);
		}

		#main-container-body .select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: calc(var(--touch-min) - 2px);
		}

		#main-container-body .select2-container--default .select2-selection--single .select2-selection__arrow {
			height: calc(var(--touch-min) - 2px);
		}

		/* Headers / action bars stack */
		#main-container-body .page-header,
		#main-container-body .batch-header-top,
		#main-container-body .workflow-header-actions,
		#main-container-body .workflow-board-panel-header,
		#main-container-body .workflow-panel-header-row,
		#main-container-body .inventory-page-header,
		#main-container-body .rv-header-top {
			flex-direction: column;
			align-items: stretch !important;
		}

		#main-container-body .workflow-header-actions,
		#main-container-body .workflow-panel-selection-actions,
		#main-container-body .rv-actions-dropdown,
		#main-container-body .page-header .btn-group,
		#main-container-body .page-header .float-right {
			width: 100%;
			float: none !important;
			justify-content: flex-start !important;
		}

		#main-container-body .workflow-header-actions .btn-group,
		#main-container-body .workflow-actions-dropdown,
		#main-container-body .rv-actions-dropdown > .btn,
		#main-container-body .page-header .btn-group > .btn {
			width: 100%;
		}

		#main-container-body .workflow-actions-dropdown > .dropdown-menu,
		#main-container-body .rv-actions-dropdown > .dropdown-menu,
		#main-container-body .dropdown-menu {
			left: 0 !important;
			right: auto;
			max-width: calc(100vw - 1.5rem);
		}

		#main-container-body .workflow-actions-dropdown > .dropdown-menu,
		#main-container-body .rv-actions-dropdown > .dropdown-menu {
			right: 0 !important;
			min-width: 0;
			max-width: none;
			width: 100%;
		}

		/* Tables scroll in-container, never blow out the page */
		#main-container-body .table-responsive,
		#main-container-body .dataTables_wrapper {
			margin-left: -0.25rem;
			margin-right: -0.25rem;
			padding-bottom: 0.25rem;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			max-width: 100%;
		}

		#main-container-body .container-fluid {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		#main-container-body .row > [class*="col-md-"],
		#main-container-body .row > [class*="col-lg-"] {
			min-width: 0;
		}

		#main-container-body .btn-group {
			flex-wrap: wrap;
		}

		/* Horizontal-scroll tab strips */
		#main-container-body .nav-tabs,
		#main-container-body .ls-tabs,
		#main-container-body .rv-tabs,
		#main-container-body .workflow-receiving-tabs {
			display: flex;
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: thin;
			gap: 0.25rem;
		}

		#main-container-body .nav-tabs .nav-link,
		#main-container-body .ls-tabs .ls-tab,
		#main-container-body .rv-tabs .rv-tab,
		#main-container-body .workflow-receiving-tab {
			white-space: nowrap;
			min-height: var(--touch-min);
			flex: 0 0 auto;
		}

		#main-container-body .modal-dialog {
			margin: 0.5rem auto;
			max-width: calc(100vw - 1rem);
		}

		#main-container-body .modal-body {
			padding: 0.85rem;
		}

		#main-container-body .dropdown-item {
			min-height: var(--touch-min);
			display: flex;
			align-items: center;
		}
	}

	@media (max-width: 767.98px) {
		:root {
			--page-pad-x: 0.5rem;
		}

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

		#main-container-body .card-body,
		#main-container-body .tab-card {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		#main-container-body h2,
		#main-container-body h3,
		#main-container-body .batch-title-group h3,
		#main-container-body .batch-title-group h4,
		#main-container-body .workflow-board-panel-header h5 {
			font-size: 1.05rem;
			line-height: 1.35;
		}

		#main-container-body .btn .btn-label-desktop {
			display: none;
		}
	}
</style>
