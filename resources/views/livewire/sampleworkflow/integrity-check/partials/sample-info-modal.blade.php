@php
    $dossier = $dossier ?? [];
    $displayLabel = (string) ($dossier['display_label'] ?? 'Sample');
    $sampleInfo = is_array($dossier['sample_info'] ?? null) ? $dossier['sample_info'] : [];
    $collection = is_array($dossier['collection'] ?? null) ? $dossier['collection'] : [];
    $customerCard = is_array($dossier['customer_card'] ?? null) ? $dossier['customer_card'] : [];
    $conditionName = trim((string) ($dossier['condition_name'] ?? ''));
    $conditionNotAcceptable = ! empty($dossier['condition_not_acceptable']);
    $requestNumber = trim((string) ($requestNumber ?? ''));
    $formNumber = trim((string) ($formNumber ?? ''));

    $clientName = trim((string) ($customerCard['client_name'] ?? ''));
    $contactPerson = trim((string) ($customerCard['contact_person'] ?? ''));
    $contactEmail = trim((string) ($customerCard['email'] ?? ''));
    $contactMobile = trim((string) ($customerCard['mobile'] ?? ''));
    $crmAddress = trim((string) ($customerCard['address'] ?? ''));
    $hasCustomerCard = $clientName !== '' || $contactPerson !== '' || $contactEmail !== '' || $contactMobile !== '' || $crmAddress !== '';
@endphp

@if($showSampleInfoModal)
    <div
        class="modal fade show d-block ls-quote-commercial-modal integrity-sample-info-modal ls-ui-kit"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="integrity-sample-info-title"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="integrity-sample-info-title">
                        {{ $displayLabel }}
                    </h5>
                    <button type="button" class="close" wire:click="closeSampleInfoModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="ls-quote-commercial-chooser__lede mb-2">
                        Sample details from the TRF for this integrity check.
                    </p>

                    <div class="ls-quote-commercial-summary integrity-sample-info-summary mb-3" aria-live="polite">
                        <div class="ls-quote-commercial-summary__item">
                            <span class="ls-quote-commercial-summary__label">Condition</span>
                            <strong class="ls-quote-commercial-summary__value {{ $conditionNotAcceptable ? 'is-warn' : '' }}">
                                {{ $conditionName !== '' ? $conditionName : '—' }}
                            </strong>
                        </div>
                        <div class="ls-quote-commercial-summary__item">
                            <span class="ls-quote-commercial-summary__label">Request</span>
                            <strong class="ls-quote-commercial-summary__value">
                                {{ $requestNumber !== '' ? $requestNumber : '—' }}
                            </strong>
                        </div>
                        <div class="ls-quote-commercial-summary__item">
                            <span class="ls-quote-commercial-summary__label">Form</span>
                            <strong class="ls-quote-commercial-summary__value">
                                {{ $formNumber !== '' ? $formNumber : '—' }}
                            </strong>
                        </div>
                    </div>

                    <div class="integrity-sample-info-stack">
                        @if($conditionName !== '' || $collection !== [])
                            <div class="ls-form-panel integrity-sample-info-panel">
                                <h4 class="ls-form-panel__title">Condition &amp; collection</h4>
                                <div class="ls-form-grid ls-form-grid--2 ls-compact">
                                    @if($conditionName !== '')
                                        <div class="ls-field">
                                            <span class="ls-field__label">Condition of sample</span>
                                            <p class="integrity-sample-info-value mb-0 {{ $conditionNotAcceptable ? 'is-warn' : '' }}">
                                                {{ $conditionName }}
                                            </p>
                                        </div>
                                    @endif
                                    @foreach($collection as $field)
                                        <div class="ls-field">
                                            <span class="ls-field__label">{{ $field['label'] ?? '' }}</span>
                                            <p class="integrity-sample-info-value mb-0">{{ filled($field['value'] ?? null) ? $field['value'] : '—' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="ls-form-panel integrity-sample-info-panel">
                            <h4 class="ls-form-panel__title">Sample information</h4>
                            @if($sampleInfo === [])
                                <p class="ls-field__hint mb-0">No sample fields were captured on the TRF.</p>
                            @else
                                <div class="ls-form-grid ls-form-grid--2 ls-compact">
                                    @foreach($sampleInfo as $field)
                                        <div class="ls-field">
                                            <span class="ls-field__label">{{ $field['label'] ?? '' }}</span>
                                            <p class="integrity-sample-info-value mb-0">{{ filled($field['value'] ?? null) ? $field['value'] : '—' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if($hasCustomerCard)
                            <div class="ls-form-panel integrity-sample-info-panel">
                                <h4 class="ls-form-panel__title">Customer info</h4>
                                <div class="ls-form-grid ls-form-grid--2 ls-compact">
                                    <div class="ls-field">
                                        <span class="ls-field__label">Client</span>
                                        <p class="integrity-sample-info-value mb-0">{{ $clientName !== '' ? $clientName : '—' }}</p>
                                    </div>
                                    <div class="ls-field">
                                        <span class="ls-field__label">Contact person</span>
                                        <p class="integrity-sample-info-value mb-0">{{ $contactPerson !== '' ? $contactPerson : '—' }}</p>
                                    </div>
                                    <div class="ls-field">
                                        <span class="ls-field__label">Email</span>
                                        <p class="integrity-sample-info-value mb-0">{{ $contactEmail !== '' ? $contactEmail : '—' }}</p>
                                    </div>
                                    <div class="ls-field">
                                        <span class="ls-field__label">Mobile number</span>
                                        <p class="integrity-sample-info-value mb-0">{{ $contactMobile !== '' ? $contactMobile : '—' }}</p>
                                    </div>
                                    <div class="ls-field ls-span-2">
                                        <span class="ls-field__label">Address</span>
                                        <p class="integrity-sample-info-value mb-0">{{ $crmAddress !== '' ? $crmAddress : '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-sm ls-quote-commercial-done"
                        wire:click="closeSampleInfoModal">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
