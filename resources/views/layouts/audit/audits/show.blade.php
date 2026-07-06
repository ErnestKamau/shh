@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit {{ $audit->audit_number }} - JASIRI LIMS</title>
<style type="text/css">
    :root {
        --primary-color: #007bff;
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

    .audit-show-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
    }

    .audit-show-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .audit-header-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        background: #ffffff;
        margin-bottom: 1.5rem;
    }

    .audit-header-card .card-header {
        background: var(--light-bg);
        border: none;
        border-left: 6px solid var(--primary-color);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .audit-header-card .card-body {
        background: #ffffff;
        padding: 1.5rem 2rem;
    }

    /* Info Card Grid System - Aligned with theme */
    .info-card-row {
        display: flex;
        flex-wrap: wrap;
        margin: -0.5rem;
    }

    .info-card-col {
        flex: 1;
        min-width: 0;
        padding: 0.5rem;
        display: flex;
    }

    .info-card-content {
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .info-card-box {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        padding: 1rem 1.25rem;
        background: var(--light-bg);
        font-weight: 500;
        word-break: break-word;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60px;
        flex-grow: 1;
        text-align: center;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .info-card-box:hover {
        background: #f1f3f5;
        border-left-color: #0056b3;
        transform: translateX(4px);
    }

    .info-card-label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-card-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        text-align: center;
    }

    /* Legacy info-box for backward compatibility */
    .info-box {
        background: var(--light-bg);
        border-left: 4px solid var(--primary-color);
        border-radius: var(--border-radius-sm);
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .info-box:hover {
        background: #f1f3f5;
        border-left-color: #0056b3;
        transform: translateX(4px);
    }

    .info-box-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-secondary);
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .info-box-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    /* Chain of Custody Summary Cards */
    .custody-summary-row {
        display: flex;
        flex-wrap: wrap;
        margin: -0.375rem;
    }

    .custody-summary-row > [class*="col-"] {
        padding: 0.375rem;
        display: flex;
    }

    .custody-summary-card {
        width: 100%;
        min-height: 95px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .custody-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12) !important;
    }

    @media (max-width: 768px) {
        .custody-summary-card {
            min-height: 90px;
        }
    }

    /* Navigation Tabs */
    .nav-tabs {
        border-bottom: 2px solid var(--border-color);
        background: transparent;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        font-weight: 500;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-tabs .nav-link:hover {
        border-bottom-color: var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: var(--primary-color);
        color: var(--primary-color);
        background: transparent;
        font-weight: 600;
    }

    /* Modern Professional Table Styling - Filament-like */
    .table-modern {
        border-radius: var(--border-radius-sm);
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .table-modern thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .table-modern thead th {
        background: transparent;
        border: none;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.75rem;
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
    .table-modern + .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: var(--border-radius-sm);
        margin-top: 1rem;
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

    .detail-text-content {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        color: #334155;
        line-height: 1.7;
        min-height: 60px;
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

    .detail-text-content.empty {
        color: #94a3b8;
        font-style: italic;
    }
    
    /* NC table formatted content styling */
    .nc-table-content {
        font-size: 0.875rem;
        line-height: 1.6;
        color: #334155;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .nc-table-content p {
        margin-bottom: 0.5rem;
    }
    
    .nc-table-content p:last-child {
        margin-bottom: 0;
    }
    
    .nc-table-content ul,
    .nc-table-content ol {
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
    }
    
    .nc-table-content ul li,
    .nc-table-content ol li {
        margin-bottom: 0.25rem;
    }
    
    .nc-table-content ul li:last-child,
    .nc-table-content ol li:last-child {
        margin-bottom: 0;
    }
    
    .nc-table-content strong {
        font-weight: 600;
        color: #1e293b;
    }
    
    .nc-table-content a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .nc-table-content a:hover {
        text-decoration: underline;
    }
    
    /* Findings table formatted content styling */
    .table-modern td div p {
        margin-bottom: 0.5rem;
    }
    
    .table-modern td div p:last-child {
        margin-bottom: 0;
    }
    
    .table-modern td div ul,
    .table-modern td div ol {
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
        font-size: 0.875rem;
    }
    
    .table-modern td div ul li,
    .table-modern td div ol li {
        margin-bottom: 0.25rem;
    }
    
    .table-modern td div strong {
        font-weight: 600;
        color: #1e293b;
    }
    
    .table-modern td div em {
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
        background: var(--primary-color);
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px var(--primary-color);
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
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-header i {
        font-size: 1.25rem;
    }

    /* Content Sections */
    .content-section {
        margin-bottom: 2rem;
    }

    .content-section:last-child {
        margin-bottom: 0;
    }

    /* Modal Enhancements */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
    }

    .modal-header {
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .audit-header-card .card-body {
            padding: 1rem;
        }

        .info-card-col {
            flex: 0 0 100%;
            max-width: 100%;
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

        .btn-modern {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .btn-modern:last-child {
            margin-bottom: 0;
        }
    }

    @media (max-width: 576px) {
        .audit-header-card .card-header {
            padding: 1rem;
        }

        .info-card-box {
            padding: 0.75rem 1rem;
            min-height: 50px;
        }

        .info-card-label {
            font-size: 0.7rem;
        }

        .info-card-value {
            font-size: 0.875rem;
        }
    }

    /* Print Styles */
    @media print {
        .btn-modern,
        .nav-tabs,
        .modal {
            display: none !important;
        }

        .audit-show-card {
            box-shadow: none;
            border: 1px solid var(--border-color);
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
                'name' => __('Audit & CAPA Dashboard'),
                'icon' => null
            ],
            [
                'link' => route('audit.audits.index'),
                'name' => __('Audit Management'),
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => $audit->audit_number,
                'icon' => null
            ]
        ];
        $currentStep = $audit->getCurrentWorkflowStep() ?? 1;
        $isClosed = $audit->status_name === 'Closed';
    @endphp
    @php
        $statusClass = match($audit->status_name) {
            'Closed' => 'success',
            'In Progress' => 'info',
            'Scheduled' => 'warning',
            default => 'secondary'
        };
        // Add status badge as the last item in breadcrumbs
        $items[count($items) - 1]['name'] = $audit->audit_number;
        $items[count($items) - 1]['badge'] = $audit->status_name;
        $items[count($items) - 1]['badge_class'] = 'badge-'.$statusClass;
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card audit-header-card">
        <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-1" style="font-size: 1.35rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; margin: 0;">
                            <i class="mdi mdi-file-document-check text-primary"></i> {{ $audit->audit_number }}
                        </h4>
                        @if(!$isClosed)
                        <a href="{{ route('audit.audits.edit', $audit->id) }}" class="btn btn-outline-warning" style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #f59e0b; color: #1e293b; background: transparent; transition: all 0.2s ease; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ __('Edit Audit') }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </a>
                        @else
                        <button class="btn btn-outline-secondary" disabled style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #94a3b8; color: #94a3b8; background: transparent; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ __('Audit is closed and cannot be edited') }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </button>
                        @endif
                    </div>
                    <p class="mb-0 text-muted mt-1">{{ $audit->title }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(!$isClosed)
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
                        <i class="mdi mdi-file-document-multiple"></i>
                        {{ __('Audit Type') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->auditType?->name ?? __('N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account-star"></i>
                        {{ __('Lead Auditor') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->lead_auditor_name ?? $audit->leadAuditor?->name ?? __('N/A') }}
                        @if($audit->leadAuditor && $audit->leadAuditor->email)
                        <br><small style="color: #64748b; font-size: 0.8125rem;">{{ $audit->leadAuditor->email }}</small>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-clock"></i>
                        {{ __('Scheduled Date') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->scheduled_date?->format('M d, Y') ?? __('N/A') }}
                    </div>
                </div>
                
                @if($audit->start_date)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-start"></i>
                        {{ __('Start Date') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->start_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
                
                @if($audit->end_date)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-end"></i>
                        {{ __('End Date') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->end_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-office-building"></i>
                        {{ __('Department / Area') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->department ?? ($audit->auditee_department_name ?? __('N/A')) }}
                    </div>
                </div>
                
                @if($audit->auditee_name)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account"></i>
                        {{ __('Auditee') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->auditee_name }}
                    </div>
                </div>
                @endif
                
                @if($audit->auditee_department_name)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-domain"></i>
                        {{ __('Auditee Department') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->auditee_department_name }}
                    </div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-check"></i>
                        {{ __('Created On') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->created_at?->format('M d, Y H:i') ?? __('N/A') }}
                    </div>
                </div>
                
                @if($audit->updated_at && $audit->updated_at != $audit->created_at)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-edit"></i>
                        {{ __('Last Updated') }}
                    </div>
                    <div class="detail-value">
                        {{ $audit->updated_at->format('M d, Y H:i') }}
                    </div>
                </div>
                @endif
            </div>
            
        </div>
    </div>

    <!-- Tabs Card -->
    <div class="card audit-show-card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#details" role="tab">
                        <i class="mdi mdi-information-outline"></i> {{ __('Details') }}
                    </a>
                </li>
                @if($currentStep >= 2)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#findings" role="tab">
                        <i class="mdi mdi-alert-circle"></i> {{ __('Findings') }}
                        @if($audit->findings->count() > 0)
                        <span class="badge badge-secondary ml-1">{{ $audit->findings->count() }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @php
                    // Show NC tab from "Record Findings & NC" status onwards (including all subsequent statuses)
                    $ncTabStatuses = [
                        'Record Findings & NC',
                        'Root Cause',
                        'Root Cause Analysis',
                        'Assign CAPA',
                        'Implement',
                        'Verify',
                        'Pending Closure',
                        'Close',
                        'Closed'
                    ];
                    $showNCTab = in_array($audit->status_name, $ncTabStatuses);
                @endphp
                @if($showNCTab)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#ncs" role="tab">
                        <i class="mdi mdi-alert-octagon"></i> {{ __('Non-Conformances') }}
                        @if($audit->nonConformances->count() > 0)
                        <span class="badge badge-danger ml-1">{{ $audit->nonConformances->count() }}</span>
                        @endif
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#team-members" role="tab">
                        <i class="mdi mdi-account-group"></i> {{ __('Team Members') }}
                        @if($audit->teamMembers->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $audit->teamMembers->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#checklists" role="tab">
                        <i class="mdi mdi-format-list-checks"></i> {{ __('Checklists') }}
                        @if($audit->checklists->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $audit->checklists->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#attachments" role="tab">
                        <i class="mdi mdi-paperclip"></i> {{ __('Attachments') }}
                        @if($audit->attachments->count() > 0)
                        <span class="badge badge-info ml-1">{{ $audit->attachments->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#activity" role="tab">
                        <i class="mdi mdi-history"></i> {{ __('Activity Log') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approval-history" role="tab">
                        <i class="mdi mdi-account-check"></i> {{ __('Approval History') }}
                        @if($audit->workflowApprovals->count() > 0)
                        <span class="badge badge-success ml-1">{{ $audit->workflowApprovals->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            @if(!$isClosed)
            @php
                $currentStep = $audit->getCurrentWorkflowStep() ?? 1;
                $auditStatusName = $audit->status_name ?? '';
                $requirements = [];
                
                // Check for missing Summary, Conclusion, and Recommendations (required for closing)
                $missingSummaryFields = [];
                if (empty($audit->executive_summary)) {
                    $missingSummaryFields[] = 'Summary';
                }
                if (empty($audit->conclusions)) {
                    $missingSummaryFields[] = 'Conclusion';
                }
                if (empty($audit->recommendations)) {
                    $missingSummaryFields[] = 'Recommendations';
                }
                
                // Summary fields are required before closing (not during CAPA In Progress)
                if ($currentStep >= 9 && !empty($missingSummaryFields)) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'The following required fields must be completed before closing the audit:',
                        'items' => array_map(function ($field) use ($audit) {
                            return [
                                'name' => $field,
                                'url' => route('audit.audits.edit', $audit->id),
                            ];
                        }, $missingSummaryFields),
                        'action_url' => route('audit.audits.edit', $audit->id),
                        'action_text' => 'Fill All Fields'
                    ];
                }
                
                // Get findings requiring NCs if not already passed
                if (!isset($findingsRequiringNC)) {
                    $findingsRequiringNC = $audit->getFindingsRequiringNC();
                }
                
                // Check for findings requiring NCs first (priority check - blocks progression)
                if ($findingsRequiringNC && $findingsRequiringNC->count() > 0 && $currentStep >= 2) {
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
                } elseif ($currentStep === 2 && $audit->findings()->count() === 0) {
                    $requirements[] = [
                        'type' => 'info',
                        'message' => 'Please record at least one finding before proceeding.',
                        'items' => []
                    ];
                } elseif ($currentStep === 5 || $auditStatusName === 'Root Cause Analysis') {
                    $ncsWithoutRca = $audit->getNCsWithoutRootCauseAnalysis();

                    if ($ncsWithoutRca->count() > 0) {
                        $requirements[] = [
                            'type' => 'warning',
                            'message' => 'The following non-conformances require root cause analysis before proceeding:',
                            'items' => $ncsWithoutRca->map(function($nc) {
                                return [
                                    'number' => $nc->nc_number,
                                    'url' => route('audit.nc.show', $nc->id),
                                    'button_class' => 'btn-primary',
                                    'button_text' => 'Add RCA',
                                    'icon' => 'magnify'
                                ];
                            })->toArray()
                        ];
                    }
                } elseif ($currentStep === 6 || $auditStatusName === 'CAPA Assigned') {
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
                                    'url' => auditNcCapaFormUrl($nc->id),
                                    'button_class' => 'btn-primary',
                                    'button_text' => 'Add CAPA',
                                    'icon' => 'plus'
                                ];
                            })->toArray()
                        ];
                    } else {
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
                                'message' => 'CAPAs are assigned. Mark each corrective action as implemented before advancing the audit:',
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
                        } elseif (isset($canProceedToNext) && $canProceedToNext && isset($nextWorkflowStatus) && $nextWorkflowStatus) {
                            $requirements[] = [
                                'type' => 'success',
                                'message' => 'All CAPAs are assigned and implemented. Ready to proceed to CAPA In Progress.',
                                'items' => []
                            ];
                        }
                    }
                } elseif ($currentStep === 7 || $auditStatusName === 'CAPA In Progress') {
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
                            'message' => 'The following corrective actions must be implemented before the audit can proceed:',
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
                    } else {
                        $unverifiedCapas = $audit->nonConformances()
                            ->with('correctiveActions.latestVerification')
                            ->get()
                            ->flatMap(function($nc) {
                                return $nc->correctiveActions;
                            })
                            ->filter(function($capaItem) {
                                $isItemImplemented = in_array($capaItem->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed']);
                                return $isItemImplemented && !$capaItem->latestVerification;
                            });

                        if ($unverifiedCapas->count() > 0) {
                            $requirements[] = [
                                'type' => 'warning',
                                'message' => 'CAPAs are implemented. Verify effectiveness on each corrective action, then use Workflow Action to advance to CAPA Verification:',
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
                        } elseif (isset($canProceedToNext) && $canProceedToNext && isset($nextWorkflowStatus) && $nextWorkflowStatus) {
                            $requirements[] = [
                                'type' => 'success',
                                'message' => 'All corrective actions are verified. Ready to proceed to CAPA Verification.',
                                'items' => []
                            ];
                        }
                    }
                } elseif ($currentStep === 8 || $auditStatusName === 'CAPA Verification') {
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
                    } elseif ($unverifiedCapas->count() === 0 && isset($canProceedToNext) && $canProceedToNext && isset($nextWorkflowStatus) && $nextWorkflowStatus) {
                        // Only show success if there are no unverified CAPAs
                        $requirements[] = [
                            'type' => 'success',
                            'message' => 'All corrective actions have been verified. The audit is ready to proceed to the next step.',
                            'items' => []
                        ];
                    }
                } elseif (isset($canProceedToNext) && $canProceedToNext && isset($nextWorkflowStatus) && $nextWorkflowStatus) {
                    // Double-check: ensure no findings requiring NCs before showing ready
                    $findingsReqCheck = isset($findingsRequiringNC) ? $findingsRequiringNC : $audit->getFindingsRequiringNC();
                    if (!$findingsReqCheck || $findingsReqCheck->count() === 0) {
                        $requirements[] = [
                            'type' => 'success',
                            'message' => 'All requirements met. Ready to proceed to next step.',
                            'items' => []
                        ];
                    }
                } elseif (isset($canProceedToNext) && !$canProceedToNext) {
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
                <div class="alert alert-warning mb-2 py-2 px-3" role="alert" style="border-left: 4px solid #ffc107; border-radius: 4px; font-size: 0.875rem;">
                    <div class="d-flex align-items-center flex-wrap mb-2">
                        <i class="mdi mdi-alert-circle-outline mr-2" style="font-size: 1rem;"></i>
                        <strong class="mr-2">Action Required:</strong>
                        <span>{{ $req['message'] }}</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">
                        @foreach($req['items'] as $index => $item)
                        <span class="badge badge-light mr-2 mb-1" style="font-size: 0.75rem; padding: 0.4rem 0.6rem;">
                            {{ isset($item['number']) ? $item['number'] : (isset($item['name']) ? $item['name'] : ($item['category'] ?? 'N/A')) }}
                        </span>
                        @if(isset($item['url']))
                        <a href="{{ $item['url'] }}" class="btn btn-sm {{ isset($item['button_class']) ? $item['button_class'] : 'btn-danger' }} mr-2 mb-1" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; line-height: 1.3;">
                            <i class="mdi mdi-{{ isset($item['icon']) ? $item['icon'] : 'alert-plus' }}" style="font-size: 0.875rem;"></i> {{ isset($item['button_text']) ? $item['button_text'] : 'View' }}
                        </a>
                        @endif
                        @if($index < count($req['items']) - 1)
                        <span class="text-muted mr-1">•</span>
                        @endif
                        @endforeach
                        @if(isset($req['action_url']) && isset($req['action_text']))
                        <a href="{{ $req['action_url'] }}" class="btn btn-sm btn-primary ml-auto mb-1" style="padding: 0.4rem 1rem; font-size: 0.875rem; font-weight: 600;">
                            <i class="mdi mdi-pencil"></i> {{ $req['action_text'] }}
                        </a>
                        @endif
                    </div>
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
                        <div class="col-lg-6">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-file-document-outline"></i> {{ __('Scope') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->scope) ? 'empty' : '' }}">
                                    @if(!empty($audit->scope))
                                        {!! $audit->scope !!}
                                    @else
                                        {{ __('Not specified') }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-check-circle-outline"></i> {{ __('Criteria') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->criteria) ? 'empty' : '' }}">
                                    @if(!empty($audit->criteria))
                                        {!! $audit->criteria !!}
                                    @else
                                        {{ __('Not specified') }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-target"></i> {{ __('Objectives') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->objective) ? 'empty' : '' }}">
                                    @if(!empty($audit->objective))
                                        {!! $audit->objective !!}
                                    @else
                                        {{ __('Not specified') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-text-box-outline"></i> {{ __('Summary') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->executive_summary) ? 'empty' : '' }}">
                                    @if(!empty($audit->executive_summary))
                                        {!! $audit->executive_summary !!}
                                    @else
                                        {{ __('No summary available') }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-check-all"></i> {{ __('Conclusion') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->conclusions) ? 'empty' : '' }}">
                                    @if(!empty($audit->conclusions))
                                        {!! $audit->conclusions !!}
                                    @else
                                        {{ __('No conclusion yet') }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-lightbulb-outline"></i> {{ __('Recommendations') }}
                                </h6>
                                <div class="detail-text-content {{ empty($audit->recommendations) ? 'empty' : '' }}">
                                    @if(!empty($audit->recommendations))
                                        {!! $audit->recommendations !!}
                                    @else
                                        {{ __('No recommendations yet') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Findings Tab -->
                @if($currentStep >= 2)
                <div class="tab-pane fade" id="findings" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-alert-circle text-primary"></i> {{ __('Audit Findings') }}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            @if($currentStep >= 2 && !$isClosed)
                            <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addFindingModal">
                                <i class="mdi mdi-plus"></i> {{ __('Add Finding') }}
                            </button>
                            @elseif($isClosed)
                                <small class="text-muted">{{ __('Audit is closed') }}</small>
                            @else
                                <small class="text-muted">{{ __('Complete Step 1 to record findings') }}</small>
                            @endif
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">{{ __('Finding #') }}</th>
                                    <th style="min-width: 150px;">{{ __('Category') }}</th>
                                    <th style="min-width: 200px;">{{ __('Observation') }}</th>
                                    <th style="min-width: 180px;">{{ __('Requirement') }}</th>
                                    <th style="min-width: 180px;">{{ __('Objective Evidence') }}</th>
                                    <th style="min-width: 100px;">{{ __('ISO Clause') }}</th>
                                    <th style="min-width: 120px;">{{ __('SOP Reference') }}</th>
                                    <th style="min-width: 120px;">{{ __('Risk Level') }}</th>
                                    <th style="min-width: 150px;">{{ __('Responsible Person') }}</th>
                                    <th style="min-width: 130px;">{{ __('Due Date') }}</th>
                                    @if($audit->findings->count() > 0)
                                    <th style="min-width: 150px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($audit->findings->count() > 0)
                                    @foreach($audit->findings as $finding)
                                    <tr>
                                        <td>
                                            <span style="font-weight: 600; color: #1e293b;">{{ $finding->finding_number }}</span>
                                        </td>
                                        <td>
                                            <span style="color: #475569;">{{ $finding->findingCategory?->name ?? $finding->finding_category_name ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <div style="max-width: 200px; line-height: 1.5; max-height: 100px; overflow: hidden;">
                                                @if(!empty($finding->observation))
                                                    <div style="color: #334155; font-size: 0.875rem;">
                                                        {!! $finding->observation !!}
                                                    </div>
                                                @else
                                                    <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 180px; line-height: 1.5; max-height: 100px; overflow: hidden;">
                                                @if(!empty($finding->requirement))
                                                    <div style="color: #334155; font-size: 0.875rem;">
                                                        {!! $finding->requirement !!}
                                                    </div>
                                                @else
                                                    <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 180px; line-height: 1.5; max-height: 100px; overflow: hidden;">
                                                @if(!empty($finding->objective_evidence))
                                                    <div style="color: #334155; font-size: 0.875rem;">
                                                        {!! $finding->objective_evidence !!}
                                                    </div>
                                                @else
                                                    <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #475569;">{{ $finding->iso_clause ?? __('N/A') }}</code>
                                        </td>
                                        <td>
                                            <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #475569;">{{ $finding->sop_reference ?? __('N/A') }}</code>
                                        </td>
                                        <td>
                                            @if($finding->riskLevel)
                                            <span style="color: #334155; font-weight: 500; font-size: 0.875rem;">{{ $finding->riskLevel->name }}</span>
                                            @else
                                            <span style="color: #94a3b8; font-size: 0.875rem;">{{ __('Not Assessed') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($finding->responsibleUser)
                                                <span style="color: #334155;">{{ $finding->responsibleUser->name }}</span>
                                            @elseif($finding->responsible_person)
                                                <span style="color: #334155;">{{ $finding->responsible_person }}</span>
                                            @else
                                                <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($finding->response_due_date)
                                                <span style="color: #334155;">{{ $finding->response_due_date->format('M d, Y') }}</span>
                                            @else
                                                <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($currentStep >= 2 && !$isClosed)
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-toggle="modal" 
                                                        data-target="#editFindingModal{{ $finding->id }}"
                                                        title="{{ __('Edit Finding') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                @endif
                                                @if($currentStep >= 2 && !$isClosed && !$finding->hasNonConformance() && $finding->requiresCapa())
                                                <a href="{{ route('audit.nc.create', ['audit_id' => $audit->id, 'finding_id' => $finding->id]) }}" 
                                                   class="btn btn-outline-danger" title="{{ __('Raise NC') }}">
                                                    <i class="mdi mdi-alert-plus"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="11" style="text-align: center; padding: 3rem 1rem;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                                <i class="mdi mdi-alert-circle-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                                                <p style="margin: 0; color: #64748b; font-size: 0.9375rem;">{{ __('No findings recorded yet.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Non-Conformances Tab -->
                @if($showNCTab)
                <div class="tab-pane fade" id="ncs" role="tabpanel">
                    @if($showNCTab && $audit->findings->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-alert-octagon text-danger"></i> {{ __('Non-Conformances') }}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            @if(!$isClosed)
                            <a href="{{ route('audit.nc.create', ['audit_id' => $audit->id]) }}" class="btn btn-modern btn-danger">
                                <i class="mdi mdi-plus"></i> {{ __('Create NC') }}
                            </a>
                            @else
                            <small class="text-muted">{{ __('Audit is closed') }}</small>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">{{ __('NC #') }}</th>
                                    <th style="min-width: 200px;">{{ __('Title') }}</th>
                                    <th style="min-width: 150px;">{{ __('Origin') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th style="min-width: 100px; text-align: center;">{{ __('CAPAs') }}</th>
                                    @if($audit->nonConformances->count() > 0)
                                    <th style="min-width: 120px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($audit->nonConformances->count() > 0)
                                    @foreach($audit->nonConformances as $nc)
                                    <tr>
                                        <td>
                                            <span style="font-weight: 600; color: #1e293b;">{{ $nc->nc_number }}</span>
                                        </td>
                                        <td>
                                            <strong style="color: #1e293b;">{{ $nc->title ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span style="color: #334155;">{{ $nc->origin_name ?? ($nc->origin?->name ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            <div class="nc-table-content">
                                                {!! $nc->description ?? 'N/A' !!}
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-modern badge-info">{{ $nc->correctiveActions->count() }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ route('audit.nc.show', $nc->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="mdi mdi-eye"></i> {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 3rem 1rem;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                                <i class="mdi mdi-alert-octagon-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                                                <p style="margin: 0; color: #64748b; font-size: 0.9375rem;">{{ __('No non-conformances raised for this audit.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Team Members Tab -->
                <div class="tab-pane fade" id="team-members" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-account-group text-primary"></i> {{ __('Audit Team') }}
                        </h6>
                        @if(!$isClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addTeamMemberModal">
                            <i class="mdi mdi-account-plus"></i> {{ __('Add Team Member') }}
                        </button>
                        @endif
                    </div>

                    <!-- Lead Auditor Section -->
                    @if($audit->lead_auditor_name || $audit->leadAuditor)
                    <div class="card mb-3" style="border-left: 4px solid var(--primary-color); border-radius: var(--border-radius-sm);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 section-header">
                                        <i class="mdi mdi-account-star text-primary"></i> {{ __('Lead Auditor') }}
                                    </h6>
                                    <p class="mb-0">
                                        <strong>{{ $audit->lead_auditor_name ?? $audit->leadAuditor->name ?? __('N/A') }}</strong>
                                        @if($audit->leadAuditor && $audit->leadAuditor->email)
                                        <br><small class="text-muted">{{ $audit->leadAuditor->email }}</small>
                                        @endif
                                    </p>
                                </div>
                                <span class="badge badge-modern badge-primary">{{ __('Lead Auditor') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Team Members List -->
                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 180px;">{{ __('Name') }}</th>
                                    <th style="min-width: 200px;">{{ __('Email') }}</th>
                                    <th style="min-width: 150px;">{{ __('Role') }}</th>
                                    <th>{{ __('Responsibilities') }}</th>
                                    @if($audit->teamMembers->count() > 0 && !$isClosed)
                                    <th style="min-width: 120px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($audit->teamMembers->count() > 0)
                                    @foreach($audit->teamMembers as $member)
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center;">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; color: white; font-weight: 600; font-size: 0.875rem;">
                                                    {{ strtoupper(substr($member->user->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 600; color: #1e293b;">{{ $member->user->name ?? __('N/A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="color: #64748b; font-size: 0.875rem;">{{ $member->user->email ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-modern badge-info">
                                                {{ $member->role_name ?? $member->role->name ?? __('No Role') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(!empty($member->responsibilities))
                                                <div style="color: #64748b; font-size: 0.875rem; line-height: 1.5;">
                                                    {!! Str::limit($member->responsibilities, 150) !!}
                                                </div>
                                            @else
                                                <span style="color: #64748b; font-size: 0.875rem;">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        @if(!$isClosed)
                                        <td style="text-align: center;">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary" 
                                                        data-toggle="modal" 
                                                        data-target="#editTeamMemberModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-user-name="{{ $member->user->name ?? '' }}"
                                                        data-role-id="{{ $member->role_id }}"
                                                        data-responsibilities="{{ $member->responsibilities ?? '' }}"
                                                        title="{{ __('Edit') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <form action="{{ route('audit.audits.team-members.destroy', $member->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to remove') }} {{ $member->user->name ?? __('this team member') }} {{ __('from the audit team?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="{{ __('Remove') }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ !$isClosed ? '5' : '4' }}" style="text-align: center; padding: 3rem 1rem;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                                <i class="mdi mdi-account-group-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                                                <p style="margin: 0; color: #64748b; font-size: 0.9375rem;">{{ __('No team members assigned yet. Click "Add Team Member" to get started.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Checklists Tab -->
                <div class="tab-pane fade" id="checklists" role="tabpanel">
                    @livewire('audit-module.audit-checklists-manager', ['auditId' => $audit->id])
                </div>

                <!-- Attachments Tab -->
                <div class="tab-pane fade" id="attachments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-paperclip text-primary"></i> {{ __('Attachments') }}
                        </h6>
                        @if(!$isClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#uploadAttachmentModal">
                            <i class="mdi mdi-upload"></i> {{ __('Add Attachment') }}
                        </button>
                        @endif
                    </div>

                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 250px;">{{ __('File Name') }}</th>
                                    <th style="min-width: 100px;">{{ __('Type') }}</th>
                                    <th style="min-width: 100px;">{{ __('Size') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th style="min-width: 150px;">{{ __('Uploaded By') }}</th>
                                    <th style="min-width: 150px;">{{ __('Uploaded On') }}</th>
                                    @if($audit->attachments->count() > 0)
                                    <th style="min-width: 150px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($audit->attachments->count() > 0)
                                    @foreach($audit->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center;">
                                                <div style="width: 40px; height: 40px; border-radius: 8px; background: {{ $attachment->isPdf() ? '#ef4444' : ($attachment->isImage() ? '#10b981' : '#6366f1') }}; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; color: white;">
                                                    <i class="mdi mdi-{{ $attachment->isPdf() ? 'file-pdf' : ($attachment->isImage() ? 'file-image' : 'file') }}" style="font-size: 1.25rem;"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.125rem;">{{ $attachment->title ?? $attachment->original_name ?? $attachment->file_name }}</div>
                                                    @if($attachment->title && $attachment->original_name)
                                                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $attachment->original_name }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-modern badge-secondary">{{ $attachment->file_type ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <span style="color: #64748b; font-size: 0.875rem;">{{ $attachment->file_size_formatted ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <span style="color: #64748b; font-size: 0.875rem;">{{ $attachment->description ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span style="color: #334155;">{{ $attachment->uploadedByUser?->name ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <span style="color: #64748b; font-size: 0.875rem;">{{ $attachment->created_at?->format('M d, Y H:i') ?? __('N/A') }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                                   target="_blank"
                                                   class="btn btn-outline-info" title="{{ __('Download') }}">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                                @if($attachment->isImage())
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-toggle="modal" 
                                                        data-target="#viewImageModal"
                                                        data-image-url="{{ asset('storage/' . $attachment->file_path) }}"
                                                        data-image-name="{{ $attachment->title ?? $attachment->original_name ?? $attachment->file_name }}"
                                                        title="{{ __('Preview') }}">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                @endif
                                                @if(!$isClosed)
                                                <form action="{{ route('audit.audits.attachments.delete', $attachment->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this attachment?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 3rem 1rem;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                                <i class="mdi mdi-file-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                                                <p style="margin: 0; color: #64748b; font-size: 0.9375rem;">{{ __('No attachments uploaded yet.') }}</p>
                                            </div>
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
                        $chainOfCustody = $chainOfCustodyService->getChainOfCustody($audit);
                    @endphp
                    
                    @if($chainOfCustody['timeline'] || $audit->activityLogs->count() > 0)
                    <!-- Chain of Custody Summary -->
                    @if(count($chainOfCustody['timeline']) > 0)
                    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #17a2b8; padding: 0.625rem 0.875rem;">
                            <h5 class="mb-0" style="font-weight: 600; color: #2d3748; font-size: 0.9375rem;">
                                <i class="mdi mdi-timeline-clock-outline text-info"></i> Chain of Custody Summary
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 0.75rem;">
                            <div class="row custody-summary-row">
                                <div class="col-md-4 col-lg-3">
                                    <div class="custody-summary-card" style="background: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 0.625rem; text-align: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;">
                                        <div>
                                            <div style="margin-bottom: 0.375rem;">
                                                <i class="mdi mdi-clock-outline" style="font-size: 1rem; color: #007bff;"></i>
                                            </div>
                                            <h6 class="text-muted mb-0" style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 0.375rem;">Total Duration</h6>
                                            <h4 class="mb-0" style="font-size: 1rem; font-weight: 700; color: #007bff; line-height: 1.2;">{{ $chainOfCustody['total_duration_formatted'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                                @if($chainOfCustody['longest_step'])
                                <div class="col-md-4 col-lg-3">
                                    <div class="custody-summary-card" style="background: #fffbf0; border: 1px solid #ffeaa7; border-radius: 6px; padding: 0.625rem; text-align: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;">
                                        <div>
                                            <div style="margin-bottom: 0.375rem;">
                                                <i class="mdi mdi-timer-sand" style="font-size: 1rem; color: #ffc107;"></i>
                                            </div>
                                            <h6 class="text-muted mb-0" style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #856404; margin-bottom: 0.375rem;">Longest Step</h6>
                                            <h5 class="mb-0" style="font-size: 0.8125rem; font-weight: 600; color: #856404; line-height: 1.3; word-break: break-word;">{{ $chainOfCustody['longest_step']['step_name'] }}</h5>
                                            <small class="text-muted" style="font-size: 0.6875rem; color: #856404; font-weight: 500;">{{ $chainOfCustody['longest_step']['average_formatted'] }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($chainOfCustody['fastest_step'])
                                <div class="col-md-4 col-lg-3">
                                    <div class="custody-summary-card" style="background: #e7f5f8; border: 1px solid #bee5eb; border-radius: 6px; padding: 0.625rem; text-align: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;">
                                        <div>
                                            <div style="margin-bottom: 0.375rem;">
                                                <i class="mdi mdi-speedometer" style="font-size: 1rem; color: #17a2b8;"></i>
                                            </div>
                                            <h6 class="text-muted mb-0" style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #0c5460; margin-bottom: 0.375rem;">Fastest Step</h6>
                                            <h5 class="mb-0" style="font-size: 0.8125rem; font-weight: 600; color: #0c5460; line-height: 1.3; word-break: break-word;">{{ $chainOfCustody['fastest_step']['step_name'] }}</h5>
                                            <small class="text-muted" style="font-size: 0.6875rem; color: #0c5460; font-weight: 500;">{{ $chainOfCustody['fastest_step']['average_formatted'] }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(count($chainOfCustody['bottlenecks']) > 0)
                                <div class="col-md-4 col-lg-3">
                                    <div class="custody-summary-card" style="background: #fff5f5; border: 1px solid #feb2b2; border-radius: 6px; padding: 0.625rem; text-align: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;">
                                        <div>
                                            <div style="margin-bottom: 0.375rem;">
                                                <i class="mdi mdi-alert-circle" style="font-size: 1rem; color: #dc3545;"></i>
                                            </div>
                                            <h6 class="text-muted mb-0" style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #721c24; margin-bottom: 0.375rem;">Bottlenecks</h6>
                                            <h4 class="mb-0" style="font-size: 1rem; font-weight: 700; color: #dc3545; line-height: 1.2;">{{ count($chainOfCustody['bottlenecks']) }}</h4>
                                            <small class="text-muted" style="font-size: 0.6875rem; color: #721c24; font-weight: 500;">Identified</small>
                                        </div>
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
                                @foreach($audit->activityLogs->sortByDesc('created_at') as $log)
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
                        $approvals = $audit->workflowApprovals()->with('approver')->ordered()->get();
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

                    <!-- Pending Approvals Section -->
                    @php
                        $pendingApprovers = $audit->getPendingApprovers();
                        $requiredApprovers = $audit->getRequiredApprovers();
                    @endphp
                    @if($pendingApprovers->count() > 0)
                    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); border-left: 6px solid #ffc107;">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="mdi mdi-clock-alert"></i> Pending Approvals
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">The following approvers need to approve before proceeding to the next workflow step:</p>
                            <ul class="list-group">
                                @foreach($pendingApprovers as $approver)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $approver->user->name ?? 'N/A' }}</strong>
                                        @if($approver->iso_role)
                                        <span class="badge badge-secondary ml-2">{{ $approver->iso_role }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $approver->user->email ?? '' }}</small>
                                    </div>
                                    <span class="badge badge-warning">{{ ucfirst($approver->role_type) }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modals -->
<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('audit.audits.attachments.upload', $audit->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-upload"></i> {{ __('Upload Attachment') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title" class="font-weight-600">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="255" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter a title for this file') }}">
                        <small class="form-text text-muted">{{ __('This title will be displayed instead of the file name') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="file" class="font-weight-600">{{ __('File') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" required style="border-radius: var(--border-radius-sm);">
                        <small class="form-text text-muted">{{ __('Maximum file size: 10MB') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="description" class="font-weight-600">{{ __('Description') }}</label>
                        <textarea class="form-control" id="description" name="description" rows="3" style="border-radius: var(--border-radius-sm);"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-upload"></i> {{ __('Upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Image Modal -->
<div class="modal fade" id="viewImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="imageModalTitle">
                    <i class="mdi mdi-image"></i> {{ __('Image Preview') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="{{ __('Preview') }}" class="img-fluid" style="max-height: 70vh; border-radius: var(--border-radius-sm);">
            </div>
        </div>
    </div>
</div>

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamMemberModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('audit.audits.team-members.store', $audit->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-account-plus"></i> {{ __('Add Team Member') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="user_id" class="font-weight-600">{{ __('User') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="user_id" name="user_id" required style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select User...') }}</option>
                            @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{ __('Select a user to add to the audit team') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="role_id" class="font-weight-600">{{ __('Role') }}</label>
                        <select class="form-control" id="role_id" name="role_id" style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select Role (Optional)...') }}</option>
                            @foreach($teamRoles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{ __('Assign a role to the team member') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="responsibilities" class="font-weight-600">{{ __('Responsibilities') }}</label>
                        <textarea class="form-control editor" id="responsibilities" name="responsibilities" rows="5" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the team member\'s responsibilities...') }}"></textarea>
                        <small class="form-text text-muted">{{ __('Optional: Describe specific responsibilities for this team member') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-account-plus"></i> {{ __('Add Member') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Member Modal -->
<div class="modal fade" id="editTeamMemberModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editTeamMemberForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-account-edit"></i> {{ __('Edit Team Member') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-600">{{ __('User') }}</label>
                        <input type="text" class="form-control" id="edit_user_name" readonly style="border-radius: var(--border-radius-sm); background-color: var(--light-bg);">
                        <small class="form-text text-muted">{{ __('User cannot be changed') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_role_id" class="font-weight-600">{{ __('Role') }}</label>
                        <select class="form-control" id="edit_role_id" name="role_id" style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select Role (Optional)...') }}</option>
                            @foreach($teamRoles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{ __('Assign a role to the team member') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_responsibilities" class="font-weight-600">{{ __('Responsibilities') }}</label>
                        <textarea class="form-control editor" id="edit_responsibilities" name="responsibilities" rows="5" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the team member\'s responsibilities...') }}"></textarea>
                        <small class="form-text text-muted">{{ __('Optional: Describe specific responsibilities for this team member') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-info" style="border-radius: var(--border-radius-sm);" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> {{ __('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Finding Modal -->
<div class="modal fade" id="addFindingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('audit.audits.findings.store', $audit->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-plus"></i> {{ __('Add Audit Finding') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="finding_category_id" class="font-weight-600">{{ __('Finding Category') }} <span class="text-danger">*</span></label>
                        <select class="form-control @error('finding_category_id') is-invalid @enderror" id="finding_category_id" name="finding_category_id" required style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select Category...') }}</option>
                            @foreach($findingCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('finding_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="observation" class="font-weight-600">{{ __('Observation') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('observation') is-invalid @enderror" id="observation" name="observation" rows="4" required style="border-radius: var(--border-radius-sm);" placeholder="{{ __('What was found during the audit?') }}"></textarea>
                        @error('observation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="requirement" class="font-weight-600">{{ __('Requirement') }}</label>
                        <textarea class="form-control editor" id="requirement" name="requirement" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('What should have been (ISO clause, SOP, regulation, etc.)') }}"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="objective_evidence" class="font-weight-600">{{ __('Objective Evidence') }}</label>
                        <textarea class="form-control editor" id="objective_evidence" name="objective_evidence" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Supporting evidence, documents, records, etc.') }}"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="iso_clause" class="font-weight-600">{{ __('ISO Clause') }}</label>
                                <input type="text" class="form-control" id="iso_clause" name="iso_clause" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('e.g., 7.10, 8.7') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sop_reference" class="font-weight-600">{{ __('SOP Reference') }}</label>
                                <input type="text" class="form-control" id="sop_reference" name="sop_reference" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('e.g., SOP-QC-001') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="risk_level_id" class="font-weight-600">{{ __('Risk Level') }}</label>
                                <select class="form-control" id="risk_level_id" name="risk_level_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Risk Level...') }}</option>
                                    @foreach($riskLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="response_due_date" class="font-weight-600">{{ __('Response Due Date') }}</label>
                                <input type="date" class="form-control" id="response_due_date" name="response_due_date" style="border-radius: var(--border-radius-sm);" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsible_user_id" class="font-weight-600">{{ __('Responsible Person') }}</label>
                                <select class="form-control" id="responsible_user_id" name="responsible_user_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Person...') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsible_person" class="font-weight-600">{{ __('Or External Party Name') }}</label>
                                <input type="text" class="form-control" id="responsible_person" name="responsible_person" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Name if external') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> {{ __('Add Finding') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Finding Modals -->
@foreach($audit->findings as $finding)
<div class="modal fade" id="editFindingModal{{ $finding->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('audit.audits.findings.update', [$audit->id, $finding->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i> {{ __('Edit Finding') }}: {{ $finding->finding_number }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="finding_category_id_{{ $finding->id }}" class="font-weight-600">{{ __('Finding Category') }} <span class="text-danger">*</span></label>
                        <select class="form-control @error('finding_category_id') is-invalid @enderror" id="finding_category_id_{{ $finding->id }}" name="finding_category_id" required style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select Category...') }}</option>
                            @foreach($findingCategories as $category)
                            <option value="{{ $category->id }}" {{ $finding->finding_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('finding_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="observation_{{ $finding->id }}" class="font-weight-600">{{ __('Observation') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('observation') is-invalid @enderror" id="observation_{{ $finding->id }}" name="observation" rows="4" required style="border-radius: var(--border-radius-sm);" placeholder="{{ __('What was found during the audit?') }}">{{ $finding->observation }}</textarea>
                        @error('observation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="requirement_{{ $finding->id }}" class="font-weight-600">{{ __('Requirement') }}</label>
                        <textarea class="form-control editor" id="requirement_{{ $finding->id }}" name="requirement" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('What should have been (ISO clause, SOP, regulation, etc.)') }}">{{ $finding->requirement }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="objective_evidence_{{ $finding->id }}" class="font-weight-600">{{ __('Objective Evidence') }}</label>
                        <textarea class="form-control editor" id="objective_evidence_{{ $finding->id }}" name="objective_evidence" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Supporting evidence, documents, records, etc.') }}">{{ $finding->objective_evidence }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="iso_clause_{{ $finding->id }}" class="font-weight-600">{{ __('ISO Clause') }}</label>
                                <input type="text" class="form-control" id="iso_clause_{{ $finding->id }}" name="iso_clause" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('e.g., 7.10, 8.7') }}" value="{{ $finding->iso_clause }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sop_reference_{{ $finding->id }}" class="font-weight-600">{{ __('SOP Reference') }}</label>
                                <input type="text" class="form-control" id="sop_reference_{{ $finding->id }}" name="sop_reference" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('e.g., SOP-QC-001') }}" value="{{ $finding->sop_reference }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="risk_level_id_{{ $finding->id }}" class="font-weight-600">{{ __('Risk Level') }}</label>
                                <select class="form-control" id="risk_level_id_{{ $finding->id }}" name="risk_level_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Risk Level...') }}</option>
                                    @foreach($riskLevels as $level)
                                    <option value="{{ $level->id }}" {{ $finding->risk_level_id == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="response_due_date_{{ $finding->id }}" class="font-weight-600">{{ __('Response Due Date') }}</label>
                                <input type="date" class="form-control" id="response_due_date_{{ $finding->id }}" name="response_due_date" style="border-radius: var(--border-radius-sm);" value="{{ $finding->response_due_date ? $finding->response_due_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsible_user_id_{{ $finding->id }}" class="font-weight-600">{{ __('Responsible Person') }}</label>
                                <select class="form-control" id="responsible_user_id_{{ $finding->id }}" name="responsible_user_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Person...') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $finding->responsible_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsible_person_{{ $finding->id }}" class="font-weight-600">{{ __('Or External Party Name') }}</label>
                                <input type="text" class="form-control" id="responsible_person_{{ $finding->id }}" name="responsible_person" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Name if external') }}" value="{{ $finding->responsible_person }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status_id_{{ $finding->id }}" class="font-weight-600">{{ __('Status') }}</label>
                        <select class="form-control" id="status_id_{{ $finding->id }}" name="status_id" style="border-radius: var(--border-radius-sm);">
                            <option value="">{{ __('Select Status...') }}</option>
                            @foreach($findingStatuses as $status)
                            <option value="{{ $status->id }}" {{ $finding->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> {{ __('Update Finding') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

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
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
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
        
        // Form validation passed, allow submission without confirmation
    });

    // Edit team member modal
    $('#editTeamMemberModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const memberId = button.data('member-id');
        const userName = button.data('user-name');
        const roleId = button.data('role-id');
        const responsibilities = button.data('responsibilities');
        
        if (!memberId) {
            console.error('Team member ID is missing');
            return;
        }
        
        const modal = $(this);
        const form = modal.find('#editTeamMemberForm');
        
        // Set form action
        const routeTemplate = '{{ route("audit.audits.team-members.update", 0) }}';
        form.attr('action', routeTemplate.replace(/\/\d+$/, '/' + memberId));
        
        // Populate form fields
        modal.find('#edit_user_name').val(userName);
        modal.find('#edit_role_id').val(roleId || '');
        
        // Initialize TinyMCE if available, then set content
        if (typeof tinymce !== 'undefined') {
            // Destroy existing instance if any
            tinymce.remove('#edit_responsibilities');
            
            // Initialize TinyMCE
            tinymce.init({
                selector: '#edit_responsibilities',
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    editor.on('init', function() {
                        editor.setContent(responsibilities || '');
                    });
                }
            });
        } else {
            modal.find('#edit_responsibilities').val(responsibilities || '');
        }
    });
    
    // Initialize TinyMCE for Add Team Member modal
    $('#addTeamMemberModal').on('shown.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            // Destroy existing instance if any
            tinymce.remove('#responsibilities');
            
            // Initialize TinyMCE
            tinymce.init({
                selector: '#responsibilities',
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false
            });
        }
    });
    
    // Clean up TinyMCE when modals are closed
    $('#addTeamMemberModal, #editTeamMemberModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#responsibilities, #edit_responsibilities');
        }
    });
    
    // Initialize TinyMCE for Add Finding modal - Initialize before modal opens
    $('#addFindingModal').on('show.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            // Destroy existing instances if any
            tinymce.remove('#observation, #requirement, #objective_evidence');
            
            // Initialize TinyMCE for all editor textareas in the modal
            modal.find('textarea.editor').each(function() {
                const textareaId = $(this).attr('id');
                if (textareaId && !tinymce.get(textareaId)) {
                    tinymce.init({
                        selector: '#' + textareaId,
                        menubar: false,
                        height: 300,
                        plugins: 'lists link code',
                        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                        branding: false
                    });
                }
            });
        }
    });
    
    // Also initialize TinyMCE on page load if modal elements exist (for faster initialization)
    $(document).ready(function() {
        if (typeof tinymce !== 'undefined' && $('#addFindingModal').length) {
            // Check if editors exist but aren't initialized yet
            $('#addFindingModal textarea.editor').each(function() {
                const textareaId = $(this).attr('id');
                if (textareaId && !tinymce.get(textareaId)) {
                    // Pre-initialize but keep them hidden until modal opens
                    tinymce.init({
                        selector: '#' + textareaId,
                        menubar: false,
                        height: 300,
                        plugins: 'lists link code',
                        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                        branding: false,
                        hidden_input: false
                    });
                }
            });
        }
    });
    
    // Initialize TinyMCE for Edit Finding modals (dynamic)
    $(document).on('shown.bs.modal', '[id^="editFindingModal"]', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            const modalId = modal.attr('id');
            
            // Destroy existing instances for this modal
            modal.find('textarea.editor').each(function() {
                tinymce.remove('#' + $(this).attr('id'));
            });
            
            // Initialize TinyMCE for all editor textareas in this modal
            tinymce.init({
                selector: '#' + modalId + ' textarea.editor',
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false
            });
        }
    });
    
    // Clean up TinyMCE when finding modals are closed
    $('#addFindingModal, [id^="editFindingModal"]').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            modal.find('textarea.editor').each(function() {
                tinymce.remove('#' + $(this).attr('id'));
            });
        }
    });
    
    // Add triggerSave to all finding form submit buttons
    $(document).on('submit', '#addFindingModal form, [id^="editFindingModal"] form', function() {
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }
    });

    // Responsive table wrapper
    $(document).ready(function() {
        // Add horizontal scroll indicator for wide tables
        $('.table-responsive').each(function() {
            const $table = $(this);
            if ($table[0].scrollWidth > $table[0].clientWidth) {
                $table.css('position', 'relative');
            }
        });
    });
</script>
@endsection

