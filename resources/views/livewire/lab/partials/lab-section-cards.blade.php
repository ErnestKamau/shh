@php
    /** @var \App\Lab $lab */
    $sections = $sections ?? $lab->labSections;
    $embeddedInTab = $embeddedInTab ?? false;
    $sectionSearch = $sectionSearch ?? '';
@endphp

<div class="{{ $embeddedInTab ? '' : 'section-shell' }}">
    @unless($embeddedInTab)
        <div class="section-shell__header">
            <div>
                <h6 class="mb-1">
                    <i class="mdi mdi-layers-triple"></i>
                    Lab Sections for {{ $lab->name }}
                </h6>
                <p class="text-muted mb-0">Track environmental analysis setup, expected values, and reporting units.</p>
            </div>
            @can('laboratory.components.labs.edit')
                <button type="button" class="btn btn-primary btn-sm section-add-btn" wire:click="showCreateLabSectionModal('{{ $lab->id }}')">
                    <i class="mdi mdi-plus"></i> Add Lab Section
                </button>
            @endcan
        </div>
    @endunless

    <div class="row g-4 {{ $embeddedInTab ? '' : 'mb-3' }}">
        @forelse($sections as $section)
            <div class="col-md-6 col-xl-4" wire:key="lab-section-card-{{ $section->id }}">
                <article class="section-card section-card--modern h-100">
                    <div class="section-card__accent {{ $section->does_environmental_analysis ? 'section-card__accent--env' : '' }}"></div>

                    <header class="section-card__header">
                        <div class="section-card__identity">
                            <span class="section-card__code">{{ $section->code }}</span>
                            <h3 class="section-card__title">{{ $section->name }}</h3>
                        </div>
                        <div class="section-card__toolbar">
                            <span class="section-card__status {{ $section->active ? 'section-card__status--active' : 'section-card__status--inactive' }}">
                                <span class="section-card__status-dot"></span>
                                {{ $section->active ? 'Active' : 'Inactive' }}
                            </span>
                            @can('laboratory.components.labs.edit')
                                <div class="section-card__actions">
                                    <button type="button" class="section-card__action section-card__action--edit" wire:click="showEditLabSectionModal('{{ $section->id }}')" title="Edit Section">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button type="button" class="section-card__action section-card__action--delete" wire:click="confirmDeleteLabSection('{{ $section->id }}')" title="Delete Section">
                                        <i class="mdi mdi-trash-can-outline"></i>
                                    </button>
                                </div>
                            @endcan
                        </div>
                    </header>

                    <div class="section-card__body">
                        <div class="section-card__detail">
                            <div class="section-card__detail-icon section-card__detail-icon--env">
                                <i class="mdi mdi-leaf"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Environmental Monitoring</span>
                                <span class="section-card__detail-value">{{ $section->does_environmental_analysis ? 'Enabled' : 'Not configured' }}</span>
                            </div>
                        </div>

                        <div class="section-card__detail">
                            <div class="section-card__detail-icon">
                                <i class="mdi mdi-microscope"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Equipment</span>
                                <span class="section-card__detail-value">{{ $section->equipment->name ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="section-card__detail">
                            <div class="section-card__detail-icon">
                                <i class="mdi mdi-target"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Optimum Level</span>
                                <span class="section-card__detail-value">{{ $section->formattedOptimumLevel() }}</span>
                            </div>
                        </div>

                        <div class="section-card__detail">
                            <div class="section-card__detail-icon">
                                <i class="mdi mdi-chart-line"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Result Nature</span>
                                <span class="section-card__detail-value">{{ $section->result_nature ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="section-card__detail">
                            <div class="section-card__detail-icon">
                                <i class="mdi mdi-clock-outline"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Reading Frequency</span>
                                <span class="section-card__detail-value">{{ $section->reading_frequency ? $section->readingFrequencyLabel() : '—' }}</span>
                            </div>
                        </div>

                        @if($section->reading_frequency)
                            <div class="section-card__detail">
                                <div class="section-card__detail-icon">
                                    <i class="mdi mdi-calendar-clock"></i>
                                </div>
                                <div class="section-card__detail-text">
                                    <span class="section-card__detail-label">Reading Schedule</span>
                                    <span class="section-card__detail-value">{{ $section->formattedReadingFrequencySchedule() }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="section-card__detail">
                            <div class="section-card__detail-icon">
                                <i class="mdi mdi-ruler"></i>
                            </div>
                            <div class="section-card__detail-text">
                                <span class="section-card__detail-label">Reporting Unit</span>
                                <span class="section-card__detail-value">{{ $section->reportingUnit->name ?? $section->reporting_unit ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-section-state">
                    <i class="mdi mdi-{{ $sectionSearch !== '' ? 'magnify-close' : 'flask-empty' }}"></i>
                    @if($sectionSearch !== '' && $lab->labSections->isNotEmpty())
                        <p class="mb-0">No sections match "<strong>{{ $sectionSearch }}</strong>".</p>
                    @else
                        <p class="mb-0">No sections created for this lab yet.</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
</div>
