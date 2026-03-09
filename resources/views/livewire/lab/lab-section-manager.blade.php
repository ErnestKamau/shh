<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-sitemap text-primary"></i>
                                Lab Sections Management
                            </h2>
                            <p class="text-muted mb-0">Manage lab sections, sample stages, and verifier configurations</p>
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

    <!-- Tabs -->
    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'lab-sections' ? 'active' : '' }}"
                        wire:click="setActiveTab('lab-sections')"
                        style="cursor: pointer;">
                        <i class="mdi mdi-sitemap"></i> Lab Sections
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'sample-stages' ? 'active' : '' }}"
                        wire:click="setActiveTab('sample-stages')"
                        style="cursor: pointer;">
                        <i class="mdi mdi-sitemap"></i> Sample Stages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'verifier-config' ? 'active' : '' }}"
                        wire:click="setActiveTab('verifier-config')"
                        style="cursor: pointer;">
                        <i class="mdi mdi-account-check"></i> Verifier Configuration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'report-configs' ? 'active' : '' }}"
                        wire:click="setActiveTab('report-configs')"
                        style="cursor: pointer;">
                        <i class="mdi mdi-file-document-outline"></i> Report Configurations
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3">
            <!-- LAB SECTIONS TAB -->
            @if($activeTab === 'lab-sections')
            <div class="tab-pane-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Lab Sections</h5>

                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text"
                            wire:model.live="labSectionSearch"
                            class="form-control"
                            placeholder="Search lab sections...">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="labSectionStatusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button wire:click="showCreateLabSectionModal" class=" float-right btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Lab Section
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Section Head</th>
                                <th>Lab</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->labSections as $section)
                            <tr>
                                <td>
                                    <span class="">{{ $section->code }}</span>
                                </td>
                                <td>
                                    <strong>{{ $section->name }}</strong>
                                </td>
                                <td>{{ $section->getSectionHead()->name ?? '-' }}</td>
                                <td>{{ $section->getLabDetails()->name ?? '-' }}</td>
                                <td>
                                    <span class="badge p-2 bg-{{ $section->active ? 'success' : 'danger' }}" style="color: white;">
                                        {{ $section->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button wire:click="showEditLabSectionModal({{ $section->id }})"
                                            class="btn btn-sm btn-outline-warning mr-1"
                                            title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDeleteLabSection({{ $section->id }})"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No lab sections found</h5>
                                    <p class="text-muted">Create your first lab section to get started.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- SAMPLE STAGES TAB -->
            @if($activeTab === 'sample-stages')
            <div class="tab-pane-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Sample Stages</h5>

                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text"
                            wire:model.live="sampleStageSearch"
                            class="form-control"
                            placeholder="Search sample stages...">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="sampleStageStatusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button wire:click="showCreateSampleStageModal" class=" float-right btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Sample Stage
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Workflow</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>System Stage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->sampleStages as $stage)
                            <tr>
                                <td>
                                    <span class="">{{ $stage->code }}</span>
                                </td>
                                <td>
                                    <strong>{{ $stage->name }}</strong>
                                </td>
                                <td>{{ $stage->sample_workflow }}</td>
                                <td>
                                    <span class="badge bg-info" style="color: white;">Level {{ $stage->level }}</span>
                                </td>
                                <td>
                                    <span class="badge p-2 bg-{{ $stage->active ? 'success' : 'danger' }}" style="color: white;">
                                        {{ $stage->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($stage->is_system == 1)
                                    <span class="badge bg-secondary" style="color: white;">
                                        <i class="mdi mdi-cog"></i> System
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button wire:click="showEditSampleStageModal({{ $stage->id }})"
                                            class="btn btn-sm btn-outline-warning mr-1"
                                            title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDeleteSampleStage({{ $stage->id }})"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="mdi mdi-sitemap text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No sample stages found</h5>
                                    <p class="text-muted">Create your first sample stage to get started.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- VERIFIER CONFIGURATION TAB -->
            @if($activeTab === 'verifier-config')
            <div class="tab-pane-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Verifier Configuration</h5>

                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-9">
                        <input type="text"
                            wire:model.live="verifierSearch"
                            class="form-control"
                            placeholder="Search verifiers...">
                    </div>
                    <div class="col-md-3">
                        <button wire:click="showCreateVerifierModal" class=" float-right btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Verifier
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Name</th>
                                <th>Title</th>
                                <th>Lab Sections</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->verifiers as $verifier)
                            <tr>
                                <td>
                                    <strong>{{ $verifier->approvername }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary p-2" style="color: white;">{{ $verifier->title }}</span>
                                </td>
                                <td>{{ $verifier->sectionname }}</td>
                                <td>
                                    <div class="d-flex">
                                        <button wire:click="showEditVerifierModal({{ $verifier->id }})"
                                            class="btn btn-sm btn-outline-warning mr-1"
                                            title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDeleteVerifier({{ $verifier->id }})"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="mdi mdi-account-check text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No verifiers found</h5>
                                    <p class="text-muted">Create your first verifier configuration to get started.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- REPORT CONFIGURATIONS TAB --}}
            @if($activeTab === 'report-configs')
            <div class="tab-pane-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="mdi mdi-file-document-outline text-primary"></i> Report Configurations</h5>
                    <button wire:click="showCreateReportConfigModal" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus"></i> Add Report Format
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Lab Section</th>
                                <th>Report Format</th>
                                <th>Document Code</th>
                                <th>Issue Date</th>
                                <th>Revision</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->reportConfigs as $config)
                            <tr>
                                <td>
                                    <span class="badge bg-info" style="color: white;">
                                        {{ $config->labSection->code ?? '-' }}
                                    </span>
                                    {{ $config->labSection->name ?? '-' }}
                                </td>
                                <td><strong>{{ $config->reportFormat->report_name ?? '-' }}</strong></td>
                                <td><code>{{ $config->document_code ?? '-' }}</code></td>
                                <td>{{ $config->issue_date ? $config->issue_date->format('d M Y') : '-' }}</td>
                                <td>{{ $config->revision_number ?? '-' }}</td>
                                <td class="text-center">
                                    @if($config->is_default)
                                    <span class="badge bg-success" style="color: white;"><i class="mdi mdi-check"></i> Default</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('livewire.report-formats.builder', $config->report_format_id) }}"
                                            class="btn btn-sm btn-outline-info mr-1" title="Builder">
                                            <i class="mdi mdi-table-cog"></i>
                                        </a>
                                        <button wire:click="showEditReportConfigModal({{ $config->id }})"
                                            class="btn btn-sm btn-outline-warning mr-1" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button wire:click="deleteReportConfig({{ $config->id }})"
                                            class="btn btn-sm btn-outline-danger" title="Remove"
                                            wire:confirm="Remove this report configuration?">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="mdi mdi-file-document-outline text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No report configurations yet</h5>
                                    <p class="text-muted">Click "Add Report Format" to configure document control numbers for a lab section.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- LAB SECTION MODAL -->
    @if($showLabSectionModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingLabSection ? 'pencil' : 'plus' }}"></i>
                        {{ $editingLabSection ? 'Edit' : 'Add' }} Lab Section
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeLabSectionModal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text"
                            wire:model="labSectionForm.name"
                            class="form-control @error('labSectionForm.name') is-invalid @enderror"
                            placeholder="Lab Section Name...">
                        @error('labSectionForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text"
                            wire:model="labSectionForm.code"
                            class="form-control @error('labSectionForm.code') is-invalid @enderror"
                            placeholder="Lab Section Code...">
                        @error('labSectionForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-account text-primary"></i> Section Head</label>
                        <div class="tag-select-container" wire:click="$set('showSectionHeadDropdown', true)">
                            <div class="tag-select-input">
                                @if($this->selectedSectionHead)
                                <span class="tag-badge">
                                    {{ $this->selectedSectionHead->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForm.section_head_id', null)"></i>
                                </span>
                                @endif

                                <input type="text"
                                    wire:model.live="sectionHeadSearch"
                                    class="tag-input"
                                    placeholder="{{ $this->selectedSectionHead ? '' : 'Search section heads...' }}"
                                    autocomplete="off">
                            </div>

                            @if($showSectionHeadDropdown && count($this->filteredSectionHeads) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredSectionHeads as $user)
                                <div class="tag-dropdown-item" wire:click.stop="selectSectionHead({{ $user->id }})">
                                    {{ $user->name }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-flask text-primary"></i> Lab</label>
                        <div class="tag-select-container" wire:click="$set('showLabDropdown', true)">
                            <div class="tag-select-input">
                                @if($this->selectedLab)
                                <span class="tag-badge">
                                    {{ $this->selectedLab->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForm.lab_id', null)"></i>
                                </span>
                                @endif

                                <input type="text"
                                    wire:model.live="labSearch"
                                    class="tag-input"
                                    placeholder="{{ $this->selectedLab ? '' : 'Search labs...' }}"
                                    autocomplete="off">
                            </div>

                            @if($showLabDropdown && count($this->filteredLabs) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredLabs as $lab)
                                <div class="tag-dropdown-item" wire:click.stop="selectLab({{ $lab->id }})">
                                    {{ $lab->code }} - {{ $lab->name }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox"
                            wire:model="labSectionForm.active"
                            class="form-check-input"
                            id="labSectionActive">
                        <label class="form-check-label" for="labSectionActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeLabSectionModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveLabSection">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SAMPLE STAGE MODAL -->
    @if($showSampleStageModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingSampleStage ? 'pencil' : 'plus' }}"></i>
                        {{ $editingSampleStage ? 'Edit' : 'Add' }} Sample Stage
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSampleStageModal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text"
                            wire:model="sampleStageForm.name"
                            class="form-control @error('sampleStageForm.name') is-invalid @enderror"
                            placeholder="Sample Analysis Stage Name...">
                        @error('sampleStageForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text"
                            wire:model="sampleStageForm.code"
                            class="form-control @error('sampleStageForm.code') is-invalid @enderror"
                            placeholder="Stage Code...">
                        @error('sampleStageForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label class="form-label">Sample Workflow <span class="text-danger">*</span></label>
                                <select wire:model="sampleStageForm.sample_workflow"
                                    class="form-control @error('sampleStageForm.sample_workflow') is-invalid @enderror">
                                    <option value="">Select Sample Workflow...</option>
                                    @foreach($workflows as $workflow)
                                    <option value="{{ $workflow }}">{{ $workflow }}</option>
                                    @endforeach
                                </select>
                                @error('sampleStageForm.sample_workflow') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Stage Level <span class="text-danger">*</span></label>
                                <input type="number"
                                    wire:model="sampleStageForm.level"
                                    class="form-control @error('sampleStageForm.level') is-invalid @enderror"
                                    min="1"
                                    placeholder="Stage Level...">
                                @error('sampleStageForm.level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="checkbox"
                            wire:model="sampleStageForm.is_system"
                            class="form-check-input"
                            id="isSystemStage">
                        <label class="form-check-label" for="isSystemStage">Is System Stage</label>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox"
                            wire:model="sampleStageForm.active"
                            class="form-check-input"
                            id="sampleStageActive">
                        <label class="form-check-label" for="sampleStageActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeSampleStageModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveSampleStage">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- VERIFIER MODAL -->
    @if($showVerifierModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingVerifier ? 'pencil' : 'plus' }}"></i>
                        {{ $editingVerifier ? 'Edit' : 'Add' }} Verifier Configuration
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeVerifierModal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text"
                            wire:model="verifierForm.title"
                            class="form-control @error('verifierForm.title') is-invalid @enderror"
                            placeholder="e.g., Senior Chemist, Lab Manager...">
                        @error('verifierForm.title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-account text-primary"></i> Approver <span class="text-danger">*</span></label>
                        <div class="tag-select-container" wire:click="$set('showVerifierUserDropdown', true)">
                            <div class="tag-select-input">
                                @if($this->selectedVerifierUser)
                                <span class="tag-badge">
                                    {{ $this->selectedVerifierUser->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('verifierForm.user_id', null)"></i>
                                </span>
                                @endif

                                <input type="text"
                                    wire:model.live="verifierUserSearch"
                                    class="tag-input"
                                    placeholder="{{ $this->selectedVerifierUser ? '' : 'Search users...' }}"
                                    autocomplete="off">
                            </div>

                            @if($showVerifierUserDropdown && count($this->filteredVerifierUsers) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredVerifierUsers as $user)
                                <div class="tag-dropdown-item" wire:click.stop="selectVerifierUser({{ $user->id }})">
                                    {{ $user->name }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @error('verifierForm.user_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-layers text-primary"></i> Lab Sections <span class="text-danger">*</span></label>
                        <div class="tag-select-container" wire:click="$set('showVerifierSectionsDropdown', true)">
                            <div class="tag-select-input">
                                @foreach($this->selectedVerifierSections as $section)
                                <span class="tag-badge">
                                    {{ $section->code }} - {{ $section->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="toggleVerifierSection({{ $section->id }})"></i>
                                </span>
                                @endforeach

                                <input type="text"
                                    wire:model.live="verifierSectionsSearch"
                                    class="tag-input"
                                    placeholder="{{ count($this->selectedVerifierSections) > 0 ? '' : 'Search sections...' }}"
                                    autocomplete="off">
                            </div>

                            @if($showVerifierSectionsDropdown && count($this->filteredVerifierSections) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredVerifierSections as $section)
                                <div class="tag-dropdown-item"
                                    wire:click.stop="toggleVerifierSection({{ $section->id }})"
                                    style="{{ in_array($section->id, $verifierForm['section_ids']) ? 'background-color: #e7f3ff;' : '' }}">
                                    @if(in_array($section->id, $verifierForm['section_ids']))
                                    <i class="mdi mdi-check-circle text-primary"></i>
                                    @endif
                                    {{ $section->code }} - {{ $section->name }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @error('verifierForm.section_ids') <span class="text-danger">{{ $message }}</span> @enderror
                        @if(count($this->selectedVerifierSections) > 0)
                        <small class="text-muted">{{ count($this->selectedVerifierSections) }} section(s) selected</small>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeVerifierModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveVerifier">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- REPORT CONFIG MODAL --}}
    @if($showReportConfigModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingReportConfig ? 'pencil' : 'plus' }}"></i>
                        {{ $editingReportConfig ? 'Edit' : 'Add' }} Report Format Configuration
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeReportConfigModal"></button>
                </div>
                <div class="modal-body">

                    {{-- Lab Section --}}
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-sitemap text-primary"></i> Lab Section <span class="text-danger">*</span></label>
                        <select wire:model="reportConfigForm.sample_analysis_stage_id"
                            class="form-control @error('reportConfigForm.sample_analysis_stage_id') is-invalid @enderror">
                            <option value="">Select Lab Section...</option>
                            @foreach($this->labSections as $section)
                            <option value="{{ $section->id }}">{{ $section->code }} - {{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('reportConfigForm.sample_analysis_stage_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Report Format --}}
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Report Format <span class="text-danger">*</span></label>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" wire:model.live="isCreatingNewReportFormat" class="form-check-input" id="createNewFormatToggle">
                                <label class="form-check-label" for="createNewFormatToggle" style="font-size: 0.85rem;">Create New Format</label>
                            </div>
                        </div>

                        @if($isCreatingNewReportFormat)
                        <div class="row bg-light p-3 rounded border">
                            <div class="col-md-6 mb-2">
                                <label class="form-label text-muted" style="font-size: 0.85rem;">Format Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newReportFormatName" class="form-control form-control-sm @error('newReportFormatName') is-invalid @enderror" placeholder="e.g. Microbiology COA">
                                @error('newReportFormatName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label text-muted" style="font-size: 0.85rem;">Format Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newReportFormatCode" class="form-control form-control-sm @error('newReportFormatCode') is-invalid @enderror" placeholder="e.g. MIC-COA">
                                @error('newReportFormatCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @else
                        <select wire:model="reportConfigForm.report_format_id"
                            class="form-control @error('reportConfigForm.report_format_id') is-invalid @enderror">
                            <option value="">Select Report Format...</option>
                            @foreach($reportFormats as $format)
                            <option value="{{ $format->id }}">{{ $format->report_name }} ({{ $format->report_code }})</option>
                            @endforeach
                        </select>
                        @error('reportConfigForm.report_format_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    {{-- Document Code --}}
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="mdi mdi-barcode text-primary"></i> Document Control Number</label>
                        <input type="text"
                            wire:model="reportConfigForm.document_code"
                            class="form-control @error('reportConfigForm.document_code') is-invalid @enderror"
                            placeholder="e.g., MIC-COA-001">
                        @error('reportConfigForm.document_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        {{-- Issue Date --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label"><i class="mdi mdi-calendar text-primary"></i> Issue Date</label>
                                <input type="date"
                                    wire:model="reportConfigForm.issue_date"
                                    class="form-control @error('reportConfigForm.issue_date') is-invalid @enderror">
                                @error('reportConfigForm.issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Revision Number --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label"><i class="mdi mdi-history text-primary"></i> Revision / Version</label>
                                <input type="text"
                                    wire:model="reportConfigForm.revision_number"
                                    class="form-control @error('reportConfigForm.revision_number') is-invalid @enderror"
                                    placeholder="e.g., v1.5">
                                @error('reportConfigForm.revision_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Is Default --}}
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            wire:model="reportConfigForm.is_default"
                            class="form-check-input"
                            id="reportConfigIsDefault">
                        <label class="form-check-label" for="reportConfigIsDefault">
                            Set as Default format for this Lab Section
                            <small class="text-muted d-block">When generating reports, this format's document control numbers will be used automatically.</small>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeReportConfigModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveReportConfig">
                        <i class="mdi mdi-content-save"></i> Save Configuration
                    </button>
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
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        <strong>Warning:</strong> This action cannot be undone. The record will be permanently deleted from the database.
                    </div>

                    <p class="mb-3">Are you sure you want to delete the following
                        @if($deleteType === 'lab-section')
                        <strong>Lab Section</strong>
                        @elseif($deleteType === 'sample-stage')
                        <strong>Sample Stage</strong>
                        @elseif($deleteType === 'verifier')
                        <strong>Verifier Configuration</strong>
                        @endif
                        ?
                    </p>

                    <div class="card">
                        <div class="card-body bg-light">
                            @if($deleteType === 'lab-section')
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th style="width: 35%;">Name:</th>
                                    <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Code:</th>
                                    <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Section Head:</th>
                                    <td>{{ $deleteDetails['section_head'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Lab:</th>
                                    <td>{{ $deleteDetails['lab'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>{{ $deleteDetails['active'] ?? '-' }}</td>
                                </tr>
                            </table>
                            @elseif($deleteType === 'sample-stage')
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th style="width: 35%;">Name:</th>
                                    <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Code:</th>
                                    <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Workflow:</th>
                                    <td>{{ $deleteDetails['workflow'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>{{ $deleteDetails['level'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>{{ $deleteDetails['active'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>System Stage:</th>
                                    <td>{{ $deleteDetails['is_system'] ?? '-' }}</td>
                                </tr>
                            </table>
                            @elseif($deleteType === 'verifier')
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th style="width: 35%;">Name:</th>
                                    <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $deleteDetails['title'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Lab Sections:</th>
                                    <td>{{ $deleteDetails['sections'] ?? '-' }}</td>
                                </tr>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="
                            @if($deleteType === 'lab-section')
                                deleteLabSection
                            @elseif($deleteType === 'sample-stage')
                                deleteSampleStage
                            @elseif($deleteType === 'verifier')
                                deleteVerifier
                            @endif
                        ">
                        <i class="mdi mdi-delete"></i> Yes, Delete Permanently
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

        /* Tag-based Select Styling */
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
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .tag-select-input:hover {
            border-color: #007bff;
        }

        .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .tag-badge i:hover {
            opacity: 1;
        }

        .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
        }

        .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -2px;
        }

        .tag-dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .tag-dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .nav-link {
            cursor: pointer;
        }
    </style>

    @script
    <script>
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.tag-select-container')) {
                $wire.set('showSectionHeadDropdown', false);
                $wire.set('showLabDropdown', false);
                $wire.set('showVerifierUserDropdown', false);
                $wire.set('showVerifierSectionsDropdown', false);
            }
        });
    </script>
    @endscript
</div>