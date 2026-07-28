<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-plus text-primary"></i>
                                Active Documents Management
                            </h2>
                            <p class="text-muted mb-0">Manage and organize your active documents</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Upload Document
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
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by title, number, or description...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Type</label>
                                <select wire:model.live="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Owner</label>
                                <select wire:model.live="ownerFilter" class="form-select">
                                    <option value="">All Owners</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="pending_approval">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">AI Status</label>
                                <select wire:model.live="kbFilter" class="form-select">
                                    <option value="">All</option>
                                    <option value="indexed">Indexed</option>
                                    <option value="not_indexed">Not Indexed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" wire:model.live="expiringFilter" class="form-check-input" id="expiringFilter">
                                <label class="form-check-label" for="expiringFilter">
                                    <i class="mdi mdi-alert text-warning"></i> Show only expiring documents (within 30 days)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($documents->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} entries
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover workflow-table livewire-table ls-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Owner</th>
                                        <th>Status</th>
                                        <th>Expiry Date</th>
                                        <th>AI Knowledge</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrichedDocuments as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="mdi mdi-file-document text-primary" style="font-size: 24px;"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $item['document']->title }}</strong>
                                                        <br><small class="text-muted">{{ $item['document']->document_number }}</small>
                                                        @if($item['document']->description)
                                                            <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($item['document']->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $item['document']->documentType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $item['document']->owner->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $item['status_class'] }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item['document']->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item['expiry']['has_expiry'])
                                                    <span class="badge badge-{{ $item['expiry']['expiry_class'] }}">
                                                        {{ $item['document']->expiry_date->format('M d, Y') }}
                                                    </span>
                                                    @if($item['expiry']['show_warning'])
                                                        <br><small class="text-muted">{{ $item['expiry']['days_remaining'] }} days remaining</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No expiry</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item['document']->is_kb_indexed)
                                                    <span class="badge badge-success" title="Last Indexed: {{ $item['document']->kb_last_indexed_at?->format('M d, Y H:i') }}">
                                                        <i class="mdi mdi-robot"></i> Indexed
                                                    </span>
                                                    @if($item['document']->kb_indexing_status === 'failed')
                                                        <br><small class="text-danger">Indexing failed</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-outline-secondary">
                                                        <i class="mdi mdi-robot-off"></i> Not Indexed
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item['document']->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="dms-actions-group">
                                                    <button type="button"
                                                            wire:click="showEditModal('{{ $item['document']->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="toggleAIIndexing('{{ $item['document']->id }}')"
                                                            class="btn btn-sm rm-act-btn {{ $item['document']->is_kb_indexed ? 'rm-act-btn--view' : 'rm-act-btn--expand' }}"
                                                            title="{{ $item['document']->is_kb_indexed ? 'Remove from AI' : 'Index in AI' }}">
                                                        <i class="mdi mdi-robot"></i>
                                                    </button>
                                                    <a href="{{ route('dms.download', $item['document']->id) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="Download">
                                                        <i class="mdi mdi-download"></i>
                                                    </a>
                                                    <button type="button"
                                                            wire:click="archiveDocument('{{ $item['document']->id }}')"
                                                            wire:confirm="Are you sure you want to archive this document?"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Archive">
                                                        <i class="mdi mdi-archive"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $documents->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-file-document-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No documents found</h5>
                            <p class="text-muted">Start by uploading your first document.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingDocument ? 'pencil' : 'plus' }}"></i>
                            {{ $editingDocument ? 'Edit' : 'Upload' }} Document
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveDocument">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-folder text-primary"></i> Document Type <span class="text-danger">*</span>
                                        </label>
                                        <select wire:model="documentForm.document_type_id" class="form-control @error('documentForm.document_type_id') is-invalid @enderror">
                                            <option value="">Select document type</option>
                                            @foreach($documentTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('documentForm.document_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-account text-success"></i> Owner
                                        </label>
                                        <select wire:model="documentForm.owner_id" class="form-control @error('documentForm.owner_id') is-invalid @enderror">
                                            <option value="">Select owner</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('documentForm.owner_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-text text-info"></i> Title <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="documentForm.title" class="form-control @error('documentForm.title') is-invalid @enderror" placeholder="Enter document title">
                                        @error('documentForm.title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-text text-warning"></i> Description
                                        </label>
                                        <textarea wire:model="documentForm.description" class="form-control @error('documentForm.description') is-invalid @enderror" rows="3" placeholder="Enter document description"></textarea>
                                        @error('documentForm.description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-calendar text-primary"></i> Expiry Date
                                        </label>
                                        <input type="date" wire:model="documentForm.expiry_date" class="form-control @error('documentForm.expiry_date') is-invalid @enderror">
                                        @error('documentForm.expiry_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-tag text-success"></i> Status
                                        </label>
                                        <select wire:model="documentForm.status" class="form-control @error('documentForm.status') is-invalid @enderror">
                                            <option value="draft">Draft</option>
                                            <option value="pending_approval">Pending Approval</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        @error('documentForm.status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            @if(!$editingDocument)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-file-upload text-primary"></i> File Upload <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" wire:model="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX, TXT (Max 50MB)</small>
                                        @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Access Control & Permissions Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card mt-3 border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="mdi mdi-shield-account text-primary"></i> Access Control & Permissions
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Role-Based Permissions -->
                                            <h6 class="fw-bold mb-3">Role-Based Access</h6>
                                            
                                            <!-- Role Selection -->
                                            <div class="row mb-3">
                                                <div class="col-md-9">
                                                    <div x-data="{
                                                        open: false,
                                                        search: '',
                                                        selected: @entangle('selectedRole').live,
                                                        roles: {{ json_encode($roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()) }},
                                                        selectedRoleIds: {{ json_encode(array_keys($selectedRoles)) }},
                                                        get filteredRoles() {
                                                            let availableRoles = this.roles.filter(role => !this.selectedRoleIds.includes(role.id));
                                                            if (!this.search) return availableRoles;
                                                            return availableRoles.filter(role => 
                                                                role.name.toLowerCase().includes(this.search.toLowerCase())
                                                            );
                                                        },
                                                        selectRole(roleId) {
                                                            this.selected = roleId;
                                                            this.open = false;
                                                            this.search = '';
                                                        },
                                                        getSelectedName() {
                                                            const role = this.roles.find(r => r.id == this.selected);
                                                            return role ? role.name : '';
                                                        }
                                                    }" class="searchable-dropdown-wrapper">
                                                        <div class="single-select-container" @click="open = !open">
                                                            <input 
                                                                type="text" 
                                                                x-model="search"
                                                                :placeholder="selected ? getSelectedName() : 'Search roles...'"
                                                                @focus="open = true"
                                                                class="form-control searchable-input-single"
                                                                autocomplete="off"
                                                            >
                                                            <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                                        </div>

                                                        <div x-show="open" 
                                                             @click.away="open = false"
                                                             x-transition
                                                             class="dropdown-list">
                                                            <template x-if="filteredRoles.length > 0">
                                                                <div class="options-list">
                                                                    <template x-for="role in filteredRoles" :key="role.id">
                                                                        <div @click="selectRole(role.id)" 
                                                                             class="option-item"
                                                                             :class="{ 'selected': selected == role.id }">
                                                                            <i class="mdi mdi-check-circle text-primary" x-show="selected == role.id"></i>
                                                                            <span x-text="role.name"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="filteredRoles.length === 0">
                                                                <div class="no-results">
                                                                    <i class="mdi mdi-alert-circle-outline"></i>
                                                                    <span>No roles available</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <button wire:click="applyRole" type="button" class="btn btn-primary w-100">
                                                        <i class="mdi mdi-plus"></i> Apply
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selected Roles Cards -->
                                            @if(count($selectedRoles) > 0)
                                            @foreach($selectedRoles as $roleId => $roleData)
                                            <div class="card border mb-3 permission-card">
                                                <div class="card-header permission-card-header d-flex justify-content-between align-items-center">
                                                    <strong class="text-dark">{{ $roleData['role_name'] }}</strong>
                                                    <button wire:click="removeRole({{ $roleId }})" 
                                                            type="button" 
                                                            class="btn btn-sm btn-danger rounded-pill">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.view" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_view">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_view">View</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.add" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_add">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_add">Add</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.edit" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_edit">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.delete" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_delete">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.amend" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_amend">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_amend">Amendment</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.authorize_amendment" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_authorize">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_authorize">Authorize Amendment</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.approve_amendment" 
                                                                   class="form-check-input" 
                                                                   id="role_{{ $roleId }}_approve">
                                                            <label class="form-check-label" for="role_{{ $roleId }}_approve">Approve Amendment</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <p class="text-muted">No roles selected. Use the dropdown above to add role permissions.</p>
                                            @endif

                                            <!-- User-Based Permissions -->
                                            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                                <h6 class="fw-bold mb-0">User-Based Access (Individual Overrides)</h6>
                                                <button wire:click="addUserPermission" type="button" class="btn btn-sm btn-outline-primary">
                                                    <i class="mdi mdi-plus"></i> Add User Permission
                                                </button>
                                            </div>

                                            @if(count($userPermissions) > 0)
                                            @foreach($userPermissions as $index => $userPerm)
                                            <div class="card border mb-3 permission-card">
                                                <div class="card-header permission-card-header-user d-flex justify-content-between align-items-center">
                                                    @php
                                                        $userIdWireModel = "userPermissions.{$index}.user_id";
                                                        $usersJson = $availableUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();
                                                    @endphp
                                                    <div style="min-width: 300px;" 
                                                         x-data="{
                                                            open: false,
                                                            search: '',
                                                            selected: @entangle($userIdWireModel).live,
                                                            users: @js($usersJson),
                                                            get filteredUsers() {
                                                                if (!this.search) return this.users;
                                                                return this.users.filter(user => 
                                                                    user.name.toLowerCase().includes(this.search.toLowerCase())
                                                                );
                                                            },
                                                            selectUser(userId) {
                                                                this.selected = userId;
                                                                this.open = false;
                                                                this.search = '';
                                                            },
                                                            getSelectedName() {
                                                                const user = this.users.find(u => u.id == this.selected);
                                                                return user ? user.name : '';
                                                            }
                                                         }" 
                                                         class="searchable-dropdown-wrapper">
                                                        <div class="single-select-container single-select-sm" @click="open = !open">
                                                            <input 
                                                                type="text" 
                                                                x-model="search"
                                                                :placeholder="selected ? getSelectedName() : 'Search users...'"
                                                                @focus="open = true"
                                                                class="form-control searchable-input-single"
                                                                autocomplete="off"
                                                            >
                                                            <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                                        </div>

                                                        <div x-show="open" 
                                                             @click.away="open = false"
                                                             x-transition
                                                             class="dropdown-list">
                                                            <template x-if="filteredUsers.length > 0">
                                                                <div class="options-list">
                                                                    <template x-for="user in filteredUsers" :key="user.id">
                                                                        <div @click="selectUser(user.id)" 
                                                                             class="option-item"
                                                                             :class="{ 'selected': selected == user.id }">
                                                                            <i class="mdi mdi-check-circle text-success" x-show="selected == user.id"></i>
                                                                            <span x-text="user.name"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="filteredUsers.length === 0">
                                                                <div class="no-results">
                                                                    <i class="mdi mdi-alert-circle-outline"></i>
                                                                    <span>No users found</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <button wire:click="removeUserPermission({{ $index }})" 
                                                            type="button" 
                                                            class="btn btn-sm btn-danger rounded-pill">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.view" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_view">
                                                            <label class="form-check-label" for="user_{{ $index }}_view">View</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.add" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_add">
                                                            <label class="form-check-label" for="user_{{ $index }}_add">Add</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.edit" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_edit">
                                                            <label class="form-check-label" for="user_{{ $index }}_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.delete" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_delete">
                                                            <label class="form-check-label" for="user_{{ $index }}_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.amend" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_amend">
                                                            <label class="form-check-label" for="user_{{ $index }}_amend">Amendment</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.authorize_amendment" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_authorize">
                                                            <label class="form-check-label" for="user_{{ $index }}_authorize">Authorize Amendment</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.approve_amendment" 
                                                                   class="form-check-input" 
                                                                   id="user_{{ $index }}_approve">
                                                            <label class="form-check-label" for="user_{{ $index }}_approve">Approve Amendment</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Access Control Section -->

                            <!-- AI Knowledge Base Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card mt-3 border">
                                        <div class="card-header bg-soft-info">
                                            <h6 class="mb-0">
                                                <i class="mdi mdi-robot text-primary"></i> AI Knowledge Base Settings
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" wire:model="documentForm.is_kb_indexed" id="is_kb_indexed">
                                                <label class="form-check-label fw-bold" for="is_kb_indexed">
                                                    Index in AI Knowledge Base
                                                </label>
                                                <p class="text-muted small">When enabled, this document's content will be searchable by the AI assistant.</p>
                                            </div>

                                            @if($documentForm['is_kb_indexed'])
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label small fw-bold">KB Collection</label>
                                                            <input type="text" wire:model="documentForm.kb_collection" class="form-control form-control-sm" placeholder="e.g. Policies, Procedures">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label small fw-bold">Chunk Size</label>
                                                            <input type="number" wire:model="documentForm.kb_chunk_size" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label small fw-bold">Chunk Overlap</label>
                                                            <input type="number" wire:model="documentForm.kb_chunk_overlap" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="form-label small fw-bold">Manual Content Override (Optional)</label>
                                                            <textarea wire:model="documentForm.kb_content" class="form-control form-control-sm" rows="5" placeholder="If provided, this text will be indexed instead of the file content."></textarea>
                                                            <small class="text-muted">Use this if the file is an image or needs custom context for the AI.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End AI Section -->
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveDocument">
                            <i class="mdi mdi-content-save"></i> Save
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
    
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
        }
    
        /* Ensure modal is properly positioned and scrollable */
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
    
        /* Smooth scrolling for modal content */
        .modal-body {
            scroll-behavior: smooth;
        }
    
        /* Ensure modal backdrop doesn't interfere with scrolling */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
        }

        /* Modern Searchable Dropdown Styling */
        .searchable-input-single {
            border: none;
            outline: none;
            box-shadow: none !important;
            padding: 4px 0;
            width: 100%;
            background: transparent;
            color: inherit;
        }
        
        .searchable-input-single:focus {
            border: none !important;
            box-shadow: none !important;
        }
        
        .single-select-container {
            position: relative;
            min-height: 45px;
            border: 1px solid #ced4da;
            border-radius: 12px;
            padding: 8px 40px 8px 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .single-select-sm {
            min-height: 38px;
            padding: 6px 35px 6px 10px;
        }
        
        .single-select-container:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .single-select-container:has(.searchable-input-single:focus) {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .dropdown-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
            font-size: 20px;
            color: #6c757d;
        }

        .dropdown-arrow.rotated {
            transform: translateY(-50%) rotate(180deg);
        }

        .searchable-dropdown-wrapper {
            position: relative;
        }

        .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 4px;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .options-list {
            padding: 8px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .option-item:hover {
            background: #f8f9fa;
        }
        
        .option-item.selected {
            background: rgba(0, 123, 255, 0.08);
            font-weight: 500;
        }
        
        .option-item i {
            font-size: 18px;
        }

        .no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .no-results i {
            font-size: 24px;
        }

        /* Permission Card Styling */
        .permission-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .permission-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .permission-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 12px 16px;
        }

        .permission-card-header-user {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-bottom: 1px solid #a5d6a7;
            padding: 12px 16px;
        }

        .rounded-pill {
            border-radius: 50px !important;
        }
    </style>
</div>

