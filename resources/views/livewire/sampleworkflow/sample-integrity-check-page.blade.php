<div
    class="sample-integrity-check-page"
    x-data="{
        sampleInfoOpen: false,
        sampleInfoTitle: '',
        sampleInfoDetails: '',
        sampleInfoFields: [],
        sampleInfoTests: []
    }"
>
    @if($flashMessage !== '')
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ $flashMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1">Sample Integrity &amp; Acceptance Check</h4>
            <p class="text-muted mb-0 small">
                Work sample by sample. Assign lab sections and analysts in bulk, then Accept to create the job and sample numbers.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $this->collectionLabelUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-printer-outline"></i> Collection label
            </a>
            <a href="{{ $this->registrationLabelUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-barcode"></i> Lab sample labels
            </a>
            <button
                type="button"
                class="btn btn-sm btn-primary"
                wire:click="openAcceptConfirm"
                @disabled(! $this->canAccept)
                title="Accept and create job/samples"
            >
                <i class="mdi mdi-check-circle-outline"></i> Accept
            </button>
        </div>
    </div>

    @include('livewire.submission-forms.request-view.cards.request-info', [
        'requestInfoCard' => $this->requestInfoCard,
    ])

    @php
        $sampleSummaries = $this->sampleSummaries;
        $visibleRows = $this->visibleTestRows;
        $sectionNames = $this->labSectionNames;
        $analystNamesById = [];
        foreach ($this->analystsBySection as $sectionAnalysts) {
            foreach ($sectionAnalysts as $analyst) {
                $analystNamesById[(string) $analyst['id']] = (string) $analyst['name'];
            }
        }
        $selectedCount = count($selectedRowKeys);
        $activeSample = collect($sampleSummaries)->firstWhere('key', $selectedSampleKey);
    @endphp

    <div class="card rv-card integrity-workspace">
        <div class="card-header rv-card-header d-flex justify-content-between align-items-center">
            <h5 class="rv-card-title mb-0">Samples &amp; tests</h5>
            <span class="text-muted small">
                {{ count($sampleSummaries) }} sample(s) · {{ count($testRows) }} test(s)
            </span>
        </div>
        <div class="card-body p-0">
            @if($sampleSummaries === [])
                <p class="text-muted text-center py-4 mb-0">No sample/test configuration found for this enquiry.</p>
            @else
                <div class="integrity-master-detail">
                    <aside class="integrity-sample-rail" aria-label="Samples">
                        <div class="integrity-rail-title">Samples</div>
                        <div class="list-group list-group-flush integrity-sample-list">
                            @foreach($sampleSummaries as $sample)
                                <div
                                    class="list-group-item integrity-sample-item {{ $selectedSampleKey === $sample['key'] ? 'is-active' : '' }}"
                                >
                                    <div class="d-flex align-items-start integrity-sample-item-row">
                                        <button
                                            type="button"
                                            class="integrity-sample-select flex-grow-1 text-left"
                                            wire:click="selectSample('{{ $sample['key'] }}')"
                                        >
                                            <div class="font-weight-bold">{{ $sample['label'] }}</div>
                                            <div class="integrity-sample-meta">
                                                {{ $sample['complete'] }}/{{ $sample['total'] }} assigned
                                                @if($sample['subcontracted'] > 0)
                                                    · {{ $sample['subcontracted'] }} subcontracted
                                                @endif
                                            </div>
                                            <div class="integrity-progress">
                                                <div
                                                    class="integrity-progress-bar"
                                                    style="width: {{ $sample['total'] > 0 ? round(($sample['complete'] / $sample['total']) * 100) : 0 }}%"
                                                ></div>
                                            </div>
                                        </button>
                                        <button
                                            type="button"
                                            class="rv-icon-btn rv-icon-btn--solo integrity-sample-info-btn"
                                            title="Click to view sample information"
                                            aria-label="Click to view sample information for {{ $sample['label'] }}"
                                            wire:click.stop="openSampleInfo('{{ $sample['key'] }}', @js($sample['label']))"
                                        >
                                            <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </aside>

                    <section class="integrity-test-pane" aria-label="Tests for selected sample">
                        <div class="integrity-test-toolbar">
                            <div>
                                <div class="font-weight-bold">{{ $activeSample['label'] ?? 'Sample' }}</div>
                                <div class="text-muted small">
                                    {{ count($visibleRows) }} shown
                                    @if($activeSample)
                                        · {{ $activeSample['complete'] }}/{{ $activeSample['total'] }} complete
                                    @endif
                                </div>
                            </div>
                            <div class="integrity-toolbar-controls">
                                <input
                                    type="search"
                                    class="form-control form-control-sm"
                                    placeholder="Search tests…"
                                    wire:model.live.debounce.250ms="testSearch"
                                >
                                <div class="btn-group btn-group-sm" role="group" aria-label="Test filter">
                                    <button type="button" class="btn {{ $testFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="setTestFilter('all')">All</button>
                                    <button type="button" class="btn {{ $testFilter === 'incomplete' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="setTestFilter('incomplete')">Incomplete</button>
                                    <button type="button" class="btn {{ $testFilter === 'subcontracted' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="setTestFilter('subcontracted')">Subcontracted</button>
                                </div>
                            </div>
                        </div>

                        @if($selectedCount > 0)
                            @php
                                $bulkSectionOptions = $this->bulkAnalystSectionOptions;
                                $bulkSubcontractAll = $this->bulkSubcontractAllSelected;
                            @endphp
                            <div class="integrity-bulk-bar" wire:key="integrity-bulk-bar-{{ $selectedCount }}">
                                <div class="integrity-bulk-toolbar">
                                    <div class="integrity-bulk-count">
                                        <strong>{{ $selectedCount }}</strong> selected
                                        <button
                                            type="button"
                                            class="rv-icon-btn rv-icon-btn--solo integrity-bulk-clear-btn"
                                            title="Clear selection"
                                            aria-label="Clear selection"
                                            wire:click="clearRowSelection"
                                        >
                                            <i class="mdi mdi-close" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="integrity-bulk-group integrity-bulk-lab-sections-group">
                                        <span class="integrity-bulk-group-label">Lab sections</span>
                                        <div wire:ignore class="integrity-bulk-select-wrap is-empty">
                                            <select
                                                class="form-control form-control-sm integrity-bulk-section-select"
                                                multiple
                                                data-placeholder="Set lab section(s)…"
                                            >
                                                @foreach($this->labSections as $section)
                                                    <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="integrity-bulk-toolbar-spacer" aria-hidden="true"></div>
                                    <div class="integrity-bulk-group integrity-bulk-more-actions">
                                            <button
                                                type="button"
                                                class="rv-icon-btn rv-icon-btn--solo {{ $bulkSubcontractAll ? 'is-active' : '' }}"
                                                title="{{ $bulkSubcontractAll ? 'Unmark subcontract on selected' : 'Mark subcontract on selected' }}"
                                                aria-label="{{ $bulkSubcontractAll ? 'Unmark subcontract on selected' : 'Mark subcontract on selected' }}"
                                                wire:click="toggleBulkSubcontractedOnSelected"
                                            >
                                                <i class="mdi mdi-truck-delivery-outline" aria-hidden="true"></i>
                                            </button>
                                            <div class="dropdown integrity-bulk-overflow">
                                                <button
                                                    type="button"
                                                    class="rv-icon-btn rv-icon-btn--solo dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                    title="More bulk actions"
                                                >
                                                    <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button
                                                        type="button"
                                                        class="dropdown-item integrity-bulk-remove-sections"
                                                    >
                                                        <i class="mdi mdi-minus-circle-outline mr-2"></i>
                                                        Remove lab section(s) from selected
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item"
                                                        wire:click="copyAssignmentsFromFirstSelected"
                                                        @disabled($selectedCount < 2)
                                                    >
                                                        <i class="mdi mdi-content-copy mr-2"></i>
                                                        Copy from first selected
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <div class="integrity-bulk-analyst-row">
                                    <div class="integrity-bulk-analyst-head">
                                        <span class="integrity-bulk-analyst-label">Analysts</span>
                                        @if($bulkSectionOptions === [])
                                            <span class="text-muted small">Assign lab section(s) on the selected test(s) first.</span>
                                        @endif
                                    </div>
                                    @if($bulkSectionOptions !== [])
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
                                            <div class="integrity-bulk-analyst-group" wire:key="bulk-analyst-group-{{ $sectionId }}">
                                                <span class="integrity-bulk-analyst-group-label">{{ $section['name'] }}</span>
                                                <div class="integrity-bulk-analyst-picks">
                                                    @forelse($sectionAnalysts as $analyst)
                                                        @php
                                                            $analystId = (string) ($analyst['id'] ?? '');
                                                            $isPicked = in_array($analystId, $picked, true);
                                                        @endphp
                                                        <label
                                                            class="badge badge-pill mb-0"
                                                            :class="picked ? 'badge-primary' : 'badge-light'"
                                                            x-data="{ picked: @js($isPicked) }"
                                                            style="cursor: pointer;"
                                                            wire:key="bulk-analyst-{{ $sectionId }}-{{ $analystId }}-{{ $isPicked ? 'selected' : 'available' }}"
                                                            wire:click.prevent.debounce.400ms="toggleBulkAnalyst('{{ $sectionId }}', '{{ $analystId }}')"
                                                            @click="picked = !picked"
                                                        >
                                                            {{ $analyst['name'] }}
                                                        </label>
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

                        <div class="table-responsive integrity-test-table-wrap">
                            <table class="table table-sm mb-0 rv-sample-table integrity-test-table">
                                <thead>
                                    <tr>
                                        <th style="width: 36px;">
                                            <input
                                                type="checkbox"
                                                title="Select all visible"
                                                wire:key="select-all-{{ $selectedCount }}-{{ count($visibleRows) }}"
                                                @checked($selectedCount > 0 && $selectedCount === count($visibleRows) && count($visibleRows) > 0)
                                                wire:click="{{ $selectedCount === count($visibleRows) && count($visibleRows) > 0 ? 'clearRowSelection' : 'selectAllVisibleRows' }}"
                                            >
                                        </th>
                                        <th>Test</th>
                                        <th>Lab section(s)</th>
                                        <th>Analysts</th>
                                        <th class="text-center" style="width: 90px;">Subcontract</th>
                                        <th style="width: 72px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($visibleRows as $row)
                                        @php
                                            $rowKey = (string) ($row['row_key'] ?? '');
                                            $selectedSections = array_map('strval', $row['lab_section_ids'] ?? []);
                                            $isEditing = $editingRowKey === $rowKey;
                                            $isComplete = ! empty($row['is_complete']);
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
                                        <tr wire:key="integrity-row-{{ $rowKey }}" class="{{ $isEditing ? 'is-editing' : '' }} {{ $isComplete ? 'is-complete' : 'is-incomplete' }}">
                                            <td class="align-middle">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $rowKey }}"
                                                    wire:model.live="selectedRowKeys"
                                                >
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold">{{ $row['test_label'] ?? '—' }}</div>
                                                @unless($isComplete)
                                                    <div class="text-warning small">Needs assignment</div>
                                                @endunless
                                            </td>
                                            <td class="align-middle">
                                                @if($selectedSections === [])
                                                    <span class="text-muted small">None</span>
                                                @else
                                                    <div class="integrity-chip-wrap">
                                                        @foreach($selectedSections as $sectionId)
                                                            <span class="integrity-chip integrity-chip--section">
                                                                {{ $sectionNames[$sectionId] ?? 'Section' }}
                                                                <button
                                                                    type="button"
                                                                    class="integrity-chip-remove"
                                                                    title="Remove {{ $sectionNames[$sectionId] ?? 'section' }} from selected tests"
                                                                    aria-label="Remove {{ $sectionNames[$sectionId] ?? 'section' }} from selected tests"
                                                                    wire:click.stop="removeLabSectionChip('{{ $rowKey }}', '{{ $sectionId }}')"
                                                                >
                                                                    <i class="mdi mdi-close" aria-hidden="true"></i>
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($selectedSections === [])
                                                    <span class="text-muted small">—</span>
                                                @elseif(! empty($row['subcontracted']))
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
                                            </td>
                                            <td class="text-center align-middle">
                                                <input
                                                    type="checkbox"
                                                    wire:key="subcontract-{{ $rowKey }}-{{ !empty($row['subcontracted']) ? '1' : '0' }}"
                                                    @checked(!empty($row['subcontracted']))
                                                    wire:click.prevent="toggleSubcontracted('{{ $rowKey }}')"
                                                >
                                            </td>
                                            <td class="align-middle text-right">
                                                <button
                                                    type="button"
                                                    class="rv-icon-btn rv-icon-btn--solo {{ $isEditing ? 'is-active' : '' }}"
                                                    title="{{ $isEditing ? 'Close editor' : 'Edit assignment' }}"
                                                    aria-label="{{ $isEditing ? 'Close editor' : 'Edit assignment' }}"
                                                    wire:click="startEditingRow('{{ $rowKey }}')"
                                                >
                                                    <i class="mdi {{ $isEditing ? 'mdi-close' : 'mdi-pencil-outline' }}" aria-hidden="true"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @if($isEditing)
                                            <tr class="integrity-editor-row" wire:key="integrity-editor-{{ $rowKey }}">
                                                <td colspan="6">
                                                    <div class="integrity-editor">
                                                        <div class="integrity-editor-grid">
                                                            <div>
                                                                <div class="rv-field-label mb-1">Lab section(s)</div>
                                                                <div wire:ignore>
                                                                    <select
                                                                        class="form-control form-control-sm integrity-lab-section-select"
                                                                        multiple
                                                                        data-row-key="{{ $rowKey }}"
                                                                        data-placeholder="Select lab section(s)"
                                                                    >
                                                                        @foreach($this->labSections as $section)
                                                                            <option
                                                                                value="{{ $section['id'] }}"
                                                                                @selected(in_array((string) $section['id'], $selectedSections, true))
                                                                            >{{ $section['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="rv-field-label mb-1">Analyst(s)</div>
                                                                @forelse($selectedSections as $sectionId)
                                                                    @php
                                                                        $analysts = $this->analystsBySection[$sectionId] ?? [];
                                                                        $assigned = array_map('strval', $row['analysts_by_lab_section'][$sectionId] ?? []);
                                                                    @endphp
                                                                    <div class="mb-2">
                                                                        <div class="small font-weight-bold text-muted mb-1">
                                                                            {{ $sectionNames[$sectionId] ?? 'Lab section' }}
                                                                        </div>
                                                                        <div class="d-flex flex-wrap" style="gap: 0.35rem;">
                                                                            @forelse($analysts as $analyst)
                                                                                <label class="badge badge-pill {{ in_array((string) $analyst['id'], $assigned, true) ? 'badge-primary' : 'badge-light' }} mb-0" style="cursor: pointer;">
                                                                                    <input
                                                                                        type="checkbox"
                                                                                        class="d-none"
                                                                                        wire:click.prevent="toggleRowAnalyst('{{ $rowKey }}', '{{ $sectionId }}', '{{ $analyst['id'] }}')"
                                                                                        @checked(in_array((string) $analyst['id'], $assigned, true))
                                                                                    >
                                                                                    {{ $analyst['name'] }}
                                                                                </label>
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
                                                        <div class="mt-2 text-right">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="stopEditingRow">Done</button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted text-center py-4">
                                                No tests match the current filter for this sample.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </div>

    @if($showAcceptConfirmModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background: rgba(15, 23, 42, 0.45);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Accept samples</h5>
                        <button type="button" class="close" wire:click="closeAcceptConfirm" aria-label="Close" @disabled($isAccepting)>
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            Accept these samples?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeAcceptConfirm" wire:loading.attr="disabled" wire:target="confirmAcceptSamples">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="confirmAcceptSamples"
                            wire:loading.attr="disabled"
                            wire:target="confirmAcceptSamples"
                        >
                            <span wire:loading.remove wire:target="confirmAcceptSamples">Accept</span>
                            <span wire:loading wire:target="confirmAcceptSamples">Creating…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="rv-modal-backdrop" x-show="sampleInfoOpen" x-cloak @keydown.escape.window="sampleInfoOpen = false">
        <div class="rv-modal" role="dialog" aria-modal="true" @click.away="sampleInfoOpen = false">
            <div class="rv-modal-header">
                <h4 class="rv-modal-title" x-text="sampleInfoTitle"></h4>
                <button type="button" class="rv-modal-close" @click="sampleInfoOpen = false" aria-label="Close">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="rv-modal-body">
                <dl class="rv-detail-grid mb-0">
                    <div class="rv-detail-row">
                        <dt>Sample Details</dt>
                        <dd x-text="sampleInfoDetails !== '' ? sampleInfoDetails : '—'"></dd>
                    </div>
                    <template x-for="field in sampleInfoFields" :key="field.label">
                        <div class="rv-detail-row">
                            <dt x-text="field.label"></dt>
                            <dd x-text="field.value"></dd>
                        </div>
                    </template>
                </dl>
                <template x-if="sampleInfoTests.length > 0">
                    <div class="mt-3 pt-3 border-top">
                        <div class="rv-field-label mb-2">Tests</div>
                        <div class="rv-test-codes">
                            <template x-for="test in sampleInfoTests" :key="test">
                                <span class="rv-test-code" x-text="test"></span>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="sampleInfoDetails === '' && sampleInfoFields.length === 0 && sampleInfoTests.length === 0">
                    <p class="rv-empty-copy mb-0 mt-3">No sample information was captured on the TRF.</p>
                </template>
            </div>
        </div>
    </div>

    <script>
        (function () {
            if (window.__sampleIntegritySelect2Bound) {
                return;
            }
            window.__sampleIntegritySelect2Bound = true;

            const pendingSectionSync = {};
            let sectionSyncTimer = null;
            let preservedScrollY = null;

            const getWire = () => {
                const root = document.querySelector('.sample-integrity-check-page')?.closest('[wire\\:id]');
                const id = root?.getAttribute('wire:id');

                return id && window.Livewire ? window.Livewire.find(id) : null;
            };

            const bindRowSelect = ($select) => {
                if ($select.attr('data-integrity-bound') === '1') {
                    return;
                }

                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder') || 'Select lab section(s)',
                        allowClear: true,
                        closeOnSelect: false,
                        dropdownParent: window.jQuery(document.body),
                    });
                }

                $select
                    .off('change.integritySections select2:close.integritySections')
                    .on('change.integritySections', function () {
                        pendingSectionSync[String($select.data('row-key'))] = $select.val() || [];
                        clearTimeout(sectionSyncTimer);
                        sectionSyncTimer = setTimeout(() => {
                            const wire = getWire();
                            Object.keys(pendingSectionSync).forEach((rowKey) => {
                                wire?.call('setRowLabSections', rowKey, pendingSectionSync[rowKey]);
                                delete pendingSectionSync[rowKey];
                            });
                        }, 250);
                    })
                    .on('select2:close.integritySections', function () {
                        clearTimeout(sectionSyncTimer);
                        const wire = getWire();
                        const rowKey = String($select.data('row-key'));
                        pendingSectionSync[rowKey] = $select.val() || [];
                        if (wire) {
                            wire.call('setRowLabSections', rowKey, pendingSectionSync[rowKey]);
                            delete pendingSectionSync[rowKey];
                        }
                    });

                $select.attr('data-integrity-bound', '1');
            };

            const bindBulkSelect = () => {
                const $select = window.jQuery('.integrity-bulk-section-select');
                if (!$select.length || $select.attr('data-integrity-bound') === '1') {
                    return;
                }

                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder') || 'Set lab section(s)…',
                        allowClear: false,
                        closeOnSelect: false,
                        dropdownParent: window.jQuery('.integrity-bulk-bar').first().length
                            ? window.jQuery('.integrity-bulk-bar').first()
                            : window.jQuery(document.body),
                    });
                }

                const $wrap = $select.closest('.integrity-bulk-select-wrap');
                const syncBulkSelectLayout = () => {
                    const hasValues = ($select.val() || []).length > 0;
                    $wrap.toggleClass('has-values', hasValues);
                    $wrap.toggleClass('is-empty', !hasValues);
                    const $field = $select.next('.select2-container').find('.select2-search__field');
                    $field.css({
                        width: hasValues ? '0.75em' : '100%',
                        textAlign: hasValues ? 'left' : 'center',
                        margin: 0,
                        height: hasValues ? '22px' : '24px',
                        lineHeight: hasValues ? '22px' : '24px',
                    });
                    if (!hasValues) {
                        $field.attr('placeholder', $select.data('placeholder') || 'Set lab section(s)…');
                    } else {
                        $field.attr('placeholder', '');
                    }
                };

                const select2 = $select.data('select2');
                if (select2?.selection?.resizeSearch) {
                    select2.selection.resizeSearch = function () {
                        const hasValues = ($select.val() || []).length > 0;
                        this.$search.css('width', hasValues ? '0.75em' : '100%');
                    };
                    select2.selection.resizeSearch();
                }

                syncBulkSelectLayout();

                $select
                    .off('change.integrityBulkSections select2:close.integrityBulkSections')
                    .on('change.integrityBulkSections', function () {
                        syncBulkSelectLayout();
                    })
                    .on('select2:close.integrityBulkSections', function () {
                        syncBulkSelectLayout();
                        const wire = getWire();
                        const values = $select.val() || [];
                        if (!wire || values.length === 0) {
                            return;
                        }

                        wire.call('applyBulkLabSections', values, true);
                    });

                $select.attr('data-integrity-bound', '1');
            };

            const initSelects = () => {
                if (!window.jQuery?.fn?.select2) {
                    return;
                }

                // Skip re-init while a Select2 dropdown is open — prevents layout glitches.
                if (window.jQuery('.select2-container--open').length) {
                    return;
                }

                window.jQuery('.integrity-lab-section-select').each(function () {
                    bindRowSelect(window.jQuery(this));
                });
                bindBulkSelect();
            };

            const boot = () => {
                Livewire.on('integrity-sample-info-open', (event) => {
                    const payload = event?.title !== undefined
                        ? event
                        : (Array.isArray(event) ? (event[0] ?? {}) : (event?.detail ?? {}));
                    const root = document.querySelector('.sample-integrity-check-page');
                    if (!root || !window.Alpine) {
                        return;
                    }

                    const state = Alpine.$data(root);
                    state.sampleInfoTitle = payload.title ?? '';
                    state.sampleInfoDetails = payload.details ?? '';
                    state.sampleInfoFields = payload.fields ?? [];
                    state.sampleInfoTests = payload.tests ?? [];
                    state.sampleInfoOpen = true;
                });

                Livewire.on('acceptance-form-completed', (event) => {
                    const payload = Array.isArray(event) ? (event[0] ?? {}) : (event ?? {});
                    const redirectUrl = payload.redirectUrl
                        ?? payload.detail?.redirectUrl
                        ?? (typeof payload[0] === 'string' ? payload[0] : null);
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                        return;
                    }
                    window.location.reload();
                });

                setTimeout(initSelects, 50);

                window.jQuery(document).on('click.integrityBulkRemove', '.integrity-bulk-remove-sections', function () {
                    const $select = window.jQuery('.integrity-bulk-section-select');
                    const values = $select.val() || [];
                    const wire = getWire();

                    if (!wire) {
                        return;
                    }

                    if (values.length === 0) {
                        wire.call('removeBulkLabSections', [], false);

                        return;
                    }

                    wire.call('removeBulkLabSections', values, false).then(() => {
                        $select.val(null).trigger('change');
                    });
                });

                Livewire.hook('commit', ({ succeed }) => {
                    preservedScrollY = window.scrollY;
                    succeed(() => {
                        requestAnimationFrame(() => {
                            if (preservedScrollY !== null) {
                                window.scrollTo(0, preservedScrollY);
                                preservedScrollY = null;
                            }
                            initSelects();
                        });
                    });
                });
            };

            if (window.Livewire) {
                boot();
            } else {
                document.addEventListener('livewire:init', boot);
            }
        })();
    </script>
</div>
