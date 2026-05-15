@php
    $p = $preview ?? [];
@endphp

<div class="modal fade show d-block eq-delete-overlay" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered eq-delete-dialog">
        <div class="modal-content eq-delete-shell border-0">
            <div class="modal-body eq-delete-body">
                <button
                    type="button"
                    class="btn-close eq-delete-close"
                    wire:click="{{ $closeMethod }}"
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
                            <p class="eq-delete-intro__lead">Review this notification before you remove it permanently.</p>
                        </div>
                    </div>

                    <div class="eq-delete-details">
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.notification_type') }}</span>
                            <span class="eq-delete-field__value">
                                <span class="eq-delete-pill">{{ $p['notification_type'] ?? '—' }}</span>
                            </span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.frequency') }}</span>
                            <span class="eq-delete-field__value eq-delete-field__value--primary">{{ $p['frequency_display'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.notification_date') }}</span>
                            <span class="eq-delete-field__value">{{ $p['next_date'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.status') }}</span>
                            <span class="eq-delete-field__value">{{ $p['status'] ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="eq-delete-warning" role="status">
                        <i class="mdi mdi-information-outline eq-delete-warning__icon" aria-hidden="true"></i>
                        <p class="eq-delete-warning__text mb-0">{{ $warningText }}</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer eq-delete-footer border-0">
                <button type="button" class="btn eq-delete-btn eq-delete-btn--cancel" wire:click="{{ $closeMethod }}">
                    {{ __('equipment.cancel') }}
                </button>
                <button
                    type="button"
                    class="btn eq-delete-btn eq-delete-btn--confirm"
                    wire:click="{{ $confirmMethod }}"
                    wire:loading.attr="disabled"
                    wire:target="{{ $confirmTarget }}"
                >
                    <span wire:loading.remove wire:target="{{ $confirmTarget }}">
                        <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                        {{ __('equipment.delete') }}
                    </span>
                    <span wire:loading wire:target="{{ $confirmTarget }}">
                        <i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i>
                        {{ __('equipment.delete') }}…
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
