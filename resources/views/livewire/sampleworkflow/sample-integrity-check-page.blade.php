<div
    class="sample-integrity-check-page ls-ui-kit"
>
    @if($flashMessage !== '')
        <div class="alert alert-{{ $flashMessageType === 'error' ? 'danger' : ($flashMessageType === 'warning' ? 'warning' : 'info') }} alert-dismissible fade show" role="alert">
            {{ $flashMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @include('livewire.sampleworkflow.integrity-check.partials.console-header', [
        'pageHeader' => $this->pageHeader,
        'canAccept' => $this->canAccept,
        'collectionLabelUrl' => $this->collectionLabelUrl,
        'registrationLabelUrl' => $this->registrationLabelUrl,
    ])

    @php
        $sampleSummaries = $this->sampleSummaries;
        $visibleRows = $this->visibleTestRows;
        $sectionNames = $this->labSectionNames;
        $activeSampleDossier = $this->activeSampleDossier;
        $analystNamesById = [];
        foreach ($this->analystsBySection as $sectionAnalysts) {
            foreach ($sectionAnalysts as $analyst) {
                $analystNamesById[(string) $analyst['id']] = (string) $analyst['name'];
            }
        }
        $selectedCount = count($selectedRowKeys);
        $activeSample = collect($sampleSummaries)->firstWhere('key', $selectedSampleKey);
    @endphp

    @if($sampleSummaries === [])
        <div class="integrity-panel">
            <div class="integrity-panel__body p-4">
                <p class="text-muted text-center mb-0">No sample/test configuration found for this enquiry.</p>
            </div>
        </div>
    @else
        <div class="row integrity-workspace g-2 g-xl-3">
            <div class="col-12 col-xl-auto integrity-sample-rail-col">
                <aside class="integrity-panel integrity-panel--sample-rail" aria-label="Samples">
                    <div class="integrity-panel__head integrity-panel__head--compact">
                        <h3 class="integrity-panel__title">
                            <i class="mdi mdi-test-tube"></i>
                            <span class="integrity-panel__title-text">Samples</span>
                        </h3>
                    </div>
                    <div class="integrity-panel__body">
                        <div class="integrity-sample-list">
                            @foreach($sampleSummaries as $sample)
                                @php
                                    $sampleIndex = (int) ($sample['sample_index'] ?? 0);
                                    $sampleLabel = trim((string) ($sample['label'] ?? ''));
                                    if ($sampleLabel === '') {
                                        $sampleLabel = trim((string) ($sample['sample_description'] ?? ''));
                                    }
                                    if ($sampleLabel === '') {
                                        $sampleLabel = 'Sample '.$sampleIndex;
                                    }
                                    $progressPct = $sample['total'] > 0
                                        ? round(($sample['complete'] / $sample['total']) * 100)
                                        : 0;
                                @endphp
                                <div class="integrity-sample-tile">
                                    <button type="button"
                                        class="integrity-sample-tab {{ $selectedSampleKey === $sample['key'] ? 'is-active' : '' }}"
                                        wire:click="selectSample('{{ $sample['key'] }}')"
                                        title="{{ $sampleLabel }}">
                                        <div class="integrity-sample-tab__compact">
                                            <span class="integrity-sample-tab__description">{{ $sampleLabel }}</span>
                                            <span class="integrity-sample-tab__counter">{{ $sample['complete'] }}/{{ $sample['total'] }}</span>
                                        </div>
                                        @if(!empty($sample['condition_not_acceptable']))
                                            <div class="integrity-sample-condition-flag mt-1" title="Condition not acceptable">
                                                <i class="mdi mdi-flag" aria-hidden="true"></i>
                                            </div>
                                        @endif
                                        <div class="integrity-progress" aria-hidden="true">
                                            <div class="integrity-progress-bar" style="width: {{ $progressPct }}%"></div>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>

            <div class="col-12 col-xl-4 integrity-dossier-col">
                <div class="integrity-panel" x-data="{ open: true }">
                    <div class="integrity-panel__head d-none d-xl-flex">
                        <h3 class="integrity-panel__title">
                            <i class="mdi mdi-file-document-outline"></i>
                            Sample dossier
                        </h3>
                    </div>
                    <button type="button"
                        class="integrity-dossier-collapse-toggle d-xl-none"
                        @click="open = !open"
                        :aria-expanded="open">
                        <span>Sample dossier</span>
                        <i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                    </button>
                    <div class="integrity-panel__body" x-show="open" x-cloak>
                        @include('livewire.sampleworkflow.integrity-check.partials.sample-dossier', [
                            'dossier' => $activeSampleDossier,
                        ])
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl integrity-assignment-col">
                @include('livewire.sampleworkflow.integrity-check.partials.assignment-pane', [
                    'visibleRows' => $visibleRows,
                    'sectionNames' => $sectionNames,
                    'selectedCount' => $selectedCount,
                    'activeSampleDossier' => $activeSampleDossier,
                    'activeSample' => $activeSample,
                    'sampleSummaries' => $sampleSummaries,
                    'testRows' => $testRows,
                    'analystNamesById' => $analystNamesById,
                    'testFilter' => $testFilter,
                    'editingRowKey' => $editingRowKey,
                    'bulkAnalystIdsBySection' => $bulkAnalystIdsBySection,
                ])
            </div>
        </div>
    @endif


    @include('livewire.sampleworkflow.integrity-check.partials.worksheet-issue-modal', [
        'showWorksheetModal' => $showWorksheetModal,
        'worksheetSectionOptions' => $this->worksheetSectionOptions,
        'selectedWorksheetSectionIds' => $selectedWorksheetSectionIds,
    ])

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
                        @if($acceptError !== '')
                            <div class="alert alert-danger py-2 px-3 small" role="alert">{{ $acceptError }}</div>
                        @endif
                        <p class="mb-3">
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
                Livewire.on('open-integrity-worksheet-pdf', (event) => {
                    const payload = Array.isArray(event) ? (event[0] ?? {}) : (event ?? {});
                    const url = payload.url ?? payload.detail?.url ?? null;
                    if (url) {
                        window.open(url, '_blank');
                    }
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

    <style>
        .sample-integrity-check-page .integrity-actions-dropdown {
            position: relative;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 34px;
            padding: 0.3rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__toggle::after {
            display: none;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__toggle .mdi {
            font-size: 1.05rem;
            line-height: 1;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__menu {
            min-width: 230px;
            margin-top: 0.35rem;
            padding: 0.35rem 0;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            z-index: 1050;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__menu .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.95rem;
            font-size: 0.875rem;
            color: #0f172a;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__menu .dropdown-item:hover,
        .sample-integrity-check-page .integrity-actions-dropdown__menu .dropdown-item:focus {
            background: #f8fafc;
            color: var(--color-primary, #6D0A0E);
        }

        .sample-integrity-check-page .integrity-actions-dropdown__menu .dropdown-item .mdi {
            width: 1.1rem;
            text-align: center;
        }

        .sample-integrity-check-page .integrity-actions-dropdown__menu .dropdown-divider {
            margin: 0.3rem 0;
        }

        .sample-integrity-check-page .integrity-sample-customer-id {
            margin-top: 0.1rem;
            font-size: 0.72rem;
            color: #64748b;
        }

        .sample-integrity-check-page .integrity-sample-condition-flag {
            margin-top: 0.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.1rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #9f1239;
            background: #fff1f2;
            border: 1px solid #fecdd3;
        }

        .sample-integrity-check-page .integrity-sample-condition-flag .mdi {
            font-size: 0.85rem;
            line-height: 1;
        }
    </style>
</div>
