@php
    $dossier = $dossier ?? [];
    $customer = is_array($dossier['customer'] ?? null) ? $dossier['customer'] : [];
    $collection = is_array($dossier['collection'] ?? null) ? $dossier['collection'] : [];
    $clientTitle = (string) ($dossier['client_title'] ?? 'Client');
    $conditionName = trim((string) ($dossier['condition_name'] ?? ''));
    $conditionNotAcceptable = ! empty($dossier['condition_not_acceptable']);
@endphp

<div class="integrity-dossier-rail request-view-page" aria-label="Sample dossier">
    <div class="rv-rail-panel integrity-dossier-rail__panel">
        @if($conditionName !== '')
            <section class="rv-rail-section">
                <h3 class="rv-rail-section-title">
                    <i class="mdi mdi-thermometer-lines" aria-hidden="true"></i>
                    Condition of sample
                </h3>
                <div class="rv-rail-body px-3 pb-3">
                    @if($conditionNotAcceptable)
                        <div class="integrity-sample-condition-flag" title="Sample condition recorded at receive">
                            <i class="mdi mdi-flag" aria-hidden="true"></i>
                            Not acceptable — {{ $conditionName }}
                        </div>
                    @else
                        <p class="mb-0 rv-rail-value--emphasis">{{ $conditionName }}</p>
                    @endif
                </div>
            </section>
        @endif

        @if($collection !== [])
            <section class="rv-rail-section">
                <h3 class="rv-rail-section-title">
                    <i class="mdi mdi-map-marker-radius-outline" aria-hidden="true"></i>
                    Sample collection
                </h3>
                <div class="rv-rail-body">
                    <dl class="rv-rail-fields">
                        @foreach($collection as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] ?? '' }}</dt>
                                <dd>{{ $field['value'] ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>
        @endif

        @if($customer !== [])
            <section class="rv-rail-section rv-rail-section--client">
                <div class="rv-rail-head">
                    <div class="rv-rail-head__copy">
                        <div class="rv-rail-eyebrow">Customer info</div>
                        <h3 class="rv-rail-head-title">{{ $clientTitle }}</h3>
                        <p class="rv-rail-head-subtitle">From TRF</p>
                    </div>
                </div>
                <div class="rv-rail-body">
                    <dl class="rv-rail-fields">
                        @foreach($customer as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] ?? '' }}</dt>
                                <dd class="{{ strcasecmp((string) ($field['label'] ?? ''), 'Client name') === 0 ? 'rv-rail-value--emphasis' : '' }}">
                                    {{ $field['value'] ?? '—' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>
        @endif
    </div>
</div>
