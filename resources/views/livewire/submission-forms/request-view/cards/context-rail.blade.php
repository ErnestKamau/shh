{{-- Lab console context rail — identity, job/sample refs when present, documents (Sample collection is a canvas tab) --}}
@php
    $identity = $contextRail['identity'] ?? [];
    $contact = $contextRail['contact'] ?? [];
    $when = $contextRail['when'] ?? [];
    $refs = $contextRail['refs'] ?? [];
    $documents = $contextRail['documents'] ?? [];
    $moreFields = $contextRail['more_fields'] ?? [];
    $canEditTrf = $canEditSampleRows ?? false;

    $clientField = collect($identity)->first(fn (array $field): bool => ($field['name'] ?? '') === 'client_name');
    $clientTitle = $clientField['value'] ?? 'Client';
    $companyUnitField = collect($identity)->first(fn (array $field): bool => ($field['name'] ?? '') === 'company_unit');
    $clientHeadSubtitle = $companyUnitField !== null
        ? (string) $companyUnitField['value']
        : 'Contact & company unit';
    $identityBody = collect($identity)
        ->reject(fn (array $field): bool => in_array($field['name'] ?? '', ['client_name', 'company_unit'], true))
        ->values()
        ->all();
@endphp

<aside class="rv-console-rail" aria-label="Client info">
    <div class="rv-rail-panel">
        @if(count($identity) > 0 || count($when) > 0 || count($contact) > 0)
            <section class="rv-rail-section rv-rail-section--client">
                <div class="rv-rail-head">
                    <div class="rv-rail-head__copy">
                        <div class="rv-rail-eyebrow">Client info</div>
                        <h3 class="rv-rail-head-title">{{ $clientTitle }}</h3>
                        <p class="rv-rail-head-subtitle">{{ $clientHeadSubtitle }}</p>
                    </div>
                    <div class="rv-rail-head__actions">
                        @if($canEditTrf)
                            <div class="d-inline-flex align-items-center rv-rail-icon-actions">
                                <button type="button"
                                    class="rv-rail-edit-btn rv-rail-edit-btn--icon"
                                    wire:click="openTrfViewer('customer')"
                                    title="View customer & contact"
                                    aria-label="View customer & contact">
                                    <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                                </button>
                                <button type="button"
                                    class="rv-rail-edit-btn rv-rail-edit-btn--icon"
                                    wire:click="openTrfEditor('customer')"
                                    title="Edit customer & contact"
                                    aria-label="Edit customer & contact">
                                    <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                                </button>
                            </div>
                        @else
                            <button type="button"
                                class="rv-rail-edit-btn rv-rail-edit-btn--icon"
                                wire:click="openTrfViewer('customer')"
                                title="View customer & contact"
                                aria-label="View customer & contact">
                                <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="rv-rail-body">
                    <dl class="rv-rail-fields">
                        @foreach($identityBody as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] }}</dt>
                                <dd class="{{ ($field['name'] ?? '') === 'company_unit' ? 'rv-rail-value--emphasis' : '' }}">{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                        @foreach($contact as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] }}</dt>
                                <dd>
                                    {{ $field['value'] }}
                                    @if(($field['name'] ?? '') === 'contact_name' && ! empty($field['email']))
                                        <span class="rv-rail-contact-email" title="{{ $field['email'] }}">
                                            <i class="mdi mdi-email-outline" aria-hidden="true"></i>
                                            <span>{{ $field['email'] }}</span>
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>
        @endif

        @if(count($refs) > 0)
            <section class="rv-rail-section">
                <h3 class="rv-rail-section-title">
                    <i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
                    References
                </h3>
                <dl class="rv-rail-fields">
                    @foreach($refs as $field)
                        <div class="rv-rail-field">
                            <dt>{{ $field['label'] }}</dt>
                            <dd>{{ $field['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        @if(count($documents) > 0)
            <section class="rv-rail-section">
                <h3 class="rv-rail-section-title">
                    <i class="mdi mdi-file-document-multiple-outline" aria-hidden="true"></i>
                    Documents
                </h3>
                <ul class="rv-rail-docs">
                    @foreach($documents as $doc)
                        <li>
                            @if(!empty($doc['available']))
                                <a href="{{ $doc['href'] }}"
                                   class="rv-rail-doc-link"
                                   target="_blank"
                                   rel="noopener">
                                    <i class="mdi {{ $doc['icon'] ?? 'mdi-file-outline' }}" aria-hidden="true"></i>
                                    <span>{{ $doc['label'] }}</span>
                                    <i class="mdi mdi-open-in-new rv-rail-doc-external" aria-hidden="true"></i>
                                </a>
                            @else
                                <span class="rv-rail-doc-link is-unavailable" title="Not generated yet">
                                    <i class="mdi {{ $doc['icon'] ?? 'mdi-file-outline' }}" aria-hidden="true"></i>
                                    <span>{{ $doc['label'] }}</span>
                                    <span class="rv-rail-doc-muted">Unavailable</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if(count($moreFields) > 0)
            <section class="rv-rail-section rv-rail-section--more"
                     x-data="{ open: false }">
                <button type="button"
                        class="rv-rail-more-toggle"
                        @click="open = !open"
                        :aria-expanded="open">
                    <span>
                        <i class="mdi mdi-dots-horizontal-circle-outline" aria-hidden="true"></i>
                        More details
                    </span>
                    <i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                </button>
                <dl class="rv-rail-fields" x-show="open" x-collapse x-cloak>
                    @foreach($moreFields as $field)
                        <div class="rv-rail-field">
                            <dt>{{ $field['label'] }}</dt>
                            <dd>{{ $field['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    </div>
</aside>
