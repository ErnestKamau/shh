<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
        <h6 class="mb-0 text-muted">
            <i class="mdi mdi-file-document-edit"></i> Amendment Records
        </h6>
    </div>
    <div class="card-body p-0">
        <!-- Message Alert -->
        @if($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show m-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="p-3 border-bottom">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search amendments...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Date From</label>
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Date To</label>
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm mt-4">
                            <i class="mdi mdi-filter-remove"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amendments Table -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Batch Code</th>
                        <th>Version</th>
                        <th>Reason</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($amendments as $amendment)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $amendment->sampleHeader->batch_code ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">v{{ $amendment->version_number }}</span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $amendment->reason }}">
                                    {{ $amendment->reason }}
                                </div>
                            </td>
                            <td>{{ $amendment->creator ?? 'N/A' }}</td>
                            <td>{{ $amendment->created_at ? \Carbon\Carbon::parse($amendment->created_at)->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>
                                @php
                                    $status = $this->getAmendmentStatus($amendment);
                                    $statusClass = $this->getAmendmentStatusClass($amendment);
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-center">
                                <button wire:click="viewAmendmentDetails({{ $amendment->id }})" 
                                        class="btn btn-outline-primary btn-sm" 
                                        title="View Details">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p class="mt-2">No amendment records found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

