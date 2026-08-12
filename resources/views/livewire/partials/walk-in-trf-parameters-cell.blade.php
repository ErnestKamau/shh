@php
    $isFlat = $rowIndex === null;
    $effectiveRowIndex = $isFlat ? -1 : (int) $rowIndex;
    $wirePrefix = $wirePrefix ?? ($isFlat ? 'formData.parameters' : ('formData.parameters.'.$effectiveRowIndex));
    $picker = $this->walkInParameterPickerState($isFlat ? null : $effectiveRowIndex);
    $selectedParams = $picker['selected'];
    $options = $picker['options'];
    $groups = $picker['groups'] ?? [];
@endphp

{{--
  Tests picker: trigger chips + modal table grouped by Sample type → collapsible Analysis type.
--}}
<div
    class="rft-param-picker rft-tests-picker"
    wire:key="tests-picker-{{ $isFlat ? 'flat' : $effectiveRowIndex }}"
    wire:ignore
    x-data="rftParamPickerUi({
        rowIndex: {{ $effectiveRowIndex }},
        flat: {{ $isFlat ? 'true' : 'false' }},
        options: @js($options),
        selected: @js($selectedParams),
        groups: @js($groups),
        useModal: true,
    })"
    @keydown.escape.window="if (open) closePanel()"
    @walk-in-parameters-loading.window="setAnalysisLoading($event.detail)"
    @walk-in-params-row-reset.window="
        const raw = $event.detail;
        const payload = Array.isArray(raw) ? (raw[0] || {}) : (raw || {});
        if (!flat && payload.rowIndex !== undefined && Number(payload.rowIndex) !== Number(rowIndex)) {
            return;
        }
        if (Array.isArray(payload.options)) {
            options = payload.options.map((value) => {
                if (value && typeof value === 'object') {
                    return { id: String(value.id ?? ''), name: String(value.name ?? value.id ?? '') };
                }
                const token = String(value ?? '');
                return { id: token, name: token };
            }).filter((item) => item.id !== '');
        }
        if (Array.isArray(payload.groups)) {
            groups = payload.groups;
        }
        if (Array.isArray(payload.selected)) {
            setSelected(payload.selected.map((value) => String(value)));
        } else {
            setSelected([]);
        }
        dirty = false;
    "
>
    <button type="button"
        class="rft-param-picker__trigger w-100 text-left"
        @click.stop="toggleOpen()"
        :aria-expanded="open ? 'true' : 'false'">
        <div class="rft-param-picker__chips" aria-live="polite">
            <template x-if="selected.length === 0">
                <span class="text-muted small" x-text="options.length ? 'Choose tests…' : 'Select analysis type first'"></span>
            </template>
            <span class="d-inline-flex flex-wrap">
                <template x-for="chip in visibleChips" :key="chip.id">
                    <span class="rft-param-chip">
                        <span x-text="chip.name" :title="chip.name"></span>
                        <button type="button" class="rft-param-chip__remove" @click.stop="removeChip(chip.id)" title="Remove" aria-label="Remove test">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </span>
                </template>
                <template x-if="hiddenCount > 0">
                    <span class="rft-param-picker__more" x-text="'+' + hiddenCount"></span>
                </template>
            </span>
        </div>
        <i class="mdi mdi-open-in-new text-muted rft-param-picker__caret"></i>
    </button>

    <template x-teleport="body">
        <div class="rft-tests-modal" x-show="open" x-cloak @click.self="closePanel()">
            <div class="rft-tests-modal__dialog" @click.stop role="dialog" aria-modal="true" aria-label="Tests">
                <div class="rft-tests-modal__header">
                    <div>
                        <h5 class="mb-0">Tests</h5>
                        <p class="small text-muted mb-0 mt-1">Grouped by sample type, then analysis type. Expand a group to review its tests.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" @click="closePanel()" aria-label="Close">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="rft-tests-modal__toolbar">
                    <div x-show="isLoading" class="text-primary small" role="status">
                        <span class="spinner-border spinner-border-sm mr-2" aria-hidden="true"></span>
                        Loading tests…
                    </div>
                    <template x-if="!isLoading">
                        <div class="d-flex align-items-center justify-content-between w-100 flex-wrap" style="gap: 8px;">
                            <span class="small text-muted">
                                <span x-text="selected.length"></span>/<span x-text="options.length"></span> selected
                            </span>
                            <span class="rft-tests-modal__toolbar-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click.prevent="selectAll()" :disabled="!options.length">Select all</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click.prevent="clearAll()" :disabled="!selected.length">Clear</button>
                            </span>
                        </div>
                    </template>
                </div>

                <div class="rft-tests-modal__body" x-show="!isLoading">
                    <input
                        type="search"
                        class="form-control form-control-sm mb-3"
                        placeholder="Search tests..."
                        x-model="search"
                        @keydown.stop
                    >

                    <template x-if="!groups.length">
                        <p class="small text-muted mb-0">Choose an analysis type to load tests.</p>
                    </template>

                    <template x-for="sampleBlock in nestedFilteredGroups" :key="sampleBlock.sample_type">
                        <section class="rft-tests-sample-block">
                            <h6 class="rft-tests-sample-block__title" x-text="sampleBlock.sample_type || 'Sample type'"></h6>

                            <template x-for="group in sampleBlock.groups" :key="groupKey(group)">
                                <div class="rft-tests-atable-wrap">
                                    <div class="rft-tests-atable__analysis">
                                        <button
                                            type="button"
                                            class="rft-tests-atable__toggle"
                                            @click.prevent="toggleGroupExpanded(group)"
                                            :aria-expanded="isGroupExpanded(group) ? 'true' : 'false'"
                                        >
                                            <i class="mdi" :class="isGroupExpanded(group) ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                            <span x-text="group.analysis_type || 'Analysis type'"></span>
                                            <span class="rft-tests-atable__count"
                                                x-text="'(' + groupSelectedCount(group) + '/' + ((group.filteredTests || group.tests || []).length) + ')'"
                                            ></span>
                                        </button>

                                        <button
                                            type="button"
                                            class="rft-tests-atable__group-check"
                                            title="Select all tests in this analysis type"
                                            @click.prevent.stop="toggleGroupCheckbox(group)"
                                        >
                                            <span
                                                class="rft-tests-check"
                                                :class="{
                                                    'is-checked': groupSelectionState(group) === 'all',
                                                    'is-partial': groupSelectionState(group) === 'partial'
                                                }"
                                                aria-hidden="true"
                                            ></span>
                                            <span class="small text-muted">Select all</span>
                                        </button>
                                    </div>

                                    <template x-if="isGroupExpanded(group)">
                                        <div>
                                            <table class="rft-tests-atable">
                                                <thead>
                                                    <tr>
                                                        <th class="rft-tests-atable__check"></th>
                                                        <th>Test</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="test in (group.filteredTests || group.tests || [])" :key="test.id">
                                                        <tr
                                                            class="rft-tests-atable__row"
                                                            :class="isSelected(test.id) && 'is-selected'"
                                                            @click.prevent="toggle(test.id)"
                                                        >
                                                            <td class="rft-tests-atable__check">
                                                                <span
                                                                    class="rft-tests-check"
                                                                    :class="isSelected(test.id) && 'is-checked'"
                                                                    aria-hidden="true"
                                                                ></span>
                                                            </td>
                                                            <td>
                                                                <span x-text="test.name" :title="test.name"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </section>
                    </template>

                    <template x-if="groups.length && nestedFilteredGroups.length === 0">
                        <p class="small text-muted mb-0 mt-2">No tests match your search.</p>
                    </template>
                </div>

                <div class="rft-tests-modal__footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-2" @click.prevent="clearAll()" :disabled="!selected.length">Clear</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="closePanel()">Done</button>
                </div>
            </div>
        </div>
    </template>
</div>
@error($wirePrefix)
    <div class="invalid-feedback d-block small font-weight-semibold">{{ $message }}</div>
@enderror
