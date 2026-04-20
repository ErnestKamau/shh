@extends('layouts.lab.layout.app')

@section('title2')
  <title>Uncertainty Budgets</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content2')
  <main>
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
    <h2 class="p-4">
      <i class="mdi mdi-calculator"></i> Uncertainty Budgets
      <span class="text-muted small">- Manage measurement uncertainty budgets</span>
    </h2>
    
    <div class="container-fluid">
      @livewire('uncertainty-budgets-table')
    </div>
  </main>

@endsection
