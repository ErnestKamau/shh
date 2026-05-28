@php
    $statusBadge = match ($request->status) {
        'open' => 'rr-badge--open',
        'pending_approval' => 'rr-badge--pending',
        'returned' => 'rr-badge--returned',
        'closed' => 'rr-badge--closed',
        'cancelled' => 'rr-badge--cancelled',
        'draft' => 'rr-badge--draft',
        default => 'rr-badge--neutral',
    };
    $statusLabel = ucwords(str_replace('_', ' ', $request->status));
    $priorityBadge = match (strtolower((string) $request->priority)) {
        'high', 'urgent' => 'rr-badge--priority-high',
        'medium' => 'rr-badge--priority-medium',
        'low' => 'rr-badge--priority-low',
        default => 'rr-badge--neutral',
    };
    $priorityLabel = $request->priority ? ucfirst($request->priority) : '—';
@endphp

<div>
<div class="rr-hero mb-4">
    <div class="rr-hero__top">
        <div class="rr-hero__identity">
            <div class="rr-hero__icon">
                <i class="mdi mdi-file-document-outline"></i>
            </div>
            <div>
                <p class="rr-hero__eyebrow">Registry Request</p>
                <h1 class="rr-hero__ref">{{ $request->reference_no }}</h1>
                <h2 class="rr-hero__subject">{{ $request->subject }}</h2>
            </div>
        </div>
        <div class="rr-hero__badges">
            <span class="rr-badge {{ $statusBadge }}">
                <i class="mdi mdi-circle-small"></i>{{ $statusLabel }}
            </span>
            @if($request->priority)
                <span class="rr-badge {{ $priorityBadge }}">{{ $priorityLabel }} priority</span>
            @endif
            @if($request->category)
                <span class="rr-badge rr-badge--category">{{ $request->category->name }}</span>
            @endif
        </div>
    </div>

    @if($request->description)
        <div class="rr-hero__description mb-0">
            <p class="rr-hero__description-label">Description</p>
            <p class="rr-hero__description-text">{{ $request->description }}</p>
        </div>
    @endif
</div>

<section class="rr-info-section mb-4" aria-labelledby="rr-info-heading">
    <div class="rr-info-section__head">
        <div class="rr-info-section__icon"><i class="mdi mdi-information-outline"></i></div>
        <div>
            <h2 id="rr-info-heading" class="rr-info-section__title">Request Information</h2>
            <p class="rr-info-section__sub">Key metadata for this registry request</p>
        </div>
    </div>
    <div class="rr-info-section__body">
        <div class="rr-meta-grid">
            <div class="rr-meta-item">
                <span class="rr-meta-label">Current stage</span>
                <span class="rr-meta-value">{{ $request->current_stage ?: '—' }}</span>
            </div>
            <div class="rr-meta-item">
                <span class="rr-meta-label">Direction</span>
                <span class="rr-meta-value">{{ $request->direction ? ucfirst($request->direction) : '—' }}</span>
            </div>
            <div class="rr-meta-item">
                <span class="rr-meta-label">Assigned to</span>
                <span class="rr-meta-value">{{ $request->assignee?->name ?? 'Unassigned' }}</span>
            </div>
            <div class="rr-meta-item">
                <span class="rr-meta-label">Submitting party</span>
                <span class="rr-meta-value">{{ $request->submitting_party ?: '—' }}</span>
            </div>
            <div class="rr-meta-item">
                <span class="rr-meta-label">Received</span>
                <span class="rr-meta-value">{{ $request->received_at?->format('M j, Y H:i') ?? '—' }}</span>
            </div>
            <div class="rr-meta-item">
                <span class="rr-meta-label">Closed</span>
                <span class="rr-meta-value">{{ $request->closed_at?->format('M j, Y H:i') ?? '—' }}</span>
            </div>
        </div>
    </div>
</section>
</div>
