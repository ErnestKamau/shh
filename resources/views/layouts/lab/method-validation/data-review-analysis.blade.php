@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
  <title>Data Review & Analysis - Method Validation</title>
@endsection

@section('content2')
  <main class="lab-surface-theme ls-admin-page" data-ls-type="plex">
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('method-validation.data-review'),
          'name' => 'Data Review & Analysis',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="px-0 pt-2 pb-3">
      <i class="mdi mdi-chart-line"></i> Data Review & Analysis
      <span class="text-muted small">- Analyze validation data and generate reports</span>
    </h2>
    
    <div class="container-fluid px-0">
      @livewire('lab.method-validation.data-review-analysis')
    </div>
  </main>
@endsection

