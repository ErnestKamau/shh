@php
    $selectedLab = $this->assignedLabs->firstWhere('id', $selectedLabId);
@endphp

<div class="env-section-list">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-1">
                <i class="mdi mdi-flask-outline text-primary"></i>
                {{ $selectedLab?->name ?? 'Lab' }} — Environmental sections
            </h5>
            <p class="text-muted small mb-0">Select a section to view configuration, logs, and trend charts.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">Log history</label>
            <select wire:model.live="logsDateRange" class="form-control form-control-sm" style="width: auto;">
                <option value="7">7 days</option>
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90">90 days</option>
            </select>
        </div>
    </div>

    @if($this->environmentalSectionsForLab->count() > 0)
        <div class="row g-3">
            @foreach($this->environmentalSectionsForLab as $section)
                <div class="col-md-6 col-xl-4">
                    <button type="button"
                            class="env-section-card w-100 text-start"
                            wire:click="selectSection('{{ $section->id }}')">
                        <div class="env-section-card__header">
                            <span class="env-section-card__code">{{ $section->code }}</span>
                            @if($section->equipment)
                                <span class="env-badge env-badge--equipment">
                                    <i class="mdi mdi-tools"></i> {{ Str::limit($section->equipment->name, 22) }}
                                </span>
                            @endif
                        </div>
                        <h6 class="env-section-card__title mb-2">{{ $section->name }}</h6>
                        <div class="env-section-card__meta">
                            <span class="env-badge env-badge--optimum">{{ $section->formattedOptimumLevel() }}</span>
                            <span class="env-badge env-badge--freq">{{ $section->readingFrequencyLabel() }}</span>
                        </div>
                        @if($section->formattedReadingFrequencySchedule() !== '—')
                            <p class="env-section-card__schedule mb-0">{{ $section->formattedReadingFrequencySchedule() }}</p>
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-light border text-center py-5">
            <i class="mdi mdi-flask-empty-outline d-block mb-2" style="font-size: 2.5rem; color: #94a3b8;"></i>
            <p class="text-muted mb-0">No environmental monitoring sections are configured for this lab.</p>
        </div>
    @endif
</div>
