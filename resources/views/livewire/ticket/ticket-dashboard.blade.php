<div>
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <!-- Dashboard Subtitle -->
    <div class="lab-dashboard-subtitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">Comprehensive overview of your ticket activity, status, and trends</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 text-primary" style="font-size: 1rem; font-weight: 500;">
                    <i class="mdi mdi-calendar"></i>
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #3498db;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['total'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-ticket" style="color: #3498db;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Total Tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #6c757d;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['draft'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-file-document-edit" style="color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Draft Tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #17a2b8;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['open'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-ticket-account" style="color: #17a2b8;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Open Tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #ffc107;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['in_progress'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-progress-check" style="color: #ffc107;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Tickets In Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #28a745;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['resolved'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-check-circle" style="color: #28a745;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Resolved Tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #dc3545;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['categories'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-tag" style="color: #dc3545;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Ticket Categories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #9b59b6;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $inProgressTickets->count() }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-clock-outline" style="color: #9b59b6;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Active Tickets</p>
                            @if($totalUnread > 0)
                                <span class="badge badge-pill badge-danger"
                                    title="{{ $totalUnread }} total unread messages">
                                    <i class="mdi mdi-message-text"></i> {{ $totalUnread }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left: 4px solid #e67e22;">
                <div class="kpi-card-body">
                    <div class="kpi-card-content">
                        <div class="kpi-card-row">
                            <h4 class="kpi-card-value">{{ $stats['archived'] ?? 0 }}</h4>
                            <div class="kpi-card-icon">
                                <i class="mdi mdi-archive" style="color: #e67e22;"></i>
                            </div>
                        </div>
                        <div class="kpi-card-row">
                            <p class="kpi-card-label">Archived Tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card date-filter-card"
                style="background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                            <div class="d-flex align-items-center">
                                <label for="startDate" class="me-2 fw-bold">From:</label>
                                <input type="date" id="startDate" wire:model.live="startDate"
                                    class="form-control form-control-sm" style="width: 140px;">
                            </div>

                            <div class="d-flex align-items-center">
                                <label for="endDate" class="me-2 fw-bold">To:</label>
                                <input type="date" id="endDate" wire:model.live="endDate"
                                    class="form-control form-control-sm" style="width: 140px;">
                            </div>

                            <div class="d-flex align-items-center">
                                <button wire:click="setDateRange('today')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">Today</button>
                                <button wire:click="setDateRange('yesterday')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">Yesterday</button>
                                <button wire:click="setDateRange('week')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Week</button>
                                <button wire:click="setDateRange('month')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Month</button>
                                <button wire:click="setDateRange('quarter')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Quarter</button>
                                <button wire:click="setDateRange('year')"
                                    class="btn btn-sm btn-outline-info me-1 mr-2 date-range-btn">This Year</button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <span class="text-primary me-3" style="font-size: 1rem; font-weight: 500;">
                                <i class="mdi mdi-calendar-range"></i>
                                Data for period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                                {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="quick-actions-horizontal"
                style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-lightning-bolt"></i> Quick Actions
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('tickets.create') }}"
                            class="btn btn-sm btn-outline-primary action-btn quick-action-btn"
                            style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-plus-circle"></i> Create Ticket
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('tickets.index') }}"
                            class="btn btn-sm btn-outline-info action-btn quick-action-btn"
                            style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-view-list"></i> View All Tickets
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('tickets.categories') }}"
                            class="btn btn-sm btn-outline-success action-btn quick-action-btn"
                            style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-tag"></i> Categories
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('tickets.deleted') }}"
                            class="btn btn-sm btn-outline-secondary action-btn quick-action-btn"
                            style="display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; color: black; border-radius: 8px;">
                            <i class="mdi mdi-archive"></i> Archived Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container"
                style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-pie"></i> Ticket Status Distribution
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="ticketStatusChart"></canvas>
                </div>
                <!-- Status Summary Table -->
                <div class="mt-3" id="statusSummaryTable" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 0.875rem;">
                            <thead class="thead-light" style="background-color: #f8f9fa;">
                                <tr>
                                    <th style="font-weight: 600;">Status</th>
                                    <th class="text-center" style="font-weight: 600;">Count</th>
                                    <th class="text-center" style="font-weight: 600;">Percentage</th>
                                </tr>
                            </thead>
                            <tbody id="statusSummaryBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="chart-container"
                style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-line"></i> Ticket Trend - {{ $trendLabel }}
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="ticketTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="chart-container"
                style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="mb-3">
                    <i class="mdi mdi-chart-bar"></i> Tickets by Category
                </h5>
                <div class="chart-wrapper" style="height: 300px; position: relative;">
                    <canvas id="ticketCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items Row -->
    <div class="row mb-4">
        <!-- Open Tickets -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-ticket-account"></i> Open Tickets
                    </h5>
                </div>
                <div class="card-body">
                    @if($openTickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($openTickets as $ticket)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tickets.show', $ticket->id) }}" class="text-primary">
                                                    <strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong>
                                                </a>
                                            </td>
                                            <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $ticket->statusBadge }}">
                                                    {{ $ticket->workflowName }}
                                                </span>
                                            </td>
                                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No open tickets.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Progress Tickets -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-warning text-dark" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-progress-check"></i> In Progress Tickets
                    </h5>
                </div>
                <div class="card-body">
                    @if($inProgressTickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Assigned To</th>
                                        <th>TAT</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inProgressTickets as $ticket)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tickets.show', $ticket->id) }}" class="text-primary">
                                                    <strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong>
                                                </a>
                                                @if(isset($unreadCounts[$ticket->id]) && $unreadCounts[$ticket->id] > 0)
                                                    <span class="badge badge-danger ml-1"
                                                        title="{{ $unreadCounts[$ticket->id] }} unread messages">
                                                        <i class="mdi mdi-message-text"></i> {{ $unreadCounts[$ticket->id] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($ticket->assignedUser)
                                                    {{ $ticket->assignedUser->name }}
                                                @elseif($ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0)
                                                    {{ $ticket->assignedDevelopers->pluck('name')->implode(', ') }}
                                                @else
                                                    Unassigned
                                                @endif
                                            </td>
                                            <td>{{ $ticket->tat_display }}</td>
                                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No tickets in progress.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets Row -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-clock-outline"></i> Recent Tickets
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentTickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created</th>
                                        <th>TAT</th>
                                        <th></th> {{-- New column for unread badge --}}
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTickets as $ticket)
                                        <tr>
                                            <td>
                                                <strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong>
                                            </td>
                                            <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($ticket->description, 50) }}</td>
                                            <td>
                                                <span class="badge {{ $ticket->statusBadge }}">
                                                    {{ $ticket->workflowName }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ticket->priorityBadge }}">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </td>
                                            <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $ticket->tat_display }}</td>
                                            <td>
                                                @if(isset($unreadCounts[$ticket->id]) && $unreadCounts[$ticket->id] > 0)
                                                    <span class="badge badge-danger"
                                                        title="{{ $unreadCounts[$ticket->id] }} unread messages">
                                                        <i class="mdi mdi-message-text"></i> {{ $unreadCounts[$ticket->id] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('tickets.show', $ticket->id) }}"
                                                    class="btn btn-sm btn-outline-info">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="mdi mdi-information" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No tickets yet</h5>
                            <p>You haven't submitted any tickets yet.</p>
                            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create Your First Ticket
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            min-height: 70px;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .kpi-card-body {
            padding: 0.75rem;
        }

        .kpi-card-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: space-between;
        }

        .kpi-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kpi-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #2d3748;
            line-height: 1;
        }

        .kpi-card-label {
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0;
        }

        .kpi-card-icon {
            font-size: 2rem;
            color: var(--widget-color, #10b981);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .quick-action-btn:hover {
            color: white !important;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .chart-container {
            transition: transform 0.3s ease;
        }

        .chart-container:hover {
            transform: translateY(-2px);
        }

        .date-filter-card {
            transition: all 0.3s ease;
        }

        .date-filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .date-range-btn {
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
        }

        .date-range-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            let statusChart = null;
            let trendChart = null;
            let categoryChart = null;

            function initCharts() {
                // Status Distribution Chart (Donut)
                const statusCtx = document.getElementById('ticketStatusChart');
                if (statusCtx && @json($statusDistribution).length > 0) {
                    if (statusChart) statusChart.destroy();

                    const statusData = @json($statusDistribution);
                    const colors = {
                        'bg-primary': '#007bff',
                        'bg-secondary': '#6c757d',
                        'bg-success': '#28a745',
                        'bg-danger': '#dc3545',
                        'bg-warning': '#ffc107',
                        'bg-info': '#17a2b8',
                    };

                    // Calculate total and percentages
                    const total = statusData.reduce((sum, item) => sum + item.value, 0);
                    const statusDataWithPercent = statusData.map(item => ({
                        ...item,
                        percentage: total > 0 ? ((item.value / total) * 100).toFixed(1) : 0
                    }));

                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: statusDataWithPercent.map(item => `${item.label} (${item.value})`),
                            datasets: [{
                                data: statusDataWithPercent.map(item => item.value),
                                backgroundColor: statusDataWithPercent.map(item => {
                                    const colorClass = item.color || 'bg-secondary';
                                    return colors[colorClass] || '#6c757d';
                                }),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        },
                                        generateLabels: function (chart) {
                                            const data = chart.data;
                                            if (data.labels.length && data.datasets.length) {
                                                return data.labels.map((label, i) => {
                                                    const value = data.datasets[0].data[i];
                                                    const percentage = statusDataWithPercent[i].percentage;
                                                    const color = data.datasets[0].backgroundColor[i];
                                                    return {
                                                        text: `${label} - ${percentage}%`,
                                                        fillStyle: color,
                                                        strokeStyle: color,
                                                        lineWidth: 2,
                                                        hidden: false,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const label = statusDataWithPercent[context.dataIndex].label;
                                            const value = context.parsed;
                                            const percentage = statusDataWithPercent[context.dataIndex].percentage;
                                            return `${label}: ${value} tickets (${percentage}%)`;
                                        },
                                        footer: function (tooltipItems) {
                                            return `Total: ${total} tickets`;
                                        }
                                    },
                                    padding: 12,
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgba(255, 255, 255, 0.1)',
                                    borderWidth: 1
                                }
                            },
                            animation: {
                                animateRotate: true,
                                animateScale: true
                            }
                        },
                        plugins: [{
                            id: 'centerText',
                            beforeDraw: function (chart) {
                                if (total > 0) {
                                    const ctx = chart.ctx;
                                    const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                                    const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;

                                    ctx.save();
                                    ctx.font = 'bold 28px Arial';
                                    ctx.fillStyle = '#2d3748';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(total.toString(), centerX, centerY - 8);

                                    ctx.font = '12px Arial';
                                    ctx.fillStyle = '#6b7280';
                                    ctx.fillText('Total Tickets', centerX, centerY + 18);
                                    ctx.restore();
                                }
                            }
                        }]
                    });

                    // Populate summary table
                    const summaryTable = document.getElementById('statusSummaryTable');
                    const summaryBody = document.getElementById('statusSummaryBody');
                    if (summaryTable && summaryBody) {
                        summaryBody.innerHTML = '';
                        statusDataWithPercent.forEach(item => {
                            const row = document.createElement('tr');
                            const colorClass = item.color || 'bg-secondary';
                            const color = colors[colorClass] || '#6c757d';
                            row.innerHTML = `
                                <td>
                                    <span style="display: inline-block; width: 12px; height: 12px; background-color: ${color}; border-radius: 2px; margin-right: 8px;"></span>
                                    ${item.label}
                                </td>
                                <td class="text-center"><strong>${item.value}</strong></td>
                                <td class="text-center">${item.percentage}%</td>
                            `;
                            summaryBody.appendChild(row);
                        });
                        summaryTable.style.display = 'block';
                    }
                } else {
                    // Hide summary table if no data
                    const summaryTable = document.getElementById('statusSummaryTable');
                    if (summaryTable) {
                        summaryTable.style.display = 'none';
                    }
                }

                // Ticket Trend Chart (Line)
                const trendCtx = document.getElementById('ticketTrendChart');
                if (trendCtx && @json($ticketTrends).length > 0) {
                    if (trendChart) trendChart.destroy();

                    const trendData = @json($ticketTrends);
                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: trendData.map(item => item.date),
                            datasets: [{
                                label: 'Tickets Created',
                                data: trendData.map(item => item.count),
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
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

                // Category Distribution Chart (Bar)
                const categoryCtx = document.getElementById('ticketCategoryChart');
                if (categoryCtx && @json($categoryDistribution).length > 0) {
                    if (categoryChart) categoryChart.destroy();

                    const categoryData = @json($categoryDistribution);
                    categoryChart = new Chart(categoryCtx, {
                        type: 'bar',
                        data: {
                            labels: categoryData.map(item => item.label),
                            datasets: [{
                                label: 'Tickets',
                                data: categoryData.map(item => item.value),
                                backgroundColor: '#007bff',
                                borderColor: '#0056b3',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
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
            }

            // Initialize charts on mount
            initCharts();

            // Re-initialize charts when data updates
            Livewire.on('chartsDataUpdated', () => {
                setTimeout(() => {
                    initCharts();
                }, 100);
            });
        });
    </script>
</div>