<div class="workflow-steps">
    @php
        $steps = [
            1 => ['name' => 'Risk Identified', 'icon' => 'fa-flag'],
            2 => ['name' => 'Risk Assessment', 'icon' => 'fa-clipboard-check'],
            3 => ['name' => 'Risk Evaluation', 'icon' => 'fa-balance-scale'],
            4 => ['name' => 'Treatment Planning', 'icon' => 'fa-tasks'],
            5 => ['name' => 'Treatment Implementation', 'icon' => 'fa-cogs'],
            6 => ['name' => 'Risk Monitoring', 'icon' => 'fa-eye'],
            7 => ['name' => 'Risk Closed', 'icon' => 'fa-check-circle'],
        ];
    @endphp

    @php
        $currentStep = (int) $risk->workflow_step;
    @endphp
    <div class="row">
        @foreach($steps as $stepNum => $stepInfo)
            @php
                $stepNumInt = (int) $stepNum;
                $isActive = $currentStep == $stepNumInt;
                $isCompleted = $currentStep > $stepNumInt;
                $isPending = $currentStep < $stepNumInt;
            @endphp
            <div class="col-md-3 mb-3">
                <div class="card {{ $isActive ? 'border-primary' : ($isCompleted ? 'border-success' : 'border-secondary') }}">
                    <div class="card-body text-center">
                        <i class="fas {{ $stepInfo['icon'] }} fa-2x {{ $isActive ? 'text-primary' : ($isCompleted ? 'text-success' : 'text-muted') }}"></i>
                        <h6 class="mt-2">{{ $stepInfo['name'] }}</h6>
                        @if($isActive)
                            <span class="badge badge-primary">Current</span>
                        @elseif($isCompleted)
                            <span class="badge badge-success">Completed</span>
                        @else
                            <span class="badge badge-secondary">Pending</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Workflow Actions -->
    <div class="mt-4">
        @php
            $currentStep = (int) $risk->workflow_step;
        @endphp
        @if($currentStep == 1)
            <a href="#assessment-form" class="btn btn-primary" data-toggle="collapse">
                <i class="fas fa-clipboard-check"></i> Proceed to Assessment
            </a>
        @elseif($currentStep == 2)
            <a href="#evaluation-form" class="btn btn-primary" data-toggle="collapse">
                <i class="fas fa-balance-scale"></i> Proceed to Evaluation
            </a>
        @elseif($currentStep == 3)
            @if($risk->evaluation_result === 'Unacceptable')
                <a href="#treatment-plan-form" class="btn btn-primary" data-toggle="collapse">
                    <i class="fas fa-tasks"></i> Create Treatment Plan
                </a>
            @else
                <a href="#review-form" class="btn btn-primary" data-toggle="collapse">
                    <i class="fas fa-eye"></i> Start Monitoring
                </a>
            @endif
        @elseif($currentStep == 4)
            <a href="#review-form" class="btn btn-primary" data-toggle="collapse">
                <i class="fas fa-eye"></i> Proceed to Monitoring
            </a>
        @elseif($currentStep == 5)
            <a href="#review-form" class="btn btn-primary" data-toggle="collapse">
                <i class="fas fa-eye"></i> Conduct Review
            </a>
        @elseif($currentStep == 6)
            @if($risk->canBeClosed())
                <a href="#close-form" class="btn btn-success" data-toggle="collapse">
                    <i class="fas fa-check-circle"></i> Close Risk
                </a>
            @endif
            <a href="#review-form" class="btn btn-primary" data-toggle="collapse">
                <i class="fas fa-eye"></i> Conduct Review
            </a>
        @endif
    </div>

    <!-- Assessment Form (Step 2) -->
    @php
        $currentStep = (int) $risk->workflow_step;
    @endphp
    @if($currentStep == 1)
    <div id="assessment-form" class="collapse mt-3">
        <div class="card">
            <div class="card-body">
                <h5>Risk Assessment</h5>
                <form action="{{ route('risk.risks.assessment.store', $risk->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Likelihood Scale</label>
                                <select name="likelihood_scale_id" class="form-control" required>
                                    <option value="">Select Likelihood</option>
                                    @foreach($likelihoodScales as $scale)
                                        <option value="{{ $scale->id }}">{{ $scale->name }} (Score: {{ $scale->score }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Likelihood Score</label>
                                <input type="number" name="likelihood_score" class="form-control" min="1" max="5" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Severity Scale</label>
                                <select name="severity_scale_id" class="form-control" required>
                                    <option value="">Select Severity</option>
                                    @foreach($severityScales as $scale)
                                        <option value="{{ $scale->id }}">{{ $scale->name }} (Score: {{ $scale->score }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Severity Score</label>
                                <input type="number" name="severity_score" class="form-control" min="1" max="5" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Notes</label>
                        <textarea name="assessment_notes" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Assessment</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Evaluation Form (Step 3) -->
    @if($currentStep == 2)
    <div id="evaluation-form" class="collapse mt-3">
        <div class="card">
            <div class="card-body">
                <h5>Risk Evaluation</h5>
                <form action="{{ route('risk.risks.evaluation.store', $risk->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Evaluation Result <span class="text-danger">*</span></label>
                        <select name="evaluation_result" class="form-control" required>
                            <option value="">Select Result</option>
                            <option value="Unacceptable">Unacceptable</option>
                            <option value="Tolerable">Tolerable</option>
                            <option value="Acceptable">Acceptable</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Evaluation Notes</label>
                        <textarea name="evaluation_notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Acceptance Threshold RPN</label>
                        <input type="number" name="acceptance_threshold_rpn" class="form-control" min="1" max="25" value="15">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Evaluation</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Treatment Plan Form (Step 4) -->
    @if($currentStep >= 3 && $risk->evaluation_result === 'Unacceptable')
    <div id="treatment-plan-form" class="collapse mt-3">
        <div class="card">
            <div class="card-body">
                <h5>Create Treatment Plan</h5>
                <form action="{{ route('risk.risks.treatment-plan.store', $risk->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Treatment Type <span class="text-danger">*</span></label>
                        <select name="treatment_type_id" class="form-control" required>
                            <option value="">Select Treatment Type</option>
                            @foreach($treatmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Control Measures</label>
                        <textarea name="control_measures" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Responsible Person</label>
                                <select name="responsible_user_id" class="form-control">
                                    <option value="">Select Person</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Target Completion Date</label>
                                <input type="date" name="target_completion_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Treatment Plan</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Review Form (Step 6) -->
    @if($currentStep >= 5)
    <div id="review-form" class="collapse mt-3">
        <div class="card">
            <div class="card-body">
                <h5>Conduct Risk Review</h5>
                <form action="{{ route('risk.risks.review.store', $risk->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Review Date <span class="text-danger">*</span></label>
                                <input type="date" name="review_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Review Type <span class="text-danger">*</span></label>
                                <select name="review_type" class="form-control" required>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Triggered">Triggered</option>
                                    <option value="Periodic">Periodic</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Review Decision <span class="text-danger">*</span></label>
                        <select name="review_decision" class="form-control" required>
                            <option value="Continue Monitoring">Continue Monitoring</option>
                            <option value="Close Risk">Close Risk</option>
                            <option value="Additional Controls Needed">Additional Controls Needed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Review Findings</label>
                        <textarea name="review_findings" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Next Review Date</label>
                        <input type="date" name="next_review_date" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Review</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Close Form (Step 7) -->
    @if($currentStep == 6 && $risk->canBeClosed())
    <div id="close-form" class="collapse mt-3">
        <div class="card">
            <div class="card-body">
                <h5>Close Risk</h5>
                <form action="{{ route('risk.risks.close', $risk->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Closure Type <span class="text-danger">*</span></label>
                        <select name="closure_type" class="form-control" required>
                            <option value="Eliminated">Eliminated</option>
                            <option value="Controlled">Controlled</option>
                            <option value="Accepted">Accepted</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Closure Justification <span class="text-danger">*</span></label>
                        <textarea name="closure_justification" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Close Risk</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>


