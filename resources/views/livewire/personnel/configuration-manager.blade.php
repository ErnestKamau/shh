<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-format-list-bulleted-type text-primary"></i>
                                {{ $config }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.personnel_configuration_overview') }}</p>
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
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_configurations') }}">
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
                            <th>{{ __('personnel.description') }}</th>
                            @if($config === 'Job Description')
                                <th>{{ __('personnel.responsibilities') }}</th>
                            @endif
                            <th style="width: 150px">{{ __('personnel.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->configItems as $item)
                            <tr>
                                <td>{{ $this->configItems->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                @if($config === 'Job Description')
                                    <td class="text-center">
                                        <a href="{{ route('showResponsibility', ['id' => $item->id]) }}" class="btn btn-sm pm-act-btn pm-act-btn--view" title="{{ __('personnel.view_responsibilities') }}">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                @endif
                                <td>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditModal('{{ $item->id }}')" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteModal('{{ $item->id }}')" title="{{ __('personnel.delete') }}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $config === 'Job Description' ? 5 : 4 }}" class="text-center text-muted">{{ __('personnel.no_configurations_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showConfigModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-{{ $editingConfigId ? 'pencil' : 'plus' }}"></i> {{ $editingConfigId ? __('personnel.edit_configuration') : __('personnel.add_configuration') }} {{ $config }}</h4>
                        <button type="button" class="close" wire:click="closeConfigModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.name') }}</label>
                            <input type="text" class="form-control" wire:model="configName" placeholder="{{ __('personnel.name') }}...">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ __('personnel.description') }}</label>
                            <textarea class="form-control" wire:model="configDescription" placeholder="{{ __('personnel.description') }}..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveConfig"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeConfigModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.delete_configuration') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">{{ strip_tags(__('personnel.confirm_delete_item', ['name' => $configName])) }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteConfig"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.close') }}</button>
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
        .pm-act-btn--view { border: 1px solid #bbf7d0; color: #15803d; background: #f0fdf4; }
        .pm-act-btn--view:hover { background: #dcfce7; border-color: #86efac; }
        .pm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .pm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }
    </style>
</div>
