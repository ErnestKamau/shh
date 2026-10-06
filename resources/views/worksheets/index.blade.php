@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
  <title>Worksheets | {{ $batch->batch_code }}</title>
  <link type="text/css" rel="stylesheet" href="{{ asset('css/method-sequences.css') }}" />
  @include('layouts.lab.partials.lab-panel-theme-styles')
  @include('layouts.lab.partials.lab-surface-theme-styles')
  @include('worksheets.partials.method-sequences-jquery-styles')
  @include('worksheets.partials.responsive-styles')
  <style>
		.worksheets-page {
			max-width: var(--ls-max-width, 1280px);
			margin-left: auto;
			margin-right: auto;
			padding: 0.75rem var(--ls-page-pad-x, 1.5rem) 1.5rem;
			font-size: var(--ls-text-base, 0.8125rem);
			color: var(--ls-color-ink, #1e293b);
		}

		.worksheets-page .breadcrumb-container {
			margin-left: 0 !important;
			margin-right: 0 !important;
			margin-bottom: 0.75rem;
		}

		.worksheets-page .batch-header-bar {
			padding: 12px 16px;
			margin-bottom: 0.85rem;
		}

		.worksheets-page .batch-header-top {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 8px;
			padding-bottom: 0;
		}

		.worksheets-page .batch-title-group {
			display: flex;
			align-items: flex-start;
			gap: 10px;
			flex-wrap: wrap;
			flex: 1 1 auto;
			min-width: 0;
		}

		.worksheets-page .batch-header-actions {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 6px;
			margin-left: auto;
			flex-shrink: 0;
		}

		.worksheets-page .batch-code-label {
			color: #fff;
			font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
			font-size: 1.05rem;
			font-weight: 700;
			margin: 0;
			line-height: 1.25;
		}

		.worksheets-page .batch-title-group > .mdi {
			color: rgba(255, 255, 255, 0.85) !important;
			font-size: 1.1rem;
		}

		.worksheets-page .batch-header-subtitle {
			color: rgba(255, 255, 255, 0.82);
			font-size: var(--ls-text-sm, 0.75rem);
			margin: 0.15rem 0 0;
			font-weight: 500;
		}

		.worksheets-page .batch-stage-pill {
			background: rgba(255, 255, 255, 0.15);
			color: #fff;
			border: 1px solid rgba(255, 255, 255, 0.35);
			border-radius: 999px;
			padding: 2px 8px;
			font-size: 0.7rem;
			font-weight: 600;
		}

		.worksheets-page .batch-header-actions .btn-action-sm,
		.worksheets-page .btn-action-sm {
			height: 30px;
			min-height: 30px;
			padding: 0 12px;
			font-size: 0.75rem;
			font-weight: 600;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			gap: 5px;
		}

		.worksheets-page .batch-header-actions .btn-outline-secondary.btn-action-sm,
		.worksheets-page .batch-header-actions .btn-outline-primary.btn-action-sm,
		.worksheets-page .batch-header-actions .btn-success.btn-action-sm,
		.worksheets-page .batch-header-actions .btn-success.btn-action-sm.btn-sm {
			background: #fff !important;
			border-color: #fff !important;
			color: var(--color-text, #1e293b) !important;
			box-shadow: none !important;
		}

		.worksheets-page .batch-header-actions .btn-success.btn-action-sm {
			color: var(--color-success-strong, #16a34a) !important;
		}

		.worksheets-page .batch-header-actions .btn-outline-secondary.btn-action-sm:hover,
		.worksheets-page .batch-header-actions .btn-outline-primary.btn-action-sm:hover,
		.worksheets-page .batch-header-actions .btn-success.btn-action-sm:hover {
			background: #f8fafc !important;
			border-color: #f8fafc !important;
			color: var(--color-primary, #800000) !important;
		}

		.worksheets-page .workflow-board-panel,
		.worksheets-page .ws-panel {
			background: #fff;
			border: 1px solid var(--ls-color-border, #e2e8f0);
			border-radius: var(--ls-radius-xl, 12px);
			box-shadow: var(--ls-shadow-md, 0 1px 3px rgb(0 0 0 / 0.08));
			margin-bottom: 0.85rem;
			overflow: hidden;
		}

		.worksheets-page .ws-panel-header {
			padding: 10px 14px 0;
			background: #fff;
			border-bottom: 1px solid var(--ls-color-border, #e2e8f0);
		}

		.worksheets-page .ws-panel-body {
			padding: 0.85rem 1rem;
		}

		.worksheets-page .ws-nav-tabs {
			display: flex;
			flex-wrap: wrap;
			align-items: flex-end;
			gap: 4px;
			padding: 0;
			margin: 0;
			list-style: none;
			border: none;
		}

		.worksheets-page .ws-nav-tabs .nav-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 8px 12px;
			margin-bottom: -1px;
			border: 1px solid transparent;
			border-radius: 6px 6px 0 0;
			color: #64748b;
			font-size: 0.8125rem;
			font-weight: 600;
			line-height: 1.25;
			background: transparent;
			text-decoration: none;
		}

		.worksheets-page .ws-nav-tabs .nav-link:hover {
			color: #334155;
			background: #f8fafc;
		}

		.worksheets-page .ws-nav-tabs .nav-link.active {
			color: var(--color-primary, #800000);
			background: #fff;
			border-color: var(--ls-color-border, #e2e8f0);
			border-bottom-color: #fff;
			box-shadow: none;
		}

		.worksheets-page .ws-nav-tabs .nav-link .badge,
		.worksheets-page .badge {
			font-size: var(--ls-text-xs, 0.6875rem);
			font-weight: 700;
			padding: 2px 6px;
			border-radius: 999px;
			vertical-align: middle;
		}

		.worksheets-page .ws-nav-tabs .nav-link.active .badge-success,
		.worksheets-page .ws-nav-tabs .nav-link .badge-success {
			background: var(--ls-color-primary-soft, #f8ecec);
			color: var(--color-primary, #800000);
			border: 1px solid var(--ls-color-primary-border, #e2b4b4);
		}

		.worksheets-page .btn,
		.worksheets-page .btn-sm {
			font-size: var(--ls-text-sm, 0.75rem);
			font-weight: 600;
			border-radius: var(--ls-radius-sm, 6px);
		}

		.worksheets-page .btn:not(.btn-sm):not(.btn-lg):not(.create-run-btn) {
			min-height: var(--ls-btn-h, 34px);
			padding: 0.35rem 0.85rem;
		}

		.worksheets-page .btn-sm {
			min-height: var(--ls-btn-h-sm, 30px);
			padding: 0.3rem 0.65rem;
		}

		.worksheets-page .form-control,
		.worksheets-page .form-control-sm {
			min-height: var(--ls-control-h, 34px);
			font-size: var(--ls-text-base, 0.8125rem);
			border-radius: var(--ls-radius-sm, 6px);
		}

		.worksheets-page .nav-pills .nav-link {
			padding: 0.35rem 0.75rem;
			font-size: var(--ls-text-sm, 0.75rem);
			font-weight: 600;
			border-radius: var(--ls-radius-md, 8px);
		}

		.worksheets-page .card {
			border-radius: var(--ls-radius-xl, 12px) !important;
			box-shadow: var(--ls-shadow-sm, 0 1px 2px rgb(0 0 0 / 0.05)) !important;
		}

		.worksheets-page .card-body.p-4 {
			padding: 0.85rem 1rem !important;
		}

		.worksheets-page .alert {
			font-size: var(--ls-text-base, 0.8125rem);
			padding: 0.55rem 0.8rem;
			border-radius: var(--ls-radius-md, 8px);
		}

		/* Method sequences densification under worksheets surface */
		.worksheets-page .sequence-info-card {
			padding: 10px 12px;
			margin-bottom: 0.85rem;
			border-radius: 8px;
		}

		.worksheets-page .create-run-btn {
			height: 34px;
			padding: 0 14px;
			font-size: 0.75rem;
			border-radius: 6px;
		}

		.worksheets-page .run-header {
			padding: 8px 12px;
			border-radius: 8px;
			box-shadow: var(--ls-shadow-sm, 0 1px 2px rgb(0 0 0 / 0.05));
		}

		.worksheets-page .run-header:hover {
			transform: none;
			box-shadow: var(--ls-shadow-md, 0 1px 3px rgb(0 0 0 / 0.08));
		}

		.worksheets-page .run-title {
			font-size: 0.8125rem;
		}

		.worksheets-page .run-sample-chip,
		.worksheets-page .sample-badge,
		.worksheets-page .step-info-badge {
			font-size: 0.6875rem;
			padding: 2px 6px;
		}

		.worksheets-page .polucon-custom-table {
			font-size: var(--ls-text-base, 0.8125rem);
		}

		.worksheets-page .polucon-custom-table th,
		.worksheets-page .polucon-custom-table td {
			padding: 0.55rem 0.7rem;
		}

		.worksheets-page .polucon-custom-table thead th {
			font-size: var(--ls-text-xs, 0.6875rem);
			text-transform: uppercase;
			letter-spacing: 0.03em;
			color: #64748b;
		}

		.worksheets-page .timer-badge {
			font-size: 0.75rem;
			padding: 3px 8px;
		}

		.worksheets-page .dropdown-menu {
			font-size: var(--ls-text-base, 0.8125rem);
			border-radius: var(--ls-radius-lg, 10px);
			padding: 0.35rem 0;
		}

		.worksheets-page .dropdown-item {
			font-size: var(--ls-text-base, 0.8125rem);
			padding: 0.5rem 0.95rem;
		}

		/* —— Worksheets modals (compact, matches stepper density) ——
		   IDs cover Bootstrap modals moved to <body>; .worksheets-page covers Livewire overlays. */
		.worksheets-page .modal .modal-content,
		#create-run-modal .modal-content,
		#edit-stage-modal .modal-content,
		#add-result-modal .modal-content,
		#post-results-modal .modal-content,
		#edit-standard-modal .modal-content {
			border-radius: 10px;
			border: 1px solid #e2e8f0;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
			font-size: 0.8125rem;
		}

		.worksheets-page .modal .modal-header,
		#create-run-modal .modal-header,
		#edit-stage-modal .modal-header,
		#add-result-modal .modal-header,
		#post-results-modal .modal-header,
		#edit-standard-modal .modal-header {
			padding: 0.65rem 0.85rem !important;
			align-items: center;
		}

		.worksheets-page .modal .modal-title,
		.worksheets-page .modal .modal-header h4,
		.worksheets-page .modal .modal-header h5,
		#create-run-modal .modal-title,
		#edit-stage-modal .modal-title,
		#add-result-modal .modal-title,
		#post-results-modal .modal-title,
		#edit-standard-modal .modal-title,
		#edit-standard-modal .modal-header h4 {
			font-size: 0.875rem !important;
			font-weight: 700 !important;
			line-height: 1.3;
			margin: 0;
		}

		.worksheets-page .modal .modal-header .close,
		#create-run-modal .modal-header .close,
		#edit-stage-modal .modal-header .close,
		#add-result-modal .modal-header .close,
		#post-results-modal .modal-header .close,
		#edit-standard-modal .modal-header .close {
			font-size: 1.25rem !important;
			padding: 0.35rem 0.5rem;
			margin: -0.25rem -0.25rem -0.25rem auto;
			line-height: 1;
		}

		.worksheets-page .modal .modal-body,
		#create-run-modal .modal-body,
		#edit-stage-modal .modal-body,
		#add-result-modal .modal-body,
		#post-results-modal .modal-body,
		#edit-standard-modal .modal-body {
			padding: 0.75rem 0.85rem !important;
			font-size: 0.8125rem;
		}

		.worksheets-page .modal .modal-footer,
		#create-run-modal .modal-footer,
		#edit-stage-modal .modal-footer,
		#add-result-modal .modal-footer,
		#post-results-modal .modal-footer,
		#edit-standard-modal .modal-footer {
			padding: 0.55rem 0.85rem !important;
			gap: 6px;
		}

		.worksheets-page .modal .modal-footer .btn,
		#create-run-modal .modal-footer .btn,
		#edit-stage-modal .modal-footer .btn,
		#add-result-modal .modal-footer .btn,
		#post-results-modal .modal-footer .btn,
		#edit-standard-modal .modal-footer .btn {
			min-height: 30px !important;
			height: auto !important;
			padding: 0.3rem 0.75rem !important;
			font-size: 0.75rem !important;
			font-weight: 600 !important;
			border-radius: 6px !important;
			line-height: 1.2 !important;
		}

		.worksheets-page .modal .form-group,
		#create-run-modal .form-group,
		#edit-stage-modal .form-group,
		#add-result-modal .form-group,
		#edit-standard-modal .form-group {
			margin-bottom: 0.65rem;
		}

		.worksheets-page .modal label,
		.worksheets-page .modal .form-label,
		.worksheets-page .modal .control-label,
		#create-run-modal label,
		#edit-stage-modal label,
		#add-result-modal label,
		#post-results-modal label,
		#post-results-modal .form-label,
		#edit-standard-modal label,
		#edit-standard-modal .control-label {
			font-size: 0.75rem !important;
			font-weight: 600 !important;
			margin-bottom: 0.3rem !important;
			color: #475569;
		}

		.worksheets-page .modal .form-control,
		.worksheets-page .modal .form-control-sm,
		#create-run-modal .form-control,
		#edit-stage-modal .form-control,
		#add-result-modal .form-control,
		#post-results-modal .form-control,
		#post-results-modal .form-control-sm,
		#edit-standard-modal .form-control {
			min-height: 34px;
			height: auto;
			padding: 0.35rem 0.65rem;
			font-size: 0.8125rem !important;
			border-radius: 6px;
		}

		.worksheets-page .modal textarea.form-control,
		#add-result-modal textarea.form-control {
			min-height: 64px;
		}

		.worksheets-page .modal .form-text,
		.worksheets-page .modal small,
		.worksheets-page .modal .text-muted,
		#create-run-modal small,
		#edit-stage-modal small,
		#add-result-modal small,
		#post-results-modal small,
		#edit-standard-modal small {
			font-size: 0.6875rem !important;
		}

		.worksheets-page .modal .alert,
		#create-run-modal .alert,
		#post-results-modal .alert,
		#edit-standard-modal .alert {
			padding: 0.5rem 0.65rem !important;
			font-size: 0.75rem !important;
			border-radius: 8px !important;
			margin-bottom: 0.65rem !important;
		}

		.worksheets-page .modal .alert h6,
		#create-run-modal .alert h6 {
			font-size: 0.8125rem !important;
			font-weight: 700 !important;
			margin-bottom: 0.15rem !important;
		}

		.worksheets-page .modal .table,
		#post-results-modal .table {
			font-size: 0.8125rem !important;
		}

		.worksheets-page .modal .table th,
		#post-results-modal .table thead th {
			padding: 0.4rem 0.5rem !important;
			font-size: 0.6875rem !important;
			font-weight: 600 !important;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			color: #64748b !important;
			vertical-align: middle !important;
		}

		.worksheets-page .modal .table td,
		#post-results-modal .table tbody td {
			padding: 0.4rem 0.5rem !important;
			font-size: 0.8125rem !important;
			vertical-align: middle !important;
		}

		.worksheets-page .modal .btn:not(.btn-sm):not(.modal-footer .btn),
		#create-run-modal .btn:not(.btn-sm),
		#post-results-modal .btn:not(.btn-sm) {
			min-height: 30px;
			padding: 0.3rem 0.75rem;
			font-size: 0.75rem;
			font-weight: 600;
			border-radius: 6px;
		}

		.worksheets-page .modal .btn-sm,
		#post-results-modal .btn-sm,
		#edit-standard-modal .btn-sm {
			min-height: 28px !important;
			padding: 0.2rem 0.55rem !important;
			font-size: 0.6875rem !important;
		}

		/* Create Run — remove oversized chrome */
		#create-run-modal .modal-content {
			border-radius: 10px !important;
		}

		#create-run-modal .modal-header {
			border-radius: 10px 10px 0 0 !important;
			padding: 0.65rem 0.85rem !important;
		}

		#create-run-modal .modal-header .rounded-circle {
			width: 22px !important;
			height: 22px !important;
			padding: 0 !important;
		}

		#create-run-modal .modal-header .rounded-circle i {
			font-size: 0.75rem !important;
		}

		#create-run-modal .modal-body.p-4,
		#create-run-modal .modal-footer.p-4 {
			padding: 0.75rem 0.85rem !important;
		}

		#create-run-modal .modal-footer .btn.px-4 {
			padding-left: 0.75rem !important;
			padding-right: 0.75rem !important;
		}

		#create-run-modal .alert i {
			font-size: 1.1rem !important;
		}

		#create-run-modal .select2-container .select2-selection--multiple {
			min-height: 34px !important;
			font-size: 0.8125rem !important;
		}

		/* Edit Standard Limit */
		#edit-standard-modal .esl-config-card {
			border-radius: 8px;
		}

		#edit-standard-modal .esl-config-card__header {
			padding: 0.45rem 0.65rem !important;
			font-size: 0.75rem !important;
			font-weight: 600;
		}

		#edit-standard-modal .esl-config-card__body {
			padding: 0.65rem !important;
		}

		#edit-standard-modal .form-check-label {
			font-size: 0.75rem !important;
			font-weight: 500 !important;
		}

		/* Post Results */
		#post-results-modal .post-results-modal-dialog {
			max-width: 96%;
			width: 96%;
		}

		#post-results-modal #post-results-table .form-control-sm {
			min-height: 30px;
			height: 30px;
			padding: 0.25rem 0.5rem;
			font-size: 0.75rem !important;
		}
  </style>
@endsection

@section('content2')
  <main class="container-fluid lab-panel-theme worksheets-page workflow-theme lab-surface-theme" data-ls-type="plex">
    <?php
      $items = [
        [
          'link' => route('dashboard-lab'),
          'name' => 'Dashboard',
          'icon' => null,
        ],
        [
          'link' => route('sample-workflow', ['status' => $batch->status ?? 'All Samples']),
          'name' => 'Sample Workflow',
          'icon' => null,
        ],
        [
          'link' => route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status]),
          'name' => $batch->batch_code,
          'icon' => null,
        ],
        [
          'link' => '#',
          'name' => 'Worksheets',
          'icon' => null,
        ],
      ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('worksheets.worksheet-manager', ['batch' => $batch])
  </main>
@endsection

@section('script2')
<script>
(function() {
    setInterval(function() {
        const table = $('.polucon-custom-table');
        if (table.length && $.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }
    }, 150);

    window.getMethodSequencesApi = function() {
        return window.MethodSequences || window.methodSequences || null;
    };

    window.initMethodSequencesWidget = function() {
        const ms = window.getMethodSequencesApi();
        if (!ms) {
            return false;
        }

        const container = document.getElementById('method-sequences-container');
        if (!container) {
            return false;
        }

        const tabPanel = container.closest('.method-sequences-tab-panel');
        if (tabPanel && tabPanel.classList.contains('d-none')) {
            return false;
        }

        const tabsHaveContent = document.getElementById('sequence-tabs')?.children.length > 0;
        if (ms.initialized && tabsHaveContent) {
            return true;
        }

        if (ms.initialized && !tabsHaveContent) {
            ms.initialized = false;
            ms.eventsBound = false;
            ms.editStandardModalInitialized = false;
        }

        ms.init();
        return ms.initialized === true;
    };

    window.scheduleMethodSequencesInit = function(maxAttempts) {
        const attempts = maxAttempts || 10;
        let attempt = 0;

        const tryInit = function() {
            if (window.initMethodSequencesWidget()) {
                return;
            }

            attempt++;
            if (attempt < attempts) {
                setTimeout(tryInit, 200);
            }
        };

        tryInit();
    };
})();

document.addEventListener('livewire:init', function () {
    Livewire.on('init-method-sequences', function () {
        window.scheduleMethodSequencesInit(12);
    });

    Livewire.hook('morph.updated', function () {
        if (document.getElementById('method-sequences-container')) {
            window.scheduleMethodSequencesInit(5);
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'method-sequences') {
        window.scheduleMethodSequencesInit(15);
    }
});
</script>
@endsection
