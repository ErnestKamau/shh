<div>
    <form wire:submit="save">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Audit Title <span class="text-danger">*</span></label>
                    <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" 
                           placeholder="Enter audit title">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Audit Type <span class="text-danger">*</span></label>
                    <select wire:model="audit_type_id" class="form-control @error('audit_type_id') is-invalid @enderror" required>
                        <option value="">Select Type...</option>
                        @foreach($auditTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('audit_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Scheduled Date <span class="text-danger">*</span></label>
                    <input wire:model="scheduled_date" type="date" class="form-control @error('scheduled_date') is-invalid @enderror">
                    @error('scheduled_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Start Date</label>
                    <input wire:model="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror">
                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>End Date</label>
                    <input wire:model="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror">
                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Lead Auditor</label>
                    <input wire:model="lead_auditor_name" type="text" class="form-control @error('lead_auditor_name') is-invalid @enderror" 
                           placeholder="Enter lead auditor name">
                    @error('lead_auditor_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Auditee / Department</label>
                    <select wire:model="auditee_department_id" class="form-control @error('auditee_department_id') is-invalid @enderror">
                        <option value="">Select Department...</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('auditee_department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Area</label>
                    <input wire:model="department" type="text" class="form-control" placeholder="e.g., Chemistry Lab, QC Department">
                </div>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <label>Scope</label>
            <textarea wire:model="scope" class="form-control editor" rows="3" placeholder="What is being audited? (processes, departments, test areas)"></textarea>
        </div>

        <div class="form-group">
            <label>Criteria</label>
            <textarea wire:model="criteria" class="form-control editor" rows="3" placeholder="ISO clauses, SOPs, regulations, internal policies"></textarea>
        </div>

        <div class="form-group">
            <label>Objectives</label>
            <textarea wire:model="objectives" class="form-control editor" rows="3" placeholder="Purpose and goals of this audit"></textarea>
        </div>

        @if($isEdit)
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Audit Summary</h5>
            @if(!$canEditSummaryFields && $statusName)
            <span class="badge badge-warning">
                <i class="mdi mdi-information-outline"></i> Available when status is "Verify" or later
            </span>
            @endif
        </div>
        
        <div class="form-group">
            <label>
                Summary
                @if(!$canEditSummaryFields && $statusName)
                <small class="text-muted">(Fill when closing the audit)</small>
                @endif
            </label>
            <textarea wire:model="summary" 
                      class="form-control editor" 
                      rows="3" 
                      placeholder="Overall audit comments and observations"
                      @if(!$canEditSummaryFields && $statusName) disabled @endif></textarea>
            @if(!$canEditSummaryFields && $statusName)
            <small class="form-text text-muted">
                <i class="mdi mdi-lock"></i> This field will be enabled when the audit status is "Verify" or later
            </small>
            @endif
        </div>

        <div class="form-group">
            <label>
                Conclusion
                @if(!$canEditSummaryFields && $statusName)
                <small class="text-muted">(Fill when closing the audit)</small>
                @endif
            </label>
            <textarea wire:model="conclusion" 
                      class="form-control editor" 
                      rows="3" 
                      placeholder="Final conclusion of the audit"
                      @if(!$canEditSummaryFields && $statusName) disabled @endif></textarea>
            @if(!$canEditSummaryFields && $statusName)
            <small class="form-text text-muted">
                <i class="mdi mdi-lock"></i> This field will be enabled when the audit status is "Verify" or later
            </small>
            @endif
        </div>

        <div class="form-group">
            <label>
                Recommendations
                @if(!$canEditSummaryFields && $statusName)
                <small class="text-muted">(Fill when closing the audit)</small>
                @endif
            </label>
            <textarea wire:model="recommendations" 
                      class="form-control editor" 
                      rows="3" 
                      placeholder="Recommendations for improvement"
                      @if(!$canEditSummaryFields && $statusName) disabled @endif></textarea>
            @if(!$canEditSummaryFields && $statusName)
            <small class="form-text text-muted">
                <i class="mdi mdi-lock"></i> This field will be enabled when the audit status is "Verify" or later
            </small>
            @endif
        </div>
        @endif

        <hr>

        <div class="d-flex justify-content-end">
            <a href="{{ route('audit.audits.index') }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-primary" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                <i class="mdi mdi-content-save"></i> {{ $isEdit ? 'Update Audit' : 'Schedule Audit' }}
            </button>
        </div>
    </form>
</div>

