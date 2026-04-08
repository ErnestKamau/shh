<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-key-change"></i> Roles</h5>
            <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i> Add Role
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search roles...">
                </div>
                <div class="col-md-4">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">Show {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Level</th>
                            <th>Active</th>
                            <th style="width: 260px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->roles as $item)
                            <tr>
                                <td>{{ $this->roles->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->level }}</td>
                                <td class="text-small">{!! $item->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openEditModal({{ $item->id }})" title="Edit">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <a class="btn btn-outline-success btn-sm" href="{{ route('view-organizational-role', ['id' => $item->id]) }}" title="View">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteModal({{ $item->id }})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Showing {{ $this->roles->firstItem() ?? 0 }} to {{ $this->roles->lastItem() ?? 0 }} of {{ $this->roles->total() }} records
                </small>
                {{ $this->roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if($showRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $editingRoleId ? 'pencil-outline' : 'plus' }}"></i>
                            {{ $editingRoleId ? 'Edit Role' : 'Add Role' }}
                        </h4>
                        <button type="button" class="close" wire:click="closeRoleModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="roleName" placeholder="Role name...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" wire:model="roleDescription" placeholder="Description..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Role Level <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control" wire:model="roleLevel">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label"><input type="checkbox" wire:model="roleActive"> Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveRole"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeRoleModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Role</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            Are you sure you want to delete role <strong>{{ $roleName }}</strong>?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteRole"><i class="mdi mdi-delete"></i> Delete</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
