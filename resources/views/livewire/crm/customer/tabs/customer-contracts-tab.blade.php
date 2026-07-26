<div>
    <div x-data="{
            init() {
                Livewire.on('show-customer-contract-modal', () => {
                   $('#customerContractModal').modal('show');
                });
                Livewire.on('close-customer-contract-modal', () => {
                   $('#customerContractModal').modal('hide');
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
                        @if($currentContract->original_name)
                            <div class="text-muted mt-1" style="font-size:0.8rem;">
                                <i class="mdi mdi-file-document-outline mr-1"></i>{{ $currentContract->original_name }}
                            </div>
                        @endif
                    </div>
                    @if($currentContract->hasFile())
                        <a href="{{ route('crm.customer.contract.download', [$customer->id, $currentContract->id]) }}"
                           class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="mdi mdi-download"></i> {{ __('crm.download_file') }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <div wire:loading
            wire:target="openNewContractModal,saveContract"
            class="crm-loading-indicator">
            <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
        </div>

        <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50" plain-rows>
            <x-slot:header>
                <tr>
                    <th>{{ __('crm.status') }}</th>
                    <th>{{ __('crm.valid_from') }}</th>
                    <th>{{ __('crm.valid_to') }}</th>
                    <th>{{ __('crm.file') }}</th>
                    <th>{{ __('crm.posted_by') }}</th>
                    <th>{{ __('crm.created_at') }}</th>
                    <th style="min-width:80px;">{{ __('crm.actions') }}</th>
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
                    <td>{{ $contract->original_name ?: '—' }}</td>
                    <td>{{ $contract->posted_by ?: '—' }}</td>
                    <td>{{ $contract->created_at ? $contract->created_at->format('d-M-Y H:i') : '—' }}</td>
                    <td nowrap>
                        @if($contract->hasFile())
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
                    <td colspan="7">
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
    @endteleport
</div>
