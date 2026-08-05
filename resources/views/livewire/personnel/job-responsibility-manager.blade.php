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
                                <i class="mdi mdi-layers text-primary"></i>
                                {{ __('personnel.job_responsibilities') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.job_responsibilities_overview') }}</p>
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
                        <i class="mdi mdi-layers"></i> {{ __('personnel.active') }} {{ __('personnel.responsibility') }}
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $activeTab === 'inactive' ? 'active' : '' }}" wire:click="setActiveTab('inactive')">
                        <i class="mdi mdi-layers-off"></i> {{ __('personnel.inactive') }} {{ __('personnel.responsibility') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_job_description') }}">
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
                            <th>{{ __('personnel.created_at') }}</th>
                            <th>{{ __('personnel.edited_by') }}</th>
                            <th>{{ __('personnel.active') }}</th>
                            <th style="width: 100px">{{ __('personnel.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->responsibilities as $item)
                            <tr>
                                <td>{{ $this->responsibilities->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td>{{ $item->edited_by ? (getUserById($item->edited_by)->name ?? 'N/a') : 'N/a' }}</td>
                                <td class="text-small">{!! $item->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <td>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditModal({{ $item->id }})" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ __('personnel.no_job_responsibilities_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $editingResponsibilityId ? 'pencil' : 'plus' }}"></i>
                            {{ $editingResponsibilityId ? __('personnel.edit') : __('personnel.add') }} {{ $this->designation->name }} {{ __('personnel.responsibility') }}
                        </h4>
                        <button type="button" class="close" wire:click="closeModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.name') }}</label>
                            <div class="tag-select-container" wire:click="$set('showConfigDropdown', true)">
                                <div class="tag-select-input">
                                    @if($selectedConfigId)
                                        @php($selectedConfig = collect($filteredConfigs)->firstWhere('id', (int) $selectedConfigId))
                                        <span class="tag-badge">
                                            {{ $selectedConfig['label'] ?? '' }}
                                            <i class="mdi mdi-close-circle" wire:click.stop="clearConfig"></i>
                                        </span>
                                    @endif
                                    <input type="text" wire:model.live="configSearch" wire:keyup="searchConfigs" class="tag-input" placeholder="{{ $selectedConfigId ? '' : __('personnel.search_responsibility') }}" autocomplete="off">
                                </div>
                                @if($showConfigDropdown && count($filteredConfigs) > 0)
                                    <div class="tag-dropdown">
                                        @foreach($filteredConfigs as $item)
                                            <div class="tag-dropdown-item" wire:click.stop="selectConfig({{ $item['id'] }})">
                                                {{ $item['label'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" wire:model="isActive" id="responsibility_active">
                            <label class="form-check-label" for="responsibility_active">{{ __('personnel.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveResponsibility"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .tag-select-container { position: relative; cursor: text; }
        .tag-select-input { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: var(--ls-control-h, 34px); padding: 0.35rem 0.45rem; background: #fff; border: 1px solid var(--ls-color-border, #e2e8f0); border-radius: var(--ls-radius-sm, 6px); transition: all 0.3s ease; }
        .tag-select-input:hover { border-color: #007bff; }
        .tag-select-input:focus-within { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); outline: none; }
        .tag-badge { display: inline-flex; align-items: center; gap: 4px; padding: 0.15rem 0.45rem; background-color: var(--ls-color-primary-soft, var(--color-primary-soft)); color: var(--ls-color-primary, var(--color-primary)); border: 1px solid var(--ls-color-primary-border, var(--color-primary-border-soft)); border-radius: 16px; font-size: var(--ls-text-md, 0.875rem); font-weight: 500; white-space: nowrap; }
        .tag-badge i { cursor: pointer; font-size: 1rem; opacity: 0.8; }
        .tag-input { flex: 1; min-width: 140px; border: none; outline: none; padding: 4px; font-size: var(--ls-text-base, 0.8125rem); }
        .tag-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 2px solid #007bff; border-top: none; border-radius: 0 0 8px 8px; max-height: 260px; overflow-y: auto; z-index: 1060; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-top: -2px; }
        .tag-dropdown-item { padding: 10px 16px; cursor: pointer; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0; }
        .tag-dropdown-item:hover { background-color: #f8f9fa; }
        .tag-dropdown-item:last-child { border-bottom: none; }
        .pm-act-btn { border-radius: var(--ls-radius-sm, 6px); padding: 0.3rem var(--ls-btn-pad-x-sm, 0.65rem); margin-right: 3px; font-size: var(--ls-text-sm, 0.75rem); }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--edit { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .pm-act-btn--edit:hover { background: #dbeafe; border-color: #93c5fd; }
    </style>

    @script
    <script>
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.tag-select-container')) {
                $wire.closeConfigDropdown();
            }
        });
    </script>
    @endscript
</div>
