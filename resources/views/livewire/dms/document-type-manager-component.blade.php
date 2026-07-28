<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-folder-multiple text-primary"></i>
                                Document Types Management
                            </h2>
                            <p class="text-muted mb-0">Manage hierarchical document types</p>
                        </div>
                        <button wire:click="showCreateModal" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Add Type
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
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Parent Type</label>
                                <select wire:model.live="parentFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($parentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
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

    <!-- Document Types Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($documentTypes->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $documentTypes->firstItem() ?? 0 }} to {{ $documentTypes->lastItem() ?? 0 }} of {{ $documentTypes->total() }} entries
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover workflow-table livewire-table ls-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Parent</th>
                                        <th>Documents</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentTypes as $type)
                                        <tr>
                                            <td>
                                                <strong>{{ $type->name }}</strong>
                                                @if($type->description)
                                                    <br><small class="text-muted">{{ $type->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $type->code }}</span>
                                            </td>
                                            <td>
                                                @if($type->parent)
                                                    <span class="text-muted">{{ $type->parent->name }}</span>
                                                @else
                                                    <span class="text-muted">Root</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $type->documents_count }}</span>
                                            </td>
                                            <td>
                                                @if($type->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $type->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="dms-actions-group">
                                                    <button type="button"
                                                            wire:click="showEditModal('{{ $type->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="deleteType('{{ $type->id }}')"
                                                            wire:confirm="Are you sure you want to delete this document type?"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete">
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
                        <div class="d-flex justify-content-center mt-3">
                            {{ $documentTypes->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-folder-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No document types found</h5>
                            <p class="text-muted">Start by adding your first document type.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Type Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingType ? 'pencil' : 'plus' }}"></i>
                            {{ $editingType ? 'Edit' : 'Create' }} Document Type
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveType">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-folder text-primary"></i> Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="typeForm.name" class="form-control @error('typeForm.name') is-invalid @enderror" placeholder="Enter document type name">
                                        @error('typeForm.name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-tag text-success"></i> Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="typeForm.code" class="form-control @error('typeForm.code') is-invalid @enderror" placeholder="Enter unique code">
                                        @error('typeForm.code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-text text-info"></i> Description
                                        </label>
                                        <textarea wire:model="typeForm.description" class="form-control @error('typeForm.description') is-invalid @enderror" rows="3" placeholder="Enter description"></textarea>
                                        @error('typeForm.description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-folder-multiple text-warning"></i> Parent Type
                                        </label>
                                        <select wire:model="typeForm.parent_id" class="form-control @error('typeForm.parent_id') is-invalid @enderror">
                                            <option value="">Select parent type (optional)</option>
                                            @foreach($parentTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('typeForm.parent_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-sort text-primary"></i> Sort Order
                                        </label>
                                        <input type="number" wire:model="typeForm.sort_order" class="form-control @error('typeForm.sort_order') is-invalid @enderror" min="0">
                                        @error('typeForm.sort_order') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-format-list-numbered text-success"></i> Numbering Format
                                        </label>
                                        <input type="text" wire:model="typeForm.numbering_format" class="form-control @error('typeForm.numbering_format') is-invalid @enderror" placeholder="{TYPE_CODE}-{YEAR}-{SEQ}">
                                        <small class="form-text text-muted">Use {TYPE_CODE}, {YEAR}, {SEQ} as placeholders</small>
                                        @error('typeForm.numbering_format') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-pencil text-warning"></i> Amendment Limit
                                        </label>
                                        <input type="number" wire:model="typeForm.amendment_limit" class="form-control @error('typeForm.amendment_limit') is-invalid @enderror" min="0" max="10">
                                        @error('typeForm.amendment_limit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="typeForm.is_active" class="form-check-input" id="is_active">
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                            @if($editingType && isset($inheritedPermissions) && is_array($inheritedPermissions) && ((isset($inheritedPermissions['roles']) && count($inheritedPermissions['roles']) > 0) || (isset($inheritedPermissions['users']) && count($inheritedPermissions['users']) > 0)))
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> This type inherits permissions from parent type. You can override them below.
                                            </div>
                                            @endif

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
                                                                   id="type_role_{{ $roleId }}_view">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_view">View</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.add" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_add">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_add">Add</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.edit" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_edit">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.delete" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_delete">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.amend" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_amend">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_amend">Amendment</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.authorize_amendment" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_authorize">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_authorize">Authorize Amendment</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   wire:model="selectedRoles.{{ $roleId }}.permissions.approve_amendment" 
                                                                   class="form-check-input" 
                                                                   id="type_role_{{ $roleId }}_approve">
                                                            <label class="form-check-label" for="type_role_{{ $roleId }}_approve">Approve Amendment</label>
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
                                                                   id="type_user_{{ $index }}_view">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_view">View</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.add" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_add">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_add">Add</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.edit" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_edit">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.delete" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_delete">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.amend" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_amend">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_amend">Amendment</label>
                                                        </div>
                                                        <div class="form-check me-3">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.authorize_amendment" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_authorize">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_authorize">Authorize Amendment</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   wire:model="userPermissions.{{ $index }}.permissions.approve_amendment" 
                                                                   class="form-check-input" 
                                                                   id="type_user_{{ $index }}_approve">
                                                            <label class="form-check-label" for="type_user_{{ $index }}_approve">Approve Amendment</label>
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
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveType">
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

