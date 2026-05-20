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

		/* Batch workspace — sleek tab bar (Livewire batch.tabs) */
		.batch-show-page .batch-tabs-panel .batch-nav-tabs {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 6px;
			padding: 10px 12px 12px;
			margin: 0;
			list-style: none;
			background: linear-gradient(180deg, #f1f5f9 0%, #e8eef4 100%);
			border: 1px solid #e2e8f0;
			border-bottom: 1px solid #cbd5e1;
			border-radius: 12px;
			box-shadow:
				inset 0 1px 0 rgba(255, 255, 255, 0.7),
				0 5px 18px -6px rgba(15, 23, 42, 0.12),
				0 2px 8px -4px rgba(15, 23, 42, 0.06);
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-item {
			margin: 0;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 10px 14px;
			border-radius: 8px;
			border: 1px solid transparent;
			color: #475569;
			font-size: 0.8125rem;
			font-weight: 600;
			line-height: 1.25;
			text-decoration: none;
			background: transparent;
			transition: color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
			color: #0f172a;
			background: rgba(255, 255, 255, 0.65);
			border-color: rgba(148, 163, 184, 0.4);
		}

		.batch-show-page .batch-tabs-panel .batch-nav-tabs .nav-link.active {
			color: #0f172a;
			background: #fff;
			border-color: rgba(148, 163, 184, 0.45);
			box-shadow:
				0 2px 8px rgba(15, 23, 42, 0.07),
				0 1px 2px rgba(15, 23, 42, 0.04);
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
                    <input name="batch_id" type="hidden" value="{{ $batch->id }}" />
                    <div class="form-group">
                        <label class="control-label">Type</label>
                        <select class="form-control" name="type" required placeholder="Message Type...">
                            <option></option>
                            @if($batch->status == 'Sample Verification' || $batch->status == 'Samples In Lab')
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
    @if(isset($batch->id) && isset($batch->status) && ($batch->status=="Sample Verification" || $batch->status=="Sample Approval"))
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
                        <select name="report_format" id="report_format" class="form-control">
                            <option value="">Choose Report Format</option>
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
			$modal.find('#report_format').val('');

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
				
				// Disable the button to prevent double-clicks
				$(this).prop('disabled', true).addClass('disabled').html('<i class="mdi mdi-loading mdi-spin"></i> Generating...');
				$modal.find('.proccesing-point').removeClass('hidden');
				
				$.ajax({
					url:"{{ route('process-raw-results', ['batch_id'=> isset($batch->id) ? $batch->id : 0]) }}",	
					data:{
						report_format : selectedFormat,
						include_pesticide : include_pesticide,
						merge_with_attachments: merge_with_attachments,
						attachment_ids: attachment_ids.join(',')
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
		})
	});
  </script>
@endsection
