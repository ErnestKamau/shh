<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <h2 class="p-4">
        <i class="mdi mdi-key"></i> {{ $this->role->name }} | <small class="text-muted">{{ __('personnel.roles') }}</small>
    </h2>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}" wire:click="setActiveTab('permissions')">
                        <i class="mdi mdi-key"></i> {{ __('personnel.role_permissions') }}
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'certifications' ? 'active' : '' }}" wire:click="setActiveTab('certifications')">
                        <i class="mdi mdi-file-certificate-outline"></i> {{ __('personnel.role_certification') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content p-3">
            @if($activeTab === 'permissions')
                <h5 class="card-title">
                    <i class="mdi mdi-key"></i> {{ __('personnel.role_permissions') }}
                    <button class="btn btn-outline-info float-right" wire:click="savePermissions"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                </h5>
                <div class="card tab-card">
                    <div class="card-header tab-card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            @foreach($this->moduleNames as $moduleName)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $activeModuleTab === $loop->index ? 'active' : '' }}" wire:click="setActiveModuleTab({{ $loop->index }})">
                                        {{ $moduleName }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tab-content p-3">
                        @foreach($this->moduleNames as $moduleName)
                            @php($components = $this->moduleRules[$moduleName]['components'] ?? [])
                            <div class="{{ $activeModuleTab === $loop->index ? 'd-block' : 'd-none' }}">
                                <h6>
                                    <button type="button" class="btn btn-sm btn-default" wire:click="toggleModulePermission('{{ $moduleName }}')">
                                        <i class="fas {{ ($permissionsState[$moduleName]['permission'] ?? false) ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                    </button>
                                    {{ $moduleName }} {{ __('personnel.module') }}
                                </h6>
                                <div class="row">
                                    @foreach($components as $component)
                                        <div class="col-sm-6 border-left-0">
                                            <b>{{ $component }}</b>
                                            <table class="table table-condensed table-sm my-small-text">
                                                <tr>
                                                    @foreach(['add' => __('personnel.add'), 'edit' => __('personnel.edit'), 'view' => __('personnel.view'), 'delete' => __('personnel.delete')] as $action => $label)
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-default" wire:click="toggleComponentAction('{{ $moduleName }}', '{{ $component }}', '{{ $action }}')">
                                                                <i class="fas {{ ($permissionsState[$moduleName]['components'][$component][$action] ?? false) ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                                            </button>
                                                            {{ $label }}
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
                    <i class="mdi mdi-file-certificate-outline"></i> {{ __('personnel.certifications') }}
                    <button class="btn btn-outline-info float-right" wire:click="openAddCertificationModal"><i class="mdi mdi-plus"></i> {{ __('personnel.add') }}</button>
                </h5>
                <div class="table-responsive bg-light p-3">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>{{ __('personnel.no') }}</th>
                                <th>{{ __('personnel.level') }}</th>
                                <th>{{ __('personnel.name') }}</th>
                                <th>{{ __('personnel.created') }}</th>
                                <th>{{ __('personnel.status') }}</th>
                                <th>{{ __('personnel.edited_by') }}</th>
                                <th>{{ __('personnel.description') }}</th>
                                <th style="width: 120px">{{ __('personnel.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->roleCertifications as $item)
                                @php($certification = getSampleTypeQualificationById($item->certification_id))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{!! $item->is_mandatory == 1 ? '<i style="color:red" class="mdi mdi-star-four-points"></i><span style="color: red">'.__('personnel.mandatory').'</span>' : __('personnel.optional') !!}</td>
                                    <td>{{ $certification->name ?? '-' }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td class="text-small">{!! $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                    <td>{{ $item->edited_by ?: 'N/a' }}</td>
                                    <td>{{ $certification->description ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditCertificationModal({{ $item->id }})" title="{{ __('personnel.edit') }}">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteCertificationModal({{ $item->id }})" title="{{ __('personnel.delete') }}">
                                            <i class="mdi mdi-delete-empty"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">{{ __('personnel.no_role_certifications_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($showCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingCertificationId ? 'pencil' : 'plus' }}"></i> {{ $editingCertificationId ? __('personnel.edit_certification') : __('personnel.add_certification') }}</h4>
                        <button type="button" class="close" wire:click="closeCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.certificate_name') }}</label>
                            <select class="form-control" wire:model="certificationId">
                                <option value="">{{ __('personnel.select_certificate') }}</option>
                                @foreach($this->certificationsList as $cert)
                                    <option value="{{ $cert->id }}">{{ $cert->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label"><input type="checkbox" wire:model="isMandatory"> {{ __('personnel.mandatory') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveCertification"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeCertificationModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteCertificationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> {{ __('personnel.delete_role_certification') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">{{ __('personnel.confirm_delete_role_certification') }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteCertification"><i class="mdi mdi-delete-empty"></i> {{ __('personnel.yes') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteCertificationModal">{{ __('personnel.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pm-act-btn { border-radius: var(--ls-radius-sm, 6px); padding: 0.3rem var(--ls-btn-pad-x-sm, 0.65rem); margin-right: 3px; font-size: var(--ls-text-sm, 0.75rem); }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--edit { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .pm-act-btn--edit:hover { background: #dbeafe; border-color: #93c5fd; }
        .pm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .pm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }
    </style>
</div>
