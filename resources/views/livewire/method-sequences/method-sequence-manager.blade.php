<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-chart-timeline text-primary"></i>
                                Method Sequence Management
                            </h2>
                            <p class="text-muted mb-0">Create, edit, and manage method sequence workflows</p>
                        </div>
                        <button wire:click="showCreateSequenceModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Create Method Sequence
                        </button>
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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, analyte, or method...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div wire:ignore>
                                    <select wire:model.live="statusFilter" class="form-select status-filter-select" id="statusFilter" style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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

    <!-- Method Sequences Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($sequences->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $sequences->firstItem() ?? 0 }} to {{ $sequences->lastItem() ?? 0 }} of {{ $sequences->total() }} entries
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
                            <table class="table table-striped table-hover" id="sequences-table">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Analyte</th>
                                        <th>Method</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Versions</th>
                                        <th>Created</th>
                                        <th style="width: 250px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sequences as $sequence)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $sequence->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $sequence->analyte->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $sequence->method->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($sequence->description, 40) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $sequence->is_active ? 'success' : 'secondary' }}">
                                                    {{ $sequence->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $sequence->versions->count() }} version(s)
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $sequence->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditSequenceModal({{ $sequence->id }})" 
                                                            class="btn btn-sm btn-outline-primary mr-2" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    @if($sequence->versions->where('is_active', true)->first())
                                                        <a href="{{ route('method-sequences.stages', $sequence->versions->where('is_active', true)->first()->id) }}" 
                                                           class="btn btn-sm btn-outline-success mr-2" title="Edit Stages">
                                                            <i class="mdi mdi-cogs"></i>
                                                        </a>
                                                    @endif
                                                    <button wire:click="showVersionModal({{ $sequence->id }})" 
                                                            class="btn btn-sm btn-outline-info mr-2" title="New Version">
                                                        <i class="mdi mdi-plus-circle"></i>
                                                    </button>
                                                    <button wire:click="cloneSequence({{ $sequence->id }})" 
                                                            class="btn btn-sm btn-outline-secondary mr-2" title="Clone">
                                                        <i class="mdi mdi-content-copy"></i>
                                                    </button>
                                                    <button wire:click="toggleSequenceStatus({{ $sequence->id }})" 
                                                            class="btn btn-sm btn-outline-{{ $sequence->is_active ? 'warning' : 'success' }} mr-2" 
                                                            title="{{ $sequence->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="mdi mdi-{{ $sequence->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteSequence({{ $sequence->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this method sequence?')">
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
                            {{ $sequences->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-chart-timeline fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No method sequences found</h5>
                            <p class="text-muted">Create your first method sequence to get started.</p>
                            <button wire:click="showCreateSequenceModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> Create Method Sequence
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- -------------------------------MODALS------------------------------- --}}
    
    <!-- Create Method Sequence Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus"></i>
                            Create New Method Sequence
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="createSequence">
                            <div class="mb-3">
                                <label for="sequenceName" class="form-label">Sequence Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="sequenceName" class="form-control @error('sequenceName') is-invalid @enderror" id="sequenceName" required>
                                @error('sequenceName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="sequenceDescription" class="form-label">Description</label>
                                <textarea wire:model="sequenceDescription" class="form-control @error('sequenceDescription') is-invalid @enderror" id="sequenceDescription" rows="3"></textarea>
                                @error('sequenceDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="analyteSearch" class="form-label fw-bold">
                                            <i class="mdi mdi-flask-outline text-primary"></i> Analyte <span class="text-danger">*</span>
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="analyteSearch" 
                                                   wire:keyup="searchAnalytes"
                                                   class="form-control @error('analyteId') is-invalid @enderror" 
                                                   id="analyteSearch"
                                                   placeholder="Type to search analytes..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="analyteId" id="analyteId">
                                            
                                            @if($showAnalyteDropdown && $filteredAnalytes->count() > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredAnalytes as $analyte)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectAnalyte({{ $analyte->id }}, '{{ $analyte->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $analyte->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedAnalyteName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge bg-primary">
                                                        {{ $selectedAnalyteName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearAnalyte" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('analyteId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="methodSearch" class="form-label fw-bold">
                                            <i class="mdi mdi-test-tube text-success"></i> Method <span class="text-danger">*</span>
                                        </label>
                                        <div class="searchable-select-container">
                                            <input type="text" 
                                                   wire:model.live="methodSearch" 
                                                   wire:keyup="searchMethods"
                                                   class="form-control @error('methodId') is-invalid @enderror" 
                                                   id="methodSearch"
                                                   placeholder="Type to search methods..."
                                                   autocomplete="off"
                                                   style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                            <input type="hidden" wire:model="methodId" id="methodId">
                                            
                                            @if($showMethodDropdown && $filteredMethods->count() > 0)
                                                <div class="searchable-dropdown">
                                                    @foreach($filteredMethods as $method)
                                                        <div class="dropdown-item" 
                                                             wire:click="selectMethod({{ $method->id }}, '{{ $method->name }}')"
                                                             style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;">
                                                            {{ $method->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($selectedMethodName)
                                                <div class="selected-item mt-2">
                                                    <span class="badge bg-success">
                                                        {{ $selectedMethodName }}
                                                        <i class="mdi mdi-close-circle ms-1" wire:click="clearMethod" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @error('methodId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="sequenceIsActive" class="form-check-input" id="sequenceIsActive">
                                    <label class="form-check-label" for="sequenceIsActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createSequence">
                            <i class="mdi mdi-content-save"></i> Create Sequence
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Edit Method Sequence Modal -->
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-pencil"></i>
                            Edit Method Sequence
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="updateSequence">
                            <div class="mb-3">
                                <label for="editSequenceName" class="form-label">Sequence Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="sequenceName" class="form-control @error('sequenceName') is-invalid @enderror" id="editSequenceName" required>
                                @error('sequenceName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="editSequenceDescription" class="form-label">Description</label>
                                <textarea wire:model="sequenceDescription" class="form-control @error('sequenceDescription') is-invalid @enderror" id="editSequenceDescription" rows="3"></textarea>
                                @error('sequenceDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editAnalyteId" class="form-label fw-bold">
                                            <i class="mdi mdi-flask-outline text-primary"></i> Analyte <span class="text-danger">*</span>
                                        </label>
                                        <div wire:ignore>
                                            <select class="form-select form-select-lg modern-select @error('analyteId') is-invalid @enderror" id="editAnalyteId" required style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                                <option value="">-- Select Analyte --</option>
                                                @foreach($analytes as $analyte)
                                                    <option value="{{ $analyte->id }}">{{ $analyte->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('analyteId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editMethodId" class="form-label fw-bold">
                                            <i class="mdi mdi-test-tube text-success"></i> Method <span class="text-danger">*</span>
                                        </label>
                                        <div wire:ignore>
                                            <select class="form-select form-select-lg modern-select @error('methodId') is-invalid @enderror" id="editMethodId" required style="border-radius: 8px; border: 2px solid #e3e6f0;">
                                                <option value="">-- Select Method --</option>
                                                @foreach($methods as $method)
                                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('methodId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="sequenceIsActive" class="form-check-input" id="editSequenceIsActive">
                                    <label class="form-check-label" for="editSequenceIsActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="updateSequence">
                            <i class="mdi mdi-content-save"></i> Update Sequence
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- New Version Modal -->
    @if($showVersionModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus-circle"></i>
                            Create New Version
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showVersionModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-alert-circle"></i> Please fix the following errors:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="createNewVersion">
                            <div class="mb-3">
                                <label for="versionDescription" class="form-label">Version Description <span class="text-danger">*</span></label>
                                <textarea wire:model="versionDescription" class="form-control @error('versionDescription') is-invalid @enderror" id="versionDescription" rows="3" 
                                          placeholder="Describe the changes in this version..." required></textarea>
                                @error('versionDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVersionModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createNewVersion">
                            <i class="mdi mdi-content-save"></i> Create Version
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <style>
        .modal.show {
            display: block !important;
        }
        
        /* Modern Select Styling */
        .modern-select {
            transition: all 0.3s ease;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            background-color: #ffffff !important;
            border: 2px solid #e3e6f0 !important;
        }
        
        .modern-select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
            outline: none;
            background-color: #ffffff !important;
        }
        
        .modern-select:hover {
            border-color: #007bff !important;
        }
        
        .modern-select option {
            padding: 10px;
        }
        
        /* Status Filter Select Styling */
        .status-filter-select {
            transition: all 0.3s ease;
        }
        
        .status-filter-select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
            outline: none;
        }
        
        .status-filter-select:hover {
            border-color: #007bff !important;
        }
        
        /* Enhanced label styling */
        .form-label.fw-bold {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-label i {
            margin-right: 5px;
        }
        
        /* Select2 Container Styling */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid #e3e6f0 !important;
            border-radius: 8px !important;
            min-height: 48px !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #ffffff !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection:hover {
            border-color: #007bff !important;
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection__rendered {
            padding-left: 0 !important;
            line-height: 2rem !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 46px !important;
        }
        
        .select2-container--bootstrap-5 .select2-dropdown {
            border: 2px solid #007bff !important;
            border-radius: 8px !important;
        }
        
        /* Fix Select2 Choice/Tag Styling */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 12px !important;
            padding-right: 20px !important;
            line-height: 44px !important;
            color: #495057 !important;
            font-weight: 500 !important;
            vertical-align: middle !important;
            display: flex !important;
            align-items: center !important;
            height: 44px !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
            font-style: italic !important;
            padding-left: 12px !important;
            line-height: 44px !important;
            vertical-align: middle !important;
        }
        
        /* Hide the search input when single select */
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 8px 12px !important;
            font-size: 14px !important;
        }
        
        /* Style dropdown options */
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 10px 12px !important;
            font-size: 14px !important;
            color: #495057 !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #495057 !important;
            font-weight: 500 !important;
        }
        
        /* Remove any duplicate text display */
        .select2-container--bootstrap-5 .select2-selection__rendered::after {
            display: none !important;
        }
        
        /* Ensure proper spacing in selection area */
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
        }
        
        /* Fix any blue text appearing below selection */
        .select2-container .select2-selection__rendered span {
            display: none !important;
        }
        
        .select2-container .select2-selection__rendered {
            color: #495057 !important;
            font-weight: 500 !important;
            padding-left: 12px !important;
            line-height: 44px !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            font-size: 14px !important;
        }
        
        /* Additional alignment fixes */
        .select2-container--bootstrap-5 .select2-selection {
            display: flex !important;
            align-items: center !important;
            padding: 0 !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 44px !important;
            top: 2px !important;
            right: 12px !important;
            display: flex !important;
            align-items: center !important;
            position: absolute !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection__arrow b {
            border-color: #6c757d transparent transparent transparent !important;
            border-style: solid !important;
            border-width: 5px 4px 0 4px !important;
            height: 0 !important;
            left: 50% !important;
            margin-left: -4px !important;
            margin-top: -2px !important;
            position: absolute !important;
            top: 50% !important;
            width: 0 !important;
        }
        
        /* Remove any additional spans or duplicate elements */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered > span {
            display: none !important;
        }
        
        /* Ensure only the selected text is shown */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            padding-left: 12px !important;
            line-height: 44px !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            font-size: 14px !important;
        }
        
        /* Fix any placeholder styling issues */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
            font-style: italic !important;
        }
        
        /* Ensure dropdown items are properly styled */
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 12px 16px !important;
            margin: 0 !important;
            border-bottom: 1px solid #f1f3f4 !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option:last-child {
            border-bottom: none !important;
        }
        
        /* Searchable Select Styles */
        .searchable-select-container {
            position: relative;
        }
        
        .searchable-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .searchable-dropdown .dropdown-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.2s;
        }
        
        .searchable-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .searchable-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .selected-item .badge {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
    </style>
        
    <script>
        console.log('Method Sequence Manager script loaded');
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.searchable-select-container')) {
                // Close all dropdowns
                @this.set('showAnalyteDropdown', false);
                @this.set('showMethodDropdown', false);
            }
        });
    </script>
</div>
