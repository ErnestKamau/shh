@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Inventory Health</h1>
            <p class="text-muted small mb-0">Stock availability, reorder tracking, and sub-category analysis.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mas.export', 'inventory') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> Download Report
            </a>
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
                        <h6 class="text-white-50 text-uppercase small mb-1">Tracked Items</h6>
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
                        <h6 class="text-dark-50 text-uppercase small mb-1">Near Expiry</h6>
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
                        <h6 class="text-white-50 text-uppercase small mb-1">Below Minimum</h6>
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
                    <h5 class="mb-0 font-weight-bold text-dark">High-Risk Stock Analysis (Top 10)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Item Name</th>
                                    <th class="border-0">Store</th>
                                    <th class="border-0">Available</th>
                                    <th class="border-0">Min Level</th>
                                    <th class="border-0">Status</th>
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
                                            <span class="badge badge-danger">CRITICAL</span>
                                        @elseif($item['near_expiry_qty'] > 0)
                                            <span class="badge badge-warning">EXPIRY RISK</span>
                                        @else
                                            <span class="badge badge-success">HEALTHY</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="/stock-management" class="btn btn-sm btn-link font-weight-bold">Open Inventory Module <i class="mdi mdi-open-in-new"></i></a>
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
</style>
@endsection
