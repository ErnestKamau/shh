{{--
  Shared mobile / tablet responsivity for Inventory module surfaces.
  Mirrors lab sample-workflow responsive patterns.
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
			min-height: 44px;
		}

		.inventory-page .form-control,
		.inventory-page .custom-select,
		.inventory-page select.form-control {
			min-height: 44px;
			font-size: 16px;
		}

		.inventory-page .workflow-board-panel-header,
		.inventory-page .inventory-page-header {
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

		.inventory-page h2.p-4,
		.inventory-page h3.p-4 {
			padding-left: 0 !important;
			padding-right: 0 !important;
		}
	}
</style>
