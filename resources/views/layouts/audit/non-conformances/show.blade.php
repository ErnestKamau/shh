@extends('layouts.audit.layout.app')

@section('title2')
<title>NC {{ $nc->nc_number }} - JASIRI LIMS</title>
<style type="text/css">
    :root {
        --primary-color: #dc3545;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --light-bg: #f8f9fa;
        --border-color: #e9ecef;
        --text-primary: #2d3748;
        --text-secondary: #6c757d;
        --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        --card-shadow-hover: 0 4px 16px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --border-radius-sm: 8px;
    }

    .nc-show-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
    }

    .nc-show-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .nc-header-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        background: #ffffff;
        margin-bottom: 1.5rem;
    }

    .nc-header-card .card-header {
        background: var(--light-bg);
        border: none;
        border-left: 6px solid var(--danger-color);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .nc-header-card .card-body {
        background: #ffffff;
        padding: 1.5rem 2rem;
    }

    /* Modern Table Styling - Filament-like */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #ffffff;
        border-radius: var(--border-radius-sm);
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .table-modern thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .table-modern thead th {
        background: transparent;
        border: none;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.6875rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.25rem;
        white-space: nowrap;
        position: relative;
    }

    .table-modern thead th:first-child {
        padding-left: 1.5rem;
    }

    .table-modern thead th:last-child {
        padding-right: 1.5rem;
    }

    .table-modern tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.15s ease;
    }

    .table-modern tbody tr:last-child {
        border-bottom: none;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
        box-shadow: inset 0 0 0 1px #e2e8f0;
    }

    .table-modern tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-top: none;
        word-break: break-word;
        color: #334155;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .table-modern tbody td:first-child {
        padding-left: 1.5rem;
        font-weight: 500;
    }

    .table-modern tbody td:last-child {
        padding-right: 1.5rem;
    }

    /* Action buttons in tables */
    .table-modern .btn-group-sm .btn {
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        border-radius: 6px;
        margin-right: 0.25rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .table-modern .btn-group-sm .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table-modern .btn-outline-primary {
        color: #3b82f6;
        border-color: #3b82f6;
    }

    .table-modern .btn-outline-primary:hover {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .table-modern .btn-outline-info {
        color: #06b6d4;
        border-color: #06b6d4;
    }

    .table-modern .btn-outline-info:hover {
        background: #06b6d4;
        border-color: #06b6d4;
        color: white;
    }

    .table-modern .btn-outline-danger {
        color: #ef4444;
        border-color: #ef4444;
    }

    .table-modern .btn-outline-danger:hover {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    /* Badge styling in tables */
    .table-modern .badge {
        font-weight: 500;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        border-radius: 6px;
        letter-spacing: 0.025em;
    }

    /* Empty state in table context */
    .table-modern tbody tr.empty-state-row td {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
        font-style: italic;
    }

    /* Professional Button Styling */
    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-modern:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .btn-modern:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-modern.btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-modern.btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .btn-modern.btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-modern.btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .btn-modern.btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-modern.btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }

    .btn-modern.btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
    }

    .btn-modern.btn-info:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
    }

    .btn-modern.btn-secondary {
        background: #64748b;
        color: white;
    }

    .btn-modern.btn-secondary:hover {
        background: #475569;
    }

    /* Details Display Section - Subtle Design */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.75rem 1.5rem;
        margin-bottom: 0;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
    }

    @media (max-width: 1400px) {
        .details-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 992px) {
        .details-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .details-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .details-grid {
            grid-template-columns: 1fr;
            padding: 0;
            gap: 0.5rem;
        }

        .detail-item {
            padding: 0.5rem 0;
        }
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0;
        background: transparent;
        border-radius: 0;
        border: none;
        border-bottom: 1px solid #f1f5f9;
        box-shadow: none;
        transition: none;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-item:hover {
        background: transparent;
        border-color: #f1f5f9;
        transform: none;
    }

    .detail-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-label i {
        font-size: 0.875rem;
        color: #94a3b8;
        width: 16px;
        text-align: center;
    }

    .detail-value {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.6;
        word-break: break-word;
    }

    .detail-value.empty {
        color: #94a3b8;
        font-style: italic;
        font-weight: 400;
    }

    .detail-value small {
        display: block;
        margin-top: 0.25rem;
        font-weight: 400;
    }

    .detail-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: var(--border-radius-sm);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detail-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-section-title i {
        color: var(--primary-color);
    }

    .detail-text-content p {
        margin-bottom: 0.75rem;
    }
    
    .detail-text-content p:last-child {
        margin-bottom: 0;
    }
    
    .detail-text-content ul,
    .detail-text-content ol {
        margin-bottom: 0.75rem;
        padding-left: 1.5rem;
    }
    
    .detail-text-content ul li,
    .detail-text-content ol li {
        margin-bottom: 0.5rem;
    }
    
    .detail-text-content ul li:last-child,
    .detail-text-content ol li:last-child {
        margin-bottom: 0;
    }
    
    .detail-text-content strong {
        font-weight: 600;
        color: #1e293b;
    }
    
    .detail-text-content a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .detail-text-content a:hover {
        text-decoration: underline;
    }
    
    .detail-text-content {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        color: #334155;
        line-height: 1.7;
        min-height: 60px;
    }

    .detail-text-content.empty {
        color: #94a3b8;
        font-style: italic;
    }

    /* Outline Icon Button Styles */
    .btn-outline-warning {
        border-radius: 8px;
        padding: 0.5rem;
        font-weight: 500;
        border: 1.5px solid #f59e0b;
        color: #1e293b;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }

    .btn-outline-warning:hover {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
    }

    .btn-outline-warning:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(245, 158, 11, 0.2);
    }

    .btn-outline-warning:disabled,
    .btn-outline-warning[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-outline-warning i {
        font-size: 1.125rem;
    }

    /* Badge Styling */
    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    /* Timeline Styling */
    .timeline-item {
        border-left: 3px solid var(--border-color);
        padding-left: 1.5rem;
        padding-bottom: 1.5rem;
        position: relative;
        margin-bottom: 0.5rem;
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0.25rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--danger-color);
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px var(--danger-color);
    }

    .timeline-item:last-child {
        border-left: none;
        padding-bottom: 0;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: var(--text-secondary);
        font-size: 1rem;
        margin: 0;
    }

    /* Section Headers */
    .section-header {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
    }

    .section-header i {
        font-size: 1.25rem;
    }

    /* Nav Tabs */
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        border-bottom-color: var(--danger-color);
        color: var(--danger-color);
        background: transparent;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: var(--danger-color);
        color: var(--danger-color);
        background: transparent;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .nc-header-card .card-body {
            padding: 1rem;
        }

        .details-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .detail-section {
            padding: 1rem;
        }

        .nav-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .btn-modern {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .btn-modern:last-child {
            margin-bottom: 0;
        }

        .table-modern {
            font-size: 0.875rem;
        }

        .table-modern thead th,
        .table-modern tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }

        .table-modern thead th:first-child,
        .table-modern tbody td:first-child {
            padding-left: 0.75rem;
        }

        .table-modern thead th:last-child,
        .table-modern tbody td:last-child {
            padding-right: 0.75rem;
        }

        .table-modern .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
        }
    }
</style>
@endsection

@section('content2')
<main>
    @php
        $items = [
            [
                'link' => route('audit.dashboard'),
                'name' => 'Audit & CAPA Dashboard',
                'icon' => null
            ],
            [
                'link' => route('audit.nc.index'),
                'name' => 'Non-Conformances',
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => $nc->nc_number,
                'icon' => null
            ]
        ];
        $currentStep = $nc->getCurrentWorkflowStep() ?? 3;
        $audit = $nc->audit;
        $auditCurrentStep = $audit ? $audit->getCurrentWorkflowStep() : null;
        $auditStatusName = $audit ? $audit->status_name : null;
        $isRootCauseAnalysisStep = ($auditStatusName === 'Root Cause Analysis');
        $isClosed = $nc->status_name === 'Closed';
        
        // Get next workflow status for audit (if audit exists)
        $nextWorkflowStatus = $audit ? $audit->getNextWorkflowStatus() : null;
        $isAuditClosed = $audit ? ($audit->status_name === 'Closed') : false;
        $canProceedToNext = $audit ? $audit->canProceedToNextStatus() : false;
        $findingsRequiringNC = $audit ? $audit->getFindingsRequiringNC() : null;
        
        // Add category to breadcrumbs if available
        $categoryName = $nc->auditFinding?->findingCategory?->name ?? null;
        if ($categoryName) {
            // Check if category is a nonconformity type - make it red
            $isNonconformity = stripos($categoryName, 'nonconformity') !== false || 
                              stripos($categoryName, 'non-conformity') !== false ||
                              stripos($categoryName, 'non conformity') !== false ||
                              stripos($categoryName, 'major') !== false ||
                              stripos($categoryName, 'minor') !== false;
            $badgeClass = $isNonconformity ? 'badge-danger' : 'badge-info';
            $items[count($items) - 1]['name'] = $nc->nc_number . ' <span class="badge badge-modern '.$badgeClass.' ml-2" style="font-size: 0.875rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center;">'.$categoryName.'</span>';
        }
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card nc-header-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-1" style="font-size: 1.35rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; margin: 0;">
                            <i class="mdi mdi-alert-octagon text-danger"></i> {{ $nc->nc_number }}
                        </h4>
                        @if(!$isClosed)
                        <a href="{{ route('audit.nc.edit', $nc->id) }}" class="btn btn-outline-warning" style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #f59e0b; color: #1e293b; background: transparent; transition: all 0.2s ease; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="Edit NC">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </a>
                        @else
                        <button class="btn btn-outline-secondary" disabled style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #94a3b8; color: #94a3b8; background: transparent; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="NC is closed and cannot be edited">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </button>
                        @endif
                    </div>
                    <p class="mb-0 text-muted mt-1">{{ $nc->title }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($audit && !$isAuditClosed && !$isClosed)
                    <button type="button" class="btn btn-modern btn-success" data-toggle="modal" data-target="#workflowActionModal">
                        <i class="mdi mdi-check-decagram"></i> Workflow Action
                    </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Comprehensive Details Grid -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account"></i>
                        Identified By
                    </div>
                    <div class="detail-value">
                        {{ $nc->identifiedByUser?->name ?? ($nc->identified_by ?? 'N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-clock"></i>
                        Identified Date
                    </div>
                    <div class="detail-value">
                        {{ $nc->date_identified?->format('M d, Y') ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-check"></i>
                        Target Closure
                    </div>
                    <div class="detail-value">
                        {{ $nc->target_closure_date?->format('M d, Y') ?? 'Not set' }}
                        @if($nc->target_closure_date && $nc->target_closure_date->isPast() && $nc->status_name !== 'Closed')
                        <br><span class="badge badge-modern badge-warning" style="font-size: 0.7rem; margin-top: 0.25rem;">OVERDUE</span>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-shield-alert"></i>
                        Risk Level
                    </div>
                    <div class="detail-value">
                        @if($nc->riskLevel)
                        <span class="badge badge-modern" style="background-color: {{ $nc->riskLevel->color ?? '#6c757d' }}; color: white;">
                            {{ $nc->riskLevel->name }}
                        </span>
                        @else
                        <span class="badge badge-modern badge-secondary">Not Assessed</span>
                        @endif
                    </div>
                </div>
                
                @if($nc->origin_name || $nc->origin)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-source-branch"></i>
                        Origin
                    </div>
                    <div class="detail-value">
                        {{ $nc->origin_name ?? ($nc->origin?->name ?? 'N/A') }}
                    </div>
                </div>
                @endif
                
                @if($nc->audit)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-link"></i>
                        Related Audit
                    </div>
                    <div class="detail-value">
                        <a href="{{ route('audit.audits.show', $nc->audit_id) }}" style="color: var(--primary-color); text-decoration: none;">
                            {{ $nc->audit->audit_number }}
                        </a>
                    </div>
                </div>
                @endif
                
                @if($nc->severity_score && $nc->likelihood_score)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-gauge"></i>
                        Risk Score
                    </div>
                    <div class="detail-value" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="background: #fee2e2; border-radius: 6px; padding: 0.25rem 0.5rem; color: #dc2626; font-weight: 700; font-size: 0.875rem;">{{ $nc->severity_score }}</span>
                        <span style="color: #64748b; font-weight: 300;">×</span>
                        <span style="background: #fef3c7; border-radius: 6px; padding: 0.25rem 0.5rem; color: #d97706; font-weight: 700; font-size: 0.875rem;">{{ $nc->likelihood_score }}</span>
                        <span style="color: #64748b; font-weight: 300;">=</span>
                        <span class="text-{{ $nc->risk_score > 15 ? 'danger' : ($nc->risk_score > 8 ? 'warning' : 'success') }}" style="font-weight: 700; font-size: 1.25rem;">
                            {{ $nc->risk_score }}
                        </span>
                        <span class="badge badge-modern" style="background-color: {{ $nc->risk_score > 15 ? '#dc2626' : ($nc->risk_score > 8 ? '#d97706' : '#059669') }}; color: white; font-size: 0.7rem; padding: 0.25rem 0.5rem; margin-left: 0.25rem;">
                            {{ $nc->risk_score > 15 ? 'HIGH' : ($nc->risk_score > 8 ? 'MEDIUM' : 'LOW') }}
                        </span>
                    </div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-check"></i>
                        Created On
                    </div>
                    <div class="detail-value">
                        {{ $nc->created_at?->format('M d, Y H:i') ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Card -->
    <div class="card nc-show-card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#details">
                        <i class="mdi mdi-information-outline"></i> Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#rca">
                        <i class="mdi mdi-magnify"></i> Root Cause Analysis
                        @if($nc->rootCauseAnalyses->count() > 0)
                        <span class="badge badge-modern badge-success ml-1">{{ $nc->rootCauseAnalyses->count() }}</span>
                        @else
                        <span class="badge badge-modern badge-warning ml-1">Pending</span>
                        @endif
                    </a>
                </li>
                @php
                    // Show CAPA tab if: has CAPAs, or status allows CAPA assignment, or has RCA and audit is in CAPA/Implement/Verify/Close status
                    $showCapaTab = $nc->correctiveActions->count() > 0 || 
                                   $nc->status_name === 'CAPA Assigned' || 
                                   ($audit && $audit->status_name === 'Assign CAPA') ||
                                   ($nc->hasRca() && $audit && in_array($audit->status_name, ['Assign CAPA', 'Implement', 'Verify', 'Close']));
                @endphp
                @if($showCapaTab)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#capas">
                        <i class="mdi mdi-checkbox-marked-circle"></i> Corrective Actions 
                        <span class="badge badge-modern badge-success ml-1">{{ $nc->correctiveActions->count() }}</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#attachments">
                        <i class="mdi mdi-paperclip"></i> Attachments 
                        <span class="badge badge-modern badge-info ml-1">{{ $nc->attachments->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#activity">
                        <i class="mdi mdi-history"></i> Activity Log
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approval-history">
                        <i class="mdi mdi-account-check"></i> Approval History
                        @if($nc->workflowApprovals->count() > 0)
                        <span class="badge badge-modern badge-success ml-1">{{ $nc->workflowApprovals->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            @if($audit && !$isAuditClosed)
            @php
                $auditCurrentStep = $audit->getCurrentWorkflowStep() ?? 1;
                $requirements = [];
                
                // Check for findings requiring NCs first (priority check - blocks progression)
                if ($findingsRequiringNC && $findingsRequiringNC->count() > 0 && $auditCurrentStep >= 2) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'The following findings require non-conformances to be raised before proceeding:',
                        'items' => $findingsRequiringNC->map(function($f) use ($audit) {
                            return [
                                'number' => $f->finding_number,
                                'category' => $f->findingCategory?->name ?? 'N/A',
                                'url' => route('audit.nc.create', ['audit_id' => $audit->id, 'finding_id' => $f->id])
                            ];
                        })->toArray()
                    ];
                } elseif ($auditCurrentStep === 2 && $audit->findings()->count() === 0) {
                    $requirements[] = [
                        'type' => 'info',
                        'message' => 'Please record at least one finding before proceeding.',
                        'items' => []
                    ];
                } elseif ($auditCurrentStep === 5 || $auditStatusName === 'Assign CAPA') {
                    // Check if all NCs with RCA have at least one CAPA
                    $ncsWithoutCapa = $audit->nonConformances()
                        ->whereHas('rootCauseAnalyses')
                        ->whereDoesntHave('correctiveActions')
                        ->get();
                    
                    if ($ncsWithoutCapa->count() > 0) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'The following non-conformances require corrective actions (CAPA) to be assigned before proceeding:',
                            'items' => $ncsWithoutCapa->map(function($nc) {
                                return [
                                    'number' => $nc->nc_number,
                                    'url' => route('audit.nc.show', $nc->id),
                                    'button_class' => 'btn-primary',
                                    'button_text' => 'Add CAPA',
                                    'icon' => 'plus'
                                ];
                            })->toArray()
                        ];
                    } elseif ($canProceedToNext && $nextWorkflowStatus) {
                        // Double-check: ensure no findings requiring NCs before showing ready
                        if (!$findingsRequiringNC || $findingsRequiringNC->count() === 0) {
                            $requirements[] = [
                                'type' => 'success',
                                'message' => 'All requirements met. Ready to proceed to next step.',
                                'items' => []
                            ];
                        }
                    }
                } elseif ($auditCurrentStep === 6 || $auditStatusName === 'Implement') {
                    // Check if ALL CAPAs in the audit are implemented (matching Audit model validation)
                    $unimplementedCapas = $audit->nonConformances()
                        ->with('correctiveActions')
                        ->get()
                        ->flatMap(function($nc) {
                            return $nc->correctiveActions;
                        })
                        ->filter(function($capaItem) {
                            return !in_array($capaItem->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed']);
                        });
                    
                    if ($unimplementedCapas->count() > 0) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'The following corrective actions must be implemented before the audit can proceed to verification:',
                            'items' => $unimplementedCapas->map(function($capaItem) {
                                return [
                                    'number' => $capaItem->capa_number,
                                    'url' => route('audit.capa.show', $capaItem->id),
                                    'button_class' => 'btn-success',
                                    'button_text' => 'Implement',
                                    'icon' => 'wrench'
                                ];
                            })->toArray()
                        ];
                    } elseif ($canProceedToNext && $nextWorkflowStatus) {
                        $requirements[] = [
                            'type' => 'success',
                            'message' => 'All corrective actions have been implemented. The audit is ready to proceed to verification.',
                            'items' => []
                        ];
                    }
                } elseif ($auditCurrentStep === 7 || $auditStatusName === 'Verify') {
                    // Check if ALL implemented CAPAs in the audit are verified
                    $unverifiedCapas = $audit->nonConformances()
                        ->with('correctiveActions.latestVerification')
                        ->get()
                        ->flatMap(function($nc) {
                            return $nc->correctiveActions;
                        })
                        ->filter(function($capaItem) {
                            // Only check CAPAs that are implemented but not verified
                            $isItemImplemented = in_array($capaItem->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed']);
                            return $isItemImplemented && !$capaItem->latestVerification;
                        });
                    
                    if ($unverifiedCapas->count() > 0) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'The following implemented corrective actions must be verified for effectiveness before the audit can proceed:',
                            'items' => $unverifiedCapas->map(function($capaItem) {
                                return [
                                    'number' => $capaItem->capa_number,
                                    'url' => route('audit.capa.show', $capaItem->id),
                                    'button_class' => 'btn-primary',
                                    'button_text' => 'Verify',
                                    'icon' => 'check-all'
                                ];
                            })->toArray()
                        ];
                    } elseif ($unverifiedCapas->count() === 0 && $canProceedToNext && $nextWorkflowStatus) {
                        // Only show success if there are no unverified CAPAs
                        $requirements[] = [
                            'type' => 'success',
                            'message' => 'All corrective actions have been verified. The audit is ready to proceed to the next step.',
                            'items' => []
                        ];
                    }
                } elseif ($canProceedToNext && $nextWorkflowStatus) {
                    // Double-check: ensure no findings requiring NCs before showing ready
                    if (!$findingsRequiringNC || $findingsRequiringNC->count() === 0) {
                        $requirements[] = [
                            'type' => 'success',
                            'message' => 'All requirements met. Ready to proceed to next step.',
                            'items' => []
                        ];
                    }
                } elseif (!$canProceedToNext) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'Please complete all required actions for the current workflow step before proceeding.',
                        'items' => []
                    ];
                }
            @endphp
            
            @if(!empty($requirements))
            <div class="requirements-indicator mb-3">
                @foreach($requirements as $req)
                @if($req['type'] === 'warning' && !empty($req['items']))
                <div class="alert alert-warning mb-2 py-2 px-3 d-flex align-items-center flex-wrap" role="alert" style="border-left: 4px solid #ffc107; border-radius: 4px; font-size: 0.875rem;">
                    <i class="mdi mdi-alert-circle-outline mr-2" style="font-size: 1rem;"></i>
                    <strong class="mr-2">Action Required:</strong>
                    <span class="mr-2">{{ $req['message'] }}</span>
                    @foreach($req['items'] as $index => $item)
                    <span class="badge badge-light mr-1" style="font-size: 0.75rem;">{{ $item['number'] }}</span>
                    <a href="{{ $item['url'] }}" class="btn btn-sm {{ isset($item['button_class']) ? $item['button_class'] : 'btn-danger' }} mr-2" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; line-height: 1.3;">
                        <i class="mdi mdi-{{ isset($item['icon']) ? $item['icon'] : 'alert-plus' }}" style="font-size: 0.875rem;"></i> {{ isset($item['button_text']) ? $item['button_text'] : 'Raise NC' }}
                    </a>
                    @if($index < count($req['items']) - 1)
                    <span class="text-muted mr-1">•</span>
                    @endif
                    @endforeach
                </div>
                @elseif($req['type'] === 'success')
                <div class="alert alert-success mb-2 py-2 px-3" role="alert" style="border-left: 4px solid #28a745; border-radius: 4px;">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle-outline mr-2" style="font-size: 1.1rem;"></i>
                        <span style="font-size: 0.875rem;"><strong>Ready to Proceed:</strong> {{ $req['message'] }}</span>
                    </div>
                </div>
                @else
                <div class="alert alert-{{ $req['type'] === 'warning' ? 'warning' : 'info' }} mb-2 py-2 px-3" role="alert" style="border-left: 4px solid {{ $req['type'] === 'warning' ? '#ffc107' : '#17a2b8' }}; border-radius: 4px;">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-{{ $req['type'] === 'warning' ? 'alert-circle' : 'information' }}-outline mr-2" style="font-size: 1.1rem;"></i>
                        <span style="font-size: 0.875rem;"><strong>{{ $req['type'] === 'warning' ? 'Action Required:' : 'Information:' }}</strong> {{ $req['message'] }}</span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
            @endif
            <div class="tab-content">
                <!-- Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-text-box-outline"></i>
                                    Description
                                </div>
                                <div class="detail-text-content {{ empty($nc->description) ? 'empty' : '' }}">
                                    @if(!empty($nc->description))
                                        {!! $nc->description !!}
                                    @else
                                        {{ 'No description provided' }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-shield-check"></i>
                                    Immediate Correction
                                </div>
                                <div class="detail-text-content {{ empty($nc->immediate_correction) ? 'empty' : '' }}">
                                    @if(!empty($nc->immediate_correction))
                                        {!! $nc->immediate_correction !!}
                                    @else
                                        {{ 'No immediate correction recorded' }}
                                    @endif
                                </div>
                                @if($nc->immediate_correction_date || $nc->immediate_correction_by)
                                <div class="mt-2" style="font-size: 0.8125rem; color: #64748b;">
                                    @if($nc->immediate_correction_date)
                                    <i class="mdi mdi-calendar"></i> {{ $nc->immediate_correction_date->format('M d, Y') }}
                                    @endif
                                    @if($nc->immediate_correction_by)
                                    <span class="ml-2"><i class="mdi mdi-account"></i> {{ $nc->immediate_correction_by }}</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($nc->risk_assessment_notes)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-clipboard-text-outline"></i>
                                    Risk Assessment Notes
                                </div>
                                <div class="detail-text-content">
                                    {!! $nc->risk_assessment_notes !!}
                                </div>
                            </div>
                            @endif
                            @if($nc->iso_clause_violated || $nc->sop_reference || $nc->sample_reference || $nc->equipment_reference || $nc->method_reference || $nc->personnel_reference)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-file-document-outline"></i>
                                    References
                                </div>
                                <div class="details-grid" style="grid-template-columns: 1fr;">
                                    @if($nc->iso_clause_violated)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-file-document"></i>
                                            ISO Clause
                                        </div>
                                        <div class="detail-value">{{ $nc->iso_clause_violated }}</div>
                                    </div>
                                    @endif
                                    @if($nc->sop_reference)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-file-document-multiple"></i>
                                            SOP Reference
                                        </div>
                                        <div class="detail-value">{{ $nc->sop_reference }}</div>
                                    </div>
                                    @endif
                                    @if($nc->sample_reference)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-flask-outline"></i>
                                            Sample Reference
                                        </div>
                                        <div class="detail-value">{{ $nc->sample_reference }}</div>
                                    </div>
                                    @endif
                                    @if($nc->equipment_reference)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-cog"></i>
                                            Equipment Reference
                                        </div>
                                        <div class="detail-value">{{ $nc->equipment_reference }}</div>
                                    </div>
                                    @endif
                                    @if($nc->method_reference)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-book-open-variant"></i>
                                            Method Reference
                                        </div>
                                        <div class="detail-value">{{ $nc->method_reference }}</div>
                                    </div>
                                    @endif
                                    @if($nc->personnel_reference)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-account"></i>
                                            Personnel Reference
                                        </div>
                                        <div class="detail-value">{{ $nc->personnel_reference }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($nc->department)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-office-building"></i>
                                    Department
                                </div>
                                <div class="detail-text-content">
                                    {{ $nc->department }}
                                </div>
                            </div>
                            @endif

                            @if($nc->actual_closure_date || $nc->closure_notes || $nc->closedByUser)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-check-circle"></i>
                                    Closure Information
                                </div>
                                <div class="details-grid" style="grid-template-columns: 1fr;">
                                    @if($nc->actual_closure_date)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-calendar-check"></i>
                                            Actual Closure Date
                                        </div>
                                        <div class="detail-value">{{ $nc->actual_closure_date->format('M d, Y') }}</div>
                                    </div>
                                    @endif
                                    @if($nc->closedByUser)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-account-check"></i>
                                            Closed By
                                        </div>
                                        <div class="detail-value">{{ $nc->closedByUser->name }}</div>
                                    </div>
                                    @endif
                                    @if($nc->closure_notes)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-note-text"></i>
                                            Closure Notes
                                        </div>
                                        <div class="detail-value" style="white-space: pre-wrap;">{{ $nc->closure_notes }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($nc->createdBy || $nc->updatedBy)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-information"></i>
                                    Audit Trail
                                </div>
                                <div class="details-grid" style="grid-template-columns: 1fr;">
                                    @if($nc->createdBy)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-account-plus"></i>
                                            Created By
                                        </div>
                                        <div class="detail-value">
                                            {{ $nc->createdBy->name }}
                                            <br><small style="color: #64748b;">{{ $nc->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                    </div>
                                    @endif
                                    @if($nc->updatedBy && $nc->updated_at != $nc->created_at)
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="mdi mdi-account-edit"></i>
                                            Last Updated By
                                        </div>
                                        <div class="detail-value">
                                            {{ $nc->updatedBy->name }}
                                            <br><small style="color: #64748b;">{{ $nc->updated_at->format('M d, Y H:i') }}</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Root Cause Analysis Tab -->
                <div class="tab-pane fade" id="rca" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="section-header mb-2">
                                <i class="mdi mdi-magnify text-danger"></i> Root Cause Analysis
                            </h6>
                            <small class="text-muted" style="display: block; margin-top: -0.5rem;">
                                <i class="mdi mdi-information-outline"></i> Perform root cause analysis to identify the underlying cause of the non-conformance.
                            </small>
                        </div>
                        @if($isRootCauseAnalysisStep && !$isClosed && !$isAuditClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addRcaModal">
                            <i class="mdi mdi-magnify"></i> Add Root Cause Analysis
                        </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table-modern" id="rcaTable" style="table-layout: auto; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 12%; min-width: 150px;">Method</th>
                                    <th style="width: 28%; min-width: 350px;">Root Cause Description</th>
                                    <th style="width: 25%; min-width: 300px;">Contributing Factors</th>
                                    <th style="width: 20%; min-width: 280px;">Evidence</th>
                                    <th style="width: 10%; min-width: 140px;">Created By</th>
                                    @if($nc->rootCauseAnalyses->count() > 0)
                                    <th style="width: 5%; min-width: 100px;">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($nc->rootCauseAnalyses->count() > 0)
                                    @foreach($nc->rootCauseAnalyses as $rca)
                                    <tr>
                                        <td>
                                            <strong>{{ $rca->rootCauseMethod?->name ?? ($rca->method_name ?? 'N/A') }}</strong>
                                            @if($rca->rootCauseMethod?->description)
                                            <br><small class="text-muted" style="margin-top: 0.25rem; display: block;">{{ Str::limit($rca->rootCauseMethod->description, 100) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="rca-table-content">
                                                {!! $rca->root_cause_description ?? 'N/A' !!}
                                            </div>
                                        </td>
                                        <td>
                                            @if($rca->contributing_factors)
                                            <div class="rca-table-content">
                                                {!! $rca->contributing_factors !!}
                                            </div>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rca->evidence_supporting_rca)
                                            <div class="rca-table-content">
                                                {!! $rca->evidence_supporting_rca !!}
                                            </div>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $rca->createdBy->name ?? 'N/A' }}
                                            <br><small class="text-muted" style="margin-top: 0.25rem; display: block; font-size: 0.75rem;">{{ $rca->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            @if(!$isClosed && !$isAuditClosed)
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-info edit-rca-btn" 
                                                        data-rca-id="{{ $rca->id }}"
                                                        data-method-id="{{ $rca->root_cause_method_id }}"
                                                        data-description="{{ htmlspecialchars($rca->root_cause_description ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                        data-factors="{{ htmlspecialchars($rca->contributing_factors ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                        data-evidence="{{ htmlspecialchars($rca->evidence_supporting_rca ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                        data-status-id="{{ $rca->status_id }}"
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <form action="{{ route('audit.nc.rca.delete', $rca->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this root cause analysis?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr class="empty-state-row">
                                    <td colspan="6">
                                        <i class="mdi mdi-magnify" style="font-size: 2rem; color: #dee2e6; display: block; margin-bottom: 0.5rem;"></i>
                                        No root cause analysis performed yet.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Corrective Actions Tab -->
                @php
                    // Show CAPA tab content if: has CAPAs, or status allows CAPA assignment, or has RCA and audit is in CAPA/Implement/Verify/Close status
                    $showCapaTabContent = $nc->correctiveActions->count() > 0 || 
                                   $nc->status_name === 'CAPA Assigned' || 
                                         ($audit && $audit->status_name === 'Assign CAPA') ||
                                         ($nc->hasRca() && $audit && in_array($audit->status_name, ['Assign CAPA', 'Implement', 'Verify', 'Close']));
                    $canAddCapaInTab = $showCapaTabContent && $nc->hasRca() && !$isClosed && !$isAuditClosed;
                @endphp
                @if($showCapaTabContent)
                <div class="tab-pane fade" id="capas" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="section-header mb-2">
                                <i class="mdi mdi-checkbox-marked-circle text-danger"></i> Corrective Actions (CAPA)
                            </h6>
                            <small class="text-muted" style="display: block; margin-top: -0.5rem;">
                                <i class="mdi mdi-information-outline"></i> Assign corrective actions to address the root cause of the non-conformance.
                            </small>
                        </div>
                        @if($canAddCapaInTab)
                        <button type="button" class="btn btn-modern btn-success" data-toggle="modal" data-target="#addCapaModal">
                            <i class="mdi mdi-plus"></i> Add Corrective Action
                        </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>CAPA #</th>
                                    <th>Title</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                    @if($nc->correctiveActions->count() > 0)
                                    <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($nc->correctiveActions->count() > 0)
                                    @foreach($nc->correctiveActions as $capa)
                                    <tr>
                                        <td><strong>{{ $capa->capa_number }}</strong></td>
                                        <td>
                                            <strong>{{ $capa->title ?? 'N/A' }}</strong>
                                            @if($capa->description)
                                            <br><div class="capa-table-content" style="margin-top: 0.5rem;">{!! $capa->description !!}</div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $capa->actionOwnerUser?->name ?? ($capa->action_owner_name ?? $capa->action_owner ?? 'N/A') }}
                                        </td>
                                        <td>
                                            {{ $capa->due_date?->format('M d, Y') ?? 'Not set' }}
                                            @if($capa->isOverdue())
                                            <br><span class="badge badge-modern badge-danger">Overdue</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($capa->priority)
                                            <span class="badge badge-modern badge-{{ $capa->priority->code === 'CRITICAL' ? 'danger' : ($capa->priority->code === 'HIGH' ? 'warning' : 'info') }}">
                                                {{ $capa->priority_name ?? $capa->priority->name ?? 'N/A' }}
                                            </span>
                                            @else
                                            <span class="badge badge-modern badge-secondary">Not Set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('audit.capa.show', $capa->id) }}" class="btn btn-sm btn-outline-info">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr class="empty-state-row">
                                        <td colspan="5">
                                            <i class="mdi mdi-checkbox-marked-circle-outline" style="font-size: 2rem; color: #dee2e6; display: block; margin-bottom: 0.5rem;"></i>
                                            No corrective actions assigned yet.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Attachments Tab -->
                <div class="tab-pane fade" id="attachments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="section-header mb-2">
                                <i class="mdi mdi-paperclip text-danger"></i> Attachments
                            </h6>
                            <small class="text-muted" style="display: block; margin-top: -0.5rem;">
                                <i class="mdi mdi-information-outline"></i> Upload supporting documents, evidence, or related files. Maximum file size: 10MB. Supported formats: PDF, Images, Documents.
                            </small>
                        </div>
                        @if(!$isClosed && !$isAuditClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#uploadAttachmentModal">
                            <i class="mdi mdi-upload"></i> Add Attachment
                        </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Description</th>
                                    <th>Uploaded On</th>
                                    @if($nc->attachments->count() > 0)
                                    <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($nc->attachments->count() > 0)
                                    @foreach($nc->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <i class="mdi mdi-{{ $attachment->isPdf() ? 'file-pdf' : ($attachment->isImage() ? 'file-image' : 'file') }} text-primary"></i>
                                            <strong>{{ $attachment->title ?? $attachment->original_name ?? $attachment->file_name }}</strong>
                                        </td>
                                        <td><span class="badge badge-modern badge-secondary">{{ $attachment->file_type ?? 'N/A' }}</span></td>
                                        <td>{{ $attachment->file_size_formatted ?? 'N/A' }}</td>
                                        <td>{{ $attachment->description ?? '-' }}</td>
                                        <td>{{ $attachment->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                                   target="_blank"
                                                   class="btn btn-outline-info" title="View File">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                                @if($attachment->isImage())
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-toggle="modal" 
                                                        data-target="#viewImageModal"
                                                        data-image-url="{{ asset('storage/' . $attachment->file_path) }}"
                                                        data-image-name="{{ $attachment->title ?? $attachment->original_name ?? $attachment->file_name }}"
                                                        title="Preview">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                @endif
                                                @if(!$isClosed)
                                                <form action="{{ route('audit.nc.attachments.delete', $attachment->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr class="empty-state-row">
                                        <td colspan="5">
                                            <i class="mdi mdi-file-outline" style="font-size: 2rem; color: #dee2e6; display: block; margin-bottom: 0.5rem;"></i>
                                            No attachments uploaded yet.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Activity Log Tab - Chain of Custody -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    @php
                        $chainOfCustodyService = new \App\Services\AuditModule\AuditChainOfCustodyService();
                        $chainOfCustody = $chainOfCustodyService->getChainOfCustody($nc);
                    @endphp
                    
                    @if($chainOfCustody['timeline'] || $nc->activityLogs->count() > 0)
                    <!-- Chain of Custody Summary -->
                    @if(count($chainOfCustody['timeline']) > 0)
                    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #17a2b8;">
                            <h5 class="mb-0">
                                <i class="mdi mdi-timeline-clock-outline text-info"></i> Chain of Custody Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3" style="background: #f8f9fa; border-radius: 8px;">
                                        <h6 class="text-muted mb-1">Total Duration</h6>
                                        <h4 class="mb-0 text-primary">{{ $chainOfCustody['total_duration_formatted'] }}</h4>
                                    </div>
                                </div>
                                @if($chainOfCustody['longest_step'])
                                <div class="col-md-3">
                                    <div class="text-center p-3" style="background: #fff3cd; border-radius: 8px;">
                                        <h6 class="text-muted mb-1">Longest Step</h6>
                                        <h5 class="mb-0 text-warning">{{ $chainOfCustody['longest_step']['step_name'] }}</h5>
                                        <small class="text-muted">{{ $chainOfCustody['longest_step']['average_formatted'] }}</small>
                                    </div>
                                </div>
                                @endif
                                @if($chainOfCustody['fastest_step'])
                                <div class="col-md-3">
                                    <div class="text-center p-3" style="background: #d1ecf1; border-radius: 8px;">
                                        <h6 class="text-muted mb-1">Fastest Step</h6>
                                        <h5 class="mb-0 text-info">{{ $chainOfCustody['fastest_step']['step_name'] }}</h5>
                                        <small class="text-muted">{{ $chainOfCustody['fastest_step']['average_formatted'] }}</small>
                                    </div>
                                </div>
                                @endif
                                @if(count($chainOfCustody['bottlenecks']) > 0)
                                <div class="col-md-3">
                                    <div class="text-center p-3" style="background: #f8d7da; border-radius: 8px;">
                                        <h6 class="text-muted mb-1">Bottlenecks</h6>
                                        <h4 class="mb-0 text-danger">{{ count($chainOfCustody['bottlenecks']) }}</h4>
                                        <small class="text-muted">Identified</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            @if(count($chainOfCustody['bottlenecks']) > 0)
                            <div class="mt-3">
                                <h6 class="text-danger mb-2"><i class="mdi mdi-alert-circle"></i> Bottlenecks Identified:</h6>
                                <div class="row">
                                    @foreach($chainOfCustody['bottlenecks'] as $bottleneck)
                                    <div class="col-md-6 mb-2">
                                        <div class="alert alert-warning mb-0 py-2" style="border-left: 4px solid #ffc107;">
                                            <strong>{{ $bottleneck['step_name'] }}</strong> took {{ $bottleneck['duration'] }} 
                                                ({{ $bottleneck['percentage'] }}% longer than average)
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Workflow Timeline -->
                    @if(count($chainOfCustody['timeline']) > 0)
                    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #28a745;">
                            <h5 class="mb-0">
                                <i class="mdi mdi-source-branch text-success"></i> Workflow Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline" style="position: relative; padding-left: 30px;">
                                @foreach($chainOfCustody['timeline'] as $index => $entry)
                                <div class="timeline-item mb-4" style="position: relative; padding-left: 20px; border-left: 3px solid #dee2e6;">
                                    <div class="d-flex align-items-start">
                                        <div class="mr-3" style="margin-left: -23px;">
                                            <div class="badge badge-modern badge-{{ $entry['workflow_step'] == 1 ? 'success' : ($entry['workflow_step'] == 7 ? 'danger' : 'info') }}" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem;">
                                                {{ $entry['workflow_step'] }}
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <h6 class="mb-0" style="font-weight: 600; color: var(--text-primary);">
                                                        {{ $entry['workflow_step_name'] }}
                                                    </h6>
                                                    @if($entry['previous_status'] !== $entry['current_status'])
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-arrow-right"></i> {{ $entry['previous_status'] }} → {{ $entry['current_status'] }}
                                                    </small>
                                                    @endif
                                                </div>
                                                @if($entry['duration_formatted'] !== 'N/A')
                                                <span class="badge badge-modern badge-secondary">
                                                    <i class="mdi mdi-clock-outline"></i> {{ $entry['duration_formatted'] }}
                                                </span>
                                                @endif
                                            </div>
                                            @if($entry['remarks'])
                                            <p class="mb-1 text-muted" style="font-size: 0.875rem;">{{ $entry['remarks'] }}</p>
                                            @endif
                                            <small class="text-muted">
                                                <i class="mdi mdi-account"></i> {{ $entry['performed_by'] }} 
                                                <i class="mdi mdi-clock-outline ml-2"></i> {{ $entry['performed_at']->format('M d, Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- All Activity Logs -->
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #6c757d;">
                            <h5 class="mb-0">
                                <i class="mdi mdi-history text-secondary"></i> All Activity Logs
                            </h5>
                        </div>
                        <div class="card-body">
                    <div class="timeline">
                        @foreach($nc->activityLogs->sortByDesc('created_at') as $log)
                                <div class="timeline-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="mr-3">
                                            @php
                                                $logClass = match($log->action) {
                                                    'Created' => 'success',
                                                    'Status Changed' => 'info',
                                                    'Workflow Transition' => 'primary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-modern badge-{{ $logClass }}">
                                                {{ $log->action }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                            <p class="mb-1" style="font-weight: 500; color: var(--text-primary);">{{ $log->description }}</p>
                                            @if($log->workflow_step)
                                            <small class="text-muted d-block mb-1">
                                                <i class="mdi mdi-source-branch"></i> Step {{ $log->workflow_step }}: {{ $log->workflow_step_name }}
                                                @if($log->duration_seconds)
                                                <span class="ml-2"><i class="mdi mdi-clock-outline"></i> {{ $log->getDurationFormatted() }}</span>
                                                @endif
                                            </small>
                                            @endif
                                    <small class="text-muted">
                                                <i class="mdi mdi-account"></i> {{ $log->performedBy?->name ?? __('System') }} 
                                        <i class="mdi mdi-clock-outline ml-2"></i> {{ $log->created_at->format('M d, Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="mdi mdi-history"></i>
                        <p>{{ __('No activity recorded yet.') }}</p>
                    </div>
                    @endif
                </div>

                <!-- Approval History Tab -->
                <div class="tab-pane fade" id="approval-history" role="tabpanel">
                    @php
                        $approvals = $nc->workflowApprovals()->with('approver')->ordered()->get();
                    @endphp
                    
                    @if($approvals->count() > 0)
                    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #28a745;">
                            <h5 class="mb-0">
                                <i class="mdi mdi-account-check text-success"></i> Workflow Approval History
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th>Workflow Step</th>
                                            <th>From Status</th>
                                            <th>To Status</th>
                                            <th>Approver</th>
                                            <th>Role Type</th>
                                            <th>ISO Role</th>
                                            <th>Remarks</th>
                                            <th>Approved At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approvals as $approval)
                                        <tr>
                                            <td>
                                                <strong>Step {{ $approval->workflow_step }}</strong><br>
                                                <small class="text-muted">{{ getWorkflowStepName($approval->workflow_step) ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $approval->from_status ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $approval->to_status ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $approval->approver_name }}</strong><br>
                                                <small class="text-muted">{{ $approval->approver->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if($approval->role_type === 'approver')
                                                    <span class="badge badge-primary">Approver</span>
                                                @else
                                                    <span class="badge badge-info">Verifier</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($approval->iso_role)
                                                    <span class="badge badge-success">{{ $approval->iso_role }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $approval->remarks ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $approval->approved_at->format('Y-m-d H:i') }}</small><br>
                                                <small class="text-muted">{{ $approval->approved_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="mdi mdi-account-check"></i>
                        <p>{{ __('No approval history recorded yet.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.nc.attachments.upload', $nc->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-upload"></i> Upload Attachment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label for="title" class="font-weight-600">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="255" style="border-radius: 8px;" placeholder="Enter a title for this file">
                        <small class="form-text text-muted">This title will be displayed instead of the file name</small>
                    </div>
                    <div class="form-group">
                        <label for="file" class="font-weight-600">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" required style="border-radius: 8px;">
                        <small class="form-text text-muted">Maximum file size: 10MB</small>
                    </div>
                    <div class="form-group">
                        <label for="description" class="font-weight-600">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px;">
                        <i class="mdi mdi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($audit)
<!-- Workflow Action Modal -->
<div class="modal fade" id="workflowActionModal" tabindex="-1" role="dialog" aria-labelledby="workflowActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('audit.audits.approve-next-step', $audit->id) }}" method="POST" id="workflowActionForm">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" id="workflowActionModalLabel">
                        <i class="mdi mdi-check-decagram"></i> Workflow Action
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="mdi mdi-information-outline" style="font-size: 24px; margin-right: 12px;"></i>
                        <div>
                            <strong>ISO Compliance Note:</strong> All workflow actions require documented remarks for audit trail purposes.
                            <br><small class="text-muted">Current Status: <strong>{{ $audit->status_name }}</strong></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="workflow_action" class="control-label font-weight-600">
                            Action <span class="text-danger">*</span>
                        </label>
                        <select name="action" id="workflow_action" class="form-control" required style="border-radius: var(--border-radius-sm);">
                            <option value="">Choose Action...</option>
                            <option value="approve">Approve & Move to Next Step</option>
                            <option value="reject">Reject & Return</option>
                            <option value="return">Return for Correction</option>
                            <option value="hold">Hold / Suspend</option>
                        </select>
                        <small class="form-text text-muted">Select the action you want to perform on this audit.</small>
                    </div>

                    <div class="form-group" id="target_status_display" style="display: none;">
                        <label class="control-label font-weight-600">
                            Target Status
                        </label>
                        <div class="alert alert-success d-flex align-items-center mb-0" id="target_status_alert" style="border-radius: 8px; border-left: 4px solid #28a745;">
                            <i class="mdi mdi-arrow-right" style="font-size: 24px; margin-right: 12px;"></i>
                            <div style="flex: 1;">
                                <strong>Audit will move to:</strong>
                                <div class="mt-1">
                                    <span class="badge badge-success" id="target_status_badge" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ $nextWorkflowStatus ? $nextWorkflowStatus->name : 'N/A' }}
                                    </span>
                                </div>
                                <small class="d-block mt-1 text-muted" id="target_status_description">
                                    <i class="mdi mdi-information-outline"></i> Moving to the next workflow step
                                </small>
                            </div>
                        </div>
                        <input type="hidden" name="target_status_id" id="target_status_id" value="{{ $nextWorkflowStatus ? $nextWorkflowStatus->id : '' }}">
                    </div>

                    <div class="form-group" id="remarks_field">
                        <label for="remarks" class="control-label font-weight-600">
                            Remarks / Notes <span class="text-danger">*</span>
                        </label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="6" 
                                  placeholder="Enter detailed remarks for this action. This is required for ISO compliance and audit trail purposes..." 
                                  required style="border-radius: var(--border-radius-sm);"></textarea>
                        <small class="form-text text-muted">
                            <i class="mdi mdi-alert-circle-outline"></i> 
                            Minimum 10 characters required. Document the reason for this action.
                        </small>
                    </div>

                    <div class="alert alert-warning mt-3" id="action_warning" style="display: none;">
                        <i class="mdi mdi-alert"></i>
                        <span id="warning_message"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-check-circle"></i> Submit Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- View Image Modal -->
<div class="modal fade" id="viewImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="imageModalTitle">
                    <i class="mdi mdi-image"></i> Image Preview
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="padding: 1.5rem;">
                <img id="modalImage" src="" alt="Preview" class="img-fluid" style="max-height: 70vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('script2')
<script>
    // Image preview modal
    $('#viewImageModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const imageUrl = button.data('image-url');
        const imageName = button.data('image-name');
        const modal = $(this);
        modal.find('#modalImage').attr('src', imageUrl);
        modal.find('#imageModalTitle').text(imageName);
    });

    @if($audit)
    // Workflow Action Modal
    $('#workflowActionModal').on('show.bs.modal', function() {
        // Reset form
        $('#workflowActionForm')[0].reset();
        $('#action_warning').hide();
        
        // Set default action to approve if next status is available and trigger change
        @if($nextWorkflowStatus)
        $('#workflow_action').val('approve').trigger('change');
        @endif
    });

    // Handle action change
    $('#workflow_action').on('change', function() {
        const action = $(this).val();
        const warningDiv = $('#action_warning');
        const warningMessage = $('#warning_message');
        const targetStatusDisplay = $('#target_status_display');
        const targetStatusBadge = $('#target_status_badge');
        const targetStatusDescription = $('#target_status_description');
        const targetStatusIdInput = $('#target_status_id');
        const targetStatusAlert = $('#target_status_alert');
        
        warningDiv.hide();
        targetStatusDisplay.hide();
        
        if (action === 'approve') {
            // Show target status for approve action
            @if($nextWorkflowStatus)
            targetStatusDisplay.show();
            targetStatusBadge.text('{{ $nextWorkflowStatus->name }}');
            targetStatusIdInput.val('{{ $nextWorkflowStatus->id }}');
            targetStatusAlert.removeClass('alert-warning alert-danger alert-info').addClass('alert-success');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Moving to the next workflow step');
            @endif
        } else if (action === 'reject' || action === 'return') {
            // Show previous status for reject/return
            @php
                $currentStep = $audit->getCurrentWorkflowStep();
                $previousStep = $currentStep && $currentStep > 1 ? $currentStep - 1 : null;
                $previousStatus = null;
                if ($previousStep) {
                    $previousStatus = \App\Models\AuditModule\AuditStatus::active()
                        ->forCompany()
                        ->where('workflow_step', $previousStep)
                        ->ordered()
                        ->first();
                }
            @endphp
            @if($previousStatus)
            targetStatusDisplay.show();
            targetStatusBadge.text('{{ $previousStatus->name }}');
            targetStatusIdInput.val('{{ $previousStatus->id }}');
            targetStatusAlert.removeClass('alert-success alert-danger alert-info').addClass('alert-warning');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Returning to the previous workflow step');
            @endif
            if (action === 'reject') {
                warningMessage.html('<strong>Rejection Note:</strong> Rejecting will return the audit to a previous status. Ensure all rejection reasons are documented.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
            } else {
                warningMessage.html('<strong>Return Note:</strong> Returning the audit requires correction. Document what needs to be corrected.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
            }
        } else if (action === 'hold') {
            // Show current status for hold
            targetStatusDisplay.show();
            targetStatusBadge.text('{{ $audit->status_name }}');
            targetStatusIdInput.val('{{ $audit->status_id }}');
            targetStatusAlert.removeClass('alert-success alert-warning alert-danger').addClass('alert-info');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Status will remain unchanged');
            warningMessage.html('<strong>Hold Note:</strong> Holding suspends the audit workflow. Document the reason for suspension.');
            warningDiv.removeClass('alert-info').addClass('alert-warning').show();
        }
    });

    // Form validation
    $('#workflowActionForm').on('submit', function(e) {
        const action = $('#workflow_action').val();
        const targetStatus = $('#target_status_id').val();
        const remarks = $('#remarks').val().trim();
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        // Target status is now automatically determined, no need to validate user selection
        
        if (!remarks || remarks.length < 10) {
            e.preventDefault();
            alert('Remarks are required and must be at least 10 characters for ISO compliance.');
            $('#remarks').focus();
            return false;
        }
        
        return true;
    });
    @endif

    // Store RCA data for later use when TinyMCE initializes
    var editRcaData = {};

    // Edit RCA Modal - Handle button click
    $(document).on('click', '.edit-rca-btn', function(e) {
        e.preventDefault();
        const rcaId = $(this).data('rca-id');
        const editUrl = '{{ url("/audit/non-conformances/rca") }}/' + rcaId + '/edit';
        const updateUrl = '{{ url("/audit/non-conformances/rca") }}/' + rcaId;
        
        // Show modal with loading overlay
        const modal = $('#editRcaModal');
        modal.modal('show');
        
        // Add loading overlay
        if (!modal.find('.loading-overlay').length) {
            modal.find('.modal-content').append('<div class="loading-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 9999; border-radius: 12px;"><div class="text-center"><i class="mdi mdi-loading mdi-spin mdi-48px text-primary"></i><p class="mt-3">Loading...</p></div></div>');
        }
        
        // Fetch RCA data via AJAX
        $.ajax({
            url: editUrl,
            method: 'GET',
            success: function(response) {
                // Remove loading overlay
                modal.find('.loading-overlay').remove();
                
                // Populate root cause method dropdown
                modal.find('#edit_root_cause_method_id').val(response.rca.root_cause_method_id || '');
                
                // Populate status dropdown (it's already populated from blade, just select the current one)
                modal.find('#edit_status_id').val(response.rca.status_id || '');
                
                // Store data for TinyMCE initialization
                editRcaData = {
                    description: response.rca.root_cause_description || '',
                    factors: response.rca.contributing_factors || '',
                    evidence: response.rca.evidence_supporting_rca || ''
                };
                
                // Set form action
                modal.find('#editRcaForm').attr('action', updateUrl);
                
                // Set textarea values (for non-TinyMCE fallback)
                modal.find('#edit_root_cause_description').val(editRcaData.description);
                modal.find('#edit_contributing_factors').val(editRcaData.factors);
                modal.find('#edit_evidence_supporting_rca').val(editRcaData.evidence);
                
                // Initialize TinyMCE for the editors
                setTimeout(function() {
                    initEditRcaTinyMCE();
                }, 300);
            },
            error: function(xhr) {
                // Remove loading overlay
                modal.find('.loading-overlay').remove();
                
                // Show error message
                modal.find('.modal-body').prepend(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="mdi mdi-alert"></i> Error loading root cause analysis data. Please try again.
                    </div>
                `);
                console.error('Error loading RCA data:', xhr);
            }
        });
    // Edit RCA Modal - Store data when modal opens
    $('#editRcaModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const rcaId = button.data('rca-id');
        const methodId = button.data('method-id');
        const description = button.data('description') || '';
        const factors = button.data('factors') || '';
        const evidence = button.data('evidence') || '';
        const statusId = button.data('status-id');
        
        const modal = $(this);
        const baseUrl = '{{ url("/audit/non-conformances/rca") }}';
        modal.find('#editRcaForm').attr('action', baseUrl + '/' + rcaId);
        
        // Set non-TinyMCE fields immediately
        modal.find('#edit_root_cause_method_id').val(methodId);
        if (statusId) {
            modal.find('#edit_status_id').val(statusId);
        }
        
        // Store TinyMCE content to be set after initialization
        modal.data('rca-description', description);
        modal.data('rca-factors', factors);
        modal.data('rca-evidence', evidence);
    });

    // Reset edit modal on close
    $('#editRcaModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        // Remove TinyMCE editors completely
        var modal = $(this);
        modal.find('form')[0].reset();
        
        // Clear stored data
        modal.removeData('rca-description');
        modal.removeData('rca-factors');
        modal.removeData('rca-evidence');
        
        // Clear TinyMCE editors
        if (typeof tinymce !== 'undefined') {
            ['edit_root_cause_description', 'edit_contributing_factors', 'edit_evidence_supporting_rca'].forEach(function(id) {
                if (tinymce.get(id)) {
                    tinymce.get(id).remove();
                }
            });
        }
        // Clear stored data
        editRcaData = {};
    });
</script>

<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    function initTinyMCE() {
        if (typeof tinymce === 'undefined') return;
        
        // Find all textareas with editor class that don't have TinyMCE yet
        $('textarea.editor').each(function() {
            var $textarea = $(this);
            var textareaId = $textarea.attr('id') || 'editor-' + Math.random().toString(36).substr(2, 9);
            if (!$textarea.attr('id')) {
                $textarea.attr('id', textareaId);
            }
            
            // Check if TinyMCE is already initialized for this textarea
            if (tinymce.get(textareaId)) {
                return; // Skip if already initialized
            }
            
            // Initialize TinyMCE for this textarea
            tinymce.init({
                selector: '#' + textareaId,
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    // Sync with form on change
                    editor.on('change keyup', function() {
                        editor.save();
                    });
                }
            });
        });
    }

    $(document).ready(function() {
        // Initial load
        setTimeout(function() {
            initTinyMCE();
        }, 200);
    });

    // Initialize TinyMCE for modals when shown
    $('#addRcaModal').on('shown.bs.modal', function() {
        var modal = $(this);
        setTimeout(function() {
            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE not loaded');
                return;
            }
            
            // Initialize each editor in this modal
            modal.find('textarea.editor').each(function() {
                var $textarea = $(this);
                var textareaId = $textarea.attr('id');
                
                if (!textareaId) {
                    textareaId = 'editor-' + Math.random().toString(36).substr(2, 9);
                    $textarea.attr('id', textareaId);
                }
                
                // Skip if already initialized
                if (tinymce.get(textareaId)) {
                    return;
                }
                
                var currentValue = $textarea.val() || '';
                
                tinymce.init({
                    selector: '#' + textareaId,
                    menubar: false,
                    height: 300,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                    branding: false,
                    setup: function(editor) {
                        editor.on('init', function() {
                            if (currentValue) {
                                editor.setContent(currentValue);
                            }
                        });
                        editor.on('change keyup', function() {
                            editor.save();
                        });
                    }
                });
            });
        }, 500);
    });

    // Function to initialize TinyMCE for edit RCA modal
    function initEditRcaTinyMCE() {
        if (typeof tinymce === 'undefined') {
            console.error('TinyMCE not loaded');
            return;
        }
        
        const editorConfigs = [
            { id: 'edit_root_cause_description', content: editRcaData.description || '' },
            { id: 'edit_contributing_factors', content: editRcaData.factors || '' },
            { id: 'edit_evidence_supporting_rca', content: editRcaData.evidence || '' }
        ];
        
        editorConfigs.forEach(function(config) {
            // Remove existing instance if present
            if (tinymce.get(config.id)) {
                tinymce.get(config.id).remove();
    $('#editRcaModal').on('shown.bs.modal', function() {
        var modal = $(this);
        var description = modal.data('rca-description') || '';
        var factors = modal.data('rca-factors') || '';
        var evidence = modal.data('rca-evidence') || '';
        
        setTimeout(function() {
            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE not loaded');
                return;
            }
            
            tinymce.init({
                selector: '#' + config.id,
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    editor.on('init', function() {
                        if (config.content) {
                            editor.setContent(config.content);
                        }
                    });
                    editor.on('change keyup', function() {
                        editor.save();
                    });
                }
            });
        });
    }
                
                // Destroy existing instance if any
                if (tinymce.get(textareaId)) {
                    tinymce.remove('#' + textareaId);
                }
                
                // Determine content based on textarea ID
                var currentValue = '';
                if (textareaId === 'edit_root_cause_description') {
                    currentValue = description;
                } else if (textareaId === 'edit_contributing_factors') {
                    currentValue = factors;
                } else if (textareaId === 'edit_evidence_supporting_rca') {
                    currentValue = evidence;
                } else {
                    currentValue = $textarea.val() || '';
                }
                
                // Set the textarea value first (for fallback)
                $textarea.val(currentValue);
                
                tinymce.init({
                    selector: '#' + textareaId,
                    menubar: false,
                    height: 300,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                    branding: false,
                    setup: function(editor) {
                        editor.on('init', function() {
                            if (currentValue) {
                                editor.setContent(currentValue);
                            }
                        });
                        editor.on('change keyup', function() {
                            editor.save();
                        });
                    }
                });
            });
        }, 100);
    });

    $('#addCapaModal').on('shown.bs.modal', function() {
        var modal = $(this);
        setTimeout(function() {
            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE not loaded');
                return;
            }
            
            // Initialize each editor in this modal
            modal.find('textarea.editor').each(function() {
                var $textarea = $(this);
                var textareaId = $textarea.attr('id');
                
                if (!textareaId) {
                    textareaId = 'editor-' + Math.random().toString(36).substr(2, 9);
                    $textarea.attr('id', textareaId);
                }
                
                // Skip if already initialized
                if (tinymce.get(textareaId)) {
                    return;
                }
                
                var currentValue = $textarea.val() || '';
                
                tinymce.init({
                    selector: '#' + textareaId,
                    menubar: false,
                    height: 300,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                    branding: false,
                    setup: function(editor) {
                        editor.on('init', function() {
                            if (currentValue) {
                                editor.setContent(currentValue);
                            }
                        });
                        editor.on('change keyup', function() {
                            editor.save();
                        });
                    }
                });
            });
        }, 500);
    });

    // Clean up TinyMCE when modals are hidden
    $('#addRcaModal, #editRcaModal, #addCapaModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            $(this).find('textarea.editor').each(function() {
                var editorId = $(this).attr('id');
                if (editorId && tinymce.get(editorId)) {
                    tinymce.get(editorId).remove();
                }
            });
        }
    });
</script>

<style>
    /* Format TinyMCE content in tables */
    .rca-table-content,
    .capa-table-content {
        font-size: 0.875rem;
        line-height: 1.6;
        color: #334155;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .rca-table-content p,
    .capa-table-content p {
        margin-bottom: 0.5rem;
    }
    
    .rca-table-content p:last-child,
    .capa-table-content p:last-child {
        margin-bottom: 0;
    }
    
    .rca-table-content ul,
    .rca-table-content ol,
    .capa-table-content ul,
    .capa-table-content ol {
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
    }
    
    .rca-table-content ul li,
    .rca-table-content ol li,
    .capa-table-content ul li,
    .capa-table-content ol li {
        margin-bottom: 0.25rem;
    }
    
    .rca-table-content strong,
    .capa-table-content strong {
        font-weight: 600;
        color: #1e293b;
    }
    
    .rca-table-content a,
    .capa-table-content a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .rca-table-content a:hover,
    .capa-table-content a:hover {
        text-decoration: underline;
    }

    /* RCA Table specific styling for better column widths */
    #rcaTable {
        min-width: 1200px;
    }
    
    #rcaTable thead th,
    #rcaTable tbody td {
        padding: 1.25rem 1.5rem;
        vertical-align: top;
    }
    
    #rcaTable tbody td {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
</style>

<!-- Add RCA Modal -->
<div class="modal fade" id="addRcaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.nc.rca.store', $nc->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-magnify"></i> Add Root Cause Analysis
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label for="root_cause_method_id" class="font-weight-600">Root Cause Method <span class="text-danger">*</span></label>
                        <select class="form-control @error('root_cause_method_id') is-invalid @enderror" id="root_cause_method_id" name="root_cause_method_id" required style="border-radius: 8px;">
                            <option value="">Select Method...</option>
                            @foreach($rootCauseMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        @error('root_cause_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">Select the method used for root cause analysis (e.g., 5 Whys, Fishbone Diagram)</small>
                    </div>

                    <div class="form-group">
                        <label for="root_cause_description" class="font-weight-600">Root Cause Description <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('root_cause_description') is-invalid @enderror" id="root_cause_description" name="root_cause_description" rows="5" required style="border-radius: 8px;" placeholder="Describe the actual root cause of the non-conformance"></textarea>
                        @error('root_cause_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="contributing_factors" class="font-weight-600">Contributing Factors</label>
                        <textarea class="form-control editor" id="contributing_factors" name="contributing_factors" rows="4" style="border-radius: 8px;" placeholder="List any contributing factors that led to the root cause"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="evidence_supporting_rca" class="font-weight-600">Evidence Supporting RCA</label>
                        <textarea class="form-control editor" id="evidence_supporting_rca" name="evidence_supporting_rca" rows="4" style="border-radius: 8px;" placeholder="Provide evidence that supports the identified root cause"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px;" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> Create RCA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit RCA Modal -->
<div class="modal fade" id="editRcaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form id="editRcaForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-info text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i> Edit Root Cause Analysis
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label for="edit_root_cause_method_id" class="font-weight-600">Root Cause Method <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_root_cause_method_id" name="root_cause_method_id" required style="border-radius: 8px;">
                            <option value="">Select Method...</option>
                            @foreach($rootCauseMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select the method used for root cause analysis (e.g., 5 Whys, Fishbone Diagram)</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_root_cause_description" class="font-weight-600">Root Cause Description <span class="text-danger">*</span></label>
                        <textarea class="form-control editor" id="edit_root_cause_description" name="root_cause_description" rows="5" required style="border-radius: 8px;" placeholder="Describe the actual root cause of the non-conformance"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_contributing_factors" class="font-weight-600">Contributing Factors</label>
                        <textarea class="form-control editor" id="edit_contributing_factors" name="contributing_factors" rows="4" style="border-radius: 8px;" placeholder="List any contributing factors that led to the root cause"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_evidence_supporting_rca" class="font-weight-600">Evidence Supporting RCA</label>
                        <textarea class="form-control editor" id="edit_evidence_supporting_rca" name="evidence_supporting_rca" rows="4" style="border-radius: 8px;" placeholder="Provide evidence that supports the identified root cause"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_status_id" class="font-weight-600">Status</label>
                        <select class="form-control" id="edit_status_id" name="status_id" style="border-radius: 8px;">
                            <option value="">Keep Current Status</option>
                            @php
                                $rcaStatuses = \App\Models\AuditModule\RcaStatus::active()->ordered()->get();
                            @endphp
                            @foreach($rcaStatuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Optionally change the status of this root cause analysis</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-info" style="border-radius: 8px;" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> Update RCA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add CAPA Modal -->
<div class="modal fade" id="addCapaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.nc.capa.store', $nc->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-checkbox-marked-circle"></i> Add Corrective Action (CAPA)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i> 
                        This corrective action will be linked to NC: <strong>{{ $nc->nc_number }}</strong>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="capa_title" class="font-weight-600">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="capa_title" name="title" required style="border-radius: 8px;" placeholder="Brief title for the corrective action">
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capa_action_type_id" class="font-weight-600">Action Type</label>
                                <select class="form-control" id="capa_action_type_id" name="action_type_id" style="border-radius: 8px;">
                                    <option value="">Select Type...</option>
                                    @foreach($capaActionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="capa_description" class="font-weight-600">Action Description <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('description') is-invalid @enderror" id="capa_description" name="description" rows="4" required style="border-radius: 8px;" placeholder="What corrective action will be taken?"></textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="capa_expected_outcome" class="font-weight-600">Expected Outcome</label>
                        <textarea class="form-control editor" id="capa_expected_outcome" name="expected_outcome" rows="2" style="border-radius: 8px;" placeholder="What is the expected result of this action?"></textarea>
                    </div>

                    <hr>
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Assignment</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capa_action_owner_id" class="font-weight-600">Assigned To <span class="text-danger">*</span></label>
                                <select class="form-control @error('action_owner_id') is-invalid @enderror" id="capa_action_owner_id" name="action_owner_id" required style="border-radius: 8px;">
                                    <option value="">Select Person...</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('action_owner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capa_due_date" class="font-weight-600">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="capa_due_date" name="due_date" required style="border-radius: 8px;" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                                @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capa_priority_id" class="font-weight-600">Priority</label>
                                <select class="form-control" id="capa_priority_id" name="priority_id" style="border-radius: 8px;">
                                    <option value="">Select Priority...</option>
                                    @foreach($capaPriorities as $priority)
                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="capa_category_id" class="font-weight-600">Category</label>
                        <select class="form-control" id="capa_category_id" name="category_id" style="border-radius: 8px;">
                            <option value="">Select Category...</option>
                            @foreach($capaCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Preventive Action (Optional)</h6>

                    <div class="form-group">
                        <label for="capa_preventive_measure" class="font-weight-600">Preventive Action</label>
                        <textarea class="form-control editor" id="capa_preventive_measure" name="preventive_measure" rows="3" style="border-radius: 8px;" placeholder="What actions will be taken to prevent recurrence?"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 8px;" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> Create Corrective Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
