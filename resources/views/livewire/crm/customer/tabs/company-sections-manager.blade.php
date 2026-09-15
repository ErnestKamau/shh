<div>
    <x-livewire.flash-messages />

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
        <x-imara.primary-btn subject="{{ __('crm.company_section') }}" wire:click="openCreateModal" />
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
                                    <x-imara.row-action-btn variant="edit" wire:click="editSection('{{ $section->id }}')" class="mr-1" :title="__('crm.edit')" />
                                    <x-imara.row-action-btn variant="delete" wire:click="deleteSection('{{ $section->id }}')" wire:confirm="{{ __('crm.delete_company_section_confirm') }}" :title="__('crm.delete')" />
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

                                <x-imara.form-field label="{{ __('crm.name') }}" :required="true" :error="$errors->first('name')">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="{{ __('crm.name') }}..." required>
                                </x-imara.form-field>

                                <div class="form-group">
                                    <x-imara.custom-checkbox wire:model="active" label="{{ __('crm.is_active') }}" id="sectionActiveCheck" />
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
