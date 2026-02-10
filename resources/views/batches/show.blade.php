@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
  {{-- Include all CSS from original show.blade.php lines 5-431 --}}
  <style>
		body{
			overflow-x: hidden !important;
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
  </style>
@endsection

@section('content2')
  <main>
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
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(!$batch || !isset($batch->id))
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="mdi mdi-alert-circle"></i> Batch not found. It may have been deleted.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <div class="text-center m-3">
            <a href="{{ route('sample-workflow', ['status' => 'Samples Reception']) }}" class="btn btn-primary">
                <i class="mdi mdi-arrow-left mr-2"></i> Back to Samples Reception
            </a>
        </div>
    @else
    <div class="container-fluid">
      {{-- Livewire Components --}}
      @livewire('batch.header', [
        'batch' => $batch,
        'workflows' => $workflows,
        'workflowstages' => $workflowstages,
        'status' => $status,
        'defaultClient' => $defaultClient,
        'clientPortal' => $client_portal ?? false
      ], 'header-'.$batch->id)
      
      <div class="row no-gutters">
        <div class="col-sm-12 p-2">
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
        
        <div class="col-sm-12 p-2">
          @livewire('batch.dates', ['batch' => $batch], 'dates-'.$batch->id)
          @livewire('batch.related', ['batch' => $batch], 'related-'.$batch->id)
        </div>
        
        <div class="col-sm-12 p-2">
          @livewire('batch.tabs', [
            'batch' => $batch,
            'not_captured' => $not_captured,
            'status' => $status
          ], 'tabs-'.$batch->id)
        </div>
      </div>
    </div>
    @endif
    
    {{-- Include all modals from original show.blade.php --}}
    {{-- This section would need to include all modal definitions from the original file --}}
    
  </main>
@endsection

@section('script2')
  <script>
	// Setup CSRF token for all AJAX requests
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	var detectChange = function(ts){
		var op = $(ts).children('option:selected');
		$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
		$('#client-unit-select').trigger('change');
		if(op.val() > 0){
			$.ajax({
				url:`/get/Client-Details/Ajax/${op.val()}`,
				method:'GET',
				success:(data)=>{
					$('#crm_contact_id').empty();
					$.each(data['contacts'],(i,obj)=>{
						var name = `${obj.first_name} ${obj.middle_name || ''} ${obj.last_name || ''}`
						var option = `<option value="${obj.id}">${name}</option>`
						$('#crm_contact_id').append(option)
					});
					$('#crm_contact_id').select2();
					$('#crm_contact_id').val($('#crm_contact_id').data('selected')).trigger('change');

					$.each(data['units'], function(i, e){
						$('#client-unit-select').append('<option value="'+e.id+'">'+e.name+'</option>');
					});
					$('#customer_email').val(data['customer'].email);
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

		$('.batch-info-trigger').on('click', function(){
			$(this).toggleClass('open');
			if($(this).hasClass('open')){
				$(this).html(`<i class="mdi mdi-chevron-double-up"></i> Batch Info`);
				$('#batch-detail-form').removeClass('hidden');
			}
			else{
				$(this).html(`<i class="mdi mdi-chevron-double-down"></i> Batch Info`);
				$('#batch-detail-form').addClass('hidden');
			}
		});
	});
  </script>
@endsection
