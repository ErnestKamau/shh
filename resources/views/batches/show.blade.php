@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
  @include('layouts.lab.partials.lab-panel-theme-styles')
  {{-- Include all CSS from original show.blade.php lines 5-431 --}}
  <style>
		body{
			overflow-x: hidden !important;
		}

        /* Keep batch details page vertically scrollable in fixed-header layouts */
        #main-container-body {
            height: calc(100vh - 56px);
            overflow-y: auto;
        }

        @media (max-width: 767.98px) {
            #main-container-body {
                height: auto;
                overflow-y: visible;
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
@endsection

@section('content2')
  <main class="container-fluid lab-panel-theme batch-show-page">
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
          $items = [
              ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
              ['link' => route('sample-workflow', ['status' => 'All Samples']), 'name' => 'Sample Workflow', 'icon' => null],
              ['link' => route('sample-workflow', ['status' => isset($status) && $status ? $status : $batch->status ?? 'Samples Reception']), 'name' => isset($status) && $status ? $status : $batch->status ?? 'Samples Reception', 'icon' => null],
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
          'samplingmethods' => $samplingmethods,
          'recieving_users' => $recieving_users,
          'qc_schemes' => $qc_schemes,
          'qc_types' => $qc_types,
          'batch_scope' => $batch_scope,
          'customer_survey' => $customer_survey,
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
                        <select class="form-control" name="user_id" required placeholder="Select User...">
                            <option></option>
                            @foreach ($notifiable_users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            Also Notify <small class="text-muted">*Optional</small>
                        </label>
                        <select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
                            <option></option>
                            @foreach ($notifiable_users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
					<input name="batch_id" type="hidden" value="{{ $batch->id ?? '' }}" />
                    <div class="form-group">
                        <label class="control-label">Type</label>
                        <select class="form-control" name="type" required placeholder="Message Type...">
                            <option></option>
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
                                <option value="{{ $format->id }}" {{ isset($format->is_default) && $format->is_default ? 'selected' : '' }}>
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

    {{-- View COA Report Modal (migrated from legacy sample-workflow show view) --}}
    @if(isset($batch->id) && isset($batch->status) && in_array($batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection']))
    <div class="modal fade" id="view-coa-report" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="#" method="get" id="coa-report-form-again">
                    <div class="modal-body">
                        @if($batch->getVerificationApprovalStatus() > 0 && $batch->status == 'Sample Verification' )
                        <div class="alert alert-danger p-2 d-flex mt-1">
                            <i class="mdi mdi-decagram" style="font-size:30px"></i>
                            <span class="p-2">Confirm all approvers have approved the report to have all the required signatories appear on the COA.</span>
                        </div>
                        @endif
                        @if($batch->getApprovalStageStatus() > 0 && $batch->status == 'Sample Approval' )
                        <div class="alert alert-danger p-2 d-flex mt-1">
                            <i class="mdi mdi-decagram" style="font-size:30px"></i>
                            <span class="p-2">Confirm all approvers have approved the report to have all the required signatories appear on the COA.</span>
                        </div>
                        @endif

                        <div class="alert alert-success p-2 d-flex">
                            <i class="mdi mdi-cogs" style="font-size: 25px"></i>
                            <span class="p-2">Confirm you want to view COA report for this batch by selecting the report standard below:</span>
                        </div>
                        <div class="form-group">
                            <label for="" class="control-label">Report Format</label>
                            <select name="report_format" id="report_format_select_again" class="form-control no-select2" required>
                                <option value="">Select Report Format</option>
                                <option value="gcla_02">GCLA 02 Form (Certificate of Analysis)</option>
                                @if(isset($batch) && $batch->hasForensicChemistryLab())
                                <option value="dcea_009">DCEA 009 Form (Government Laboratory Analyst Report)</option>
                                @endif
                                @if(isset($report_formats) && $report_formats->isNotEmpty())
                                    @foreach($report_formats as $format)
                                    <option value="{{ $format->id }}" {{ isset($format->is_default) && $format->is_default ? 'selected' : '' }}>
                                        {{ $format->report_name }}@if($format->report_code) ({{ $format->report_code }})@endif
                                    </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No report formats configured for this batch's lab section</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group hidden" id="gcla_language_group_again" style="margin-top: 10px;">
                            <label for="gcla_language_again" class="control-label">Report Language</label>
                            <select name="gcla_language_again" id="gcla_language_again" class="form-control no-select2">
                                <option value="sw" selected>Kiswahili (Swahili)</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <input type="hidden" name="batch_id" value="{{$batch->id}}">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-outline-success" type="button" id="generate-coa-btn-again"><i class="mdi mdi-cogs"></i> Generate Report</button>
                        <span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
                    </div>
                </form>
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
                        <select class="form-control" name="method_id" id="settings_method_id">
                            <option value="">Select Method...</option>
                            @foreach ($methods as $item)
                                <option value="{{ is_array($item) ? $item['id'] : $item->id }}">{{ is_array($item) ? $item['name'] : $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Reporting Unit</label>
                        <select class="form-control" name="reporting_unit" id="settings_reporting_unit">
                            <option value="">Select Reporting Unit...</option>
                            @foreach ($reportingUnits as $item)
                                <option value="{{ is_array($item) ? $item['id'] : $item->id }}">{{ is_array($item) ? $item['name'] : $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Analyst/Operator</label>
                        <select class="form-control" name="analyst_id" id="settings_analyst_id">
                            <option value="">Select Analyst...</option>
                            @foreach ($analysts as $item)
                                <option value="{{ is_array($item) ? $item['id'] : $item->id }}">{{ is_array($item) ? $item['name'] : $item->name }}</option>
                            @endforeach
                        </select>
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
    <div id="edit-standard-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
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
                    
                    <div class="form-group">
                        <label class="control-label">Standard Limit Value</label>
                        <input type="text" class="form-control" name="standard_value" id="standard_value" required placeholder="e.g. 10.0">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Limit Type</label>
                        <select class="form-control" name="limit_type" id="standard_limit_type" required>
                            <option value="MAX">MAX</option>
                            <option value="MIN">MIN</option>
                            <option value="RANGE">RANGE</option>
                        </select>
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
    
  </main>
@endsection

@section('script2')
  <script>
    (function () {
        function unlockStuckScroll() {
            var hasOpenModal = document.querySelector('.modal.show');

            if (!hasOpenModal) {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }

        document.addEventListener('DOMContentLoaded', unlockStuckScroll);
        document.addEventListener('hidden.bs.modal', unlockStuckScroll);

        document.addEventListener('livewire:initialized', function () {
            unlockStuckScroll();
            Livewire.hook('morph.updated', unlockStuckScroll);
        });
    })();

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

		if(op.val() > 0){
			$.ajax({
				url:`/get/Client-Details/Ajax/${op.val()}`,
				method:'GET',
				success:(data)=>{
					// Update contacts
					$('#crm_contact_id').empty();
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

					// Default customer email from CRM
					if (data['customer'] && data['customer'].email) {
						$('#customer_email').val(data['customer'].email);
					}

					$('#client-unit-select').val($('#client-unit-select').data('selected')).trigger('change');
				},
				error:(data)=>{
					console.log(data);
				}
			})
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

		// Process Results Modal Handler
		$('#process-results-modal').on('show.bs.modal', function(){
			var $modal = $('#process-results-modal');
			var batch = $modal.data('batch');

			$modal.find('.proccesing-point').addClass('hidden');
			$modal.find('#initiate-process').prop('disabled', false).removeClass('disabled').html('<i class="mdi mdi-cogs"></i> Generate Report');
			
			// Initialize Select2 for report format dropdown inside modal
			if ($.fn.select2) {
				$modal.find('#report_format').select2({
					width: '100%',
					dropdownParent: $modal
				});
			}
			$modal.find('#report_format').val('').trigger('change');

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
				
				// Disable the button to prevent double-clicks
				$(this).prop('disabled', true).addClass('disabled').html('<i class="mdi mdi-loading mdi-spin"></i> Generating...');
				$modal.find('.proccesing-point').removeClass('hidden');
				
				$.ajax({
					url:"{{ route('process-raw-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0]) }}",	
					data:{
						report_format : selectedFormat,
						include_pesticide : include_pesticide,
						merge_with_attachments: merge_with_attachments,
						attachment_ids: attachment_ids.join(','),
						gcla_language: gcla_language
					},
					method:'GET',
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

		// View COA Report Modal Handler
		$('#view-coa-report').on('show.bs.modal', function(){
			var $modal = $('#view-coa-report');
			
			if ($.fn.select2) {
				$modal.find('#report_format_select_again').select2({
					width: '100%',
					dropdownParent: $modal
				});
			}
			$modal.find('#report_format_select_again').val('').trigger('change');

			$modal.find('#report_format_select_again').off('change').on('change', function () {
				if ($(this).val() === 'gcla_02' || $(this).val() === 'dcea_009') {
					$modal.find('#gcla_language_group_again').removeClass('hidden');
				} else {
					$modal.find('#gcla_language_group_again').addClass('hidden');
				}
			});
		});

		// Handle COA report generation
		$('#generate-coa-btn-again').on('click', function() {
			var reportFormat = $('#report_format_select_again').val();
			var batchId = $('input[name="batch_id"]').val();

			if (!reportFormat) {
				alert('Please select a report format');
				return;
			}

			// Generate the URL for the PDF report
			var url = '{{ route("process-pdf-report", ["batch_id" => ":batch_id", "report_format" => ":report_format"]) }}';
			url = url.replace(':batch_id', batchId);
			url = url.replace(':report_format', reportFormat);

			if (reportFormat === 'gcla_02' || reportFormat === 'dcea_009') {
				var lang = $('#gcla_language_again').val() || 'sw';
				url += '?gcla_language=' + lang;
			}

			// Open the PDF in a new window/tab
			window.open(url, '_blank');

			// Close the modal
			$('#view-coa-report').modal('hide');
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
			var resultId = $btn.attr('data-result-id') || $btn.data('result-id');
			var sampleCode = $btn.data('sample-code');
			var analyte = $btn.data('analyte');

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
							$('#settings_analyst_id').val(data.analyst_id || '');
							$('#settings_reporting_symbol').val(data.reporting_symbol || '');
							$('#settings_accredited').prop('checked', data.accredited == 1);
							$('#settings_subcontracted').prop('checked', data.subcontracted == 1);
						}
					}
				});
			}
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

		// Edit standard modal show handler
		$(document).on('click', '.edit-standard-btn', function() {
			var $btn = $(this);
			var resultId = $btn.attr('data-result-id') || $btn.data('result-id');
			var sampleCode = $btn.data('sample-code');
			var analyte = $btn.data('analyte');

			$('#edit-standard-form')[0].reset();
			$('#standard_result_id').val(resultId || '');
			$('#standard_sample_code').val(sampleCode || '');
			$('#standard_analyte').val(analyte || '');

			var existingText = $btn.siblings('.standard-limit-text').text().trim();
			if (existingText && existingText !== 'No limit set') {
				var parts = existingText.split(' ');
				var val = parts[0];
				var type = parts.length > 1 ? parts[1].toUpperCase() : 'MAX';
				$('#standard_value').val(val);
				$('#standard_limit_type').val(type);
			}
		});

		// Save standard limit form submission
		$('#edit-standard-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
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
						var $input = $(`.result-input[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						var $editBtn = $(`.edit-standard-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						var $settingsBtn = $(`.parameter-settings-btn[data-sample-code="${sampleCode}"][data-analyte="${analyte}"]`);
						
						if (response.result_id) {
							$input.attr('data-result-id', response.result_id);
							$input.data('result-id', response.result_id);
							$editBtn.attr('data-result-id', response.result_id);
							$editBtn.data('result-id', response.result_id);
							if ($settingsBtn.length) {
								$settingsBtn.attr('data-result-id', response.result_id).data('result-id', response.result_id);
							}
						}

						if (response.standard_limit) {
							$editBtn.siblings('.standard-limit-text').text(response.standard_limit);
						}
						
						// Trigger change on result input to re-validate styling with new limit
						$input.trigger('change');
						
						alert('Standard limit saved successfully.');
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function(xhr) {
					submitBtn.prop('disabled', false).html('<i class="mdi mdi-check-circle"></i> Save Standard');
					alert('Failed to save standard limit: ' + xhr.responseText);
				}
			});
		});
	});
  </script>
@endsection
