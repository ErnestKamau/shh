@extends('layouts.risk.layout.app')

@section('title')
Risk Configuration Settings
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="mdi mdi-cog"></i> Risk Management Configuration Settings
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Manage all dropdown options and configuration values for the Risk Management module.</p>
                    
                    <div class="row">
                        @foreach($optionTypes as $type => $label)
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $label }}</h5>
                                    <p class="card-text text-muted small">
                                        @if($type === 'risk_level')
                                            Configure risk levels (Critical, High, Medium, Low)
                                        @elseif($type === 'evaluation_result')
                                            Configure evaluation results (Acceptable, Tolerable, Unacceptable, Escalate)
                                        @elseif($type === 'implementation_status')
                                            Configure implementation statuses (Planned, In Progress, Completed, etc.)
                                        @elseif($type === 'treatment_priority')
                                            Configure treatment priorities (High, Medium, Low)
                                        @elseif($type === 'review_type')
                                            Configure review types (Scheduled, Triggered, Periodic)
                                        @elseif($type === 'review_decision')
                                            Configure review decisions (Continue Monitoring, Close Risk, etc.)
                                        @elseif($type === 'closure_type')
                                            Configure closure types (Eliminated, Controlled, Accepted)
                                        @elseif($type === 'likelihood_score' || $type === 'severity_score')
                                            Configure score options (1-5)
                                        @endif
                                    </p>
                                    <a href="{{ route('risk.settings.show', $type) }}" class="btn btn-sm btn-primary">
                                        <i class="mdi mdi-pencil"></i> Manage Options
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

