<div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('message'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information"></i> {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-folder-outline text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.company_sections') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.group_customer_units_by_section') }}</small>
            </div>
        </div>
        <button class="btn btn-add btn-sm" wire:click="openCreateModal">
            <i class="mdi mdi-plus"></i> {{ __('crm.add') }}
        </button>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th nowrap>{{ __('crm.name') }}</th>
                <th nowrap>{{ __('crm.status') }}</th>
                <th style="min-width: 100px;">{{ __('crm.actions') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($this->sections as $section)
                        <tr wire:key="section-{{ $section->id }}">
                            <td>{{ $this->sections->firstItem() + $loop->index }}</td>
                            <td>{{ $section->name }}</td>
                            <td>
                                @if($section->active == '1')
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click="editSection({{ $section->id }})" title="{{ __('crm.edit') }}">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button class="btn crm-btn crm-btn-delete btn-sm"
                                        wire:click="deleteSection({{ $section->id }})"
                                        wire:confirm="{{ __('crm.delete_company_section_confirm') }}"
                                        title="{{ __('crm.delete') }}">
                                        <i class="mdi mdi-delete-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-crm.empty-state
                                    icon="mdi-folder-outline"
                                    :message="__('crm.no_company_sections_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($this->sections->firstItem() ?? 0) . ' to ' . ($this->sections->lastItem() ?? 0) . ' of ' . $this->sections->total() . ' results'">
        {{ $this->sections->links() }}
    </x-crm.pagination>

    @if($showCreateModal)
        <template x-teleport="body">
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
                role="dialog" wire:key="create-section-modal" wire:click.self="close">
                <div class="modal-dialog" wire:click.self="close">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-{{ $editingSectionId ? 'pencil-outline' : 'plus' }}"></i>
                                {{ $editingSectionId ? __('crm.edit') : __('crm.add') }} {{ __('crm.company_section') }}
                            </h5>
                            <button type="button" wire:click="close" class="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="saveSection">
                            <div class="modal-body">
                                @if($modalError)
                                    <div class="alert alert-danger">{{ $modalError }}</div>
                                @endif

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="{{ __('crm.name') }}..." required>
                                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="sectionActiveCheck"
                                            wire:model="active">
                                        <label class="custom-control-label" for="sectionActiveCheck">{{ __('crm.is_active') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="saveSection"><i class="mdi mdi-content-save"></i> {{ __('crm.save_changes') }}</span>
                                    <span wire:loading wire:target="saveSection">{{ __('crm.saving') }}...</span>
                                </button>
                                <button type="button" wire:click="close" class="btn btn-secondary">{{ __('crm.close') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
