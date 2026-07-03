<div>
    <!-- Main Card -->
    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <div class="card-header" style="background: #f8f9fa; border: none; border-left: 6px solid var(--color-primary); padding: 18px 24px;">
            <h4 class="mb-0" style="font-size: 1.35rem; font-weight: 600; color: #222;">
                <i class="mdi mdi-chart-bar"></i> Reports & KPIs
            </h4>
        </div>
        <div class="card-body">
            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="kpi-card" style="border-left: 4px solid #3498db;">
                        <div class="kpi-card-body">
                            <div class="kpi-card-content">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ number_format($kpis['avg_nc_closure_time'] ?? 0, 1) }}</h4>
                                    <div class="kpi-card-icon">
                                        <i class="mdi mdi-clock-outline" style="color: #3498db;"></i>
                                    </div>
                                </div>
                                <div class="kpi-card-row">
                                    <p class="kpi-card-label">Avg NC Closure (Days)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-card" style="border-left: 4px solid #28a745;">
                        <div class="kpi-card-body">
                            <div class="kpi-card-content">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ number_format($kpis['avg_capa_closure_time'] ?? 0, 1) }}</h4>
                                    <div class="kpi-card-icon">
                                        <i class="mdi mdi-timer-sand" style="color: #28a745;"></i>
                                    </div>
                                </div>
                                <div class="kpi-card-row">
                                    <p class="kpi-card-label">Avg CAPA Closure (Days)</p>
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
                                    <h4 class="kpi-card-value">{{ number_format($kpis['effectiveness_rate'] ?? 0, 1) }}%</h4>
                                    <div class="kpi-card-icon">
                                        <i class="mdi mdi-check-circle" style="color: #17a2b8;"></i>
                                    </div>
                                </div>
                                <div class="kpi-card-row">
                                    <p class="kpi-card-label">CAPA Effectiveness Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-card" style="border-left: 4px solid {{ ($kpis['overdue_rate'] ?? 0) > 20 ? '#dc3545' : '#ffc107' }};">
                        <div class="kpi-card-body">
                            <div class="kpi-card-content">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ number_format($kpis['overdue_rate'] ?? 0, 1) }}%</h4>
                                    <div class="kpi-card-icon">
                                        <i class="mdi mdi-alert-circle" style="color: {{ ($kpis['overdue_rate'] ?? 0) > 20 ? '#dc3545' : '#ffc107' }};"></i>
                                    </div>
                                </div>
                                <div class="kpi-card-row">
                                    <p class="kpi-card-label">CAPA Overdue Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr style="margin: 2rem 0; border-top: 1px solid #dee2e6;">

            <!-- Report Links -->
            <h5 class="mb-3" style="font-weight: 600; color: #495057;">
                <i class="mdi mdi-file-document-multiple"></i> Available Reports
            </h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px; height: 100%;">
                        <div class="card-body text-center">
                            <i class="mdi mdi-file-document" style="font-size: 3rem; color: var(--color-primary);"></i>
                            <h6 class="mt-3 mb-2" style="font-weight: 600;">Audit Summary</h6>
                            <p class="text-muted small mb-3">Summary of all audits</p>
                            <a href="{{ route('audit.reports.audit-summary') }}" class="btn btn-sm" style="border-color: var(--color-primary); color: var(--color-primary); font-weight: 600;">
                                <i class="mdi mdi-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px; height: 100%;">
                        <div class="card-body text-center">
                            <i class="mdi mdi-alert-circle" style="font-size: 3rem; color: #dc3545;"></i>
                            <h6 class="mt-3 mb-2" style="font-weight: 600;">NC Register</h6>
                            <p class="text-muted small mb-3">All non-conformances</p>
                            <a href="{{ route('audit.reports.nc-register') }}" class="btn btn-sm btn-outline-danger">
                                <i class="mdi mdi-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px; height: 100%;">
                        <div class="card-body text-center">
                            <i class="mdi mdi-checkbox-marked-circle" style="font-size: 3rem; color: #28a745;"></i>
                            <h6 class="mt-3 mb-2" style="font-weight: 600;">CAPA Status</h6>
                            <p class="text-muted small mb-3">Corrective actions</p>
                            <a href="{{ route('audit.reports.capa-status') }}" class="btn btn-sm btn-outline-success">
                                <i class="mdi mdi-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px; height: 100%;">
                        <div class="card-body text-center">
                            <i class="mdi mdi-chart-line" style="font-size: 3rem; color: #17a2b8;"></i>
                            <h6 class="mt-3 mb-2" style="font-weight: 600;">Advanced Statistics</h6>
                            <p class="text-muted small mb-3">Trends & analysis</p>
                            <a href="{{ route('audit.reports.advanced-statistics') }}" class="btn btn-sm btn-outline-info">
                                <i class="mdi mdi-chart-areaspline"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NC by Origin (Simple List) -->
            @if(!empty($kpis['nc_by_origin']))
            <hr style="margin: 2rem 0; border-top: 1px solid #dee2e6;">
            <h5 class="mb-3" style="font-weight: 600; color: #495057;">
                <i class="mdi mdi-chart-bar"></i> Top NC Origins
            </h5>
            <div class="row">
                @foreach($kpis['nc_by_origin'] as $origin => $count)
                <div class="col-md-2 mb-2">
                    <div class="card" style="border: 1px solid #dee2e6; border-radius: 6px;">
                        <div class="card-body text-center py-2">
                            <h5 class="mb-1" style="font-weight: 700; color: var(--color-primary);">{{ $count }}</h5>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($origin, 12) }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
