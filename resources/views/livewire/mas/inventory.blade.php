<div>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/inventory.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/inventory.subtitle') }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm mr-2">
                <button onclick="exportInventoryPdf(false)" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-file-pdf"></i> {{ __('mas/common.download') }} PDF
                </button>
                <button onclick="exportInventoryPdf(true)" class="btn btn-outline-primary btn-sm border-left-0">
                    <i class="mdi mdi-eye"></i>
                </button>
            </div>
            <a href="{{ route('mas.export', 'inventory') }}" class="btn btn-success btn-sm">
                <i class="mdi mdi-microsoft-excel"></i> Excel
            </a>

            <form id="pdfExportForm" action="{{ route('mas.export.visuals', 'inventory') }}" method="POST" style="display:none">
                @csrf
                <input type="hidden" name="preview" id="preview_input" value="false">
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-primary text-white" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-white-transparent p-3 mr-3">
                        <i class="mdi mdi-package-variant mdi-24px"></i>
                    </div>
                    <div>
                        <h6 class="text-white-50 text-uppercase small mb-1">{{ __('mas/inventory.tracked_items') }}</h6>
                        <h2 class="font-weight-bold mb-0">{{ $stats['summary']['tracked_items'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-warning text-dark" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-white-transparent p-3 mr-3">
                        <i class="mdi mdi-clock-alert mdi-24px"></i>
                    </div>
                    <div>
                        <h6 class="text-dark-50 text-uppercase small mb-1">{{ __('mas/inventory.near_expiry') }}</h6>
                        <h2 class="font-weight-bold mb-0">{{ $stats['summary']['items_near_expiry'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-danger text-white" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-white-transparent p-3 mr-3">
                        <i class="mdi mdi-alert-decagram mdi-24px"></i>
                    </div>
                    <div>
                        <h6 class="text-white-50 text-uppercase small mb-1">{{ __('mas/inventory.below_minimum') }}</h6>
                        <h2 class="font-weight-bold mb-0">{{ $stats['summary']['items_below_minimum'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed List -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('mas/inventory.high_risk_analysis') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">{{ __('mas/inventory.item_name') }}</th>
                                    <th class="border-0">{{ __('mas/inventory.store') }}</th>
                                    <th class="border-0">{{ __('mas/inventory.available') }}</th>
                                    <th class="border-0">{{ __('mas/inventory.min_level') }}</th>
                                    <th class="border-0">{{ __('mas/inventory.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['priority_items'] as $item)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $item['item_name'] }}</div>
                                        <div class="small text-muted">{{ $item['item_code'] }}</div>
                                    </td>
                                    <td><span class="badge badge-light px-2">{{ $item['store_name'] }}</span></td>
                                    <td class="font-weight-bold">{{ $item['available_qty'] }}</td>
                                    <td>{{ $item['minimum_level'] }}</td>
                                    <td>
                                        @if($item['available_qty'] < $item['minimum_level'])
                                            <span class="badge badge-danger">{{ __('mas/inventory.critical') }}</span>
                                        @elseif($item['near_expiry_qty'] > 0)
                                            <span class="badge badge-warning">{{ __('mas/inventory.expiry_risk') }}</span>
                                        @else
                                            <span class="badge badge-success">{{ __('mas/inventory.healthy') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="/stock-management" class="btn btn-sm btn-link font-weight-bold">{{ __('mas/inventory.open_inventory_module') }} <i class="mdi mdi-open-in-new"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-white-transparent {
    background-color: rgba(255, 255, 255, 0.2);
}
.text-dark-50 {
    color: rgba(30, 30, 30, 0.6);
}
.btn-primary { background-color: #6D0A0E !important; border-color: #6D0A0E !important; color: white !important; }
.btn-primary:hover { background-color: #55080b !important; border-color: #55080b !important; color: white !important; }
.btn-outline-primary { color: #6D0A0E !important; border-color: #6D0A0E !important; }
.btn-outline-primary:hover { background-color: #6D0A0E !important; border-color: #6D0A0E !important; color: white !important; }
.bg-primary { background-color: #6D0A0E !important; }
.btn-link { color: #6D0A0E !important; }
.btn-link:hover { color: #55080b !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.exportInventoryPdf = function(isPreview = false) {
            const form = document.getElementById('pdfExportForm');
            document.getElementById('preview_input').value = isPreview;
            form.target = isPreview ? "_blank" : "_self";
            form.submit();
        }
    });
</script>
</div>