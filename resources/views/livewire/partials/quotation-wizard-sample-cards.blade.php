{{--
    Create-enquiry wizard sample cards — same RFT columns/components as walk-in TRF fill.
    Uses test-request-field-render, ls-field-rich-text-cell, walk-in-trf-qty-unit-cell.
--}}
@php
    $rowCount = max(1, (int) ($rowCount ?? 1));
    $gridRows = $this->wizardSampleCardGridRows($fields ?? []);
    $progressFieldNames = collect($fields ?? [])
        ->map(static fn (array $field): string => (string) ($field['name'] ?? ''))
        ->filter(static fn (string $name): bool => $name !== '' && $name !== 'sample_quantity_unit')
        ->values()
        ->all();
@endphp

<div
    class="rft-sample-cards ceq-wizard-sample-cards trf-ls-theme"
    x-data="{
        openRow: 0,
        typeId: @js($typeId),
        rowCount: {{ $rowCount }},
        fieldNames: @js($progressFieldNames),
        progress: {},
        fieldHasValue(value) {
            if (typeof value === 'boolean') {
                return value;
            }
            if (Array.isArray(value)) {
                if (value.length === 0) {
                    return false;
                }
                return value.some((item) => this.fieldHasValue(item));
            }
            if (value && typeof value === 'object') {
                return Object.values(value).some((item) => item === true || item === 1 || item === '1' || item === 'true');
            }
            if (typeof value === 'string') {
                const stripped = value.replace(/<[^>]*>/g, '').trim();
                return stripped !== '';
            }
            return value !== null && value !== undefined && value !== '';
        },
        computeRowProgress(rowIndex) {
            const rows = this.$wire.sectionRowFieldValuesByType?.[this.typeId] ?? {};
            const values = rows[rowIndex] ?? {};
            if (this.fieldNames.length === 0) {
                return 0;
            }
            let filled = 0;
            for (const name of this.fieldNames) {
                if (this.fieldHasValue(values[name])) {
                    filled++;
                }
            }
            return Math.round((filled / this.fieldNames.length) * 100);
        },
        refreshAllProgress() {
            const next = {};
            for (let rowIndex = 0; rowIndex < this.rowCount; rowIndex++) {
                next[rowIndex] = this.computeRowProgress(rowIndex);
            }
            this.progress = next;
        },
        setOpenRow(row) {
            const previous = this.openRow;
            this.openRow = this.openRow === row ? -1 : row;
            if (previous >= 0 && previous !== this.openRow) {
                this.$dispatch('rft-sample-card-hidden', { row: previous });
            }
            if (this.openRow >= 0) {
                this.$nextTick(() => {
                    this.$dispatch('rft-sample-card-shown', { row: this.openRow });
                    this.refreshAllProgress();
                });
            } else {
                this.$nextTick(() => this.refreshAllProgress());
            }
        }
    }"
    x-init="
        refreshAllProgress();
        if ($wire && typeof $wire.$watch === 'function') {
            $wire.$watch('sectionRowFieldValuesByType', () => refreshAllProgress());
        }
        $el.addEventListener('input', () => $nextTick(() => refreshAllProgress()), true);
        $el.addEventListener('change', () => $nextTick(() => refreshAllProgress()), true);
        window.addEventListener('ceq-sample-progress-refresh', () => $nextTick(() => refreshAllProgress()));
        $nextTick(() => {
            $dispatch('rft-sample-card-shown', { row: 0 });
            if (typeof window.initWalkInLsSelect2 === 'function') {
                window.initWalkInLsSelect2($el);
            }
        });
    "
    x-on:ceq-sample-rows-cloned.window="if (openRow >= 0) { $nextTick(() => $dispatch('rft-sample-card-shown', { row: openRow })); } $nextTick(() => refreshAllProgress());"
>
    @if($rowCount > 1)
        <div class="d-flex justify-content-end mb-2">
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary ceq-clone-first-sample-btn"
                title="Copy Sample 1 details to all other samples"
                aria-label="Copy Sample 1 details to all other samples"
                wire:click="cloneFirstSampleRowToOthers('{{ $typeId }}')"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="cloneFirstSampleRowToOthers('{{ $typeId }}')">
                    <i class="mdi mdi-content-copy"></i>
                    Copy Sample 1 to all
                </span>
                <span wire:loading wire:target="cloneFirstSampleRowToOthers('{{ $typeId }}')">
                    <span class="spinner-border spinner-border-sm"></span>
                    Copying...
                </span>
            </button>
        </div>
    @endif

    @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
        @php
            $rowValues = $sectionRowFieldValuesByType[$typeId][$rowIndex] ?? [];
            $wireBase = 'sectionRowFieldValuesByType.'.$typeId.'.'.$rowIndex;
        @endphp

        <article
            class="rft-sample-row-card"
            data-trf-sample-index="{{ $rowIndex }}"
            wire:key="ceq-row-card-{{ $typeId }}-{{ $trfIndex }}-{{ $rowIndex }}"
            :class="{ 'is-open': openRow === {{ $rowIndex }} }"
        >
            <div class="rft-sample-row-card__header">
                <button
                    type="button"
                    class="rft-sample-row-card__toggle-main border-0 bg-transparent p-0 text-left flex-grow-1 min-width-0"
                    @click="setOpenRow({{ $rowIndex }})"
                >
                    <h6 class="rft-sample-row-card__title mb-1">
                        Sample {{ $rowIndex + 1 }}
                    </h6>
                    <div class="ceq-sample-progress" x-show="openRow !== {{ $rowIndex }}" x-cloak>
                        <div class="progress ceq-sample-progress__bar">
                            <div
                                class="progress-bar"
                                :class="(progress[{{ $rowIndex }}] ?? 0) >= 100 ? 'bg-success' : 'bg-primary'"
                                role="progressbar"
                                :style="'width: ' + (progress[{{ $rowIndex }}] ?? 0) + '%;'"
                                :aria-valuenow="progress[{{ $rowIndex }}] ?? 0"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>
                </button>
                <div class="rft-sample-row-card__header-actions">
                    @if($rowCount > 1 && $rowIndex > 0)
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary btn-action-sm ceq-clone-row-btn"
                            title="Copy from Sample 1"
                            aria-label="Copy Sample 1 details into Sample {{ $rowIndex + 1 }}"
                            wire:click="cloneFirstSampleRowToRow('{{ $typeId }}', {{ $rowIndex }})"
                            wire:loading.attr="disabled"
                            x-on:click.stop
                        >
                            <i class="mdi mdi-content-copy"></i>
                        </button>
                    @endif
                    <button
                        type="button"
                        class="rft-sample-row-card__chevron border-0 bg-transparent p-0"
                        @click="setOpenRow({{ $rowIndex }})"
                        :aria-expanded="openRow === {{ $rowIndex }} ? 'true' : 'false'"
                        aria-label="Toggle sample {{ $rowIndex + 1 }}"
                    >
                        <i class="mdi" :class="openRow === {{ $rowIndex }} ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                    </button>
                </div>
            </div>

            <div
                class="rft-sample-row-card__body"
                x-show="openRow === {{ $rowIndex }}"
                x-cloak
            >
                @foreach($gridRows as $gridRow)
                    @php
                        $gridColumns = $gridRow['columns'] ?? [];
                        $gridCols = (int) ($gridRow['cols'] ?? 3);
                        $gridRowClass = 'rft-sample-grid-row rft-sample-grid-row--cols-'.$gridCols;
                    @endphp
                    <div class="{{ $gridRowClass }}">
                        @foreach($gridColumns as $column)
                            @php
                                $fieldColClass = match ($gridCols) {
                                    1 => 'col-md-12',
                                    2 => 'col-md-6',
                                    default => 'col-md-4',
                                };
                            @endphp
                            <div class="{{ $fieldColClass }} rft-sample-field">
                                @if($column === null)
                                    <div class="rft-sample-field--empty"></div>
                                @else
                                    @php
                                        $field = $column['field'];
                                        $fieldName = (string) ($field['name'] ?? '');
                                        $wirePrefix = $wireBase.'.'.$fieldName;
                                    @endphp
                                    <label>
                                        {{ $column['label'] ?? ($field['label'] ?? $fieldName) }}
                                        @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                    </label>
                                    @if(($column['type'] ?? '') === 'qty_unit')
                                        @include('livewire.partials.walk-in-trf-qty-unit-cell', [
                                            'rowIndex' => $rowIndex,
                                            'hideLabel' => true,
                                            'nativeSelect' => false,
                                            'quantityWire' => $wireBase.'.sample_quantity',
                                            'unitWire' => $wireBase.'.sample_quantity_unit',
                                            'qtyValue' => $rowValues['sample_quantity'] ?? '',
                                            'unitValue' => $rowValues['sample_quantity_unit'] ?? '',
                                            'unitOptions' => $column['unit_options'] ?? [],
                                        ])
                                    @elseif($fieldName === 'sample_description' || ($field['type'] ?? '') === 'rich_text')
                                        @include('layouts.lab.partials.ls-ui.fields.ls-field-rich-text-cell', [
                                            'label' => null,
                                            'value' => $rowValues[$fieldName] ?? '',
                                            'wireModel' => $wirePrefix,
                                            'editorId' => 'ceq-desc-'.$typeId.'-'.$trfIndex.'-'.$rowIndex.'-'.$fieldName,
                                            'rowLabel' => 'Sample '.($rowIndex + 1),
                                            'compact' => true,
                                        ])
                                    @else
                                        @include('livewire.sampleworkflow.test-request-field-render', [
                                            'field' => $field,
                                            'wirePrefix' => $wirePrefix,
                                            'fieldId' => 'ceq_'.$typeId.'_'.$rowIndex.'_'.$fieldName,
                                            'rowIndex' => $rowIndex,
                                            'compact' => false,
                                            'hideLabel' => true,
                                            'useCrmSelectors' => true,
                                            'showCrmActions' => false,
                                        ])
                                    @endif
                                    @error($wirePrefix)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </article>
    @endfor
</div>
