@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
  @include('layouts.lab.partials.lab-panel-theme-styles')
  {{-- Include all CSS from original show.blade.php lines 5-431 --}}
  <style>
		/*
		 * Lock window scroll on batch details. Content scrolls only inside
		 * #main-container-body so Select2/dropdowns appended to body cannot
		 * inflate document height into endless page scroll.
		 */
		html.batch-details-page,
		body.batch-details-page {
			overflow: hidden !important;
			height: 100%;
			overscroll-behavior: none;
		}

		body.batch-details-page #main-container-body {
			height: calc(100vh - 56px) !important;
			max-height: calc(100vh - 56px) !important;
			overflow-y: auto !important;
			overflow-x: hidden !important;
			overscroll-behavior: contain;
		}

		body.batch-details-page .batch-show-page {
			padding-bottom: 14px;
			min-height: 0;
		}

		body.batch-details-page .batch-tabs-panel {
			margin-bottom: 0;
		}

		body.batch-details-page .batch-show-modals {
			display: contents;
		}

		body.batch-details-page .batch-show-modals > .modal {
			position: fixed !important;
		}

		body.batch-details-page .lw-alpine-modal-overlay {
			position: fixed !important;
			left: 0 !important;
			right: 0 !important;
			width: 100% !important;
		}

		body.batch-details-page .lw-alpine-modal-overlay.show {
			display: block !important;
		}

		.batch-show-page .workflow-board-panel-body.flush-top {
			padding: 0;
		}

		@media (max-width: 767.98px) {
			html.batch-details-page {
				overflow: hidden !important;
				height: 100%;
			}

			body.batch-details-page {
				overflow-x: hidden !important;
				overflow-y: auto !important;
				height: 100%;
				overscroll-behavior: none;
			}

			body.batch-details-page #main-container-body {
				height: auto !important;
				max-height: none !important;
				overflow-y: visible !important;
			}
		}

		/* Page chrome */
		.batch-show-page{
			padding: 14px 18px 28px 18px;
		}
		.batch-show-shell{
			background: #fff;
			border: 1px solid #e9ecef;
			border-radius: 12px;
			box-shadow: 0 1px 0 rgba(16, 24, 40, 0.02);
			padding: 14px;
		}
		.batch-show-breadcrumbs .breadcrumb-container{
			margin-left: 0 !important;
			margin-right: 0 !important;
			margin-top: 8px;
			margin-bottom: 12px;
		}
		.batch-show-alerts .alert{
			border-radius: 10px;
			border: 1px solid rgba(0,0,0,0.06);
		}
		.batch-show-alerts .alert i{
			margin-right: 6px;
		}

		.form-part-toggler{
			margin: 0px 0px 5px 0px !important;
			padding: 6px 6px 6px 6px;
			border-bottom: 1px solid rgba(0,0,0,0.09);
			cursor: pointer;
		}

		.form-part-toggler:hover{
			background-color: rgba(0,0,0,0.08);
		}

		#sample-detail-rows .form-group{
			display: none;
		}

		#sample-detail-rows tr.selected-row{
			background-color: #eef7d5;
		}
		#sample-detail-rows tr.selected-row td{
			border: none !important;
		}

		td .form-group {
			margin-bottom: unset !important;
		}

		#sample-detail-rows .text{
			display: unset;
		}

		#sample-detail-rows tr.editable .form-group{
			display: unset;
		}

		#sample-detail-rows tr.editable .text{
			display: none;
		}
		
		.select2-selection{
			min-width: 200px !important;
		}
		.bg-white{
			background-color: white !important;
		}
		.hidden{
			display: none;
		}

		/* Hover-to-expand behaviour for long note messages */
		.show-hoverable .complete{
			display: none;
		}
		.show-hoverable .partial{
			display: unset;
		}
		.show-hoverable:hover .partial{
			display: none;
		}
		.show-hoverable:hover .complete{
			display: unset;
		}

		/* Batch workspace tab bar (Livewire batch.tabs) */
		.batch-show-page .batch-tabs-panel .batch-nav-tabs {
			display: flex;
			flex-wrap: wrap;
			align-items: flex-end;
			gap: 4px;
			padding: 8px 10px 0;
			margin: 0;
			list-style: none;
			background: #f8fafc;
			border: none;
			border-bottom: 1px solid #e2e8f0;
			border-radius: 0;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-item {
			margin: 0;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 10px 14px;
			margin-bottom: -1px;
			border-radius: 6px 6px 0 0;
			border: 1px solid transparent;
			border-bottom: 1px solid transparent;
			color: #64748b;
			font-size: 0.8125rem;
			font-weight: 600;
			line-height: 1.25;
			text-decoration: none;
			background: transparent;
			box-shadow: none;
			transition: color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
			color: #334155;
			background: rgba(255, 255, 255, 0.7);
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link.active {
			color: #0f172a;
			background: #fff;
			border-color: #e2e8f0;
			border-bottom-color: #fff;
			box-shadow: 0 -1px 4px rgba(15, 23, 42, 0.04), 0 2px 4px rgba(15, 23, 42, 0.06);
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link .mdi {
			font-size: 1.125rem;
			line-height: 1;
			opacity: 0.9;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link.active .mdi {
			color: #2563eb;
			opacity: 1;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link .badge {
			font-size: 0.65rem;
			font-weight: 700;
			padding: 3px 8px;
			border-radius: 999px;
			background: #e2e8f0;
			color: #475569;
			border: none;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link.active .badge {
			background: #dbeafe;
			color: #1d4ed8;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus {
			outline: none;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus-visible {
			outline: 2px solid #3b82f6;
			outline-offset: 2px;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link:active {
			transform: translateY(0.5px);
		}

		.batch-show-page .batch-tabs-panel #batch-tabs-content {
			margin-top: 1rem !important;
			padding-top: 0 !important;
			border-top: none !important;
		}

		@media (max-width: 575.98px) {
			.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link {
				padding: 8px 10px;
				font-size: 0.75rem;
			}
		}
  </style>
  <script>
    document.documentElement.classList.add('batch-details-page');
    if (document.body) {
      document.body.classList.add('batch-details-page');
    } else {
      document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('batch-details-page');
      });
    }
  </script>
@endsection

@section('content2')
  <main class="container-fluid lab-panel-theme batch-show-page workflow-theme">
    {{-- Breadcrumbs and alerts from original lines 435-530 --}}
    <?php
      if ($defaultClient) {
          $customerDetails = App\Models\CRM\CRMCustomer::find($defaultClient);
          if ($client_portal || Auth::user()->is_client == 1) {
              $items = [
                  ['link' => '/dashboard/crm/client-details', 'name' => $customerDetails->name, 'icon' => null],
                  ['link' => '#', 'name' => 'Customer Orders > '.(isset($batch->id) ? $batch->batch_code.' - Order Info' : 'Create New Order'), 'icon' => null],
              ];
          } else {
              $items = [
                  ['link' => route('customers-list'), 'name' => 'CRM', 'icon' => null],
                  ['link' => route('customers-list'), 'name' => 'Customer List', 'icon' => null],
                  ['link' => route('show-customer', ['id' => $defaultClient]), 'name' => $customerDetails->name, 'icon' => null],
                  ['link' => '#', 'name' => 'Customer Orders > '.(isset($batch->id) ? $batch->batch_code.' - Order Info' : 'Create New Order'), 'icon' => null],
              ];
          }
      } else {
          $workflowBreadcrumbStatus = $batch->status ?? ($status ?: 'Samples Reception');
          $items = [
              ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
              ['link' => route('sample-workflow', ['status' => 'All Samples']), 'name' => 'Sample Workflow', 'icon' => null],
              ['link' => route('sample-workflow', ['status' => $workflowBreadcrumbStatus]), 'name' => $workflowBreadcrumbStatus, 'icon' => null],
              ['link' => '#', 'name' => isset($batch->batch_code) ? $batch->batch_code.' - Batch Info' : 'New Batch', 'icon' => null],
          ];
      }
    ?>
    <div class="batch-show-shell">
    <div class="batch-show-breadcrumbs">
      <x-bread-crumb :items="$items"></x-bread-crumb>
    </div>
    
    <div class="batch-show-alerts">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(!$batch || !isset($batch->id))
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <i class="mdi mdi-alert-circle"></i> Batch not found. It may have been deleted.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <div class="text-center mb-3">
            <a href="{{ route('sample-workflow', ['status' => 'Samples Reception']) }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-arrow-left mr-2"></i> Back to Samples Reception
            </a>
        </div>
    @endif
    </div> {{-- batch-show-alerts --}}

    @if($batch && isset($batch->id))
    <div class="pb-2">
      {{-- Livewire Components --}}
      @livewire('batch.header', [
        'batch' => $batch,
        'workflows' => $workflows,
        'workflowstages' => $workflowstages,
        'status' => $status,
        'defaultClient' => $defaultClient,
        'clientPortal' => $client_portal ?? false
      ], 'header-'.$batch->id)
      
      <div class="mt-3">
        @livewire('batch.info', [
          'batch' => $batch,
          'batchID' => $batchID,
          'clients' => $clients,
          'sample_types' => $sample_types,
          'labsections' => $labsections,
          'labs' => $labs,
          'samplingmethods' => $samplingmethods,
          'recieving_users' => $recieving_users,
          'qc_schemes' => $qc_schemes,
          'qc_types' => $qc_types,
          'active_company' => $active_company,
          'defaultClient' => $defaultClient,
          'clientPageSize' => $clientPageSize
        ], 'info-'.$batch->id)
      </div>
      
      <span id="operators-list" data-operators='{{ json_encode($analysts) }}'></span>
      
      <div class="mt-4">
        @livewire('batch.tabs', [
          'batch' => $batch,
          'not_captured' => $not_captured,
          'status' => $status
        ], 'tabs-'.$batch->id)
      </div>
    </div>
    @endif
    </div> {{-- batch-show-shell --}}
  </main>

  <div class="batch-show-modals" aria-hidden="true">
    {{-- Add Sample Notes Modal (migrated from legacy sample-workflow show view) --}}
    <div id="add-sample-notes" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <form class="modal-content" id="print-labels-form" method="POST" action="{{ route('add-batch-comment') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-message-plus"></i> Add Note
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">User To Notify</label>
                        <select class="form-control no-select2" name="user_id" required>
                            <option value="">Select user...</option>
                            @foreach ($notifiable_users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            Also Notify <small class="text-muted">*Optional</small>
                        </label>
                        <select class="form-control no-select2" name="followers[]" multiple>
                            @foreach ($notifiable_users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
					<input name="batch_id" type="hidden" value="{{ $batch->id ?? '' }}" />
                    <div class="form-group">
                        <label class="control-label">Type</label>
                        <select class="form-control no-select2" name="type" required>
                            <option value="">Select type...</option>
							@if(($batch->status ?? null) == 'Sample Verification' || ($batch->status ?? null) == 'Samples In Lab')
                                <option value="Recheck">Recheck</option>
                            @endif
                            @foreach ($notesReminderType as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Message</label>
                        <textarea class="form-control" name="message" placeholder="Message..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm print-label-btn">
                        <i class="mdi mdi-email-send"></i> Send
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Process Results Modal (migrated from legacy sample-workflow show view) --}}
    @if(isset($batch->id) && isset($batch->status) && ($batch->status=="Sample Verification" || $batch->status=="Sample Approval" || $batch->status=="Samples In Lab"))
    <div id="process-results-modal" data-backdrop="static" data-keyboard="false" data-batch="{{json_encode($batch->id)}}" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-file-cog-outline"></i> Process Results </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p><i class="mdi mdi-information pull-left"></i> Results for batch <b>{{$batch->batch_code}} is being processed! </b> ?</p>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Report Format</label>
                        <select name="report_format" id="report_format" class="form-control no-select2">
                            <option value="">Choose Report Format</option>
                            <option value="gcla_02">GCLA 02 Form (Certificate of Analysis)</option>
                            @if(isset($batch) && $batch->hasForensicChemistryLab())
                            <option value="dcea_009">DCEA 009 Form (Government Laboratory Analyst Report)</option>
                            @endif
                            @if(isset($report_formats) && $report_formats->isNotEmpty())
								@foreach($report_formats as $format)
								<option value="{{ $format->id }}" data-report-code="{{ $format->report_code ?? '' }}" {{ isset($format->is_default) && $format->is_default ? 'selected' : '' }}>
                                    {{ $format->report_name }}@if($format->report_code) ({{ $format->report_code }})@endif
                                </option>
                                @endforeach
                            @else
                                <option value="" disabled>No report formats configured for this batch's lab section</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group hidden" id="gcla_language_group" style="margin-top: 10px;">
                        <label for="gcla_language" class="control-label">Report Language</label>
                        <select name="gcla_language" id="gcla_language" class="form-control no-select2">
                            <option value="sw" selected>Kiswahili (Swahili)</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="merge_with_attachments">
                            <label class="custom-control-label" for="merge_with_attachments">
                                Merge with attachments (append selected files to this COA)
                            </label>
                        </div>
                    </div>
                    <div class="form-group attachment-selection hidden" id="attachment-selection-group">
                        <label for="attachment_ids" class="control-label">Select attachments to append</label>
                        <select name="attachment_ids[]" id="attachment_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                            @foreach($batch->batch_attachments as $attachment)
                                <option value="{{ $attachment->id }}" {{ $attachment->show_on_coa ? 'selected' : '' }}>
                                    {{ $attachment->title ?? 'Attachment #'.$attachment->id }}
                                    @if(isset($attachment->attachtypename))
                                        ({{ $attachment->attachtypename }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="help-block">
                            <small>Order of selection will determine the order after the COA in the final PDF.</small>
                        </p>
                    </div>
                    <div class="proccesing-point hidden">
                        <center>
                            <img src="/images/load.gif" height="250px" width="auto" alt="">
                        </center>
                    </div>
                    <div class="form-group hidden">
                        <label for="" class="control-label"><input type="checkbox" name="add_pesticide" id="" class=""> Include Pesticide Results</label>
                    </div>
                    <span class="btn btn-sm btn-outline-info btn-block" id="initiate-process"><i class="mdi mdi-cogs"></i> Generate Report</span>
                </div>
                <!-- Modal content-->
                <div class="modal-footer">	
                    <a href="/sample-workflow/batch/{{$batch->id}}/details" class="btn btn-outline-danger float-right btn-sm">Close</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Parameter Settings Modal --}}
    <div id="parameter-settings-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form class="modal-content" id="parameter-settings-form">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-cog"></i> Parameter Settings
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="result_id" id="settings_result_id">
                    <input type="hidden" name="sample_code" id="settings_sample_code">
                    <input type="hidden" name="analyte" id="settings_analyte">
                    
                    <div class="form-group">
                        <label class="control-label">Method</label>
                        <select class="form-control no-select2" name="method_id" id="settings_method_id">
                            <option value="">Select Method...</option>
                            @foreach ($methods as $methodId => $methodName)
                                <option value="{{ $methodId }}">{{ $methodName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Reporting Unit</label>
                        <select class="form-control no-select2" name="reporting_unit" id="settings_reporting_unit">
                            <option value="">Select Reporting Unit...</option>
                            @foreach ($reportingUnits as $item)
                                <option value="{{ is_array($item) ? ($item['id'] ?? '') : (is_object($item) ? ($item->id ?? '') : (string) $item) }}">
                                    {{ is_array($item) ? ($item['name'] ?? $item['id'] ?? '') : (is_object($item) ? ($item->name ?? $item->id ?? '') : (string) $item) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Analyst/Operator</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name ?? 'Current user' }}" readonly>
                        <small class="text-muted">Assigned automatically to the logged-in user when settings are saved.</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Reporting Symbol</label>
                        <input type="text" class="form-control" name="reporting_symbol" id="settings_reporting_symbol" placeholder="e.g. <, >, N/D">
                    </div>
                    <div class="form-group mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="settings_accredited" name="accredited" value="1">
                            <label class="custom-control-label" for="settings_accredited">Accredited</label>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="settings_subcontracted" name="subcontracted" value="1">
                            <label class="custom-control-label" for="settings_subcontracted">Subcontracted</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="mdi mdi-check-circle"></i> Save Settings
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Standard Modal --}}
    <style>
        #edit-standard-modal .modal-content,
        #edit-standard-modal .modal-body {
            overflow: visible;
        }
        #edit-standard-modal .esl-config-card {
            border: 1px solid #17a2b8;
            border-radius: 8px;
            overflow: hidden;
        }
        #edit-standard-modal .esl-config-card__header {
            background: #17a2b8;
            color: #fff;
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        #edit-standard-modal .esl-config-card__body {
            padding: 0.85rem;
            background: #fff;
        }
    </style>
    @php
        $editStandardLookupValues = \App\StandardValue::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    @endphp
    <div id="edit-standard-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="edit-standard-form">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-pencil"></i> Edit Standard Limit
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="result_id" id="standard_result_id">
                    <input type="hidden" name="sample_code" id="standard_sample_code">
                    <input type="hidden" name="analyte" id="standard_analyte">

                    <div class="form-group mb-3">
                        <label class="control-label d-block">Value Type <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input esl-value-type" type="radio" name="value_type" id="esl_value_type_range" value="range">
                            <label class="form-check-label" for="esl_value_type_range">
                                <i class="mdi mdi-range"></i> Range
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input esl-value-type" type="radio" name="value_type" id="esl_value_type_use_value" value="use_value" checked>
                            <label class="form-check-label" for="esl_value_type_use_value">
                                <i class="mdi mdi-numeric"></i> Use Value
                            </label>
                        </div>
                    </div>

                    <div id="esl-range-section" class="esl-config-card mb-3" style="display:none; border-color:#007bff;">
                        <div class="esl-config-card__header" style="background:#007bff;">
                            <i class="mdi mdi-range"></i> Range Configuration
                        </div>
                        <div class="esl-config-card__body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="control-label">Low Value <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="range_low" id="esl_range_low" placeholder="Enter low value">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="control-label">High Value <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="range_high" id="esl_range_high" placeholder="Enter high value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="esl-use-value-section" class="esl-config-card mb-0">
                        <div class="esl-config-card__header">
                            <i class="mdi mdi-numeric"></i> Standard Value Configuration
                        </div>
                        <div class="esl-config-card__body">
                            <div class="form-group mb-3">
                                <label class="control-label">Standard Value <span class="text-danger">*</span></label>
                                <select class="form-control no-select2" name="standard_value_id" id="esl_standard_value_id">
                                    <option value="">Select standard value...</option>
                                    @foreach($editStandardLookupValues as $lookupValue)
                                        <option value="{{ $lookupValue->id }}" data-code="{{ $lookupValue->code }}">
                                            {{ $lookupValue->name }} ({{ $lookupValue->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="esl-is-value-fields" style="display:none;">
                                <div class="alert alert-info py-2">
                                    <i class="mdi mdi-information"></i> Additional configuration for the selected standard value.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="control-label">Matrix Operator <span class="text-danger">*</span></label>
                                            <select class="form-control no-select2" name="matrix_operator" id="esl_matrix_operator">
                                                <option value="">Select Operator</option>
                                                <option value="max">Max</option>
                                                <option value="min">Min</option>
                                                <option value="greater_than">> (Greater Than)</option>
                                                <option value="less_than">< (Less Than)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="control-label">Actual Value <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="matrix_value" id="esl_matrix_value" placeholder="Enter actual value">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="mdi mdi-check-circle"></i> Save Standard
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
  </div> {{-- batch-show-modals --}}
@endsection

@section('script2')
  <script>
    (function () {
        var unlockTimer = null;

        function isVisibleModal(el) {
            return el && window.getComputedStyle(el).display !== 'none';
        }

        function hasOpenAlpineModal() {
            return Array.from(document.querySelectorAll('.lw-alpine-modal-overlay, .lw-alpine-wizard-backdrop, .report-prep-modal-root')).some(isVisibleModal);
        }

        function hasOpenAccWizardBackdrop() {
            return Array.from(document.querySelectorAll('.acc-wizard-backdrop')).some(isVisibleModal);
        }

        function unlockStuckScroll() {
            if (unlockTimer !== null) {
                clearTimeout(unlockTimer);
            }

            unlockTimer = setTimeout(function () {
                unlockTimer = null;
                var hasOpenBootstrapModal = document.querySelector('.modal.show, .modal.in');
                var mainBody = document.getElementById('main-container-body');

                if (mainBody) {
                    mainBody.style.removeProperty('overflow');
                    mainBody.style.overflowY = 'auto';
                }

                if (!hasOpenBootstrapModal && !hasOpenAccWizardBackdrop() && !hasOpenAlpineModal()) {
                    document.body.classList.remove('modal-open');
                    document.body.classList.remove('acc-wizard-open');
                    document.body.classList.remove('acc-wizard-page-scroll');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                }

                // Keep window scroll locked so dropdowns cannot inflate page height.
                document.documentElement.classList.add('batch-details-page');
                document.body.classList.add('batch-details-page');
                if (window.scrollY !== 0) {
                    window.scrollTo(0, 0);
                }
            }, 250);
        }

        document.documentElement.classList.add('batch-details-page');

        window.addEventListener('scroll', function () {
            if (document.documentElement.classList.contains('batch-details-page') && window.scrollY !== 0) {
                window.scrollTo(0, 0);
            }
        }, { passive: true });

        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('batch-details-page');
            unlockStuckScroll();
        });
        document.addEventListener('hidden.bs.modal', unlockStuckScroll);

        document.addEventListener('livewire:initialized', function () {
            unlockStuckScroll();
            Livewire.hook('morph.updated', unlockStuckScroll);
        });
    })();

    $(document).on('click', '[data-target="#add-attachment-batch"]', function (event) {
        event.preventDefault();
        var $modal = $('#add-attachment-batch');
        if ($modal.length) {
            $modal.appendTo('body').modal('show');
        }
    });

    $(document).on('click', '[data-target="#add-sample-notes"]', function (event) {
        event.preventDefault();
        var $modal = $('#add-sample-notes');
        if ($modal.length) {
            $modal.appendTo('body').modal('show');
        }
    });

    $(document).on('click', '[data-target="#edit-standard-modal"], .edit-standard-btn', function (event) {
        var $modal = $('#edit-standard-modal');
        if ($modal.length) {
            $modal.appendTo('body');
        }
    });

    $('#add-sample-notes').on('shown.bs.modal', function () {
        var $modal = $(this);
        $modal.find('select.no-select2').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                width: '100%',
                dropdownParent: $modal
            });
        });
    });

	// Setup CSRF token for all AJAX requests
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	var detectChange = function(ts){
		var op = $(ts).children('option:selected');
		$('#client-unit-select').html('<option value="" selected>Select Site Location...</option>');
		$('#client-unit-select').trigger('change');

		if(op.val() && String(op.val()).trim() !== ''){
			$.ajax({
				url:`/get/Client-Details/Ajax/${op.val()}`,
				method:'GET',
				success:(data)=>{
					// Update contacts
					$('#crm_contact_id').empty();
					$('#crm_contact_id').append('<option value="">Choose contact...</option>');
					$.each(data['contacts'],(i,obj)=>{
						var name = `${obj.first_name} ${obj.middle_name || ''} ${obj.last_name || ''}`
						var option = `<option value="${obj.id}">${name}</option>`
						$('#crm_contact_id').append(option)
					});
					$('#crm_contact_id').select2();
					$('#crm_contact_id').val($('#crm_contact_id').data('selected')).trigger('change');

					// Update Site Location label from CRM config (Company Section / Unit, etc.)
					if (data['unit_name']) {
						$('.client-prefered-unit-name').text(data['unit_name']);
					}

					// Populate Site Location options from CRM units
					$.each(data['units'], function(i, e){
						$('#client-unit-select').append('<option value="'+e.id+'">'+e.name+'</option>');
					});

					// Default customer email from CRM only when batch has no saved email
					if (data['customer'] && data['customer'].email && !$('#customer_email').val()) {
						$('#customer_email').val(data['customer'].email);
					}

					var selectedContactId = $('#crm_contact_id').data('selected');
					if (selectedContactId && data['contacts']) {
						var matchedContact = data['contacts'].find(function (contact) {
							return String(contact.id) === String(selectedContactId);
						});
						if (matchedContact && matchedContact.email && !$('#customer_email').val()) {
							$('#customer_email').val(matchedContact.email);
						}
					}

					if (data['mode_of_payment']) {
						$('#mode-of-payment').val(data['mode_of_payment']);
					} else {
						$('#mode-of-payment').val('');
					}

					$('#client-unit-select').val($('#client-unit-select').data('selected')).trigger('change');
				},
				error:(data)=>{
					console.log(data);
				}
			})
		} else {
			$('#mode-of-payment').val('');
		}
	};
	
	$(function(){
		$('#client-select').on('change', function(){
			detectChange(this);
		}).trigger('change');

		// Handle tab activation via query parameter
		var urlParams = new URLSearchParams(window.location.search);
		var tab = urlParams.get('tab');
		if (tab) {
			$('#' + tab + '-tab').tab('show');
		}

		$('.batch-info-trigger').on('click', function(){
			$(this).toggleClass('open');
			if($(this).hasClass('open')){
				$(this).html(`<i class="mdi mdi-chevron-double-up"></i> Batch info`);
				$('#batch-detail-form').removeClass('hidden');
			}
			else{
				$(this).html(`<i class="mdi mdi-chevron-double-down"></i> Batch info`);
				$('#batch-detail-form').addClass('hidden');
			}
		});

		var lastProcessedReportOptions = null;

		// Capture which action opened Process Results so we can apply context-specific options.
		$(document).on('click', '[data-target="#process-results-modal"]', function () {
			var nextModalTarget = $(this).attr('data-next-modal') || '';
			$('#process-results-modal').data('next-modal-target', nextModalTarget);
		});

		// Process Results Modal Handler
		$('#process-results-modal').on('show.bs.modal', function(event){
			var $modal = $('#process-results-modal');
			var batch = $modal.data('batch');
			var trigger = event && event.relatedTarget ? $(event.relatedTarget) : $();
			var nextModalTarget = trigger.attr('data-next-modal')
				|| $modal.data('next-modal-target')
				|| '';

			$modal.data('next-modal-target', nextModalTarget);

			var isTestRequestFlow = nextModalTarget === '#process-test-request-report-modal';
			var $formatSelect = $modal.find('#report_format');

			if (!Array.isArray($modal.data('all-report-format-options'))) {
				var allOptions = [];
				$formatSelect.find('option').each(function () {
					allOptions.push({
						value: $(this).attr('value') || '',
						text: $(this).text(),
						disabled: $(this).prop('disabled'),
						reportCode: ($(this).attr('data-report-code') || '').toUpperCase()
					});
				});
				$modal.data('all-report-format-options', allOptions);
			}

			var allReportOptions = $modal.data('all-report-format-options') || [];
			$formatSelect.empty();

			if (isTestRequestFlow) {
				var finalOption = allReportOptions.find(function (opt) {
					var text = (opt.text || '').toUpperCase();
					return opt.reportCode === 'FINAL_RESULTS' || text.indexOf('FINAL RESULTS') !== -1 || text.indexOf('FINAL REPORT') !== -1;
				});

				if (finalOption) {
					$formatSelect.append(
						$('<option>', {
							value: finalOption.value,
							text: finalOption.text,
							'aria-label': finalOption.text
						})
					);
					$formatSelect.val(finalOption.value);
				} else {
					$formatSelect.append(
						$('<option>', {
							value: '',
							text: 'Final Report format is not configured for this batch.',
							disabled: true,
							selected: true
						})
					);
				}
			} else {
				allReportOptions.forEach(function (opt) {
					$formatSelect.append(
						$('<option>', {
							value: opt.value,
							text: opt.text,
							disabled: !!opt.disabled
						})
					);
				});
				$formatSelect.val('');
			}

			$modal.find('.proccesing-point').addClass('hidden');
			$modal.find('#initiate-process').prop('disabled', false).removeClass('disabled').html('<i class="mdi mdi-cogs"></i> Generate Report');
			
			// Initialize Select2 for report format dropdown inside modal
			if ($.fn.select2) {
				$modal.find('#report_format').select2({
					width: '100%',
					dropdownParent: $modal
				});
			}
			$formatSelect.trigger('change');

			// Show/hide language choice group based on format selection
			$modal.find('#report_format').off('change').on('change', function () {
				if ($(this).val() === 'gcla_02' || $(this).val() === 'dcea_009') {
					$modal.find('#gcla_language_group').removeClass('hidden');
				} else {
					$modal.find('#gcla_language_group').addClass('hidden');
				}
			});

			// Reset merge with attachments UI
			$modal.find('#merge_with_attachments').prop('checked', false);
			$modal.find('#attachment-selection-group').addClass('hidden');

			// Initialize Select2 for attachments if available
			if ($.fn.select2) {
				$modal.find('#attachment_ids').select2({
					width: '100%',
					dropdownParent: $modal
				});
			}

			$modal.find('#merge_with_attachments').off('change').on('change', function () {
				if (this.checked) {
					$modal.find('#attachment-selection-group').removeClass('hidden');
				} else {
					$modal.find('#attachment-selection-group').addClass('hidden');
				}
			});

			lastProcessedReportOptions = null;

			$modal.find('#initiate-process').off('click').on('click', function(){
				var selectedFormat = $modal.find('#report_format').val();
				
				// Validate that a report format has been selected
				if (!selectedFormat) {
					alert('Please select a report format before generating the report.');
					return;
				}

				var include_pesticide = $modal.find('input[name="add_pesticide"]').is(':checked') ? 1 : 0;
				var merge_with_attachments = $modal.find('#merge_with_attachments').is(':checked') ? 1 : 0;
				var attachment_ids = [];
				if (merge_with_attachments) {
					attachment_ids = $modal.find('#attachment_ids').val() || [];
				}
				var gcla_language = $modal.find('#gcla_language').val() || 'sw';
				lastProcessedReportOptions = {
					report_format: selectedFormat,
					gcla_language: gcla_language
				};
				
				// Disable the button to prevent double-clicks
				$(this).prop('disabled', true).addClass('disabled').html('<i class="mdi mdi-loading mdi-spin"></i> Generating...');
				$modal.find('.proccesing-point').removeClass('hidden');
				
				$.ajax({
					url:"{{ route('process-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0]) }}",	
					data:{
						_token: '{{ csrf_token() }}',
						report_format : selectedFormat,
						include_pesticide : include_pesticide,
						merge_with_attachments: merge_with_attachments,
						attachment_ids: attachment_ids.join(','),
						gcla_language: gcla_language
					},
					method:'POST',
					success: function(data){
						console.log(data);
						$modal.find('.modal-body').empty();
						var success_tag = $(`
							<div class="alert alert-success p-2">
							<i class="mdi mdi-information pull-left"></i>
								Results processed successfully!
							</div>
							<center>
							<img src="/images/suc.gif" height="250px" width="auto" alt="">
							</center>
						`).clone();
						$modal.find('.modal-body').append(success_tag);
						if (typeof Livewire !== 'undefined') {
							Livewire.dispatch('attachmentsUpdated');
						}

						setTimeout(function () {
							$modal.modal('hide');
							var postProcessModal = $modal.data('next-modal-target');
							if (postProcessModal) {
								$(postProcessModal).modal('show');
							}
						}, 500);
					},
					error: function(data){
						console.log(data);
						// Re-enable the button on error
						$modal.find('#initiate-process').prop('disabled', false).removeClass('disabled').html('<i class="mdi mdi-cogs"></i> Generate Report');
						$modal.find('.proccesing-point').addClass('hidden');
						alert('An error occurred while processing the report. Please try again.');
					}
				})
			})
		});

		// ----------------------------------------------------
		// RAW RESULTS LIVE AUTO-SAVE & MODALS EVENT HANDLERS
		// ----------------------------------------------------

		// Trigger blur on enter inside result input
		$(document).on('keypress', '.result-input', function(e) {
			if (e.which === 13) {
				$(this).blur();
			}
		});

		// Focus event to capture starting value
		$(document).on('focus', '.result-input', function() {
			var $input = $(this);
			$input.data('last-val', $input.val());
		});

		// Auto-save result on blur or change
		$(document).on('change blur', '.result-input', function() {
			var $input = $(this);
			var resultVal = $input.val();
			var resultId = $input.attr('data-result-id') || $input.data('result-id');
			var sampleCode = $input.data('sample-code');
			var analyte = $input.data('analyte');

			// Prevent duplicate triggers
			if ($input.data('last-val') === resultVal) {
				return;
			}
			$input.data('last-val', resultVal);

			$input.removeClass('border-success border-danger border-secondary').addClass('border-warning');

			$.ajax({
				url: '{{ route("update-result") }}',
				method: 'POST',
				data: {
					result_id: resultId,
					sample_code: sampleCode,
					analyte: analyte,
					result: resultVal
				},
				success: function(response) {
					if (response.success) {
						$input.removeClass('border-warning');
						if (response.result_id) {
							$input.attr('data-result-id', response.result_id);
							$input.data('result-id', response.result_id);
							
							// Update modal trigger button data attributes in the same cell
							var $cell = $input.closest('.parameter-cell');
							$cell.find('.parameter-settings-btn').attr('data-result-id', response.result_id).data('result-id', response.result_id);
							$cell.find('.edit-standard-btn').attr('data-result-id', response.result_id).data('result-id', response.result_id);
						}
						
						if (response.validation_result === 'PASS') {
							$input.addClass('border-success');
						} else if (response.validation_result === 'FAIL') {
							$input.addClass('border-danger');
						} else {
							$input.addClass('border-secondary');
						}

						// Update standard limit if returned
						if (response.standard_limit) {
							var $cell = $input.closest('.parameter-cell');
							$cell.find('.standard-limit-text').text(response.standard_limit);
						}
					} else {
						$input.removeClass('border-warning').addClass('border-danger');
						console.error(response.message);
					}
				},
				error: function(xhr) {
					$input.removeClass('border-warning').addClass('border-danger');
					console.error('Failed to auto-save result:', xhr.responseText);
				}
			});
		});

		// Parameter settings modal show handler
		$(document).on('click', '.parameter-settings-btn', function() {
			var $btn = $(this);
			var $cell = $btn.closest('.parameter-cell');
			var resultId = $btn.attr('data-result-id') || $btn.data('result-id');
			var sampleCode = $btn.data('sample-code') || $cell.data('sample') || '';
			var analyte = $btn.data('analyte') || $cell.data('analyte') || '';

			$('#parameter-settings-form')[0].reset();
			$('#settings_result_id').val(resultId || '');
			$('#settings_sample_code').val(sampleCode || '');
			$('#settings_analyte').val(analyte || '');

			if (resultId) {
				$.ajax({
					url: `/captured-results/get-parameter-settings/${resultId}`,
					method: 'GET',
					success: function(response) {
						if (response.success && response.data) {
							var data = response.data;
							$('#settings_method_id').val(data.method_id || '');
							$('#settings_reporting_unit').val(data.reporting_unit || '');
							$('#settings_reporting_symbol').val(data.reporting_symbol || '');
							$('#settings_accredited').prop('checked', data.accredited == 1);
							$('#settings_subcontracted').prop('checked', data.subcontracted == 1);
						}
					}
				});
			}
		});

		$('#parameter-settings-modal').on('shown.bs.modal', function () {
			var $modal = $(this);
			$modal.find('select.no-select2').each(function () {
				var $select = $(this);
				if ($select.hasClass('select2-hidden-accessible')) {
					$select.select2('destroy');
				}
				$select.select2({
					width: '100%',
					dropdownParent: $modal,
				});
			});
		});

		// Save parameter settings form submission
		$('#parameter-settings-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
			var submitBtn = $form.find('button[type="submit"]');
			submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');

			$.ajax({
				url: '{{ route("update-parameter-settings") }}',
				method: 'POST',
				data: $form.serialize(),
				success: function(response) {
					submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Settings');
					if (response.success) {
						$('#parameter-settings-modal').modal('hide');
						
						var sampleCode = $('#settings_sample_code').val();
						var analyte = $('#settings_analyte').val();
						var $input = $(`.result-input[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						var $btn = $(`.parameter-settings-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						var $editBtn = $(`.edit-standard-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						
						if (response.result_id) {
							$input.attr('data-result-id', response.result_id);
							$input.data('result-id', response.result_id);
							$btn.attr('data-result-id', response.result_id);
							$btn.data('result-id', response.result_id);
							if ($editBtn.length) {
								$editBtn.attr('data-result-id', response.result_id).data('result-id', response.result_id);
							}
						}
						alert('Parameter settings saved successfully.');
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function(xhr) {
					submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Settings');
					alert('Failed to save parameter settings: ' + xhr.responseText);
				}
			});
		});

		function syncEditStandardModalSections() {
			var valueType = $('#edit-standard-form input[name="value_type"]:checked').val() || 'use_value';
			var isRange = valueType === 'range';
			$('#esl-range-section').toggle(isRange);
			$('#esl-use-value-section').toggle(!isRange);

			var selectedCode = $('#esl_standard_value_id option:selected').data('code') || '';
			var showIsValueFields = !isRange && selectedCode === 'IsValue';
			$('#esl-is-value-fields').toggle(showIsValueFields);

			if (!showIsValueFields) {
				$('#esl_matrix_operator').val('');
				$('#esl_matrix_value').val('');
			}
		}

		function resetEditStandardFormFields() {
			$('#esl_value_type_use_value').prop('checked', true);
			$('#esl_range_low').val('');
			$('#esl_range_high').val('');
			$('#esl_standard_value_id').val('');
			$('#esl_matrix_operator').val('');
			$('#esl_matrix_value').val('');
			syncEditStandardModalSections();
		}

		function populateEditStandardForm(existingText) {
			resetEditStandardFormFields();

			if (!existingText || existingText === 'No limit set') {
				return;
			}

			var typedMatch = existingText.match(/^(min|max)\s+(\d+(?:\.\d+)?)$/i);
			if (typedMatch) {
				$('#esl_value_type_use_value').prop('checked', true);
				var isValueOption = $('#esl_standard_value_id option').filter(function () {
					return String($(this).data('code')) === 'IsValue';
				}).first();
				if (isValueOption.length) {
					$('#esl_standard_value_id').val(isValueOption.val());
				}
				$('#esl_matrix_operator').val(typedMatch[1].toLowerCase());
				$('#esl_matrix_value').val(typedMatch[2]);
				syncEditStandardModalSections();
				return;
			}

			var reversedMatch = existingText.match(/^(\d+(?:\.\d+)?)\s+(min|max)$/i);
			if (reversedMatch) {
				$('#esl_value_type_use_value').prop('checked', true);
				var isValueOptionReversed = $('#esl_standard_value_id option').filter(function () {
					return String($(this).data('code')) === 'IsValue';
				}).first();
				if (isValueOptionReversed.length) {
					$('#esl_standard_value_id').val(isValueOptionReversed.val());
				}
				$('#esl_matrix_operator').val(reversedMatch[2].toLowerCase());
				$('#esl_matrix_value').val(reversedMatch[1]);
				syncEditStandardModalSections();
				return;
			}

			var compareMatch = existingText.match(/^([<>])\s*(\d+(?:\.\d+)?)$/);
			if (compareMatch) {
				$('#esl_value_type_use_value').prop('checked', true);
				var isValueOptionCompare = $('#esl_standard_value_id option').filter(function () {
					return String($(this).data('code')) === 'IsValue';
				}).first();
				if (isValueOptionCompare.length) {
					$('#esl_standard_value_id').val(isValueOptionCompare.val());
				}
				$('#esl_matrix_operator').val(compareMatch[1] === '<' ? 'less_than' : 'greater_than');
				$('#esl_matrix_value').val(compareMatch[2]);
				syncEditStandardModalSections();
				return;
			}

			if (/^\d+(?:\.\d+)?\s*-\s*\d+(?:\.\d+)?$/i.test(existingText)) {
				var parts = existingText.split(/\s*-\s*/);
				$('#esl_value_type_range').prop('checked', true);
				$('#esl_range_low').val(parts[0] || '');
				$('#esl_range_high').val(parts[1] || '');
				syncEditStandardModalSections();
				return;
			}

			var codeOption = $('#esl_standard_value_id option').filter(function () {
				var code = String($(this).data('code') || '');
				var name = String($(this).text() || '');
				return code.toLowerCase() === existingText.toLowerCase()
					|| name.toLowerCase().indexOf(existingText.toLowerCase()) !== -1;
			}).first();

			if (codeOption.length) {
				$('#esl_value_type_use_value').prop('checked', true);
				$('#esl_standard_value_id').val(codeOption.val());
				syncEditStandardModalSections();
			}
		}

		function applyEditStandardSettings(data) {
			if (!data) {
				return;
			}

			var valueType = data.value_type || 'use_value';
			if (valueType === 'range') {
				$('#esl_value_type_range').prop('checked', true);
				$('#esl_range_low').val(data.range_low || '');
				$('#esl_range_high').val(data.range_high || '');
			} else {
				$('#esl_value_type_use_value').prop('checked', true);
				$('#esl_standard_value_id').val(data.standard_value_id || '');
				$('#esl_matrix_operator').val(data.matrix_operator || '');
				$('#esl_matrix_value').val(data.matrix_value || '');
			}

			syncEditStandardModalSections();
		}

		$(document).on('change', '#edit-standard-form .esl-value-type, #esl_standard_value_id', function () {
			syncEditStandardModalSections();
		});

		// Edit standard modal show handler
		$(document).on('click', '.edit-standard-btn', function() {
			var $btn = $(this);
			var $cell = $btn.closest('.parameter-cell');
			var resultId = $btn.attr('data-result-id') || $btn.data('result-id');
			var sampleCode = $btn.data('sample-code') || $cell.data('sample') || '';
			var analyte = $btn.data('analyte') || $cell.data('analyte') || '';

			$('#edit-standard-form')[0].reset();
			$('#standard_result_id').val(resultId || '');
			$('#standard_sample_code').val(sampleCode || '');
			$('#standard_analyte').val(analyte || '');
			resetEditStandardFormFields();

			var existingText = $btn.siblings('.standard-limit-text').text().trim();
			populateEditStandardForm(existingText);

			if (resultId) {
				$.ajax({
					url: `/captured-results/get-standard-settings/${resultId}`,
					method: 'GET',
					success: function(response) {
						if (response.success && response.data) {
							applyEditStandardSettings(response.data);
						}
					}
				});
			}
		});

		$('#edit-standard-modal').on('shown.bs.modal', function () {
			var $modal = $(this);
			$modal.find('select.no-select2').each(function () {
				var $select = $(this);
				if ($.fn.select2) {
					if ($select.hasClass('select2-hidden-accessible')) {
						$select.select2('destroy');
					}
					$select.select2({
						width: '100%',
						dropdownParent: $modal,
						minimumResultsForSearch: $select.is('#esl_standard_value_id') ? 0 : Infinity,
					});
				}
			});
			syncEditStandardModalSections();
		});

		// Save standard limit form submission
		$('#edit-standard-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
			var valueType = $form.find('input[name="value_type"]:checked').val() || 'use_value';

			if (valueType === 'range') {
				if (!$('#esl_range_low').val() || !$('#esl_range_high').val()) {
					alert('Please enter both low and high values for the range.');
					return;
				}
			} else {
				if (!$('#esl_standard_value_id').val()) {
					alert('Please select a standard value.');
					return;
				}
				var selectedCode = $('#esl_standard_value_id option:selected').data('code') || '';
				if (selectedCode === 'IsValue') {
					if (!$('#esl_matrix_operator').val() || !$('#esl_matrix_value').val()) {
						alert('Please enter Matrix Operator and Actual Value for IsValue.');
						return;
					}
				}
			}

			var submitBtn = $form.find('button[type="submit"]');
			submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');

			$.ajax({
				url: '{{ route("update-standard-limit") }}',
				method: 'POST',
				data: $form.serialize(),
				success: function(response) {
					submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Standard');
					if (response.success) {
						$('#edit-standard-modal').modal('hide');

						var sampleCode = $('#standard_sample_code').val();
						var analyte = $('#standard_analyte').val();
						var resultId = response.result_id || $('#standard_result_id').val();
						var $editBtn = $();
						var $input = $();
						var $settingsBtn = $();
						var $cell = $();

						if (resultId) {
							$editBtn = $(`.edit-standard-btn[data-result-id="${resultId}"]`);
							$input = $(`.result-input[data-result-id="${resultId}"]`);
							$settingsBtn = $(`.parameter-settings-btn[data-result-id="${resultId}"]`);
							$cell = $editBtn.closest('.parameter-cell');
						}

						if (!$cell.length && sampleCode && analyte) {
							$editBtn = $(`.edit-standard-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
							$input = $(`.result-input[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
							$settingsBtn = $(`.parameter-settings-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
							$cell = $editBtn.closest('.parameter-cell');
						}

						if (!$cell.length && sampleCode && analyte) {
							$cell = $(`.parameter-cell[data-sample="${sampleCode}"][data-analyte="${analyte}"]`);
							$editBtn = $cell.find('.edit-standard-btn');
							$input = $cell.find('.result-input');
							$settingsBtn = $cell.find('.parameter-settings-btn');
						}

						if (resultId) {
							$input.attr('data-result-id', resultId).data('result-id', resultId);
							$editBtn.attr('data-result-id', resultId).data('result-id', resultId);
							$settingsBtn.attr('data-result-id', resultId).data('result-id', resultId);
						}

						if (sampleCode) {
							$editBtn.attr('data-sample-code', sampleCode).data('sample-code', sampleCode);
							$input.attr('data-sample-code', sampleCode).data('sample-code', sampleCode);
							$settingsBtn.attr('data-sample-code', sampleCode).data('sample-code', sampleCode);
						}

						if (analyte) {
							$editBtn.attr('data-analyte', analyte).data('analyte', analyte);
							$input.attr('data-analyte', analyte).data('analyte', analyte);
							$settingsBtn.attr('data-analyte', analyte).data('analyte', analyte);
						}

						if (response.standard_limit) {
							$cell.find('.standard-limit-text').text(response.standard_limit);
						}

						$input.removeClass('border-success border-danger border-secondary');
						if (response.validation_result === 'PASS') {
							$input.addClass('border-success');
						} else if (response.validation_result === 'FAIL') {
							$input.addClass('border-danger');
						} else {
							$input.addClass('border-secondary');
						}

						if (window.Livewire) {
							Livewire.dispatch('resultsUpdated');
						}
					} else {
						alert('Error: ' + (response.message || 'Unable to save standard limit.'));
					}
				},
				error: function(xhr) {
					submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Standard');
					alert('Failed to save standard limit: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.responseText));
				}
			});
		});
	});
  </script>
@endsection
