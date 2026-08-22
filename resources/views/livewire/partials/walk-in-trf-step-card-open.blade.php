@php
    $stepCardTitle = $stepCardTitle ?? 'Section';
    $stepCardSummary = trim((string) ($stepCardSummary ?? ''));
    $stepCardKey = $stepCardKey ?? 'trf-step-card';
    $stepCardOpen = (bool) ($stepCardOpen ?? true);
@endphp
<div class="rft-trf-step-cards" wire:key="{{ $stepCardKey }}">
    <article
        class="rft-sample-row-card rft-trf-step-card"
        x-data="{ open: @json($stepCardOpen) }"
        :class="{ 'is-open': open }"
    >
        <div class="rft-sample-row-card__header">
            <button
                type="button"
                class="rft-sample-row-card__toggle-main border-0 bg-transparent p-0 text-left flex-grow-1 min-width-0"
                @click="open = !open"
                :aria-expanded="open ? 'true' : 'false'"
            >
                <h6 class="rft-sample-row-card__title mb-0">{{ $stepCardTitle }}</h6>
                @if($stepCardSummary !== '')
                    <div class="rft-sample-row-card__summary text-muted small mt-1" x-show="!open" x-cloak>
                        {{ $stepCardSummary }}
                    </div>
                @endif
            </button>
            <div class="rft-sample-row-card__header-actions">
                <button
                    type="button"
                    class="rft-sample-row-card__chevron border-0 bg-transparent p-0"
                    @click="open = !open"
                    :aria-expanded="open ? 'true' : 'false'"
                    :aria-label="open ? 'Collapse section' : 'Expand section'"
                >
                    <i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                </button>
            </div>
        </div>
        <div class="rft-sample-row-card__body" x-show="open" x-cloak>
