@extends('layouts.risk.layout.app')

@section('title2')
<title>Treatment Plan - {{ $treatmentPlan->title }} - JASIRI LIMS</title>
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

    .treatment-plan-show-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
    }

    .treatment-plan-header-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        background: #ffffff;
        margin-bottom: 1.5rem;
    }

    .treatment-plan-header-card .card-header {
        background: var(--light-bg);
        border: none;
        border-left: 6px solid var(--primary-color);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .treatment-plan-header-card .card-body {
        background: #ffffff;
        padding: 1.5rem 2rem;
    }

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
    }

    .detail-item:last-child {
        border-bottom: none;
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

    .detail-text-content.empty {
        color: #94a3b8;
        font-style: italic;
    }

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
    }

    .table-modern tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.15s ease;
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
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-modern.btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-modern.btn-secondary {
        background: #64748b;
        color: white;
    }

    .section-header {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 1.25rem;
        width: 2px;
        height: calc(100% - 0.5rem);
        background: #e5e7eb;
    }

    .timeline-item:last-child::after {
        display: none;
    }
</style>
@endsection

@section('content2')
<main>
    @php
        $items = [
            [
                'link' => route('risk.risks.index'),
                'name' => 'Risk Management',
                'icon' => null
            ],
            [
                'link' => route('risk.risks.show', $risk->id),
                'name' => $risk->risk_number,
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => 'Treatment Plan',
                'icon' => null
            ]
        ];
        $isClosed = $risk->workflow_step == 8;

        // Add status badge like in risk show blade
        $statusClass = match($risk->status_name) {
            'Closed' => 'success',
            'Monitored', 'Risk Monitoring' => 'info',
            'Identified' => 'warning',
            'Treatment Planning', 'Treatment Implementation' => 'primary',
            default => 'secondary'
        };
        
        // Update the last item to show Status
        $items[count($items) - 1]['name'] = 'Treatment Plan <span class="badge badge-modern badge-'.$statusClass.' ml-2" style="font-size: 0.875rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center;">'.($risk->status_name ?? getWorkflowStepName($risk->workflow_step)).'</span>';
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card treatment-plan-header-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-1" style="font-size: 1.35rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; margin: 0;">
                            <i class="mdi mdi-clipboard-list text-primary"></i> {{ $treatmentPlan->title }}
                        </h4>
                        @if(!$isClosed)
                        <a href="{{ route('risk.risks.show', $risk->id) }}#treatment-plans" class="btn btn-outline-warning" style="border-radius: 8px; padding: 0.5rem; font-weight: 500; border: 1.5px solid #f59e0b; color: #1e293b; background: transparent; transition: all 0.2s ease; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-top: -2px;" title="{{ __('Edit Treatment Plan') }}">
                            <i class="mdi mdi-pencil" style="font-size: 1.125rem;"></i>
                        </a>
                        @endif
                    </div>
                    <p class="mb-0 text-muted mt-1">{{ __('Treatment Plan for Risk') }}: {{ $risk->risk_number }}</p>
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
            <!-- Details Grid -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-tag"></i>
                        Treatment Type
                    </div>
                    <div class="detail-value">
                        {{ $treatmentPlan->treatmentType->name ?? $treatmentPlan->treatment_type_name ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-check-circle"></i>
                        Status
                    </div>
                    <div class="detail-value">
                        <span class="badge badge-{{ $treatmentPlan->implementation_status === 'Completed' ? 'success' : ($treatmentPlan->implementation_status === 'In Progress' ? 'info' : 'secondary') }}">
                            {{ $treatmentPlan->implementation_status ?? 'Planned' }}
                        </span>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-account"></i>
                        Responsible Person
                    </div>
                    <div class="detail-value">
                        {{ $treatmentPlan->responsible_person ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-office-building"></i>
                        Department
                    </div>
                    <div class="detail-value">
                        {{ $treatmentPlan->department ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="mdi mdi-calendar-clock"></i>
                        Target Completion Date
                    </div>
                    <div class="detail-value">
                        {{ $treatmentPlan->target_completion_date ? $treatmentPlan->target_completion_date->format('M d, Y') : 'Not set' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Card -->
    <div class="card treatment-plan-show-card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#details" role="tab">
                        <i class="mdi mdi-information-outline"></i> {{ __('Details') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#implementation" role="tab">
                        <i class="mdi mdi-cog"></i> {{ __('Implementation') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#attachments" role="tab">
                        <i class="mdi mdi-paperclip"></i> {{ __('Attachments') }}
                        @if($treatmentPlan->attachments && $treatmentPlan->attachments->count() > 0)
                        <span class="badge badge-info ml-1">{{ $treatmentPlan->attachments->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#activity" role="tab">
                        <i class="mdi mdi-history"></i> {{ __('Activity Log') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#approvals" role="tab">
                        <i class="mdi mdi-check-all"></i> {{ __('Approval History') }}
                        @if(isset($approvals) && $approvals->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $approvals->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="mdi mdi-text"></i>
                            Description
                        </div>
                        <div class="detail-text-content {{ empty($treatmentPlan->description) ? 'empty' : '' }}">
                            {!! $treatmentPlan->description ?? '<em>No description provided</em>' !!}
                        </div>
                    </div>

                    @if($treatmentPlan->control_measures)
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="mdi mdi-shield-check"></i>
                            Control Measures
                        </div>
                        <div class="detail-text-content">
                            {!! $treatmentPlan->control_measures !!}
                        </div>
                    </div>
                    @endif

                    @if($treatmentPlan->expected_outcome)
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="mdi mdi-target"></i>
                            Expected Outcome
                        </div>
                        <div class="detail-text-content">
                            {!! $treatmentPlan->expected_outcome !!}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Implementation Tab -->
                <div class="tab-pane fade" id="implementation" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-cog text-primary"></i> {{ __('Implementation Details') }}
                        </h6>
                        @php
                            $completedStatusCodes = getImplementationStatuses()->where('code', 'completed')->pluck('code')->toArray();
                            $completedStatusNames = getImplementationStatuses()->where('code', 'completed')->pluck('name')->toArray();
                            $isCompleted = in_array($treatmentPlan->implementation_status, array_merge($completedStatusCodes, $completedStatusNames)) || 
                                          strtolower($treatmentPlan->implementation_status ?? '') === 'completed';
                            // Visible only if risk is in Treatment Implementation step (Step 6)
                            $canRecordImplementation = !$isClosed && !$isCompleted && $risk->workflow_step == 6;
                        @endphp
                        @if($canRecordImplementation)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#recordImplementationModal">
                            <i class="mdi mdi-plus"></i> {{ __('Record Implementation') }}
                        </button>
                        @endif
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="mdi mdi-cog"></i>
                            Implementation Details
                        </div>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="mdi mdi-calendar-start"></i>
                                    Start Date
                                </div>
                                <div class="detail-value {{ empty($treatmentPlan->implementation_start_date) ? 'empty' : '' }}">
                                    {{ $treatmentPlan->implementation_start_date ? $treatmentPlan->implementation_start_date->format('M d, Y') : 'Not set' }}
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="mdi mdi-calendar-end"></i>
                                    End Date
                                </div>
                                <div class="detail-value {{ empty($treatmentPlan->implementation_end_date) ? 'empty' : '' }}">
                                    {{ $treatmentPlan->implementation_end_date ? $treatmentPlan->implementation_end_date->format('M d, Y') : 'Not set' }}
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="mdi mdi-calendar-check"></i>
                                    Actual Completion Date
                                </div>
                                <div class="detail-value {{ empty($treatmentPlan->actual_completion_date) ? 'empty' : '' }}">
                                    {{ $treatmentPlan->actual_completion_date ? $treatmentPlan->actual_completion_date->format('M d, Y') : 'Not set' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($treatmentPlan->implementation_notes)
                        <div class="mt-3">
                            <div class="detail-label">
                                <i class="mdi mdi-note-text"></i>
                                Implementation Notes
                            </div>
                            <div class="detail-text-content">
                                {!! $treatmentPlan->implementation_notes !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Attachments Tab -->
                <div class="tab-pane fade" id="attachments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h6 class="section-header mb-0">
                            <i class="mdi mdi-paperclip text-primary"></i> {{ __('Attachments') }}
                        </h6>
                        @if(!$isClosed)
                        <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#uploadAttachmentModal">
                            <i class="mdi mdi-upload"></i> {{ __('Upload Attachment') }}
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
                                    @if($treatmentPlan->attachments && $treatmentPlan->attachments->count() > 0)
                                    <th style="min-width: 150px; text-align: center;">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($treatmentPlan->attachments && $treatmentPlan->attachments->count() > 0)
                                    @foreach($treatmentPlan->attachments as $attachment)
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
                                            <span style="color: #334155;">{{ $attachment->uploadedBy?->name ?? __('N/A') }}</span>
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
                                                <form action="{{ route('risk.risks.treatment-plan.attachments.delete', $attachment->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this attachment?');">
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
                                        <td colspan="7" style="text-align: center; padding: 3rem 1rem;">
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

                <!-- Activity Log Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    @if($treatmentPlan->activityLogs && $treatmentPlan->activityLogs->count() > 0)
                    <!-- Activity Logs -->
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
                        <div class="card-header bg-light" style="border-left: 6px solid #6c757d;">
                            <h5 class="mb-0">
                                <i class="mdi mdi-history text-secondary"></i> All Activity Logs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach(($treatmentPlan->activityLogs ?? collect())->sortByDesc('created_at') as $log)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="mr-3">
                                            @php
                                                $logClass = match($log->action ?? '') {
                                                    'Created' => 'success',
                                                    'Status Changed' => 'info',
                                                    'Workflow Transition' => 'primary',
                                                    'Treatment Plan Created' => 'success',
                                                    'Treatment Plan Updated' => 'info',
                                                    'Implementation Recorded' => 'success',
                                                    'Attachment Uploaded' => 'primary',
                                                    'Attachment Deleted' => 'warning',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-modern badge-{{ $logClass }}">
                                                {{ $log->action ?? 'Activity' }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1" style="font-weight: 500; color: var(--text-primary);">{{ $log->description ?? 'No description' }}</p>
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
                <div class="tab-pane fade" id="approvals" role="tabpanel">
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
                                        <span class="badge badge-primary">{{ $approval->to_status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs mr-2" style="width: 30px; height: 30px; background-color: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                {{ substr($approval->approver_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-600">{{ $approval->approver_name }}</div>
                                                <small class="text-muted">{{ $approval->role_type ?? 'Approver' }}</small>
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
                        <i class="mdi mdi-clipboard-check-outline" style="font-size: 4rem; color: #e2e8f0;"></i>
                        <p class="mt-3 text-muted">{{ __('No approval history recorded yet.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Record Implementation Modal -->
    <div class="modal fade" id="recordImplementationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('risk.risks.treatment-plan.update', ['riskId' => $risk->id, 'treatmentPlanId' => $treatmentPlan->id]) }}?from_treatment_plan_page=1" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_treatment_plan_page" value="1">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-cog"></i> {{ __('Record Implementation') }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="treatment_type_id" value="{{ $treatmentPlan->treatment_type_id }}">
                        <input type="hidden" name="title" value="{{ $treatmentPlan->title }}">
                        <input type="hidden" name="description" value="{{ $treatmentPlan->description }}">
                        <input type="hidden" name="control_measures" value="{{ $treatmentPlan->control_measures }}">
                        <input type="hidden" name="expected_outcome" value="{{ $treatmentPlan->expected_outcome }}">
                        <input type="hidden" name="responsible_user_id" value="{{ $treatmentPlan->responsible_user_id }}">
                        <input type="hidden" name="department" value="{{ $treatmentPlan->department }}">
                        <input type="hidden" name="target_completion_date" value="{{ $treatmentPlan->target_completion_date ? $treatmentPlan->target_completion_date->format('Y-m-d') : '' }}">
                        
                        <div class="form-group">
                            <label for="implementation_status" class="font-weight-600">{{ __('Implementation Status') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('implementation_status') is-invalid @enderror" id="implementation_status" name="implementation_status" required style="border-radius: var(--border-radius-sm);">
                                @foreach(getImplementationStatuses() as $status)
                                <option value="{{ $status->code }}" {{ ($treatmentPlan->implementation_status === $status->code || $treatmentPlan->implementation_status === $status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                            @error('implementation_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="implementation_start_date" class="font-weight-600">{{ __('Implementation Start Date') }}</label>
                                    <input type="date" class="form-control @error('implementation_start_date') is-invalid @enderror" id="implementation_start_date" name="implementation_start_date" value="{{ $treatmentPlan->implementation_start_date ? $treatmentPlan->implementation_start_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                    @error('implementation_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="implementation_end_date" class="font-weight-600">{{ __('Implementation End Date') }}</label>
                                    <input type="date" class="form-control @error('implementation_end_date') is-invalid @enderror" id="implementation_end_date" name="implementation_end_date" value="{{ $treatmentPlan->implementation_end_date ? $treatmentPlan->implementation_end_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                    @error('implementation_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="actual_completion_date" class="font-weight-600">{{ __('Actual Completion Date') }}</label>
                                    <input type="date" class="form-control @error('actual_completion_date') is-invalid @enderror" id="actual_completion_date" name="actual_completion_date" value="{{ $treatmentPlan->actual_completion_date ? $treatmentPlan->actual_completion_date->format('Y-m-d') : '' }}" style="border-radius: var(--border-radius-sm);">
                                    @error('actual_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="implementation_notes" class="font-weight-600">{{ __('Implementation Notes') }}</label>
                            <textarea class="form-control editor @error('implementation_notes') is-invalid @enderror" id="implementation_notes" name="implementation_notes" rows="6" style="border-radius: var(--border-radius-sm);" placeholder="{{ __('Enter implementation notes...') }}">{{ $treatmentPlan->implementation_notes ?? '' }}</textarea>
                            @error('implementation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: var(--border-radius-sm);">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" style="border-radius: var(--border-radius-sm);">
                            <i class="mdi mdi-content-save"></i> {{ __('Save Implementation') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Workflow Action Modal -->
    @if(!$isClosed)
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
                                <br><small class="text-muted">Treatment Plan: <strong>{{ $treatmentPlan->title }}</strong></small>
                                <br><small class="text-muted">Current Risk Status: <strong>{{ $risk->status_name }}</strong></small>
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
                                            @if(isset($nextWorkflowStatus) && $nextWorkflowStatus)
                                                {{ $nextWorkflowStatus->name }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                    <small class="d-block mt-1 text-muted" id="target_status_description">
                                        <i class="mdi mdi-information-outline"></i> Moving to next workflow step
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
                                      placeholder="Enter detailed remarks for this workflow action. This is required for ISO compliance and audit trail purposes..." 
                                      required style="border-radius: var(--border-radius-sm);"></textarea>
                            <small class="form-text text-muted">
                                <i class="mdi mdi-alert-circle-outline"></i> 
                                Minimum 10 characters required. Document the reason for this workflow action.
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
</main>
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    // Initialize TinyMCE for Record Implementation modal
    $('#recordImplementationModal').on('shown.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            const modal = $(this);
            const textareaId = 'implementation_notes';
            
            // Destroy existing instance if any
            tinymce.remove('#' + textareaId);
            
            // Initialize TinyMCE
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
    
    // Clean up TinyMCE when modal is closed
    $('#recordImplementationModal').on('hidden.bs.modal', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#implementation_notes');
        }
    });
    
    // Store next workflow status data
    var nextWorkflowStatusData = {
        @if(isset($nextWorkflowStatus) && $nextWorkflowStatus)
        id: {{ $nextWorkflowStatus->id }},
        name: '{{ addslashes($nextWorkflowStatus->name) }}',
        available: true
        @else
        available: false
        @endif
    };
    
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
            } else {
                warningMessage.html('<strong>Warning:</strong> No next workflow status configured. Please configure workflow step statuses in the system settings.');
                warningDiv.removeClass('alert-info').addClass('alert-warning').show();
                targetStatusIdInput.val('');
            }
        } else if (action === 'reject' || action === 'return') {
            // For reject/return, we'll need to get previous status
            // For now, hide target status display
            targetStatusDisplay.hide();
            warningMessage.html('<strong>Note:</strong> This action will return the risk to the previous workflow step.');
            warningDiv.removeClass('alert-danger alert-info').addClass('alert-warning').show();
        } else if (action === 'hold') {
            // For hold, no status change
            targetStatusDisplay.hide();
            warningMessage.html('<strong>Note:</strong> This action will suspend the risk workflow without changing the status.');
            warningDiv.removeClass('alert-danger alert-success').addClass('alert-info').show();
        }
    });
    
    // Form validation
    $('#workflowActionForm').on('submit', function(e) {
        const action = $('#workflow_action').val();
        const remarks = $('#remarks').val().trim();
        const targetStatusId = $('#target_status_id').val();
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        if (remarks.length < 10) {
            e.preventDefault();
            alert('Remarks must be at least 10 characters long for ISO compliance.');
            return false;
        }
        
        if (action === 'approve' && !targetStatusId) {
            e.preventDefault();
            alert('Please configure the next workflow status before approving.');
            return false;
        }
        
        return true;
    });
    
    // Image preview modal
    $('#viewImageModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const imageUrl = button.data('image-url');
        const imageName = button.data('image-name');
        const modal = $(this);
        modal.find('#modalImage').attr('src', imageUrl);
        modal.find('#imageModalTitle').html('<i class="mdi mdi-image"></i> ' + imageName);
    });
</script>

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('risk.risks.treatment-plan.attachments.upload', ['riskId' => $risk->id, 'treatmentPlanId' => $treatmentPlan->id]) }}?from_treatment_plan_page=1" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="from_treatment_plan_page" value="1">
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
@endsection
