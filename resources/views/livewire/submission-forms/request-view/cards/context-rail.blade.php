{{-- Lab console context rail — identity, refs, documents (Sample collection is a canvas tab) --}}
@php
    $identity = $contextRail['identity'] ?? [];
    $contact = $contextRail['contact'] ?? [];
    $when = $contextRail['when'] ?? [];
    $refs = $contextRail['refs'] ?? [];
    $documents = $contextRail['documents'] ?? [];
    $moreFields = $contextRail['more_fields'] ?? [];
    $canEditTrf = $canEditSampleRows ?? false;
@endphp

<aside class="rv-console-rail" aria-label="Request context">
    <div class="rv-rail-panel">
        @if(count($identity) > 0 || count($when) > 0 || count($contact) > 0)
            <section class="rv-rail-section">
                <div class="rv-rail-section-heading">
                    <h3 class="rv-rail-section-title mb-0">
                        <i class="mdi mdi-account-tie-outline" aria-hidden="true"></i>
                        Request
                    </h3>
                    @if($canEditTrf)
                        <button type="button"
                            class="rv-rail-edit-btn"
                            wire:click="openTrfEditor('customer')"
                            title="Edit customer & contact">
                            <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                            Edit
                        </button>
                    @endif
                </div>
                <div class="rv-rail-identity-grid">
                    <dl class="rv-rail-fields">
                        @foreach($identity as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] }}</dt>
                                <dd class="{{ ($field['name'] ?? '') === 'client_name' ? 'rv-rail-value--emphasis' : '' }}">{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                        @foreach($when as $field)
                            <div class="rv-rail-field">
                                <dt>{{ $field['label'] }}</dt>
                                <dd>{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if(count($contact) > 0)
                        <dl class="rv-rail-fields">
                            @foreach($contact as $field)
                                <div class="rv-rail-field">
                                    <dt>{{ $field['label'] }}</dt>
                                    <dd>{{ $field['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
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
