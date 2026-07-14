<div class="container-fluid">
    <style>
        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .tag-select-input:hover {
            border-color: #86b7fe;
        }

        .tag-select-input:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
            outline: none;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #0d6efd;
            color: #fff;
            border-radius: 999px;
            font-size: .875rem;
            font-weight: 500;
            line-height: 1.2;
            white-space: nowrap;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: .85;
        }

        .tag-badge i:hover {
            opacity: 1;
        }

        .tag-input {
            flex: 1 1 140px;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px 0;
            font-size: .95rem;
            background: transparent;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 2px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #86b7fe;
            border-radius: .375rem;
            max-height: 220px;
            overflow-y: auto;
            z-index: 2100;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .12);
        }

        .tag-dropdown-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f5;
            font-size: .95rem;
        }

        .tag-dropdown-item:hover {
            background: #f8f9fa;
        }

        .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }
    </style>

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="mdi mdi-flask-outline text-primary"></i>
                            Labs Management
                        </h2>
                        <p class="text-muted mb-0">View, create, edit, and delete labs across all directorates.</p>
                    </div>
                    <button wire:click="showCreateLabModal" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Lab
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert --}}
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted"><i class="mdi mdi-filter-variant"></i> Filter Labs</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, phone, email...">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Zone</label>
                            <select wire:model.live="zoneFilter" class="form-select">
                                <option value="">All Zones</option>
                                @foreach($this->zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->key }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Directorate</label>
                            <select wire:model.live="directorateFilter" class="form-select">
                                <option value="">All Directorates</option>
                                @foreach($this->directoratesForFilter as $directorate)
                                    <option value="{{ $directorate->id }}">{{ $directorate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="statusFilter" class="form-select">
                                <option value="">All</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1 mb-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Labs Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Labs</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">{{ $this->allLabs->total() }} record(s)</span>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }} per page</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->allLabs->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Zone</th>
                                        <th>Directorate</th>
                                        <th>Lab Manager</th>
                                        <th>Analysts</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->allLabs as $lab)
                                        <tr>
                                            <td>{{ $lab->code }}</td>
                                            <td>
                                                <strong>{{ $lab->name }}</strong>
                                                <br><small class="text-muted">Start Sample No: {{ $lab->start_sample_no ?: $lab->code }}</small>
                                            </td>
                                            <td>{{ $lab->zone?->key ?? '—' }}</td>
                                            <td>{{ $lab->directorate?->name ?? '—' }}</td>
                                            <td>{{ $lab->manager?->name ?? 'Not assigned' }}</td>
                                            <td>
                                                <span title="{{ $this->analystNames($lab->analyst_ids ?? []) }}">
                                                    {{ count($lab->analyst_ids ?? []) }} assigned
                                                </span>
                                            </td>
                                            <td>{{ $lab->phone1 }}</td>
                                            <td>{{ $lab->email }}</td>
                                            <td>
                                                <span class="badge bg-{{ $lab->active ? 'success' : 'danger' }} text-white">
                                                    {{ $lab->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button wire:click="viewLab('{{ $lab->id }}')" class="btn btn-sm btn-outline-info" title="View details"><i class="mdi mdi-eye"></i></button>
                                                    <button wire:click="showEditLabModal('{{ $lab->id }}')" class="btn btn-sm btn-outline-warning" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                                    <button wire:click="confirmDelete('{{ $lab->id }}')" class="btn btn-sm btn-outline-danger" title="Delete"><i class="mdi mdi-delete"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $this->allLabs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No labs found</h5>
                            <p class="text-muted mb-0">Try adjusting your filters or add a new lab.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- View Lab Modal --}}
    @if($showViewModal && !empty($viewLabData))
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <div>
                            <h5 class="modal-title text-white mb-0">
                                <i class="mdi mdi-flask-outline me-2"></i>{{ $viewLabData['name'] }}
                            </h5>
                            <small class="text-white-50">{{ $viewLabData['code'] }}</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body p-0">

                        {{-- Lab Info --}}
                        <div class="p-4 border-bottom">
                            <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: .75rem; letter-spacing: .08em;">
                                <i class="mdi mdi-information-outline me-1"></i> Lab Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-identifier text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Lab Code</div>
                                            <div class="fw-semibold">{{ $viewLabData['code'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-numeric text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Start Sample No</div>
                                            <div class="fw-semibold">{{ $viewLabData['start_sample_no'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-map-marker-outline text-success"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Zone</div>
                                            <div class="fw-semibold">{{ $viewLabData['zone'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-office-building text-success"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Directorate</div>
                                            <div class="fw-semibold">{{ $viewLabData['directorate'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-phone text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Phone</div>
                                            <div class="fw-semibold">{{ $viewLabData['phone1'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-email-outline text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Email</div>
                                            <div class="fw-semibold">{{ $viewLabData['email'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle {{ $viewLabData['active'] ? 'bg-success' : 'bg-danger' }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="mdi mdi-check-circle-outline {{ $viewLabData['active'] ? 'text-success' : 'text-danger' }}"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Status</div>
                                            <span class="badge bg-{{ $viewLabData['active'] ? 'success' : 'danger' }} text-white">
                                                {{ $viewLabData['active'] ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Lab Manager --}}
                        <div class="p-4 border-bottom">
                            <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: .75rem; letter-spacing: .08em;">
                                <i class="mdi mdi-account-tie me-1"></i> Lab Manager
                            </h6>
                            @if($viewLabData['manager'])
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:48px;height:48px;font-size:1.1rem;">
                                        {{ strtoupper(substr($viewLabData['manager']['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $viewLabData['manager']['name'] }}</div>
                                        <div class="text-muted small"><i class="mdi mdi-email-outline me-1"></i>{{ $viewLabData['manager']['email'] }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted d-flex align-items-center gap-2">
                                    <i class="mdi mdi-account-off-outline" style="font-size:1.4rem;"></i>
                                    No lab manager assigned
                                </div>
                            @endif
                        </div>

                        {{-- Analysts --}}
                        <div class="p-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: .75rem; letter-spacing: .08em;">
                                <i class="mdi mdi-account-group me-1"></i>
                                Analysts
                                <span class="badge bg-secondary ms-1">{{ count($viewLabData['analysts']) }}</span>
                            </h6>
                            @if(!empty($viewLabData['analysts']))
                                <div class="row g-2">
                                    @foreach($viewLabData['analysts'] as $analyst)
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #f8f9fa;">
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;">
                                                    {{ strtoupper(substr($analyst['name'], 0, 1)) }}
                                                </div>
                                                <div style="min-width:0;">
                                                    <div class="fw-semibold text-truncate">{{ $analyst['name'] }}</div>
                                                    <div class="text-muted small text-truncate"><i class="mdi mdi-email-outline me-1"></i>{{ $analyst['email'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted d-flex align-items-center gap-2">
                                    <i class="mdi mdi-account-off-outline" style="font-size:1.4rem;"></i>
                                    No analysts assigned to this lab
                                </div>
                            @endif
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                        <button type="button" class="btn btn-warning" wire:click="showEditLabModal('{{ $viewLabData['id'] }}')">
                            <i class="mdi mdi-pencil me-1"></i> Edit Lab
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Create / Edit Lab Modal --}}
    @if($showLabModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingLabId ? 'Edit' : 'Create' }} Lab</h5>
                        <button type="button" class="btn-close" wire:click="closeLabModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Zone</label>
                                <select wire:model.live="labForm.zone_id" class="form-select @error('labForm.zone_id') is-invalid @enderror">
                                    <option value="">— Select a zone (optional) —</option>
                                    @foreach($this->zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->key }}</option>
                                    @endforeach
                                </select>
                                @error('labForm.zone_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Directorate <span class="text-danger">*</span></label>
                                <select wire:model.live="labForm.directorate_id" class="form-select @error('labForm.directorate_id') is-invalid @enderror">
                                    <option value="">
                                        {{ $labForm['zone_id'] !== '' ? 'Select directorate in this zone' : 'Select directorate' }}
                                    </option>
                                    @foreach($this->directoratesForLab as $directorate)
                                        <option value="{{ $directorate->id }}">{{ $directorate->name }} ({{ $directorate->labs->count() }}/7)</option>
                                    @endforeach
                                </select>
                                @error('labForm.directorate_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lab Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="labForm.name" class="form-control @error('labForm.name') is-invalid @enderror">
                                @error('labForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lab Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="labForm.code" class="form-control @error('labForm.code') is-invalid @enderror">
                                @error('labForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Sample No</label>
                                <input type="text" wire:model="labForm.start_sample_no" class="form-control @error('labForm.start_sample_no') is-invalid @enderror" placeholder="Defaults to lab code if left blank">
                                @error('labForm.start_sample_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lab Manager</label>
                                <select wire:model="labForm.manager_id" class="form-select @error('labForm.manager_id') is-invalid @enderror">
                                    <option value="">Select a user</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('labForm.manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Analysts Working In This Lab</label>
                                <div class="tag-select-container @error('labForm.analyst_ids') is-invalid @enderror" wire:click="$set('showAnalystDropdown', true)">
                                    <div class="tag-select-input">
                                        @foreach($labForm['analyst_ids'] as $analystId)
                                            @php($analyst = $this->users->firstWhere('id', $analystId))
                                            @if($analyst)
                                                <span class="tag-badge">
                                                    {{ $analyst->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="removeAnalyst('{{ $analyst->id }}')"></i>
                                                </span>
                                            @endif
                                        @endforeach

                                        <input
                                            type="text"
                                            wire:model.live="analystSearch"
                                            wire:keyup="searchAnalysts"
                                            class="tag-input"
                                            placeholder="{{ count($labForm['analyst_ids']) > 0 ? 'Add more analysts...' : 'Search analysts...' }}"
                                            autocomplete="off"
                                        >
                                    </div>

                                    @if($showAnalystDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredAnalysts as $user)
                                                <div class="tag-dropdown-item d-flex justify-content-between" wire:click.stop="toggleAnalystSelection('{{ $user->id }}')">
                                                    <span>{{ $user->name }}</span>
                                                    @if(in_array((string) $user->id, $labForm['analyst_ids'], true))
                                                        <i class="mdi mdi-check text-success"></i>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No analysts found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                @error('labForm.analyst_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @error('labForm.analyst_ids.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lab Phone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="labForm.phone1" class="form-control @error('labForm.phone1') is-invalid @enderror">
                                @error('labForm.phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lab Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="labForm.email" class="form-control @error('labForm.email') is-invalid @enderror">
                                @error('labForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="labForm.active" class="form-check-input" id="lab_active_page">
                                    <label class="form-check-label" for="lab_active_page">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLabModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLab">Save Lab</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone.
                        </div>
                        <p class="mb-1"><strong>{{ $deleteDetails['title'] ?? '' }}</strong></p>
                        <p class="text-muted mb-1">{{ $deleteDetails['subtitle'] ?? '' }}</p>
                        <p class="mb-0">{{ $deleteDetails['summary'] ?? '' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteRecord">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
