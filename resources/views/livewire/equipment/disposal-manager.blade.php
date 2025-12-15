<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-delete-sweep text-primary"></i>
                                Equipment Disposal Management
                            </h2>
                            <p class="text-muted mb-0">Manage equipment disposal requests and approvals</p>
                        </div>
                        <div>
                            <a href="{{ route('equipment.disposal.workflow.index') }}" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-sitemap"></i> Manage Workflows
                            </a>
                            <button wire:click="$dispatch('open-disposal-form')" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create New Disposal Request
                            </button>
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
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by equipment name, number, reason...">
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

    <!-- Tabs Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'draft' ? 'active' : '' }}" 
                            wire:click="switchTab('draft')" type="button">
                        <i class="mdi mdi-file-document-outline"></i> Draft
                        @if($this->draftCount > 0)
                            <span class="badge bg-secondary ms-1">{{ $this->draftCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" 
                            wire:click="switchTab('pending')" type="button">
                        <i class="mdi mdi-clock-outline"></i> Pending Approval
                        @if($this->pendingCount > 0)
                            <span class="badge bg-warning ms-1">{{ $this->pendingCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" 
                            wire:click="switchTab('approved')" type="button">
                        <i class="mdi mdi-check-circle"></i> Approved
                        @if($this->approvedCount > 0)
                            <span class="badge bg-success ms-1">{{ $this->approvedCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" 
                            wire:click="switchTab('rejected')" type="button">
                        <i class="mdi mdi-close-circle"></i> Rejected
                        @if($this->rejectedCount > 0)
                            <span class="badge bg-danger ms-1">{{ $this->rejectedCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" 
                            wire:click="switchTab('all')" type="button">
                        <i class="mdi mdi-format-list-bulleted"></i> All Requests
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Disposal Requests Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($disposals->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $disposals->firstItem() ?? 0 }} to {{ $disposals->lastItem() ?? 0 }} of {{ $disposals->total() }} entries
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
                            <table class="table table-hover table-striped">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Equipment</th>
                                        <th>Requested By</th>
                                        <th>Status</th>
                                        <th>Risk Level</th>
                                        <th>Method</th>
                                        <th>Created</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($disposals as $disposal)
                                        <tr>
                                            <td><strong>#{{ $disposal->id }}</strong></td>
                                            <td>
                                                <strong>{{ $disposal->equipment->name }}</strong><br>
                                                <small class="text-muted">{{ $disposal->equipment->equipment_number }}</small>
                                            </td>
                                            <td>{{ $disposal->requester->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($disposal->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($disposal->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($disposal->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif($disposal->status === 'draft')
                                                    <span class="badge bg-secondary">Draft</span>
                                                @elseif($disposal->status === 'executed')
                                                    <span class="badge bg-primary">Executed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $disposal->risk_level === 'Critical' ? 'danger' : ($disposal->risk_level === 'High' ? 'warning' : 'info') }}">
                                                    {{ $disposal->risk_level }}
                                                </span>
                                            </td>
                                            <td><small>{{ ucfirst($disposal->proposed_method) }}</small></td>
                                            <td>{{ $disposal->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('equipment-disposal-detail', $disposal->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $disposals->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No {{ $activeTab }} disposal requests found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Disposal Request Form Component -->
    @livewire('equipment.disposal-request-form')

    @script
    <script>
    // Auto-trigger disposal form when equipment_id is in URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const equipmentId = urlParams.get('equipment_id');
        
        if (equipmentId) {
            // Wait for Livewire to be ready
            setTimeout(function() {
                Livewire.dispatch('open-disposal-form', { equipmentId: parseInt(equipmentId) });
            }, 500);
        }
    });
    </script>
    @endscript
</div>

