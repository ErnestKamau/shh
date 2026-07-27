@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
  <title>Uncertainty Budgets</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
          'link' => route('uncertainty-budgets.index'),
          'name' => 'Uncertainty Budgets',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="px-0 pt-2 pb-3">
      <i class="mdi mdi-calculator"></i> Uncertainty Budgets
      <span class="text-muted small">- Manage measurement uncertainty budgets</span>
    </h2>
    
    <div class="container-fluid px-0">
      @livewire('uncertainty-budgets-table')
    </div>
  </main>

@endsection
