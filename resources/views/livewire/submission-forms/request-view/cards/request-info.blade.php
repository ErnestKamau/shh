{{-- Request Info — collapsible 4-column read-only card (collapsed by default) --}}
<div class="workflow-board-panel rv-request-info-panel"
     id="request-info-panel"
     x-data="{ open: false }">
    <button type="button"
        class="workflow-board-panel-header rv-request-info-toggle"
        @click="open = !open"
        aria-expanded="false"
        :aria-expanded="open"
        aria-controls="request-info-body">
        <h5>
            <i class="mdi mdi-information-outline" aria-hidden="true"></i>
            Request Info
        </h5>
        <i class="mdi rv-request-info-chevron mdi-chevron-down"
           :class="{ 'mdi-chevron-up': open, 'mdi-chevron-down': !open }"
           aria-hidden="true"></i>
    </button>
    <div class="workflow-board-panel-body"
         id="request-info-body"
         x-show="open"
         x-collapse
         x-cloak>
        <div class="row w-100 mx-0 rv-request-info-grid">
            @forelse($requestInfoCard['fields'] as $field)
                @php
                    $isClientName = ($field['name'] ?? null) === 'client_name';
                @endphp
                <div class="form-group col-md-3 rv-info-field">
                    <label class="control-label rv-info-label">{{ $field['label'] }}</label>
                    <div class="form-control rv-info-value {{ $isClientName ? 'rv-info-value--emphasis' : '' }}"
                         readonly
                         tabindex="-1">{{ $field['value'] }}</div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted mb-0 small">No request details have been captured yet.</p>
                </div>
            @endforelse

            @if(!empty($requestInfoCard['remarks']))
                <div class="form-group col-md-12 rv-info-field rv-info-field--remarks">
                    <label class="control-label rv-info-label">Remarks</label>
                    <div class="form-control rv-info-remarks" readonly tabindex="-1">{{ $requestInfoCard['remarks'] }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
