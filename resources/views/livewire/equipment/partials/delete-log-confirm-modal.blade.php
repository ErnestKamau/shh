@php
    $p = $preview ?? [];
    $frameClasses = 'eq-delete-frame' . (($scrollable ?? false) ? ' eq-delete-frame--scroll' : '');
    $notesContent = ($useRemarks ?? false) ? ($p['remarks'] ?? '') : ($p['notes'] ?? '');
@endphp

<div class="modal fade show d-block eq-delete-overlay" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog {{ ($wide ?? false) ? 'modal-lg' : 'modal-md' }} modal-dialog-centered eq-delete-dialog">
        <div class="modal-content eq-delete-shell border-0">
            <div class="modal-body eq-delete-body">
                <button
                    type="button"
                    class="btn-close eq-delete-close"
                    wire:click="{{ $closeMethod }}"
                    aria-label="{{ __('equipment.close') }}"
                ></button>

                <div class="{{ $frameClasses }}">
                    <div class="eq-delete-frame__glow" aria-hidden="true"></div>

                    <div class="eq-delete-intro">
                        <span class="eq-delete-intro__icon" aria-hidden="true">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </span>
                        <div class="eq-delete-intro__copy">
                            <p class="eq-delete-intro__eyebrow">{{ __('equipment.delete') }}</p>
                            <p class="eq-delete-intro__lead">Review this record before you remove it permanently.</p>
                        </div>
                    </div>

                    <div class="eq-delete-details">
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ $dateLabel }}</span>
                            <span class="eq-delete-field__value eq-delete-field__value--primary">{{ $p['date'] ?? '—' }}</span>
                        </div>
                        <div class="eq-delete-field">
                            <span class="eq-delete-field__label">{{ __('equipment.service_type') }}</span>
                            <span class="eq-delete-field__value">
                                <span class="eq-delete-pill">{{ $p['service_type'] ?? '—' }}</span>
                            </span>
                        </div>
                        @if(! empty($p['is_external']))
                            <div class="eq-delete-field eq-delete-field--span">
                                <span class="eq-delete-field__label">{{ __('equipment.supplier') }}</span>
                                <span class="eq-delete-field__value">{{ $p['supplier_name'] ?? __('equipment.not_available') }}</span>
                            </div>
                        @else
                            <div class="eq-delete-field eq-delete-field--span">
                                <span class="eq-delete-field__label">{{ ($useOperatorLabel ?? false) ? 'Operator' : __('equipment.employee') }}</span>
                                <span class="eq-delete-field__value">{{ ($useOperatorLabel ?? false) ? ($p['operator_name'] ?? __('equipment.not_available')) : ($p['employee_name'] ?? __('equipment.not_available')) }}</span>
                            </div>
                        @endif
                        @if(($showReferenceStandard ?? false) && filled($p['reference_standard'] ?? null))
                            <div class="eq-delete-field eq-delete-field--span">
                                <span class="eq-delete-field__label">Reference Standard</span>
                                <span class="eq-delete-field__value">{{ $p['reference_standard'] }}</span>
                            </div>
                        @endif
                        @if($showCalibrationMetrics ?? false)
                            @if(filled($p['correction_factor'] ?? null))
                                <div class="eq-delete-field">
                                    <span class="eq-delete-field__label">{{ __('equipment.correction_factor') }}</span>
                                    <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['correction_factor'] }}</span>
                                </div>
                            @endif
                            @if(filled($p['uncertainty_of_measure'] ?? null))
                                <div class="eq-delete-field">
                                    <span class="eq-delete-field__label">{{ __('equipment.uncertainty_of_measure') }}</span>
                                    <span class="eq-delete-field__value eq-delete-field__value--mono">{{ $p['uncertainty_of_measure'] }}</span>
                                </div>
                            @endif
                        @endif
                        <div class="eq-delete-field eq-delete-field--full">
                            <span class="eq-delete-field__label">{{ $notesLabel ?? __('equipment.notes') }}</span>
                            <div class="eq-delete-notes">{{ $notesContent !== '' ? $notesContent : '—' }}</div>
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
