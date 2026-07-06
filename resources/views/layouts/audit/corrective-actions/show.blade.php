@extends('layouts.audit.layout.app')

@section('title2')
<title>CAPA {{ $capa->capa_number }} - JASIRI LIMS</title>
<style type="text/css">
    :root {
        --primary-color: #28a745;
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

    .capa-show-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
    }

    .capa-show-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .capa-header-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        background: #ffffff;
        margin-bottom: 1.5rem;
    }

    .capa-header-card .card-header {
        background: var(--light-bg);
        border: none;
        border-left: 6px solid var(--success-color);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .capa-header-card .card-body {
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
        color: var(--success-color);
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

    /* TinyMCE Content Formatting in Detail Sections */
    .detail-text-content p {
        margin: 0 0 0.75rem 0;
    }

    .detail-text-content p:last-child {
        margin-bottom: 0;
    }

    .detail-text-content ul,
    .detail-text-content ol {
        margin: 0.75rem 0;
        padding-left: 1.5rem;
    }

    .detail-text-content li {
        margin: 0.5rem 0;
        line-height: 1.6;
    }

    .detail-text-content li p {
        margin: 0;
    }

    .detail-text-content strong {
        font-weight: 600;
        color: #1e293b;
    }

    .detail-text-content a {
        color: #3b82f6;
        text-decoration: none;
    }

    .detail-text-content a:hover {
        text-decoration: underline;
    }

    /* Remove data attributes styling */
    .detail-text-content [data-start],
    .detail-text-content [data-end] {
        /* These are TinyMCE internal attributes, hide them visually */
    }

    /* TinyMCE Content Formatting in Tables */
    .capa-table-content {
        color: #334155;
        line-height: 1.6;
        word-wrap: break-word;
    }

    .capa-table-content p {
        margin: 0 0 0.5rem 0;
    }

    .capa-table-content p:last-child {
        margin-bottom: 0;
    }

    .capa-table-content ul,
    .capa-table-content ol {
        margin: 0.5rem 0;
        padding-left: 1.5rem;
    }

    .capa-table-content li {
        margin: 0.25rem 0;
    }

    .capa-table-content strong {
        font-weight: 600;
        color: #1e293b;
    }

    .capa-table-content a {
        color: #3b82f6;
        text-decoration: none;
    }

    .capa-table-content a:hover {
        text-decoration: underline;
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
        background: var(--success-color);
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px var(--success-color);
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
        border-bottom-color: var(--success-color);
        color: var(--success-color);
        background: transparent;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: var(--success-color);
        color: var(--success-color);
        background: transparent;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .capa-header-card .card-body {
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
            'link' => route('audit.capa.index'),
            'name' => 'Corrective Actions',
            'icon' => null
            ],
            [
            'link' => '#',
            'name' => $capa->capa_number,
            'icon' => null
            ]
        ];
        $isClosed = $capa->status_name === 'Closed';
        $audit = $capa->nonConformance?->audit;
        $nc = $capa->nonConformance;
        $auditStatusName = $audit ? $audit->status_name : null;
        $isAuditClosed = $audit ? ($audit->status_name === 'Closed') : false;
        $isImplementStep = ($auditStatusName === 'Implement' || ($auditWorkflowStep ?? null) == 6);
        
        // Add action type to breadcrumbs if available
        $actionTypeName = $capa->action_type_name ?? ($capa->actionType?->name ?? null);
        if ($actionTypeName) {
            $items[count($items) - 1]['name'] = $capa->capa_number;
            $items[count($items) - 1]['badge'] = $actionTypeName;
            $items[count($items) - 1]['badge_class'] = 'badge-info';
        }
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card capa-header-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-1" style="font-size: 1.35rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; margin: 0;">
                            <i class="mdi mdi-checkbox-marked-circle text-success"></i> {{ $capa->capa_number }}
                        </h4>
                        @if(!$isClosed && !$isAuditClosed)
                        <a href="{{ route('audit.capa.edit', $capa->id) }}" class="btn btn-outline-warning" style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #f59e0b; color: #1e293b; background: transparent; transition: all 0.2s ease; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="Edit CAPA">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </a>
                        @else
                        <button class="btn btn-outline-secondary" disabled style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #94a3b8; color: #94a3b8; background: transparent; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ $isAuditClosed ? 'Audit is closed and cannot be edited' : 'CAPA is closed and cannot be edited' }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </button>
                        @endif
                </div>
                    <p class="mb-0 text-muted mt-1">{{ $capa->title }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($audit && !$isAuditClosed && !$isClosed)
                    <button type="button" class="btn btn-modern btn-success" data-toggle="modal" data-target="#workflowActionModal">
                        <i class="mdi mdi-check-decagram"></i> Workflow Action
                    </button>
                    @endif
                    @if($auditWorkflowStep == 7 && $capa->implementation_date && !$capa->latestVerification && !$isClosed)
                    <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#verifyModal">
                        <i class="mdi mdi-check-all"></i> Verify Effectiveness
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
                        Assigned To
                        </div>
                    <div class="detail-value">
                        {{ $capa->actionOwnerUser?->name ?? ($capa->action_owner_name ?? $capa->action_owner ?? 'N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-clock"></i>
                        Due Date
                        </div>
                    <div class="detail-value">
                            {{ $capa->due_date?->format('M d, Y') ?? 'Not set' }}
                            @if($capa->isOverdue())
                        <br><span class="badge badge-modern badge-danger" style="font-size: 0.7rem; margin-top: 0.25rem;">OVERDUE</span>
                            @elseif($capa->due_date)
                                @php
                                    $daysRemaining = now()->diffInDays($capa->due_date, false);
                                @endphp
                                @if($daysRemaining <= 3 && $daysRemaining > 0)
                            <br><span class="badge badge-modern badge-warning" style="font-size: 0.7rem; margin-top: 0.25rem;">{{ $daysRemaining }} days left</span>
                                @endif
                            @endif
                        </div>
                    </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-tag"></i>
                        Priority
                </div>
                    <div class="detail-value">
                        @if($capa->priority)
                        <span class="badge badge-modern" style="background-color: {{ $capa->priority->code === 'CRITICAL' ? '#dc2626' : ($capa->priority->code === 'HIGH' ? '#d97706' : '#3b82f6') }}; color: white;">
                            {{ $capa->priority_name ?? $capa->priority->name ?? 'N/A' }}
                        </span>
                        @else
                        <span class="badge badge-modern badge-secondary">Not Set</span>
                        @endif
                        </div>
                    </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-tag-multiple"></i>
                        Category
                </div>
                    <div class="detail-value">
                        {{ $capa->category?->name ?? ($capa->category_name ?? 'N/A') }}
                        </div>
                        </div>
                
                @if($nc)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-link"></i>
                        Related NC
                    </div>
                    <div class="detail-value">
                        <a href="{{ route('audit.nc.show', $nc->id) }}" style="color: var(--primary-color); text-decoration: none;">
                            {{ $nc->nc_number }}
                        </a>
                </div>
            </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-plus"></i>
                        Created On
                    </div>
                    <div class="detail-value">
                        {{ $capa->created_at?->format('M d, Y H:i') ?? 'N/A' }}
                    </div>
                </div>
                
                @if($capa->implementation_date)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-check"></i>
                        Implementation Date
                    </div>
                    <div class="detail-value">
                        {{ $capa->implementation_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
                
                @if($capa->latestVerification?->verification_date)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-star"></i>
                        Verification Date
                    </div>
                    <div class="detail-value">
                        {{ $capa->latestVerification->verification_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
                
                @if($capa->action_type_name || $capa->actionType)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-tag-multiple"></i>
                        Action Type
                    </div>
                    <div class="detail-value">
                        {{ $capa->action_type_name ?? ($capa->actionType?->name ?? 'Corrective') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabs Card -->
    <div class="card capa-show-card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#details">
                        <i class="mdi mdi-information-outline"></i> Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#implementation">
                        <i class="mdi mdi-wrench"></i> Implementation
                    </a>
                </li>
                @if($auditWorkflowStep == 7 || $auditStatusName === 'Verify' || $capa->latestVerification)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#verification">
                        <i class="mdi mdi-check-decagram"></i> Verification
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#attachments">
                        <i class="mdi mdi-paperclip"></i> Attachments 
                        <span class="badge badge-modern badge-info ml-1">{{ $capa->attachments->count() }}</span>
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
                        @if($capa->workflowApprovals->count() > 0)
                        <span class="badge badge-modern badge-success ml-1">{{ $capa->workflowApprovals->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            @if($audit && !$isAuditClosed)
            @php
                $requirements = [];
                
                // Check for implementation requirement at step 6 (only if NOT at step 7)
                if (($auditWorkflowStep == 6 || $auditStatusName === 'Implement') && $auditWorkflowStep != 7 && $auditStatusName !== 'Verify') {
                    // Check if this specific CAPA is implemented (status check matches Audit model logic)
                    if (!in_array($capa->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed'])) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'This corrective action must be implemented before the audit can proceed to verification. Click "Mark as Implemented" in the Implementation tab to record implementation details.',
                            'items' => []
                        ];
                    } else {
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
                    }
                }
                
                // Check for verification requirement at step 7
                if ($auditWorkflowStep == 7 || $auditStatusName === 'Verify') {
                    // Check if this specific CAPA is implemented and verified
                    $isImplemented = in_array($capa->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed']);
                    
                    if (!$isImplemented) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'This corrective action must be implemented before it can be verified. Click "Mark as Implemented" in the Implementation tab first.',
                            'items' => []
                        ];
                    } elseif ($isImplemented && !$capa->latestVerification) {
                        // PRIORITY: If this specific CAPA is not verified, show warning and don't check others
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'This corrective action must be verified for effectiveness before the audit can proceed. Click "Verify Effectiveness" to record verification details.',
                            'items' => []
                        ];
                    } elseif ($capa->latestVerification) {
                        // Only if THIS CAPA is verified, check if ALL other CAPAs are verified
                        $unverifiedCapas = $audit->nonConformances()
                            ->with('correctiveActions.latestVerification')
                            ->get()
                            ->flatMap(function($nc) {
                                return $nc->correctiveActions;
                            })
                            ->filter(function($capaItem) use ($capa) {
                                // Only check CAPAs that are implemented but not verified (excluding current CAPA)
                                $isItemImplemented = in_array($capaItem->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed']);
                                return $capaItem->id !== $capa->id && $isItemImplemented && !$capaItem->latestVerification;
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
                        } else {
                            // Check for missing Summary, Conclusion, and Recommendations (required for closing)
                            $missingFields = [];
                            if (empty($audit->executive_summary)) {
                                $missingFields[] = 'Summary';
                            }
                            if (empty($audit->conclusions)) {
                                $missingFields[] = 'Conclusion';
                            }
                            if (empty($audit->recommendations)) {
                                $missingFields[] = 'Recommendations';
                            }
                            
                            if (!empty($missingFields)) {
                                $editUrl = route('audit.audits.edit', $audit->id);
                                $requirements[] = [
                                    'type' => 'warning',
                                    'message' => 'Cannot close audit. The following required fields must be completed: ' . implode(', ', $missingFields) . '.',
                                    'items' => [[
                                        'number' => 'Fill Required Fields',
                                        'url' => $editUrl,
                                        'button_class' => 'btn-warning',
                                        'button_text' => 'Click here to fill them',
                                        'icon' => 'pencil'
                                    ]]
                            ];
                        } elseif ($canProceedToNext && $nextWorkflowStatus) {
                            $requirements[] = [
                                'type' => 'success',
                                'message' => 'All corrective actions have been verified. The audit is ready to proceed to the next step.',
                                'items' => []
                            ];
                        }
                        }
                    }
                }
                
                // Check for missing Summary, Conclusion, and Recommendations at step 7 or 8 (if not already checked above)
                if (($auditWorkflowStep == 7 || $auditWorkflowStep == 8 || $auditStatusName === 'Verify' || $auditStatusName === 'Pending Closure') && empty($requirements)) {
                    $missingFields = [];
                    if (empty($audit->executive_summary)) {
                        $missingFields[] = 'Summary';
                    }
                    if (empty($audit->conclusions)) {
                        $missingFields[] = 'Conclusion';
                    }
                    if (empty($audit->recommendations)) {
                        $missingFields[] = 'Recommendations';
                    }
                    
                    if (!empty($missingFields)) {
                        $editUrl = route('audit.audits.edit', $audit->id);
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'Cannot close audit. The following required fields must be completed: ' . implode(', ', $missingFields) . '.',
                            'items' => [[
                                'number' => 'Fill Required Fields',
                                'url' => $editUrl,
                                'button_class' => 'btn-warning',
                                'button_text' => 'Click here to fill them',
                                'icon' => 'pencil'
                            ]]
                        ];
                    }
                }
                
                // General check if can proceed (for other steps)
                if (empty($requirements) && $canProceedToNext && $nextWorkflowStatus) {
                    $requirements[] = [
                        'type' => 'success',
                        'message' => 'All requirements met. The audit is ready to proceed to the next workflow step.',
                        'items' => []
                    ];
                } elseif (empty($requirements) && !$canProceedToNext) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'Please complete all required actions for the current workflow step before proceeding. Review the audit requirements and ensure all corrective actions are properly implemented and verified.',
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
                        <i class="mdi mdi-{{ isset($item['icon']) ? $item['icon'] : 'alert-plus' }}" style="font-size: 0.875rem;"></i> {{ isset($item['button_text']) ? $item['button_text'] : 'View' }}
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
                                <div class="detail-text-content {{ empty($capa->description) ? 'empty' : '' }}">
                                    {!! $capa->description ?? 'No description provided' !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($capa->expected_outcome)
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-target"></i>
                                    Expected Outcome
                                </div>
                                <div class="detail-text-content">
                                    {!! $capa->expected_outcome !!}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if($capa->preventive_measure)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="mdi mdi-shield-check"></i>
                                    Preventive Action
                            </div>
                                <div class="detail-text-content">
                                    {!! $capa->preventive_measure !!}
                            </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Implementation Tab -->
                <div class="tab-pane fade" id="implementation" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="section-header mb-2">
                                <i class="mdi mdi-wrench text-success"></i> Implementation
                            </h6>
                            <small class="text-muted" style="display: block; margin-top: -0.5rem;">
                                <i class="mdi mdi-information-outline"></i> Record implementation details for this corrective action.
                            </small>
                                </div>
                        @if($isImplementStep && !$capa->implementation_date && !$isClosed)
                        <button type="button" class="btn btn-modern btn-success" data-toggle="modal" data-target="#implementModal">
                            <i class="mdi mdi-check-circle"></i> Mark as Implemented
                        </button>
                                @endif
                                </div>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Implementation Date</th>
                                    <th>Implementation Notes</th>
                                    <th>Evidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($capa->implementation_date || $capa->implementation_notes || $capa->implementation_evidence)
                                <tr>
                                    <td>
                                        @if($capa->implementation_date)
                                        <strong>{{ $capa->implementation_date->format('M d, Y') }}</strong>
                                        @else
                                        <span class="text-muted">-</span>
                                @endif
                                    </td>
                                    <td>
                                        @if($capa->implementation_notes)
                                        <div class="capa-table-content">
                                            {!! $capa->implementation_notes !!}
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                @if($capa->implementation_evidence)
                                        <div class="capa-table-content">
                                            {!! $capa->implementation_evidence !!}
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                @endif
                                    </td>
                                </tr>
                    @else
                                <tr class="empty-state-row">
                                    <td colspan="3">
                                        <i class="mdi mdi-wrench" style="font-size: 2rem; color: #dee2e6; display: block; margin-bottom: 0.5rem;"></i>
                                        No implementation details recorded yet.
                                    </td>
                                </tr>
                    @endif
                            </tbody>
                        </table>
                </div>
                </div>

                <!-- Verification Tab -->
                @if($auditWorkflowStep == 7 || $auditStatusName === 'Verify' || $capa->latestVerification)
                <div class="tab-pane fade" id="verification" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="section-header mb-2">
                                <i class="mdi mdi-check-decagram text-success"></i> Verification
                        </h6>
                            <small class="text-muted" style="display: block; margin-top: -0.5rem;">
                                <i class="mdi mdi-information-outline"></i> Record verification details for this corrective action's effectiveness.
                            </small>
                                </div>
                        @if(in_array($capa->status_name, ['Implemented', 'Verification Pending']) && !$capa->latestVerification && !$isClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#verifyModal">
                            <i class="mdi mdi-check-all"></i> Verify Effectiveness
                        </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Verification Date</th>
                                    <th>Verified By</th>
                                    <th>Effectiveness Result</th>
                                    <th>Verification Method</th>
                                    <th>Evidence Reviewed</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($capa->latestVerification)
                                <tr>
                                    <td>
                                        <strong>{{ $capa->latestVerification->verification_date?->format('M d, Y') ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        {{ $capa->latestVerification->verifiedByUser?->name ?? ($capa->latestVerification->verified_by ?? 'N/A') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-modern badge-{{ $capa->latestVerification->effectiveness_result_name === 'Effective' ? 'success' : ($capa->latestVerification->effectiveness_result_name === 'Not Effective' ? 'danger' : 'warning') }}">
                                            {{ $capa->latestVerification->effectiveness_result_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($capa->latestVerification->verification_method)
                                        <div class="capa-table-content">
                                            {!! $capa->latestVerification->verification_method !!}
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($capa->latestVerification->evidence_reviewed)
                                        <div class="capa-table-content">
                                            {!! $capa->latestVerification->evidence_reviewed !!}
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($capa->latestVerification->comments)
                                        <div class="capa-table-content">
                                            {!! $capa->latestVerification->comments !!}
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                    @else
                                <tr class="empty-state-row">
                                    <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary); font-style: italic;">
                            @if(in_array($capa->status_name, ['Implemented', 'Verification Pending']))
                                        No verification recorded yet. Click "Verify Effectiveness" to record verification details.
                            @else
                            Action must be implemented before verification.
                            @endif
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
                                <i class="mdi mdi-paperclip text-success"></i> Attachments
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
                                    @if($capa->attachments->count() > 0)
                                    <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($capa->attachments->count() > 0)
                                @foreach($capa->attachments as $attachment)
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
                                            @if(!$isClosed && !$isAuditClosed)
                                            <form action="{{ route('audit.capa.attachments.delete', $attachment->id) }}" 
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

                <!-- Activity Log Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    @if($capa->activityLogs && $capa->activityLogs->count() > 0)
                    <div class="timeline">
                        @foreach($capa->activityLogs->sortByDesc('created_at') as $log)
                        <div class="timeline-item">
                            <div class="d-flex align-items-start">
                                <div class="mr-3">
                                    <span class="badge badge-modern badge-{{ $log->action === 'Created' ? 'success' : ($log->action === 'Status Changed' ? 'info' : 'secondary') }}">
                                        {{ $log->action ?? 'Activity' }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1" style="font-weight: 500; color: #2d3748;">{{ $log->description }}</p>
                                    <small class="text-muted">
                                        <i class="mdi mdi-account"></i> {{ $log->performedBy?->name ?? 'System' }} 
                                        <i class="mdi mdi-clock-outline ml-2"></i> {{ $log->created_at->format('M d, Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="mdi mdi-history"></i>
                        <p>No activity recorded yet.</p>
                    </div>
                    @endif
                </div>

                <!-- Approval History Tab -->
                <div class="tab-pane fade" id="approval-history" role="tabpanel">
                    @php
                        $approvals = $capa->workflowApprovals()->with('approver')->ordered()->get();
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
                        <p>No approval history recorded yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Verification Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.capa.verify', $capa->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-all"></i> Verify Effectiveness
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label>Effectiveness Result <span class="text-danger">*</span></label>
                        <select name="result_id" id="verification_result_id" class="form-control" required style="border-radius: 8px;">
                            <option value="">Select...</option>
                            @foreach($verificationResults ?? [] as $result)
                            <option value="{{ $result->id }}" data-name="{{ $result->name }}" data-next-step="{{ $result->next_workflow_step }}">
                                {{ $result->name }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="result_name" id="result_name_hidden">
                    </div>
                    <div class="form-group">
                        <label>Verification Method</label>
                        <textarea name="verification_method" id="verification_method" class="form-control editor" rows="2" placeholder="How was effectiveness verified?" style="border-radius: 8px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Evidence Reviewed</label>
                        <textarea name="evidence_reviewed" id="evidence_reviewed" class="form-control editor" rows="2" placeholder="What evidence was reviewed?" style="border-radius: 8px;"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label>Comments</label>
                        <textarea name="comments" id="verification_comments" class="form-control editor" rows="3" style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px;" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-check-all"></i> Submit Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.capa.attachments.upload', $capa->id) }}" method="POST" enctype="multipart/form-data">
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

<!-- Implement Modal -->
<div class="modal fade" id="implementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <form action="{{ route('audit.capa.implement', $capa->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-circle"></i> Mark as Implemented
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label>Implementation Date <span class="text-danger">*</span></label>
                        <input type="date" name="implementation_date" class="form-control" 
                               value="{{ old('implementation_date', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                        <small class="form-text text-muted">Date when the corrective action was implemented</small>
                    </div>
                    <div class="form-group">
                        <label>Implementation Notes <span class="text-danger">*</span></label>
                        <textarea name="implementation_notes" id="implementation_notes" class="form-control editor" rows="5" 
                                  placeholder="Describe what was done to implement this corrective action..." 
                                  required style="border-radius: 8px;">{{ old('implementation_notes') }}</textarea>
                        <small class="form-text text-muted">Minimum 10 characters. Provide detailed description of the implementation.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label>Evidence</label>
                        <textarea name="implementation_evidence" id="implementation_evidence" class="form-control editor" rows="3" 
                                  placeholder="Any evidence or documentation related to the implementation..." 
                                  style="border-radius: 8px;">{{ old('implementation_evidence') }}</textarea>
                        <small class="form-text text-muted">Optional: Reference to evidence, documents, or records</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 8px;" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-check-circle"></i> Mark as Implemented
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

    // Handle verification result selection
    $('#verification_result_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const resultName = selectedOption.data('name');
        $('#result_name_hidden').val(resultName);
    });
</script>

<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    // Initialize TinyMCE for Implement Modal
    $('#implementModal').on('shown.bs.modal', function() {
        if (typeof tinymce === 'undefined') {
            console.error('TinyMCE not loaded');
            return;
        }
        
        setTimeout(function() {
            // Destroy existing instances if any
            tinymce.remove('#implementation_notes, #implementation_evidence');
            
            // Initialize TinyMCE for implementation notes
            tinymce.init({
                selector: '#implementation_notes',
                menubar: false,
                height: 300,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic | bullist numlist | link',
                branding: false,
                promotion: false
            });
            
            // Initialize TinyMCE for implementation evidence
            tinymce.init({
                selector: '#implementation_evidence',
                menubar: false,
                height: 200,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic | bullist numlist | link',
                branding: false,
                promotion: false
            });
        }, 300);
    });
    
    // Clean up TinyMCE when Implement modal is closed
    $('#implementModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#implementation_notes, #implementation_evidence');
        }
    });
    
    // Initialize TinyMCE for Verify Modal
    $('#verifyModal').on('shown.bs.modal', function() {
        if (typeof tinymce === 'undefined') {
            console.error('TinyMCE not loaded');
            return;
        }
        
        setTimeout(function() {
            // Destroy existing instances if any
            tinymce.remove('#verification_method, #evidence_reviewed, #verification_comments');
            
            // Initialize TinyMCE for verification method
            tinymce.init({
                selector: '#verification_method',
                menubar: false,
                height: 200,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic | bullist numlist | link',
                branding: false,
                promotion: false
            });
            
            // Initialize TinyMCE for evidence reviewed
            tinymce.init({
                selector: '#evidence_reviewed',
                menubar: false,
                height: 200,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic | bullist numlist | link',
                branding: false,
                promotion: false
            });
            
            // Initialize TinyMCE for comments
            tinymce.init({
                selector: '#verification_comments',
                menubar: false,
                height: 250,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic | bullist numlist | link',
                branding: false,
                promotion: false
            });
        }, 300);
    });
    
    // Clean up TinyMCE when Verify modal is closed
    $('#verifyModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#verification_method, #evidence_reviewed, #verification_comments');
        }
    });
</script>
@endsection
