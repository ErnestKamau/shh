{{--
  Shared mobile / tablet responsivity for Billing / Quotations / Pricelists.
--}}
<style>
	.ls-quotation-shell,
	.quotation-show-page,
	.pricelist-page,
	.billing-page {
		max-width: 100%;
	}

	@media (max-width: 991.98px) {
		.quotation-show-page .ls-quotation-workspace {
			display: flex;
			flex-direction: column;
			gap: 0.85rem;
		}

		.quotation-show-page .ls-quotation-rail {
			position: static;
			width: 100%;
			max-width: 100%;
		}

		.quotation-show-page .ls-quotation-rail__card {
			margin-bottom: 0;
		}

		.quotation-show-page .quotation-lines-toolbar,
		.ls-quotation-shell .page-header,
		.ls-quotation-shell .d-flex.justify-content-between {
			flex-direction: column;
			align-items: stretch !important;
			gap: 0.5rem;
		}

		.quotation-show-page .quotation-lines-toolbar .btn,
		.ls-quotation-shell .btn-group {
			width: 100%;
		}

		.quotation-show-page .table-responsive,
		.ls-quotation-shell .table-responsive,
		.pricelist-page .table-responsive,
		main .pricelist-kpi-row {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			max-width: 100%;
		}

		.ls-quotation-shell .nav-tabs,
		.quotation-show-page .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.ls-quotation-shell .nav-tabs .nav-link,
		.quotation-show-page .nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
		}

		.pricelist-kpi-row > [class*="col-"] {
			flex: 0 0 100%;
			max-width: 100%;
		}
	}

	@media (max-width: 767.98px) {
		.quotation-show-page .ls-quotation-header-card .card-body {
			padding: 0.75rem;
		}

		.ls-quotation-workflow-modal .modal-dialog {
			margin: 0.35rem auto;
			max-width: calc(100vw - 0.7rem);
		}
	}
</style>
