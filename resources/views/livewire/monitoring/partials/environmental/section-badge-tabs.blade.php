@php
    $selectedLab = $this->assignedLabs->firstWhere('id', $selectedLabId);
@endphp

<div class="env-section-nav mb-3">
    <div class="env-section-nav__toolbar">
        <div class="env-section-nav__heading">
            <span class="env-section-nav__lab">{{ $selectedLab?->name ?? 'Lab' }}</span>
            <span class="env-section-nav__subtitle">Sections</span>
        </div>
        <div class="env-section-nav__history">
            <label class="env-section-nav__history-label" for="env-log-history">History</label>
            <select id="env-log-history"
                    wire:model.live="logsDateRange"
                    class="form-control form-control-sm env-history-select">
                <option value="7">7 days</option>
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90">90 days</option>
            </select>
        </div>
    </div>

    @if($this->environmentalSectionsForLab->count() > 0)
        <div class="env-section-chip-track" role="tablist" aria-label="Lab sections">
            @foreach($this->environmentalSectionsForLab as $section)
                @php $isActive = $selectedSectionId === $section->id; @endphp
                <button type="button"
                        role="tab"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        class="env-section-chip {{ $isActive ? 'is-active' : '' }}"
                        wire:click="selectSection('{{ $section->id }}')"
                        title="{{ $section->name }} · {{ $section->formattedOptimumLevel() }}">
                    <span class="env-section-chip__code">{{ $section->code }}</span>
                    <span class="env-section-chip__name">{{ Str::limit($section->name, 32) }}</span>
                    <span class="env-section-chip__meta">{{ $section->formattedOptimumLevel() }}</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="env-section-nav__empty">
            No environmental monitoring sections configured for this lab.
        </div>
    @endif
</div>
