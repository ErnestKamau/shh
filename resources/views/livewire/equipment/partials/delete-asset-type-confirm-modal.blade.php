@php
    $p = $preview ?? [];
    $canDelete = (bool) ($p['can_delete'] ?? true);
@endphp

<div class="modal fade show d-block eq-delete-overlay" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered eq-delete-dialog">
        <div class="modal-content eq-delete-shell border-0">
            <div class="modal-body eq-delete-body">
                <button
                    type="button"
                    class="btn-close eq-delete-close"
                    wire:click="closeDeleteAssetTypeConfirmModal"
                    aria-label="{{ __('equipment.close') }}"
                ></button>

                <div class="eq-delete-frame">
                    <div class="eq-delete-frame__glow" aria-hidden="true"></div>

                    <div class="eq-delete-intro">
                        <span class="eq-delete-intro__icon" aria-hidden="true">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </span>
                        <div class="eq-delete-intro__copy">
                            <p class="eq-delete-intro__eyebrow">{{ __('equipment.delete') }}</p>
                            <p class="eq-delete-intro__lead">{{ __('equipment.asset_type_delete_review_lead') }}</p>
                        </div>
                    </div>

                    <div class="eq-delete-details">
                        <div class="eq-delete-field eq-delete-field--full">
                            <span class="eq-delete-field__label">{{ __('equipment.asset_code') }}</span>
                            <span class="eq-delete-field__value eq-delete-field__value--primary">{{ $p['asset_code'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field eq-delete-field--full">
                            <span class="eq-delete-field__label">{{ __('equipment.description') }}</span>
                            <span class="eq-delete-field__value">{{ $p['description'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.active_equipments') }}</span>
                            <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['active_equipments_count'] ?? '0' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.status') }}</span>
                            <span class="eq-delete-field__value">
                                <span class="eq-delete-pill">{{ $p['status'] ?? '—' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="eq-delete-warning" role="status">
                        <i class="mdi mdi-information-outline eq-delete-warning__icon" aria-hidden="true"></i>
                        <p class="eq-delete-warning__text mb-0">
                            @if($canDelete)
                                {{ __('equipment.confirm_delete_asset_type') }}
                            @else
                                {{ __('equipment.asset_type_delete_has_equipment') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="modal-footer eq-delete-footer border-0">
                <button type="button" class="btn eq-delete-btn eq-delete-btn--cancel" wire:click="closeDeleteAssetTypeConfirmModal">
                    {{ __('equipment.cancel') }}
                </button>
                <button
                    type="button"
                    class="btn eq-delete-btn eq-delete-btn--confirm"
                    wire:click="confirmDeleteAssetType"
                    wire:loading.attr="disabled"
                    wire:target="confirmDeleteAssetType"
                    @disabled(! $canDelete)
                >
                    <span wire:loading.remove wire:target="confirmDeleteAssetType">
                        <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                        {{ __('equipment.delete') }}
                    </span>
                    <span wire:loading wire:target="confirmDeleteAssetType">
                        <i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i>
                        {{ __('equipment.delete') }}…
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
