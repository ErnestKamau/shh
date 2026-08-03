<div class="sample-integrity-check-page">
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
            @if($this->hasLabSamples)
                <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#print-sample-labels-modal">
                    <i class="mdi mdi-barcode"></i> Lab sample labels
                </button>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Lab sample labels unlock after Accept creates the job.">
                    <i class="mdi mdi-barcode"></i> Lab sample labels
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="saveAssignments" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveAssignments">Save assignments</span>
                <span wire:loading wire:target="saveAssignments">Saving…</span>
            </button>
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
                                <button
                                    type="button"
                                    class="list-group-item list-group-item-action integrity-sample-item {{ $selectedSampleKey === $sample['key'] ? 'is-active' : '' }}"
                                    wire:click="selectSample('{{ $sample['key'] }}')"
                                >
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="font-weight-bold">{{ $sample['label'] }}</span>
                                        @if($sample['incomplete'] === 0)
                                            <span class="badge badge-success">Done</span>
                                        @else
                                            <span class="badge badge-warning">{{ $sample['incomplete'] }} left</span>
                                        @endif
                                    </div>
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
                            <div class="integrity-bulk-bar" wire:key="integrity-bulk-bar-{{ $selectedCount }}">
                                <div class="integrity-bulk-count">
                                    <strong>{{ $selectedCount }}</strong> selected
                                    <button type="button" class="btn btn-link btn-sm p-0 ml-2" wire:click="clearRowSelection">Clear</button>
                                </div>
                                <div class="integrity-bulk-actions">
                                    <div wire:ignore class="integrity-bulk-select-wrap">
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
                                    <button type="button" class="btn btn-sm btn-primary" id="integrity-bulk-apply-sections">
                                        Apply sections
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="applyBulkSubcontracted(true)">
                                        Mark subcontracted
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="applyBulkSubcontracted(false)">
                                        Unmark subcontracted
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="copyAssignmentsFromFirstSelected">
                                        Copy from first selected
                                    </button>
                                </div>
                                <div class="integrity-bulk-analyst-row">
                                    <span class="integrity-bulk-analyst-label">Analysts</span>
                                    @php
                                        $bulkSectionOptions = $this->bulkAnalystSectionOptions;
                                    @endphp
                                    @if($bulkSectionOptions === [])
                                        <span class="text-muted small">Assign lab section(s) on the selected test(s) first.</span>
                                    @else
                                        <div class="integrity-bulk-analyst-sections">
                                            @foreach($bulkSectionOptions as $section)
                                                @php
                                                    $sectionId = (string) ($section['id'] ?? '');
                                                    $sectionSelected = in_array($sectionId, $bulkAnalystLabSectionIds, true);
                                                @endphp
                                                <label class="badge badge-pill {{ $sectionSelected ? 'badge-primary' : 'badge-light' }} mb-0" style="cursor: pointer;">
                                                    <input
                                                        type="checkbox"
                                                        class="d-none"
                                                        value="{{ $sectionId }}"
                                                        wire:model.live="bulkAnalystLabSectionIds"
                                                    >
                                                    {{ $section['name'] }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @foreach($bulkAnalystLabSectionIds as $sectionId)
                                            @php
                                                $sectionId = (string) $sectionId;
                                                $sectionName = $sectionNames[$sectionId] ?? 'Lab section';
                                                $sectionAnalysts = $this->analystsBySection[$sectionId] ?? [];
                                                $picked = array_values(array_map(
                                                    'strval',
                                                    is_array($bulkAnalystIdsBySection[$sectionId] ?? null)
                                                        ? $bulkAnalystIdsBySection[$sectionId]
                                                        : []
                                                ));
                                            @endphp
                                            <div class="integrity-bulk-analyst-group" wire:key="bulk-analyst-group-{{ $sectionId }}">
                                                <div class="integrity-bulk-analyst-group-label">{{ $sectionName }}</div>
                                                <div class="integrity-bulk-analyst-picks">
                                                    @forelse($sectionAnalysts as $analyst)
                                                        @php
                                                            $analystId = (string) ($analyst['id'] ?? '');
                                                            $isPicked = in_array($analystId, $picked, true);
                                                        @endphp
                                                        <label class="badge badge-pill {{ $isPicked ? 'badge-primary' : 'badge-light' }} mb-0" style="cursor: pointer;">
                                                            <input
                                                                type="checkbox"
                                                                class="d-none"
                                                                wire:click.prevent="toggleBulkAnalyst('{{ $sectionId }}', '{{ $analystId }}')"
                                                                @checked($isPicked)
                                                            >
                                                            {{ $analyst['name'] }}
                                                        </label>
                                                    @empty
                                                        <span class="text-muted small">No analysts for this section.</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($bulkAnalystLabSectionIds !== [])
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                wire:click="applyBulkAnalysts"
                                                @disabled(! $this->hasBulkAnalystPicks)
                                            >
                                                Apply analysts
                                            </button>
                                        @endif
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
                                            $analystCount = (int) ($row['analyst_count'] ?? 0);
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
                                                            <span class="integrity-chip">{{ $sectionNames[$sectionId] ?? 'Section' }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($selectedSections === [])
                                                    <span class="text-muted small">—</span>
                                                @elseif(! empty($row['subcontracted']))
                                                    <span class="text-muted small">N/A (subcontracted)</span>
                                                @else
                                                    <span class="badge badge-light">{{ $analystCount }} analyst(s)</span>
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
                                                    class="btn btn-sm {{ $isEditing ? 'btn-secondary' : 'btn-outline-primary' }}"
                                                    wire:click="startEditingRow('{{ $rowKey }}')"
                                                >
                                                    {{ $isEditing ? 'Close' : 'Edit' }}
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
                                                                <div class="rv-field-label mb-1">Analyst(s) per section</div>
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
                            Accept these samples and create the job order and sample numbers now?
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
                            <span wire:loading.remove wire:target="confirmAcceptSamples">Accept &amp; create JO</span>
                            <span wire:loading wire:target="confirmAcceptSamples">Creating…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="print-sample-labels-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-printer mr-1"></i> Print Labels
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-muted">Choose which label to generate for the samples listed. Each option opens in a new tab.</p>
                    <div class="list-group">
                        <a href="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'collection']) }}"
                            target="_blank"
                            rel="noopener"
                            class="list-group-item list-group-item-action"
                            data-dismiss="modal">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-tag-outline mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Sample Collection Label</strong>
                                    <small class="text-muted">Collection details with TRF barcode</small>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id, 'type' => 'registration']) }}"
                            target="_blank"
                            rel="noopener"
                            class="list-group-item list-group-item-action"
                            data-dismiss="modal">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-barcode mr-2 text-primary" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block">Registration Label with barcode</strong>
                                    <small class="text-muted">Job / sample registration label for scanning</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
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
            };

            const bindBulkSelect = () => {
                const $select = window.jQuery('.integrity-bulk-section-select');
                if (!$select.length) {
                    return;
                }

                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        width: '240px',
                        placeholder: $select.data('placeholder') || 'Set lab section(s)…',
                        allowClear: true,
                        closeOnSelect: false,
                        dropdownParent: window.jQuery('.integrity-bulk-bar').first().length
                            ? window.jQuery('.integrity-bulk-bar').first()
                            : window.jQuery(document.body),
                    });
                }

                window.jQuery(document)
                    .off('click.integrityBulk', '#integrity-bulk-apply-sections')
                    .on('click.integrityBulk', '#integrity-bulk-apply-sections', function (event) {
                        event.preventDefault();
                        const wire = getWire();
                        const $bulk = window.jQuery('.integrity-bulk-section-select');
                        if (!wire || !$bulk.length) {
                            return;
                        }

                        const values = $bulk.val() || [];
                        if ($bulk.hasClass('select2-hidden-accessible')) {
                            try {
                                $bulk.select2('close');
                            } catch (e) {
                                // ignore
                            }
                        }

                        wire.call('applyBulkLabSections', values);
                    });
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
