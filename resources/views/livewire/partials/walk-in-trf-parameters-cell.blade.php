@php
    $pickerContext = $pickerContext ?? 'walkin';
    $isFlat = $rowIndex === null || $pickerContext === 'edit';
    $effectiveRowIndex = $isFlat ? -1 : (int) $rowIndex;
    $syncMethod = $pickerContext === 'edit' ? 'setEditingRowParameters' : 'setWalkInParameters';
    $stateMethod = $pickerContext === 'edit' ? 'editingRowParameterPickerState' : 'walkInParameterPickerState';

    if ($pickerContext === 'edit') {
        $picker = $this->editingRowParameterPickerState();
        $fieldId = $fieldId ?? ('edit-row-parameters-'.($editingRowIndex ?? 0));
        $wirePrefix = 'editingRowFields.parameters';
    } else {
        $wirePrefix = $wirePrefix ?? ($isFlat ? 'formData.parameters' : ('formData.parameters.'.$effectiveRowIndex));
        $fieldId = $fieldId ?? ('parameters_'.$effectiveRowIndex);
        $picker = $this->walkInParameterPickerState($isFlat ? null : $effectiveRowIndex);
    }

    $selectedParams = $picker['selected'];
    $options = $picker['options'];
    $groups = $picker['groups'] ?? [];
    $selectOptions = $pickerContext === 'edit'
        ? []
        : $this->walkInParameterSelectOptions($isFlat ? null : $effectiveRowIndex);
    $selectedIds = array_values(array_map('strval', $selectedParams));
    $paramNameById = [];
    foreach ($groups as $group) {
        foreach ($group['tests'] ?? [] as $test) {
            $paramNameById[(string) ($test['id'] ?? '')] = (string) ($test['name'] ?? $test['id'] ?? '');
        }
    }
    foreach ($options as $option) {
        $paramNameById[(string) ($option['id'] ?? '')] = (string) ($option['name'] ?? $option['id'] ?? '');
    }
    $paramNames = array_values(array_map(
        static fn (string $id): string => $paramNameById[$id] ?? $id,
        $selectedIds,
    ));
    $sampleTypeSelected = filled(data_get($formData ?? $this->formData ?? [], 'sample_type_id.'.($isFlat ? 0 : $effectiveRowIndex))
        ?? data_get($formData ?? $this->formData ?? [], 'sample_type.'.($isFlat ? 0 : $effectiveRowIndex)));
@endphp

{{-- Tests: LS multi-columns Select2 display + quotation-style modal for bulk pick --}}
<div
    class="rft-param-picker rft-tests-picker"
    wire:ignore
    wire:key="tests-picker-{{ $isFlat ? 'flat' : $effectiveRowIndex }}"
    x-data="rftParamPickerUi({
        rowIndex: {{ $effectiveRowIndex }},
        flat: {{ $isFlat ? 'true' : 'false' }},
        fieldId: @js('field_'.$fieldId),
        syncMethod: @js($syncMethod),
        stateMethod: @js($stateMethod),
        livewireComponentId: @js($this->getId()),
        options: @js($options),
        selected: @js($selectedParams),
        paramNames: @js($paramNames),
        groups: @js($groups),
        useModal: true,
    })"
    @keydown.escape.window="if (open) cancelPanel()"
    @walk-in-parameters-loading.window="setAnalysisLoading($event.detail)"
    @walk-in-params-row-reset.window="applyParamsRowReset($event.detail)"
>
    <div class="rft-tests-picker__shell">
        <div
            class="rft-trf-tests-params ls-quote-params"
            :class="{ 'ls-quote-params--empty': persistedSelected.length === 0 }"
            role="button"
            tabindex="0"
            @click="toggleOpen()"
            @keydown.enter.prevent="toggleOpen()"
            @keydown.space.prevent="toggleOpen()"
            :aria-label="persistedSelected.length ? (persistedSelected.length + ' tests selected') : 'Choose tests'"
        >
            <div class="ls-quote-params__toolbar">
                <span class="ls-quote-params__title">
                    <span x-text="sampleTypeHeading"></span>
                    <span class="ls-quote-params__count" x-show="persistedSelected.length > 0" x-text="persistedSelected.length" x-cloak></span>
                </span>
                <button
                    type="button"
                    class="ls-btn ls-quote-params__edit rft-trf-tests-params__edit"
                    @click.stop="toggleOpen()"
                    :title="persistedSelected.length ? 'Edit tests' : 'Choose tests'"
                >
                    <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                </button>
            </div>
            <div class="ls-select2-view ls-quote-params__chips">
                <template x-if="persistedSelected.length === 0">
                    <span class="text-muted small">Select sample type, then choose tests…</span>
                </template>
                <template x-for="chip in visibleChips" :key="chip.id">
                    <span class="ls-select2-view__chip ls-quote-param-chip ls-quote-param-chip--acc" :title="chip.name">
                        <span x-text="chip.name"></span>
                        <i class="mdi mdi-check-decagram" aria-hidden="true"></i>
                    </span>
                </template>
            </div>
        </div>
        {{-- Hidden select retained for legacy row sync helpers --}}
        <select
            id="field_{{ $fieldId }}"
            class="d-none"
            multiple
            aria-hidden="true"
            tabindex="-1"
            data-wire-field="{{ $wirePrefix }}"
            data-sync-method="setWalkInParameters"
            data-sync-key="{{ $effectiveRowIndex }}"
        ></select>
    </div>

    <template x-teleport="body">
        <div
            class="rft-trf-params-modal"
            x-show="open"
            x-cloak
            style="display: none;"
            :data-rft-params-row="rowIndex"
            @click.self="cancelPanel()"
            @keydown.escape.window="if (open) cancelPanel()"
        >
            <div class="rft-trf-params-modal__dialog" @click.stop role="dialog" aria-modal="true" aria-label="Choose tests">
                <div class="rft-trf-params-modal__header">
                    <h5 class="rft-trf-params-modal__title">
                        <i class="mdi mdi-flask-outline" aria-hidden="true"></i>
                        Choose tests
                    </h5>
                    <button type="button" class="rft-trf-params-modal__close" @click="cancelPanel()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="rft-trf-params-modal__body">
                    <div class="ls-quote-params-shell">
                        <div class="ls-quote-params-hero">
                            <div class="ls-quote-params-hero__copy">
                                <p class="ls-quote-params-hero__title">Choose tests for this sample</p>
                                <p class="ls-quote-params-hero__caption">
                                    Tests are grouped by analysis type and filtered by the sample type you chose. Select tests, then press Save to apply.
                                </p>
                            </div>
                            <div class="ls-quote-params-legend" aria-hidden="true">
                                <span class="ls-quote-params-legend__chip ls-quote-params-legend__chip--acc">Lab section</span>
                                <span class="ls-quote-params-legend__chip ls-quote-params-legend__chip--sub">Method</span>
                            </div>
                        </div>

                        <div class="ls-quote-params-toolbar">
                            <label class="ls-quote-params-switch">
                                <input
                                    type="checkbox"
                                    class="ls-quote-params-switch__input"
                                    :checked="allSelected"
                                    @change.prevent="toggleSelectAllSwitch()"
                                    title="Select all visible tests"
                                >
                                <span class="ls-quote-params-switch__ui" aria-hidden="true"></span>
                                <span class="ls-quote-params-switch__label">Select all</span>
                            </label>
                            <button
                                type="button"
                                class="ls-quote-params-switch ls-quote-params-switch--btn"
                                @click.prevent="toolbarClearSelection()"
                                :disabled="hasActiveViewFilter ? !visibleSelectedCount : !selected.length"
                                :title="hasActiveViewFilter ? 'Clear selection for visible filtered tests' : 'Clear all selected tests'"
                            >
                                <span class="ls-quote-params-switch__label" x-text="hasActiveViewFilter ? 'Clear visible' : 'Clear all'"></span>
                            </button>
                            <span class="rft-trf-params-modal__count small text-muted ml-auto">
                                <span x-show="isLoading" class="text-primary" role="status">
                                    <span class="spinner-border spinner-border-sm mr-1" aria-hidden="true"></span>
                                    Loading…
                                </span>
                                <span x-show="!isLoading">
                                    <span x-text="visibleSelectedCount"></span>/<span x-text="visibleTestCount"></span> selected
                                    <span
                                        class="rft-trf-params-modal__count-total"
                                        x-show="hasActiveViewFilter && options.length !== visibleTestCount"
                                        x-cloak
                                        x-text="' · ' + options.length + ' total'"
                                    ></span>
                                </span>
                            </span>
                        </div>

                        <div class="rft-trf-params-modal__search-wrap">
                            @include('livewire.partials.walk-in-trf-parameters-modal-search')
                        </div>

                        <div class="ls-quote-params-stage">
                            <template x-if="!groups.length && isLoading">
                                <div class="ls-quote-params-empty ls-quote-params-empty--muted">
                                    <div class="ls-quote-params-empty__art" aria-hidden="true">
                                        <span class="spinner-border text-primary" role="status"></span>
                                    </div>
                                    <h4 class="ls-quote-params-empty__title">Loading tests…</h4>
                                    <p class="ls-quote-params-empty__text mb-0">Fetching the catalog for this sample type.</p>
                                </div>
                            </template>

                            <template x-if="!groups.length && !isLoading">
                                <div class="ls-quote-params-empty">
                                    <div class="ls-quote-params-empty__art" aria-hidden="true">
                                        <svg viewBox="0 0 160 160" width="108" height="108" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="80" cy="80" r="72" fill="#fff1f2"/>
                                            <circle cx="80" cy="80" r="56" fill="#ffe4e6"/>
                                            <path d="M62 28h36v14l22 54a28 28 0 1 1-54 0l22-54V28z" fill="#fda4af" stroke="#be123c" stroke-width="4" stroke-linejoin="round"/>
                                            <path d="M58 96c8 14 36 14 44 0 2 18-10 30-22 30S56 114 58 96z" fill="#f43f5e"/>
                                            <circle cx="72" cy="108" r="5" fill="#fecdd3"/>
                                            <circle cx="90" cy="102" r="3.5" fill="#fecdd3"/>
                                            <circle cx="82" cy="116" r="2.5" fill="#fecdd3"/>
                                            <rect x="58" y="22" width="44" height="10" rx="4" fill="#9f1239"/>
                                            <path d="M118 46c8-2 14 8 8 14" fill="none" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
                                            <circle cx="128" cy="40" r="5" fill="#fbbf24"/>
                                            <path d="M36 58c-6 4-4 14 4 12" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round"/>
                                            <circle cx="34" cy="52" r="4" fill="#0ea5e9"/>
                                        </svg>
                                    </div>
                                    <h4 class="ls-quote-params-empty__title">No tests available</h4>
                                    <p class="ls-quote-params-empty__text mb-0">Select a sample type first, then open this dialog to pick tests.</p>
                                </div>
                            </template>

                            <template x-if="groups.length && flatTableRows.length === 0 && !isLoading">
                                <div class="ls-quote-params-empty">
                                    <div class="ls-quote-params-empty__art" aria-hidden="true">
                                        <svg viewBox="0 0 160 160" width="108" height="108" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="80" cy="80" r="72" fill="#fff1f2"/>
                                            <circle cx="80" cy="80" r="56" fill="#ffe4e6"/>
                                            <path d="M62 28h36v14l22 54a28 28 0 1 1-54 0l22-54V28z" fill="#fda4af" stroke="#be123c" stroke-width="4" stroke-linejoin="round"/>
                                            <path d="M58 96c8 14 36 14 44 0 2 18-10 30-22 30S56 114 58 96z" fill="#f43f5e"/>
                                            <circle cx="72" cy="108" r="5" fill="#fecdd3"/>
                                            <circle cx="90" cy="102" r="3.5" fill="#fecdd3"/>
                                            <circle cx="82" cy="116" r="2.5" fill="#fecdd3"/>
                                            <rect x="58" y="22" width="44" height="10" rx="4" fill="#9f1239"/>
                                            <path d="M118 46c8-2 14 8 8 14" fill="none" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
                                            <circle cx="128" cy="40" r="5" fill="#fbbf24"/>
                                            <path d="M36 58c-6 4-4 14 4 12" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round"/>
                                            <circle cx="34" cy="52" r="4" fill="#0ea5e9"/>
                                        </svg>
                                    </div>
                                    <h4 class="ls-quote-params-empty__title">No tests match</h4>
                                    <p class="ls-quote-params-empty__text mb-0">Try clearing search or filters to see more tests.</p>
                                </div>
                            </template>

                            <div class="ls-quote-analyte-table rft-trf-params-table-wrap" x-show="groups.length > 0" x-cloak>
                                <div
                                    class="rft-trf-params-stage-loading"
                                    x-show="isLoading"
                                    x-cloak
                                    role="status"
                                    aria-live="polite"
                                    aria-label="Loading tests"
                                >
                                    <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                    <span class="rft-trf-params-stage-loading__label">Updating tests…</span>
                                </div>
                                <table class="ls-quote-analyte-grid rft-trf-analyte-grid">
                                    <colgroup>
                                        <col class="ls-quote-analyte-col--select">
                                        <col class="ls-quote-analyte-col--name">
                                        <col class="ls-quote-analyte-col--unit">
                                        <col class="ls-quote-analyte-col--lod">
                                        <col class="ls-quote-analyte-col--loq">
                                        <col class="ls-quote-analyte-col--mu">
                                        <col class="ls-quote-analyte-col--tat">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th scope="col" aria-label="Select"></th>
                                            <th scope="col">Analyte</th>
                                            <th scope="col" class="text-center">Unit</th>
                                            <th scope="col" class="text-center">LOD</th>
                                            <th scope="col" class="text-center">LOQ</th>
                                            <th scope="col" class="text-center">MU%</th>
                                            <th scope="col" class="text-center" title="Turnaround time (days)">TAT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="rft-trf-analyte-tbody">
                                        @include('livewire.partials.walk-in-trf-parameters-modal-table-body')
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rft-trf-params-modal__footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="cancelPanel()">Close</button>
                    <button type="button" class="btn btn-rft-params-primary btn-sm" @click="savePanel()">
                        <i class="mdi mdi-content-save-outline" aria-hidden="true"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@php
    $analysisTypeErrorKeys = $isFlat
        ? ['formData.analysis_type_id', 'formData.analysis_type', 'formData.analysis_types', 'formData.parameters', 'formData.parameter']
        : [
            'formData.analysis_type_id.'.$effectiveRowIndex,
            'formData.analysis_type.'.$effectiveRowIndex,
            'formData.analysis_types.'.$effectiveRowIndex,
            'formData.parameters.'.$effectiveRowIndex,
            'formData.parameter.'.$effectiveRowIndex,
        ];
@endphp
@foreach($analysisTypeErrorKeys as $errorKey)
    @error($errorKey)
        <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
    @enderror
@endforeach
