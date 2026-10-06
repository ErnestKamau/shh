{{--
  CRM staff + client dashboard mobile / tablet responsivity.
  Sidebars already use d-lg-block (PR0).
--}}
<style>
	.crm-page,
	.crm-dashboard-page,
	.v2-customer-show,
	.complaint-workflow-page {
		max-width: 100%;
	}

	@media (max-width: 991.98px) {
		#main-container-body .crm-page .page-header,
		#main-container-body .v2-customer-show .d-flex.justify-content-between,
		#main-container-body .complaint-workflow-page .workflow-board-panel-header {
			flex-direction: column;
			align-items: stretch !important;
			gap: 0.5rem;
		}

		#main-container-body .crm-page .btn,
		#main-container-body .v2-customer-show .btn,
		#main-container-body .complaint-workflow-page .btn {
			min-height: var(--touch-min, 44px);
		}

		#main-container-body .crm-page .form-control,
		#main-container-body .v2-customer-show .form-control {
			min-height: var(--touch-min, 44px);
			font-size: var(--touch-input-font, 16px) !important;
		}

		#main-container-body .crm-page .table-responsive,
		#main-container-body .v2-customer-show .table-responsive,
		#main-container-body .complaint-workflow-page .table-responsive {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		#main-container-body .crm-page .nav-tabs,
		#main-container-body .v2-customer-show .nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		#main-container-body .crm-page .nav-tabs .nav-link,
		#main-container-body .v2-customer-show .nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
		}
	}
</style>
