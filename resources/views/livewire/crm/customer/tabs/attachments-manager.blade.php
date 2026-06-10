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
                <i class="mdi mdi-file-certificate-outline text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.certifications') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.certifications_documents') }}</small>
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
                                    <button class="btn crm-btn crm-btn-edit btn-sm" title="Edit"
                                        wire:click="editAttachment('{{ $item->id }}')">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button class="btn crm-btn crm-btn-delete btn-sm" title="{{ __('crm.delete') }}"
                                        wire:click="deleteAttachment('{{ $item->id }}')"
                                        wire:confirm="{{ __('crm.delete_attachment_confirm') }}">
                                        <i class="mdi mdi-delete-outline"></i>
                                    </button>
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

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="{{ __('crm.name') }}..." required>
                                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.certificate') }} @if(!$editingId)<span class="text-danger">*</span>@endif</label>
                                    <input type="file" class="form-control @error('certificate') is-invalid @enderror"
                                        wire:model="certificate">
                                    @if($editingId)
                                        <small class="text-muted d-block mt-1">{{ __('crm.leave_blank_retain_existing') }}</small>
                                    @endif
                                    @error('certificate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.certificate_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('certificationDate') is-invalid @enderror"
                                        wire:model="certificationDate">
                                    @error('certificationDate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.expire_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expireDate') is-invalid @enderror"
                                        wire:model="expireDate">
                                    @error('expireDate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{ __('crm.certification_body') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('certificationBody') is-invalid @enderror"
                                        wire:model="certificationBody" placeholder="{{ __('crm.certification_body') }}...">
                                    @error('certificationBody') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                @if($editingId)
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="attachment-status-active"
                                                wire:model="isActive">
                                            <label class="custom-control-label" for="attachment-status-active">{{ ucfirst(__('crm.active')) }}</label>
                                        </div>
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
