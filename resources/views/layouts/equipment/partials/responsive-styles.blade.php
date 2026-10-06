{{--
  Equipment module mobile / tablet responsivity.
--}}
<style>
	.equipment-page,
	.equipment-detail-page {
		max-width: 100%;
	}

	@media (max-width: 991.98px) {
		#main-container-body.equipment-page .page-header,
		#main-container-body.equipment-page .d-flex.justify-content-between,
		.equipment-page .card-header.d-flex {
			flex-direction: column;
			align-items: stretch !important;
			gap: 0.5rem;
		}

		.equipment-page .btn,
		.equipment-detail-page .btn {
			min-height: var(--touch-min, 44px);
		}

		.equipment-page .form-control,
		.equipment-detail-page .form-control {
			min-height: var(--touch-min, 44px);
			font-size: var(--touch-input-font, 16px) !important;
		}

		.equipment-page .table-responsive,
		.equipment-detail-page .table-responsive {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.equipment-page .nav-tabs,
		.equipment-detail-page .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.equipment-page .nav-tabs .nav-link,
		.equipment-detail-page .nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
		}

		.equipment-detail-page .row > [class*="col-md-"],
		.equipment-detail-page .row > [class*="col-lg-"] {
			min-width: 0;
		}
	}
</style>
