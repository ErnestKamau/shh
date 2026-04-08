<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <h2 class="p-4">
        <i class="mdi mdi-key"></i> {{ $this->role->name }} | <small class="text-muted">Roles</small>
    </h2>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}" wire:click="setActiveTab('permissions')">
                        <i class="mdi mdi-key"></i> Role Permissions
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'certifications' ? 'active' : '' }}" wire:click="setActiveTab('certifications')">
                        <i class="mdi mdi-file-certificate-outline"></i> Role Certification
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            @if($activeTab === 'permissions')
                <h5 class="card-title">
                    <i class="mdi mdi-key"></i> Role Permissions
                    <button class="btn btn-outline-info float-right" wire:click="savePermissions"><i class="mdi mdi-content-save"></i> Save</button>
                </h5>
                <div class="card tab-card">
                    <div class="card-header tab-card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            @foreach($this->moduleNames as $moduleName)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#module-{{ $loop->index }}">{{ $moduleName }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tab-content p-3">
                        @foreach($this->moduleNames as $moduleName)
                            @php($components = $this->moduleRules[$moduleName]['components'] ?? [])
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="module-{{ $loop->index }}">
                                <h6>
                                    <button type="button" class="btn btn-sm btn-default" wire:click="toggleModulePermission('{{ $moduleName }}')">
                                        <i class="fas {{ ($permissionsState[$moduleName]['permission'] ?? false) ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                    </button>
                                    {{ $moduleName }} Module
                                </h6>
                                <div class="row">
                                    @foreach($components as $component)
                                        <div class="col-sm-6 border-left-0">
                                            <b>{{ $component }}</b>
                                            <table class="table table-condensed table-sm my-small-text">
                                                <tr>
                                                    @foreach(['Add','Edit','View','Delete'] as $action)
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-default" wire:click="toggleComponentAction('{{ $moduleName }}', '{{ $component }}', '{{ $action }}')">
                                                                <i class="fas {{ ($permissionsState[$moduleName]['components'][$component][$action] ?? false) ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                                            </button>
                                                            {{ $action }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeTab === 'certifications')
                <h5 class="card-title">
                    <i class="mdi mdi-file-certificate-outline"></i> Certifications
                    <button class="btn btn-outline-info float-right" wire:click="openAddCertificationModal"><i class="mdi mdi-plus"></i> Add</button>
                </h5>
                <div class="table-responsive bg-light p-3">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Level</th>
                                <th>Name</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Edited By</th>
                                <th>Description</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->roleCertifications as $item)
                                @php($certification = getSampleTypeQualificationById($item->certification_id))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{!! $item->is_mandatory == 1 ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">Mandatory</span>' : 'Optional' !!}</td>
                                    <td>{{ $certification->name ?? '-' }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td class="text-small">{!! $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                    <td>{{ $item->edited_by ?: 'N/a' }}</td>
                                    <td>{{ $certification->description ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openEditCertificationModal({{ $item->id }})" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openDeleteCertificationModal({{ $item->id }})" title="Delete">
                                            <i class="mdi mdi-delete-empty"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No role certifications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
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
                            <label class="control-label">Certificate Name</label>
                            <select class="form-control" wire:model="certificationId">
                                <option value="">Select certificate...</option>
                                @foreach($this->certificationsList as $cert)
                                    <option value="{{ $cert->id }}">{{ $cert->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label"><input type="checkbox" wire:model="isMandatory"> Mandatory</label>
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

    @if($showDeleteCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> Delete Role Certification</h4>
                        <button type="button" class="close" wire:click="closeDeleteCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">Are you sure you want to delete this role certification?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteCertification"><i class="mdi mdi-delete-empty"></i> Yes</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteCertificationModal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
