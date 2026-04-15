@extends('layouts.mas.layout.app')

@section('content2')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">Executive Overview</h1>
            <p class="text-muted small mb-0">High-level KPIs from across all modules.</p>
        </div>
        <div class="col-auto">
            <span class="badge badge-info p-2 shadow-sm">
                <i class="mdi mdi-calendar mr-1"></i> {{ date('Y-m-d H:i') }}
            </span>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row">
        <!-- Lab TAT Performance -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #2563eb !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">TAT Performance</h6>
                        <i class="mdi mdi-timer-outline mdi-24px text-primary"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['total_samples'] }}</h2>
                            <p class="text-muted small mb-0">Active Batches</p>
                        </div>
                        <a href="{{ route('mas.lab.tat') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-primary">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Lab Analytics -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #4f46e5 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Lab Analytics</h6>
                        <i class="mdi mdi-chart-bubble mdi-24px" style="color: #4f46e5;"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['sample_types_count'] }}</h2>
                            <p class="text-muted small mb-0">Active Sample Types</p>
                        </div>
                        <a href="{{ route('mas.lab.general') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold" style="color: #4f46e5;">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory KPI -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Stock Health</h6>
                        <i class="mdi mdi-package-variant-alert mdi-24px text-warning"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['inventory_alerts'] }}</h2>
                            <p class="text-muted small mb-0">Reorder Alerts</p>
                        </div>
                        <a href="{{ route('mas.inventory') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-warning">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financials KPI -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #17a2b8 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Financials</h6>
                        <i class="mdi mdi-currency-usd mdi-24px text-info"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">${{ $stats['unpaid_billing'] }}</h2>
                            <p class="text-muted small mb-0">Unpaid Invoices</p>
                        </div>
                        <a href="{{ route('mas.crm') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-info">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Profile KPI -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Risk Profile</h6>
                        <i class="mdi mdi-alert-octagon-outline mdi-24px text-danger"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['high_risks'] }}</h2>
                            <p class="text-muted small mb-0">Critical Threats</p>
                        </div>
                        <a href="{{ route('mas.risk') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-danger">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Intelligence KPI -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #6f42c1 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">AI Intelligence</h6>
                        <i class="mdi mdi-head-snowflake mdi-24px text-purple" style="color: #6f42c1;"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['ai_health'] }}%</h2>
                            <p class="text-muted small mb-0">System Health</p>
                        </div>
                        <a href="{{ route('mas.ai') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-purple" style="color: #6f42c1;">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- QC & Stability -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #20c997 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">QC & Stability</h6>
                        <i class="mdi mdi-shield-check mdi-24px text-teal" style="color: #20c997;"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['qc_pending'] }}</h2>
                            <p class="text-muted small mb-0">Pending Verification</p>
                        </div>
                        <a href="{{ route('mas.lab.qc') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-teal" style="color: #20c997;">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Lifecycle -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #fd7e14 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Asset Lifecycle</h6>
                        <i class="mdi mdi-tools mdi-24px text-orange" style="color: #fd7e14;"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['equip_overdue'] }}</h2>
                            <p class="text-muted small mb-0">Maint. Overdue</p>
                        </div>
                        <a href="{{ route('mas.equipment') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-orange" style="color: #fd7e14;">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Human Capital -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #007bff !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Human Capital</h6>
                        <i class="mdi mdi-account-star mdi-24px text-primary"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['staff_count'] }}</h2>
                            <p class="text-muted small mb-0">Active Personnel</p>
                        </div>
                        <a href="{{ route('mas.personnel') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-primary">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Audit -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-left: 5px solid #6c757d !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted text-uppercase font-weight-bold small mb-0">Compliance Audit</h6>
                        <i class="mdi mdi-history mdi-24px text-secondary"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="font-weight-bold mb-1 text-dark">{{ $stats['audit_alerts'] }}</h2>
                            <p class="text-muted small mb-0">Events (7d)</p>
                        </div>
                        <a href="{{ route('mas.audit') }}" class="btn btn-sm btn-link p-0 mt-2 font-weight-bold text-secondary">
                            Details <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Insights (Mini-row) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-light bg-white shadow-sm border-0 d-flex align-items-center">
                <i class="mdi mdi-information-outline mdi-24px text-info mr-3"></i>
                <div>
                    <strong>Pro-tip:</strong> Click on "Details" or use the sidebar to access deep-dive charts and visualizations for each module.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
