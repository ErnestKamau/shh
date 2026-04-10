<div>
    <form wire:submit="save">
        @if($auditId || $findingId)
        <div class="alert alert-info">
            <i class="mdi mdi-information"></i> 
            @if($findingId)
                This NC will be linked to an audit finding.
            @elseif($auditId)
                This NC will be linked to audit {{ $auditId }}.
            @endif
        </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Title <span class="text-danger">*</span></label>
                    <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" 
                           placeholder="Brief title for the non-conformance">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Origin <span class="text-danger">*</span></label>
                    <select wire:model="origin_id" class="form-control @error('origin_id') is-invalid @enderror">
                        <option value="">Select Origin...</option>
                        @foreach($origins as $origin)
                        <option value="{{ $origin->id }}">{{ $origin->name }}</option>
                        @endforeach
                    </select>
                    @error('origin_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Identified Date <span class="text-danger">*</span></label>
                    <input wire:model="date_identified" type="date" class="form-control @error('date_identified') is-invalid @enderror">
                    @error('date_identified') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Target Closure Date</label>
                    <input wire:model="target_closure_date" type="date" class="form-control @error('target_closure_date') is-invalid @enderror">
                    @error('target_closure_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Description <span class="text-danger">*</span></label>
            <textarea wire:model="description" class="form-control editor @error('description') is-invalid @enderror" rows="4" 
                      placeholder="Clear statement of the deviation or non-conformance"></textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>ISO Clause Reference</label>
                    <input wire:model="iso_clause_violated" type="text" class="form-control" placeholder="e.g., 7.10, 8.7">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>SOP Reference</label>
                    <input wire:model="sop_reference" type="text" class="form-control" placeholder="e.g., SOP-QC-001">
                </div>
            </div>
        </div>

        <hr>
        <h5>Link to Related Items</h5>
        <p class="text-muted small">Link this non-conformance to related samples, equipment, methods, or personnel</p>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Sample</label>
                    <select wire:model="sample_id" class="form-control">
                        <option value="">Select Sample/Batch...</option>
                        @foreach($samples as $sample)
                        <option value="{{ $sample->id }}">
                            {{ $sample->batch_code }}@if($sample->reference_number) - {{ $sample->reference_number }}@endif
                        </option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="sample_reference">
                    <small class="form-text text-muted">Search and select a sample/batch</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Equipment</label>
                    <select wire:model="equipment_id" class="form-control">
                        <option value="">Select Equipment...</option>
                        @foreach($equipment as $eq)
                        <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="equipment_reference">
                    <small class="form-text text-muted">Search and select equipment</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Method</label>
                    <select wire:model="method_id" class="form-control">
                        <option value="">Select Analysis Method...</option>
                        @foreach($methods as $method)
                        <option value="{{ $method->id }}">
                            {{ $method->name }}@if($method->code) ({{ $method->code }})@endif
                        </option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="method_reference">
                    <small class="form-text text-muted">Search and select analysis method</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Personnel</label>
                    <select wire:model="personnel_id" class="form-control">
                        <option value="">Select Personnel...</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="personnel_reference">
                    <small class="form-text text-muted">Select personnel involved</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Immediate Correction</label>
            <textarea wire:model="immediate_correction" class="form-control editor" rows="3" 
                      placeholder="Quick action taken to contain the issue"></textarea>
        </div>

        <hr>
        <h5>Assignment</h5>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Responsible Person</label>
                    <select wire:model="responsible_person_id" class="form-control">
                        <option value="">Select Person...</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Or External Party Name</label>
                    <input wire:model="responsible_person_name" type="text" class="form-control" placeholder="Name if external">
                </div>
            </div>
        </div>

        <hr>
        <h5>Risk Assessment</h5>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Risk Level</label>
                    <select wire:model="risk_level_id" class="form-control">
                        <option value="">Select Level...</option>
                        @foreach($riskLevels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Severity</label>
                    <select wire:model="severity_scale_id" class="form-control">
                        <option value="">Select Severity...</option>
                        @foreach($severityScales as $scale)
                        <option value="{{ $scale->id }}">{{ $scale->name }} (Score: {{ $scale->score }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="severity_score">
                    <small class="form-text text-muted">Select from configured severity levels</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Occurrence Likelihood</label>
                    <select wire:model="likelihood_scale_id" class="form-control">
                        <option value="">Select Likelihood...</option>
                        @foreach($likelihoodScales as $scale)
                        <option value="{{ $scale->id }}">{{ $scale->name }} (Score: {{ $scale->score }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" wire:model="likelihood_score">
                    <small class="form-text text-muted">Select from configured likelihood levels</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Risk Assessment Notes</label>
            <textarea wire:model="risk_assessment_notes" class="form-control editor" rows="2" 
                      placeholder="Justification for risk scores"></textarea>
        </div>

        <hr>

        <div class="d-flex justify-content-end">
            <a href="{{ route('audit.nc.index') }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-danger" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                <i class="mdi mdi-content-save"></i> {{ $isEdit ? 'Update NC' : 'Raise Non-Conformance' }}
            </button>
        </div>
    </form>
</div>


