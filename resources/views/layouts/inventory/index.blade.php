@extends('layouts.inventory.layout.app')

@php
  $activityRows = collect($activity ?? []);
  $hasActivity = (bool) ($hasActivity ?? false);
  $ops = $ops ?? [];
  $pipeline = collect($ops['pipeline'] ?? []);
  $expiring = collect($ops['expiring'] ?? []);
  $lowStock = collect($ops['low_stock'] ?? []);
  $recentLots = collect($ops['recent_lots'] ?? []);
  $stores = collect($ops['stores'] ?? []);
  $openTakes = collect($ops['open_stock_takes'] ?? []);
  $recentRequests = collect($ops['recent_requests'] ?? []);
  $pendingCount = (int) ($pendingApprovalsCount ?? 0);
  $onHand = (int) ($ops['on_hand_lots'] ?? 0);
  $lowStockCount = (int) ($ops['low_stock_count'] ?? 0);
  $expiringCount = (int) ($ops['expiring_lots'] ?? 0);
  $expiredCount = (int) ($ops['expired_lots'] ?? 0);
  $openPurchaseCount = (int) data_get($pipeline->firstWhere('stage', 'Purchase Request'), 'count', 0);
  $locationLabel = $locationName ?: inventoryLabel('select_location', 'Select Location');
  $chartLabels = [
    inventoryLabel('stock_in', 'Stock In'),
    inventoryLabel('stock_out', 'Stock Out'),
  ];
@endphp

@section('title2')
  <title>{{ inventoryLabel('dashboard', 'Dashboard') }} | {{ inventoryLabel('module_name', 'Inventory Management') }}</title>
  <style type="text/css">
    #activity-graph {
      height: 260px;
    }
  </style>
@endsection

@section('content2')
  <main class="inventory-dashboard">
    <header class="inventory-dash-hero">
      <div>
        <p class="inventory-dash-kicker">
          <i class="mdi mdi-map-marker-outline"></i>
          {{ $locationLabel }}
        </p>
        <h1 class="inventory-dash-title">{{ inventoryLabel('dashboard', 'Dashboard') }}</h1>
        <p class="inventory-dash-subtitle">{{ inventoryLabel('overview_subtitle', 'Stock health and work waiting at this location') }}</p>
      </div>
      <div class="inventory-dash-hero-meta">
        <i class="mdi mdi-calendar-month-outline"></i>
        {{ inventoryLabel('last_30_days', 'Last 30 Days') }}
      </div>
    </header>

    <div class="stat-cards-row inventory-kpi-row">
      <a href="{{ route('inventory-stores') }}" class="stat-card stat-card-link">
        <div class="stat-card-label">{{ inventoryLabel('on_hand_lots', 'On-hand lots') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($onHand) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('available_in_store', 'Available in store') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-package-variant-closed"></i></div>
        </div>
      </a>
      <a href="{{ route('inventory-categories') }}" class="stat-card stat-card-link {{ $lowStockCount > 0 ? 'is-alert' : '' }}">
        <div class="stat-card-label">{{ inventoryLabel('low_stock', 'Low stock') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($lowStockCount) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('below_reorder', 'Below reorder level') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-arrow-down-bold-circle-outline"></i></div>
        </div>
      </a>
      <a href="{{ route('inventory-categories') }}" class="stat-card stat-card-link {{ $expiringCount > 0 ? 'is-warn' : '' }}">
        <div class="stat-card-label">{{ inventoryLabel('expiring_soon', 'Expiring in 30 days') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($expiringCount) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('shelf_life', 'Shelf life') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-timer-sand"></i></div>
        </div>
      </a>
      <a href="{{ route('inventory-categories') }}" class="stat-card stat-card-link {{ $expiredCount > 0 ? 'is-alert' : '' }}">
        <div class="stat-card-label">{{ inventoryLabel('expired_lots', 'Expired lots') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($expiredCount) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('remove_from_use', 'Remove from use') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-alert-octagon-outline"></i></div>
        </div>
      </a>
      <a href="{{ route('my-approvals') }}" class="stat-card stat-card-link {{ $pendingCount > 0 ? 'is-action' : '' }}">
        <div class="stat-card-label">{{ inventoryLabel('approval_requests', 'Approval Requests') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($pendingCount) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('awaiting_you', 'Awaiting your approval') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-draw"></i></div>
        </div>
      </a>
      <a href="{{ route('go_to_stage', ['stage' => 'Purchase Request']) }}" class="stat-card stat-card-link {{ $openPurchaseCount > 0 ? 'is-action' : '' }}">
        <div class="stat-card-label">{{ inventoryLabel('open_purchase_requests', 'Purchase requests') }}</div>
        <div class="stat-card-content">
          <div>
            <div class="stat-card-value">{{ number_format($openPurchaseCount) }}</div>
            <div class="stat-card-hint">{{ inventoryLabel('in_pipeline', 'In the pipeline') }}</div>
          </div>
          <div class="stat-card-icon"><i class="mdi mdi-cart-outline"></i></div>
        </div>
      </a>
    </div>

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

    <div class="inventory-dash-grid">
      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-chart-bar"></i>
            {{ inventoryLabel('inventory_activity', 'Inventory Activity') }}
            <small class="text-muted">{{ inventoryLabel('last_30_days', 'Last 30 Days') }}</small>
          </h5>
          <div class="inventory-panel-meta">
            <span>{{ inventoryLabel('stock_in', 'Stock In') }} {{ number_format($stockInTotal ?? 0) }}</span>
            <span>{{ inventoryLabel('stock_out', 'Stock Out') }} {{ number_format($stockOutTotal ?? 0) }}</span>
            <a href="{{ route('inventory-activity') }}" class="inventory-panel-link">
              {{ inventoryLabel('view_movement', 'View movement') }}
              <i class="mdi mdi-arrow-right"></i>
            </a>
          </div>
        </div>
        <div class="workflow-board-panel-body">
          @if($hasActivity)
            <div id="activity-graph" data-json='{{ json_encode($activityRows->values()) }}'></div>
            <div class="inventory-chart-legend" aria-hidden="true">
              <span><i class="inventory-legend-swatch is-in"></i> {{ inventoryLabel('stock_in', 'Stock In') }}</span>
              <span><i class="inventory-legend-swatch is-out"></i> {{ inventoryLabel('stock_out', 'Stock Out') }}</span>
            </div>
          @else
            <div class="inventory-empty-state">
              <i class="mdi mdi-chart-timeline-variant"></i>
              <h6>{{ inventoryLabel('no_recent_activity', 'No stock movement in the last 30 days') }}</h6>
              <p>{{ inventoryLabel('no_recent_activity_hint', 'Receipts and issues will appear here as soon as items move.') }}</p>
              <a href="{{ route('go_to_stage', ['stage' => 'Goods Receipt']) }}" class="btn btn-outline-primary btn-sm">
                {{ inventoryLabel('workflow_goods_receipt', 'Goods Receipt') }}
              </a>
            </div>
          @endif
        </div>
      </section>

      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-timer-sand"></i>
            {{ inventoryLabel('expiring_reagents', 'Expiring lots') }}
          </h5>
          <a href="{{ route('inventory-categories') }}" class="inventory-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
        </div>
        <div class="workflow-board-panel-body p-0">
          @forelse($expiring as $lot)
            <a class="inventory-attention-item" href="{{ $lot['url'] }}">
              <span class="inventory-attention-dot {{ ($lot['days_left'] ?? 30) <= 7 ? 'is-alert' : 'is-warn' }}"></span>
              <span class="inventory-attention-copy">
                <strong>{{ $lot['name'] }}</strong>
                <small>
                  {{ $lot['expiry'] }}
                  · {{ $lot['days_left'] }} {{ inventoryLabel('days_left', 'days left') }}
                  @if($lot['qty']) · {{ number_format($lot['qty']) }} {{ $lot['unit'] }} @endif
                  @if($lot['batch']) · {{ $lot['batch'] }} @endif
                </small>
              </span>
              <i class="mdi mdi-chevron-right"></i>
            </a>
          @empty
            <div class="inventory-empty-state inventory-empty-state-compact">
              <i class="mdi mdi-check-circle-outline"></i>
              <h6>{{ inventoryLabel('no_expiring', 'No lots expiring in the next 30 days') }}</h6>
            </div>
          @endforelse
        </div>
      </section>
    </div>

    <div class="inventory-dash-grid">
      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-arrow-down-bold-circle-outline"></i>
            {{ inventoryLabel('low_stock', 'Low stock') }}
          </h5>
          <a href="{{ route('inventory-categories') }}" class="inventory-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
        </div>
        <div class="workflow-board-panel-body p-0">
          @forelse($lowStock as $item)
            <a class="inventory-attention-item" href="{{ $item['url'] }}">
              <span class="inventory-attention-dot is-alert"></span>
              <span class="inventory-attention-copy">
                <strong>{{ $item['name'] }}</strong>
                <small>
                  {{ number_format($item['available']) }} {{ $item['unit'] }}
                  {{ inventoryLabel('on_hand', 'on hand') }}
                  · {{ inventoryLabel('reorder_at', 'reorder at') }} {{ number_format($item['minimum']) }}
                </small>
              </span>
              <i class="mdi mdi-chevron-right"></i>
            </a>
          @empty
            <div class="inventory-empty-state inventory-empty-state-compact">
              <i class="mdi mdi-check-circle-outline"></i>
              <h6>{{ inventoryLabel('no_low_stock', 'Nothing below reorder level') }}</h6>
            </div>
          @endforelse
        </div>
      </section>

      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-swap-vertical"></i>
            {{ inventoryLabel('recent_lots', 'Recent lots') }}
          </h5>
          <a href="{{ route('inventory-activity') }}" class="inventory-panel-link">{{ inventoryLabel('view_movement', 'View movement') }}</a>
        </div>
        <div class="workflow-board-panel-body p-0">
          @forelse($recentLots as $lot)
            <a class="inventory-attention-item" href="{{ $lot['url'] }}">
              <span class="inventory-attention-dot {{ ($lot['kind'] ?? 'in') === 'out' ? 'is-out' : 'is-action' }}"></span>
              <span class="inventory-attention-copy">
                <strong>{{ $lot['name'] }}</strong>
                <small>
                  @if(($lot['stock_in'] ?? 0) > 0)+{{ number_format($lot['stock_in']) }}@endif
                  @if(($lot['stock_in'] ?? 0) > 0 && ($lot['stock_out'] ?? 0) > 0) / @endif
                  @if(($lot['stock_out'] ?? 0) > 0)−{{ number_format($lot['stock_out']) }}@endif
                  {{ $lot['unit'] }}
                  · {{ $lot['when'] }}
                </small>
              </span>
              <i class="mdi mdi-chevron-right"></i>
            </a>
          @empty
            <div class="inventory-empty-state inventory-empty-state-compact">
              <i class="mdi mdi-package-variant"></i>
              <h6>{{ inventoryLabel('no_recent_lots', 'No recent receipts') }}</h6>
            </div>
          @endforelse
        </div>
      </section>
    </div>

    <div class="inventory-dash-grid">
      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-warehouse"></i>
            {{ inventoryLabel('stores_overview', 'Stores') }}
          </h5>
          <a href="{{ route('inventory-stores') }}" class="inventory-panel-link">{{ inventoryLabel('view_all', 'View all') }}</a>
        </div>
        <div class="workflow-board-panel-body p-0">
          @forelse($stores as $store)
            <a class="inventory-attention-item" href="{{ $store['url'] }}">
              <span class="inventory-store-mark">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($store['name'], 0, 1)) }}</span>
              <span class="inventory-attention-copy">
                <strong>{{ $store['name'] }}</strong>
                <small>{{ number_format($store['lots']) }} {{ inventoryLabel('lots', 'lots') }} · {{ number_format($store['qty']) }} {{ inventoryLabel('on_hand', 'on hand') }}</small>
              </span>
              <i class="mdi mdi-chevron-right"></i>
            </a>
          @empty
            <div class="inventory-empty-state inventory-empty-state-compact">
              <i class="mdi mdi-warehouse"></i>
              <h6>{{ inventoryLabel('no_stores', 'No stores at this location yet') }}</h6>
            </div>
          @endforelse
        </div>
      </section>

      <section class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-clipboard-text-outline"></i>
            {{ inventoryLabel('open_work', 'Open work') }}
          </h5>
        </div>
        <div class="workflow-board-panel-body p-0">
          @if($openTakes->isNotEmpty())
            <div class="inventory-attention-heading">
              <span>{{ inventoryLabel('open_stock_takes', 'Open stock takes') }}</span>
              <a href="{{ route('stock-taking-list') }}">{{ inventoryLabel('view_all', 'View all') }}</a>
            </div>
            @foreach($openTakes as $taking)
              <a class="inventory-attention-item" href="{{ $taking['url'] }}">
                <span class="inventory-attention-dot is-action"></span>
                <span class="inventory-attention-copy">
                  <strong>{{ $taking['code'] }}</strong>
                  <small>{{ $taking['status'] }}@if($taking['stores']) · {{ $taking['stores'] }}@endif · {{ $taking['when'] }}</small>
                </span>
                <i class="mdi mdi-chevron-right"></i>
              </a>
            @endforeach
          @endif
          @if($recentRequests->isNotEmpty())
            <div class="inventory-attention-heading">
              <span>{{ inventoryLabel('recent_requests', 'Recent requests') }}</span>
            </div>
            @foreach($recentRequests as $request)
              <a class="inventory-attention-item" href="{{ $request['url'] }}">
                <span class="inventory-attention-dot is-action"></span>
                <span class="inventory-attention-copy">
                  <strong>{{ $request['code'] }}</strong>
                  <small>{{ $request['type'] }}@if($request['priority']) · {{ $request['priority'] }}@endif · {{ $request['when'] }}</small>
                </span>
                <i class="mdi mdi-chevron-right"></i>
              </a>
            @endforeach
          @endif
          @if($openTakes->isEmpty() && $recentRequests->isEmpty())
            <div class="inventory-empty-state inventory-empty-state-compact">
              <i class="mdi mdi-check-circle-outline"></i>
              <h6>{{ inventoryLabel('no_open_work', 'No open stock takes or recent requests') }}</h6>
            </div>
          @endif
        </div>
      </section>
    </div>
  </main>
@endsection

@section('script2')
  @if($hasActivity)
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script>
      $(function () {
        Morris.Bar({
          element: 'activity-graph',
          data: $('#activity-graph').data('json'),
          xkey: 'category',
          barColors: ['#6d0a0e', '#64748b'],
          ykeys: ['stock_in', 'stock_out'],
          labels: {!! json_encode($chartLabels) !!},
          gridTextFamily: 'IBM Plex Sans, system-ui, sans-serif',
          gridTextSize: 11,
          hideHover: 'auto',
          resize: true,
          padding: 12
        });
      });
    </script>
  @endif
@endsection
