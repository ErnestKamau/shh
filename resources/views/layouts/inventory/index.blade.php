@extends('layouts.inventory.layout.app', ['dataTable' => true, 'select2' => true])

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
  $openStoreReqCount = (int) data_get($pipeline->firstWhere('stage', 'Request to Store'), 'count', $recentRequests->count());

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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <style type="text/css">
    :root {
      --primary-glass: #ffffff;
      --accent-blue: var(--color-primary, #1e3a8a);
      --accent-green: #10b981;
      --accent-red: #ef4444;
      --accent-orange: #f59e0b;
      --bg-color: #f8fafc;
      --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
    }

    .inventory-dashboard-page {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
      padding-bottom: 2.5rem;
    }

    .bento-card {
      background: var(--primary-glass);
      border-radius: 12px;
      border: 1px solid rgba(0, 0, 0, 0.06);
      box-shadow: var(--card-shadow);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      overflow: hidden;
      position: relative;
    }

    .bento-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .pipeline-card {
      padding: var(--space-md, 1rem);
      text-align: center;
      position: relative;
      cursor: pointer;
      text-decoration: none !important;
      color: inherit;
    }

    .pipeline-arrow {
      position: absolute;
      right: -15px;
      top: 50%;
      transform: translateY(-50%);
      font-size: var(--text-xl, 1.25rem);
      color: #cbd5e1;
      z-index: 10;
    }

    .stat-value {
      font-size: var(--text-metric, 1.85rem);
      font-weight: var(--font-bold, 700);
      line-height: var(--leading-tight, 1.2);
      margin: 0.4rem 0;
      color: var(--color-text, #0f172a);
    }

    .stat-label {
      font-size: var(--text-caption, 0.75rem);
      font-weight: var(--font-semibold, 600);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--color-primary, #0f172a);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
    }

    .stat-subtext {
      font-size: var(--text-caption, 0.75rem);
      color: var(--color-muted, #64748b);
      margin-top: 0.25rem;
    }

    .section-header {
      padding: 0.85rem 1.15rem;
      border-bottom: 1px solid var(--color-border, #e2e8f0);
      font-size: var(--text-base, 0.95rem);
      font-weight: var(--font-semibold, 600);
      color: var(--color-text, #0f172a);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fafafa;
    }

    .section-body {
      padding: 1.15rem;
    }

    .method-list-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f1f5f9;
      gap: 0.75rem;
    }

    .method-list-item:last-child {
      border-bottom: none;
    }

    .method-name {
      font-weight: var(--font-semibold, 600);
      color: #1e293b;
      font-size: var(--text-sm, 0.85rem);
    }

    .method-bar-bg {
      height: 6px;
      background: #e2e8f0;
      border-radius: 3px;
      width: 120px;
      overflow: hidden;
      flex-shrink: 0;
    }

    .method-bar-fill {
      height: 100%;
      border-radius: 3px;
      background-color: var(--color-primary, #2563eb);
      transition: width 0.3s ease;
    }

    .pulse-dot {
      height: 10px;
      width: 10px;
      border-radius: 50%;
      display: inline-block;
      animation: pulse 2.5s infinite;
    }

    .pulse-red { background-color: var(--accent-red); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    .pulse-yellow { background-color: var(--accent-orange); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    .pulse-green { background-color: var(--accent-green); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    .pulse-blue { background-color: var(--color-primary); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }

    @keyframes pulse {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color, 37, 99, 235), 0.5); }
      70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(var(--box-color, 37, 99, 235), 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color, 37, 99, 235), 0); }
    }

    .nav-tabs.modern-tabs {
      border-bottom: 2px solid #e2e8f0;
    }

    .nav-tabs.modern-tabs .nav-link {
      border: none;
      color: var(--color-muted, #64748b);
      font-size: var(--text-sm, 0.85rem);
      font-weight: var(--font-semibold, 600);
      padding: 12px 20px;
      position: relative;
      background: transparent;
      cursor: pointer;
    }

    .nav-tabs.modern-tabs .nav-link.active {
      color: var(--color-primary, #0f172a);
      background: transparent;
    }

    .nav-tabs.modern-tabs .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      right: 0;
      height: 2px;
      background: var(--color-primary, #0f172a);
    }

    .smart-table th {
      background: #f8fafc;
      font-size: var(--text-caption, 0.72rem);
      font-weight: var(--font-semibold, 600);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--color-muted, #64748b);
      border-top: none;
      padding: 0.65rem 0.85rem;
    }

    .smart-table td {
      vertical-align: middle;
      font-size: var(--text-sm, 0.8125rem);
      font-weight: var(--font-medium, 500);
      color: var(--color-text, #1e293b);
      border-color: #f1f5f9;
      padding: 0.7rem 0.85rem;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    /* Filter Toolbar */
    .inv-filter-toolbar {
      padding: 0.75rem 1rem;
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

    .inv-chart-canvas-wrap {
      position: relative;
      height: 310px;
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

    .inv-chart-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 0.35rem;
    }

    .inv-chart-dot.is-in { background: #10b981; }
    .inv-chart-dot.is-out { background: #ef4444; }
    .inv-chart-dot.is-net { background: #6366f1; }
    .inv-chart-dot.is-cum { background: #f59e0b; }

    .inv-loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(2px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 20;
      border-radius: 12px;
    }

    .inv-loading-overlay.is-active {
      display: flex;
    }
  </style>
@endsection

@section('content2')
<main>
  <?php
    $items = array(
      array('link' => null, 'name' => inventoryLabel('dashboard', 'Dashboard'), 'icon' => null)
    );
  ?>
  <x-bread-crumb :items="$items"></x-bread-crumb>
  <div data-sf-slot="after_breadcrumb"></div>
  @include('layouts.partials.dashboard-page-styles')

  <div class="p-4 lab-dashboard-page inventory-dashboard-page workflow-theme">
    {{-- WELCOME HERO BANNER (MATCHING LAB DASHBOARD STANDARD) --}}
    <div class="dashboard-welcome-hero">
      <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 1rem;">
        <div>
          <h3 class="mb-1">{{ __('dashboard.welcome_back') }}, {{ explode(' ', Auth::user()->name)[0] }} 👋</h3>
          <p class="text-muted mb-0 dashboard-welcome-subtitle">
            {{ \Carbon\Carbon::now()->format('l, jS F Y') }} &mdash; {{ inventoryLabel('overview_of_inventory_operations', 'Overview of Inventory Operations & Movements') }}
            · <i class="mdi mdi-map-marker"></i> <strong id="dash-location-name">{{ $locationLabel }}</strong>
          </p>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 0.6rem;">
          {{-- Low Stock Hero Chip --}}
          @if($lowStockCount > 0)
            <div class="bento-card px-3 py-2 d-flex align-items-center cursor-pointer" onclick="$('#tab-low-stock-link').click(); scrollToWorkspace();">
              <span class="pulse-dot pulse-red mr-2" style="--box-color: 239, 68, 68;"></span>
              <span class="font-weight-bold text-danger text-sm dashboard-hero-chip">{{ $lowStockCount }} {{ inventoryLabel('low_stock', 'Low Stock Alerts') }}</span>
            </div>
          @else
            <div class="bento-card px-3 py-2 d-flex align-items-center">
              <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
              <span class="font-weight-bold text-success text-sm dashboard-hero-chip">{{ inventoryLabel('optimal_stock', 'Stock Levels Optimal') }}</span>
            </div>
          @endif

          {{-- Expiring Lots Chip --}}
          <div class="bento-card px-3 py-2 d-flex align-items-center cursor-pointer" onclick="$('#tab-expiring-link').click(); scrollToWorkspace();">
            <span class="pulse-dot pulse-yellow mr-2" style="--box-color: 245, 158, 11;"></span>
            <span class="font-weight-bold text-sm dashboard-hero-chip" style="color: #f59e0b;">{{ $expiringCount }} {{ inventoryLabel('expiring_soon', 'Expiring Lots (<30d)') }}</span>
          </div>

          {{-- Pending Approvals Chip --}}
          <div class="bento-card px-3 py-2 d-flex align-items-center cursor-pointer" onclick="$('#tab-approvals-link').click(); scrollToWorkspace();">
            <span class="pulse-dot pulse-blue mr-2" style="--box-color: 37, 99, 235;"></span>
            <span class="font-weight-bold text-white text-sm dashboard-hero-chip">{{ $pendingCount }} {{ inventoryLabel('approvals', 'Pending Approvals') }}</span>
          </div>

          {{-- Active On-Hand Lots Chip --}}
          <div class="bento-card px-3 py-2 d-flex align-items-center cursor-pointer" onclick="window.location.href='{{ route('inventory-stores') }}'">
            <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
            <span class="font-weight-bold text-white text-sm dashboard-hero-chip">{{ number_format($onHand) }} {{ inventoryLabel('on_hand_lots', 'Lots On-Hand') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- HERO PIPELINE (4-STAGE OPERATIONAL FLOW MATCHING LAB PIPELINE) --}}
    <div class="row mb-4">
      <!-- Stage 1: Request to Order -->
      <div class="col-md-3 mb-3 mb-md-0">
        <div class="bento-card pipeline-card h-100" onclick="window.location.href='{{ route('go_to_stage', ['stage' => 'Purchase Request']) }}'">
          <div class="stat-label text-info"><i class="mdi mdi-cart-arrow-down fa-lg"></i> {{ inventoryLabel('purchase_requests', 'Purchase Requests') }}</div>
          <div class="stat-value">{{ number_format($openPurchaseCount) }}</div>
          <div class="stat-subtext">{{ inventoryLabel('awaiting_procurement', 'Awaiting Procurement Pipeline') }}</div>
          <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
        </div>
      </div>

      <!-- Stage 2: Store Requests -->
      <div class="col-md-3 mb-3 mb-md-0">
        <div class="bento-card pipeline-card h-100" onclick="window.location.href='{{ route('go_to_stage', ['stage' => 'Request to Store']) }}'">
          <div class="stat-label text-warning"><i class="mdi mdi-inbox-arrow-down fa-lg"></i> {{ inventoryLabel('store_requisitions', 'Store Requisitions') }}</div>
          <div class="stat-value">{{ number_format($openStoreReqCount) }}</div>
          <div class="stat-subtext">{{ inventoryLabel('pending_issuance', 'Pending Store Issue & Fulfillment') }}</div>
          <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
        </div>
      </div>

      <!-- Stage 3: Active On-Hand Stock -->
      <div class="col-md-3 mb-3 mb-md-0">
        <div class="bento-card pipeline-card h-100" onclick="window.location.href='{{ route('inventory-stores') }}'">
          <div class="stat-label text-primary"><i class="mdi mdi-package-variant-closed fa-lg"></i> {{ inventoryLabel('on_hand_stock', 'On-Hand Stock') }}</div>
          <div class="stat-value">{{ number_format($onHand) }}</div>
          <div class="stat-subtext">{{ inventoryLabel('active_in_stores', 'Available Across Registered Stores') }}</div>
          <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
        </div>
      </div>

      <!-- Stage 4: Pending Sign-Offs -->
      <div class="col-md-3">
        <div class="bento-card pipeline-card h-100" onclick="window.location.href='{{ route('my-approvals') }}'">
          <div class="stat-label text-success"><i class="mdi mdi-draw fa-lg"></i> {{ inventoryLabel('approval_requests', 'Approval Requests') }}</div>
          <div class="stat-value">{{ number_format($pendingCount) }}</div>
          <div class="stat-subtext">{{ inventoryLabel('awaiting_sign_off', 'Awaiting Your Review & Sign-Off') }}</div>
        </div>
      </div>
    </div>

    {{-- FILTER TOOLBAR --}}
    <div class="bento-card inv-filter-toolbar mb-4">
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
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
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
          <a href="{{ route('inventory-home') }}" class="btn btn-outline-secondary btn-sm" id="btnResetFilter" title="Reset filters">
            <i class="mdi mdi-refresh"></i>
          </a>
        </div>
      </form>
    </div>

    {{-- TIER 2: TIME-SERIES TRENDS & CATEGORY BREAKDOWN --}}
    <div class="row mb-4">
      <!-- Movement Volume & Trend Line Chart -->
      <div class="col-lg-7 mb-3 mb-lg-0">
        <div class="bento-card h-100 position-relative">
          <div class="inv-loading-overlay" id="chart-loading-overlay">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>

          <div class="section-header">
            <span>
              <i class="mdi mdi-chart-timeline-variant text-primary mr-1"></i>
              {{ inventoryLabel('inventory_trends', 'Inventory Movement Trends') }}
              <small class="text-muted ml-1" id="chart-granularity-indicator">
                · {{ $periodMetrics['granularity_label'] ?? 'Week-by-Week' }}
              </small>
            </span>
            <div class="d-flex align-items-center" style="gap: 0.6rem;">
              <span class="badge badge-success px-2 py-1" id="panel-meta-in">
                <i class="mdi mdi-arrow-down mr-1"></i>In: {{ number_format($periodMetrics['total_stock_in'] ?? 0, 2) }}
              </span>
              <span class="badge badge-danger px-2 py-1" id="panel-meta-out">
                <i class="mdi mdi-arrow-up mr-1"></i>Out: {{ number_format($periodMetrics['total_stock_out'] ?? 0, 2) }}
              </span>
            </div>
          </div>

          <div class="section-body">
            <div class="inv-chart-canvas-wrap">
              <canvas id="inventoryTrendChart"></canvas>
            </div>
          </div>

          <div class="inv-chart-footer-stats">
            <span class="d-inline-flex align-items-center text-muted">
              <i class="inv-chart-dot is-in"></i>
              <strong>{{ inventoryLabel('stock_in', 'Stock In') }}</strong>
            </span>
            <span class="d-inline-flex align-items-center text-muted">
              <i class="inv-chart-dot is-out"></i>
              <strong>{{ inventoryLabel('stock_out', 'Stock Out') }}</strong>
            </span>
            <span class="d-inline-flex align-items-center text-muted">
              <i class="inv-chart-dot is-net"></i>
              <strong>{{ inventoryLabel('net_movement', 'Net Movement') }}</strong>
            </span>
            <span class="d-inline-flex align-items-center text-muted">
              <i class="inv-chart-dot is-cum"></i>
              <strong>{{ inventoryLabel('cumulative_stock', 'Cumulative Net') }}</strong>
            </span>
          </div>
        </div>
      </div>

      <!-- Category Movement Leaderboard -->
      <div class="col-lg-5">
        <div class="bento-card h-100">
          <div class="section-header">
            <span><i class="mdi mdi-shape-outline text-info mr-2"></i> {{ inventoryLabel('category_distribution', 'Category Movement Volume') }}</span>
            <a href="{{ route('inventory-categories') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">{{ inventoryLabel('catalog', 'Catalog') }}</a>
          </div>
          <div class="section-body p-3" id="category-leaderboard-container">
            @php
              $maxCatMove = $categoryBreakdownList->max('move_count') ?: 1;
            @endphp
            @forelse($categoryBreakdownList->take(6) as $cat)
              @php
                $percent = min(100, round(($cat['move_count'] / $maxCatMove) * 100));
              @endphp
              <div class="method-list-item">
                <div class="flex-grow-1 mr-2" style="min-width: 0;">
                  <div class="method-name text-truncate">{{ $cat['name'] }}</div>
                  <small class="text-muted">
                    <span class="text-success font-weight-bold">+{{ number_format($cat['stock_in'], 2) }} In</span> ·
                    <span class="text-danger font-weight-bold">−{{ number_format($cat['stock_out'], 2) }} Out</span>
                  </small>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.6rem;">
                  <div class="method-bar-bg d-none d-sm-block">
                    <div class="method-bar-fill" style="width: {{ $percent }}%;"></div>
                  </div>
                  <span class="badge {{ $cat['net'] >= 0 ? 'badge-success' : 'badge-danger' }} badge-pill px-2 py-1" style="min-width: 48px; text-align: center;">
                    {{ $cat['net'] > 0 ? '+' : '' }}{{ number_format($cat['net'], 2) }}
                  </span>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="mdi mdi-shape-outline d-block mb-1" style="font-size: 2rem; opacity: 0.4;"></i>
                {{ inventoryLabel('no_category_activity', 'No category movements in selected range') }}
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    {{-- TIER 3: BUSINESS INTELLIGENCE & FAST MOVERS --}}
    <div class="row mb-4">
      <!-- Top Moving Items -->
      <div class="col-lg-4 mb-3 mb-lg-0">
        <div class="bento-card h-100">
          <div class="section-header">
            <span><i class="mdi mdi-fire text-warning mr-2"></i> {{ inventoryLabel('top_moving_items', 'Fast-Moving Items') }}</span>
          </div>
          <div class="section-body p-3" id="top-moving-container">
            @forelse($topItems->take(5) as $item)
              <div class="method-list-item">
                <div style="min-width: 0; flex: 1;">
                  <a href="{{ $item['url'] }}" class="method-name text-truncate text-dark d-block">
                    {{ $item['name'] }}
                  </a>
                  <small class="text-muted">{{ $item['category'] }} @if(!empty($item['code'])) · {{ $item['code'] }} @endif</small>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="badge {{ $item['net_change'] >= 0 ? 'badge-success' : 'badge-danger' }} badge-pill px-2 py-1">
                    {{ $item['net_change'] > 0 ? '+' : '' }}{{ number_format($item['net_change'], 2) }}
                  </span>
                  <small class="d-block text-muted mt-1">+{{ number_format($item['stock_in'], 1) }} / −{{ number_format($item['stock_out'], 1) }}</small>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="mdi mdi-package-variant-closed d-block mb-1" style="font-size: 2rem; opacity: 0.4;"></i>
                {{ inventoryLabel('no_movement_in_period', 'No items transacted in this period.') }}
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Stores Overview & Distribution -->
      <div class="col-lg-4 mb-3 mb-lg-0">
        <div class="bento-card h-100">
          <div class="section-header">
            <span><i class="mdi mdi-warehouse text-primary mr-2"></i> {{ inventoryLabel('stores_overview', 'Stores at Location') }}</span>
            <a href="{{ route('inventory-stores') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">{{ inventoryLabel('manage_stores', 'Stores') }}</a>
          </div>
          <div class="section-body p-3">
            @forelse($storesList as $store)
              <a href="{{ $store['url'] }}" class="method-list-item text-decoration-none text-dark">
                <div>
                  <div class="method-name">{{ $store['name'] }}</div>
                  <small class="text-muted">{{ number_format($store['lots']) }} {{ inventoryLabel('lots', 'lots') }}</small>
                </div>
                <div class="text-right">
                  <span class="badge badge-light border px-2 py-1 font-weight-bold">{{ number_format($store['qty'], 2) }} units</span>
                </div>
              </a>
            @empty
              <div class="text-center text-muted py-4">
                <i class="mdi mdi-warehouse d-block mb-1" style="font-size: 2rem; opacity: 0.4;"></i>
                {{ inventoryLabel('no_stores', 'No stores registered at this location.') }}
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Period Operational Summary Card (Matching Lab Billing Style) -->
      <div class="col-lg-4">
        <div class="bento-card h-100 p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="text-muted font-weight-bold text-uppercase mb-3 text-sm" style="letter-spacing: 0.05em;">
              {{ $periodMetrics['period_description'] ?? date('F Y') }} Operational Stats
            </div>

            <div class="d-flex align-items-center mb-3">
              <div class="rounded p-3 mr-3" style="background-color: rgba(37, 99, 235, 0.1);">
                <i class="mdi mdi-pulse text-primary fa-2x"></i>
              </div>
              <div>
                <div class="h3 mb-0 font-weight-bold" id="kpi-transactions-count" style="color:#1e293b;">
                  {{ number_format($periodMetrics['transactions_count'] ?? 0) }}
                </div>
                <div class="small text-muted font-weight-bold">{{ inventoryLabel('transactions', 'Lot Movements Transacted') }}</div>
              </div>
            </div>

            <div class="d-flex align-items-center mb-3">
              <div class="rounded p-3 mr-3" style="background-color: rgba(16, 185, 129, 0.1);">
                <i class="mdi mdi-package-variant text-success fa-2x"></i>
              </div>
              <div>
                <div class="h3 mb-0 font-weight-bold" id="kpi-active-items-count" style="color:#1e293b;">
                  {{ number_format($periodMetrics['active_items_count'] ?? $topItems->count()) }}
                </div>
                <div class="small text-muted font-weight-bold">{{ inventoryLabel('active_items', 'Active Tracked Items') }}</div>
              </div>
            </div>

            <div class="d-flex align-items-center mb-3">
              <div class="rounded p-3 mr-3" style="background-color: rgba(99, 102, 241, 0.1);">
                <i class="mdi mdi-swap-vertical text-info fa-2x"></i>
              </div>
              <div>
                <div class="h3 mb-0 font-weight-bold" id="kpi-net-movement" style="color:#1e293b;">
                  @php $net = (float)($periodMetrics['net_movement'] ?? 0); @endphp
                  {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
                </div>
                <div class="small text-muted font-weight-bold">{{ inventoryLabel('net_movement', 'Net Period Movement') }}</div>
              </div>
            </div>
          </div>

          <div class="mt-2 pt-2 border-top d-flex gap-2" style="gap: 0.5rem;">
            <a href="{{ route('inventory-activity') }}" class="btn btn-sm btn-outline-primary btn-block rounded-pill font-weight-bold py-2">
              <i class="mdi mdi-history mr-1"></i> {{ inventoryLabel('activity_log', 'Activity Log') }}
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- TIER 4: SMART ACTION GRID / INTERACTIVE WORKSPACE (MATCHING LAB DASHBOARD ACTION GRID) --}}
    <div class="row" id="inventory-workspace-section">
      <div class="col-12">
        <div class="bento-card">
          <div class="d-flex justify-content-between align-items-center border-bottom px-4 pt-3 pb-0 flex-wrap" style="gap: 0.5rem;">
            <h5 class="font-weight-bold mb-0" style="color:#1e293b; font-size: 1.1rem;">
              <i class="mdi mdi-view-dashboard-outline text-primary mr-1"></i> {{ inventoryLabel('workspace', 'Inventory Workspace') }}
            </h5>
            <ul class="nav nav-tabs modern-tabs" id="inventoryWorkspaceTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="tab-approvals-link" data-toggle="tab" href="#approvals-pane" role="tab">
                  🎯 {{ inventoryLabel('approvals_and_requests', 'Pending Approvals & Requisitions') }}
                  @if($pendingCount > 0)
                    <span class="badge badge-danger badge-pill ml-1">{{ $pendingCount }}</span>
                  @endif
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-danger" id="tab-low-stock-link" data-toggle="tab" href="#low-stock-pane" role="tab">
                  🔴 {{ inventoryLabel('critical_stock', 'Low Stock Alerts') }}
                  @if($lowStockCount > 0)
                    <span class="badge badge-danger badge-pill ml-1">{{ $lowStockCount }}</span>
                  @endif
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-warning" id="tab-expiring-link" data-toggle="tab" href="#expiring-pane" role="tab">
                  ⏳ {{ inventoryLabel('shelf_life_watchlist', 'Shelf Life Watchlist') }}
                  @if($expiringCount > 0)
                    <span class="badge badge-warning badge-pill ml-1">{{ $expiringCount }}</span>
                  @endif
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="tab-stock-takes-link" data-toggle="tab" href="#stock-takes-pane" role="tab">
                  📋 {{ inventoryLabel('stock_takes', 'Stock Takes & Transfers') }}
                </a>
              </li>
              <li class="nav-item border-left ml-2 pl-2">
                <a class="nav-link text-muted" id="tab-recent-lots-link" data-toggle="tab" href="#recent-lots-pane" role="tab">
                  📦 {{ inventoryLabel('recent_lots', 'Recent Lot Logs') }}
                </a>
              </li>
            </ul>
          </div>

          <div class="section-body p-0">
            <div class="tab-content">
              {{-- Tab 1: Pending Approvals & Requisitions --}}
              <div class="tab-pane fade show active p-0" id="approvals-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table smart-table table-hover mb-0">
                    <thead>
                      <tr>
                        <th class="pl-4">{{ inventoryLabel('type', 'Type / Ref') }}</th>
                        <th>{{ inventoryLabel('details', 'Details / Entity') }}</th>
                        <th>{{ inventoryLabel('stage', 'Stage') }}</th>
                        <th>{{ inventoryLabel('requested_by', 'Requested By / When') }}</th>
                        <th class="text-right pr-4">{{ inventoryLabel('action', 'Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php $hasApprovalsOrReqs = false; @endphp
                      @if(isset($pendingApprovalsPreview) && $pendingApprovalsPreview->isNotEmpty())
                        @php $hasApprovalsOrReqs = true; @endphp
                        @foreach($pendingApprovalsPreview as $appr)
                          <tr style="cursor:pointer;" onclick="window.location.href='{{ route('my-approvals') }}'">
                            <td class="pl-4 font-weight-bold text-primary">
                              <span class="badge badge-primary px-2 py-1 mr-1">Approval</span>
                              {{ $appr->request_no ?? ('REQ #' . $appr->id) }}
                            </td>
                            <td>{{ $appr->entity_name ?? 'Purchase Requisition' }}</td>
                            <td><span class="badge badge-warning px-2 py-1">Awaiting Sign-off</span></td>
                            <td>{{ $appr->created_at ? $appr->created_at->format('d M Y') : 'Recent' }}</td>
                            <td class="text-right pr-4">
                              <a href="{{ route('my-approvals') }}" class="btn btn-sm btn-outline-primary rounded px-3">Review</a>
                            </td>
                          </tr>
                        @endforeach
                      @endif

                      @if($recentRequests->isNotEmpty())
                        @php $hasApprovalsOrReqs = true; @endphp
                        @foreach($recentRequests as $req)
                          <tr style="cursor:pointer;" onclick="window.location.href='{{ $req['url'] }}'">
                            <td class="pl-4 font-weight-bold text-dark">
                              <span class="badge badge-info px-2 py-1 mr-1">{{ $req['type'] }}</span>
                              {{ $req['code'] }}
                            </td>
                            <td>{{ $req['priority'] ? 'Priority: ' . $req['priority'] : 'Standard Requisition' }}</td>
                            <td><span class="badge badge-secondary px-2 py-1">In Workflow</span></td>
                            <td>{{ $req['when'] }}</td>
                            <td class="text-right pr-4">
                              <a href="{{ $req['url'] }}" class="btn btn-sm btn-outline-primary rounded px-3">View</a>
                            </td>
                          </tr>
                        @endforeach
                      @endif

                      @if(! $hasApprovalsOrReqs)
                        <tr>
                          <td colspan="5" class="text-center py-4 text-muted">
                            <i class="mdi mdi-check-circle-outline text-success mr-1"></i>
                            {{ inventoryLabel('queue_clear', 'All pending approval queues and requisition requests are up to date!') }}
                          </td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Tab 2: Critical Low Stock Alerts --}}
              <div class="tab-pane fade p-0" id="low-stock-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table smart-table table-hover mb-0">
                    <thead>
                      <tr>
                        <th class="pl-4">{{ inventoryLabel('item_name', 'Item Name') }}</th>
                        <th>{{ inventoryLabel('available_qty', 'On-Hand Qty') }}</th>
                        <th>{{ inventoryLabel('minimum_reorder', 'Reorder Level') }}</th>
                        <th>{{ inventoryLabel('status', 'Stock Status') }}</th>
                        <th class="text-right pr-4">{{ inventoryLabel('action', 'Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($lowStock as $item)
                        <tr style="cursor:pointer;" onclick="window.location.href='{{ $item['url'] }}'">
                          <td class="pl-4 font-weight-bold text-dark">
                            <span class="text-danger mr-1"><i class="mdi mdi-alert-circle"></i></span>
                            {{ $item['name'] }}
                          </td>
                          <td class="text-danger font-weight-bold">{{ number_format($item['available'], 2) }} {{ $item['unit'] }}</td>
                          <td>{{ number_format($item['minimum'], 2) }} {{ $item['unit'] }}</td>
                          <td>
                            <span class="badge badge-danger px-2 py-1">
                              {{ $item['available'] <= 0 ? 'Out of Stock' : 'Below Minimum' }}
                            </span>
                          </td>
                          <td class="text-right pr-4">
                            <a href="{{ route('go_to_stage', ['stage' => 'Purchase Request']) }}" class="btn btn-sm btn-outline-danger rounded px-3">Re-Order</a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center py-4 text-muted">
                            <i class="mdi mdi-check-circle text-success mr-1"></i>
                            {{ inventoryLabel('optimal_stock_desc', 'Zero low stock items! All inventory levels are above configured minimums.') }}
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Tab 3: Shelf Life & Expiry Watchlist --}}
              <div class="tab-pane fade p-0" id="expiring-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table smart-table table-hover mb-0">
                    <thead>
                      <tr>
                        <th class="pl-4">{{ inventoryLabel('item_name', 'Item / Lot Name') }}</th>
                        <th>{{ inventoryLabel('batch_number', 'Batch Number') }}</th>
                        <th>{{ inventoryLabel('expiry_date', 'Expiry Date') }}</th>
                        <th>{{ inventoryLabel('shelf_life_status', 'Days Remaining') }}</th>
                        <th class="text-right pr-4">{{ inventoryLabel('action', 'Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($expiring as $lot)
                        <tr style="cursor:pointer;" onclick="window.location.href='{{ $lot['url'] }}'">
                          <td class="pl-4 font-weight-bold text-dark">
                            {{ $lot['name'] }}
                            @if(!empty($lot['qty']))
                              <small class="text-muted d-block">{{ number_format($lot['qty'], 2) }} {{ $lot['unit'] }}</small>
                            @endif
                          </td>
                          <td>{{ $lot['batch'] ?: 'N/A' }}</td>
                          <td>{{ $lot['expiry'] }}</td>
                          <td>
                            @php $days = $lot['days_left'] ?? 30; @endphp
                            <span class="badge {{ $days <= 7 ? 'badge-danger' : 'badge-warning' }} px-2 py-1">
                              {{ $days <= 0 ? 'Expired' : $days . ' Days Left' }}
                            </span>
                          </td>
                          <td class="text-right pr-4">
                            <a href="{{ $lot['url'] }}" class="btn btn-sm btn-outline-primary rounded px-3">Inspect</a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center py-4 text-muted">
                            <i class="mdi mdi-check-circle text-success mr-1"></i>
                            {{ inventoryLabel('no_expiring_lots', 'No lots expiring within the next 30 days.') }}
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Tab 4: Stock Takes & Transfers --}}
              <div class="tab-pane fade p-0" id="stock-takes-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table smart-table table-hover mb-0">
                    <thead>
                      <tr>
                        <th class="pl-4">{{ inventoryLabel('code', 'Sheet Code') }}</th>
                        <th>{{ inventoryLabel('status', 'Status') }}</th>
                        <th>{{ inventoryLabel('stores', 'Target Stores') }}</th>
                        <th>{{ inventoryLabel('date', 'Timestamp') }}</th>
                        <th class="text-right pr-4">{{ inventoryLabel('action', 'Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($openTakes as $take)
                        <tr style="cursor:pointer;" onclick="window.location.href='{{ $take['url'] }}'">
                          <td class="pl-4 font-weight-bold text-primary">{{ $take['code'] }}</td>
                          <td><span class="badge badge-info px-2 py-1">{{ $take['status'] }}</span></td>
                          <td>{{ $take['stores'] ?: 'All Stores' }}</td>
                          <td>{{ $take['when'] }}</td>
                          <td class="text-right pr-4">
                            <a href="{{ $take['url'] }}" class="btn btn-sm btn-outline-primary rounded px-3">Open Sheet</a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center py-4 text-muted">
                            <i class="mdi mdi-clipboard-check-outline text-success mr-1"></i>
                            {{ inventoryLabel('no_open_stock_takes', 'No active stock taking sheets in progress.') }}
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Tab 5: Recent Lot Activity --}}
              <div class="tab-pane fade p-0" id="recent-lots-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table smart-table table-hover mb-0">
                    <thead>
                      <tr>
                        <th class="pl-4">{{ inventoryLabel('item', 'Item') }}</th>
                        <th>{{ inventoryLabel('code', 'Code') }}</th>
                        <th>{{ inventoryLabel('type', 'Movement') }}</th>
                        <th>{{ inventoryLabel('quantity', 'Quantity') }}</th>
                        <th>{{ inventoryLabel('when', 'When') }}</th>
                        <th class="text-right pr-4">{{ inventoryLabel('action', 'Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($recentLots as $lot)
                        <tr style="cursor:pointer;" onclick="window.location.href='{{ $lot['url'] }}'">
                          <td class="pl-4 font-weight-bold text-dark">{{ $lot['name'] }}</td>
                          <td><small class="badge badge-light border">{{ $lot['code'] ?: 'N/A' }}</small></td>
                          <td>
                            @if(($lot['kind'] ?? '') === 'out')
                              <span class="badge badge-danger px-2 py-1">Stock Out</span>
                            @else
                              <span class="badge badge-success px-2 py-1">Stock In</span>
                            @endif
                          </td>
                          <td class="font-weight-bold {{ ($lot['kind'] ?? '') === 'out' ? 'text-danger' : 'text-success' }}">
                            {{ ($lot['kind'] ?? '') === 'out' ? '−' . number_format($lot['stock_out'] ?? 0, 2) : '+' . number_format($lot['stock_in'] ?? 0, 2) }} {{ $lot['unit'] ?? '' }}
                          </td>
                          <td>{{ $lot['when'] ?? '' }}</td>
                          <td class="text-right pr-4">
                            <a href="{{ $lot['url'] }}" class="btn btn-sm btn-outline-primary rounded px-3">View</a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="6" class="text-center py-4 text-muted">
                            {{ inventoryLabel('no_recent_lots', 'No recent lot activity.') }}
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@section('script2')
  <script>
    (function () {
      var trendChartInstance = null;
      var rawTrendPayload = @json($trendChartData);
      var currentMetrics = @json($periodMetrics);

      function scrollToWorkspace() {
        var el = document.getElementById('inventory-workspace-section');
        if (el) {
          el.scrollIntoView({ behavior: 'smooth' });
        }
      }
      window.scrollToWorkspace = scrollToWorkspace;

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
        gradientIn.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
        gradientIn.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        var gradientOut = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOut.addColorStop(0, 'rgba(239, 68, 68, 0.22)');
        gradientOut.addColorStop(1, 'rgba(239, 68, 68, 0.00)');

        var gradientNet = ctx.createLinearGradient(0, 0, 0, 300);
        gradientNet.addColorStop(0, 'rgba(99, 102, 241, 0.18)');
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
                hidden: true,
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

      // Initialize on DOM ready
      $(function () {
        initTrendChart(rawTrendPayload);

        // Preset button clicks
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

        // Form submission
        $('#inventoryFilterForm').on('submit', function (e) {
          e.preventDefault();
          fetchDashboardData();
        });

        // Dropdown auto-filter
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
                var net = Number(m.net_movement || 0);
                var netFormatted = (net > 0 ? '+' : '') + net.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                $('#kpi-net-movement').text(netFormatted);
                $('#kpi-transactions-count').text(Number(m.transactions_count || 0).toLocaleString());
                if (m.active_items_count !== undefined) {
                  $('#kpi-active-items-count').text(Number(m.active_items_count).toLocaleString());
                }

                if (m.granularity_label) {
                  $('#chart-granularity-indicator').text('· ' + m.granularity_label);
                }

                $('#panel-meta-in').html('<i class="mdi mdi-arrow-down mr-1"></i>In: ' + Number(m.total_stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#panel-meta-out').html('<i class="mdi mdi-arrow-up mr-1"></i>Out: ' + Number(m.total_stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
              }

              // Update top moving items
              if (res.top_moving_items) {
                var topContainer = $('#top-moving-container');
                topContainer.empty();
                if (res.top_moving_items.length === 0) {
                  topContainer.append('<div class="text-center text-muted py-4"><i class="mdi mdi-package-variant-closed d-block mb-1" style="font-size: 2rem; opacity: 0.4;"></i>No items transacted in this period.</div>');
                } else {
                  $.each(res.top_moving_items.slice(0, 5), function (i, it) {
                    var netVal = Number(it.net_change || 0);
                    var netClass = netVal >= 0 ? 'badge-success' : 'badge-danger';
                    var netSign = netVal > 0 ? '+' : '';
                    topContainer.append(
                      '<div class="method-list-item">' +
                        '<div style="min-width: 0; flex: 1;">' +
                          '<a href="' + it.url + '" class="method-name text-truncate text-dark d-block">' + it.name + '</a>' +
                          '<small class="text-muted">' + it.category + (it.code ? ' · ' + it.code : '') + '</small>' +
                        '</div>' +
                        '<div class="text-right flex-shrink-0">' +
                          '<span class="badge ' + netClass + ' badge-pill px-2 py-1">' + netSign + netVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>' +
                          '<small class="d-block text-muted mt-1">+' + Number(it.stock_in || 0).toFixed(1) + ' / −' + Number(it.stock_out || 0).toFixed(1) + '</small>' +
                        '</div>' +
                      '</div>'
                    );
                  });
                }
              }

              // Update category breakdown
              if (res.category_breakdown) {
                var catContainer = $('#category-leaderboard-container');
                catContainer.empty();
                if (res.category_breakdown.length === 0) {
                  catContainer.append('<div class="text-center text-muted py-4"><i class="mdi mdi-shape-outline d-block mb-1" style="font-size: 2rem; opacity: 0.4;"></i>No category movements in selected range</div>');
                } else {
                  var maxMove = 1;
                  $.each(res.category_breakdown, function(i, c) {
                    if (Number(c.move_count || 0) > maxMove) maxMove = Number(c.move_count);
                  });

                  $.each(res.category_breakdown.slice(0, 6), function (i, cat) {
                    var netVal = Number(cat.net || 0);
                    var netBadgeClass = netVal >= 0 ? 'badge-success' : 'badge-danger';
                    var netSign = netVal > 0 ? '+' : '';
                    var pct = Math.min(100, Math.round((Number(cat.move_count || 0) / maxMove) * 100));

                    catContainer.append(
                      '<div class="method-list-item">' +
                        '<div class="flex-grow-1 mr-2" style="min-width: 0;">' +
                          '<div class="method-name text-truncate">' + cat.name + '</div>' +
                          '<small class="text-muted"><span class="text-success font-weight-bold">+' + Number(cat.stock_in || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' In</span> · <span class="text-danger font-weight-bold">−' + Number(cat.stock_out || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Out</span></small>' +
                        '</div>' +
                        '<div class="d-flex align-items-center" style="gap: 0.6rem;">' +
                          '<div class="method-bar-bg d-none d-sm-block">' +
                            '<div class="method-bar-fill" style="width: ' + pct + '%;"></div>' +
                          '</div>' +
                          '<span class="badge ' + netBadgeClass + ' badge-pill px-2 py-1" style="min-width: 48px; text-align: center;">' + netSign + netVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>' +
                        '</div>' +
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
