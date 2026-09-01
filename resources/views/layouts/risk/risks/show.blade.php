@extends('layouts.risk.layout.app')

@section('title2')
<title>Risk {{ $risk->risk_number }} - JASIRI LIMS</title>
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

    /* Linked Items Badge Styles */
    .linked-item-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--border-radius-sm);
        font-size: 0.875rem;
        color: var(--text-primary);
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .linked-item-badge:hover {
        background: #e2e8f0;
        border-color: var(--primary-color);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .linked-item-badge i {
        color: var(--primary-color);
        font-size: 1rem;
    }

    .linked-item-badge strong {
        font-weight: 600;
        color: #475569;
    }

    .linked-item-badge a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }

    .linked-item-badge a:hover {
        text-decoration: underline;
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
                'link' => route('risk.risks.index'),
                'name' => __('Risk Management'),
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => $risk->risk_number,
                'icon' => null
            ]
        ];
        $currentStep = $risk->getCurrentWorkflowStep() ?? 1;
        $isClosed = $risk->isClosed();
        $likelihoodScore = $risk->likelihoodScale->score ?? $risk->likelihood_score ?? null;
        $severityScore = $risk->severityScale->score ?? $risk->severity_score ?? null;
        $hasAssessment = $likelihoodScore && $severityScore;
        $hasEvaluation = ! empty($risk->evaluation_result);
        $canEditAssessment = ! $isClosed
            && auth()->user()->can('risk-management.components.risks.edit')
            && (
                in_array((int) $currentStep, [2, 3, 4], true)
                || ($hasAssessment && (int) $currentStep < 8)
            );
        $canEditEvaluation = ! $isClosed
            && auth()->user()->can('risk-management.components.risks.edit')
            && $hasAssessment
            && (int) $currentStep >= 4
            && (int) $currentStep < 8;
    @endphp
    @php
        // Breadcrumbs render escaped text, so keep the label plain.
        $items[count($items) - 1]['name'] = $risk->risk_number . ' - ' . ($risk->display_status_name ?? 'Unknown');
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card audit-header-card">
        <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-1" style="font-size: 1.35rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; margin: 0;">
                            <i class="mdi mdi-alert-octagon text-danger"></i> {{ $risk->risk_number }}
                        </h4>
                        @if(!$isClosed)
                        @if(auth()->user()->can('risk-management.components.risks.edit'))
                        <a href="{{ route('risk.risks.edit', $risk->id) }}" class="btn btn-outline-warning" style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #f59e0b; color: #1e293b; background: transparent; transition: all 0.2s ease; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ __('Edit Risk') }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </a>
                        @endif
                        @else
                        <button class="btn btn-outline-secondary" disabled style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #94a3b8; color: #94a3b8; background: transparent; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ __('Risk is closed and cannot be edited') }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </button>
                        @endif
                    </div>
                    <p class="mb-0 text-muted mt-1">{{ $risk->title }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(!$isClosed)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                    <button type="button" class="btn btn-modern btn-success" data-toggle="modal" data-target="#workflowActionModal">
                        <i class="mdi mdi-check-decagram"></i> Workflow Action
                    </button>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Comprehensive Details Grid -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-tag-multiple"></i>
                        {{ __('Category') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->category_name ?? __('N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-source-branch"></i>
                        {{ __('Other Sources') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->other_source_name ?? __('N/A') }}
                    </div>
                </div>
                
                @if($risk->sample_id)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-flask-outline"></i>
                        {{ __('Sample') }} <span class="text-muted" style="font-size: 0.65rem; font-weight: 400;">(Source)</span>
                    </div>
                    <div class="detail-value">
                        @if($risk->sample)
                            @php
                                $sampleRoute = null;
                                try {
                                    $sampleRoute = route('sample.show', $risk->sample_id);
                                } catch (\Exception $e) {
                                    // Route doesn't exist, just display text
                                }
                            @endphp
                            @if($sampleRoute)
                                <a href="{{ $sampleRoute }}" target="_blank" style="color: var(--primary-color); text-decoration: none;">
                                    {{ $risk->sample->batch_code ?? $risk->sample_reference }}
                                    @if($risk->sample->reference_number) - {{ $risk->sample->reference_number }}@endif
                                </a>
                            @else
                                {{ $risk->sample->batch_code ?? $risk->sample_reference }}
                                @if($risk->sample->reference_number) - {{ $risk->sample->reference_number }}@endif
                            @endif
                        @else
                            {{ $risk->sample_reference ?? __('N/A') }}
                        @endif
                    </div>
                </div>
                @endif
                
                @if($risk->equipment_id)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-cog"></i>
                        {{ __('Equipment') }} <span class="text-muted" style="font-size: 0.65rem; font-weight: 400;">(Source)</span>
                    </div>
                    <div class="detail-value">
                        @if($risk->equipment)
                            @php
                                $equipmentRoute = null;
                                try {
                                    $equipmentRoute = route('equipment.show', $risk->equipment_id);
                                } catch (\Exception $e) {
                                    // Route doesn't exist, just display text
                                }
                            @endphp
                            @if($equipmentRoute)
                                <a href="{{ $equipmentRoute }}" target="_blank" style="color: var(--primary-color); text-decoration: none;">
                                    {{ $risk->equipment->name }}
                                </a>
                            @else
                                {{ $risk->equipment->name }}
                            @endif
                        @else
                            {{ __('Equipment ID') }}: {{ $risk->equipment_id }}
                        @endif
                    </div>
                </div>
                @endif
                
                @if($risk->method_id)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-flask"></i>
                        {{ __('Method') }} <span class="text-muted" style="font-size: 0.65rem; font-weight: 400;">(Source)</span>
                    </div>
                    <div class="detail-value">
                        @if($risk->method)
                            {{ $risk->method->name }}
                            @if($risk->method->code) <span class="text-muted">({{ $risk->method->code }})</span>@endif
                        @else
                            {{ $risk->method_reference ?? __('N/A') }}
                        @endif
                    </div>
                </div>
                @endif
                
                @if($risk->personnel_id)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account"></i>
                        {{ __('Personnel') }} <span class="text-muted" style="font-size: 0.65rem; font-weight: 400;">(Source)</span>
                    </div>
                    <div class="detail-value">
                        @if($risk->personnel)
                            {{ $risk->personnel->name }}
                        @else
                            {{ __('User ID') }}: {{ $risk->personnel_id }}
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account-star"></i>
                        {{ __('Risk Owner') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->risk_owner_name ?? __('N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-clock"></i>
                        {{ __('Date Identified') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->date_identified?->format('M d, Y') ?? __('N/A') }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account"></i>
                        {{ __('Identified By') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->identified_by ?? __('N/A') }}
                    </div>
                </div>
                
                @if($risk->department)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-office-building"></i>
                        {{ __('Department') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->department }}
                    </div>
                </div>
                @endif
                
                @if($risk->risk_level)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-alert-circle"></i>
                        {{ __('Risk Level') }}
                    </div>
                    <div class="detail-value">
                        <span class="badge badge-{{ $risk->risk_level === 'Critical' ? 'danger' : ($risk->risk_level === 'High' ? 'warning' : ($risk->risk_level === 'Medium' ? 'info' : 'secondary')) }}">
                            {{ $risk->risk_level }}
                        </span>
                    </div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-check"></i>
                        {{ __('Created On') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->created_at?->format('M d, Y H:i') ?? __('N/A') }}
                    </div>
                </div>
                
                @if($risk->updated_at && $risk->updated_at != $risk->created_at)
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-edit"></i>
                        {{ __('Last Updated') }}
                    </div>
                    <div class="detail-value">
                        {{ $risk->updated_at->format('M d, Y H:i') }}
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
                @if($risk->processLinks->count() > 0 || ($currentStep == 2 && !$isClosed && auth()->user()->can('risk-management.components.risks.edit')))
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#processes" role="tab">
                        <i class="mdi mdi-link-variant"></i> {{ __('Processes') }}
                        @if($risk->processLinks->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $risk->processLinks->count() }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @if($hasAssessment || (int) $currentStep >= 2)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#assessment" role="tab">
                        <i class="mdi mdi-clipboard-check"></i> {{ __('Assessment') }}
                    </a>
                </li>
                @endif
                @if($risk->evaluation_result || $currentStep >= 4)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#evaluation" role="tab">
                        <i class="mdi mdi-scale-balance"></i> {{ __('Evaluation') }}
                    </a>
                </li>
                @endif
                @if(($risk->treatmentPlans && $risk->treatmentPlans->count() > 0) || $currentStep >= 5)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#treatment-plans" role="tab">
                        <i class="mdi mdi-clipboard-list"></i> {{ __('Treatment Plans') }}
                        @if($risk->treatmentPlans && $risk->treatmentPlans->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $risk->treatmentPlans->count() }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @if(($risk->reviews && $risk->reviews->count() > 0) || $currentStep >= 7)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#reviews" role="tab">
                        <i class="mdi mdi-eye"></i> {{ __('Reviews') }}
                        @if($risk->reviews && $risk->reviews->count() > 0)
                        <span class="badge badge-info ml-1">{{ $risk->reviews->count() }}</span>
                        @endif
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#attachments" role="tab">
                        <i class="mdi mdi-paperclip"></i> {{ __('Attachments') }}
                        @if($risk->attachments && $risk->attachments->count() > 0)
                        <span class="badge badge-info ml-1">{{ $risk->attachments->count() }}</span>
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
                        <i class="mdi mdi-check-all"></i> {{ __('Approval History') }}
                        @if(isset($approvals) && $approvals->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $approvals->count() }}</span>
                        @endif
                    </a>
                </li>

            </ul>
        </div>
        <div class="card-body">
            @if(!$isClosed)
            @php
                $currentStep = $risk->getCurrentWorkflowStep() ?? 1;
                $riskStatusName = $risk->display_status_name ?? '';
                $requirements = [];
                
                // Check for missing required fields based on workflow step
                // Assessment is only required from step 3 (Under Assessment) onwards
                // Step 2 (Identified) doesn't require assessment yet
                if ($currentStep == 3 && (!$risk->likelihood_score || !$risk->severity_score)) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'Risk assessment is required. Please complete the assessment before proceeding.',
                        'items' => [],
                    ];
                }
                
                // Evaluation is only required from step 4 (Under Evaluation) onwards
                if ($currentStep >= 4 && !$risk->evaluation_result) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'Risk evaluation is required. Please complete the evaluation before proceeding.',
                        'items' => [],
                    ];
                }
                
                if ($currentStep >= 4 && $risk->evaluation_result === 'Unacceptable' && (!$risk->treatmentPlans || $risk->treatmentPlans->count() === 0)) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => 'Treatment plan is required for unacceptable risks. Please create a treatment plan.',
                        'items' => [],
                    ];
                }
                
                if ($currentStep >= 5 && $risk->treatmentPlans && $risk->treatmentPlans->count() > 0) {
                    $incompletePlans = $risk->treatmentPlans()->where('implementation_status', '!=', 'Completed')->count();
                    if ($incompletePlans > 0) {
                        $requirements[] = [
                            'type' => 'info',
                            'message' => $incompletePlans . ' treatment plan(s) are not yet completed.',
                            'items' => [],
                        ];
                    }
                }
                
                if ($currentStep >= 6 && (!$risk->reviews || $risk->reviews->count() === 0)) {
                    $requirements[] = [
                        'type' => 'info',
                        'message' => 'At least one risk review is recommended before closing.',
                        'items' => [],
                    ];
                }

                $workflowBlockingReasons = $risk->getWorkflowBlockingReasons();
                foreach ($workflowBlockingReasons as $blockingReason) {
                    $requirements[] = [
                        'type' => 'warning',
                        'message' => $blockingReason,
                        'items' => [],
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
                        @if(!empty($risk->description))
                        <div class="col-lg-6 col-xl-6 mb-4">
                            <div class="detail-section" style="height: 100%; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-file-document-outline"></i> {{ __('Description') }}
                                </h6>
                                <div class="detail-text-content" style="flex: 1;">
                                        {!! $risk->description !!}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($risk->sample_id || $risk->equipment_id || $risk->method_id || $risk->personnel_id)
                        <div class="col-lg-6 col-xl-6 mb-4">
                            <div class="detail-section" style="height: 100%; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-link-variant"></i> {{ __('Linked Items') }}
                                </h6>
                                <div class="detail-text-content" style="flex: 1;">
                                    <ul class="list-unstyled mb-0" style="padding-left: 0;">
                                        @if($risk->sample_id)
                                        <li class="mb-3 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-flask-outline mr-3 mt-1" style="font-size: 1rem; color: var(--primary-color);"></i>
                                                <div style="flex: 1;">
                                                    <strong style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Sample') }}</strong>
                                                    <div style="font-size: 0.8125rem; color: #1e293b; margin-top: 0.25rem;">
                                                        @if($risk->sample)
                                                            @php
                                                                $sampleRoute = null;
                                                                try {
                                                                    $sampleRoute = route('sample.show', $risk->sample_id);
                                                                } catch (\Exception $e) {
                                                                    // Route doesn't exist, just display text
                                                                }
                                                            @endphp
                                                            @if($sampleRoute)
                                                                <a href="{{ $sampleRoute }}" target="_blank" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                                    {{ $risk->sample->batch_code ?? $risk->sample_reference }}
                                                                    @if($risk->sample->reference_number) - {{ $risk->sample->reference_number }}@endif
                                                                </a>
                                                            @else
                                                                {{ $risk->sample->batch_code ?? $risk->sample_reference }}
                                                                @if($risk->sample->reference_number) - {{ $risk->sample->reference_number }}@endif
                                                            @endif
                                                        @else
                                                            {{ $risk->sample_reference ?? __('N/A') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endif
                                        
                                        @if($risk->equipment_id)
                                        <li class="mb-3 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-cog mr-3 mt-1" style="font-size: 1rem; color: var(--primary-color);"></i>
                                                <div style="flex: 1;">
                                                    <strong style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Equipment') }}</strong>
                                                    <div style="font-size: 0.8125rem; color: #1e293b; margin-top: 0.25rem;">
                                                        @if($risk->equipment)
                                                            @php
                                                                $equipmentRoute = null;
                                                                try {
                                                                    $equipmentRoute = route('equipment.show', $risk->equipment_id);
                                                                } catch (\Exception $e) {
                                                                    // Route doesn't exist, just display text
                                                                }
                                                            @endphp
                                                            @if($equipmentRoute)
                                                                <a href="{{ $equipmentRoute }}" target="_blank" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                                    {{ $risk->equipment->name }}
                                                                </a>
                                                            @else
                                                                {{ $risk->equipment->name }}
                                                            @endif
                                                        @else
                                                            {{ __('Equipment ID') }}: {{ $risk->equipment_id }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endif
                                        
                                        @if($risk->method_id)
                                        <li class="mb-3 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-flask mr-3 mt-1" style="font-size: 1rem; color: var(--primary-color);"></i>
                                                <div style="flex: 1;">
                                                    <strong style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Method') }}</strong>
                                                    <div style="font-size: 0.8125rem; color: #1e293b; margin-top: 0.25rem;">
                                                        @if($risk->method)
                                                            {{ $risk->method->name }}
                                                            @if($risk->method->code) <span class="text-muted">({{ $risk->method->code }})</span>@endif
                                                        @else
                                                            {{ $risk->method_reference ?? __('N/A') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endif
                                        
                                        @if($risk->personnel_id)
                                        <li class="mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="mdi mdi-account mr-3 mt-1" style="font-size: 1rem; color: var(--primary-color);"></i>
                                                <div style="flex: 1;">
                                                    <strong style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Personnel') }}</strong>
                                                    <div style="font-size: 0.8125rem; color: #1e293b; margin-top: 0.25rem;">
                                                        @if($risk->personnel)
                                                            {{ $risk->personnel->name }}
                                                        @else
                                                            {{ __('User ID') }}: {{ $risk->personnel_id }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endif


                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                            
                        @if(!empty($risk->assessment_notes))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-clipboard-check"></i> {{ __('Assessment Notes') }}
                                </h6>
                                <div class="detail-text-content">
                                        {!! $risk->assessment_notes !!}
                                    @if($risk->assessment_date)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="mdi mdi-calendar"></i> Assessed on {{ $risk->assessment_date->format('M d, Y') }}
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                            
                        @if(!empty($risk->evaluation_notes))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-scale-balance"></i> {{ __('Evaluation Notes') }}
                                </h6>
                                <div class="detail-text-content">
                                        {!! $risk->evaluation_notes !!}
                                    @if($risk->evaluation_date)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="mdi mdi-calendar"></i> Evaluated on {{ $risk->evaluation_date->format('M d, Y') }}
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(!empty($risk->initial_comments))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-comment-text-outline"></i> {{ __('Initial Comments') }}
                                </h6>
                                <div class="detail-text-content">
                                    {!! $risk->initial_comments !!}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                                    @if(!empty($risk->treatment_justification))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-clipboard-list"></i> {{ __('Treatment Justification') }}
                                </h6>
                                <div class="detail-text-content">
                                        {!! $risk->treatment_justification !!}
                                </div>
                            </div>
                        </div>
                        @endif
                            
                        @if(!empty($risk->lessons_learned))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-school-outline"></i> {{ __('Lessons Learned') }}
                                </h6>
                                <div class="detail-text-content">
                                    {!! $risk->lessons_learned !!}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(!empty($risk->closure_justification) || ($currentStep >= 7 && !$isClosed))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="detail-section-title mb-0">
                                        <i class="mdi mdi-check-circle"></i> {{ __('Closure Justification') }}
                                    </h6>
                                    @if($currentStep >= 7 && !$isClosed)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editClosureJustificationModal" title="{{ __('Add/Edit Closure Justification') }}">
                                        <i class="mdi mdi-{{ !empty($risk->closure_justification) ? 'pencil' : 'plus' }}"></i>
                                    </button>
                                    @endif
                                    @endif
                                </div>
                                <div class="detail-text-content">
                                    @if(!empty($risk->closure_justification))
                                        {!! $risk->closure_justification !!}
                                        @if($risk->closure_date)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="mdi mdi-calendar"></i> Closed on {{ $risk->closure_date->format('M d, Y') }}
                                            </small>
                                        </div>
                                        @endif
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="mdi mdi-information-outline"></i> 
                                            @if($risk->residual_rpn && $risk->residual_rpn > ($risk->acceptance_threshold_rpn ?? 15))
                                                Closure justification is required because residual RPN ({{ $risk->residual_rpn }}) exceeds the acceptance threshold ({{ $risk->acceptance_threshold_rpn ?? 15 }}).
                                            @else
                                                No closure justification provided yet.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                            
                        @if($risk->next_review_date || $risk->last_review_date)
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-calendar-clock"></i> {{ __('Review Schedule') }}
                                </h6>
                                <div class="detail-text-content">
                                    @if($risk->next_review_date)
                                        <p>
                                            <strong>Next Review:</strong> 
                                            <span class="badge badge-info">{{ $risk->next_review_date->format('M d, Y') }}</span>
                                        </p>
                                    @endif
                                    @if($risk->last_review_date)
                                        <p>
                                            <strong>Last Review:</strong> 
                                            {{ $risk->last_review_date->format('M d, Y') }}
                                        </p>
                                    @endif
                                    @if($risk->review_frequency_days)
                                        <p>
                                            <strong>Frequency:</strong> 
                                            Every {{ $risk->review_frequency_days }} day(s)
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(!empty($risk->acceptance_criteria))
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-check-all"></i> {{ __('Acceptance Criteria') }}
                                </h6>
                                <div class="detail-text-content">
                                    {!! $risk->acceptance_criteria !!}
                    </div>
                </div>
                                </div>
                        @endif
                        
                        @if($risk->audit || $risk->auditFinding || $risk->nonConformance || $risk->complaint_id)
                        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-link-variant"></i> {{ __('Related Entities') }}
                                </h6>
                                <div class="detail-text-content">
                                    @if($risk->audit)
                                    <p class="mb-2">
                                        <strong>Audit:</strong> 
                                        <a href="{{ route('audit.audits.show', $risk->audit->id) }}" target="_blank" class="text-primary">
                                            {{ $risk->audit->audit_number ?? $risk->audit->id }}
                                        </a>
                                    </p>
                                    @endif
                                    @if($risk->auditFinding)
                                    <p class="mb-2">
                                        <strong>Finding:</strong> 
                                        <a href="{{ route('audit.audits.show', $risk->auditFinding->audit_id) }}#findings" target="_blank" class="text-primary">
                                            {{ $risk->auditFinding->finding_number ?? 'View Finding' }}
                                        </a>
                                    </p>
                                    @endif
                                    @if($risk->nonConformance)
                                    <p class="mb-2">
                                        <strong>Non-Conformance:</strong> 
                                        <a href="{{ route('audit.non-conformances.show', $risk->nonConformance->id) }}" target="_blank" class="text-primary">
                                            {{ $risk->nonConformance->nc_number ?? 'View NC' }}
                                        </a>
                                    </p>
                                    @endif
                                    @if($risk->complaint_id)
                                    <p class="mb-2">
                                        <strong>Complaint:</strong> 
                                        <span class="text-muted">ID #{{ $risk->complaint_id }}</span>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                                </div>
                    
                    @if(empty($risk->description) && empty($risk->assessment_notes) && empty($risk->evaluation_notes) && empty($risk->treatment_justification) && empty($risk->closure_justification) && !$risk->next_review_date && !$risk->last_review_date && !$risk->audit && !$risk->auditFinding && !$risk->nonConformance && !$risk->complaint_id && empty($risk->acceptance_criteria))
                    <div class="alert alert-info text-center">
                        <i class="mdi mdi-information-outline"></i> 
                        {{ __('No additional details available for this risk.') }}
                                </div>
                    @endif
                            </div>

                <!-- Processes Tab -->
                <div class="tab-pane fade" id="processes" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-link-variant text-primary"></i> {{ __('Linked Business Processes') }}
                        </h6>
                        @if($currentStep == 2 && !$isClosed && auth()->user()->can('risk-management.components.risks.edit'))
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addProcessLinkModal">
                            <i class="mdi mdi-plus"></i> {{ __('Link Process') }}
                        </button>
                        @endif
                    </div>

                    @if($risk->processLinks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Process Name') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Linked By') }}</th>
                                        <th>{{ __('Date Linked') }}</th>
                                        @if($currentStep == 2 && !$isClosed && auth()->user()->can('risk-management.components.risks.edit'))
                                        <th class="text-right">{{ __('Actions') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($risk->processLinks as $link)
                                        <tr>
                                            <td class="font-weight-bold">{{ $link->businessProcess->name }}</td>
                                            <td>{{ $link->description ?? '-' }}</td>
                                            <td>{{ $link->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $link->created_at->format('M d, Y') }}</td>
                                            @if($currentStep == 2 && !$isClosed && auth()->user()->can('risk-management.components.risks.edit'))
                                            <td class="text-right">
                                                <button type="button" class="btn btn-sm btn-outline-warning mr-1 edit-process-link-btn" 
                                                        data-id="{{ $link->id }}" 
                                                        data-process="{{ $link->business_process_id }}" 
                                                        data-description="{{ $link->description }}"
                                                        title="{{ __('Edit Link') }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <form action="{{ route('risk.risks.process-links.destroy', $link->id) }}" method="POST" class="d-inline process-link-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-process-link-btn" title="Remove Link">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center" style="border-radius: var(--border-radius-sm);">
                            <i class="mdi mdi-information-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p style="margin: 1rem 0 0 0; color: #64748b; font-size: 0.9375rem;">{{ __('No business processes linked to this risk yet.') }}</p>
                        </div>
                    @endif
                </div>
                <!-- Assessment Tab -->
                @if($hasAssessment || (int) $currentStep >= 2)
                <div class="tab-pane fade" id="assessment" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-clipboard-check text-primary"></i> {{ __('Risk Assessment') }}
                        </h6>
                        @if($canEditAssessment)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#assessmentModal">
                            <i class="mdi mdi-{{ $hasAssessment ? 'pencil' : 'plus' }}"></i>
                            {{ $hasAssessment ? __('Edit Assessment') : __('Add Assessment') }}
                        </button>
                        @endif
                        </div>
                    
                    @if($hasAssessment)
                    @php
                        $calculatedRpn = $likelihoodScore * $severityScore;
                        $displayRpn = $risk->rpn ?? $calculatedRpn;
                    @endphp
                    <div class="row">
                        <!-- Likelihood Card -->
                        <div class="col-md-3 col-lg-3 mb-3">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.875rem;">
                                    <i class="mdi mdi-chart-line" style="font-size: 1rem;"></i> {{ __('Likelihood') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Scale') }}
                                        </div>
                                        <div style="font-size: 0.8125rem; color: #334155; font-weight: 500; margin-bottom: 0.375rem;">
                                            {{ $risk->likelihoodScale->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div style="padding-top: 0.375rem; border-top: 1px solid #e5e7eb; margin-top: 0.375rem;">
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Score') }}
                                        </div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--info-color);">
                                            {{ $likelihoodScore }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Severity Card -->
                        <div class="col-md-3 col-lg-3 mb-3">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.875rem;">
                                    <i class="mdi mdi-alert-circle" style="font-size: 1rem;"></i> {{ __('Severity') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Scale') }}
                                        </div>
                                        <div style="font-size: 0.8125rem; color: #334155; font-weight: 500; margin-bottom: 0.375rem;">
                                            {{ $risk->severityScale->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div style="padding-top: 0.375rem; border-top: 1px solid #e5e7eb; margin-top: 0.375rem;">
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Score') }}
                                        </div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning-color);">
                                            {{ $severityScore }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- RPN Calculation Card -->
                        <div class="col-md-3 col-lg-3 mb-3">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                <h6 class="detail-section-title" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.875rem;">
                                    <i class="mdi mdi-calculator" style="font-size: 1rem;"></i> {{ __('RPN') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Calculation') }}
                                        </div>
                                        <div style="font-size: 0.875rem; color: #334155; font-weight: 500; line-height: 1.4;">
                                            {{ $likelihoodScore }}×{{ $severityScore }}={{ $calculatedRpn }}
                                        </div>
                                    </div>
                                    <div style="padding-top: 0.375rem; border-top: 1px solid #cbd5e1; margin-top: 0.375rem;">
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('RPN') }}
                                        </div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">
                                            {{ $displayRpn }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Risk Level Card -->
                        <div class="col-md-3 col-lg-3 mb-3">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.875rem;">
                                    <i class="mdi mdi-alert-octagon" style="font-size: 1rem;"></i> {{ __('Risk Level') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <span class="badge badge-{{ $risk->risk_level === 'Critical' ? 'danger' : ($risk->risk_level === 'High' ? 'warning' : ($risk->risk_level === 'Medium' ? 'info' : 'secondary')) }}" style="font-size: 0.875rem; padding: 0.5rem 0.75rem; font-weight: 600;">
                                                    {{ $risk->risk_level ?? 'N/A' }}
                                                </span>
                                    </div>
                                        @if($risk->assessment_date)
                                    <div style="padding-top: 0.375rem; border-top: 1px solid #e5e7eb; margin-top: 0.375rem;">
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Assessment Date') }}
                                        </div>
                                        <div style="font-size: 0.8125rem; color: #334155; font-weight: 500;">
                                            {{ $risk->assessment_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                    @if($risk->currentAssessment)
                                    <div style="padding-top: 0.375rem; border-top: 1px solid #e5e7eb; margin-top: 0.375rem;">
                                        <div style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;">
                                            {{ __('Assessed By') }}
                                        </div>
                                        <div style="font-size: 0.8125rem; color: #334155; font-weight: 500;">
                                            @php
                                                $assessorDisplay = $risk->currentAssessment->assessed_by ?? ($risk->currentAssessment->assessedByUser->name ?? 'N/A');
                                                $notes = $risk->currentAssessment->assessment_notes ?? $risk->assessment_notes ?? '';
                                                if (\Illuminate\Support\Str::contains($notes, '**Assessment Team:**')) {
                                                    $assessorDisplay = trim(\Illuminate\Support\Str::after($notes, '**Assessment Team:**'));
                                                }
                                            @endphp
                                            {{ $assessorDisplay }}
                                        </div>
                                    </div>
                                    @endif
                                            @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                                        @if($risk->assessment_notes)
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-note-text-outline"></i> {{ __('Assessment Notes') }}
                                </h6>
                                <div class="detail-text-content">
                                                {!! $risk->assessment_notes !!}
                                        </div>
                                    </div>
                                </div>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-info text-center" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-information-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p style="margin: 1rem 0 0 0; color: #64748b; font-size: 0.9375rem;">
                            {{ __('No assessment recorded yet.') }}
                            @if(!empty($canEditAssessment))
                                {{ __('Click "Add Assessment" to complete the risk assessment.') }}
                            @else
                                {{ __('Complete the assessment when this risk reaches the assessment workflow step.') }}
                            @endif
                        </p>
                            </div>
                    @endif
                </div>
                @endif

                <!-- Evaluation Tab -->
                @if($risk->evaluation_result || $currentStep >= 4)
                <div class="tab-pane fade" id="evaluation" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-scale-balance text-primary"></i> {{ __('Risk Evaluation') }}
                        </h6>
                        @if($canEditEvaluation)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#evaluationModal">
                            <i class="mdi mdi-{{ $hasEvaluation ? 'pencil' : 'plus' }}"></i> 
                            {{ $hasEvaluation ? __('Edit Evaluation') : __('Add Evaluation') }}
                        </button>
                        @endif
                        </div>
                    
                    @if($risk->evaluation_result)
                    @php
                        $currentEvaluation = $risk->currentEvaluation;
                        $evaluationRpn = $currentEvaluation ? $currentEvaluation->risk_score : null;
                        $evaluationResultCode = $risk->evaluation_result;
                        $rpnRange = getEvaluationResultRpnRange($evaluationResultCode);
                        
                        // Determine colors based on evaluation result
                        $resultLower = strtolower($evaluationResultCode);
                        if ($resultLower === 'unacceptable') {
                            $cardBgColor = '#fef2f2'; // Light red
                            $cardBorderColor = '#ef4444'; // Red
                            $badgeClass = 'danger';
                            $iconColor = '#dc2626';
                        } elseif ($resultLower === 'tolerable') {
                            $cardBgColor = '#fffbeb'; // Light yellow
                            $cardBorderColor = '#f59e0b'; // Yellow/amber
                            $badgeClass = 'warning';
                            $iconColor = '#d97706';
                        } else {
                            $cardBgColor = '#f0fdf4'; // Light green
                            $cardBorderColor = '#22c55e'; // Green
                            $badgeClass = 'success';
                            $iconColor = '#16a34a';
                        }
                    @endphp
                    <div class="row">
                        <!-- Evaluation Result Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column; background: {{ $cardBgColor }}; border: 2px solid {{ $cardBorderColor }}; border-left-width: 5px;">
                                <h6 class="detail-section-title" style="justify-content: center; color: {{ $iconColor }};">
                                    <i class="mdi mdi-check-circle" style="color: {{ $iconColor }};"></i> {{ __('Evaluation Result') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; align-items: center; justify-content: center; background: transparent; border: none;">
                                    <span class="badge badge-{{ $badgeClass }}" style="font-size: 1.25rem; padding: 0.75rem 1.25rem; font-weight: 700;">
                                        {{ ucfirst($risk->evaluation_result) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- RPN Value Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center;">
                                    <i class="mdi mdi-numeric"></i> {{ __('RPN Value') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column; justify-content: center;">
                                    @if($evaluationRpn !== null)
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #334155;">
                                            {{ $evaluationRpn }}
                                        </div>
                                        @if($rpnRange)
                                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                                                {{ __('Range:') }} {{ $rpnRange['min'] }} - {{ $rpnRange['max'] }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evaluation Date Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center;">
                                    <i class="mdi mdi-calendar"></i> {{ __('Evaluation Date') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; align-items: center; justify-content: center;">
                                    @if($risk->evaluation_date)
                                        <div style="font-size: 0.9375rem; color: #334155; font-weight: 500;">
                                            {{ $risk->evaluation_date->format('M d, Y') }}
                                        </div>
                                        @else
                                        <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evaluated By Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center;">
                                    <i class="mdi mdi-account"></i> {{ __('Evaluated By') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; align-items: center; justify-content: center;">
                                    @if($currentEvaluation && $currentEvaluation->evaluatedByUser)
                                        <div style="font-size: 0.9375rem; color: #334155; font-weight: 500;">
                                            {{ $currentEvaluation->evaluatedByUser->name }}
                                        </div>
                                    @elseif($currentEvaluation && $currentEvaluation->evaluated_by)
                                        <div style="font-size: 0.9375rem; color: #334155; font-weight: 500;">
                                            {{ $currentEvaluation->evaluated_by }}
                                        </div>
                                        @else
                                        <span style="color: #94a3b8;">{{ __('N/A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Requires Treatment Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center;">
                                    <i class="mdi mdi-clipboard-list"></i> {{ __('Requires Treatment') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; align-items: center; justify-content: center;">
                                        @if($risk->requires_treatment)
                                        <span class="badge badge-warning" style="font-size: 1rem; padding: 0.5rem 1rem;">{{ __('Yes') }}</span>
                                        @else
                                        <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">{{ __('No') }}</span>
                                        @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($currentEvaluation && $currentEvaluation->evaluation_number)
                        <!-- Evaluation Number Card -->
                        <div class="col-md-3 col-lg-3 mb-4">
                            <div class="detail-section" style="height: 100%; text-align: center; display: flex; flex-direction: column;">
                                <h6 class="detail-section-title" style="justify-content: center;">
                                    <i class="mdi mdi-identifier"></i> {{ __('Evaluation Number') }}
                                </h6>
                                <div class="detail-text-content" style="padding: 1.5rem; flex: 1; display: flex; align-items: center; justify-content: center;">
                                    <div style="font-size: 0.9375rem; color: #334155; font-weight: 500;">
                                        {{ $currentEvaluation->evaluation_number }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                                        @if($risk->evaluation_notes)
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="detail-section">
                                <h6 class="detail-section-title">
                                    <i class="mdi mdi-note-text-outline"></i> {{ __('Evaluation Notes') }}
                                </h6>
                                <div class="detail-text-content">
                                                {!! $risk->evaluation_notes !!}
                                </div>
                            </div>
                    </div>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-info text-center" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-information-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p style="margin: 1rem 0 0 0; color: #64748b; font-size: 0.9375rem;">
                            {{ __('No evaluation recorded yet.') }}
                            @if(!empty($canEditEvaluation))
                                {{ __('Click "Add Evaluation" to complete the risk evaluation.') }}
                            @else
                                {{ __('Complete the evaluation when this risk reaches the evaluation workflow step.') }}
                            @endif
                        </p>
                            </div>
                            @endif
                </div>
                @endif

                <!-- Treatment Plans Tab -->
                @if(($risk->treatmentPlans && $risk->treatmentPlans->count() > 0) || $currentStep >= 5)
                <div class="tab-pane fade" id="treatment-plans" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-clipboard-list text-primary"></i> {{ __('Treatment Plans') }}
                        </h6>
                        @if(!$isClosed && $currentStep == 5)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addTreatmentPlanModal">
                            <i class="mdi mdi-plus"></i> {{ __('Add Treatment Plan') }}
                        </button>
                        @endif
                        @endif
                    </div>
                    
                    @if($risk->treatmentPlans && $risk->treatmentPlans->count() > 0)
                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern mb-0" id="treatmentPlansTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th style="width: 130px; text-align: center;">{{ __('Actions') }}</th>
                                    <th style="min-width: 180px;">{{ __('Treatment Plan') }}</th>
                                    <th style="min-width: 120px;">{{ __('Type') }}</th>
                                    <th style="min-width: 90px;">{{ __('Priority') }}</th>
                                    <th style="min-width: 180px;">{{ __('Description') }}</th>
                                    <th style="min-width: 100px;">{{ __('Status') }}</th>
                                    <th style="min-width: 110px;">{{ __('Target Date') }}</th>
                                    <th style="min-width: 120px;">{{ __('Responsible') }}</th>
                                    <th style="min-width: 70px; text-align: center;">{{ __('RPN') }}</th>
                                    <th style="min-width: 100px;">{{ __('Start Date') }}</th>
                                    <th style="min-width: 100px;">{{ __('End Date') }}</th>
                                    <th style="min-width: 110px;">{{ __('Completed') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($risk->treatmentPlans as $index => $plan)
                                @php
                                    $statusLower = strtolower($plan->implementation_status ?? 'planned');
                                    $statusClass = match($statusLower) {
                                        'completed' => 'success',
                                        'in progress', 'in_progress' => 'primary',
                                        'on hold', 'on_hold' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    $priorityLower = strtolower($plan->priority ?? '');
                                    $priorityClass = match($priorityLower) {
                                        'critical' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <!-- Parent Row - Treatment Plan -->
                                <tr class="treatment-plan-row" data-plan-id="{{ $plan->id }}" style="cursor: pointer;">
                                    <td class="toggle-cell" style="text-align: center; vertical-align: middle;">
                                        <i class="mdi mdi-chevron-down toggle-icon" style="font-size: 1.25rem; color: #64748b; transition: transform 0.2s;"></i>
                                    </td>
                                    <td class="actions-cell" style="text-align: center; vertical-align: middle;">
                                        <div class="btn-group btn-group-sm">
                                            {{-- View button - Always visible with View permission --}}
                                            @if(auth()->user()->can('risk-management.components.risks.view') || auth()->user()->can('risk-management.components.risks.edit'))
                                            <button type="button" class="btn btn-outline-info btn-action" data-toggle="modal" data-target="#viewTreatmentPlanModal{{ $plan->id }}" title="{{ __('View Details') }}">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            @endif
                                            
                                            {{-- Edit Plan button - Only in Treatment Planning step (5) with Edit permission --}}
                                            @if(!$isClosed && $currentStep == 5)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                                            <button type="button" class="btn btn-outline-primary btn-action" data-toggle="modal" data-target="#updateTreatmentPlanModal{{ $plan->id }}" title="{{ __('Edit Plan') }}">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            @endif
                                            @endif
                                            
                                            {{-- Record Implementation button - Only in Implementation step (6) with Edit permission --}}
                                            @if(!$isClosed && $currentStep == 6)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                                            <button type="button" class="btn btn-outline-success btn-action" data-toggle="modal" data-target="#recordImplementationModal{{ $plan->id }}" title="{{ __('Record Implementation') }}">
                                                <i class="mdi mdi-progress-check"></i>
                                            </button>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <strong style="color: #1e293b; font-size: 0.9375rem;">{{ $plan->title }}</strong>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="color: #475569; font-size: 0.875rem;">{{ $plan->treatmentType->name ?? $plan->treatment_type_name ?? 'N/A' }}</span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($plan->priority)
                                        <span class="badge badge-{{ $priorityClass }}" style="font-size: 0.75rem;">{{ ucfirst($plan->priority) }}</span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle; max-width: 200px;">
                                        @if($plan->description)
                                        <div style="font-size: 0.8125rem; color: #475569; line-height: 1.4;">
                                            {!! \Str::limit(strip_tags($plan->description), 80) !!}
                                        </div>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span class="badge badge-{{ $statusClass }}" style="font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                                            {{ $plan->implementation_status ?? 'Planned' }}
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($plan->target_completion_date)
                                        <span style="color: #475569; font-size: 0.875rem;">{{ $plan->target_completion_date->format('M d, Y') }}</span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="color: #475569; font-size: 0.875rem;">{{ $plan->responsible_person ?? '-' }}</span>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        @if($plan->residual_risk_expected)
                                        <span class="badge badge-{{ $plan->residual_risk_expected >= 15 ? 'danger' : ($plan->residual_risk_expected >= 9 ? 'warning' : 'success') }}" style="font-size: 0.875rem; min-width: 35px;">
                                            {{ $plan->residual_risk_expected }}
                                        </span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($plan->implementation_start_date)
                                        <span style="color: #475569; font-size: 0.8125rem;">{{ $plan->implementation_start_date->format('M d, Y') }}</span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($plan->implementation_end_date)
                                        <span style="color: #475569; font-size: 0.8125rem;">{{ $plan->implementation_end_date->format('M d, Y') }}</span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        @if($plan->actual_completion_date)
                                        <span style="color: #475569; font-size: 0.8125rem;">{{ $plan->actual_completion_date->format('M d, Y') }}</span>
                                        @else
                                        <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Child Row - Implementation Details (Expandable) -->
                                <tr id="planDetails{{ $plan->id }}" class="collapse">
                                    <td colspan="13" style="padding: 0; background: #f8fafc; border-top: none;">
                                        <div style="padding: 1.25rem 1.5rem; border-left: 4px solid #3b82f6;">
                                            <div class="row">
                                                <!-- Column 1: Description & Control Measures (Card Style) -->
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 8px; height: 100%;">
                                                        <div class="card-header bg-white py-2" style="border-bottom: 1px solid #f1f5f9;">
                                                            <strong style="font-size: 0.8rem; color: #475569;"><i class="mdi mdi-text-box-outline text-primary mr-1"></i> {{ __('Plan Details') }}</strong>
                                                        </div>
                                                        <div class="card-body p-3">
                                                            <div class="mb-4">
                                                                <strong class="text-muted d-block mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ __('Description') }}</strong>
                                                                <div style="font-size: 0.9rem; color: #334155; line-height: 1.6; max-height: 150px; overflow-y: auto;">
                                                                    {!! $plan->description ? strip_tags($plan->description) : '<span class="text-muted">No description provided</span>' !!}
                                                                </div>
                                                            </div>
                                                            
                                                            @if($plan->control_measures)
                                                            <div class="pt-3 border-top">
                                                                <strong class="text-muted d-block mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ __('Control Measures') }}</strong>
                                                                <div style="font-size: 0.9rem; color: #334155; line-height: 1.6; max-height: 100px; overflow-y: auto;">
                                                                    {!! strip_tags($plan->control_measures) !!}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Column 2: Implementation Progress & Attachments -->
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 8px; height: 100%;">
                                                        <div class="card-header bg-white py-2" style="border-bottom: 1px solid #f1f5f9;">
                                                            <strong style="font-size: 0.8rem; color: #475569;"><i class="mdi mdi-progress-clock text-primary mr-1"></i> {{ __('Implementation & Evidence') }}</strong>
                                                        </div>
                                                        <div class="card-body p-3">
                                                            <div class="row mb-3">
                                                                <div class="col-4">
                                                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Start</small>
                                                                    <strong style="font-size: 0.9rem;">{{ $plan->implementation_start_date ? $plan->implementation_start_date->format('d M Y') : '-' }}</strong>
                                                                </div>
                                                                <div class="col-4 text-center">
                                                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">End</small>
                                                                    <strong style="font-size: 0.9rem;">{{ $plan->implementation_end_date ? $plan->implementation_end_date->format('d M Y') : '-' }}</strong>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Completed</small>
                                                                    <span class="badge badge-{{ $plan->actual_completion_date ? 'success' : 'light' }}" style="font-size: 0.8rem;">
                                                                        {{ $plan->actual_completion_date ? $plan->actual_completion_date->format('d M Y') : 'Pending' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            
                                                            @if($plan->implementation_notes)
                                                            <div class="mb-3 pt-2 border-top">
                                                                <small class="text-muted d-block mb-1 text-uppercase" style="font-size: 0.7rem;">Notes</small>
                                                                <div style="font-size: 0.85rem; color: #64748b; max-height: 80px; overflow-y: auto;">
                                                                    {!! \Str::limit(strip_tags($plan->implementation_notes), 150) !!}
                                                                </div>
                                                            </div>
                                                            @endif
                                                            
                                                            <!-- Nested Evidence Section -->
                                                            <div class="mt-auto pt-3 border-top">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b;"><i class="mdi mdi-paperclip mr-1"></i> {{ __('Attachments') }}</strong>
                                                                    @if($plan->attachments && $plan->attachments->count() > 0)
                                                                    <span class="badge badge-pill badge-light" style="font-size: 0.7rem;">{{ $plan->attachments->count() }}</span>
                                                                    @endif
                                                                </div>
                                                                
                                                                @if($plan->attachments && $plan->attachments->count() > 0)
                                                                <div class="list-group list-group-flush" style="max-height: 120px; overflow-y: auto;">
                                                                    @foreach($plan->attachments as $attachment)
                                                                    <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex justify-content-between align-items-center">
                                                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $attachment->original_name }}">
                                                                            <i class="mdi mdi-file-outline text-muted mr-1" style="font-size: 0.85rem;"></i>
                                                                            <span style="font-size: 0.8rem; color: #334155;">{{ $attachment->original_name }}</span>
                                                                        </div>
                                                                        <div class="btn-group">
                                                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1 mr-1" title="View"><i class="mdi mdi-eye"></i></a>
                                                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" download class="btn btn-sm btn-outline-secondary py-0 px-1" title="Download"><i class="mdi mdi-download"></i></a>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                                @else
                                                                <div class="text-muted small py-2">
                                                                    <i class="mdi mdi-file-hidden mr-1"></i> {{ __('No files attached') }}
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Record Implementation Modals -->
                    @foreach($risk->treatmentPlans as $plan)
                    <div class="modal fade" id="recordImplementationModal{{ $plan->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <form action="{{ route('risk.risks.treatment-plan.update', ['riskId' => $risk->id, 'treatmentPlanId' => $plan->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <!-- Hidden fields to preserve existing data -->
                                    <input type="hidden" name="treatment_type_id" value="{{ $plan->treatment_type_id }}">
                                    <input type="hidden" name="title" value="{{ $plan->title }}">
                                    <input type="hidden" name="description" value="{{ $plan->description }}">
                                    <input type="hidden" name="priority" value="{{ $plan->priority }}">
                                    <input type="hidden" name="responsible_user_id" value="{{ $plan->responsible_user_id }}">
                                    <input type="hidden" name="target_completion_date" value="{{ $plan->target_completion_date ? $plan->target_completion_date->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="residual_risk_expected" value="{{ $plan->residual_risk_expected }}">
                                    <input type="hidden" name="control_measures" value="{{ $plan->control_measures }}">
                                    <input type="hidden" name="expected_outcome" value="{{ $plan->expected_outcome }}">
                                    <input type="hidden" name="resources_required" value="{{ $plan->resources_required }}">
                                    
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">
                                            <i class="mdi mdi-progress-check"></i> {{ __('Record Implementation') }}: {{ $plan->title }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-600">{{ __('Implementation Status') }} <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="implementation_status" required style="border-radius: var(--border-radius-sm);">
                                                        @foreach(getImplementationStatuses() as $status)
                                                        <option value="{{ $status->code }}" {{ ($plan->implementation_status == $status->code || $plan->implementation_status == $status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-600">{{ __('Actual Completion Date') }}</label>
                                                    <input type="date" class="form-control" name="actual_completion_date" value="{{ $plan->actual_completion_date ? $plan->actual_completion_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-600">{{ __('Start Date') }}</label>
                                                    <input type="date" class="form-control" name="implementation_start_date" value="{{ $plan->implementation_start_date ? $plan->implementation_start_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-600">{{ __('End Date') }}</label>
                                                    <input type="date" class="form-control" name="implementation_end_date" value="{{ $plan->implementation_end_date ? $plan->implementation_end_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-600">{{ __('Implementation Notes') }}</label>
                                            <textarea class="form-control" name="implementation_notes" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Add notes about the implementation progress...') }}">{{ $plan->implementation_notes }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-600">
                                                <i class="mdi mdi-paperclip"></i> {{ __('Evidence Attachments') }}
                                            </label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="impl_attachments{{ $plan->id }}" name="implementation_attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.png,.jpg,.jpeg,.gif,.webp">
                                                <label class="custom-file-label" for="impl_attachments{{ $plan->id }}">{{ __('Choose files...') }}</label>
                                            </div>
                                            <small class="form-text text-muted">{{ __('Upload multiple files as evidence. Accepted: PDF, Word, Excel, PowerPoint, Text, Zip, Images.') }}</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-success" style="border-radius: var(--border-radius-sm);">
                                            <i class="mdi mdi-content-save"></i> {{ __('Save Implementation') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="alert alert-info text-center" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-information-outline" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p style="margin: 1rem 0 0 0; color: #64748b; font-size: 0.9375rem;">{{ __('No treatment plans have been created yet. Click "Add Treatment Plan" to create one.') }}</p>
                    </div>
                    @endif
                    
                    <!-- Update Treatment Plan Modals -->
                    @if($risk->treatmentPlans && $risk->treatmentPlans->count() > 0)
                        @foreach($risk->treatmentPlans as $plan)
                        <div class="modal fade" id="updateTreatmentPlanModal{{ $plan->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('risk.risks.treatment-plan.update', ['riskId' => $risk->id, 'treatmentPlanId' => $plan->id]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="mdi mdi-pencil"></i> {{ __('Edit Treatment Plan') }}: {{ $plan->title }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="treatment_type_id{{ $plan->id }}" class="font-weight-600">{{ __('Treatment Type') }} <span class="text-danger">*</span></label>
                                                        <select class="form-control @error('treatment_type_id') is-invalid @enderror" id="treatment_type_id{{ $plan->id }}" name="treatment_type_id" required style="border-radius: var(--border-radius-sm);">
                                                            <option value="">{{ __('Select Treatment Type...') }}</option>
                                                            @foreach($treatmentTypes as $type)
                                                            <option value="{{ $type->id }}" {{ $plan->treatment_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('treatment_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="priority{{ $plan->id }}" class="font-weight-600">{{ __('Priority') }}</label>
                                                        <select class="form-control @error('priority') is-invalid @enderror" id="priority{{ $plan->id }}" name="priority" style="border-radius: var(--border-radius-sm);">
                                                            <option value="">{{ __('Select Priority...') }}</option>
                                                            @foreach(getTreatmentPriorities() as $priority)
                                                            <option value="{{ $priority->code }}" {{ $plan->priority === $priority->code ? 'selected' : '' }}>{{ $priority->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="title{{ $plan->id }}" class="font-weight-600">{{ __('Title') }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title{{ $plan->id }}" name="title" required maxlength="255" value="{{ $plan->title }}" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter treatment plan title...') }}">
                                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="description{{ $plan->id }}" class="font-weight-600">{{ __('Description') }} <span class="text-danger">*</span></label>
                                                <textarea class="form-control editor @error('description') is-invalid @enderror" id="description{{ $plan->id }}" name="description" rows="4" required style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter detailed description of the treatment plan...') }}">{{ $plan->description ?? '' }}</textarea>
                                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="responsible_user_id{{ $plan->id }}" class="font-weight-600">{{ __('Responsible Person') }}</label>
                                                        <select class="form-control @error('responsible_user_id') is-invalid @enderror" id="responsible_user_id{{ $plan->id }}" name="responsible_user_id" style="border-radius: var(--border-radius-sm);">
                                                            <option value="">{{ __('Select Responsible Person...') }}</option>
                                                            @foreach($users as $user)
                                                            <option value="{{ $user->id }}" {{ $plan->responsible_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('responsible_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="department{{ $plan->id }}" class="font-weight-600">{{ __('Department') }}</label>
                                                        <input type="text" class="form-control @error('department') is-invalid @enderror" id="department{{ $plan->id }}" name="department" value="{{ $plan->department ?? '' }}" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter department...') }}">
                                                        @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="target_completion_date{{ $plan->id }}" class="font-weight-600">{{ __('Target Completion Date') }}</label>
                                                        <input type="date" class="form-control @error('target_completion_date') is-invalid @enderror" id="target_completion_date{{ $plan->id }}" name="target_completion_date" value="{{ $plan->target_completion_date ? $plan->target_completion_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                        @error('target_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="implementation_status{{ $plan->id }}" class="font-weight-600">{{ __('Implementation Status') }} <span class="text-danger">*</span></label>
                                                        <select class="form-control @error('implementation_status') is-invalid @enderror" id="implementation_status{{ $plan->id }}" name="implementation_status" required style="border-radius: var(--border-radius-sm);">
                                                            @foreach(getImplementationStatuses() as $status)
                                                            <option value="{{ $status->code }}" {{ ($plan->implementation_status === $status->code || $plan->implementation_status === $status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('implementation_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="implementation_start_date{{ $plan->id }}" class="font-weight-600">{{ __('Implementation Start Date') }}</label>
                                                        <input type="date" class="form-control @error('implementation_start_date') is-invalid @enderror" id="implementation_start_date{{ $plan->id }}" name="implementation_start_date" value="{{ $plan->implementation_start_date ? $plan->implementation_start_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                        @error('implementation_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="implementation_end_date{{ $plan->id }}" class="font-weight-600">{{ __('Implementation End Date') }}</label>
                                                        <input type="date" class="form-control @error('implementation_end_date') is-invalid @enderror" id="implementation_end_date{{ $plan->id }}" name="implementation_end_date" value="{{ $plan->implementation_end_date ? $plan->implementation_end_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                        @error('implementation_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="actual_completion_date{{ $plan->id }}" class="font-weight-600">{{ __('Actual Completion Date') }}</label>
                                                        <input type="date" class="form-control @error('actual_completion_date') is-invalid @enderror" id="actual_completion_date{{ $plan->id }}" name="actual_completion_date" value="{{ $plan->actual_completion_date ? $plan->actual_completion_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                                        @error('actual_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="control_measures{{ $plan->id }}" class="font-weight-600">{{ __('Control Measures') }}</label>
                                                <textarea class="form-control editor @error('control_measures') is-invalid @enderror" id="control_measures{{ $plan->id }}" name="control_measures" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the control measures to be implemented...') }}">{{ $plan->control_measures ?? '' }}</textarea>
                                                @error('control_measures') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="expected_outcome{{ $plan->id }}" class="font-weight-600">{{ __('Expected Outcome') }}</label>
                                                <textarea class="form-control editor @error('expected_outcome') is-invalid @enderror" id="expected_outcome{{ $plan->id }}" name="expected_outcome" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the expected outcome...') }}">{{ $plan->expected_outcome ?? '' }}</textarea>
                                                @error('expected_outcome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="resources_required{{ $plan->id }}" class="font-weight-600">{{ __('Resources Required') }}</label>
                                                <textarea class="form-control editor @error('resources_required') is-invalid @enderror" id="resources_required{{ $plan->id }}" name="resources_required" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe resources required (budget, staff, equipment, etc.)...') }}">{{ $plan->resources_required ?? '' }}</textarea>
                                                @error('resources_required') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="residual_risk_expected{{ $plan->id }}" class="font-weight-600">{{ __('Residual Risk Expected (RPN)') }}</label>
                                                        @php
                                                            // Get current RPN from assessment
                                                            $likelihoodScore = $risk->likelihoodScale->score ?? $risk->likelihood_score ?? null;
                                                            $severityScore = $risk->severityScale->score ?? $risk->severity_score ?? null;
                                                            $currentRpn = null;
                                                            if ($likelihoodScore && $severityScore) {
                                                                $currentRpn = $likelihoodScore * $severityScore;
                                                            } elseif ($risk->rpn) {
                                                                $currentRpn = $risk->rpn;
                                                            }
                                                        @endphp
                                                        @if($currentRpn)
                                                        <div class="alert alert-info mb-2" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                                                            <i class="mdi mdi-information-outline"></i> <strong>{{ __('Current RPN:') }}</strong> {{ $currentRpn }}
                                                        </div>
                                                        @endif
                                                        <input type="number" 
                                                               class="form-control @error('residual_risk_expected') is-invalid @enderror" 
                                                               id="residual_risk_expected{{ $plan->id }}" 
                                                               name="residual_risk_expected" 
                                                               min="1" 
                                                               max="{{ $currentRpn ? $currentRpn : 25 }}" 
                                                               step="1"
                                                               value="{{ $plan->residual_risk_expected ?? '' }}" 
                                                               style="border-radius: var(--border-radius-sm);" 
                                                               placeholder="{{ __('Estimated RPN after treatment (min: 1)') }}"
                                                               @if($currentRpn) data-current-rpn="{{ $currentRpn }}" @endif
                                                               oninput="if(this.value < 1) { this.value = ''; this.setCustomValidity('Residual RPN must be at least 1'); } else { this.setCustomValidity(''); }"
                                                               onkeydown="return event.key !== '-' && event.key !== '+'">
                                                        @error('residual_risk_expected') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        <small class="form-text text-muted">
                                                            {{ __('Estimated Risk Priority Number after implementing this treatment plan') }}
                                                            @if($currentRpn)
                                                                <br><strong>{{ __('Note:') }}</strong> {{ __('Residual RPN should be less than or equal to the current RPN (') }}{{ $currentRpn }}{{ __(')') }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="implementation_notes{{ $plan->id }}" class="font-weight-600">{{ __('Implementation Notes') }}</label>
                                                <textarea class="form-control editor @error('implementation_notes') is-invalid @enderror" id="implementation_notes{{ $plan->id }}" name="implementation_notes" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter implementation notes...') }}">{{ $plan->implementation_notes ?? '' }}</textarea>
                                                @error('implementation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                                            <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                                                <i class="mdi mdi-content-save"></i> {{ __('Update Treatment Plan') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                    
                    <!-- View Treatment Plan Modals - Full Details with Implementation -->
                    @if($risk->treatmentPlans && $risk->treatmentPlans->count() > 0)
                        @foreach($risk->treatmentPlans as $plan)
                        <div class="modal fade" id="viewTreatmentPlanModal{{ $plan->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">
                                            <i class="mdi mdi-clipboard-list"></i> {{ __('Treatment Plan Details') }}: {{ $plan->title }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                        <!-- Status Badge -->
                                        <div class="mb-4 text-center">
                                            @php
                                                $statusLower = strtolower($plan->implementation_status ?? 'planned');
                                                $statusClass = match($statusLower) {
                                                    'completed' => 'success',
                                                    'in progress', 'in_progress' => 'primary',
                                                    'on hold', 'on_hold' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                                                {{ $plan->implementation_status ?? 'Planned' }}
                                            </span>
                                        </div>
                                        
                                        <!-- Treatment Plan Details Grid -->
                                        <div class="row mb-4">
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Treatment Type') }}</strong>
                                                    <span style="font-size: 0.9375rem;">{{ $plan->treatmentType->name ?? $plan->treatment_type_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Priority') }}</strong>
                                                    @if($plan->priority)
                                                    @php
                                                        $priorityLower = strtolower($plan->priority);
                                                        $priorityClass = match($priorityLower) {
                                                            'critical' => 'danger',
                                                            'high' => 'warning',
                                                            'medium' => 'info',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge badge-{{ $priorityClass }}">{{ ucfirst($plan->priority) }}</span>
                                                    @else
                                                    <span class="text-muted">N/A</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Responsible Person') }}</strong>
                                                    <span style="font-size: 0.9375rem;">{{ $plan->responsibleUser->name ?? $plan->responsible_person ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Target Completion') }}</strong>
                                                    <span style="font-size: 0.9375rem;">{{ $plan->target_completion_date ? $plan->target_completion_date->format('M d, Y') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Residual Risk Expected') }}</strong>
                                                    <span style="font-size: 0.9375rem;">{{ $plan->residual_risk_expected ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-section h-100" style="padding: 1rem;">
                                                    <strong class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Actual Completion') }}</strong>
                                                    <span style="font-size: 0.9375rem;">{{ $plan->actual_completion_date ? $plan->actual_completion_date->format('M d, Y') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="mb-4">
                                            <strong class="text-muted d-block mb-2" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Description') }}</strong>
                                            <div class="detail-section" style="padding: 1rem;">
                                                {!! $plan->description ?? '<span class="text-muted">No description provided</span>' !!}
                                            </div>
                                        </div>
                                        
                                        <!-- Control Measures -->
                                        @if($plan->control_measures)
                                        <div class="mb-4">
                                            <strong class="text-muted d-block mb-2" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Control Measures') }}</strong>
                                            <div class="detail-section" style="padding: 1rem;">
                                                {!! $plan->control_measures !!}
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <!-- Implementation Notes -->
                                        @if($plan->implementation_notes)
                                        <div class="mb-4">
                                            <strong class="text-muted d-block mb-2" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Implementation Notes') }}</strong>
                                            <div class="detail-section" style="padding: 1rem;">
                                                {!! $plan->implementation_notes !!}
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <!-- Implementation Status Summary -->
                                        @if($plan->implementation_status)
                                        <div class="mb-4">
                                            <strong class="text-muted d-block mb-2" style="font-size: 0.75rem; text-transform: uppercase;">{{ __('Implementation Progress') }}</strong>
                                            <div class="detail-section" style="padding: 1rem;">
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <strong class="text-muted d-block" style="font-size: 0.7rem;">{{ __('Start Date') }}</strong>
                                                        <span>{{ $plan->implementation_start_date ? $plan->implementation_start_date->format('M d, Y') : 'Not set' }}</span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <strong class="text-muted d-block" style="font-size: 0.7rem;">{{ __('End Date') }}</strong>
                                                        <span>{{ $plan->implementation_end_date ? $plan->implementation_end_date->format('M d, Y') : 'Not set' }}</span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <strong class="text-muted d-block" style="font-size: 0.7rem;">{{ __('Actual Completion') }}</strong>
                                                        <span>{{ $plan->actual_completion_date ? $plan->actual_completion_date->format('M d, Y') : 'Not completed' }}</span>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <strong class="text-muted d-block" style="font-size: 0.7rem;">{{ __('Status') }}</strong>
                                                        @php
                                                            $viewStatusClass = match(strtolower($plan->implementation_status ?? '')) {
                                                                'completed' => 'success',
                                                                'in progress', 'in_progress' => 'primary',
                                                                'on hold', 'on_hold' => 'warning',
                                                                'cancelled' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $viewStatusClass }}">{{ $plan->implementation_status }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <!-- Existing Attachments -->
                                        @if($plan->attachments && $plan->attachments->count() > 0)
                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="mdi mdi-attachment"></i> {{ __('Attached Evidence') }} ({{ $plan->attachments->count() }})</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('File Name') }}</th>
                                                                <th>{{ __('Type') }}</th>
                                                                <th>{{ __('Size') }}</th>
                                                                <th>{{ __('Uploaded') }}</th>
                                                                <th>{{ __('Actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($plan->attachments as $attachment)
                                                            <tr>
                                                                <td>{{ $attachment->original_filename ?? $attachment->filename }}</td>
                                                                <td>{{ strtoupper($attachment->file_extension ?? pathinfo($attachment->filename, PATHINFO_EXTENSION)) }}</td>
                                                                <td>{{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB</td>
                                                                <td>{{ $attachment->created_at ? $attachment->created_at->format('M d, Y') : 'N/A' }}</td>
                                                                <td>
                                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                                        <i class="mdi mdi-eye"></i>
                                                                    </a>
                                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" download class="btn btn-sm btn-outline-info" title="{{ __('Download') }}">
                                                                        <i class="mdi mdi-download"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                @endif

                <!-- Reviews Tab -->
                @if(($risk->reviews && $risk->reviews->count() > 0) || $currentStep >= 6)
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-eye text-primary"></i> {{ __('Risk Reviews') }}
                        </h6>
                        @if(!$isClosed && $currentStep == 7)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addReviewModal">
                            <i class="mdi mdi-plus"></i> {{ __('Add Review') }}
                        </button>
                        @endif
                        @endif
                    </div>
                    
                    @if($currentStep >= 7 && !$isClosed)
                    <div class="alert alert-info mb-3" style="border-left: 4px solid #17a2b8; border-radius: var(--border-radius-sm);">
                        <div class="d-flex align-items-start">
                            <i class="mdi mdi-information-outline" style="font-size: 1.5rem; margin-right: 0.75rem; margin-top: 0.125rem;"></i>
                            <div>
                                <strong>{{ __('ISO 31000 Review Decision Loop') }}</strong>
                                <p class="mb-2 mt-1" style="font-size: 0.875rem;">
                                    {{ __('Based on your review decision, the system will automatically route the risk:') }}
                                </p>
                                <ul class="mb-0" style="font-size: 0.875rem; padding-left: 1.25rem;">
                                    <li><strong>{{ __('Close Risk') }}</strong> → {{ __('Moves to Closed status (if all requirements met)') }}</li>
                                    <li><strong>{{ __('Continue Monitoring') }}</strong> → {{ __('Stays in Monitoring, sets next review date') }}</li>
                                    <li><strong>{{ __('Additional Controls Needed') }}</strong> → {{ __('Returns to Treatment Planning for new controls') }}</li>
                                    <li><strong>{{ __('Reassess Risk') }}</strong> → {{ __('Check "Reassess Risk" checkbox OR system will auto-detect if scores differ significantly') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($risk->reviews && $risk->reviews->count() > 0)
                    <!-- Review Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card" style="border-left: 4px solid #17a2b8; border-radius: var(--border-radius-sm);">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0" style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">{{ __('Total Reviews') }}</h6>
                                            <h3 class="mb-0 mt-1" style="font-weight: 700; color: #1e293b;">{{ $risk->reviews->count() }}</h3>
                                        </div>
                                        <i class="mdi mdi-eye" style="font-size: 2rem; color: #17a2b8;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="border-left: 4px solid #28a745; border-radius: var(--border-radius-sm);">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0" style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">{{ __('Last Review') }}</h6>
                                            <h6 class="mb-0 mt-1" style="font-weight: 600; color: #1e293b;">
                                                {{ $risk->reviews->sortByDesc('review_date')->first()->review_date->format('M d, Y') ?? 'N/A' }}
                                            </h6>
                                        </div>
                                        <i class="mdi mdi-calendar-check" style="font-size: 2rem; color: #28a745;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="border-left: 4px solid #ffc107; border-radius: var(--border-radius-sm);">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0" style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">{{ __('Latest Decision') }}</h6>
                                            @php
                                                $latestReview = $risk->reviews->sortByDesc('review_date')->first();
                                                $latestDecision = $latestReview->review_decision ?? 'N/A';
                                                $decisionCode = strtolower(str_replace(' ', '_', $latestDecision));
                                                $decisionBadge = $decisionCode === 'close_risk' || $latestDecision === 'Close Risk' ? 'success' : 
                                                                ($decisionCode === 'additional_controls_needed' || $latestDecision === 'Additional Controls Needed' ? 'warning' : 
                                                                ($decisionCode === 'continue_monitoring' || $latestDecision === 'Continue Monitoring' ? 'info' : 'secondary'));
                                            @endphp
                                            <span class="badge badge-{{ $decisionBadge }} mt-1" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                                {{ $latestDecision }}
                                            </span>
                                        </div>
                                        <i class="mdi mdi-check-decagram" style="font-size: 2rem; color: #ffc107;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="border-left: 4px solid #6f42c1; border-radius: var(--border-radius-sm);">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0" style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">{{ __('Next Review') }}</h6>
                                            <h6 class="mb-0 mt-1" style="font-weight: 600; color: #1e293b;">
                                                {{ $risk->next_review_date ? $risk->next_review_date->format('M d, Y') : __('Not scheduled') }}
                                            </h6>
                                        </div>
                                        <i class="mdi mdi-calendar-clock" style="font-size: 2rem; color: #6f42c1;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Review #') }}</th>
                                    <th>{{ __('Review Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Likelihood') }}</th>
                                    <th>{{ __('Severity') }}</th>
                                    <th>{{ __('RPN') }}</th>
                                    <th>{{ __('Risk Level') }}</th>
                                    <th>{{ __('Decision') }}</th>
                                    <th>{{ __('Workflow Impact') }}</th>
                                    <th>{{ __('Reviewed By') }}</th>
                                    <th>{{ __('Next Review') }}</th>
                                    <th>{{ __('Review Findings') }}</th>
                                    <th>{{ __('Control Effectiveness') }}</th>
                                    <th>{{ __('Decision Justification') }}</th>
                                    <th>{{ __('KPI Metrics') }}</th>
                                    <th>{{ __('Action Required') }}</th>
                                    @if(!$isClosed)
                                    <th style="min-width: 100px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($risk->reviews as $review)
                                <tr>
                                    <td><strong>{{ $review->review_number }}</strong></td>
                                    <td>{{ $review->review_date->format('M d, Y') }}</td>
                                    <td><span class="badge badge-info">{{ $review->review_type }}</span></td>
                                    <td>{{ $review->review_likelihood_score ?? 'N/A' }}</td>
                                    <td>{{ $review->review_severity_score ?? 'N/A' }}</td>
                                    <td>
                                        @if($review->review_rpn)
                                        <span class="badge badge-modern badge-{{ $review->review_rpn >= 15 ? 'danger' : ($review->review_rpn >= 9 ? 'warning' : 'success') }}">
                                            {{ $review->review_rpn }}
                                        </span>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->review_risk_level)
                                        <span class="badge badge-modern badge-{{ strtolower($review->review_risk_level) === 'high' ? 'danger' : (strtolower($review->review_risk_level) === 'medium' ? 'warning' : 'success') }}">
                                            {{ $review->review_risk_level }}
                                        </span>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $decisionCode = strtolower(str_replace(' ', '_', $review->review_decision ?? ''));
                                            $decisionName = $review->review_decision ?? 'N/A';
                                            $decisionOption = getReviewDecisions()->firstWhere('code', $decisionCode) ?? getReviewDecisions()->firstWhere('name', $review->review_decision);
                                        @endphp
                                        <span class="badge badge-{{ 
                                            $decisionCode === 'close_risk' || $review->review_decision === 'Close Risk' ? 'success' : 
                                            ($decisionCode === 'additional_controls_needed' || $review->review_decision === 'Additional Controls Needed' ? 'warning' : 
                                            ($decisionCode === 'continue_monitoring' || $review->review_decision === 'Continue Monitoring' ? 'info' : 'primary')) 
                                        }}">
                                            {{ $decisionName }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $workflowImpact = '';
                                            $impactBadge = 'secondary';
                                            if ($decisionCode === 'close_risk' || $review->review_decision === 'Close Risk') {
                                                $workflowImpact = 'Risk Closed';
                                                $impactBadge = 'success';
                                            } elseif ($decisionCode === 'continue_monitoring' || $review->review_decision === 'Continue Monitoring') {
                                                $workflowImpact = 'Continue Monitoring';
                                                $impactBadge = 'info';
                                            } elseif ($decisionCode === 'additional_controls_needed' || $review->review_decision === 'Additional Controls Needed') {
                                                $workflowImpact = 'Returned to Treatment Planning';
                                                $impactBadge = 'warning';
                                            } elseif ($review->reassess_risk || ($review->action_required && str_contains(strtolower($review->action_required_reason ?? ''), 'reassess'))) {
                                                $workflowImpact = 'Returned to Assessment';
                                                $impactBadge = 'primary';
                                            } else {
                                                $workflowImpact = 'No Change';
                                                $impactBadge = 'secondary';
                                            }
                                        @endphp
                                        <span class="badge badge-{{ $impactBadge }}" title="{{ __('Workflow action taken based on this review decision') }}">
                                            <i class="mdi mdi-{{ 
                                                $impactBadge === 'success' ? 'check-circle' : 
                                                ($impactBadge === 'warning' ? 'alert-circle' : 
                                                ($impactBadge === 'info' ? 'arrow-right-circle' : 'circle-outline')) 
                                            }}"></i> {{ $workflowImpact }}
                                        </span>
                                    </td>
                                    <td>{{ $review->reviewed_by ?? 'N/A' }}</td>
                                    <td>{{ $review->next_review_date ? $review->next_review_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        @if($review->review_findings)
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ strip_tags($review->review_findings) }}">
                                            {{ Str::limit(strip_tags($review->review_findings), 50) }}
                                        </div>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->control_effectiveness_assessment)
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ strip_tags($review->control_effectiveness_assessment) }}">
                                            {{ Str::limit(strip_tags($review->control_effectiveness_assessment), 50) }}
                                        </div>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->decision_justification)
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ strip_tags($review->decision_justification) }}">
                                            {{ Str::limit(strip_tags($review->decision_justification), 50) }}
                                        </div>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->kpi_metrics)
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ strip_tags($review->kpi_metrics) }}">
                                            {{ Str::limit(strip_tags($review->kpi_metrics), 50) }}
                                        </div>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->action_required)
                                        <span class="badge badge-warning">{{ __('Yes') }}</span>
                                        @if($review->action_required_reason)
                                        <br><small style="color: #64748b;" title="{{ strip_tags($review->action_required_reason) }}">{{ Str::limit(strip_tags($review->action_required_reason), 30) }}</small>
                                        @endif
                                        @else
                                        <span class="badge badge-success">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                    @if(!$isClosed)
                                    <td style="text-align: center;">
                                        @if($currentStep == 7)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-toggle="modal" 
                                                data-target="#editReviewModal{{ $review->id }}"
                                                title="{{ __('Edit Review') }}">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        @endif
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i> No reviews have been conducted yet.
                    </div>
                    @endif
                </div>
                @endif



                <!-- Attachments Tab -->
                <div class="tab-pane fade" id="attachments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-paperclip text-primary"></i> {{ __('Attachments') }}
                        </h6>
                        @if(!$isClosed)
                    @if(auth()->user()->can('risk-management.components.risks.edit'))
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#uploadAttachmentModal">
                            <i class="mdi mdi-upload"></i> {{ __('Add Attachment') }}
                        </button>
                        @endif
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
                                    @if($risk->attachments && $risk->attachments->count() > 0)
                                    <th style="min-width: 150px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($risk->attachments && $risk->attachments->count() > 0)
                                    @foreach($risk->attachments as $attachment)
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
                                                @if(auth()->user()->can('risk-management.components.risks.delete'))
                                                <form action="{{ route('risk.risks.attachments.delete', $attachment->id) }}" 
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
                    @if($chainOfCustody['timeline'] || $risk->activityLogs->count() > 0)
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
                                @foreach($risk->activityLogs->sortByDesc('created_at') as $log)
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
                    @if(isset($approvals) && $approvals->count() > 0)
                    <div class="table-responsive" style="border-radius: var(--border-radius-sm);">
                        <table class="table table-modern table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Step') }}</th>
                                    <th>{{ __('From') }}</th>
                                    <th>{{ __('To') }}</th>
                                    <th>{{ __('Approver') }}</th>
                                    <th>{{ __('Remarks') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvals as $approval)
                                <tr>
                                    <td class="font-weight-bold">
                                        <span class="badge badge-info">{{ getWorkflowStepName($approval->workflow_step) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $approval->from_status ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $approval->to_status ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs mr-2" style="width: 30px; height: 30px; background-color: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                {{ substr($approval->approver_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-600">{{ $approval->approver_name }}</div>
                                                <div class="text-muted small">
                                                    {{ $approval->role_type ?? 'Approver' }}
                                                    @if($approval->iso_role)
                                                    <span class="badge badge-light border ml-1">{{ $approval->iso_role }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        @if(strlen($approval->remarks) > 50)
                                            <span title="{{ $approval->remarks }}" data-toggle="tooltip">{{ substr($approval->remarks, 0, 50) }}...</span>
                                        @else
                                            {{ $approval->remarks }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $approval->approved_at ? \Carbon\Carbon::parse($approval->approved_at)->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state text-center p-5">
                        <i class="mdi mdi-check-all" style="font-size: 4rem; color: #e2e8f0;"></i>
                        <p class="mt-3 text-muted">{{ __('No approval history recorded yet.') }}</p>
                    </div>
                    @endif

                    <!-- Pending Approvals Section -->
                    @php
                        $pendingApprovers = $risk->getPendingApprovers();
                        $requiredApprovers = $risk->getRequiredApprovers();
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
<!-- Add Review Modal -->
@if(!$isClosed && ($currentStep >= 5))
<div class="modal fade" id="addReviewModal" tabindex="-1" role="dialog" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.review.store', $risk->id) }}" method="POST" id="reviewForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addReviewModalLabel">
                        <i class="mdi mdi-eye"></i> {{ __('Conduct Risk Review') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="review_date" class="font-weight-600">{{ __('Review Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" id="review_date" name="review_date" value="{{ date('Y-m-d') }}" required style="border-radius: var(--border-radius-sm);">
                                @error('review_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="review_type" class="font-weight-600">{{ __('Review Type') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('review_type') is-invalid @enderror" id="review_type" name="review_type" required style="border-radius: var(--border-radius-sm);">
                                    @foreach(getReviewTypes() as $type)
                                    <option value="{{ $type->code }}" {{ old('review_type') === $type->code || old('review_type') === $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('review_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="review_likelihood_score" class="font-weight-600">{{ __('Review Likelihood Score') }}</label>
                                <select class="form-control @error('review_likelihood_score') is-invalid @enderror" id="review_likelihood_score" name="review_likelihood_score" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Score...') }}</option>
                                    @foreach(getRiskScores() as $score)
                                    <option value="{{ $score->code }}" {{ old('review_likelihood_score') == $score->code ? 'selected' : '' }}>{{ $score->code }}@if(data_get($score, 'metadata.label')) - {{ data_get($score, 'metadata.label') }}@endif</option>
                                    @endforeach
                                </select>
                                @error('review_likelihood_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="review_severity_score" class="font-weight-600">{{ __('Review Severity Score') }}</label>
                                <select class="form-control @error('review_severity_score') is-invalid @enderror" id="review_severity_score" name="review_severity_score" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Score...') }}</option>
                                    @foreach(getRiskScores() as $score)
                                    <option value="{{ $score->code }}" {{ old('review_severity_score') == $score->code ? 'selected' : '' }}>{{ $score->code }}@if(data_get($score, 'metadata.label')) - {{ data_get($score, 'metadata.label') }}@endif</option>
                                    @endforeach
                                </select>
                                @error('review_severity_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="review_decision" class="font-weight-600">{{ __('Review Decision') }} <span class="text-danger">*</span></label>
                        <select class="form-control @error('review_decision') is-invalid @enderror" id="review_decision" name="review_decision" required style="border-radius: var(--border-radius-sm);">
                            @foreach(getReviewDecisions() as $decision)
                            <option value="{{ $decision->code }}" {{ old('review_decision') === $decision->code || old('review_decision') === $decision->name ? 'selected' : '' }}>{{ $decision->name }}</option>
                            @endforeach
                        </select>
                        @error('review_decision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="review_findings" class="font-weight-600">{{ __('Review Findings') }}</label>
                        <textarea class="form-control editor @error('review_findings') is-invalid @enderror" id="review_findings" name="review_findings" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter review findings...') }}">{{ old('review_findings') }}</textarea>
                        @error('review_findings') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="control_effectiveness_assessment" class="font-weight-600">{{ __('Control Effectiveness Assessment') }}</label>
                        <textarea class="form-control editor @error('control_effectiveness_assessment') is-invalid @enderror" id="control_effectiveness_assessment" name="control_effectiveness_assessment" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Assess the effectiveness of controls...') }}">{{ old('control_effectiveness_assessment') }}</textarea>
                        @error('control_effectiveness_assessment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="decision_justification" class="font-weight-600">{{ __('Decision Justification') }}</label>
                        <textarea class="form-control editor @error('decision_justification') is-invalid @enderror" id="decision_justification" name="decision_justification" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Justify the review decision...') }}">{{ old('decision_justification') }}</textarea>
                        @error('decision_justification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="next_review_date" class="font-weight-600">{{ __('Next Review Date') }}</label>
                        <input type="date" class="form-control @error('next_review_date') is-invalid @enderror" id="next_review_date" name="next_review_date" value="{{ old('next_review_date') }}" style="border-radius: var(--border-radius-sm);">
                        @error('next_review_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">{{ __('Set the date for the next scheduled review') }}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="kpi_metrics" class="font-weight-600">{{ __('KPI Metrics') }}</label>
                        <textarea class="form-control editor @error('kpi_metrics') is-invalid @enderror" id="kpi_metrics" name="kpi_metrics" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter KPI metrics related to this risk...') }}">{{ old('kpi_metrics') }}</textarea>
                        @error('kpi_metrics') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="reassess_risk" name="reassess_risk" value="1" {{ old('reassess_risk') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="reassess_risk">
                                <strong>{{ __('Reassess Risk') }}</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('Check this to return the risk to Assessment step for a new assessment. The system will also automatically trigger reassessment if review scores differ significantly from current assessment.') }}
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="action_required" name="action_required" value="1" {{ old('action_required') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="action_required">{{ __('Action Required') }}</label>
                        </div>
                        <small class="form-text text-muted">{{ __('Check if this review requires additional action (other than reassessment)') }}</small>
                    </div>
                    
                    <div class="form-group" id="action_required_reason_group" style="display: none;">
                        <label for="action_required_reason" class="font-weight-600">{{ __('Action Required Reason') }}</label>
                        <textarea class="form-control editor @error('action_required_reason') is-invalid @enderror" id="action_required_reason" name="action_required_reason" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Explain what action is required and why...') }}">{{ old('action_required_reason') }}</textarea>
                        @error('action_required_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Save Review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Review Modals -->
@if(!$isClosed && ($currentStep >= 5) && $risk->reviews && $risk->reviews->count() > 0)
@foreach($risk->reviews as $review)
<div class="modal fade" id="editReviewModal{{ $review->id }}" tabindex="-1" role="dialog" aria-labelledby="editReviewModalLabel{{ $review->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.review.update', ['riskId' => $risk->id, 'reviewId' => $review->id]) }}" method="POST" id="editReviewForm{{ $review->id }}">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editReviewModalLabel{{ $review->id }}">
                        <i class="mdi mdi-pencil"></i> {{ __('Edit Risk Review') }} - {{ $review->review_number }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_review_date{{ $review->id }}" class="font-weight-600">{{ __('Review Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" id="edit_review_date{{ $review->id }}" name="review_date" value="{{ $review->review_date->format('Y-m-d') }}" required style="border-radius: var(--border-radius-sm);">
                                @error('review_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_review_type{{ $review->id }}" class="font-weight-600">{{ __('Review Type') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('review_type') is-invalid @enderror" id="edit_review_type{{ $review->id }}" name="review_type" required style="border-radius: var(--border-radius-sm);">
                                    @foreach(getReviewTypes() as $type)
                                    <option value="{{ $type->code }}" {{ ($review->review_type === $type->code || $review->review_type === $type->name) ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('review_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_review_likelihood_score{{ $review->id }}" class="font-weight-600">{{ __('Review Likelihood Score') }}</label>
                                <select class="form-control @error('review_likelihood_score') is-invalid @enderror" id="edit_review_likelihood_score{{ $review->id }}" name="review_likelihood_score" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Score...') }}</option>
                                    @foreach(getRiskScores() as $score)
                                    <option value="{{ $score->code }}" {{ $review->review_likelihood_score == $score->code ? 'selected' : '' }}>{{ $score->code }}@if(data_get($score, 'metadata.label')) - {{ data_get($score, 'metadata.label') }}@endif</option>
                                    @endforeach
                                </select>
                                @error('review_likelihood_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_review_severity_score{{ $review->id }}" class="font-weight-600">{{ __('Review Severity Score') }}</label>
                                <select class="form-control @error('review_severity_score') is-invalid @enderror" id="edit_review_severity_score{{ $review->id }}" name="review_severity_score" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Score...') }}</option>
                                    @foreach(getRiskScores() as $score)
                                    <option value="{{ $score->code }}" {{ $review->review_severity_score == $score->code ? 'selected' : '' }}>{{ $score->code }}@if(data_get($score, 'metadata.label')) - {{ data_get($score, 'metadata.label') }}@endif</option>
                                    @endforeach
                                </select>
                                @error('review_severity_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_review_decision{{ $review->id }}" class="font-weight-600">{{ __('Review Decision') }} <span class="text-danger">*</span></label>
                        <select class="form-control @error('review_decision') is-invalid @enderror" id="edit_review_decision{{ $review->id }}" name="review_decision" required style="border-radius: var(--border-radius-sm);">
                            @foreach(getReviewDecisions() as $decision)
                            <option value="{{ $decision->code }}" {{ ($review->review_decision === $decision->code || $review->review_decision === $decision->name) ? 'selected' : '' }}>{{ $decision->name }}</option>
                            @endforeach
                        </select>
                        @error('review_decision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_review_findings{{ $review->id }}" class="font-weight-600">{{ __('Review Findings') }}</label>
                        <textarea class="form-control editor @error('review_findings') is-invalid @enderror" id="edit_review_findings{{ $review->id }}" name="review_findings" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter review findings...') }}">{{ $review->review_findings }}</textarea>
                        @error('review_findings') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_control_effectiveness_assessment{{ $review->id }}" class="font-weight-600">{{ __('Control Effectiveness Assessment') }}</label>
                        <textarea class="form-control editor @error('control_effectiveness_assessment') is-invalid @enderror" id="edit_control_effectiveness_assessment{{ $review->id }}" name="control_effectiveness_assessment" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Assess the effectiveness of controls...') }}">{{ $review->control_effectiveness_assessment }}</textarea>
                        @error('control_effectiveness_assessment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_decision_justification{{ $review->id }}" class="font-weight-600">{{ __('Decision Justification') }}</label>
                        <textarea class="form-control editor @error('decision_justification') is-invalid @enderror" id="edit_decision_justification{{ $review->id }}" name="decision_justification" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Justify the review decision...') }}">{{ $review->decision_justification }}</textarea>
                        @error('decision_justification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_next_review_date{{ $review->id }}" class="font-weight-600">{{ __('Next Review Date') }}</label>
                        <input type="date" class="form-control @error('next_review_date') is-invalid @enderror" id="edit_next_review_date{{ $review->id }}" name="next_review_date" value="{{ $review->next_review_date ? $review->next_review_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                        @error('next_review_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">{{ __('Set the date for the next scheduled review') }}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_kpi_metrics{{ $review->id }}" class="font-weight-600">{{ __('KPI Metrics') }}</label>
                        <textarea class="form-control editor @error('kpi_metrics') is-invalid @enderror" id="edit_kpi_metrics{{ $review->id }}" name="kpi_metrics" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter KPI metrics related to this risk...') }}">{{ $review->kpi_metrics ?? '' }}</textarea>
                        @error('kpi_metrics') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_reassess_risk{{ $review->id }}" name="reassess_risk" value="1" {{ $review->reassess_risk ? 'checked' : '' }}>
                            <label class="custom-control-label" for="edit_reassess_risk{{ $review->id }}">
                                <strong>{{ __('Reassess Risk') }}</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('Check this to return the risk to Assessment step for a new assessment. The system will also automatically trigger reassessment if review scores differ significantly from current assessment.') }}
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_action_required{{ $review->id }}" name="action_required" value="1" {{ $review->action_required ? 'checked' : '' }}>
                            <label class="custom-control-label" for="edit_action_required{{ $review->id }}">{{ __('Action Required') }}</label>
                        </div>
                        <small class="form-text text-muted">{{ __('Check if this review requires additional action (other than reassessment)') }}</small>
                    </div>
                    
                    <div class="form-group" id="edit_action_required_reason_group{{ $review->id }}" style="display: {{ $review->action_required ? 'block' : 'none' }};">
                        <label for="edit_action_required_reason{{ $review->id }}" class="font-weight-600">{{ __('Action Required Reason') }}</label>
                        <textarea class="form-control editor @error('action_required_reason') is-invalid @enderror" id="edit_action_required_reason{{ $review->id }}" name="action_required_reason" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Explain what action is required and why...') }}">{{ $review->action_required_reason ?? '' }}</textarea>
                        @error('action_required_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Update Review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.attachments.upload', $risk->id) }}" method="POST" enctype="multipart/form-data">
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

<!-- Edit Closure Justification Modal -->
@if($currentStep >= 7 && !$isClosed)
<div class="modal fade" id="editClosureJustificationModal" tabindex="-1" role="dialog" aria-labelledby="editClosureJustificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.closure-justification.update', $risk->id) }}" method="POST" id="editClosureJustificationForm">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editClosureJustificationModalLabel">
                        <i class="mdi mdi-check-circle"></i> {{ __('Add/Edit Closure Justification') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($risk->residual_rpn && $risk->residual_rpn > ($risk->acceptance_threshold_rpn ?? 15))
                    <div class="alert alert-warning mb-3">
                        <i class="mdi mdi-alert"></i> 
                        <strong>Note:</strong> The residual RPN ({{ $risk->residual_rpn }}) exceeds the acceptance threshold ({{ $risk->acceptance_threshold_rpn ?? 15 }}). 
                        Please provide a detailed justification explaining why this risk can be closed despite the high residual risk level.
                    </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="closure_justification" class="font-weight-600">{{ __('Closure Justification') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('closure_justification') is-invalid @enderror" id="closure_justification" name="closure_justification" rows="8" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Provide a detailed justification for closing this risk...') }}">{{ $risk->closure_justification }}</textarea>
                        @error('closure_justification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">
                            Explain why this risk can be closed, including any mitigating factors, controls implemented, or acceptance rationale.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Save Closure Justification') }}
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


<!-- Workflow Action Modal -->
<div class="modal fade" id="workflowActionModal" tabindex="-1" role="dialog" aria-labelledby="workflowActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.approve-next-step', $risk->id) }}" method="POST" id="workflowActionForm">
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
                            <br><small class="text-muted">Current Status: <strong>{{ $risk->display_status_name }}</strong></small>
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
                        <small class="form-text text-muted">Select the action you want to perform on this risk.</small>
                    </div>

                    <div class="form-group" id="target_status_display" style="display: none;">
                        <label class="control-label font-weight-600">
                            Target Status
                        </label>
                        <div class="alert alert-success d-flex align-items-center mb-0" id="target_status_alert" style="border-radius: 8px; border-left: 4px solid #28a745;">
                            <i class="mdi mdi-arrow-right" style="font-size: 24px; margin-right: 12px;"></i>
                            <div style="flex: 1;">
                                <strong>Risk will move to:</strong>
                                <div class="mt-1">
                                    <span class="badge badge-success" id="target_status_badge" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ isset($nextWorkflowStatus) && $nextWorkflowStatus ? $nextWorkflowStatus->name : 'N/A' }}
                                    </span>
                                </div>
                                <small class="d-block mt-1 text-muted" id="target_status_description">
                                    <i class="mdi mdi-information-outline"></i> Moving to the next workflow step
                                </small>
                            </div>
                        </div>
                        <input type="hidden" name="target_status_id" id="target_status_id" value="{{ isset($nextWorkflowStatus) && $nextWorkflowStatus ? $nextWorkflowStatus->id : '' }}">
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
    // Initialize TinyMCE for closure justification modal
    $('#editClosureJustificationModal').on('shown.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            // Destroy existing instance if any
            tinymce.remove('#closure_justification');
            
            // Initialize TinyMCE
            tinymce.init({
                selector: '#closure_justification',
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save(); // Sync content to textarea on change
                    });
                }
            });
        }
    });
    
    // Sync TinyMCE content to textarea before form submission
    $('#editClosureJustificationForm').on('submit', function(e) {
        if (typeof tinymce !== 'undefined') {
            const editor = tinymce.get('closure_justification');
            if (editor) {
                editor.save(); // Sync TinyMCE content to textarea
                
                // Validate that content is not empty
                const content = editor.getContent({format: 'text'}).trim();
                if (!content) {
                    e.preventDefault();
                    alert('Please provide closure justification before saving.');
                    editor.focus();
                    return false;
                }
            }
        }
    });
    
    // Clean up TinyMCE when closure justification modal is closed
    $('#editClosureJustificationModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#closure_justification');
        }
    });
    
    // Test if JavaScript is running
    console.log('=== Risk Evaluation Script Loaded (RPN-First Flow) ===');
    
    // Evaluation results data for RPN-based lookup
    @php
        $evaluationResultsForJs = $evaluationResults->map(function($result) {
            $metadata = normalizeRiskConfigurationMetadata($result->metadata);
            return [
                'code' => $result->code,
                'name' => $result->name,
                'rpn_min' => $metadata['rpn_min'] ?? null,
                'rpn_max' => $metadata['rpn_max'] ?? null,
                'workflow_step' => isset($metadata['workflow_step']) ? (int) $metadata['workflow_step'] : null,
                'required_actions' => $metadata['required_actions'] ?? '',
            ];
        })->values();
        // JSON-object keys (not a PHP-indexed array) so JS lookup works for step 1–7
        $workflowStepsForJs = getRiskWorkflowSteps();
    @endphp
    const evaluationResultsData = @json($evaluationResultsForJs);
    
    // Workflow steps for display (object keys: "1"…"7"; skip "0" = All Risks)
    const workflowSteps = @json($workflowStepsForJs);
    
    // Function to determine evaluation result based on RPN
    function getEvaluationResultByRPN(rpn) {
        for (let i = 0; i < evaluationResultsData.length; i++) {
            const result = evaluationResultsData[i];
            const rpnMin = parseInt(result.rpn_min);
            const rpnMax = parseInt(result.rpn_max);
            
            if (!isNaN(rpnMin) && !isNaN(rpnMax)) {
                if (rpn >= rpnMin && rpn <= rpnMax) {
                    return result;
                }
            }
        }
        return null;
    }
    
    // Function to get badge class based on evaluation result code
    function getEvaluationBadgeClass(code) {
        const codeLower = code.toLowerCase();
        if (codeLower === 'unacceptable') return 'danger';
        if (codeLower === 'tolerable') return 'warning';
        return 'success';
    }
    
    // Handle RPN input change - auto-determine evaluation result
    $(document).on('input change keyup', '#rpn', function() {
        const rpnInput = $(this);
        const enteredRpnStr = rpnInput.val();
        const enteredRpn = enteredRpnStr ? parseInt(enteredRpnStr) : null;
        
        const evaluationResultInput = $('#evaluation_result');
        const resultDisplay = $('#auto_evaluation_result_display');
        const resultText = $('#auto_evaluation_result_text');
        const workflowStepText = $('#auto_workflow_step_text');
        const actionsDiv = $('#evaluation_actions');
        const actionsList = $('#actions_list');
        const validationDiv = $('#rpn_validation_message');
        const escalationFields = $('#escalation_fields');
        const submitButton = $('#evaluationModal button[type="submit"]');
        
        // Clear state if no RPN entered
        if (!enteredRpnStr || enteredRpnStr === '' || enteredRpn === null || isNaN(enteredRpn)) {
            evaluationResultInput.val('');
            resultDisplay.hide();
            actionsDiv.hide();
            validationDiv.hide();
            escalationFields.hide();
            submitButton.prop('disabled', false);
            return;
        }
        
        // Validate RPN - must be a positive number
        if (enteredRpn < 1) {
            evaluationResultInput.val('');
            resultDisplay.hide();
            actionsDiv.hide();
            validationDiv.html('<i class="mdi mdi-alert-circle"></i> <strong>Invalid RPN:</strong> Please enter a positive value.');
            validationDiv.show();
            submitButton.prop('disabled', true);
            escalationFields.hide();
            return;
        }
        
        // Get evaluation result based on RPN
        const matchedResult = getEvaluationResultByRPN(enteredRpn);
        
        if (matchedResult) {
            // Valid RPN - show evaluation result
            evaluationResultInput.val(matchedResult.code);
            
            const badgeClass = getEvaluationBadgeClass(matchedResult.code);
            resultText.text(matchedResult.name);
            resultText.removeClass('badge-success badge-warning badge-danger').addClass('badge-' + badgeClass);
            
            // Set alert class based on result
            resultDisplay.removeClass('alert-success alert-warning alert-danger');
            if (badgeClass === 'danger') {
                resultDisplay.addClass('alert-danger');
            } else if (badgeClass === 'warning') {
                resultDisplay.addClass('alert-warning');
            } else {
                resultDisplay.addClass('alert-success');
            }
            
            // Show next workflow step (status-scale keys 1–7)
            const nextStepKey = String(matchedResult.workflow_step ?? '');
            if (nextStepKey && workflowSteps[nextStepKey]) {
                workflowStepText.html('<i class="mdi mdi-arrow-right"></i> Next: ' + workflowSteps[nextStepKey]);
            } else {
                workflowStepText.html('');
            }
            
            resultDisplay.show();
            validationDiv.hide();
            submitButton.prop('disabled', false);
            
            // Show/hide escalation fields
            if (matchedResult.code === 'escalate') {
                escalationFields.show();
            } else {
                escalationFields.hide();
            }
            
            // Show required actions
            if (matchedResult.required_actions && matchedResult.required_actions.trim() !== '') {
                const actionsArray = matchedResult.required_actions.split(/[,\n]/).map(a => a.trim()).filter(a => a !== '');
                actionsList.empty();
                actionsArray.forEach(function(action) {
                    actionsList.append('<li>' + action + '</li>');
                });
                actionsDiv.show();
            } else {
                actionsDiv.hide();
            }
        } else {
            // No matching result found - this shouldn't happen with proper configuration
            evaluationResultInput.val('');
            resultDisplay.hide();
            actionsDiv.hide();
            validationDiv.html('<i class="mdi mdi-alert-circle"></i> <strong>Configuration Error:</strong> No evaluation result found for RPN value ' + enteredRpn + '. Please check the RPN range configuration.');
            validationDiv.show();
            submitButton.prop('disabled', true);
            escalationFields.hide();
        }
    });
    
    // Initialize on modal open
    $(document).on('shown.bs.modal', '#evaluationModal', function() {
        console.log('=== Evaluation Modal Opened (RPN-First Flow) ===');
        
        const rpnInput = $('#rpn');
        const evaluationResultInput = $('#evaluation_result');
        const initialValue = rpnInput.data('initial-value');
        
        // Check if there's an existing RPN value (editing mode)
        const existingRpn = rpnInput.val() || initialValue;
        
        if (existingRpn && existingRpn !== '') {
            // Editing mode - preserve the existing value and trigger display
            console.log('Editing mode - existing RPN:', existingRpn);
            rpnInput.val(existingRpn);
            // Trigger the input event to show evaluation result
            rpnInput.trigger('input');
        } else {
            // New evaluation - clear all fields
            rpnInput.val('');
            evaluationResultInput.val('');
            $('#auto_evaluation_result_display').hide();
            $('#evaluation_actions').hide();
            $('#rpn_validation_message').hide();
            $('#escalation_fields').hide();
            $('#evaluationModal button[type="submit"]').prop('disabled', false);
        }
        
        // Focus on RPN input
        rpnInput.focus();
    });
    
    // Clear on modal close
    $('#evaluationModal').on('hidden.bs.modal', function() {
        $('#rpn').val('');
        $('#evaluation_result').val('');
        $('#auto_evaluation_result_display').hide();
        $('#evaluation_actions').hide();
        $('#rpn_validation_message').hide();
        $('#escalation_fields').hide();
    });
    
    // Show/hide action required reason based on checkbox
    $('#addReviewModal').on('change', '#action_required', function() {
        const isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#action_required_reason_group').show();
        } else {
            $('#action_required_reason_group').hide();
        }
    });
    
    // Initialize TinyMCE for review modal
    $('#addReviewModal').on('shown.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            const textareaIds = ['review_findings', 'control_effectiveness_assessment', 'decision_justification', 'kpi_metrics', 'action_required_reason'];
            
            textareaIds.forEach(function(textareaId) {
                // Destroy existing instance if any
                tinymce.remove('#' + textareaId);
                
                // Initialize TinyMCE
                tinymce.init({
                    selector: '#' + textareaId,
                    menubar: false,
                    height: 200,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                    branding: false
                });
            });
        }
    });
    
    // Clean up TinyMCE when modal is closed
    $('#addReviewModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#review_findings');
            tinymce.remove('#control_effectiveness_assessment');
            tinymce.remove('#decision_justification');
        }
    });
    
    // Show/hide action required reason for edit review modals
    @if($risk->reviews && $risk->reviews->count() > 0)
    @foreach($risk->reviews as $review)
    $('#editReviewModal{{ $review->id }}').on('change', '#edit_action_required{{ $review->id }}', function() {
        const isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#edit_action_required_reason_group{{ $review->id }}').show();
        } else {
            $('#edit_action_required_reason_group{{ $review->id }}').hide();
        }
    });
    @endforeach
    @endif
    
    // Initialize TinyMCE for edit review modals
    @if($risk->reviews && $risk->reviews->count() > 0)
    @foreach($risk->reviews as $review)
    $('#editReviewModal{{ $review->id }}').on('shown.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            const textareaIds = ['edit_review_findings{{ $review->id }}', 'edit_control_effectiveness_assessment{{ $review->id }}', 'edit_decision_justification{{ $review->id }}', 'edit_kpi_metrics{{ $review->id }}', 'edit_action_required_reason{{ $review->id }}'];
            
            textareaIds.forEach(function(textareaId) {
                // Destroy existing instance if any
                tinymce.remove('#' + textareaId);
                
                // Initialize TinyMCE
                tinymce.init({
                    selector: '#' + textareaId,
                    menubar: false,
                    height: 200,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                    branding: false
                });
            });
        }
    });
    
    // Clean up TinyMCE when edit modal is closed
    $('#editReviewModal{{ $review->id }}').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#edit_review_findings{{ $review->id }}');
            tinymce.remove('#edit_control_effectiveness_assessment{{ $review->id }}');
            tinymce.remove('#edit_decision_justification{{ $review->id }}');
            tinymce.remove('#edit_kpi_metrics{{ $review->id }}');
            tinymce.remove('#edit_action_required_reason{{ $review->id }}');
        }
    });
    @endforeach
    @endif
    
    // Image preview modal
    $('#viewImageModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const imageUrl = button.data('image-url');
        const imageName = button.data('image-name');
        const modal = $(this);
        modal.find('#modalImage').attr('src', imageUrl);
        modal.find('#imageModalTitle').text(imageName);
    });

    // Store next workflow status data
    var nextWorkflowStatusData = {
        @if(isset($nextWorkflowStatus) && $nextWorkflowStatus)
        id: '{{ $nextWorkflowStatus->id }}',
        name: '{{ addslashes($nextWorkflowStatus->name) }}',
        available: true
        @else
        available: false
        @endif
    };
    
    // Store available statuses for status-config step 2 (Under Assessment)
    // Used when Identified (risk-record step 2 / status-config step 1) has no explicit next status.
    var nextAssessmentStatuses = [
        @if(isset($availableStatuses) && isset($availableStatuses[2]))
            @foreach($availableStatuses[2] as $status)
            {id: '{{ $status->id }}', name: '{{ addslashes($status->name) }}'},
            @endforeach
        @endif
    ];

    // Workflow Action Modal
    $('#workflowActionModal').on('show.bs.modal', function() {
        // Reset form but preserve hidden inputs
        const targetStatusId = $('#target_status_id').val();
        $('#workflowActionForm')[0].reset();
        $('#action_warning').hide();
        $('#target_status_display').hide();
        
        // Restore target status ID if it was set
        if (targetStatusId) {
            $('#target_status_id').val(targetStatusId);
        }
        
        // Set default action to approve if next status is available and trigger change
        if (nextWorkflowStatusData.available) {
        $('#workflow_action').val('approve').trigger('change');
        }
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
            if (nextWorkflowStatusData.available) {
            targetStatusDisplay.show();
                targetStatusBadge.text(nextWorkflowStatusData.name);
                targetStatusIdInput.val(nextWorkflowStatusData.id);
            targetStatusAlert.removeClass('alert-warning alert-danger alert-info').addClass('alert-success');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Moving to the next workflow step');
            } else if (nextAssessmentStatuses.length > 0) {
                // If no next status but step-2 statuses exist, use the first one
                targetStatusDisplay.show();
                targetStatusBadge.text(nextAssessmentStatuses[0].name);
                targetStatusIdInput.val(nextAssessmentStatuses[0].id);
                targetStatusAlert.removeClass('alert-warning alert-danger alert-info').addClass('alert-success');
                targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Moving to workflow step 2 (Under Assessment)');
            } else {
                warningMessage.html('<strong>Warning:</strong> No next workflow status configured. Please configure a status for workflow step 2 (Under Assessment) in Risk Statuses.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
                targetStatusIdInput.val('');
            }
        } else if (action === 'reject' || action === 'return') {
            // Show previous status for reject/return
            @php
                $currentRecordStep = $risk->getCurrentWorkflowStep();
                $previousStatusStep = $currentRecordStep !== null
                    ? mapRiskRecordWorkflowStepToStatusStep(max(1, (int) $currentRecordStep - 1))
                    : null;
                $previousStatus = null;
                if ($previousStatusStep) {
                    $previousStatus = \App\Models\RiskManagement\RiskStatus::forCompany()
                        ->active()
                        ->where('workflow_step', $previousStatusStep)
                        ->ordered()
                        ->first();
                }
            @endphp
            @if($previousStatus)
            targetStatusDisplay.show();
            targetStatusBadge.text('{{ addslashes($previousStatus->name) }}');
            targetStatusIdInput.val('{{ $previousStatus->id }}');
            targetStatusAlert.removeClass('alert-success alert-danger alert-info').addClass('alert-warning');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Returning to the previous workflow step');
            @else
            warningMessage.html('<strong>Warning:</strong> No previous status available for return/reject.');
            warningDiv.removeClass('alert-info').addClass('alert-warning').show();
            targetStatusIdInput.val('');
            @endif
            if (action === 'reject') {
                warningMessage.html('<strong>Rejection Note:</strong> Rejecting will return the risk to a previous status. Ensure all rejection reasons are documented.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
            } else {
                warningMessage.html('<strong>Return Note:</strong> Returning the risk requires correction. Document what needs to be corrected.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
            }
        } else if (action === 'hold') {
            // Show current status for hold
            targetStatusDisplay.show();
            targetStatusBadge.text('{{ $risk->status_name }}');
            targetStatusIdInput.val('{{ $risk->status_id }}');
            targetStatusAlert.removeClass('alert-success alert-warning alert-danger').addClass('alert-info');
            targetStatusDescription.html('<i class="mdi mdi-information-outline"></i> Status will remain unchanged');
            warningMessage.html('<strong>Hold Note:</strong> Holding suspends the risk workflow. Document the reason for suspension.');
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
        
        // Validate target status is set
        if (!targetStatus || targetStatus === '') {
            e.preventDefault();
            alert('Please select a target status. The target status should be automatically set based on your selected action. Please try selecting the action again.');
            $('#workflow_action').focus();
            return false;
        }
        
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
    
    // Validate Residual Risk Expected RPN in Add and Update Treatment Plan modals
    $(document).on('input change blur', '#addTreatmentPlanModal #residual_risk_expected, [id^="updateTreatmentPlanModal"] input[id^="residual_risk_expected"]', function() {
        const input = $(this);
        const enteredValue = parseInt(input.val());
        const currentRpn = parseInt(input.data('current-rpn')) || null;
        const maxValue = parseInt(input.attr('max')) || 25;
        const minValue = parseInt(input.attr('min')) || 1;
        
        // Remove existing invalid feedback
        input.next('.invalid-feedback').remove();
        input.removeClass('is-invalid');
        
        if (input.val() !== '' && !isNaN(enteredValue)) {
            // Check if value is less than minimum (1) or negative
            if (enteredValue < minValue || enteredValue < 1) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback">Residual RPN must be at least ' + minValue + '. You entered ' + enteredValue + '.</div>');
                // Clear the invalid value
                if (enteredValue < 1) {
                    input.val('');
                }
                return;
            }
            
            // Check if value exceeds current RPN
            if (currentRpn && enteredValue > currentRpn) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback">Residual RPN (' + enteredValue + ') cannot exceed the current RPN (' + currentRpn + ').</div>');
                return;
            }
            
            // Check if value exceeds max
            if (enteredValue > maxValue) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback">Residual RPN cannot exceed ' + maxValue + '.</div>');
                return;
            }
        } else if (input.val() !== '' && isNaN(enteredValue)) {
            // Invalid input (not a number)
            input.addClass('is-invalid');
            input.after('<div class="invalid-feedback">Please enter a valid number.</div>');
            return;
        }
    });
    
    // Clear validation when modals close
    $('#addTreatmentPlanModal, [id^="updateTreatmentPlanModal"]').on('hidden.bs.modal', function() {
        const modal = $(this);
        const input = modal.find('input[id^="residual_risk_expected"]');
        input.removeClass('is-invalid');
        input.next('.invalid-feedback').remove();
    });
    
    // Initialize TinyMCE for Add Treatment Plan modal - Initialize before modal opens
    $('#addTreatmentPlanModal').on('show.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            // Destroy existing instances if any
            modal.find('textarea.editor').each(function() {
                const textareaId = $(this).attr('id');
                if (textareaId) {
                    tinymce.remove('#' + textareaId);
                }
            });
            
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
        if (typeof tinymce !== 'undefined' && $('#addTreatmentPlanModal').length) {
            // Check if editors exist but aren't initialized yet
            $('#addTreatmentPlanModal textarea.editor').each(function() {
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
    
    // Initialize TinyMCE for Update Treatment Plan modals (dynamic)
    $(document).on('shown.bs.modal', '[id^="updateTreatmentPlanModal"]', function() {
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
    
    // Clean up TinyMCE when treatment plan modals are closed
    $('#addTreatmentPlanModal, [id^="updateTreatmentPlanModal"]').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            $(this).find('textarea.editor').each(function() {
                tinymce.remove('#' + $(this).attr('id'));
            });
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
        
        // Handle custom file input label for multiple file uploads
        $(document).on('change', 'input[type="file"][multiple]', function() {
            const input = $(this);
            const label = input.next('.custom-file-label');
            const files = this.files;
            
            if (files.length === 0) {
                label.text('{{ __("Choose files...") }}');
            } else if (files.length === 1) {
                label.text(files[0].name);
            } else {
                label.text(files.length + ' {{ __("files selected") }}');
            }
        });
        
        // Treatment Plan Row Toggle - Only toggle when clicking non-action elements
        $(document).on('click', '.treatment-plan-row', function(e) {
            // Don't toggle if clicked on actions cell or any button
            if ($(e.target).closest('.actions-cell').length > 0 || 
                $(e.target).closest('.btn-action').length > 0 ||
                $(e.target).closest('button').length > 0 ||
                $(e.target).is('button') ||
                $(e.target).is('i') && $(e.target).closest('.btn').length > 0) {
                return; // Let the button handle its own action
            }
            
            const planId = $(this).data('plan-id');
            const $detailRow = $('#planDetails' + planId);
            const $icon = $(this).find('.toggle-icon');
            
            // Toggle the collapse
            $detailRow.collapse('toggle');
            
            // Toggle icon rotation
            if ($detailRow.hasClass('show')) {
                $icon.css('transform', 'rotate(0deg)');
            } else {
                $icon.css('transform', 'rotate(180deg)');
            }
        });
        
        // Listen for collapse events to sync icon state
        $(document).on('shown.bs.collapse', '[id^="planDetails"]', function() {
            const planId = $(this).attr('id').replace('planDetails', '');
            $('[data-plan-id="' + planId + '"] .toggle-icon').css('transform', 'rotate(180deg)');
        });
        
        $(document).on('hidden.bs.collapse', '[id^="planDetails"]', function() {
            const planId = $(this).attr('id').replace('planDetails', '');
            $('[data-plan-id="' + planId + '"] .toggle-icon').css('transform', 'rotate(0deg)');
        });
        
        // Add hover effect to treatment plan rows
        $(document).on('mouseenter', '.treatment-plan-row', function() {
            $(this).css('background-color', '#f1f5f9');
        });
        $(document).on('mouseleave', '.treatment-plan-row', function() {
            $(this).css('background-color', '');
        });
    });
</script>

<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.evaluation.store', $risk->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-scale-balance"></i> {{ __('Risk Evaluation') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- RPN Reference Table -->
                    <div class="alert alert-light border mb-4" style="border-radius: var(--border-radius-sm);">
                        <h6 class="font-weight-bold mb-2"><i class="mdi mdi-information"></i> {{ __('RPN Ranges Reference') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Evaluation Result') }}</th>
                                        <th>{{ __('RPN Range') }}</th>
                                        <th>{{ __('Next Step') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluationResults as $result)
                                    @php
                                        $metadata = normalizeRiskConfigurationMetadata($result->metadata);
                                        $rpnMin = $metadata['rpn_min'] ?? 'N/A';
                                        $rpnMax = $metadata['rpn_max'] ?? 'N/A';
                                        $workflowStep = isset($metadata['workflow_step']) ? (int) $metadata['workflow_step'] : null;
                                        $workflowSteps = getRiskWorkflowSteps();
                                        $nextStepName = $workflowStep && isset($workflowSteps[$workflowStep]) ? $workflowSteps[$workflowStep] : '-';
                                        $badgeClass = strtolower($result->code) === 'unacceptable' ? 'danger' : (strtolower($result->code) === 'tolerable' ? 'warning' : 'success');
                                    @endphp
                                    <tr>
                                        <td><span class="badge badge-{{ $badgeClass }}">{{ $result->name }}</span></td>
                                        <td>{{ $rpnMin }} - {{ $rpnMax }}</td>
                                        <td>{{ $nextStepName }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RPN Input Field (Primary) -->
                    <div class="form-group">
                        <label for="rpn" class="font-weight-600">{{ __('Enter RPN Value') }} <span class="text-danger">*</span></label>
                        @php
                            $currentRpnValue = $risk->currentEvaluation ? $risk->currentEvaluation->risk_score : null;
                        @endphp
                        <input type="number" 
                               class="form-control form-control-lg @error('rpn') is-invalid @enderror" 
                               id="rpn" 
                               name="rpn" 
                               min="1"
                               required 
                               value="{{ $currentRpnValue ?? '' }}"
                               data-initial-value="{{ $currentRpnValue ?? '' }}"
                               style="border-radius: var(--border-radius-sm); font-size: 1.25rem; font-weight: 600;" 
                               placeholder="{{ __('Enter RPN value...') }}">
                        @error('rpn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">
                            {{ __('Enter the Risk Priority Number. The evaluation result will be automatically determined based on this value.') }}
                        </small>
                    </div>
                    
                    <!-- Auto-determined Evaluation Result Display -->
                    <div id="auto_evaluation_result_display" class="alert" style="display: none; margin-top: 1rem; border-radius: var(--border-radius-sm);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <strong><i class="mdi mdi-scale-balance"></i> {{ __('Evaluation Result:') }}</strong>
                                <span id="auto_evaluation_result_text" class="ml-2 badge badge-lg" style="font-size: 1rem; padding: 0.5rem 1rem;"></span>
                            </div>
                            <div id="auto_workflow_step_text" class="text-muted" style="font-size: 0.875rem;"></div>
                        </div>
                    </div>
                    
                    <!-- Hidden Evaluation Result (auto-filled) -->
                    <input type="hidden" id="evaluation_result" name="evaluation_result" value="">
                    
                    <!-- Required Actions Display -->
                    <div id="evaluation_actions" class="alert alert-info" style="display: none; margin-top: 1rem; border-radius: var(--border-radius-sm);">
                        <strong><i class="mdi mdi-information-outline"></i> {{ __('Required Actions:') }}</strong>
                        <ul id="actions_list" class="mb-0 mt-2" style="padding-left: 1.5rem;"></ul>
                    </div>
                    
                    <!-- Invalid RPN Message -->
                    <div id="rpn_validation_message" class="alert alert-danger" style="display: none; margin-top: 1rem; border-radius: var(--border-radius-sm);"></div>
                    <div class="form-group">
                        <label for="evaluation_notes" class="font-weight-600">{{ __('Evaluation Notes') }}</label>
                        <textarea class="form-control @error('evaluation_notes') is-invalid @enderror" id="evaluation_notes" name="evaluation_notes" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter evaluation notes...') }}">{{ $risk->evaluation_notes ?? '' }}</textarea>
                        @error('evaluation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="evaluated_by_user_id" class="font-weight-600">{{ __('Evaluated By') }}</label>
                                <select class="form-control @error('evaluated_by_user_id') is-invalid @enderror" id="evaluated_by_user_id" name="evaluated_by_user_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Evaluator...') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ Auth::id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('evaluated_by_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div id="escalation_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="escalated_to_user_id" class="font-weight-600">{{ __('Escalate To') }}</label>
                                    <select class="form-control @error('escalated_to_user_id') is-invalid @enderror" id="escalated_to_user_id" name="escalated_to_user_id" style="border-radius: var(--border-radius-sm);">
                                        <option value="">{{ __('Select User...') }}</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('escalated_to_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="escalation_reason" class="font-weight-600">{{ __('Escalation Reason') }}</label>
                            <textarea class="form-control @error('escalation_reason') is-invalid @enderror" id="escalation_reason" name="escalation_reason" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter reason for escalation...') }}"></textarea>
                            @error('escalation_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Save Evaluation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Treatment Plan Modal -->
<div class="modal fade" id="addTreatmentPlanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.treatment-plan.store', $risk->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-clipboard-list"></i> {{ __('Add Treatment Plan') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="treatment_type_id" class="font-weight-600">{{ __('Treatment Type') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('treatment_type_id') is-invalid @enderror" id="treatment_type_id" name="treatment_type_id" required style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Treatment Type...') }}</option>
                                    @foreach($treatmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('treatment_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="font-weight-600">{{ __('Priority') }}</label>
                                <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Priority...') }}</option>
                                    @foreach(getTreatmentPriorities() as $priority)
                                    <option value="{{ $priority->code }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title" class="font-weight-600">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required maxlength="255" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter treatment plan title...') }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="description" class="font-weight-600">{{ __('Description') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control editor @error('description') is-invalid @enderror" id="description" name="description" rows="4" required style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter detailed description of the treatment plan...') }}"></textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsible_user_id" class="font-weight-600">{{ __('Responsible Person') }}</label>
                                <select class="form-control @error('responsible_user_id') is-invalid @enderror" id="responsible_user_id" name="responsible_user_id" style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Responsible Person...') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('responsible_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department" class="font-weight-600">{{ __('Department') }}</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter department...') }}">
                                @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_completion_date" class="font-weight-600">{{ __('Target Completion Date') }}</label>
                                <input type="date" class="form-control @error('target_completion_date') is-invalid @enderror" id="target_completion_date" name="target_completion_date" style="border-radius: var(--border-radius-sm);">
                                @error('target_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="implementation_status" class="font-weight-600">{{ __('Implementation Status') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('implementation_status') is-invalid @enderror" id="implementation_status" name="implementation_status" required style="border-radius: var(--border-radius-sm);">
                                    @foreach(getImplementationStatuses() as $status)
                                    <option value="{{ $status->code }}" {{ $status->code === 'planned' ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                @error('implementation_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="implementation_start_date" class="font-weight-600">{{ __('Implementation Start Date') }}</label>
                                <input type="date" class="form-control @error('implementation_start_date') is-invalid @enderror" id="implementation_start_date" name="implementation_start_date" style="border-radius: var(--border-radius-sm);">
                                @error('implementation_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="implementation_end_date" class="font-weight-600">{{ __('Implementation End Date') }}</label>
                                <input type="date" class="form-control @error('implementation_end_date') is-invalid @enderror" id="implementation_end_date" name="implementation_end_date" style="border-radius: var(--border-radius-sm);">
                                @error('implementation_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="actual_completion_date" class="font-weight-600">{{ __('Actual Completion Date') }}</label>
                                <input type="date" class="form-control @error('actual_completion_date') is-invalid @enderror" id="actual_completion_date" name="actual_completion_date" style="border-radius: var(--border-radius-sm);">
                                @error('actual_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="control_measures" class="font-weight-600">{{ __('Control Measures') }}</label>
                        <textarea class="form-control editor @error('control_measures') is-invalid @enderror" id="control_measures" name="control_measures" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the control measures to be implemented...') }}"></textarea>
                        @error('control_measures') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="expected_outcome" class="font-weight-600">{{ __('Expected Outcome') }}</label>
                        <textarea class="form-control editor @error('expected_outcome') is-invalid @enderror" id="expected_outcome" name="expected_outcome" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe the expected outcome...') }}"></textarea>
                        @error('expected_outcome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="resources_required" class="font-weight-600">{{ __('Resources Required') }}</label>
                        <textarea class="form-control editor @error('resources_required') is-invalid @enderror" id="resources_required" name="resources_required" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Describe resources required (budget, staff, equipment, etc.)...') }}"></textarea>
                        @error('resources_required') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="residual_risk_expected" class="font-weight-600">{{ __('Residual Risk Expected (RPN)') }}</label>
                                @php
                                    // Get current RPN from assessment
                                    $likelihoodScore = $risk->likelihoodScale->score ?? $risk->likelihood_score ?? null;
                                    $severityScore = $risk->severityScale->score ?? $risk->severity_score ?? null;
                                    $currentRpn = null;
                                    if ($likelihoodScore && $severityScore) {
                                        $currentRpn = $likelihoodScore * $severityScore;
                                    } elseif ($risk->rpn) {
                                        $currentRpn = $risk->rpn;
                                    }
                                @endphp
                                @if($currentRpn)
                                <div class="alert alert-info mb-2" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                                    <i class="mdi mdi-information-outline"></i> <strong>{{ __('Current RPN:') }}</strong> {{ $currentRpn }}
                                </div>
                                @endif
                                <input type="number" 
                                       class="form-control @error('residual_risk_expected') is-invalid @enderror" 
                                       id="residual_risk_expected" 
                                       name="residual_risk_expected" 
                                       min="1" 
                                       max="{{ $currentRpn ? $currentRpn : 25 }}"
                                       step="1"
                                       oninput="if(this.value < 1) { this.value = ''; this.setCustomValidity('Residual RPN must be at least 1'); } else { this.setCustomValidity(''); }"
                                       onkeydown="return event.key !== '-' && event.key !== '+'" 
                                       style="border-radius: var(--border-radius-sm);" 
                                       placeholder="{{ __('Estimated RPN after treatment') }}"
                                       @if($currentRpn) data-current-rpn="{{ $currentRpn }}" @endif>
                                @error('residual_risk_expected') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">
                                    {{ __('Estimated Risk Priority Number after implementing this treatment plan') }}
                                    @if($currentRpn)
                                        <br><strong>{{ __('Note:') }}</strong> {{ __('Residual RPN should be less than or equal to the current RPN (') }}{{ $currentRpn }}{{ __(')') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="implementation_notes" class="font-weight-600">{{ __('Implementation Notes') }}</label>
                        <textarea class="form-control editor @error('implementation_notes') is-invalid @enderror" id="implementation_notes" name="implementation_notes" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter implementation notes...') }}"></textarea>
                        @error('implementation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Save Treatment Plan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assessment Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.assessment.store', $risk->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-clipboard-check"></i>
                        @php
                            $assessmentModalHasData = ($risk->likelihood_score && $risk->severity_score)
                                || ($risk->likelihood_scale_id && $risk->severity_scale_id);
                        @endphp
                        {{ $assessmentModalHasData ? __('Edit Assessment') : __('Add Assessment') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="likelihood_scale_id" class="font-weight-600">{{ __('Likelihood') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('likelihood_scale_id') is-invalid @enderror" id="likelihood_scale_id" name="likelihood_scale_id" required style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Likelihood...') }}</option>
                                    @foreach($likelihoodScales as $scale)
                                    <option value="{{ $scale->id }}" data-score="{{ $scale->score }}" {{ $risk->likelihood_scale_id == $scale->id ? 'selected' : '' }}>
                                        {{ $scale->name }} (Score: {{ $scale->score }})
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="likelihood_score" name="likelihood_score" value="{{ $risk->likelihood_score ?? '' }}">
                                @error('likelihood_scale_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @error('likelihood_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="severity_scale_id" class="font-weight-600">{{ __('Severity') }} <span class="text-danger">*</span></label>
                                <select class="form-control @error('severity_scale_id') is-invalid @enderror" id="severity_scale_id" name="severity_scale_id" required style="border-radius: var(--border-radius-sm);">
                                    <option value="">{{ __('Select Severity...') }}</option>
                                    @foreach($severityScales as $scale)
                                    <option value="{{ $scale->id }}" data-score="{{ $scale->score }}" {{ $risk->severity_scale_id == $scale->id ? 'selected' : '' }}>
                                        {{ $scale->name }} (Score: {{ $scale->score }})
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="severity_score" name="severity_score" value="{{ $risk->severity_score ?? '' }}">
                                @error('severity_scale_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @error('severity_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="assessment_notes" class="font-weight-600">{{ __('Assessment Notes') }}</label>
                        <textarea class="form-control @error('assessment_notes') is-invalid @enderror" id="assessment_notes" name="assessment_notes" rows="4" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter assessment notes...') }}">{{ $risk->assessment_notes ?? '' }}</textarea>
                        @error('assessment_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assessed_by_user_id" class="font-weight-600">{{ __('Assessed By') }}</label>
                                <select class="form-control select2 @error('assessed_by_user_id') is-invalid @enderror" id="assessed_by_user_id" name="assessed_by_user_id[]" multiple="multiple" data-placeholder="{{ __('Select Assessor(s)...') }}" style="border-radius: var(--border-radius-sm); width: 100%;">
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (is_array(old('assessed_by_user_id')) && in_array($user->id, old('assessed_by_user_id'))) || (isset($risk) && $risk->currentAssessment && $risk->currentAssessment->assessed_by_user_id == $user->id) || (!old('assessed_by_user_id') && Auth::id() == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('assessed_by_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    @php
                        // Get scores from current assessment or risk model, with fallback to scale score
                        $likelihoodScore = $risk->likelihood_score ?? ($risk->likelihoodScale->score ?? null);
                        $severityScore = $risk->severity_score ?? ($risk->severityScale->score ?? null);
                        $calculatedRpn = $likelihoodScore && $severityScore ? ($likelihoodScore * $severityScore) : null;
                        $displayRpn = $risk->rpn ?? $calculatedRpn;
                    @endphp
                    @if($likelihoodScore && $severityScore)
                    <div class="form-group">
                        <label for="reassessment_reason" class="font-weight-600">{{ __('Reassessment Reason') }}</label>
                        <textarea class="form-control @error('reassessment_reason') is-invalid @enderror" id="reassessment_reason" name="reassessment_reason" rows="3" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter reason for reassessment (if applicable)...') }}"></textarea>
                        @error('reassessment_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">{{ __('Required if this is a reassessment of an existing risk') }}</small>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                        <i class="mdi mdi-content-save"></i> {{ __('Save Assessment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-update scores when scales are selected
    $(document).ready(function() {
        function updateLikelihoodScore() {
            var selected = $('#likelihood_scale_id').find(':selected');
            var score = selected.data('score');
            $('#likelihood_score').val(score || '');
        }

        function updateSeverityScore() {
            var selected = $('#severity_scale_id').find(':selected');
            var score = selected.data('score');
            $('#severity_score').val(score || '');
        }

        // Initialize on load
        updateLikelihoodScore();
        updateSeverityScore();

        // Listen for changes (works with Select2 too)
        $('#likelihood_scale_id').on('change', updateLikelihoodScore);
        $('#severity_scale_id').on('change', updateSeverityScore);
    });
</script>

<!-- Add Process Link Modal -->
<div class="modal fade" id="addProcessLinkModal" tabindex="-1" role="dialog" aria-labelledby="addProcessLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProcessLinkModalLabel">{{ __('Link Business Process') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('risk.risks.process-links.store', $risk->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="business_process_id" class="font-weight-600">{{ __('Business Process') }} <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="business_process_id" name="business_process_id" required style="width: 100%;">
                            <option value="">{{ __('Select Process...') }}</option>
                            @foreach(\App\Models\RiskManagement\RiskBusinessProcess::forCompany()->orderBy('name')->get() as $process)
                                <option value="{{ $process->id }}">{{ $process->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="process_description" class="font-weight-600">{{ __('Description') }}</label>
                        <textarea class="form-control" id="process_description" name="description" rows="3" placeholder="{{ __('Describe how this risk relates to the process...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Link Process') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

    });
</script>

<!-- Edit Process Link Modal -->
<div class="modal fade" id="editProcessLinkModal" tabindex="-1" role="dialog" aria-labelledby="editProcessLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProcessLinkModalLabel">{{ __('Edit Process Link') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProcessLinkForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_business_process_id" class="font-weight-600">{{ __('Business Process') }} <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="edit_business_process_id" name="business_process_id" required style="width: 100%;">
                            <option value="">{{ __('Select Process...') }}</option>
                            @foreach(\App\Models\RiskManagement\RiskBusinessProcess::forCompany()->orderBy('name')->get() as $process)
                                <option value="{{ $process->id }}">{{ $process->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_process_description" class="font-weight-600">{{ __('Description') }}</label>
                        <textarea class="form-control" id="edit_process_description" name="description" rows="3" placeholder="{{ __('Describe how this risk relates to the process...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update Link') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Select2 in the modal
        $('#addProcessLinkModal').on('shown.bs.modal', function () {
            $('#business_process_id').select2({
                dropdownParent: $('#addProcessLinkModal'),
                placeholder: "{{ __('Select Process...') }}",
                width: '100%'
            });
        });
        
        // Initialize Select2 in edit modal
        $('#editProcessLinkModal').on('shown.bs.modal', function () {
            $('#edit_business_process_id').select2({
                dropdownParent: $('#editProcessLinkModal'),
                placeholder: "{{ __('Select Process...') }}",
                width: '100%'
            });
        });

        // Handle edit button click
        $('.edit-process-link-btn').on('click', function() {
            var id = $(this).data('id');
            var processId = $(this).data('process');
            var description = $(this).data('description');
            var updateUrl = "{{ route('risk.risks.process-links.update', ':id') }}".replace(':id', id);
            
            $('#editProcessLinkForm').attr('action', updateUrl);
            $('#edit_business_process_id').val(processId).trigger('change');
            $('#edit_process_description').val(description);
            
            $('#editProcessLinkModal').modal('show');
        });

        // Handle delete with confirmation (simple version)
        $('.delete-process-link-btn').on('click', function(e) {
            e.preventDefault();
            if(confirm("{{ __('Are you sure you want to remove this linked process?') }}")) {
                $(this).closest('form').submit();
            }
        });
    });
</script>
@endsection

