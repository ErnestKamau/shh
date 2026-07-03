<div>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/logistics.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/logistics.subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm">
                <button onclick="exportLogisticsPdf(false)" class="btn btn-cyan btn-sm text-white">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/logistics.download_audit') }}
                </button>
                <button onclick="exportLogisticsPdf(true)" class="btn btn-outline-cyan btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i> {{ __('mas/logistics.preview') }}
                </button>
            </div>
            
            <form id="logisticsExportForm" action="{{ route('mas.export.visuals', 'lab-logistics') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="chart_image" id="chart_image_input">
                <input type="hidden" name="preview" id="preview_input" value="false">
            </form>

            <button wire:click="refresh" class="btn btn-outline-primary btn-sm shadow-sm bg-white ml-2">
                <i class="mdi mdi-refresh"></i> {{ __('mas/logistics.refresh_data') }}
            </button>
        </div>
    </div>

    <!-- Summary Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/logistics.monitored_buffers') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-white">{{ count($stats['buffers']) }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/logistics.active_solutions') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/logistics.low_stock_alerts') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-white">{{ $stats['low_stock_count'] }}</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/logistics.below_threshold') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-2 opacity-75">{{ __('mas/logistics.restock_compliance') }}</h6>
                    <h2 class="font-weight-bold mb-0 text-white">{{ $stats['restock_compliance'] }}%</h2>
                    <p class="small mb-0 mt-2">{{ __('mas/logistics.avg_lead_time') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Stock Levels Grid -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/logistics.inventory_title') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/logistics.item_name') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/logistics.batch_code') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold text-center">{{ __('mas/logistics.available_qty') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold text-center">{{ __('mas/logistics.min_level') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/logistics.status') }}</th>
                                    <th class="border-0 small text-uppercase font-weight-bold">{{ __('mas/logistics.health') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['buffers'] as $buffer)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $buffer['name'] }}</div>
                                        </td>
                                        <td><code>{{ $buffer['code'] }}</code></td>
                                        <td class="text-center">{{ number_format($buffer['current_qty'], 1) }}</td>
                                        <td class="text-center text-muted">{{ number_format($buffer['min_level'], 1) }}</td>
                                        <td>
                                            @if($buffer['is_low'])
                                                <span class="badge badge-soft-danger px-3 py-1">{{ __('mas/logistics.critically_low') }}</span>
                                            @else
                                                <span class="badge badge-soft-success px-3 py-1">{{ __('mas/logistics.optimum') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $ratio = $buffer['min_level'] > 0 ? ($buffer['current_qty'] / $buffer['min_level']) * 100 : 100;
                                                $barClass = $ratio < 100 ? 'bg-danger' : ($ratio < 150 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress" style="height: 6px; width: 100px;">
                                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ min(100, $ratio) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No buffer data tracked in analytics.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/logistics.recent_movements_title') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                @forelse($stats['recent_movements'] ?? [] as $move)
                                    <tr class="small">
                                        <td>
                                            <i class="mdi mdi-arrow-left-box text-primary h5 icon-sm"></i>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $move->buffer_name }}</div>
                                            <div class="x-small text-muted">{{ $move->batch_code }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $move->quantity }} {{ __('mas/logistics.units') }}</div>
                                            <div class="x-small text-muted">{{ __('mas/logistics.consumption') }}</div>
                                        </td>
                                        <td class="text-right">
                                            <div class="text-muted">{{ \Carbon\Carbon::parse($move->created_at)->diffForHumans() }}</div>
                                            <div class="x-small font-weight-bold text-uppercase">{{ $move->staff_name }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted italic small">{{ __('mas/logistics.no_movements') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procurement Insight -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h5 class="mb-3 font-weight-bold text-dark">{{ __('mas/logistics.procurement_insight') }}</h5>
                    <div class="alert alert-info border-0 shadow-sm small">
                        <i class="mdi mdi-lightbulb-on mr-1"></i> 
                        {!! $stats['ai_suggestion'] !!}
                    </div>
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item bg-transparent px-0 py-2 border-0">
                            <div class="d-flex justify-content-between x-small uppercase text-muted font-weight-bold">
                                <span>{{ __('mas/logistics.prep_frequency') }}</span>
                                <span>{{ $stats['prep_frequency'] }}</span>
                            </div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: {{ $stats['prep_percentage'] }}%"></div>
                            </div>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-0">
                            <div class="d-flex justify-content-between x-small uppercase text-muted font-weight-bold">
                                <span>{{ __('mas/logistics.waste_factor') }}</span>
                                <span>{{ $stats['waste_factor'] }}%</span>
                            </div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ min(100, $stats['waste_factor'] * 10) }}%"></div>
                            </div>
                        </li>
                    </ul>
                    <button class="btn btn-primary btn-block btn-sm mt-4 shadow-sm border-0">
                        <i class="mdi mdi-cart-outline mr-1"></i> {{ __('mas/logistics.generate_requisition') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .opacity-75 { opacity: 0.75; }
    .x-small { font-size: 10px; }
    .uppercase { text-transform: uppercase; }
    .badge-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .badge-soft-success { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .btn-primary { background-color: var(--color-primary) !important; border-color: var(--color-primary) !important; color: white !important; }
    .btn-primary:hover { background-color: #55080b !important; border-color: #55080b !important; color: white !important; }
    .btn-outline-primary { color: var(--color-primary) !important; border-color: var(--color-primary) !important; }
    .btn-outline-primary:hover { background-color: var(--color-primary) !important; border-color: var(--color-primary) !important; color: white !important; }
    .bg-primary { background-color: var(--color-primary) !important; }
    .text-primary { color: var(--color-primary) !important; }
    .btn-cyan { background-color: var(--color-primary) !important; color: white !important; border: none; }
    .btn-cyan:hover { background-color: #55080b !important; color: white !important; }
    .btn-outline-cyan { border-color: var(--color-primary) !important; color: var(--color-primary) !important; background: transparent; }
    .btn-outline-cyan:hover { background-color: var(--color-primary) !important; color: white !important; }
</style>

<script>
    function exportLogisticsPdf(isPreview = false) {
        // No charts currently on logistics page, but maintaining structure for future consistency
        const form = document.getElementById('logisticsExportForm');
        document.getElementById('preview_input').value = isPreview;
        form.target = isPreview ? "_blank" : "_self";
        form.submit();
    }
</script>
</div>
