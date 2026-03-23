@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
  <title>Worksheets | {{ $batch->batch_code }}</title>
  @include('layouts.lab.partials.lab-panel-theme-styles')
@endsection

@section('content2')
<main class="lab-panel-theme">
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
      'link' => route('view-batch-details', ['batch' => $batch->id]),
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

  <div class="container-fluid pt-4">
    <div class="row">
      <div class="col-12">
        @livewire('worksheets.worksheet-manager', ['batch' => $batch])
      </div>
    </div>
  </div>
</main>
@endsection