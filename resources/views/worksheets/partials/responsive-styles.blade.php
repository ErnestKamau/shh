{{--
  Worksheets: tablet-first specialty grids.
  Portrait phones navigate + scroll; plate entry is best on tablet landscape.
--}}
<style>
	.worksheets-page,
	.fws-card,
	.formula-worksheet,
	.worksheet-executor {
		max-width: 100%;
	}

	@media (max-width: 991.98px) {
		.worksheets-page {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		.worksheets-page .batch-header-top,
		.worksheets-page .batch-header-actions,
		.worksheets-page .ws-panel-header,
		.fws-card-header {
			flex-direction: column;
			align-items: stretch !important;
			gap: 0.5rem;
		}

		.worksheets-page .batch-header-actions {
			width: 100%;
		}

		.worksheets-page .batch-header-actions .btn,
		.worksheets-page .ws-panel-header .btn {
			min-height: var(--touch-min, 44px);
		}

		.worksheets-page .table-responsive,
		.fws-card .table-responsive,
		.worksheet-executor .table-responsive {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			max-width: 100%;
		}

		.worksheets-page .nav-pills,
		.worksheets-page .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.worksheets-page .nav-pills .nav-link,
		.worksheets-page .nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
		}

		.worksheets-page .form-control,
		.worksheets-page .form-control-sm,
		.fws-input {
			min-height: var(--touch-min, 44px);
			font-size: var(--touch-input-font, 16px) !important;
		}

		/* Freeze toolbar while scrolling plate/matrix grids */
		.worksheets-page .ws-panel-header,
		.fws-card-header {
			position: sticky;
			top: 0;
			z-index: 4;
			background: #fff;
		}
	}

	@media (max-width: 767.98px) {
		.worksheets-page::before {
			content: "Tip: rotate to landscape for plate and matrix entry.";
			display: block;
			margin: 0 0 0.65rem;
			padding: 0.55rem 0.75rem;
			border: 1px solid #dbeafe;
			border-radius: 8px;
			background: #eff6ff;
			color: #1e3a8a;
			font-size: 0.78rem;
			font-weight: 600;
		}

		.worksheets-page .batch-title-group h3,
		.worksheets-page .batch-title-group h4 {
			font-size: 1.05rem;
		}
	}
</style>
