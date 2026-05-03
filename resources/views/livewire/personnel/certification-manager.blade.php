<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-certificate text-primary"></i>
                                {{ __('personnel.certifications') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.certifications_overview') }}</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" wire:click="openCreateModal">
                            <i class="mdi mdi-plus"></i> {{ __('personnel.add') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'active' ? 'active' : '' }}" wire:click="setActiveTab('active')">
                        <i class="mdi mdi-file-certificate"></i> {{ __('personnel.certifications') }}
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'archived' ? 'active' : '' }}" wire:click="setActiveTab('archived')">
                        <i class="mdi mdi-file-certificate-outline"></i> {{ __('personnel.archived') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_certifications') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>{{ __('personnel.no') }}</th>
                            <th>{{ __('personnel.name') }}</th>
                            <th>{{ __('personnel.created') }}</th>
                            <th>{{ __('personnel.status') }}</th>
                            <th>{{ __('personnel.edited_by') }}</th>
                            <th>{{ __('personnel.description') }}</th>
                            <th style="width: 140px;">{{ __('personnel.actions') }}</th>
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
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditModal({{ $item->id }})" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteModal({{ $item->id }})" title="{{ __('personnel.delete') }}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ __('personnel.no_certifications_found') }}</td>
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
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingCertificationId ? 'pencil' : 'plus' }}"></i> {{ $editingCertificationId ? __('personnel.edit_certification') : __('personnel.add_certification') }}</h4>
                        <button type="button" class="close" wire:click="closeCertificationModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.name') }}</label>
                            <input type="text" class="form-control" wire:model="certificationName" placeholder="{{ __('personnel.certification_name') }}...">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.status') }}</label>
                            <select class="form-control" wire:model="certificationStatus">
                                <option value="0">{{ __('personnel.active') }}</option>
                                <option value="1">{{ __('personnel.archived') }}</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('personnel.description') }}</label>
                            <textarea class="form-control" wire:model="certificationDescription" rows="4" placeholder="{{ __('personnel.certification_description') }}..."></textarea>
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

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.delete_certification') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            {!! __('personnel.confirm_delete_certification', ['name' => $certificationName]) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteCertification"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pm-act-btn { border-radius: 7px; padding: 4px 8px; margin-right: 3px; font-size: 12px; }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--edit { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .pm-act-btn--edit:hover { background: #dbeafe; border-color: #93c5fd; }
        .pm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .pm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }
    </style>
</div>
