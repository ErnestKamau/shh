@extends('layouts.inventory.layout.app')

@php
  $locationLabel = $locationName ?: inventoryLabel('select_location', 'Select Location');
  $ops = $ops ?? [];
  $pipeline = collect($ops['pipeline'] ?? []);
  $expiring = collect($ops['expiring'] ?? []);
  $lowStock = collect($ops['low_stock'] ?? []);
  $recentLots = collect($ops['recent_lots'] ?? []);
  $storesList = collect($ops['stores'] ?? []);
  $openTakes = collect($ops['open_stock_takes'] ?? []);
  $recentRequests = collect($ops['recent_requests'] ?? []);
  $pendingCount = (int) ($pendingApprovalsCount ?? 0);
  $onHand = (int) ($ops['on_hand_lots'] ?? 0);
  $lowStockCount = (int) ($ops['low_stock_count'] ?? 0);
  $expiringCount = (int) ($ops['expiring_lots'] ?? 0);
  $expiredCount = (int) ($ops['expired_lots'] ?? 0);
  $openPurchaseCount = (int) data_get($pipeline->firstWhere('stage', 'Purchase Request'), 'count', 0);

  $currentFilters = $filters ?? [
    'range' => '1m',
    'start_date' => '',
    'end_date' => '',
    'category_id' => '',
    'store_id' => '',
    'department_id' => '',
  ];
  $activeRange = $currentFilters['range'] ?? '1m';
  $trendChartData = $trend_chart ?? [
    'labels' => [],
    'stock_in' => [],
    'stock_out' => [],
    'net_movement' => [],
    'cumulative' => [],
    'tooltips' => [],
    'timeline' => [],
    'granularity' => 'weekly',
    'granularity_label' => inventoryLabel('granularity_weekly', 'Week-by-Week (1 Month)'),
  ];
  $periodMetrics = $metrics ?? [
    'total_stock_in' => 0,
    'total_stock_out' => 0,
    'net_movement' => 0,
    'transactions_count' => 0,
    'active_items_count' => 0,
    'range_preset' => $activeRange,
    'granularity' => 'weekly',
    'granularity_label' => inventoryLabel('granularity_weekly', 'Week-by-Week (1 Month)'),
    'period_description' => inventoryLabel('period_1m', 'Last 30 Days (1 Month)'),
    'start_formatted' => '',
    'end_formatted' => '',
  ];
  $topItems = collect($top_moving_items ?? []);
  $categoryBreakdownList = collect($category_breakdown ?? []);
  $storeBreakdownList = collect($store_breakdown ?? []);
  $categoriesSelect = collect($categories ?? []);
  $storesSelect = collect($stores ?? []);
  $departmentsSelect = collect($departmentsList ?? []);
  $hasChartActivity = (bool) ($has_activity ?? false);
@endphp

@section('title2')
  <title>{{ inventoryLabel('dashboard', 'Dashboard') }} | {{ inventoryLabel('module_name', 'Inventory Management') }}</title>
  <style type="text/css">
    .inv-dash-container {
      display: flex;
      flex-direction: column;
      gap: 1.15rem;
      padding-bottom: 2rem;
    }

    .inv-dash-header {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      background: var(--ls-color-surface, #ffffff);
      border: 1px solid var(--workflow-border, var(--color-border, #e2e8f0));
      border-radius: 14px;
      padding: 1.15rem 1.35rem;
      box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.08));
    }

    .inv-dash-kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--workflow-muted, #64748b);
      margin-bottom: 0.25rem;
    }

    .inv-dash-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--workflow-text-main, #0f172a);
      margin: 0;
      line-height: 1.25;
    }

    .inv-dash-subtitle {
      font-size: 0.8125rem;
      color: var(--workflow-muted, #64748b);
      margin: 0.25rem 0 0 0;
    }

    .inv-filter-toolbar {
      background: var(--ls-color-surface, #ffffff);
      border: 1px solid var(--workflow-border, var(--color-border, #e2e8f0));
      border-radius: 12px;
      padding: 0.75rem 1rem;
      box-shadow: var(--card-shadow, 0 1px 2px 0 rgb(0 0 0 / 0.05));
    }

    .inv-filter-row {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
    }

    .inv-preset-group {
      display: inline-flex;
      background: #f1f5f9;
      padding: 0.2rem;
      border-radius: 9px;
      border: 1px solid #e2e8f0;
      gap: 0.15rem;
    }

    .inv-preset-btn {
      border: none;
      background: transparent;
      padding: 0.35rem 0.75rem;
      border-radius: 7px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #475569;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      white-space: nowrap;
    }

    .inv-preset-btn:hover {
      color: var(--color-primary, #0f172a);
      background: rgba(255, 255, 255, 0.7);
    }

    .inv-preset-btn.is-active {
      background: #ffffff;
      color: var(--color-primary, #0f172a);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .inv-filter-inputs {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem;
    }

    .inv-control-select,
    .inv-control-date {
      height: 32px;
      padding: 0.2rem 0.6rem;
      font-size: 0.75rem;
      font-weight: 500;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      background-color: #ffffff;
      color: #1e293b;
      min-width: 130px;
    }

    .inv-control-select:focus,
    .inv-control-date:focus {
      outline: none;
      border-color: var(--color-primary, #2563eb);
      box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .inv-custom-dates {
      display: none;
      align-items: center;
      gap: 0.35rem;
    }

    .inv-custom-dates.is-visible {
      display: inline-flex;
    }

    /* KPI Summary Row */
    .inv-metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 0.85rem;
    }

    .inv-kpi-card {
      background: #ffffff;
      border: 1px solid var(--workflow-border, #e2e8f0);
      border-radius: 12px;
      padding: 0.9rem 1.1rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
      box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.05));
      transition: transform 0.15s ease, box-shadow 0.15s ease;
      text-decoration: none !important;
      color: inherit;
    }

    .inv-kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .inv-kpi-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      width: 4px;
      background: #94a3b8;
    }

    .inv-kpi-card.is-in::before { background: #10b981; }
    .inv-kpi-card.is-out::before { background: #ef4444; }
    .inv-kpi-card.is-net::before { background: #6366f1; }
    .inv-kpi-card.is-events::before { background: #0ea5e9; }
    .inv-kpi-card.is-onhand::before { background: #3b82f6; }
    .inv-kpi-card.is-lowstock::before { background: #f59e0b; }
    .inv-kpi-card.is-expired::before { background: #dc2626; }
    .inv-kpi-card.is-approvals::before { background: #8b5cf6; }

    .inv-kpi-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.4rem;
    }

    .inv-kpi-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .inv-kpi-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      background: #f8fafc;
      color: #475569;
    }

    .inv-kpi-card.is-in .inv-kpi-icon { background: #ecfdf5; color: #059669; }
    .inv-kpi-card.is-out .inv-kpi-icon { background: #fef2f2; color: #dc2626; }
    .inv-kpi-card.is-net .inv-kpi-icon { background: #eef2ff; color: #4f46e5; }
    .inv-kpi-card.is-events .inv-kpi-icon { background: #f0f9ff; color: #0284c7; }
    .inv-kpi-card.is-onhand .inv-kpi-icon { background: #eff6ff; color: #2563eb; }
    .inv-kpi-card.is-lowstock .inv-kpi-icon { background: #fffbeb; color: #d97706; }
    .inv-kpi-card.is-expired .inv-kpi-icon { background: #fef2f2; color: #b91c1c; }
    .inv-kpi-card.is-approvals .inv-kpi-icon { background: #f5f3ff; color: #7c3aed; }

    .inv-kpi-value {
      font-size: 1.4rem;
      font-weight: 700;
      color: #0f172a;
      line-height: 1.2;
    }

    .inv-kpi-sub {
      font-size: 0.75rem;
      color: #64748b;
      margin-top: 0.2rem;
    }

    /* Primary Trend Panel */
    .inv-chart-panel {
      background: #ffffff;
      border: 1px solid var(--workflow-border, #e2e8f0);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.05));
    }

    .inv-chart-panel-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.9rem 1.25rem;
      border-bottom: 1px solid var(--workflow-border, #e2e8f0);
      background: #fafafa;
    }

    .inv-chart-panel-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #0f172a;
      margin: 0;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
    }

    .inv-chart-panel-meta {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.65rem;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .inv-chart-body {
      padding: 1.25rem 1.25rem 0.85rem;
      position: relative;
    }

    .inv-chart-canvas-wrap {
      position: relative;
      height: 330px;
      width: 100%;
    }

    .inv-chart-footer-stats {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 1.5rem;
      padding: 0.75rem 1.25rem;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      font-size: 0.75rem;
    }

    .inv-chart-stat-item {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      color: #475569;
    }

    .inv-chart-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
    }

    .inv-chart-dot.is-in { background: #10b981; }
    .inv-chart-dot.is-out { background: #ef4444; }
    .inv-chart-dot.is-net { background: #6366f1; }
    .inv-chart-dot.is-cum { background: #f59e0b; }

    /* Secondary Split Grid */
    .inv-dual-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
      gap: 1rem;
    }

    .inv-sub-panel {
      background: #ffffff;
      border: 1px solid var(--workflow-border, #e2e8f0);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.05));
    }

    .inv-sub-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }

    .inv-sub-panel-header h6 {
      font-size: 0.85rem;
      font-weight: 700;
      color: #0f172a;
      margin: 0;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .inv-sub-panel-link {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-primary, #2563eb);
      text-decoration: none;
    }

    .inv-sub-panel-body {
      padding: 0;
    }

    .inv-table {
      width: 100%;
      margin: 0;
      border-collapse: collapse;
      font-size: 0.78rem;
    }

    .inv-table th {
      background: #f8fafc;
      padding: 0.5rem 0.75rem;
      font-size: 0.6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #64748b;
      border-bottom: 1px solid #e2e8f0;
    }

    .inv-table td {
      padding: 0.6rem 0.75rem;
      border-bottom: 1px solid #f1f5f9;
      color: #1e293b;
      vertical-align: middle;
    }

    .inv-table tr:last-child td {
      border-bottom: none;
    }

    .inv-table tr:hover td {
      background-color: #f8fafc;
    }

    /* Attention Item List */
    .inv-attention-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      border-bottom: 1px solid #f1f5f9;
      text-decoration: none !important;
      color: inherit;
      transition: background 0.1s ease;
    }

    .inv-attention-item:last-child {
      border-bottom: none;
    }

    .inv-attention-item:hover {
      background-color: #f8fafc;
    }

    .inv-attention-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .inv-attention-dot.is-danger { background: #ef4444; }
    .inv-attention-dot.is-warning { background: #f59e0b; }
    .inv-attention-dot.is-info { background: #3b82f6; }
    .inv-attention-dot.is-action { background: #10b981; }

    /* Category 2-Column Grid (Horizontal 2 at a time) */
    .inv-category-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.75rem;
      padding: 0.85rem 1rem;
    }

    @media (max-width: 767.98px) {
      .inv-category-grid {
        grid-template-columns: 1fr;
      }
    }

    .inv-category-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      transition: all 0.15s ease;
    }

    .inv-category-card:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .inv-attention-copy {
      flex: 1;
      min-width: 0;
    }

    .inv-attention-copy strong {
      display: block;
      font-size: 0.8125rem;
      font-weight: 600;
      color: #0f172a;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .inv-attention-copy small {
      display: block;
      font-size: 0.7rem;
      color: #64748b;
      margin-top: 0.1rem;
    }

    .inv-empty-state {
      padding: 2.5rem 1rem;
      text-align: center;
      color: #64748b;
    }

    .inv-empty-state i {
      font-size: 2.5rem;
      opacity: 0.4;
      display: block;
      margin-bottom: 0.5rem;
    }

    .inv-empty-state h6 {
      font-size: 0.875rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 0.25rem;
    }

    .inv-empty-state p {
      font-size: 0.75rem;
      max-width: 380px;
      margin: 0 auto;
    }

    /* Loading overlay */
    .inv-loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(1px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10;
      border-radius: 12px;
    }

    .inv-loading-overlay.is-active {
      display: flex;
    }

    @media (max-width: 767.98px) {
      .inv-chart-canvas-wrap {
        height: 250px;
      }
      .inv-metrics-grid {
        grid-template-columns: 1fr;
      }
      .inv-dual-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endsection

@section('content2')
  <main class="inv-dash-container">
    {{-- Header Section --}}
    <header class="inv-dash-header">
      <div>
        <p class="inv-dash-kicker">
          <i class="mdi mdi-map-marker-outline"></i>
          <span id="dash-location-name">{{ $locationLabel }}</span>
        </p>
        <h1 class="inv-dash-title">{{ inventoryLabel('dashboard', 'Inventory Dashboard') }}</h1>
        <p class="inv-dash-subtitle">{{ inventoryLabel('dashboard_subtitle', 'Dynamic trend line analytics, inventory metrics, and operational health') }}</p>
      </div>

      <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
        <span class="badge badge-pill badge-light border px-3 py-2" id="active-granularity-badge" style="font-size: 0.78rem;">
          <i class="mdi mdi-chart-timeline-variant text-primary mr-1"></i>
          <strong id="active-granularity-text">{{ $periodMetrics['granularity_label'] ?? 'Weekly Trend' }}</strong>
        </span>
        <span class="badge badge-pill badge-primary px-3 py-2" id="active-period-badge" style="font-size: 0.78rem;">
          <i class="mdi mdi-calendar-range mr-1"></i>
          <span id="active-period-text">{{ $periodMetrics['period_description'] ?? 'Last 30 Days' }}</span>
        </span>
      </div>
    </header>

    {{-- Filter Toolbar --}}
    <section class="inv-filter-toolbar">
      <form id="inventoryFilterForm" method="GET" action="{{ route('inventory-home') }}" class="inv-filter-row">
        {{-- Preset Range Buttons --}}
        <div class="inv-preset-group" role="group" aria-label="Date Range Presets">
          <button type="button" class="inv-preset-btn {{ $activeRange === '24h' ? 'is-active' : '' }}" data-range="24h">
            <i class="mdi mdi-clock-outline"></i> 24 Hours
          </button>
          <button type="button" class="inv-preset-btn {{ $activeRange === '7d' ? 'is-active' : '' }}" data-range="7d">
            <i class="mdi mdi-calendar-week"></i> 7 Days
          </button>
          <button type="button" class="inv-preset-btn {{ $activeRange === '1m' ? 'is-active' : '' }}" data-range="1m">
            <i class="mdi mdi-calendar-month"></i> 1 Month
          </button>
          <button type="button" class="inv-preset-btn {{ $activeRange === '1y' ? 'is-active' : '' }}" data-range="1y">
            <i class="mdi mdi-calendar-text"></i> 1 Year
          </button>
          <button type="button" class="inv-preset-btn {{ $activeRange === 'custom' ? 'is-active' : '' }}" data-range="custom">
            <i class="mdi mdi-tune-variant"></i> Custom
          </button>
        </div>
        <input type="hidden" name="range" id="filter_range" value="{{ $activeRange }}">

        {{-- Dropdowns & Date Controls --}}
        <div class="inv-filter-inputs">
          {{-- Custom Date Pickers --}}
          <div class="inv-custom-dates {{ $activeRange === 'custom' ? 'is-visible' : '' }}" id="custom-date-container">
            <input type="date" name="start_date" id="filter_start_date" class="inv-control-date" value="{{ $currentFilters['start_date'] ?? '' }}" placeholder="From">
            <span class="text-muted">–</span>
            <input type="date" name="end_date" id="filter_end_date" class="inv-control-date" value="{{ $currentFilters['end_date'] ?? '' }}" placeholder="To">
          </div>

          {{-- Category Filter --}}
          <select name="category_id" id="filter_category_id" class="inv-control-select">
            <option value="">{{ inventoryLabel('all_categories', 'All Categories') }}</option>
            @foreach($categoriesSelect as $cat)
              <option value="{{ $cat->id }}" {{ ($currentFilters['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>

          {{-- Store Filter --}}
          <select name="store_id" id="filter_store_id" class="inv-control-select">
            <option value="">{{ inventoryLabel('all_stores', 'All Stores') }}</option>
            @foreach($storesSelect as $st)
              <option value="{{ $st->id }}" {{ ($currentFilters['store_id'] ?? '') === (string)$st->id ? 'selected' : '' }}>
                {{ $st->name }}
              </option>
            @endforeach
          </select>

          {{-- Department Filter --}}
          @if($departmentsSelect->isNotEmpty())
            <select name="department_id" id="filter_department_id" class="inv-control-select">
              <option value="">{{ inventoryLabel('all_departments', 'All Departments') }}</option>
              @foreach($departmentsSelect as $dept)
                <option value="{{ $dept->id }}" {{ ($currentFilters['department_id'] ?? '') === (string)$dept->id ? 'selected' : '' }}>
                  {{ $dept->name }}
                </option>
              @endforeach
            </select>
          @endif

          <button type="submit" class="btn btn-primary btn-sm px-3" id="btnApplyFilter">
            <i class="mdi mdi-filter"></i> {{ inventoryLabel('filter', 'Filter') }}
          </button>
          <a href="{{ route('inventory-home') }}" class="btn btn-outline-secondary btn-sm" id="btnResetFilter" title="Reset filters" data-toggle="tooltip" data-placement="top" aria-label="Reset filters">
            <i class="mdi mdi-refresh"></i>
          </a>
        </div>
      </form>
    </section>

    {{-- Period Filtered KPI Metrics Row --}}
    <section class="inv-metrics-grid" id="inv-kpi-section">
      {{-- Total Stock In in Period --}}
      <div class="inv-kpi-card is-in">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('stock_in', 'Stock In') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-tray-arrow-down"></i></span>
        </div>
        <div class="inv-kpi-value" id="kpi-total-in">{{ number_format($periodMetrics['total_stock_in'] ?? 0, 2) }}</div>
        <div class="inv-kpi-sub" id="kpi-in-sub">{{ inventoryLabel('received_in_period', 'Received in selected range') }}</div>
      </div>

      {{-- Total Stock Out in Period --}}
      <div class="inv-kpi-card is-out">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('stock_out', 'Stock Out') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-tray-arrow-up"></i></span>
        </div>
        <div class="inv-kpi-value" id="kpi-total-out">{{ number_format($periodMetrics['total_stock_out'] ?? 0, 2) }}</div>
        <div class="inv-kpi-sub" id="kpi-out-sub">{{ inventoryLabel('issued_in_period', 'Issued in selected range') }}</div>
      </div>

      {{-- Net Movement in Period --}}
      <div class="inv-kpi-card is-net">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('net_movement', 'Net Movement') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-swap-vertical"></i></span>
        </div>
        <div class="inv-kpi-value" id="kpi-net-movement">
          @php $net = (float)($periodMetrics['net_movement'] ?? 0); @endphp
          {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
        </div>
        <div class="inv-kpi-sub" id="kpi-net-sub">{{ inventoryLabel('stock_in_minus_out', 'Stock In − Stock Out') }}</div>
      </div>

      {{-- Total Transactions in Period --}}
      <div class="inv-kpi-card is-events">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('transactions', 'Movements') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-pulse"></i></span>
        </div>
        <div class="inv-kpi-value" id="kpi-transactions-count">{{ number_format($periodMetrics['transactions_count'] ?? 0) }}</div>
        <div class="inv-kpi-sub" id="kpi-events-sub">{{ inventoryLabel('lots_transacted', 'Lot events in range') }}</div>
      </div>

      {{-- On-Hand Lots (Store Status) --}}
      <a href="{{ route('inventory-stores') }}" class="inv-kpi-card is-onhand">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('on_hand_lots', 'On-Hand Lots') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-package-variant-closed"></i></span>
        </div>
        <div class="inv-kpi-value">{{ number_format($onHand) }}</div>
        <div class="inv-kpi-sub">{{ inventoryLabel('available_in_store', 'Available in store') }}</div>
      </a>

      {{-- Low Stock Count --}}
      <a href="{{ route('inventory-categories') }}" class="inv-kpi-card is-lowstock">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('low_stock', 'Low Stock') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-arrow-down-bold-circle-outline"></i></span>
        </div>
        <div class="inv-kpi-value">{{ number_format($lowStockCount) }}</div>
        <div class="inv-kpi-sub">{{ inventoryLabel('below_reorder', 'Below reorder level') }}</div>
      </a>

      {{-- Expiring Lots --}}
      <a href="{{ route('inventory-categories') }}" class="inv-kpi-card is-expired">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('expiring_soon', 'Expiring < 30d') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-timer-sand"></i></span>
        </div>
        <div class="inv-kpi-value">{{ number_format($expiringCount) }}</div>
        <div class="inv-kpi-sub">{{ inventoryLabel('shelf_life_alert', 'Lots near expiration') }}</div>
      </a>

      {{-- Pending Approvals --}}
      <a href="{{ route('my-approvals') }}" class="inv-kpi-card is-approvals">
        <div class="inv-kpi-header">
          <span class="inv-kpi-label">{{ inventoryLabel('approval_requests', 'Approvals') }}</span>
          <span class="inv-kpi-icon"><i class="mdi mdi-draw"></i></span>
        </div>
        <div class="inv-kpi-value">{{ number_format($pendingCount) }}</div>
        <div class="inv-kpi-sub">{{ inventoryLabel('awaiting_you', 'Awaiting your approval') }}</div>
      </a>
    </section>

    {{-- Purchase Pipeline Navigation --}}
    @if($pipeline->isNotEmpty())
      <nav class="inventory-pipeline" aria-label="{{ inventoryLabel('purchase_pipeline', 'Purchase pipeline') }}">
        @foreach($pipeline as $step)
          <a href="{{ route('go_to_stage', ['stage' => $step['stage']]) }}" class="inventory-pipeline-step {{ ($step['count'] ?? 0) > 0 ? 'has-work' : '' }}">
            <span class="inventory-pipeline-count">{{ number_format($step['count'] ?? 0) }}</span>
            <span class="inventory-pipeline-label">{{ $step['label'] }}</span>
          </a>
        @endforeach
      </nav>
    @endif

    {{-- Quick Action Buttons --}}
    <nav class="inventory-quick-actions" aria-label="{{ inventoryLabel('quick_actions', 'Quick actions') }}">
      <a href="{{ route('go_to_stage', ['stage' => 'Purchase Request']) }}">
        <i class="mdi mdi-cart-plus"></i>
        <span>{{ inventoryLabel('new_purchase_request', 'Purchase request') }}</span>
      </a>
      <a href="{{ route('go_to_stage', ['stage' => 'Request to Store']) }}">
        <i class="mdi mdi-inbox-arrow-down"></i>
        <span>{{ inventoryLabel('request_to_store', 'Request to Store') }}</span>
      </a>
      <a href="{{ route('inventory-stores') }}">
        <i class="mdi mdi-warehouse"></i>
        <span>{{ inventoryLabel('store', 'Store') }}</span>
      </a>
      <a href="{{ route('stock-taking-list') }}">
        <i class="mdi mdi-clipboard-check-outline"></i>
        <span>{{ inventoryLabel('stock_taking', 'Stock Taking') }}</span>
      </a>
      <a href="{{ route('inventory-activity') }}">
        <i class="mdi mdi-chart-areaspline"></i>
        <span>{{ inventoryLabel('inventory_movement', 'Inventory Movement') }}</span>
      </a>
    </nav>

    {{-- PRIMARY VISUALIZATION: TIME-SERIES LINE/TREND CHART ONLY --}}
    <section class="inv-chart-panel position-relative">
      <div class="inv-loading-overlay" id="chart-loading-overlay">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Loading...</span>
        </div>
      </div>

      <div class="inv-chart-panel-header">
        <h5 class="inv-chart-panel-title">
          <i class="mdi mdi-chart-areaspline text-primary"></i>
          {{ inventoryLabel('inventory_trends', 'Inventory Movement Trends') }}
          <small class="text-muted ml-1" id="chart-granularity-indicator">
            · {{ $periodMetrics['granularity_label'] ?? 'Week-by-Week' }}
          </small>
        </h5>

        <div class="inv-chart-panel-meta">
          <span class="text-success font-weight-bold" id="panel-meta-in">
            <i class="mdi mdi-arrow-down mr-1"></i>In: {{ number_format($periodMetrics['total_stock_in'] ?? 0, 2) }}
          </span>
          <span class="text-danger font-weight-bold" id="panel-meta-out">
            <i class="mdi mdi-arrow-up mr-1"></i>Out: {{ number_format($periodMetrics['total_stock_out'] ?? 0, 2) }}
          </span>
          <a href="{{ route('inventory-activity') }}" class="btn btn-outline-primary btn-sm py-0 px-2 ml-2" style="font-size: 0.72rem;">
            {{ inventoryLabel('view_all_movement', 'View Activity Log') }}
            <i class="mdi mdi-arrow-right ml-1"></i>
          </a>
        </div>
      </div>

      <div class="inv-chart-body">
        <div class="inv-chart-canvas-wrap">
          <canvas id="inventoryTrendChart"></canvas>
        </div>
      </div>

      <div class="inv-chart-footer-stats">
        <span class="inv-chart-stat-item">
          <i class="inv-chart-dot is-in"></i>
          <strong>{{ inventoryLabel('stock_in', 'Stock In') }}</strong>
        </span>
        <span class="inv-chart-stat-item">
          <i class="inv-chart-dot is-out"></i>
          <strong>{{ inventoryLabel('stock_out', 'Stock Out') }}</strong>
        </span>
        <span class="inv-chart-stat-item">
          <i class="inv-chart-dot is-net"></i>
          <strong>{{ inventoryLabel('net_movement', 'Net Movement') }}</strong>
        </span>
        <span class="inv-chart-stat-item">
          <i class="inv-chart-dot is-cum"></i>
          <strong>{{ inventoryLabel('cumulative_stock', 'Cumulative Net') }}</strong>
        </span>
      </div>
    </section>

    {{-- Full-Width Panel: Top Moving Items in Period --}}
    <section class="inv-sub-panel w-100">
      <div class="inv-sub-panel-header">
        <h6>
          <i class="mdi mdi-fire text-warning"></i>
          {{ inventoryLabel('top_moving_items', 'Top Moving Items in Period') }}
        </h6>
        <a href="{{ route('inventory-categories') }}" class="inv-sub-panel-link">{{ inventoryLabel('catalog', 'Catalog') }}</a>
      </div>
      <div class="inv-sub-panel-body">
        <div class="table-responsive">
          <table class="inv-table" id="top-moving-table">
            <thead>
              <tr>
                <th>{{ inventoryLabel('item', 'Item') }}</th>
                <th>{{ inventoryLabel('category', 'Category') }}</th>
                <th class="text-right">{{ inventoryLabel('in', 'In') }}</th>
                <th class="text-right">{{ inventoryLabel('out', 'Out') }}</th>
                <th class="text-right">{{ inventoryLabel('net', 'Net') }}</th>
              </tr>
            </thead>
            <tbody id="top-moving-tbody">
              @forelse($topItems as $item)
                <tr>
                  <td>
                    <a href="{{ $item['url'] }}" class="font-weight-bold text-dark text-decoration-none">
                      {{ $item['name'] }}
                    </a>
                    @if(!empty($item['code']))
                      <small class="text-muted d-block">{{ $item['code'] }}</small>
                    @endif
                  </td>
                  <td><small class="badge badge-light border">{{ $item['category'] }}</small></td>
                  <td class="text-right text-success font-weight-bold">+{{ number_format($item['stock_in'], 2) }}</td>
                  <td class="text-right text-danger font-weight-bold">−{{ number_format($item['stock_out'], 2) }}</td>
                  <td class="text-right font-weight-bold {{ $item['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $item['net_change'] > 0 ? '+' : '' }}{{ number_format($item['net_change'], 2) }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="mdi mdi-package-variant-closed d-block mb-1" style="font-size: 1.5rem; opacity: 0.5;"></i>
                    {{ inventoryLabel('no_movement_in_period', 'No items transacted in this period.') }}
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>

    {{-- Full-Width Panel in New Line: Category Activity Breakdown (Horizontal 2 at a time) --}}
    <section class="inv-sub-panel w-100">
      <div class="inv-sub-panel-header">
        <h6>
          <i class="mdi mdi-shape-outline text-info"></i>
          {{ inventoryLabel('category_distribution', 'Category Activity Breakdown') }}
        </h6>
        <a href="{{ route('inventory-categories') }}" class="inv-sub-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
      </div>
      <div class="inv-sub-panel-body">
        <div class="inv-category-grid" id="category-breakdown-container">
          @forelse($categoryBreakdownList as $cat)
            <div class="inv-category-card">
              <div class="d-flex align-items-center" style="gap: 0.6rem;">
                <span class="inv-attention-dot is-action"></span>
                <div class="inv-attention-copy">
                  <strong>{{ $cat['name'] }}</strong>
                  <small>
                    <span class="text-success font-weight-bold">+{{ number_format($cat['stock_in'], 2) }} In</span> · 
                    <span class="text-danger font-weight-bold">−{{ number_format($cat['stock_out'], 2) }} Out</span> · 
                    {{ number_format($cat['move_count']) }} {{ inventoryLabel('movements', 'events') }}
                  </small>
                </div>
              </div>
              <span class="badge {{ $cat['net'] >= 0 ? 'badge-success' : 'badge-danger' }} badge-pill px-2 py-1">
                {{ $cat['net'] > 0 ? '+' : '' }}{{ number_format($cat['net'], 2) }}
              </span>
            </div>
          @empty
            <div class="inv-empty-state" style="grid-column: 1 / -1;">
              <i class="mdi mdi-shape-outline"></i>
              <h6>{{ inventoryLabel('no_category_activity', 'No category movements in range') }}</h6>
            </div>
          @endforelse
        </div>
      </div>
    </section>

    {{-- Split Grid 2: Operational Health Attention Lists --}}
    <div class="inv-dual-grid">
      {{-- Expiring Lots --}}
      <section class="inv-sub-panel">
        <div class="inv-sub-panel-header">
          <h6>
            <i class="mdi mdi-timer-sand text-danger"></i>
            {{ inventoryLabel('expiring_reagents', 'Expiring Lots (< 30 Days)') }}
          </h6>
          <a href="{{ route('inventory-categories') }}" class="inv-sub-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
        </div>
        <div class="inv-sub-panel-body">
          @forelse($expiring as $lot)
            <a class="inv-attention-item" href="{{ $lot['url'] }}">
              <span class="inv-attention-dot {{ ($lot['days_left'] ?? 30) <= 7 ? 'is-alert' : 'is-warn' }}"></span>
              <span class="inv-attention-copy">
                <strong>{{ $lot['name'] }}</strong>
                <small>
                  {{ $lot['expiry'] }} · 
                  <span class="{{ ($lot['days_left'] ?? 30) <= 7 ? 'text-danger font-weight-bold' : 'text-warning font-weight-bold' }}">
                    {{ $lot['days_left'] }} {{ inventoryLabel('days_left', 'days left') }}
                  </span>
                  @if(!empty($lot['qty'])) · {{ number_format($lot['qty'], 2) }} {{ $lot['unit'] }} @endif
                  @if(!empty($lot['batch'])) · Batch: {{ $lot['batch'] }} @endif
                </small>
              </span>
              <i class="mdi mdi-chevron-right text-muted"></i>
            </a>
          @empty
            <div class="inv-empty-state">
              <i class="mdi mdi-check-circle-outline text-success" style="opacity: 0.8;"></i>
              <h6>{{ inventoryLabel('no_expiring', 'No lots expiring in the next 30 days') }}</h6>
            </div>
          @endforelse
        </div>
      </section>

      {{-- Low Stock Items --}}
      <section class="inv-sub-panel">
        <div class="inv-sub-panel-header">
          <h6>
            <i class="mdi mdi-arrow-down-bold-circle-outline text-warning"></i>
            {{ inventoryLabel('low_stock_attention', 'Items Below Reorder Level') }}
          </h6>
          <a href="{{ route('inventory-categories') }}" class="inv-sub-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
        </div>
        <div class="inv-sub-panel-body">
          @forelse($lowStock as $item)
            <a class="inv-attention-item" href="{{ $item['url'] }}">
              <span class="inv-attention-dot is-alert"></span>
              <span class="inv-attention-copy">
                <strong>{{ $item['name'] }}</strong>
                <small>
                  <span class="text-danger font-weight-bold">{{ number_format($item['available'], 2) }} {{ $item['unit'] }} on hand</span> · 
                  <span>Reorder at: {{ number_format($item['minimum'], 2) }}</span>
                </small>
              </span>
              <i class="mdi mdi-chevron-right text-muted"></i>
            </a>
          @empty
            <div class="inv-empty-state">
              <i class="mdi mdi-check-circle-outline text-success" style="opacity: 0.8;"></i>
              <h6>{{ inventoryLabel('no_low_stock', 'All inventory items are above reorder levels') }}</h6>
            </div>
          @endforelse
        </div>
      </section>
    </div>

    {{-- Split Grid 3: Stores Overview & Recent Work --}}
    <div class="inv-dual-grid">
      {{-- Stores --}}
      <section class="inv-sub-panel">
        <div class="inv-sub-panel-header">
          <h6>
            <i class="mdi mdi-warehouse text-primary"></i>
            {{ inventoryLabel('stores_overview', 'Stores at this Location') }}
          </h6>
          <a href="{{ route('inventory-stores') }}" class="inv-sub-panel-link">{{ inventoryLabel('manage_stores', 'Manage Stores') }}</a>
        </div>
        <div class="inv-sub-panel-body">
          @forelse($storesList as $store)
            <a class="inv-attention-item" href="{{ $store['url'] }}">
              <span class="inv-attention-dot is-action"></span>
              <span class="inv-attention-copy">
                <strong>{{ $store['name'] }}</strong>
                <small>{{ number_format($store['lots']) }} {{ inventoryLabel('lots', 'lots') }} · {{ number_format($store['qty'], 2) }} {{ inventoryLabel('total_units', 'total units') }}</small>
              </span>
              <i class="mdi mdi-chevron-right text-muted"></i>
            </a>
          @empty
            <div class="inv-empty-state">
              <i class="mdi mdi-warehouse"></i>
              <h6>{{ inventoryLabel('no_stores', 'No stores registered at this location.') }}</h6>
            </div>
          @endforelse
        </div>
      </section>

      {{-- Open Stock Takes & Requests --}}
      <section class="inv-sub-panel">
        <div class="inv-sub-panel-header">
          <h6>
            <i class="mdi mdi-clipboard-text-outline text-secondary"></i>
            {{ inventoryLabel('open_work', 'Open Stock Takes & Requisitions') }}
          </h6>
          <a href="{{ route('stock-taking-list') }}" class="inv-sub-panel-link">{{ inventoryLabel('stock_taking', 'Stock Takes') }}</a>
        </div>
        <div class="inv-sub-panel-body">
          @if($openTakes->isNotEmpty())
            <div class="px-3 py-1 bg-light border-bottom font-weight-bold text-muted" style="font-size: 0.72rem;">
              {{ inventoryLabel('open_stock_takes', 'Active Stock Takes') }}
            </div>
            @foreach($openTakes as $taking)
              <a class="inv-attention-item" href="{{ $taking['url'] }}">
                <span class="inv-attention-dot is-action"></span>
                <span class="inv-attention-copy">
                  <strong>{{ $taking['code'] }}</strong>
                  <small>{{ $taking['status'] }} @if($taking['stores']) · {{ $taking['stores'] }} @endif · {{ $taking['when'] }}</small>
                </span>
                <i class="mdi mdi-chevron-right text-muted"></i>
              </a>
            @endforeach
          @endif

          @if($recentRequests->isNotEmpty())
            <div class="px-3 py-1 bg-light border-bottom font-weight-bold text-muted" style="font-size: 0.72rem;">
              {{ inventoryLabel('recent_requests', 'Recent Requisitions') }}
            </div>
            @foreach($recentRequests as $req)
              <a class="inv-attention-item" href="{{ $req['url'] }}">
                <span class="inv-attention-dot is-in"></span>
                <span class="inv-attention-copy">
                  <strong>{{ $req['code'] }}</strong>
                  <small>{{ $req['type'] }} @if($req['priority']) · {{ $req['priority'] }} @endif · {{ $req['when'] }}</small>
                </span>
                <i class="mdi mdi-chevron-right text-muted"></i>
              </a>
            @endforeach
          @endif

          @if($openTakes->isEmpty() && $recentRequests->isEmpty())
            <div class="inv-empty-state">
              <i class="mdi mdi-check-circle-outline text-success"></i>
              <h6>{{ inventoryLabel('no_pending_work', 'No open stock takes or recent requests.') }}</h6>
            </div>
          @endif
        </div>
      </section>
    </div>
  </main>
@endsection

@section('script2')
  {{-- Load Chart.js for Line/Trend Visualizations (NO BAR CHARTS) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    (function () {
      var trendChartInstance = null;
      var rawTrendPayload = @json($trendChartData);
      var currentMetrics = @json($periodMetrics);

      function initTrendChart(chartData) {
        var canvas = document.getElementById('inventoryTrendChart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        if (trendChartInstance) {
          trendChartInstance.destroy();
        }

        var labels = chartData.labels || [];
        var stockIn = chartData.stock_in || [];
        var stockOut = chartData.stock_out || [];
        var netMovement = chartData.net_movement || [];
        var cumulative = chartData.cumulative || [];
        var tooltips = chartData.tooltips || [];

        var gradientIn = ctx.createLinearGradient(0, 0, 0, 300);
        gradientIn.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        gradientIn.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        var gradientOut = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOut.addColorStop(0, 'rgba(239, 68, 68, 0.20)');
        gradientOut.addColorStop(1, 'rgba(239, 68, 68, 0.00)');

        var gradientNet = ctx.createLinearGradient(0, 0, 0, 300);
        gradientNet.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
        gradientNet.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

        trendChartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              {
                label: 'Stock In',
                data: stockIn,
                borderColor: '#10b981',
                backgroundColor: gradientIn,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: labels.length > 30 ? 2 : 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
              },
              {
                label: 'Stock Out',
                data: stockOut,
                borderColor: '#ef4444',
                backgroundColor: gradientOut,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: labels.length > 30 ? 2 : 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ef4444',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
              },
              {
                label: 'Net Movement',
                data: netMovement,
                borderColor: '#6366f1',
                backgroundColor: gradientNet,
                borderWidth: 2,
                borderDash: [5, 4],
                fill: false,
                tension: 0.35,
                pointRadius: labels.length > 30 ? 1.5 : 3.5,
                pointHoverRadius: 5.5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
              },
              {
                label: 'Cumulative Net',
                data: cumulative,
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                tension: 0.35,
                pointRadius: labels.length > 30 ? 1 : 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#f59e0b',
                hidden: true, // Can be toggled on demand
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                  boxWidth: 12,
                  boxHeight: 12,
                  usePointStyle: true,
                  pointStyle: 'circle',
                  font: {
                    family: 'IBM Plex Sans, system-ui, sans-serif',
                    size: 11,
                    weight: 600,
                  },
                  color: '#475569',
                  padding: 16,
                }
              },
              tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                titleFont: {
                  family: 'IBM Plex Sans, system-ui, sans-serif',
                  size: 12,
                  weight: 700,
                },
                bodyFont: {
                  family: 'IBM Plex Sans, system-ui, sans-serif',
                  size: 11,
                },
                padding: 10,
                cornerRadius: 8,
                boxPadding: 4,
                callbacks: {
                  title: function (items) {
                    if (!items.length) return '';
                    var idx = items[0].dataIndex;
                    return tooltips[idx] || items[0].label;
                  },
                  label: function (context) {
                    var val = Number(context.parsed.y || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    var label = context.dataset.label || '';
                    if (label === 'Stock In') return ' ' + label + ': +' + val;
                    if (label === 'Stock Out') return ' ' + label + ': −' + val;
                    return ' ' + label + ': ' + (context.parsed.y > 0 ? '+' : '') + val;
                  }
                }
              }
            },
            scales: {
              x: {
                grid: {
                  display: false,
                  drawBorder: false,
                },
                ticks: {
                  font: {
                    family: 'IBM Plex Sans, system-ui, sans-serif',
                    size: 11,
                  },
                  color: '#64748b',
                  maxRotation: 45,
                  minRotation: 0,
                  autoSkip: true,
                  maxTicksLimit: 14,
                }
              },
              y: {
                beginAtZero: true,
                grid: {
                  color: '#f1f5f9',
                  drawBorder: false,
                },
                ticks: {
                  font: {
                    family: 'IBM Plex Sans, system-ui, sans-serif',
                    size: 11,
                  },
                  color: '#64748b',
                  callback: function (val) {
                    return Number(val).toLocaleString();
                  }
                }
              }
            }
          }
        });
      }

      // Initialize on DOM load
      $(function () {
        initTrendChart(rawTrendPayload);

        if ($.fn.tooltip) {
          $('#btnResetFilter').tooltip({ title: 'Reset filters', placement: 'top' });
        }

        // Preset buttons click handler
        $('.inv-preset-btn').on('click', function (e) {
          e.preventDefault();
          var range = $(this).data('range');
          $('.inv-preset-btn').removeClass('is-active');
          $(this).addClass('is-active');
          $('#filter_range').val(range);

          if (range === 'custom') {
            $('#custom-date-container').addClass('is-visible');
          } else {
            $('#custom-date-container').removeClass('is-visible');
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
            fetchDashboardData();
          }
        });

        // Form submission with AJAX update
        $('#inventoryFilterForm').on('submit', function (e) {
          e.preventDefault();
          fetchDashboardData();
        });

        // Dropdown changes trigger fast refresh
        $('#filter_category_id, #filter_store_id, #filter_department_id').on('change', function () {
          fetchDashboardData();
        });

        function fetchDashboardData() {
          var form = $('#inventoryFilterForm');
          var url = '{{ route("inventory-dashboard-data") }}';
          var data = form.serialize();

          $('#chart-loading-overlay').addClass('is-active');

          $.ajax({
            url: url,
            method: 'GET',
            data: data,
            dataType: 'json',
            success: function (res) {
              $('#chart-loading-overlay').removeClass('is-active');

              if (res.trend_chart) {
                initTrendChart(res.trend_chart);
              }

              if (res.metrics) {
                var m = res.metrics;
                $('#kpi-total-in').text(Number(m.total_stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#kpi-total-out').text(Number(m.total_stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                
                var net = Number(m.net_movement || 0);
                var netFormatted = (net > 0 ? '+' : '') + net.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                $('#kpi-net-movement').text(netFormatted);

                $('#kpi-transactions-count').text(Number(m.transactions_count || 0).toLocaleString());

                if (m.granularity_label) {
                  $('#active-granularity-text').text(m.granularity_label);
                  $('#chart-granularity-indicator').text('· ' + m.granularity_label);
                }
                if (m.period_description) {
                  $('#active-period-text').text(m.period_description);
                }

                $('#panel-meta-in').html('<i class="mdi mdi-arrow-down mr-1"></i>In: ' + Number(m.total_stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#panel-meta-out').html('<i class="mdi mdi-arrow-up mr-1"></i>Out: ' + Number(m.total_stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
              }

              // Update top moving items table
              if (res.top_moving_items) {
                var tbody = $('#top-moving-tbody');
                tbody.empty();
                if (res.top_moving_items.length === 0) {
                  tbody.append('<tr><td colspan="5" class="text-center text-muted py-4"><i class="mdi mdi-package-variant-closed d-block mb-1" style="font-size: 1.5rem; opacity: 0.5;"></i>No items transacted in this period.</td></tr>');
                } else {
                  $.each(res.top_moving_items, function (i, it) {
                    var netVal = Number(it.net_change || 0);
                    var netClass = netVal >= 0 ? 'text-success' : 'text-danger';
                    var netSign = netVal > 0 ? '+' : '';
                    tbody.append(
                      '<tr>' +
                        '<td><a href="' + it.url + '" class="font-weight-bold text-dark text-decoration-none">' + it.name + '</a>' + (it.code ? '<small class="text-muted d-block">' + it.code + '</small>' : '') + '</td>' +
                        '<td><small class="badge badge-light border">' + it.category + '</small></td>' +
                        '<td class="text-right text-success font-weight-bold">+' + Number(it.stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                        '<td class="text-right text-danger font-weight-bold">−' + Number(it.stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                        '<td class="text-right font-weight-bold ' + netClass + '">' + netSign + netVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                      '</tr>'
                    );
                  });
                }
              }

              // Update category breakdown
              if (res.category_breakdown) {
                var catContainer = $('#category-breakdown-container');
                catContainer.empty();
                if (res.category_breakdown.length === 0) {
                  catContainer.append('<div class="inv-empty-state" style="grid-column: 1 / -1;"><i class="mdi mdi-shape-outline"></i><h6>No category movements in range</h6></div>');
                } else {
                  $.each(res.category_breakdown, function (i, cat) {
                    var netVal = Number(cat.net || 0);
                    var netBadgeClass = netVal >= 0 ? 'badge-success' : 'badge-danger';
                    var netSign = netVal > 0 ? '+' : '';
                    catContainer.append(
                      '<div class="inv-category-card">' +
                        '<div class="d-flex align-items-center" style="gap: 0.6rem;">' +
                          '<span class="inv-attention-dot is-action"></span>' +
                          '<div class="inv-attention-copy">' +
                            '<strong>' + cat.name + '</strong>' +
                            '<small><span class="text-success font-weight-bold">+' + Number(cat.stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' In</span> · ' +
                            '<span class="text-danger font-weight-bold">−' + Number(cat.stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Out</span> · ' +
                            Number(cat.move_count || 0) + ' events</small>' +
                          '</div>' +
                        '</div>' +
                        '<span class="badge ' + netBadgeClass + ' badge-pill px-2 py-1">' + netSign + netVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>' +
                      '</div>'
                    );
                  });
                }
              }
            },
            error: function (xhr) {
              $('#chart-loading-overlay').removeClass('is-active');
              console.error('Failed to fetch inventory dashboard data:', xhr);
            }
          });
        }
      });
    })();
  </script>
@endsection
