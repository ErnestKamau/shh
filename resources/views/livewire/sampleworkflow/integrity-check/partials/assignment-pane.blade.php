@php
    $visibleRows = $visibleRows ?? [];
    $sectionNames = $sectionNames ?? [];
    $selectedCount = $selectedCount ?? 0;
    $activeSampleDossier = $activeSampleDossier ?? [];
    $activeSample = $activeSample ?? null;
    $sampleSummaries = $sampleSummaries ?? [];
    $testRows = $testRows ?? [];
    $analystNamesById = $analystNamesById ?? [];
    $filteredCount = count($this->filteredTestRows);
    $pagination = $this->testsPagination;
    $activeFilterCount = $this->activeTestFilterCount;
    $visibleKeys = array_values(array_map(
        static fn (array $row): string => (string) ($row['row_key'] ?? ''),
        $visibleRows
    ));
    $allVisibleSelected = $visibleKeys !== []
        && count(array_diff($visibleKeys, $selectedRowKeys)) === 0;
@endphp
<section class="integrity-panel integrity-assign-panel" aria-label="Tests and assignment for selected sample">
    <div class="integrity-panel__head">
        <h3 class="integrity-panel__title">
            <i class="mdi mdi-playlist-edit"></i>
            Tests &amp; assignment
        </h3>
    </div>

    @if($selectedCount > 0)
        @php
            $bulkSectionOptions = $this->bulkAnalystSectionOptions;
            $bulkSubcontractAll = $this->bulkSubcontractAllSelected;
            $bulkSelectedSectionIds = array_values(array_map(
                static fn (array $section): string => (string) ($section['id'] ?? ''),
                $bulkSectionOptions
            ));
            $labSectionSelectOptions = [];
            foreach ($this->labSections as $section) {
                $labSectionSelectOptions[(string) ($section['id'] ?? '')] = (string) ($section['name'] ?? 'Lab section');
            }
        @endphp
        <div class="integrity-bulk-bar" wire:key="integrity-bulk-bar-{{ $selectedCount }}-{{ implode('_', $bulkSelectedSectionIds) }}">
            <div class="integrity-bulk-toolbar">
                <div class="integrity-bulk-count">
                    <strong>{{ $selectedCount }}</strong> selected
                    <button type="button"
                        class="btn btn-link btn-sm p-0 ml-1 text-muted"
                        title="Clear selection"
                        aria-label="Clear selection"
                        wire:click="clearRowSelection">
                        <i class="mdi mdi-close" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="integrity-bulk-lab-sections-group">
                    <span class="integrity-bulk-group-label">Lab sections</span>
                    <div class="integrity-bulk-select-wrap {{ $bulkSelectedSectionIds === [] ? 'is-empty' : 'has-values' }}">
                        @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
                            'label' => null,
                            'id' => 'integrity-bulk-lab-sections',
                            'name' => 'bulkLabSectionIds',
                            'placeholder' => 'Set lab section(s)…',
                            'options' => $labSectionSelectOptions,
                            'selected' => $bulkSelectedSectionIds,
                            'variant' => 'slate',
                            'wireIgnore' => true,
                            'extraSelectClass' => 'integrity-bulk-section-select',
                            'selectedValuesJson' => json_encode(array_values($bulkSelectedSectionIds)),
                        ])
                    </div>
                    <button type="button"
                        class="integrity-toggle-btn integrity-bulk-save-sections"
                        title="Apply lab section assignments to selected tests"
                        aria-label="Apply lab section assignments to selected tests">
                        <i class="mdi mdi-content-save-outline" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center ml-auto" style="gap: 0.35rem;">
                    <button type="button"
                        class="integrity-toggle-btn {{ $bulkSubcontractAll ? 'is-active' : '' }}"
                        title="{{ $bulkSubcontractAll ? 'Unmark subcontract on selected' : 'Mark subcontract on selected' }}"
                        aria-label="{{ $bulkSubcontractAll ? 'Unmark subcontract on selected' : 'Mark subcontract on selected' }}"
                        wire:click="toggleBulkSubcontractedOnSelected">
                        <i class="mdi mdi-truck-delivery-outline" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown">
                        <button type="button"
                            class="integrity-toggle-btn dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            title="More bulk actions">
                            <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button type="button" class="dropdown-item integrity-bulk-remove-sections">
                                <i class="mdi mdi-minus-circle-outline mr-2"></i>
                                Remove lab section(s) from selected
                            </button>
                            <button type="button"
                                class="dropdown-item"
                                wire:click="copyAssignmentsFromFirstSelected"
                                @disabled($selectedCount < 2)>
                                <i class="mdi mdi-content-copy mr-2"></i>
                                Copy from first selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="integrity-bulk-analyst-row">
                <span class="integrity-bulk-group-label">Analysts</span>
                @if($bulkSectionOptions === [])
                    <span class="text-muted small ml-2">Assign lab section(s) on the selected test(s) first.</span>
                @else
                    <div class="integrity-bulk-analyst-columns">
                        @foreach($bulkSectionOptions as $section)
                            @php
                                $sectionId = (string) ($section['id'] ?? '');
                                $sectionAnalysts = $this->analystsBySection[$sectionId] ?? [];
                                $picked = array_values(array_map(
                                    'strval',
                                    is_array($bulkAnalystIdsBySection[$sectionId] ?? null)
                                        ? $bulkAnalystIdsBySection[$sectionId]
                                        : []
                                ));
                            @endphp
                            <div wire:key="bulk-analyst-group-{{ $sectionId }}">
                                <span class="integrity-bulk-analyst-group-label">{{ $section['name'] }}</span>
                                <div class="integrity-bulk-analyst-picks">
                                    @forelse($sectionAnalysts as $analyst)
                                        @php
                                            $analystId = (string) ($analyst['id'] ?? '');
                                            $isPicked = in_array($analystId, $picked, true);
                                        @endphp
                                        <button type="button"
                                            class="integrity-pick-chip {{ $isPicked ? 'is-active' : '' }}"
                                            wire:key="bulk-analyst-{{ $sectionId }}-{{ $analystId }}-{{ $isPicked ? 'selected' : 'available' }}"
                                            wire:click.prevent.debounce.400ms="toggleBulkAnalyst('{{ $sectionId }}', '{{ $analystId }}')">
                                            {{ $analyst['name'] }}
                                        </button>
                                    @empty
                                        <span class="text-muted small">No analysts for this section.</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="integrity-panel__body">
        <div
            class="integrity-assign-list__toolbar"
            x-data
            @click.outside="if ($wire.testFiltersOpen) $wire.closeTestFilters()"
            @keydown.escape.window="if ($wire.testFiltersOpen) $wire.closeTestFilters()"
        >
            <label class="integrity-assign-card__check mb-0">
                <input type="checkbox"
                    title="Select all visible"
                    wire:key="select-all-{{ $selectedCount }}-{{ count($visibleRows) }}-{{ $pagination['current_page'] }}"
                    @checked($allVisibleSelected)
                    @disabled($visibleRows === [])
                    wire:click="{{ $allVisibleSelected ? 'clearRowSelection' : 'selectAllVisibleRows' }}">
                <span class="small text-muted ml-1">Select all visible</span>
            </label>

            <div class="integrity-assign-list__search">
                @include('layouts.lab.partials.ls-ui.fields.ls-search-bar', [
                    'placeholder' => 'Search tests…',
                    'variant' => 'ghost',
                    'wireModel' => 'testSearch',
                    'fullWidth' => false,
                    'filterWireClick' => 'toggleTestFilters',
                    'filtersOpen' => $testFiltersOpen,
                    'filterBadge' => $activeFilterCount,
                ])

                @if($activeFilterCount > 0 || $testSearch !== '')
                    <button type="button" class="ls-quotation-search-toolbar__clear" wire:click="clearTestFilters" title="Clear filters">
                        <i class="mdi mdi-close-circle-outline"></i>
                        <span>Clear</span>
                    </button>
                @endif

                @if($testFiltersOpen)
                    <div class="ls-quotation-filter-panel integrity-assign-filter-panel">
                        <div class="ls-quotation-filter-panel__head">
                            <span><i class="mdi mdi-filter-variant"></i> Filters</span>
                            <button type="button" class="ls-quotation-filter-panel__close" wire:click="closeTestFilters" aria-label="Close filters">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>

                        <div class="ls-quotation-filter-panel__grid ls-compact">
                            <div class="ls-field">
                                <label class="ls-field__label" for="integrity-filter-status">Status</label>
                                <div class="ls-field__control">
                                    <select id="integrity-filter-status" class="ls-field__input" wire:model.live="testFilter">
                                        <option value="all">All</option>
                                        <option value="incomplete">Incomplete</option>
                                        <option value="subcontracted">Subcontracted</option>
                                    </select>
                                </div>
                            </div>

                            <div class="ls-field">
                                <label class="ls-field__label" for="integrity-filter-lab-section">Lab section</label>
                                <div class="ls-field__control">
                                    <select id="integrity-filter-lab-section" class="ls-field__input" wire:model.live="filterLabSectionId">
                                        <option value="">All lab sections</option>
                                        @foreach($this->labSections as $section)
                                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="ls-field">
                                <label class="ls-field__label" for="integrity-filter-analysis-type">Analysis type</label>
                                <div class="ls-field__control">
                                    <select id="integrity-filter-analysis-type" class="ls-field__input" wire:model.live="filterAnalysisType">
                                        <option value="">All analysis types</option>
                                        @foreach($this->filterAnalysisTypeOptions as $analysisType)
                                            <option value="{{ $analysisType }}">{{ $analysisType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="ls-field">
                                <label class="ls-field__label" for="integrity-filter-sample-type">Sample type</label>
                                <div class="ls-field__control">
                                    <select id="integrity-filter-sample-type" class="ls-field__input" wire:model.live="filterSampleType">
                                        <option value="">All sample types</option>
                                        @foreach($this->filterSampleTypeOptions as $sampleType)
                                            <option value="{{ $sampleType }}">{{ $sampleType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ls-quotation-filter-panel__foot">
                            <button type="button" class="ls-btn" wire:click="clearTestFilters">
                                Clear all
                            </button>
                            <button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="closeTestFilters">
                                Done
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($filteredCount === 0)
            <div class="integrity-assign-empty">
                No tests match the current filter for this sample.
            </div>
        @else
            <div class="integrity-assign-list">
                @foreach($visibleRows as $row)
                    @php
                        $rowKey = (string) ($row['row_key'] ?? '');
                        $selectedSections = array_map('strval', $row['lab_section_ids'] ?? []);
                        $isEditing = $editingRowKey === $rowKey;
                        $isComplete = ! empty($row['is_complete']);
                        $isSubcontracted = ! empty($row['subcontracted']);
                        $assignedAnalystIds = [];
                        foreach (($row['analysts_by_lab_section'] ?? []) as $analystIds) {
                            foreach ((array) $analystIds as $analystId) {
                                $assignedAnalystIds[(string) $analystId] = true;
                            }
                        }
                        $assignedAnalystNames = array_map(
                            fn (string $analystId): string => $analystNamesById[$analystId] ?? 'Unknown analyst',
                            array_keys($assignedAnalystIds)
                        );
                    @endphp
                    <article wire:key="integrity-row-{{ $rowKey }}"
                        class="integrity-assign-card {{ $isEditing ? 'is-editing' : '' }} {{ $isComplete ? 'is-complete' : 'is-incomplete' }} {{ $isSubcontracted ? 'is-subcontracted' : '' }}">
                        <div class="integrity-assign-card__main">
                            <label class="integrity-assign-card__check">
                                <input type="checkbox"
                                    value="{{ $rowKey }}"
                                    wire:model.live="selectedRowKeys">
                            </label>
                            <div>
                                <h4 class="integrity-assign-card__title">
                                    <button type="button"
                                        class="integrity-assign-card__title-btn"
                                        wire:click="startEditingRow('{{ $rowKey }}')"
                                        title="Edit assignment">
                                        {{ $row['test_label'] ?? '—' }}
                                    </button>
                                </h4>
                                <div class="integrity-assign-card__meta">
                                    @if($isSubcontracted)
                                        <span class="ls-quotation-meta-chip ls-quotation-meta-chip--warn">
                                            <i class="mdi mdi-truck-delivery-outline"></i> Subcontracted
                                        </span>
                                    @elseif($isComplete)
                                        <span class="integrity-assign-status-chip integrity-assign-status-chip--assigned">
                                            <i class="mdi mdi-check-circle-outline"></i> Assigned
                                        </span>
                                    @else
                                        <span class="ls-quotation-meta-chip ls-quotation-meta-chip--warn">
                                            <i class="mdi mdi-alert-circle-outline"></i> Needs assignment
                                        </span>
                                    @endif
                                </div>
                                <div class="integrity-assign-field">
                                    <span class="integrity-assign-field__label">Lab section(s)</span>
                                    @if($selectedSections === [])
                                        <span class="text-muted small">None selected</span>
                                    @else
                                        <div class="integrity-chip-wrap">
                                            @foreach($selectedSections as $sectionId)
                                                <span class="integrity-chip integrity-chip--section">
                                                    {{ $sectionNames[$sectionId] ?? 'Section' }}
                                                    <button type="button"
                                                        class="integrity-chip-remove"
                                                        title="Remove {{ $sectionNames[$sectionId] ?? 'section' }}"
                                                        wire:click.stop="removeLabSectionChip('{{ $rowKey }}', '{{ $sectionId }}')">
                                                        <i class="mdi mdi-close" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="integrity-assign-field">
                                    <span class="integrity-assign-field__label">Analyst(s)</span>
                                    @if($selectedSections === [])
                                        <span class="text-muted small">—</span>
                                    @elseif($isSubcontracted)
                                        <span class="text-muted small">N/A (subcontracted)</span>
                                    @elseif($assignedAnalystNames === [])
                                        <span class="text-muted small">None assigned</span>
                                    @else
                                        <div class="integrity-chip-wrap">
                                            @foreach($assignedAnalystNames as $analystName)
                                                <span class="integrity-chip">{{ $analystName }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="integrity-assign-card__actions">
                                <button type="button"
                                    class="integrity-toggle-btn {{ $isSubcontracted ? 'is-active' : '' }}"
                                    title="{{ $isSubcontracted ? 'Unmark subcontract' : 'Mark subcontract' }}"
                                    wire:click.prevent="toggleSubcontracted('{{ $rowKey }}')">
                                    <i class="mdi mdi-truck-delivery-outline" aria-hidden="true"></i>
                                </button>
                                <button type="button"
                                    class="integrity-toggle-btn integrity-toggle-btn--info"
                                    title="View test information"
                                    wire:click.prevent="openTestInfoModal('{{ $rowKey }}')">
                                    <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        @if($isEditing)
                            <div class="integrity-assign-editor" wire:key="integrity-editor-{{ $rowKey }}">
                                <div class="integrity-editor-grid">
                                    <div>
                                        <div class="ls-type-label mb-1">Lab section(s)</div>
                                        <div wire:ignore>
                                            <select class="form-control form-control-sm integrity-lab-section-select"
                                                multiple
                                                data-row-key="{{ $rowKey }}"
                                                data-placeholder="Select lab section(s)">
                                                @foreach($this->labSections as $section)
                                                    <option value="{{ $section['id'] }}"
                                                        @selected(in_array((string) $section['id'], $selectedSections, true))>
                                                        {{ $section['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="ls-type-label mb-1">Analyst(s)</div>
                                        @forelse($selectedSections as $sectionId)
                                            @php
                                                $analysts = $this->analystsBySection[$sectionId] ?? [];
                                                $assigned = array_map('strval', $row['analysts_by_lab_section'][$sectionId] ?? []);
                                            @endphp
                                            <div class="mb-2">
                                                <div class="integrity-bulk-analyst-group-label mb-1">
                                                    {{ $sectionNames[$sectionId] ?? 'Lab section' }}
                                                </div>
                                                <div class="integrity-bulk-analyst-picks">
                                                    @forelse($analysts as $analyst)
                                                        @php
                                                            $analystId = (string) ($analyst['id'] ?? '');
                                                            $isPicked = in_array($analystId, $assigned, true);
                                                        @endphp
                                                        <button type="button"
                                                            class="integrity-pick-chip {{ $isPicked ? 'is-active' : '' }}"
                                                            wire:click.prevent="toggleRowAnalyst('{{ $rowKey }}', '{{ $sectionId }}', '{{ $analystId }}')">
                                                            {{ $analyst['name'] }}
                                                        </button>
                                                    @empty
                                                        <span class="text-muted small">No analysts for this section.</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-muted small">Select lab section(s) first.</span>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="mt-3 text-right">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="stopEditingRow">Done</button>
                                </div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if($pagination['total'] > 0)
                <div class="integrity-assign-pagination {{ $pagination['last_page'] <= 1 ? 'integrity-assign-pagination--meta-only' : '' }}">
                    <div class="integrity-assign-pagination__meta">
                        Showing {{ $pagination['from'] }}–{{ $pagination['to'] }} of {{ $pagination['total'] }}
                    </div>
                    @if($pagination['last_page'] > 1)
                        <div class="integrity-assign-pagination__controls">
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                wire:click="goToTestsPage({{ $pagination['current_page'] - 1 }})"
                                @disabled($pagination['current_page'] <= 1)>
                                Prev
                            </button>
                            <span class="integrity-assign-pagination__page">
                                Page {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}
                            </span>
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                wire:click="goToTestsPage({{ $pagination['current_page'] + 1 }})"
                                @disabled($pagination['current_page'] >= $pagination['last_page'])>
                                Next
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>
</section>
