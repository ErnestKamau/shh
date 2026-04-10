<div class="audit-details-popover" style="max-width: 800px; max-height: 600px; overflow-y: auto;">
    <div class="mb-3">
        <h6 class="mb-2" style="color: #1a73e8; font-weight: 600;">
            <i class="mdi mdi-file-document-outline"></i> {{ $audit->audit_number }} - {{ $audit->title }}
        </h6>
        <div class="small text-muted">
            <strong>Type:</strong> {{ $audit->auditType?->name ?? 'N/A' }} | 
            <strong>Status:</strong> {{ $audit->status_name }} |
            <strong>Lead Auditor:</strong> {{ $audit->leadAuditor?->name ?? 'N/A' }}
        </div>
    </div>
    
    @php
        $workflowStep = $audit->getCurrentWorkflowStep() ?? 1;
    @endphp
    
    <!-- Findings Section -->
    @if($audit->findings->count() > 0)
    <div class="mb-3">
        <h6 class="text-primary"><i class="mdi mdi-alert-circle"></i> Findings ({{ $audit->findings->count() }})</h6>
        <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
            <table class="table table-sm table-bordered mb-0">
                <thead style="background: #f5f5f5; position: sticky; top: 0;">
                    <tr>
                        <th style="font-size: 11px;">#</th>
                        <th style="font-size: 11px;">Description</th>
                        <th style="font-size: 11px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audit->findings->take(5) as $finding)
                    <tr>
                        <td style="font-size: 11px;">{{ $finding->finding_number ?? $finding->order_index ?? $loop->iteration }}</td>
                        <td style="font-size: 11px;">{{ Str::limit($finding->observation ?? 'N/A', 50) }}</td>
                        <td style="font-size: 11px;">
                            <span class="badge badge-sm badge-info">{{ $finding->status_name ?? 'Open' }}</span>
                        </td>
                    </tr>
                    @endforeach
                    @if($audit->findings->count() > 5)
                    <tr>
                        <td colspan="3" class="text-center small text-muted">+ {{ $audit->findings->count() - 5 }} more findings</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    <!-- Non-Conformances with Root Causes and Corrective Actions -->
    @if($audit->nonConformances->count() > 0)
    <div class="mb-3">
        <h6 class="text-danger"><i class="mdi mdi-alert-octagon"></i> Non-Conformances ({{ $audit->nonConformances->count() }})</h6>
        @foreach($audit->nonConformances->take(3) as $nc)
        <div class="card mb-2" style="border-left: 3px solid #dc3545;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <strong style="font-size: 12px;">{{ $nc->nc_number }}</strong>
                    <span class="badge badge-sm badge-{{ $nc->risk_level_name === 'Critical' ? 'danger' : ($nc->risk_level_name === 'High' ? 'warning' : 'info') }}">
                        {{ $nc->risk_level_name ?? 'Medium' }}
                    </span>
                </div>
                <div class="small mb-1" style="font-size: 11px;"><strong>Title:</strong> {{ Str::limit($nc->title, 60) }}</div>
                
                @if($nc->rootCauseAnalysis)
                <div class="small mb-1" style="font-size: 11px;">
                    <strong>Root Cause:</strong> {{ Str::limit($nc->rootCauseAnalysis->root_cause_description, 80) }}
                </div>
                @endif
                
                @if($nc->correctiveActions->count() > 0)
                <div class="mt-1">
                    <strong style="font-size: 11px;">Corrective Actions ({{ $nc->correctiveActions->count() }}):</strong>
                    @foreach($nc->correctiveActions->take(2) as $capa)
                    <div class="small pl-2 mt-1" style="font-size: 10px; border-left: 2px solid #28a745;">
                        <div><strong>{{ $capa->capa_number }}:</strong> {{ Str::limit($capa->title, 50) }}</div>
                        <div class="text-muted">
                            <span class="badge badge-sm badge-{{ $capa->status_name === 'Closed' ? 'success' : ($capa->status_name === 'Implemented' ? 'info' : 'warning') }}">
                                {{ $capa->status_name }}
                            </span>
                            @if($capa->implementation_date)
                            | Implemented: {{ $capa->implementation_date->format('M d, Y') }}
                            @endif
                        </div>
                        @if($capa->latestVerification)
                        <div class="text-success small mt-1">
                            <i class="mdi mdi-check-circle"></i> Verified: {{ $capa->latestVerification->effectiveness_result_name ?? 'Effective' }}
                            @if($capa->latestVerification->verification_date)
                            ({{ $capa->latestVerification->verification_date->format('M d, Y') }})
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @if($nc->correctiveActions->count() > 2)
                    <div class="small text-muted pl-2">+ {{ $nc->correctiveActions->count() - 2 }} more CAPAs</div>
                    @endif
                </div>
                @else
                <div class="small text-muted" style="font-size: 11px;">No corrective actions yet</div>
                @endif
            </div>
        </div>
        @endforeach
        @if($audit->nonConformances->count() > 3)
        <div class="small text-muted text-center">+ {{ $audit->nonConformances->count() - 3 }} more NCs</div>
        @endif
    </div>
    @endif
    
    <!-- Checklists Section -->
    @if($audit->checklists->count() > 0)
    <div class="mb-3">
        <h6 class="text-info"><i class="mdi mdi-format-list-checks"></i> Checklists ({{ $audit->checklists->count() }})</h6>
        @foreach($audit->checklists->take(2) as $checklist)
        <div class="small mb-1" style="font-size: 11px;">
            <strong>{{ $checklist->name }}</strong>
            @if($checklist->iso_standard)
            <span class="badge badge-sm badge-secondary">{{ $checklist->iso_standard }}</span>
            @endif
            - {{ $checklist->items->count() }} items
        </div>
        @endforeach
        @if($audit->checklists->count() > 2)
        <div class="small text-muted">+ {{ $audit->checklists->count() - 2 }} more checklists</div>
        @endif
    </div>
    @endif
    
    <!-- Requirements Check -->
    @php
        $findingsRequiringNC = $audit->findings->filter(function($f) {
            return $f->findingCategory && $f->findingCategory->requires_capa && !$f->nonConformance;
        });
    @endphp
    @if($findingsRequiringNC->count() > 0 && $workflowStep == 3)
    <div class="mb-3">
        <h6 class="text-danger"><i class="mdi mdi-alert-circle"></i> Action Required</h6>
        <div class="small" style="font-size: 11px; color: #dc3545;">
            <div><strong>Findings requiring NC:</strong> {{ $findingsRequiringNC->count() }}</div>
            <ul class="mb-0 pl-3" style="font-size: 10px;">
                @foreach($findingsRequiringNC->take(3) as $finding)
                <li>{{ $finding->finding_number }} - {{ Str::limit($finding->observation ?? 'N/A', 40) }}</li>
                @endforeach
                @if($findingsRequiringNC->count() > 3)
                <li>+ {{ $findingsRequiringNC->count() - 3 }} more</li>
                @endif
            </ul>
        </div>
    </div>
    @endif
    
    <!-- Workflow Status Based Information -->
    <div class="mb-3">
        <h6 class="text-secondary"><i class="mdi mdi-information"></i> Workflow Information</h6>
        <div class="small" style="font-size: 11px;">
            <div><strong>Current Step:</strong> {{ $workflowStep }} - {{ $audit->status_name }}</div>
            <div><strong>Scheduled:</strong> {{ $audit->scheduled_date?->format('M d, Y') ?? 'N/A' }}</div>
            @if($audit->start_date)
            <div><strong>Started:</strong> {{ $audit->start_date->format('M d, Y') }}</div>
            @endif
            @if($audit->end_date)
            <div><strong>Ended:</strong> {{ $audit->end_date->format('M d, Y') }}</div>
            @endif
            @if($audit->closure_date)
            <div><strong>Closed:</strong> {{ $audit->closure_date->format('M d, Y') }}</div>
            @endif
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="border-top pt-2">
        <div class="row text-center">
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">Findings</div>
                <div class="h6 mb-0" style="font-size: 14px;">{{ $audit->findings->count() }}</div>
            </div>
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">NCs</div>
                <div class="h6 mb-0" style="font-size: 14px;">{{ $audit->nonConformances->count() }}</div>
            </div>
            <div class="col-4">
                <div class="small" style="font-size: 10px; color: #666;">Checklists</div>
                <div class="h6 mb-0" style="font-size: 14px;">{{ $audit->checklists->count() }}</div>
            </div>
        </div>
    </div>
</div>

