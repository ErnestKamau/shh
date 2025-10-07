<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-history text-warning"></i>
                                Worksheet Execution History
                            </h2>
                            <p class="text-muted mb-0">View and manage worksheet execution history</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by formula name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Formula</label>
                                <select wire:model.live="formulaFilter" class="form-select">
                                    <option value="">All Formulas</option>
                                    @foreach($formulas as $formula)
                                        <option value="{{ $formula['id'] }}">{{ $formula['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Execution Mode</label>
                                <select wire:model.live="executionModeFilter" class="form-select">
                                    <option value="">All Modes</option>
                                    <option value="standalone">Standalone</option>
                                    <option value="sample">Sample</option>
                                    <option value="batch">Batch</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Executions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($executions->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $executions->firstItem() ?? 0 }} to {{ $executions->lastItem() ?? 0 }} of {{ $executions->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="executions-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Formula</th>
                                        <th>Version</th>
                                        <th>Execution Mode</th>
                                        <th>Executor</th>
                                        <th>Sample/Batch</th>
                                        <th>Status</th>
                                        <th>Executed At</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($executions as $execution)
                                        <tr>
                                            <td>{{ $execution->id }}</td>
                                            <td>
                                                <strong>{{ $execution->formulaVersion->formula->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">v{{ $execution->formulaVersion->version_number }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $execution->execution_mode === 'standalone' ? 'primary' : ($execution->execution_mode === 'sample' ? 'success' : 'warning') }}">
                                                    {{ ucfirst($execution->execution_mode) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $execution->executor->name ?? 'System' }}</small>
                                            </td>
                                            <td>
                                                @if($execution->sample)
                                                    <span class="badge badge-success">Sample #{{ $execution->sample->id }}</span>
                                                @elseif($execution->batch)
                                                    <span class="badge badge-warning">Batch #{{ $execution->batch->id }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $execution->is_saved ? 'success' : 'secondary' }}">
                                                    {{ $execution->is_saved ? 'Saved' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $execution->created_at->format('M d, Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showExecutionDetails({{ $execution->id }})" 
                                                            class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                    <button wire:click="deleteExecution({{ $execution->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this execution?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $executions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No executions found</h5>
                            <p class="text-muted">Execute some worksheets to see their history here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Execution Details Modal -->
@if($showExecutionModal && $selectedExecution)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-eye"></i>
                        Execution Details - {{ $selectedExecution->formulaVersion->formula->name }} v{{ $selectedExecution->formulaVersion->version_number }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeExecutionModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Execution Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Execution ID:</strong></td>
                                    <td>{{ $selectedExecution->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Formula:</strong></td>
                                    <td>{{ $selectedExecution->formulaVersion->formula->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Version:</strong></td>
                                    <td>{{ $selectedExecution->formulaVersion->version_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Execution Mode:</strong></td>
                                    <td>{{ ucfirst($selectedExecution->execution_mode) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Executor:</strong></td>
                                    <td>{{ $selectedExecution->executor->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Executed At:</strong></td>
                                    <td>{{ $selectedExecution->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $selectedExecution->is_saved ? 'success' : 'secondary' }}">
                                            {{ $selectedExecution->is_saved ? 'Saved' : 'Draft' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Context Information</h6>
                            <table class="table table-sm">
                                @if($selectedExecution->sample)
                                    <tr>
                                        <td><strong>Sample ID:</strong></td>
                                        <td>{{ $selectedExecution->sample->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sample Name:</strong></td>
                                        <td>{{ $selectedExecution->sample->name ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                                @if($selectedExecution->batch)
                                    <tr>
                                        <td><strong>Batch ID:</strong></td>
                                        <td>{{ $selectedExecution->batch->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Batch Name:</strong></td>
                                        <td>{{ $selectedExecution->batch->name ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($selectedExecution->execution_data)
                        <div class="mt-4">
                            <h6>Execution Data</h6>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">{{ json_encode(json_decode($selectedExecution->execution_data), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($selectedExecution->results)
                        <div class="mt-4">
                            <h6>Results</h6>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">{{ json_encode(json_decode($selectedExecution->results), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeExecutionModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif

    <style>
    .modal.show {
        display: block !important;
    }
    </style>
</div>