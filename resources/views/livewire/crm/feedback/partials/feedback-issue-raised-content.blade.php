@php $feedback = $feedback ?? null; @endphp
@if($feedback && $feedback->complaint)

    @php
        if (!$feedback->relationLoaded('complaint.resolutions')) {
            $feedback->load('complaint.resolutions');
        }
        $resolution = $feedback->complaint->resolutions->first();
    @endphp

    {{-- Who Raised the Issue --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#e0f2fe;flex-shrink:0;">
            <i class="mdi mdi-account-group-outline text-primary" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Issue Raised By</span>
    </div>
    <div class="row mb-4 px-1">
        <div class="col-md-12 mb-2">
            <p class="mb-0 text-muted"
                style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Issue Submitter</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                {{ $feedback->getSubmitterNameAttribute() }}
                @if($feedback->contact && $feedback->contact->title)
                    ({{ $feedback->contact->title }})
                @endif
            </p>
        </div>
    </div>

    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

    {{-- Issue Description --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#fef3c7;flex-shrink:0;">
            <i class="mdi mdi-file-document-outline text-warning" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Issue Description</span>
    </div>
    <div class="px-1 mb-4">
        @if($feedback->complaint && filled($feedback->complaint->description))
            <div class="p-3 rounded border" style="background:#fafbfd;">
                <p class="mb-0 text-dark" style="font-size:0.85rem;line-height:1.6;white-space: pre-wrap;">{{ strip_tags($feedback->complaint->description) }}</p>
            </div>
        @else
            <div class="text-muted" style="font-size:0.8rem;">No issue description available</div>
        @endif
    </div>

    {{-- Cause of Complaint --}}
    @if($resolution && filled($resolution->cause_of_complaint))
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#fee2e2;flex-shrink:0;">
                <i class="mdi mdi-magnify text-danger" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Cause of Complaint</span>
        </div>
        <div class="px-1 mb-4">
            <div class="p-3 rounded border" style="background:#fef2f2;border-color:#fca5a5 !important;">
                <p class="mb-2 text-dark" style="font-size:0.82rem;line-height:1.6;white-space: pre-wrap;">{!! html_entity_decode($resolution->cause_of_complaint) !!}</p>
                @if($resolution->root_cause_by)
                    <p class="mt-2 mb-0 text-muted" style="font-size:0.7rem;">
                        <i class="mdi mdi-account-check-outline mr-1"></i>
                        Identified by: {{ is_array($resolution->root_cause_by) ? implode(', ', $resolution->root_cause_by) : $resolution->root_cause_by }}
                        @if($resolution->root_cause_date)
                            on {{ $resolution->root_cause_date->format('d M Y') }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Immediate Actions Taken --}}
    @if($resolution && filled($resolution->action_taken))
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#dcfce7;flex-shrink:0;">
                <i class="mdi mdi-flash-outline text-success" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Immediate Actions Taken</span>
        </div>
        <div class="px-1 mb-4">
            <div class="p-3 rounded border" style="background:#f0fdf4;border-color:#86efac !important;">
                <p class="mb-2 text-dark" style="font-size:0.82rem;line-height:1.6;white-space: pre-wrap;">{!! html_entity_decode($resolution->action_taken) !!}</p>
                @if($resolution->action_taken_by)
                    <p class="mt-2 mb-0 text-muted" style="font-size:0.7rem;">
                        <i class="mdi mdi-account-wrench-outline mr-1"></i>
                        Action taken by: {{ is_array($resolution->action_taken_by) ? implode(', ', $resolution->action_taken_by) : $resolution->action_taken_by }}
                        @if($resolution->action_taken_date)
                            on {{ $resolution->action_taken_date->format('d M Y') }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Corrective Actions Taken --}}
    @php
        $hasComplaintCorrectiveAction = $resolution && filled(trim((string) $resolution->corrective_action_taken));
        $hasCorrectiveAction = $feedback->correctiveAction && $feedback->correctiveAction->exists;
    @endphp
    @if($hasComplaintCorrectiveAction)
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#dbeafe;flex-shrink:0;">
                <i class="mdi mdi-tools text-primary" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Corrective Actions Taken</span>
        </div>
        <div class="px-1 mb-4">
            <div class="p-3 rounded border" style="background:#eff6ff;border-color:#93c5fd !important;">
                <p class="mb-2 text-dark" style="font-size:0.82rem;line-height:1.6;white-space: pre-wrap;">{!! html_entity_decode($resolution->corrective_action_taken) !!}</p>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Action Administered By</p>
                        <p class="font-weight-bold text-dark mb-0" style="font-size:0.8rem;">
                            {{ is_array($resolution->corrective_action_by) ? implode(', ', $resolution->corrective_action_by) : $resolution->corrective_action_by }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Date Administered</p>
                        <p class="font-weight-bold text-dark mb-0" style="font-size:0.8rem;">
                            {{ $resolution->corrective_action_date ? $resolution->corrective_action_date->format('d M Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @elseif($hasCorrectiveAction)
        {{-- Fallback to Feedback Corrective Action if no complaint resolution --}}
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#dbeafe;flex-shrink:0;">
                <i class="mdi mdi-tools text-primary" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Corrective Action Taken</span>
        </div>
        <div class="px-1 mb-4">
            <div class="p-3 rounded border" style="background:#eff6ff;border-color:#93c5fd !important;">
                @if(filled($feedback->correctiveAction->summary_notes))
                    <p class="mb-2 text-dark" style="font-size:0.82rem;line-height:1.6;white-space: pre-wrap;">{{ $feedback->correctiveAction->summary_notes }}</p>
                @endif
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Action Taken By</p>
                        <p class="font-weight-bold text-dark mb-0" style="font-size:0.8rem;">{{ $feedback->correctiveAction->assignedUser->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Date Taken</p>
                        <p class="font-weight-bold text-dark mb-0" style="font-size:0.8rem;">
                            {{ $feedback->correctiveAction->created_at ? $feedback->correctiveAction->created_at->format('d M Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Add Corrective Action Button --}}
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="px-1 mb-4">
            <a href="/show/complaint/{{ $feedback->complaint->id }}?tab=investigation" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus mr-1"></i> Add Corrective Action
            </a>
        </div>
    @endif

    {{-- Client and Internal Remarks --}}
    @if($resolution && (filled($resolution->client_remarks) || filled($resolution->internal_remarks)))
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#f3f4f6;flex-shrink:0;">
                <i class="mdi mdi-comment-text-outline text-muted" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Remarks</span>
        </div>
        <div class="row px-1 mb-4">
            @if(filled($resolution->client_remarks))
                <div class="col-md-6 mb-2">
                    <p class="mb-1 text-muted" style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Client Remarks</p>
                    <div class="p-2 rounded border" style="background:#fafafa;">
                        <p class="mb-0 text-dark small" style="line-height:1.5;">{!! html_entity_decode($resolution->client_remarks) !!}</p>
                    </div>
                </div>
            @endif
            @if(filled($resolution->internal_remarks))
                <div class="col-md-6 mb-2">
                    <p class="mb-1 text-muted" style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Internal Remarks</p>
                    <div class="p-2 rounded border" style="background:#fafafa;">
                        <p class="mb-0 text-dark small" style="line-height:1.5;">{!! html_entity_decode($resolution->internal_remarks) !!}</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Complaint Details Footer --}}
    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
    <div class="row px-1 mb-4">
        <div class="col-md-4 mb-2">
            <p class="mb-1 text-muted" style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Complaint ID</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">{{ $feedback->complaint->complaint_id }}</p>
        </div>
        <div class="col-md-4 mb-2">
            <p class="mb-1 text-muted" style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Status</p>
            <span class="crm-badge {{ $feedback->complaint->is_closed ? 'crm-badge-success' : 'crm-badge-warning' }}">
                {{ $feedback->complaint->is_closed ? 'Closed' : 'Open' }}
            </span>
        </div>
        <div class="col-md-4 mb-2">
            <p class="mb-1 text-muted" style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Date</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                {{ $feedback->complaint->date ? $feedback->complaint->date->format('d M Y') : 'N/A' }}
            </p>
        </div>
    </div>

@endif