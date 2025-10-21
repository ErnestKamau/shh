<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-multiple text-primary"></i>
                                Document Management System
                            </h2>
                            <p class="text-muted mb-0">Dashboard Overview</p>
                        </div>
                        <button wire:click="refreshStats" class="btn btn-outline-primary">
                            <i class="mdi mdi-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Documents</h6>
                            <h3 class="mb-0">{{ $stats['total_documents'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="mdi mdi-file-document" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">My Documents</h6>
                            <h3 class="mb-0">{{ $stats['my_documents'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="mdi mdi-account-box" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Approvals</h6>
                            <h3 class="mb-0">{{ $stats['pending_approvals'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="mdi mdi-clock-alert" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Expiring Soon</h6>
                            <h3 class="mb-0 text-danger">{{ $stats['expiring_soon'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="mdi mdi-alert" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Documents Alert -->
    @if($expiringDocuments->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning shadow-sm" style="border-radius: 15px;">
                <h5 class="alert-heading">
                    <i class="mdi mdi-alert-circle-outline"></i> Documents Expiring Soon
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Document Number</th>
                                <th>Type</th>
                                <th>Expiry Date</th>
                                <th>Days Remaining</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringDocumentsWithDays->take(5) as $item)
                            <tr>
                                <td>{{ $item['document']->title }}</td>
                                <td>{{ $item['document']->document_number }}</td>
                                <td>{{ $item['document']->documentType->name }}</td>
                                <td>{{ $item['document']->expiry_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $item['badge_class'] }}">
                                        {{ $item['days_remaining'] }} day(s)
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dms.active') }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($expiringDocuments->count() > 5)
                <div class="mt-3">
                    <a href="{{ route('dms.active') }}" class="btn btn-sm btn-warning">
                        View All {{ $expiringDocuments->count() }} Expiring Documents
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Notifications -->
    @if($unreadNotifications->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="mdi mdi-bell text-primary"></i> Notifications 
                            <span class="badge badge-primary">{{ $unreadNotifications->count() }}</span>
                        </h6>
                        <button wire:click="markAllNotificationsRead" class="btn btn-sm btn-outline-primary">
                            Mark All Read
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($unreadNotifications->take(5) as $notification)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $notification->title }}</h6>
                                    <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <button wire:click="markNotificationRead({{ $notification->id }})" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-check"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-file-document text-primary"></i> Recent Documents
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDocuments as $doc)
                                <tr>
                                    <td>
                                        <strong>{{ $doc->title }}</strong><br>
                                        <small class="text-muted">{{ $doc->document_number }}</small>
                                    </td>
                                    <td>{{ $doc->documentType->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'pending_approval' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-pencil text-warning"></i> Recent Amendments
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Document</th>
                                    <th>Requester</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAmendments as $amendment)
                                <tr>
                                    <td>
                                        <strong>{{ $amendment->document->title }}</strong><br>
                                        <small class="text-muted">{{ $amendment->amendment_reason }}</small>
                                    </td>
                                    <td>{{ $amendment->requester->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $amendment->status === 'approved' ? 'success' : ($amendment->status === 'requested' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($amendment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $amendment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amendment Trends Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-chart-line text-info"></i> Amendment Trends (Last 6 Months)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="amendmentTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-lightning-bolt text-warning"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('dms.types') }}" class="btn btn-outline-primary btn-block">
                                <i class="mdi mdi-folder-multiple"></i> Manage Types
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('dms.active') }}" class="btn btn-outline-success btn-block">
                                <i class="mdi mdi-file-document-plus"></i> Upload Document
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('dms.amendments') }}" class="btn btn-outline-warning btn-block">
                                <i class="mdi mdi-pencil"></i> Request Amendment
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('dms.reports') }}" class="btn btn-outline-info btn-block">
                                <i class="mdi mdi-chart-bar"></i> Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('livewire:navigated', function() {
    const ctx = document.getElementById('amendmentTrendsChart');
    if (ctx) {
        const data = @json($amendmentTrends);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.month),
                datasets: [{
                    label: 'Amendments',
                    data: data.map(item => item.count),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

