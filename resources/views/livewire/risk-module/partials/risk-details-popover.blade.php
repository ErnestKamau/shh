<div class="risk-details-popover" style="max-width: 800px;">
    <!-- Header -->
    <div class="mb-3">
        <h6 class="mb-2" style="color: #1a73e8; font-weight: 600;">
            <i class="mdi mdi-alert-octagon"></i> {{ $risk->risk_number }} - {{ $risk->title }}
        </h6>
        <div class="small text-muted border-bottom pb-2">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-1"><strong>Category:</strong> {{ $risk->category->name ?? 'N/A' }}</div>
                    <div class="mb-1"><strong>Source:</strong> {{ $risk->source->name ?? $risk->other_source_name ?? 'N/A' }}</div>
                    <div><strong>Department:</strong> {{ $risk->department ?? 'N/A' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="mb-1"><strong>Owner:</strong> {{ $risk->riskOwner->name ?? 'N/A' }}</div>
                    <div class="mb-1"><strong>Identified By:</strong> {{ $risk->identifiedByUser->name ?? $risk->identified_by ?? 'N/A' }}</div>
                    <div><strong>Date:</strong> {{ $risk->date_identified ? $risk->date_identified->format('M d, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Context / Scope Section (Conditionals) -->
    @if($risk->sample || $risk->method || $risk->equipment)
    <div class="mb-3">
        <h6 class="text-info"><i class="mdi mdi-target-crosshair"></i> Risk Context</h6>
        <div class="p-2 bg-light rounded small text-muted border-left-3" style="border-left: 3px solid #17a2b8;">
            @if($risk->sample)
                <div class="mb-1"><strong>Sample:</strong> {{ $risk->sample->sample_name ?? $risk->sample_reference }}</div>
            @endif
            @if($risk->method)
                <div class="mb-1"><strong>Method:</strong> {{ $risk->method->method_name ?? $risk->method_reference }}</div>
            @endif
            @if($risk->equipment)
                <div><strong>Equipment:</strong> {{ $risk->equipment->name ?? 'N/A' }}</div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Description Section -->
    <div class="mb-3">
        <h6 class="text-primary"><i class="mdi mdi-text-box-outline"></i> Description</h6>
        <div class="p-2 bg-light rounded small text-muted border-left-3" style="border-left: 3px solid #1a73e8;">
            {{ $risk->description }}
        </div>
    </div>
    
    <!-- Related Entities (Audit/NC) -->
    @if($risk->audit || $risk->nonConformance)
    <div class="mb-3">
        <h6 class="text-dark"><i class="mdi mdi-link-variant"></i> Related Entities</h6>
        <div class="p-2 bg-light rounded small text-muted border-left-3" style="border-left: 3px solid #343a40;">
            @if($risk->audit)
                <div class="mb-1"><strong>Audit:</strong> {{ $risk->audit->audit_number ?? '' }} - {{ $risk->audit->title ?? '' }}</div>
            @endif
            @if($risk->nonConformance)
                <div><strong>Non-Conformance:</strong> {{ $risk->nonConformance->nc_number ?? '' }}</div>
            @endif
        </div>
    </div>
    @endif

    <!-- Assessment Section -->
    <div class="mb-3">
        <h6 class="text-danger"><i class="mdi mdi-speedometer"></i> Assessment & Rating</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead style="background: #f5f5f5;">
                    <tr>
                        <th style="font-size: 11px;">Phase</th>
                        <th style="font-size: 11px;">Likelihood</th>
                        <th style="font-size: 11px;">Severity</th>
                        <th style="font-size: 11px;">RPN</th>
                        <th style="font-size: 11px;">Level</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-size: 11px;"><strong>Initial</strong></td>
                        <td style="font-size: 11px;">{{ $risk->likelihood_score ?? '-' }}</td>
                        <td style="font-size: 11px;">{{ $risk->severity_score ?? '-' }}</td>
                        <td style="font-size: 11px;"><strong>{{ $risk->rpn ?? '-' }}</strong></td>
                        <td style="font-size: 11px;">
                             @php
                                $badgeClass = 'secondary';
                                if ($risk->risk_level === 'Critical') $badgeClass = 'danger';
                                elseif ($risk->risk_level === 'High') $badgeClass = 'warning';
                                elseif ($risk->risk_level === 'Medium') $badgeClass = 'info';
                                elseif ($risk->risk_level === 'Low') $badgeClass = 'success';
                            @endphp
                            <span class="badge badge-sm badge-{{ $badgeClass }}">{{ $risk->risk_level ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    @if($risk->residual_rpn)
                    <tr>
                        <td style="font-size: 11px;"><strong>Residual</strong></td>
                        <td style="font-size: 11px;">{{ $risk->residual_likelihood_score ?? '-' }}</td>
                        <td style="font-size: 11px;">{{ $risk->residual_severity_score ?? '-' }}</td>
                        <td style="font-size: 11px;"><strong>{{ $risk->residual_rpn ?? '-' }}</strong></td>
                        <td style="font-size: 11px;">
                             @php
                                $resBadgeClass = 'secondary';
                                if ($risk->residual_risk_level === 'Critical') $resBadgeClass = 'danger';
                                elseif ($risk->residual_risk_level === 'High') $resBadgeClass = 'warning';
                                elseif ($risk->residual_risk_level === 'Medium') $resBadgeClass = 'info';
                                elseif ($risk->residual_risk_level === 'Low') $resBadgeClass = 'success';
                            @endphp
                            <span class="badge badge-sm badge-{{ $resBadgeClass }}">{{ $risk->residual_risk_level ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Evaluation Section (NEW) -->
    @if($risk->evaluation_result || $risk->evaluation_notes)
    <div class="mb-3">
        <h6 class="text-warning" style="color: #fd7e14 !important;"><i class="mdi mdi-scale-balance"></i> Evaluation</h6>
        <div class="p-2 bg-light rounded small text-muted border-left-3" style="border-left: 3px solid #fd7e14;">
            <div class="d-flex justify-content-between mb-1">
                <strong>Result: {{ $risk->evaluation_result ?? 'Pending' }}</strong>
                <span>{{ $risk->evaluation_date ? $risk->evaluation_date->format('M d, Y') : '' }}</span>
            </div>
            @if($risk->evaluation_notes)
            <div style="white-space: normal;">
                <strong>Notes:</strong> {{ $risk->evaluation_notes }}
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Treatment Plans -->
    @if($risk->treatmentPlans->count() > 0)
    <div class="mb-3">
        <h6 class="text-success"><i class="mdi mdi-clipboard-check"></i> Treatment Plans ({{ $risk->treatmentPlans->count() }})</h6>
        @foreach($risk->treatmentPlans as $plan)
        <div class="card mb-2" style="border-left: 3px solid #28a745;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <strong style="font-size: 12px; white-space: normal;">{{ $plan->activity }}</strong>
                    <span class="badge badge-sm badge-{{ $plan->implementation_status === 'Completed' ? 'success' : 'secondary' }}">
                        {{ $plan->implementation_status }}
                    </span>
                </div>
                <div class="small mb-1" style="font-size: 11px;">
                     <strong>Owner:</strong> {{ $plan->responsiblePerson->name ?? 'N/A' }} |
                     <strong>Due:</strong> {{ $plan->due_date ? $plan->due_date->format('M d, Y') : 'N/A' }}
                </div>
                @if($plan->resources_required)
                <div class="small text-muted" style="font-size: 11px; white-space: normal;">
                    <strong>Resources:</strong> {{ $plan->resources_required }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Latest Review -->
    @if($risk->latestReview->isNotEmpty())
    <div class="mb-3">
        <h6 class="text-secondary"><i class="mdi mdi-comment-eye-outline"></i> Latest Review</h6>
        @foreach($risk->latestReview as $review)
        <div class="p-2 bg-light rounded small text-muted border-left-3" style="border-left: 3px solid #6c757d;">
            <div class="d-flex justify-content-between mb-1">
                <strong>{{ $review->review_date ? $review->review_date->format('M d, Y') : 'N/A' }}</strong>
                <span>{{ $review->reviewer->name ?? 'N/A' }}</span>
            </div>
            <div style="white-space: normal;">{{ $review->review_comments }}</div>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Footer Info -->
    <div class="border-top pt-2">
        <div class="small text-muted d-flex justify-content-between">
            <div><strong>Status:</strong> {{ $risk->status_name }} (Step {{ $risk->workflow_step }})</div>
            @if($risk->next_review_date)
            <div class="{{ $risk->next_review_date < now() ? 'text-danger font-weight-bold' : '' }}">
                <i class="mdi mdi-calendar"></i> Next Review: {{ $risk->next_review_date->format('M d, Y') }}
            </div>
            @endif
        </div>
    </div>
</div>
