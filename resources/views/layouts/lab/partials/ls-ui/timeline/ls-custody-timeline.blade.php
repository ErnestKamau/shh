{{--
    Auditor-grade custody timeline with expandable detail cards.

    @param \Illuminate\Support\Collection|array $events
    @param string $emptyMessage
    @param string $rootClass
--}}
@php
    $events = collect($events ?? []);
    $emptyMessage = $emptyMessage ?? 'No custody or workflow events recorded yet.';
    $rootClass = trim($rootClass ?? 'ls-custody-timeline');
@endphp

<style>
    .ls-custody-timeline {
        --coc-spine: #cbd5e1;
        --coc-dot: #3b82f6;
        --coc-dot-done: #10b981;
        --coc-dot-warn: #f59e0b;
        --coc-card-border: #e2e8f0;
        --coc-muted: #64748b;
        position: relative;
        padding: 0.25rem 0 0.25rem 1.85rem;
        margin: 0.75rem 0;
    }

    .ls-custody-timeline::before {
        content: '';
        position: absolute;
        left: 0.45rem;
        top: 0.35rem;
        bottom: 0.35rem;
        width: 2px;
        background: var(--coc-spine);
    }

    .ls-custody-timeline__event {
        position: relative;
        margin-bottom: 0.85rem;
    }

    .ls-custody-timeline__event:last-child {
        margin-bottom: 0;
    }

    .ls-custody-timeline__dot {
        position: absolute;
        left: -1.85rem;
        top: 0.85rem;
        width: 0.95rem;
        height: 0.95rem;
        border-radius: 999px;
        background: #fff;
        border: 2px solid var(--coc-dot);
        z-index: 1;
    }

    .ls-custody-timeline__dot.is-success { border-color: var(--coc-dot-done); background: var(--coc-dot-done); }
    .ls-custody-timeline__dot.is-warning { border-color: var(--coc-dot-warn); background: var(--coc-dot-warn); }
    .ls-custody-timeline__dot.is-workflow { border-color: #6366f1; }

    .ls-custody-timeline__card {
        border: 1px solid var(--coc-card-border);
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
    }

    .ls-custody-timeline__toggle {
        width: 100%;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
        border: 0;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .ls-custody-timeline__toggle:hover {
        background: #f8fafc;
    }

    .ls-custody-timeline__title-wrap {
        min-width: 0;
        flex: 1 1 auto;
    }

    .ls-custody-timeline__title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
    }

    .ls-custody-timeline__subtitle {
        margin: 0.15rem 0 0;
        font-size: 0.75rem;
        color: var(--coc-muted);
    }

    .ls-custody-timeline__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        color: var(--coc-muted);
        white-space: nowrap;
    }

    .ls-custody-timeline__chevron {
        color: var(--coc-muted);
        transition: transform 0.15s ease;
    }

    .ls-custody-timeline__chevron.is-open {
        transform: rotate(180deg);
    }

    .ls-custody-timeline__body {
        border-top: 1px solid #f1f5f9;
        padding: 0.75rem 0.85rem 0.85rem;
        background: #fafbfc;
    }

    .ls-custody-timeline__grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.65rem;
    }

    @media (min-width: 768px) {
        .ls-custody-timeline__grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .ls-custody-timeline__field-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--coc-muted);
        margin-bottom: 0.15rem;
    }

    .ls-custody-timeline__field-value {
        font-size: 0.8125rem;
        color: #334155;
        line-height: 1.45;
    }

    .ls-custody-timeline__field-value.is-action {
        font-weight: 600;
        color: #0f172a;
    }

    .ls-custody-timeline__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.1rem 0.45rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ls-custody-timeline__empty {
        margin: 0;
        padding: 1rem 0.25rem;
        color: var(--coc-muted);
        font-size: 0.875rem;
    }
</style>

@if($events->isEmpty())
    <p class="ls-custody-timeline__empty">{{ $emptyMessage }}</p>
@else
    <div class="{{ $rootClass }} ls-ui-kit">
        @foreach($events as $index => $event)
            @php
                $badge = (string) ($event->badge ?? '');
                $isCompleted = ! empty($event->is_completed) || $badge === 'success';
                $isWorkflow = ($event->source ?? '') === 'workflow_event';
                $dotClass = $isCompleted ? 'is-success' : ($badge === 'warning' ? 'is-warning' : ($isWorkflow ? 'is-workflow' : ''));
                $metadata = is_array($event->metadata ?? null) ? $event->metadata : [];
                $actionText = filled($event->comment ?? null) ? $event->comment : null;
                $hasDetails = filled($actionText)
                    || filled($event->user_name ?? null)
                    || filled($event->occurred_at ?? null)
                    || filled($event->what ?? null)
                    || filled($event->how ?? null)
                    || filled($event->why ?? null)
                    || filled($event->where ?? null)
                    || $metadata !== []
                    || ($event->source ?? '') === 'batch';
            @endphp
            <div class="ls-custody-timeline__event" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                <span class="ls-custody-timeline__dot {{ $dotClass }}" aria-hidden="true"></span>
                <article class="ls-custody-timeline__card">
                    <button type="button"
                        class="ls-custody-timeline__toggle"
                        @click="open = !open"
                        :aria-expanded="open">
                        <span class="ls-custody-timeline__title-wrap">
                            <h6 class="ls-custody-timeline__title">{{ $event->title ?? 'Event' }}</h6>
                            @if(! empty($event->subtitle))
                                <p class="ls-custody-timeline__subtitle">{{ $event->subtitle }}</p>
                            @endif
                        </span>
                        <span class="ls-custody-timeline__meta">
                            @if(! empty($event->event_type))
                                <span class="ls-custody-timeline__badge">{{ str_replace('_', ' ', $event->event_type) }}</span>
                            @endif
                            <i class="mdi mdi-account-outline"></i>
                            {{ $event->user_name ?? 'System' }}
                            <span>&bull;</span>
                            <i class="mdi mdi-clock-outline"></i>
                            {{ $event->occurred_at?->format('Y-m-d H:i') ?? '—' }}
                            @if($hasDetails)
                                <i class="mdi mdi-chevron-down ls-custody-timeline__chevron" :class="{ 'is-open': open }"></i>
                            @endif
                        </span>
                    </button>

                    @if($hasDetails)
                        <div class="ls-custody-timeline__body" x-show="open" x-cloak>
                            <div class="ls-custody-timeline__grid">
                                @if(filled($actionText))
                                    <div class="md:col-span-2">
                                        <span class="ls-custody-timeline__field-label">Action</span>
                                        <span class="ls-custody-timeline__field-value is-action">{{ $actionText }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->user_name))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">Who</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->user_name }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->occurred_at))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">When</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->occurred_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->what))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">What</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->what }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->how))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">How</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->how }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->where))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">Where</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->where }}</span>
                                    </div>
                                @endif
                                @if(! empty($event->why))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">Why</span>
                                        <span class="ls-custody-timeline__field-value">{{ $event->why }}</span>
                                    </div>
                                @endif
                                @if($event->source === 'batch' && isset($event->is_completed))
                                    <div>
                                        <span class="ls-custody-timeline__field-label">Completion</span>
                                        <span class="ls-custody-timeline__field-value">
                                            @if($event->is_completed)
                                                {{ $event->completed_by ?? 'System' }}
                                                @if(! empty($event->completed_at))
                                                    · {{ is_object($event->completed_at) ? $event->completed_at->format('Y-m-d H:i') : $event->completed_at }}
                                                @endif
                                            @else
                                                In progress
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                @foreach($metadata as $metaKey => $metaValue)
                                    @if(is_scalar($metaValue) && filled($metaValue))
                                        <div>
                                            <span class="ls-custody-timeline__field-label">{{ ucwords(str_replace('_', ' ', (string) $metaKey)) }}</span>
                                            <span class="ls-custody-timeline__field-value">{{ $metaValue }}</span>
                                        </div>
                                    @elseif(is_array($metaValue) && $metaValue !== [])
                                        <div>
                                            <span class="ls-custody-timeline__field-label">{{ ucwords(str_replace('_', ' ', (string) $metaKey)) }}</span>
                                            <span class="ls-custody-timeline__field-value">{{ implode(', ', array_map('strval', $metaValue)) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </div>
        @endforeach
    </div>
@endif
