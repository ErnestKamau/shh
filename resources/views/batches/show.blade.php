@extends($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['select2'=>true, 'datePicker'=>true])

@section('title2')
  <title> {{ isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch" }}</title>
  {{-- Include all CSS from original show.blade.php lines 5-431 --}}
  <style>
  {{-- Copy all styles from original file --}}
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
    
    {{-- Include all modals from original show.blade.php --}}
    {{-- This section would need to include all modal definitions from the original file --}}
    
  </main>
@endsection

@section('script2')
  {{-- Include all JavaScript from original show.blade.php --}}
  {{-- This section would include all existing JS functionality --}}
@endsection
