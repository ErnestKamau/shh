@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title>Dashboard | Lab</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<style type="text/css">
    :root {
        --primary-glass: #ffffff;
        --accent-blue: var(--color-primary);
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-orange: #f59e0b;
        --bg-color: #f8fafc;
        --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.08);
    }
    body { background-color: var(--bg-color); }
    .bento-card {
        background: var(--primary-glass);
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: var(--card-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .bento-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }
    .pipeline-card { padding: var(--space-md); text-align: center; position: relative; cursor: pointer; }
    .pipeline-arrow { 
        position: absolute; right: -15px; top: 50%; transform: translateY(-50%);
        font-size: var(--text-xl); color: #e2e8f0; z-index: 10;
    }
    .stat-value { font-size: var(--text-metric); font-weight: var(--font-bold); line-height: var(--leading-tight); margin: 0.5rem 0; color: var(--color-text); }
    .stat-label { font-size: var(--text-caption); font-weight: var(--font-semibold); text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-muted); }
    .stat-subtext { font-size: var(--text-caption); color: var(--color-muted); margin-top: 0.35rem; }

    .section-header { padding: var(--space-sm) var(--space-md); border-bottom: 1px solid var(--color-border); font-size: var(--text-base); font-weight: var(--font-semibold); color: var(--color-text); display: flex; align-items: center; justify-content: space-between;}
    .section-body { padding: var(--space-md); }
    
    .method-list-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; }
    .method-list-item:last-child { border-bottom: none; }
    .method-name { font-weight: var(--font-medium); color: var(--color-text-secondary); font-size: var(--text-sm); }
    .method-bar-bg { height: 6px; background: #e2e8f0; border-radius: 3px; width: 100px; overflow: hidden; }
    .method-bar-fill { height: 100%; border-radius: 3px; background-color: var(--accent-blue); }

    .pulse-dot {
        height: 10px; width: 10px; border-radius: 50%; display: inline-block;
        animation: pulse 2.5s infinite;
    }
    .pulse-red { background-color: var(--accent-red); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    .pulse-yellow { background-color: var(--accent-orange); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    .pulse-green { background-color: var(--accent-green); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(var(--box-color), 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0); }
    }

    .nav-tabs.modern-tabs { border-bottom: 2px solid #e2e8f0; }
    .nav-tabs.modern-tabs .nav-link { border: none; color: var(--color-muted); font-size: var(--text-sm); font-weight: var(--font-semibold); padding: 12px 24px; position: relative; background: transparent; }
    .nav-tabs.modern-tabs .nav-link.active { color: var(--color-primary); background: transparent; }
    .nav-tabs.modern-tabs .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--color-primary); }

    .smart-table th { background: #f8fafc; font-size: var(--text-caption); font-weight: var(--font-semibold); text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-muted); border-top: none; }
    .smart-table td { vertical-align: middle; font-size: var(--text-sm); font-weight: var(--font-medium); color: var(--color-text); border-color: #f1f5f9; }
    .priority-Urgent, .priority-High { color: var(--accent-red); font-weight: 600; }
    .priority-Normal { color: #64748b; }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array('link' => null, 'name' => 'Dashboard', 'icon' => null)
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <div data-sf-slot="after_breadcrumb"></div>
    @include('layouts.partials.dashboard-page-styles')
    <div class="p-4 lab-dashboard-page workflow-theme">
        <!-- HEADER -->
        <div class="dashboard-welcome-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1">{{ __('dashboard.welcome_back') }}, {{ explode(' ', Auth::user()->name)[0] }} 👋</h3>
                <p class="text-muted mb-0 dashboard-welcome-subtitle">{{ \Carbon\Carbon::now()->format('l, jS F Y') }} &mdash; {{ __('dashboard.overview_of_lab_operations') }}</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if(isset($complaint) && count($complaint) > 0)
                <div class="bento-card px-3 py-2 mr-2 d-flex align-items-center cursor-pointer" onclick="$('#tab-complaints-link').click()">
                    <span class="pulse-dot pulse-red mr-2" style="--box-color: 239, 68, 68;"></span>
                    <span class="font-weight-bold text-danger text-sm dashboard-hero-chip">{{ count($complaint) }} Active Complaints</span>
                </div>
                @else
                <div class="bento-card px-3 py-2 mr-2 d-flex align-items-center">
                    <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
                    <span class="font-weight-bold text-success text-sm dashboard-hero-chip">Zero Complaints</span>
                </div>
                @endif
                
                <div class="bento-card px-3 py-2 mr-2 d-flex align-items-center cursor-pointer" onclick="$('#tab-tat-link').click()">
                    <span class="pulse-dot mr-2" style="background-color: var(--accent-orange); --box-color: 245, 158, 11;"></span>
                    <span class="font-weight-bold text-sm dashboard-hero-chip" style="color: #f59e0b;">{{ $tat_warnings_count ?? 0 }} TAT Warnings</span>
                </div>

                <div class="bento-card px-3 py-2 d-flex align-items-center">
                    <span class="pulse-dot mr-2" style="background-color: var(--accent-blue); box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4); --box-color: 14, 165, 233;"></span>
                    <span class="font-weight-bold text-muted text-sm dashboard-hero-chip">{{ isset($notifications) ? count($notifications) : 0 }} Notifications</span>
                </div>
            </div>
        </div>
        </div>

        <!-- HERO PIPELINE -->
        <div class="row mb-4">
            <!-- Intake -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='/sample-workflow/Samples%20Receiving'">
                    <div class="stat-label text-info"><i class="fas fa-inbox"></i> Samples Receiving</div>
                    <div class="stat-value">{{ $samples_receiving ?? 0 }}</div>
                    <div class="stat-subtext">Awaiting receipt at lab</div>
                    <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
                </div>
            </div>
            <!-- Prep -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='/sample-workflow/Sample%20Verification'">
                    <div class="stat-label text-warning"><i class="fas fa-barcode"></i> {{ __('dashboard.sample_verification') }}</div>
                    <div class="stat-value">{{ $samples_verification ?? 0 }}</div>
                    <div class="stat-subtext">{{ __('dashboard.awaiting_verification') }}</div>
                    <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
                </div>
            </div>
            <!-- Lab -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='/sample-workflow/Samples%20In%20Lab'">
                    <div class="stat-label text-primary"><i class="fas fa-flask"></i> Samples In Lab</div>
                    <div class="stat-value">{{ $samples_lab ?? 0 }}</div>
                    <div class="stat-subtext">Active Tests in Lab</div>
                    <i class="fas fa-chevron-right pipeline-arrow d-none d-md-block"></i>
                </div>
            </div>
            <!-- Approval -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='/sample-workflow/Sample%20Approval'">
                    <div class="stat-label text-success"><i class="fas fa-check-double"></i> Sample Approval</div>
                    <div class="stat-value">{{ $samples_approval ?? 0 }}</div>
                    <div class="stat-subtext">Awaiting Sign-off</div>
                </div>
            </div>
        </div>

        <div data-sf-slot="after_stats_row"></div>
        <!-- GEOGRAPHY & LAB SECTIONS -->
        <div class="row mb-4">
            <!-- Geographic Pulse Map -->
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-map-marked-alt text-muted mr-2"></i> Geographic Origins</span>
                        <div class="badge badge-light">GPS Tracking</div>
                    </div>
                    <div class="section-body p-0">
                        <div id="sample-maps" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Lab Sections Bar Chart -->
            <div class="col-lg-5">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-layer-group text-muted mr-2"></i> Samples by Lab Section</span>
                    </div>
                    <div class="section-body p-4 d-flex justify-content-center align-items-center" style="height: 350px;">
                        <canvas id="lab-section-chart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRENDS TIER -->
        <div class="row mb-4">
            <!-- Monthly Trends -->
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-chart-area text-muted mr-2"></i> Monthly Intake Volume</span>
                        <select id="trendYearSelector" class="form-control form-control-sm w-auto border-0 bg-light"><option value="2026">2026</option><option value="2025">2025</option></select>
                    </div>
                    <div class="section-body">
                        <canvas id="monthly-trend-chart" style="width:100%; height:200px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Active Methods Leaderboard -->
            <div class="col-lg-6">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-microscope text-muted mr-2"></i> Active Methods Workload</span>
                    </div>
                    <div class="section-body p-3" id="active-methods-container">
                        <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading methods...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUSINESS INTELLIGENCE TIER -->
        <div class="row mb-4">
            <!-- Popular Sample Types -->
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-vials text-muted mr-2"></i> Popular Sample Types</span>
                    </div>
                    <div class="section-body p-3" id="popular-samples-container">
                        <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Popular Clients -->
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="bento-card h-100">
                    <div class="section-header">
                        <span><i class="fas fa-users text-muted mr-2"></i> Top Volume Clients</span>
                    </div>
                    <div class="section-body p-3" id="popular-clients-container">
                        <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Billing & Invoicing -->
            <div class="col-lg-4">
                <div class="bento-card h-100 p-4 d-flex flex-column justify-content-center">
                    <div class="text-muted font-weight-bold text-uppercase mb-3 text-sm" style="letter-spacing: 0.05em;">{{ date('F Y') }} Billing Basic Stats</div>
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded p-3 mr-3" style="background-color: rgba(14, 165, 233, 0.1);"><i class="fas fa-file-invoice-dollar text-primary fa-lg"></i></div>
                        <div>
                            <div class="h3 mb-0 font-weight-bold" style="color:#1e293b;">{{ \App\Invoice::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count() }}</div>
                            <div class="small text-muted font-weight-bold">Invoices Generated</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded p-3 mr-3" style="background-color: rgba(16, 185, 129, 0.1);"><i class="fas fa-money-check-alt text-success fa-lg"></i></div>
                        <div>
                            <div class="h3 mb-0 font-weight-bold" style="color:#1e293b;">{{ \App\InvoicePaymentDetail::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count() }}</div>
                            <div class="small text-muted font-weight-bold">Payments Received</div>
                        </div>
                    </div>
                    <div class="mt-auto pt-2">
                        <a href="/billing/invoices" class="btn btn-sm btn-outline-primary btn-block rounded-pill font-weight-bold py-2"><i class="fas fa-external-link-alt mr-1"></i> Open Billing Center</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CLIENT ANALYSIS TIER -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bento-card">
                    <div class="section-header">
                        <span><i class="fas fa-chart-bar text-muted mr-2"></i> Client Intake by Sample Type</span>
                    </div>
                    <div class="section-body p-4" style="height: 350px;">
                        <canvas id="customer-sample-type-chart" style="width:100%; height:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMART ACTION GRID -->
        <div class="row">
            <div class="col-12">
                <div class="bento-card">
                    <div class="d-flex justify-content-between align-items-center border-bottom px-4 pt-3 pb-0">
                        <h5 class="font-weight-bold mb-0" style="color:#1e293b; font-size: 1.1rem;">Workspace</h5>
                        <ul class="nav nav-tabs modern-tabs" id="actionGridTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-my_tasks" data-toggle="tab" href="#grid-pane" role="tab" onclick="loadGrid('my_tasks')">🎯 My Tasks</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger" id="tab-urgent" data-toggle="tab" href="#grid-pane" role="tab" onclick="loadGrid('urgent')">🔴 Urgent Queue</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-approvals" data-toggle="tab" href="#grid-pane" role="tab" onclick="loadGrid('approvals')">📝 Pending Approvals</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-pending-submissions" data-toggle="tab" href="#grid-pane" role="tab" onclick="loadGrid('pending_submissions')">📄 Pending Submission Forms</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-warning" id="tab-tat-link" data-toggle="tab" href="#grid-pane" role="tab" onclick="loadGrid('tat_awareness')">⏳ TAT Watchlist</a>
                            </li>
                            <li class="nav-item border-left ml-2 pl-2">
                                <a class="nav-link text-danger" id="tab-complaints-link" data-toggle="tab" href="#complaints-pane" role="tab">⚠️ Active Complaints</a>
                            </li>
                        </ul>
                    </div>
                    <div class="section-body p-0">
                        <div class="tab-content">
                            <!-- Main Smart Grid -->
                            <div class="tab-pane fade show active p-0" id="grid-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table smart-table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-4">Priority</th>
                                                <th>Batch Ref</th>
                                                <th>Client</th>
                                                <th>Sample Type</th>
                                                <th>Status</th>
                                                <th>Target Date</th>
                                                <th class="text-right pr-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="smart-grid-body">
                                            <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Loading tasks...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Complaints Grid -->
                            <div class="tab-pane fade p-0" id="complaints-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table smart-table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-4">Reference</th>
                                                <th>Detail</th>
                                                <th class="text-right pr-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($complaint) && count($complaint) > 0)
                                                @foreach($complaint as $c)
                                                <tr>
                                                    <td class="pl-4 font-weight-bold text-danger">Complaint ID #{{ $c->id }}</td>
                                                    <td>Requires attention in the CRM Quality module.</td>
                                                    <td class="text-right pr-4"><a href="/crm/complaints-manager" class="btn btn-sm btn-outline-danger rounded px-3">Review</a></td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="3" class="text-center py-4 text-muted">Zero active complaints! <i class="fas fa-glass-cheers text-success ml-1"></i></td></tr>
                                            @endif
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

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    // Softer color palette for simpler UI
    const CHART_COLOR_BLUE = 'rgba(14, 165, 233, 0.8)';
    const CHART_COLOR_BLUE_BG = 'rgba(14, 165, 233, 0.15)';

    function loadGrid(tabName) {
        if(tabName == 'intake' || tabName == 'prep'){
            tabName = (tabName == 'intake') ? 'my_tasks' : 'urgent';
        }
        
        $('#smart-grid-body').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Syncing queue...</td></tr>');
        
        $.get('/getSmartGridTasks', { tab: tabName }, function(data) {
            if(data.length === 0) {
                $('#smart-grid-body').html('<tr><td colspan="7" class="text-center py-4 text-muted">Queue is clear! <i class="fas fa-check-circle text-success ml-1"></i></td></tr>');
                return;
            }
            
            let html = '';
            data.forEach(item => {
                let prioClass = `priority-${item.priority}`;
                let prioIcon = item.priority === 'Urgent' ? '<i class="fas fa-exclamation-circle mr-1"></i>' : '';
                
                let tatDisplay = item.target_date;
                if (tabName === 'tat_awareness' && item.tat_status) {
                    let badgeColor = item.tat_status.includes('Overdue') ? 'badge-danger' : (item.tat_status.includes('Today') ? 'badge-warning' : 'badge-info');
                    tatDisplay = `${item.target_date} <br><small class="badge ${badgeColor} mt-1">${item.tat_status}</small>`;
                }
                
                const detailUrl = item.detail_url ? item.detail_url : `/sample-workflow/batch/${item.id}/details`;
                const isSubmissionItem = tabName === 'pending_submissions';
                const sampleTypeLabel = isSubmissionItem ? 'Submitted By' : 'Sample Type';
                let statusClass = item.status == 'Sample Approval' ? 'badge-success' : 'badge-info';
                if (isSubmissionItem) {
                    statusClass = item.status.toLowerCase().includes('draft') ? 'badge-warning' : 'badge-primary';
                }

                html += `<tr style="cursor:pointer;" onclick="window.location.href='${detailUrl}'">
                    <td class="pl-4 ${prioClass}">${prioIcon}${item.priority}</td>
                    <td class="font-weight-bold text-primary">${item.batch_code}</td>
                    <td>${item.client_name}</td>
                    <td><span class="badge badge-light px-2 py-1 text-secondary border" title="${sampleTypeLabel}">${item.sample_type}</span></td>
                    <td><span class="badge ${statusClass} px-2 py-1 text-white">${item.status}</span></td>
                    <td>${tatDisplay}</td>
                    <td class="text-right pr-4"><button class="btn btn-sm btn-outline-primary rounded px-3">View</button></td>
                </tr>`;
            });
            $('#smart-grid-body').html(html);
        }).fail(function() {
            $('#smart-grid-body').html('<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load datagrid.</td></tr>');
        });
    }

    function loadActiveMethods() {
        $.get('/getActiveMethods', function(data) {
            let html = '';
            if(data.length === 0){ html = '<div class="text-muted text-center py-4">No active methods workload.</div>'; }
            else {
                let maxTotal = data[0].total || 1;
                data.forEach((item) => {
                    let percent = (item.total / maxTotal) * 100;
                    
                    html += `
                    <div class="method-list-item">
                        <div>
                            <div class="method-name">${item.name}</div>
                            <small class="text-muted">${item.total} tests pending</small>
                        </div>
                        <div class="method-bar-bg">
                            <div class="method-bar-fill" style="width: ${percent}%;"></div>
                        </div>
                    </div>`;
                });
            }
            $('#active-methods-container').html(html);
        });
    }

    function loadMonthlyTrend() {
        $.get('/getSamplesByMonth', { year: $('#trendYearSelector').val() }, function(data) {
            let ctx = document.getElementById('monthly-trend-chart').getContext('2d');
            let labels = Object.keys(data);
            let values = Object.values(data);
            
            if(window.trendChart) window.trendChart.destroy();
            
            window.trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Batches Intake',
                        data: values,
                        borderColor: '#0ea5e9',
                        backgroundColor: CHART_COLOR_BLUE_BG,
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        lineTension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: { display: false }, ticks:{fontSize: 10, fontColor: '#64748b'} }],
                        yAxes: [{ gridLines: { borderDash: [4, 4], color: '#f1f5f9' }, ticks:{beginAtZero: true, fontSize:10, fontColor: '#64748b'} }]
                    }
                }
            });
        });
    }

    function loadLabSectionChart() {
        $.get('/getSamplesByLabSection', function(data) {
            let labels = Object.keys(data);
            let values = Object.values(data);
            
            let ctx = document.getElementById('lab-section-chart').getContext('2d');
            if(window.sectionChart) window.sectionChart.destroy();
            
            window.sectionChart = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Samples',
                        data: values,
                        backgroundColor: CHART_COLOR_BLUE,
                        borderRadius: 4,
                        barThickness: 'flex',
                        maxBarThickness: 25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ 
                            gridLines: { borderDash: [4, 4], color: '#f1f5f9' }, 
                            ticks:{beginAtZero: true, fontColor: '#64748b'} 
                        }],
                        yAxes: [{ 
                            gridLines: { display: false }, 
                            ticks: {fontSize: 11, fontColor:'#334155'} 
                        }]
                    }
                }
            });
        });
    }

    function loadMaps() {
        $.get('/getSamplesByGps', function(gps) {
            var mapProp = {
                center: new google.maps.LatLng(-19.015, 29.156),
                zoom: 5.5,
                styles: [
                    { "elementType": "geometry", "stylers": [{"color": "#f8fafc"}] },
                    { "elementType": "labels.icon", "stylers": [{"visibility": "off"}] },
                    { "elementType": "labels.text.fill", "stylers": [{"color": "#64748b"}] },
                    { "elementType": "labels.text.stroke", "stylers": [{"color": "#f8fafc"}] },
                    { "featureType": "water", "elementType": "geometry", "stylers": [{"color": "#e2e8f0"}] }
                ]
            };
            var map = new google.maps.Map(document.getElementById('sample-maps'), mapProp);
            var latArr = Object.keys(gps);
            
            for (var i = 0; i < latArr.length; i++) {
                var coords = latArr[i].split(',');
                var lat = parseFloat(coords[1]);
                var lng = parseFloat(coords[0]);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    new google.maps.Circle({
                        strokeColor: "#ef4444",
                        strokeOpacity: 0.6,
                        strokeWeight: 1,
                        fillColor: "#ef4444",
                        fillOpacity: 0.25,
                        map,
                        center: { lat: lat, lng: lng },
                        radius: 8000 + (gps[latArr[i]] * 1500)
                    });
                }
            }
        });
    }

    function loadSampleTypesList() {
        $.get('/getsamplesBySampletype', { year: $('#trendYearSelector').val() }, function(data) {
            let sorted = Object.entries(data).sort((a,b) => b[1] - a[1]).slice(0,5);
            let html = '';
            if(sorted.length === 0){ html = '<div class="text-muted text-center py-4">No data.</div>'; }
            else {
                let maxTotal = sorted[0][1] || 1;
                sorted.forEach((item) => {
                    let percent = (item[1] / maxTotal) * 100;
                    html += `
                    <div class="method-list-item">
                        <div>
                            <div class="method-name" style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item[0]}</div>
                            <small class="text-muted">${item[1]} batches</small>
                        </div>
                        <div class="method-bar-bg" style="width: 80px;">
                            <div class="method-bar-fill" style="width: ${percent}%; background-color:#10b981;"></div>
                        </div>
                    </div>`;
                });
            }
            $('#popular-samples-container').html(html);
        });
    }

    function loadClientsList() {
        $.get('/getSamplesByCustomer', function(data) {
            let sorted = Object.entries(data).sort((a,b) => b[1] - a[1]).slice(0,5);
            let html = '';
            if(sorted.length === 0){ html = '<div class="text-muted text-center py-4">No data.</div>'; }
            else {
                let maxTotal = sorted[0][1] || 1;
                sorted.forEach((item) => {
                    let percent = (item[1] / maxTotal) * 100;
                    html += `
                    <div class="method-list-item">
                        <div>
                            <div class="method-name" style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${item[0]}">${item[0]}</div>
                            <small class="text-muted">${item[1]} batches</small>
                        </div>
                        <div class="method-bar-bg" style="width: 80px;">
                            <div class="method-bar-fill" style="width: ${percent}%; background-color:#f59e0b;"></div>
                        </div>
                    </div>`;
                });
            }
            $('#popular-clients-container').html(html);
        });
    }

    function loadCustomerSampleTypesChart() {
        $.get('/getCustomerSampleTypes', function(response) {
            let ctx = document.getElementById('customer-sample-type-chart').getContext('2d');
            if(window.customerSampleChart) window.customerSampleChart.destroy();
            
            let datasets = [];
            const STACK_PALETTE = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#64748b', '#06b6d4', '#84cc16'];
            
            response.types.forEach((type, index) => {
                let dataPoints = [];
                response.clients.forEach(client => {
                    dataPoints.push(response.data[client][type] || 0);
                });
                
                datasets.push({
                    label: type,
                    data: dataPoints,
                    backgroundColor: STACK_PALETTE[index % STACK_PALETTE.length],
                    maxBarThickness: 45
                });
            });

            window.customerSampleChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: response.clients,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, fontSize: 11, fontColor: '#64748b' }
                    },
                    scales: {
                        xAxes: [{ 
                            stacked: true,
                            gridLines: { display: false }, 
                            ticks: { fontSize: 11, fontColor: '#334155' } 
                        }],
                        yAxes: [{ 
                            stacked: true,
                            gridLines: { borderDash: [4, 4], color: '#f1f5f9' }, 
                            ticks: { beginAtZero: true, fontColor: '#64748b' } 
                        }]
                    }
                }
            });
        });
    }

    $(document).ready(function() {
        loadGrid('my_tasks');
        loadActiveMethods();
        loadMonthlyTrend();
        loadLabSectionChart();
        loadSampleTypesList();
        loadClientsList();
        loadCustomerSampleTypesChart();
        setTimeout(loadMaps, 1000); 
        
        $('#trendYearSelector').on('change', function() {
            loadMonthlyTrend();
            loadSampleTypesList();
        });
    });
</script>
@endsection