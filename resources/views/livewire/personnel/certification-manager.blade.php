<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="card tab-card">
        <div class="card-header tab-card-header d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'active' ? 'active' : '' }}" wire:click="setActiveTab('active')">
                        <i class="mdi mdi-file-certificate"></i> Certifications
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'archived' ? 'active' : '' }}" wire:click="setActiveTab('archived')">
                        <i class="mdi mdi-file-certificate-outline"></i> Archived
                    </button>
                </li>
            </ul>
            <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateModal">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search certifications...">
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
                            <th>Created</th>
                            <th>Status</th>
                            <th>Edited By</th>
                            <th>Description</th>
                            <th style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->certifications as $item)
                            <tr>
                                <td>{{ $this->certifications->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td class="text-small">{!! $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <td>{{ $item->edited_by ?: 'N/a' }}</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openEditModal({{ $item->id }})" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteModal({{ $item->id }})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No certifications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingCertificationId ? 'pencil' : 'plus' }}"></i> {{ $editingCertificationId ? 'Edit' : 'Add' }} Certification</h4>
                        <button type="button" class="close" wire:click="closeCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" wire:model="certificationName" placeholder="Certification name...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Status</label>
                            <select class="form-control" wire:model="certificationStatus">
                                <option value="0">Active</option>
                                <option value="1">Archived</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" wire:model="certificationDescription" rows="4" placeholder="Certification description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveCertification"><i class="mdi mdi-content-save"></i> Save</button>
                        <button type="button" class="btn btn-default" wire:click="closeCertificationModal">Close</button>
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
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Certification</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            Are you sure you want to delete <strong>{{ $certificationName }}</strong>?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteCertification"><i class="mdi mdi-delete"></i> Delete</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
