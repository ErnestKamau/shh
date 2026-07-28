@extends('layouts.inventory.layout.app')

@section('title2')
  <title>{{ __('inventory.dashboard') }} | {{ __('inventory.module_name') }}</title>
  <style type="text/css">
    #activity-graph {
      height: 250px;
    }
  </style>
@endsection

@section('content2')
  <main>
    <h2 class="px-0 pt-2 pb-3 inventory-page-header">
      <i class="mdi mdi-desktop-mac-dashboard"></i> {{ __('inventory.dashboard') }}
      <small class="badge badge-pill bg-white my-small-text"><i class="mdi mdi-hammer-wrench"></i> {{ __('inventory.in_development') }}</small>
    </h2>

    <div class="stat-cards-row">
      <div class="stat-card">
        <div class="stat-card-label">{{ __('inventory.categories') }}</div>
        <div class="stat-card-content">
          <div class="stat-card-value">{{ number_format($categoriesNo) }}</div>
          <div class="stat-card-icon"><i class="mdi mdi-format-list-bulleted-type"></i></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">{{ __('inventory.suppliers') }}</div>
        <div class="stat-card-content">
          <div class="stat-card-value">{{ number_format($suppliers) }}</div>
          <div class="stat-card-icon"><i class="mdi mdi-account-group"></i></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">{{ __('inventory.departments') }}</div>
        <div class="stat-card-content">
          <div class="stat-card-value">{{ number_format($departments) }}</div>
          <div class="stat-card-icon"><i class="mdi mdi-home-group"></i></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">{{ __('inventory.restock_notifications') }}</div>
        <div class="stat-card-content">
          <div class="stat-card-value">{{ number_format(getRestockNotifications(false, true)) }}</div>
          <div class="stat-card-icon"><i class="mdi mdi-bell-ring-outline"></i></div>
        </div>
      </div>
    </div>

    <div class="workflow-board-panel">
      <div class="workflow-board-panel-header">
        <h5>
          <i class="mdi mdi-chart-line"></i> {{ __('inventory.inventory_activity') }}
          <small class="text-muted ml-2"><i class="mdi mdi-calendar"></i> {{ __('inventory.last_30_days') }}</small>
        </h5>
      </div>
      <div class="workflow-board-panel-body">
        <div id="activity-graph" data-json='{{ json_encode($activity) }}'></div>
      </div>
    </div>
  </main>
@endsection

@section('script2')
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
  <script>
    $(function () {
      Morris.Bar({
        element: 'activity-graph',
        data: $('#activity-graph').data('json'),
        xkey: 'category',
        barColors: ['#800000', '#94a3b8'],
        ykeys: ['stock_in', 'stock_out'],
        labels: ['Stock In', 'Stock Out']
      });
    });
  </script>
@endsection
