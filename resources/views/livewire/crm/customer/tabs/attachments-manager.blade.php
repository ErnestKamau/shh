<div>
    <x-livewire.flash-messages />

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-file-certificate-outline text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.certifications') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.certifications_documents') }}</small>
            </div>
        </div>
        <x-imara.primary-btn subject="{{ __('crm.attachment') }}" wire:click="openCreateModal" />
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th nowrap>{{ __('crm.name') }}</th>
                <th>{{ __('crm.attachment') }}</th>
                <th>{{ __('crm.start_date') }}</th>
                <th>{{ __('crm.end_date') }}</th>
                <th nowrap>{{ __('crm.certification_body') }}</th>
                <th>{{ __('crm.status') }}</th>
                <th nowrap>{{ __('crm.edited_by') }}</th>
                <th style="min-width: 100px;">{{ __('crm.actions') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($this->certifications as $item)
                        <tr wire:key="cert-{{ $item->id }}">
                            <td>{{ ($this->certifications->currentPage() - 1) * $this->certifications->perPage() + $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td class="text-center">
                                <a href="{{ $item->certificate }}" class="btn btn-sm btn-transparent" target="_blank" rel="noopener">
                                    <i class="mdi mdi-download text-success"></i>
                                </a>
                            </td>
                            <td><small>{{ $item->certification_date }}</small></td>
                            <td><small>{{ $item->expire_date }}</small></td>
                            <td>{{ $item->certification_body }}</td>
                            <td>
                                @if($item->status == 0)
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ $item->edited ?: '—' }}</td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <x-imara.row-action-btn variant="edit" wire:click="editAttachment('{{ $item->id }}')" class="mr-1" :title="__('crm.edit')" />
                                    <x-imara.row-action-btn variant="delete" wire:click="deleteAttachment('{{ $item->id }}')" wire:confirm="{{ __('crm.delete_attachment_confirm') }}" :title="__('crm.delete')" />
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-crm.empty-state
                                    icon="mdi-file-certificate-outline"
                                    :message="__('crm.no_attachments_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($this->certifications->firstItem() ?? 0) . ' to ' . ($this->certifications->lastItem() ?? 0) . ' of ' . $this->certifications->total() . ' results'">
        {{ $this->certifications->links() }}
    </x-crm.pagination>

    @if($showCreateModal)
        <template x-teleport="body">
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
                role="dialog" wire:key="create-attachment-modal" wire:click.self="close">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-{{ $editingId ? 'pencil' : 'plus' }}"></i>
                                {{ $editingId ? __('crm.edit') : __('crm.add') }} {{ __('crm.customer_certification') }}
                            </h5>
                            <button type="button" wire:click="close" class="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="saveAttachment">
                            <div class="modal-body">
                                @if($modalError)
                                    <div class="alert alert-danger">{{ $modalError }}</div>
                                @endif

                                <x-imara.form-field label="{{ __('crm.name') }}" :required="true" :error="$errors->first('name')">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="{{ __('crm.name') }}..." required>
                                </x-imara.form-field>

                                <x-imara.form-field label="{{ __('crm.certificate') }}" :required="!$editingId" :error="$errors->first('certificate')">
                                    <input type="file" class="form-control @error('certificate') is-invalid @enderror"
                                        wire:model="certificate">
                                    @if($editingId)
                                        <small class="text-muted d-block mt-1">{{ __('crm.leave_blank_retain_existing') }}</small>
                                    @endif
                                </x-imara.form-field>

                                <x-imara.form-field label="{{ __('crm.certificate_date') }}" :required="true" :error="$errors->first('certificationDate')">
                                    <input type="date" class="form-control @error('certificationDate') is-invalid @enderror"
                                        wire:model="certificationDate">
                                </x-imara.form-field>

                                <x-imara.form-field label="{{ __('crm.expire_date') }}" :required="true" :error="$errors->first('expireDate')">
                                    <input type="date" class="form-control @error('expireDate') is-invalid @enderror"
                                        wire:model="expireDate">
                                </x-imara.form-field>

                                <x-imara.form-field label="{{ __('crm.certification_body') }}" :required="true" :error="$errors->first('certificationBody')">
                                    <input type="text" class="form-control @error('certificationBody') is-invalid @enderror"
                                        wire:model="certificationBody" placeholder="{{ __('crm.certification_body') }}...">
                                </x-imara.form-field>

                                @if($editingId)
                                    <div class="form-group">
                                        <x-imara.custom-checkbox wire:model="isActive" label="{{ ucfirst(__('crm.active')) }}" id="attachment-status-active" />
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="saveAttachment"><i class="mdi mdi-content-save"></i> {{ __('crm.save_changes') }}</span>
                                    <span wire:loading wire:target="saveAttachment">{{ __('crm.saving') }}...</span>
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
