<div>
    <form wire:submit="save">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Related Non-Conformance <span class="text-danger">*</span></label>
                    @if($ncId)
                        <input type="hidden" wire:model="non_conformance_id" value="{{ $ncId }}">
                        <select class="form-control" disabled>
                            @foreach($ncs as $nc)
                                @if($nc->id == $ncId)
                                <option value="{{ $nc->id }}" selected>{{ $nc->nc_number }} - {{ Str::limit($nc->description ?? $nc->title ?? 'N/A', 50) }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select wire:model="non_conformance_id" class="form-control @error('non_conformance_id') is-invalid @enderror">
                            <option value="">Select NC...</option>
                            @foreach($ncs as $nc)
                            <option value="{{ $nc->id }}">{{ $nc->nc_number }} - {{ Str::limit($nc->description ?? $nc->title ?? 'N/A', 50) }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('non_conformance_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Action Type</label>
                    <select wire:model="action_type_id" class="form-control @error('action_type_id') is-invalid @enderror">
                        <option value="">Select Type...</option>
                        @foreach($actionTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('action_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Title <span class="text-danger">*</span></label>
            <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" 
                   placeholder="Brief title for the corrective action">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Action Description <span class="text-danger">*</span></label>
            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4" 
                      placeholder="What corrective action will be taken?"></textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Expected Outcome</label>
            <textarea wire:model="expected_outcome" class="form-control" rows="2" 
                      placeholder="What is the expected result of this action?"></textarea>
        </div>

        <hr>
        <h5>Assignment</h5>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Assigned To <span class="text-danger">*</span></label>
                    <select wire:model="action_owner_id" class="form-control @error('action_owner_id') is-invalid @enderror">
                        <option value="">Select Person...</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('action_owner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Due Date <span class="text-danger">*</span></label>
                    <input wire:model="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror">
                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Priority</label>
                    <select wire:model="priority_id" class="form-control @error('priority_id') is-invalid @enderror">
                        <option value="">Select Priority...</option>
                        @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                    @error('priority_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select wire:model="category_id" class="form-control">
                <option value="">Select Category...</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        @if($isEdit)
        <hr>
        <h5>Implementation</h5>

        <div class="form-group">
            <label>Implementation Notes</label>
            <textarea wire:model="implementation_notes" class="form-control" rows="3" 
                      placeholder="Describe how the action was implemented"></textarea>
        </div>
        @endif

        <hr>
        <h5>Preventive Action (Optional)</h5>

        <div class="form-group">
            <label>Preventive Action</label>
            <textarea wire:model="preventive_measure" class="form-control" rows="3" 
                      placeholder="What actions will be taken to prevent recurrence?"></textarea>
        </div>

        <hr>

        <div class="d-flex justify-content-end">
            <a href="{{ route('audit.capa.index') }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-success">
                <i class="mdi mdi-content-save"></i> {{ $isEdit ? 'Update CAPA' : 'Create Corrective Action' }}
            </button>
        </div>
    </form>
</div>

