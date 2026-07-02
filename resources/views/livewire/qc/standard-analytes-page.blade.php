<div class="qc-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-flask-outline mr-1"></i> QC Standard Analytes</h5>
            <small class="text-muted">Standard: {{ $standard->name }} ({{ $standard->code }})</small>
        </div>
        <a href="{{ route('qc_configuration_index') }}" class="btn btn-sm btn-light">Back to Configurations</a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="saveAnalyte" class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>{{ $editingAnalyteId ? 'Edit Standard Analyte' : 'Add Standard Analyte' }}</strong>
                @if($editingAnalyteId)
                    <button type="button" class="btn btn-sm btn-light" wire:click="resetForm">Cancel</button>
                @endif
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Analyte</label>
                    <select wire:model="analyteId" class="form-control form-control-sm @error('analyteId') is-invalid @enderror">
                        <option value="">Select analyte...</option>
                        @foreach($analytes as $analyte)
                            <option value="{{ $analyte->id }}">{{ $analyte->code }} - {{ $analyte->name }}</option>
                        @endforeach
                    </select>
                    @error('analyteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Expected</label>
                    <input type="number" step="0.0001" wire:model="expectedValue" class="form-control form-control-sm @error('expectedValue') is-invalid @enderror">
                    @error('expectedValue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Tolerance 1</label>
                    <input type="number" step="0.0001" wire:model="tolerance1" class="form-control form-control-sm @error('tolerance1') is-invalid @enderror">
                    @error('tolerance1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-2">
                    <label>Tolerance 2</label>
                    <input type="number" step="0.0001" wire:model="tolerance2" class="form-control form-control-sm @error('tolerance2') is-invalid @enderror">
                    @error('tolerance2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" wire:model="useAbsoluteTolerance" class="form-check-input" id="useAbsoluteTolerance">
                        <label for="useAbsoluteTolerance" class="form-check-label">Absolute</label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Comment</label>
                    <textarea wire:model="comment" rows="2" class="form-control form-control-sm @error('comment') is-invalid @enderror"></textarea>
                    @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Recommendation</label>
                    <textarea wire:model="recommendation" rows="2" class="form-control form-control-sm @error('recommendation') is-invalid @enderror"></textarea>
                    @error('recommendation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" wire:model="isActive" id="isActiveAnalyte">
                <label class="form-check-label" for="isActiveAnalyte">Active</label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Save Analyte</button>
        </div>
    </form>

    <div class="card qc-table-card">
        <div class="card-body">
            <div class="table-responsive qc-table-wrap">
                <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 120px;">Actions</th>
                        <th>Analyte</th>
                        <th>Expected</th>
                        <th>Tol 1</th>
                        <th>Tol 2</th>
                        <th>Low</th>
                        <th>High</th>
                        <th>Status</th>
                        <th>Comment</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standardAnalytes as $row)
                        <tr>
                            <td>
                                <button class="btn btn-sm btn-light" wire:click="editAnalyte('{{ $row->id }}')">Edit</button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deactivateAnalyte('{{ $row->id }}')">Deactivate</button>
                            </td>
                            <td>{{ $row->getAnalyte()->code ?? '-' }}</td>
                            <td>{{ $row->expected_value }}</td>
                            <td>{{ $row->tolerance_1 }}</td>
                            <td>{{ $row->tolerance_2 }}</td>
                            <td>{{ $row->low }}</td>
                            <td>{{ $row->high }}</td>
                            <td>{{ $row->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $row->comments ?: '-' }}</td>
                            <td>{{ $row->recommendations ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">No analytes configured for this standard.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
