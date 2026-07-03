<div>
    <!-- Main Card -->
    <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);">
        <div class="card-header" style="background: #f8f9fa; border: none; border-left: 6px solid var(--color-primary); padding: 18px 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="font-size: 1.35rem; font-weight: 600; color: #222;">
                    <i class="mdi mdi-file-document"></i> Audit Summary Report
                </h4>
                <div>
                    <button wire:click="exportExcel" class="btn btn-sm btn-success me-2">
                        <i class="mdi mdi-download"></i> Export Excel
                    </button>
                    <a href="{{ route('audit.reports.index') }}" class="btn btn-sm btn-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Search and Basic Filters -->
            <div class="card mb-3 mx-3 mt-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="mdi mdi-magnify"></i> Search & Basic Filters</h5>
                        <button type="button" class="btn btn-sm" style="border-color: var(--color-primary); color: var(--color-primary); font-weight: 600;" wire:click="$toggle('showAdvancedFilters')">
                            <i class="mdi {{ $showAdvancedFilters ? 'mdi-filter-variant-minus' : 'mdi-filter-variant-plus' }}"></i> 
                            Advanced Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Bar and Per Page -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="mdi mdi-magnify text-info"></i>
                                </span>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-lg border-start-0" 
                                       placeholder="Search by audit number, title, or scope...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label me-2 mb-0">Show:</label>
                                <select wire:model.live="perPage" class="form-select form-select-lg" id="perPage" style="width: auto;">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            @if($showAdvancedFilters)
            <div class="card mb-3 mx-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="mdi mdi-filter-variant"></i> Advanced Filters</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" wire:model.live="startDate" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" wire:model.live="endDate" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Audit Type</label>
                            <select wire:model.live="auditTypeFilter" class="form-select form-select-lg">
                                <option value="">All Types</option>
                                @foreach($auditTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="statusFilter" class="form-select form-select-lg">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button wire:click="clearFilters" class="btn btn-warning">
                                <i class="mdi mdi-refresh"></i> Clear All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Audit #</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Lead Auditor</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Findings</th>
                            <th>NCs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                        <tr>
                            <td><strong>{{ $audit->audit_number }}</strong></td>
                            <td>{{ Str::limit($audit->title, 40) }}</td>
                            <td><span class="badge badge-info">{{ $audit->auditType?->name ?? 'N/A' }}</span></td>
                            <td>{{ $audit->leadAuditor?->name ?? 'Not Assigned' }}</td>
                            <td>{{ $audit->scheduled_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Scheduled' => 'secondary', 'In Progress' => 'info',
                                        'Record Findings & NC' => 'warning', 'Root Cause Analysis' => 'primary',
                                        'Pending Closure' => 'warning', 'Closed' => 'success',
                                    ];
                                    $badgeClass = $statusColors[$audit->status_name] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">{{ $audit->status_name }}</span>
                            </td>
                            <td class="text-center">{{ $audit->findings->count() }}</td>
                            <td class="text-center">{{ $audit->nonConformances->count() }}</td>
                            <td>
                                <a href="{{ route('audit.audits.show', $audit->id) }}" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="mdi mdi-information text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No audits found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($audits->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $audits->firstItem() }} to {{ $audits->lastItem() }} of {{ $audits->total() }} results
                    </div>
                    <div>
                        {{ $audits->links('livewire::bootstrap') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
