@php
    $sampleCollection = $contextRail['sample_collection'] ?? [];
    $canEditTrf = $canEditSampleRows ?? false;

    /** Gallery icons (ls-icon-catalog) — burgundy tile background kept. */
    $iconFor = static function (?string $name): string {
        $key = strtolower(trim((string) $name));

        return match (true) {
            str_contains($key, 'date') && str_contains($key, 'receiv') => 'mdi-calendar-check',
            str_contains($key, 'date') => 'mdi-calendar-clock',
            str_contains($key, 'time') => 'mdi-clock-outline',
            str_contains($key, 'location') || str_contains($key, 'point') => 'mdi-map-marker-radius-outline',
            str_contains($key, 'transport') => 'mdi-truck-outline',
            str_contains($key, 'apparatus') || str_contains($key, 'sampler') => 'mdi-flask-outline',
            str_contains($key, 'thermometer') || str_contains($key, 'temp') => 'mdi-thermometer',
            str_contains($key, 'method') => 'mdi-clipboard-check-outline',
            str_contains($key, 'reason') => 'mdi-comment-text-outline',
            default => 'mdi-test-tube',
        };
    };
@endphp

<div class="rv-sample-collection-tab">
    <div class="rv-sample-collection-tab__header">
        <div>
            <h5 class="rv-sample-collection-tab__title mb-1">
                <span class="ls-icon-tile__glyph ls-icon--burgundy rv-tab-title-icon" aria-hidden="true">
                    <i class="mdi mdi-map-marker-radius-outline"></i>
                </span>
                Sample collection data
            </h5>
            <p class="rv-sample-collection-tab__hint mb-0">
                Sampling date, location, transport, apparatus, and method captured for this request.
            </p>
        </div>
        <div class="d-inline-flex align-items-center" style="gap: 6px;">
            <button type="button"
                class="rv-rail-edit-btn"
                wire:click="openTrfViewer('collection')"
                title="View collection details">
                <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                View
            </button>
            @if($canEditTrf)
                <button type="button"
                    class="rv-rail-edit-btn"
                    wire:click="openTrfEditor('collection')"
                    title="Edit collection details">
                    <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                    Edit
                </button>
            @endif
        </div>
    </div>

    @if(count($sampleCollection) > 0)
        <div class="rv-sample-collection-grid">
            @foreach($sampleCollection as $field)
                <article class="rv-sample-collection-card">
                    <span class="ls-icon-tile__glyph ls-icon--burgundy rv-sample-collection-card__icon" aria-hidden="true">
                        <i class="mdi {{ $iconFor($field['name'] ?? null) }}"></i>
                    </span>
                    <div class="rv-sample-collection-card__body">
                        <div class="rv-sample-collection-card__label">{{ $field['label'] ?? 'Field' }}</div>
                        <div class="rv-sample-collection-card__value">{{ $field['value'] ?? '—' }}</div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rv-sample-collection-empty">
            <span class="ls-icon-tile__glyph ls-icon--burgundy" aria-hidden="true">
                <i class="mdi mdi-test-tube"></i>
            </span>
            <p class="mb-0 mt-2">No collection details captured yet.</p>
            @if($canEditTrf)
                <button type="button"
                    class="btn btn-sm btn-primary mt-3"
                    wire:click="openTrfEditor('collection')">
                    Add collection details
                </button>
            @endif
        </div>
    @endif
</div>
