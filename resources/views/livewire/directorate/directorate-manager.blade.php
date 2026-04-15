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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="mdi mdi-office-building text-primary"></i>
                            Directorate Management
                        </h2>
                        <p class="text-muted mb-0">Create directorates, assign directors, and manage up to seven labs under each directorate.</p>
                    </div>
                    <button wire:click="showCreateDirectorateModal" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Directorate
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted"><i class="mdi mdi-filter-variant"></i> Filter Directorates</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" wire:model.live="directorateSearch" class="form-control" placeholder="Search by directorate name or code...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select wire:model.live="statusFilter" class="form-select">
                                <option value="">All</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                <i class="mdi mdi-refresh"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h6 class="text-muted text-uppercase mb-3">Selected Directorate</h6>
                    @if($this->selectedDirectorate)
                        <h4 class="mb-1">{{ $this->selectedDirectorate->name }}</h4>
                        <div class="text-muted mb-2">{{ $this->selectedDirectorate->code }}</div>
                        <div class="mb-2">Director: {{ $this->selectedDirectorate->head?->name ?? 'Not assigned' }}</div>
                        <div>Labs configured: {{ $this->selectedDirectorate->labs->count() }}/7</div>
                    @else
                        <p class="text-muted mb-0">Create or select a directorate to manage its labs.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Directorates</h5>
                    <span class="text-muted small">{{ $this->directorates->count() }} record(s)</span>
                </div>
                <div class="card-body">
                    @if($this->directorates->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Director</th>
                                        <th>Labs</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->directorates as $directorate)
                                        <tr class="{{ $selectedDirectorateId === $directorate->id ? 'table-primary' : '' }}">
                                            <td>{{ $directorate->code }}</td>
                                            <td><strong>{{ $directorate->name }}</strong></td>
                                            <td>{{ $directorate->head?->name ?? 'Not assigned' }}</td>
                                            <td>{{ $directorate->labs->count() }}/7</td>
                                            <td>
                                                <span class="badge bg-{{ $directorate->active ? 'success' : 'danger' }} text-white">
                                                    {{ $directorate->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button wire:click="selectDirectorate({{ $directorate->id }})" class="btn btn-sm btn-outline-primary mr-1">Manage Labs</button>
                                                    <button wire:click="showEditDirectorateModal({{ $directorate->id }})" class="btn btn-sm btn-outline-warning mr-1"><i class="mdi mdi-pencil"></i></button>
                                                    <button wire:click="confirmDelete('directorate', {{ $directorate->id }})" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-office-building-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No directorates found</h5>
                            <p class="text-muted">Create the first directorate to begin assigning labs.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Labs</h5>
                        <small class="text-muted">
                            @if($this->selectedDirectorate)
                                {{ $this->selectedDirectorate->name }} · {{ $this->labs->count() }}/7 configured
                            @else
                                Select a directorate to manage labs
                            @endif
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" wire:model.live="labSearch" class="form-control mr-2" placeholder="Search labs..." style="width: 220px;">
                        <button wire:click="showCreateLabModal" class="btn btn-primary" @disabled(!$this->selectedDirectorate)>
                            <i class="mdi mdi-plus"></i> Add Lab
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->selectedDirectorate)
                        @if($this->labs->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Lab Manager</th>
                                            <th>Analysts</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->labs as $lab)
                                            <tr>
                                                <td>{{ $lab->code }}</td>
                                                <td>
                                                    <strong>{{ $lab->name }}</strong>
                                                    <br><small class="text-muted">Start Sample No: {{ $lab->start_sample_no ?: $lab->code }}</small>
                                                </td>
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
                                                    <button wire:click="showEditLabModal({{ $lab->id }})" class="btn btn-sm btn-outline-warning mr-1"><i class="mdi mdi-pencil"></i></button>
                                                    <button wire:click="confirmDelete('lab', {{ $lab->id }})" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No labs configured</h5>
                                <p class="text-muted mb-0">Add up to seven unique labs for this directorate.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-arrow-up-bold-circle-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No directorate selected</h5>
                            <p class="text-muted mb-0">Pick a directorate from the table above to manage its labs.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showDirectorateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingDirectorateId ? 'Edit' : 'Create' }} Directorate</h5>
                        <button type="button" class="btn-close" wire:click="closeDirectorateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Directorate Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="directorateForm.name" class="form-control @error('directorateForm.name') is-invalid @enderror">
                                @error('directorateForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Directorate Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="directorateForm.code" class="form-control @error('directorateForm.code') is-invalid @enderror">
                                @error('directorateForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Head / Director</label>
                                <select wire:model="directorateForm.head_id" class="form-select @error('directorateForm.head_id') is-invalid @enderror">
                                    <option value="">Select a user</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}{{ $user->email ? ' · ' . $user->email : '' }}</option>
                                    @endforeach
                                </select>
                                @error('directorateForm.head_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="directorateForm.active" class="form-check-input" id="directorate_active">
                                    <label class="form-check-label" for="directorate_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDirectorateModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveDirectorate">Save Directorate</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Directorate <span class="text-danger">*</span></label>
                                <select wire:model="labForm.directorate_id" class="form-select @error('labForm.directorate_id') is-invalid @enderror">
                                    <option value="">Select directorate</option>
                                    @foreach($this->directorates as $directorate)
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
                                            @php($analyst = $this->users->firstWhere('id', (int) $analystId))
                                            @if($analyst)
                                                <span class="tag-badge">
                                                    {{ $analyst->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="removeAnalyst({{ $analyst->id }})"></i>
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
                                                <div class="tag-dropdown-item d-flex justify-content-between" wire:click.stop="toggleAnalystSelection({{ $user->id }})">
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
                                    <input type="checkbox" wire:model="labForm.active" class="form-check-input" id="lab_active">
                                    <label class="form-check-label" for="lab_active">Active</label>
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
