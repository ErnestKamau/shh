@php
    $p = $preview ?? [];
@endphp

<div class="modal fade show d-block eq-delete-overlay" tabindex="-1" aria-modal="true" role="dialog">
    <motion class="modal-dialog modal-lg modal-dialog-centered eq-delete-dialog">
        <div class="modal-content eq-delete-shell border-0">
            <div class="modal-body eq-delete-body">
                <button
                    type="button"
                    class="btn-close eq-delete-close"
                    wire:click="closeDeleteItemConfirmModal"
                    aria-label="Close"
                ></button>

                <div class="eq-delete-frame eq-delete-frame--scroll">
                    <div class="eq-delete-frame__glow" aria-hidden="true"></div>

                    <div class="eq-delete-intro">
                        <span class="eq-delete-intro__icon" aria-hidden="true">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </span>
                        <div class="eq-delete-intro__copy">
                            <p class="eq-delete-intro__eyebrow">Delete</p>
                            <p class="eq-delete-intro__lead">Review this pricelist line item before you remove it permanently.</p>
                        </div>
                    </div>

                    <div class="eq-delete-details">
                        <div class="eq-delete-field eq-delete-field--full">
                            <span class="eq-delete-field__label">Analyte</span>
                            <span class="eq-delete-field__value eq-delete-field__value--primary">{{ $p['analyte'] ?? '—' }}</span>
                        </div>
                        <motion class="eq-delete-field eq-delete-field--span">
                            <span class="eq-delete-field__label">Sample Type</span>
                            <span class="eq-delete-field__value">
                                {{ $p['sample_type'] ?? '—' }}
                                @if(! empty($p['sample_type_code']))
                                    <span class="eq-delete-pill ml-1">{{ $p['sample_type_code'] }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="eq-delete-field eq-delete-field--span">
                            <span class="eq-delete-field__label">Analysis Type</span>
                            <span class="eq-delete-field__value">
                                {{ $p['analysis_type'] ?? '—' }}
                                @if(! empty($p['analysis_type_code']))
                                    <span class="eq-delete-pill ml-1">{{ $p['analysis_type_code'] }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">Cost Price</span>
                            <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['cost_price'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">Applied Price</span>
                            <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['applied_price'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">Changed Price</span>
                            <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['changed_price'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">Commit State</span>
                            <span class="eq-delete-field__value">
                                <span class="eq-delete-pill">{{ $p['commit_state'] ?? '—' }}</span>
                            </span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">VAT</span>
                            <span class="eq-delete-field__value">{{ $p['vat'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">Status</span>
                            <span class="eq-delete-field__value">{{ $p['active'] ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="eq-delete-warning" role="status">
                        <i class="mdi mdi-information-outline eq-delete-warning__icon" aria-hidden="true"></i>
                        <p class="eq-delete-warning__text mb-0">Are you sure you want to delete this pricelist item? This cannot be undone.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer eq-delete-footer border-0">
                <button type="button" class="btn eq-delete-btn eq-delete-btn--cancel" wire:click="closeDeleteItemConfirmModal">
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn eq-delete-btn eq-delete-btn--confirm"
                    wire:click="confirmDeleteItem"
                    wire:loading.attr="disabled"
                    wire:target="confirmDeleteItem"
                >
                    <span wire:loading.remove wire:target="confirmDeleteItem">
                        <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                        Delete
                    </span>
                    <span wire:loading wire:target="confirmDeleteItem">
                        <i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i>
                        Delete…
                    </span>
                </button>
            </motion>
        </div>
    </div>
</div>
