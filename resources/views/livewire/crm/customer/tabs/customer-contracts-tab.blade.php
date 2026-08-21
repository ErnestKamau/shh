<div>
    <div x-data="{
            init() {
                Livewire.on('show-customer-contract-modal', () => {
                   $('#customerContractModal').modal('show');
                });
                Livewire.on('close-customer-contract-modal', () => {
                   $('#customerContractModal').modal('hide');
                });
                Livewire.on('show-customer-contract-view-modal', () => {
                   $('#customerContractViewModal').modal('show');
                });
                Livewire.on('close-customer-contract-view-modal', () => {
                   $('#customerContractViewModal').modal('hide');
                });
            }
        }">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                    style="width:32px;height:32px;background:#eef2ff;flex-shrink:0;">
                    <i class="mdi mdi-file-sign" style="font-size:1.1rem;color:#4f46e5;"></i>
                </span>
                <div>
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.contracts') }}</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.contracts_tab_subtitle') }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button type="button" class="btn btn-outline-primary btn-sm crm-outline-btn-sm" wire:click="openNewContractModal">
                    <i class="mdi mdi-plus"></i> {{ __('crm.new_contract') }}
                </button>
            </div>
        </div>

        @if($currentContract)
            <div class="crm-card border mb-3 p-3" style="border-radius:10px;background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:12px;">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="crm-badge crm-badge-success mr-2">{{ __('crm.current_contract') }}</span>
                            <small class="text-muted">
                                {{ __('crm.posted_by') }}: {{ $currentContract->posted_by ?: '—' }}
                            </small>
                        </div>
                        <div class="text-dark" style="font-size:0.9rem;">
                            <strong>{{ __('crm.contract_validity') }}:</strong>
                            {{ $currentContract->valid_from ? $currentContract->valid_from->format('d-M-Y') : '—' }}
                            →
                            {{ $currentContract->valid_to ? $currentContract->valid_to->format('d-M-Y') : '—' }}
                        </div>
                        <div class="d-flex flex-wrap align-items-center mt-2" style="gap:6px;">
                            @if($currentContract->contractScopeLabel())
                                <span class="crm-badge crm-badge-info"
                                      @if($scopeTip = \App\Models\CRM\CrmCustomerContract::contractScopeTooltip((string) $currentContract->contract_scope))
                                          title="{{ $scopeTip }}"
                                          data-toggle="tooltip"
                                          data-placement="top"
                                      @endif
                                >{{ $currentContract->contractScopeLabel() }}</span>
                            @endif
                            @if($currentContract->is_scheduled_sampling)
                                <span class="crm-badge crm-badge-info"
                                      title="{{ __('crm.scheduled_sampling_help') }}"
                                      data-toggle="tooltip"
                                      data-placement="top">{{ __('crm.scheduled_sampling') }}</span>
                            @elseif($currentContract->collectionMethodLabel())
                                <span class="crm-badge crm-badge-neutral"
                                      title="{{ __('crm.collection_method_help') }}"
                                      data-toggle="tooltip"
                                      data-placement="top">{{ $currentContract->collectionMethodLabel() }}</span>
                            @endif
                        </div>
                        @if($currentContract->original_name)
                            <div class="text-muted mt-1" style="font-size:0.8rem;">
                                <i class="mdi mdi-file-document-outline mr-1"></i>{{ $currentContract->original_name }}
                            </div>
                        @endif
                    </div>
                    @if($currentContract->hasFile())
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                wire:click="openViewContractModal('{{ $currentContract->id }}')">
                                <i class="mdi mdi-eye-outline"></i> {{ __('crm.view_file') }}
                            </button>
                            <a href="{{ route('crm.customer.contract.download', [$customer->id, $currentContract->id]) }}"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="mdi mdi-download"></i> {{ __('crm.download_file') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div wire:loading
            wire:target="openNewContractModal,saveContract,openViewContractModal"
            class="crm-loading-indicator">
            <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
        </div>

        <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50" plain-rows>
            <x-slot:header>
                <tr>
                    <th>{{ __('crm.status') }}</th>
                    <th>{{ __('crm.valid_from') }}</th>
                    <th>{{ __('crm.valid_to') }}</th>
                    <th>{{ __('crm.contract_scope') }}</th>
                    <th>{{ __('crm.sampling_collection') }}</th>
                    <th>{{ __('crm.file') }}</th>
                    <th>{{ __('crm.posted_by') }}</th>
                    <th>{{ __('crm.created_at') }}</th>
                    <th style="min-width:110px;">{{ __('crm.actions') }}</th>
                </tr>
            </x-slot:header>
            @forelse($contracts as $contract)
                <tr wire:key="customer-contract-{{ $contract->id }}" @class(['table-success' => $contract->is_current])>
                    <td>
                        @if($contract->is_current)
                            <span class="crm-badge crm-badge-success">{{ __('crm.current') }}</span>
                        @else
                            <span class="crm-badge crm-badge-neutral">{{ __('crm.superseded') }}</span>
                        @endif
                    </td>
                    <td>{{ $contract->valid_from ? $contract->valid_from->format('d-M-Y') : '—' }}</td>
                    <td>{{ $contract->valid_to ? $contract->valid_to->format('d-M-Y') : '—' }}</td>
                    <td>{{ $contract->contractScopeLabel() ?: '—' }}</td>
                    <td>
                        @if($contract->is_scheduled_sampling)
                            {{ __('crm.scheduled_sampling') }}
                        @else
                            {{ $contract->collectionMethodLabel() ?: '—' }}
                        @endif
                    </td>
                    <td>{{ $contract->original_name ?: '—' }}</td>
                    <td>{{ $contract->posted_by ?: '—' }}</td>
                    <td>{{ $contract->created_at ? $contract->created_at->format('d-M-Y H:i') : '—' }}</td>
                    <td nowrap>
                        @if($contract->hasFile())
                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--view"
                                wire:click="openViewContractModal('{{ $contract->id }}')"
                                title="{{ __('crm.view_file') }}">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                            <a href="{{ route('crm.customer.contract.download', [$customer->id, $contract->id]) }}"
                               class="btn btn-sm rm-act-btn rm-act-btn--view" target="_blank" title="{{ __('crm.download_file') }}">
                                <i class="mdi mdi-download"></i>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-crm.empty-state
                            icon="mdi-file-sign"
                            :message="__('crm.no_contracts_for_client')"
                            :help="__('crm.contracts_empty_help')"
                        />
                    </td>
                </tr>
            @endforelse
        </x-crm.data-table>

        <x-crm.pagination :summary="__('crm.showing_to_of_results', ['from' => ($contracts->firstItem() ?? 0), 'to' => ($contracts->lastItem() ?? 0), 'total' => $contracts->total()])">
            {{ $contracts->links() }}
        </x-crm.pagination>
    </div>

    @teleport('body')
    <div wire:ignore.self class="modal fade" id="customerContractModal" tabindex="-1" role="dialog"
        aria-labelledby="customerContractModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveContract">
                    <div class="modal-header">
                        <h5 class="modal-title" id="customerContractModalLabel">
                            <i class="mdi mdi-file-sign mr-1 text-primary"></i>
                            {{ __('crm.new_contract') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.85rem;">
                            <i class="mdi mdi-alert-outline mr-1"></i>
                            {{ __('crm.new_contract_invalidates_previous') }}
                        </div>
                        <div class="form-group">
                            <label>{{ __('crm.valid_from') }}:</label>
                            <input type="date" wire:model="valid_from" class="form-control" />
                            @error('valid_from') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('crm.valid_to') }}:</label>
                            <input type="date" wire:model="valid_to" class="form-control" />
                            @error('valid_to') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="d-flex align-items-center flex-wrap" style="gap:6px;">
                                <span>{{ __('crm.contract_scope') }} <span class="text-muted">({{ __('crm.optional') }})</span></span>
                                <i class="mdi mdi-information-outline text-muted"
                                   style="cursor:help;font-size:1rem;"
                                   title="{{ __('crm.contract_scope_tooltip_overview') }}"
                                   data-toggle="tooltip"
                                   data-placement="top"
                                   aria-label="{{ __('crm.contract_scope_tooltip_overview') }}"></i>
                            </label>
                            <select wire:model.live="contract_scope" class="form-control">
                                <option value="">{{ __('crm.select_optional') }}</option>
                                @foreach($contractScopeOptions as $value => $label)
                                    @php $scopeTip = \App\Models\CRM\CrmCustomerContract::contractScopeTooltip($value); @endphp
                                    <option value="{{ $value }}" @if($scopeTip) title="{{ $scopeTip }}" @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                            <ul class="list-unstyled mb-0 mt-2 small text-muted">
                                @foreach(\App\Models\CRM\CrmCustomerContract::contractScopeTooltips() as $value => $tip)
                                    <li class="d-flex align-items-start mb-1" style="gap:6px;">
                                        <i class="mdi mdi-help-circle-outline mt-0"
                                           style="cursor:help;flex-shrink:0;"
                                           title="{{ $tip }}"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           aria-label="{{ $tip }}"></i>
                                        <span>
                                            <strong class="text-dark">{{ $contractScopeOptions[$value] ?? $value }}:</strong>
                                            {{ $tip }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            @if(filled($contract_scope) && ($selectedScopeTip = \App\Models\CRM\CrmCustomerContract::contractScopeTooltip($contract_scope)))
                                <div class="alert alert-light border py-2 px-3 mt-2 mb-0 small">
                                    <i class="mdi mdi-information-outline text-primary mr-1"></i>
                                    <strong>{{ $contractScopeOptions[$contract_scope] ?? $contract_scope }}:</strong>
                                    {{ $selectedScopeTip }}
                                </div>
                            @endif
                            @error('contract_scope') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="contract_is_scheduled_sampling"
                                    wire:model.live="is_scheduled_sampling">
                                <label class="custom-control-label" for="contract_is_scheduled_sampling">
                                    {{ __('crm.scheduled_sampling_contracts') }}
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">{{ __('crm.scheduled_sampling_help') }}</small>
                        </div>
                        @if(! $is_scheduled_sampling)
                            <div class="form-group">
                                <label>{{ __('crm.collection_method') }}:</label>
                                <select wire:model="default_collection_method" class="form-control">
                                    <option value="">{{ __('crm.select_optional') }}</option>
                                    @foreach($collectionMethodOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">{{ __('crm.collection_method_help') }}</small>
                                @error('default_collection_method') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="form-group mb-0">
                            <label>{{ __('crm.contract_document') }}:</label>
                            <input type="file" wire:model="contractFile" class="form-control" />
                            <div wire:loading wire:target="contractFile" class="text-info small mt-1">{{ __('crm.uploading') }}...</div>
                            @error('contractFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveContract">
                                <i class="mdi mdi-content-save-outline mr-1"></i>{{ __('crm.save') }}
                            </span>
                            <span wire:loading wire:target="saveContract">
                                <i class="mdi mdi-loading mdi-spin mr-1"></i>{{ __('crm.saving') }}...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="customerContractViewModal" tabindex="-1" role="dialog"
        aria-labelledby="customerContractViewModalLabel" aria-hidden="true"
        x-on:hidden.bs.modal="$wire.closeViewContractModal()">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerContractViewModalLabel">
                        <i class="mdi mdi-file-eye-outline mr-1 text-primary"></i>
                        {{ __('crm.view_contract') }}
                        @if($viewingContract?->original_name)
                            <small class="text-muted ml-1">— {{ $viewingContract->original_name }}</small>
                        @endif
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" wire:click="closeViewContractModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="background:#f8fafc;min-height:420px;">
                    @if($viewingContract && $viewingContract->hasFile())
                        @if($viewingContract->isImagePreview())
                            <div class="d-flex align-items-center justify-content-center p-3" style="min-height:420px;">
                                <img src="{{ route('crm.customer.contract.view', [$customer->id, $viewingContract->id]) }}"
                                     alt="{{ $viewingContract->original_name }}"
                                     style="max-width:100%;max-height:70vh;object-fit:contain;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                            </div>
                        @elseif($viewingContract->isPreviewable())
                            <iframe
                                src="{{ route('crm.customer.contract.view', [$customer->id, $viewingContract->id]) }}"
                                title="{{ $viewingContract->original_name }}"
                                style="width:100%;height:70vh;border:0;background:#fff;"
                            ></iframe>
                        @else
                            <div class="text-center p-5">
                                <i class="mdi mdi-file-document-outline text-muted" style="font-size:3rem;"></i>
                                <p class="mt-3 mb-1 font-weight-bold">{{ $viewingContract->original_name }}</p>
                                <p class="text-muted mb-3">{{ __('crm.contract_preview_unavailable') }}</p>
                                <a href="{{ route('crm.customer.contract.download', [$customer->id, $viewingContract->id]) }}"
                                   class="btn btn-primary" target="_blank">
                                    <i class="mdi mdi-download"></i> {{ __('crm.download_file') }}
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted p-5">
                            {{ __('crm.contract_file_not_found') }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if($viewingContract && $viewingContract->hasFile())
                        <a href="{{ route('crm.customer.contract.download', [$customer->id, $viewingContract->id]) }}"
                           class="btn btn-outline-primary" target="_blank">
                            <i class="mdi mdi-download"></i> {{ __('crm.download_file') }}
                        </a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="closeViewContractModal">
                        {{ __('crm.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>
