@php
    $rowCount = max(1, (int) ($rowCount ?? 1));
    $progressFieldNames = collect($fields ?? [])
        ->map(static fn (array $field): string => (string) ($field['name'] ?? ''))
        ->filter(static fn (string $name): bool => $name !== '' && $name !== 'sample_quantity_unit')
        ->values()
        ->all();
@endphp

<div
    class="rft-sample-cards ceq-wizard-sample-cards"
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
            if (previous >= 0 && previous !== row) {
                window.dispatchEvent(new CustomEvent('ceq-sync-tinymce'));
            }
            this.openRow = this.openRow === row ? -1 : row;
            if (previous >= 0 && previous !== this.openRow) {
                this.$dispatch('ceq-sample-card-hidden', { row: previous });
            }
            if (this.openRow >= 0) {
                this.$nextTick(() => {
                    this.$dispatch('ceq-sample-card-shown', { row: this.openRow });
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
        $nextTick(() => $dispatch('ceq-sample-card-shown', { row: 0 }));
    "
    x-on:ceq-sample-rows-cloned.window="if (openRow >= 0) { $nextTick(() => $dispatch('ceq-sample-card-shown', { row: openRow })); } $nextTick(() => refreshAllProgress());"
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
                x-on:click="window.dispatchEvent(new CustomEvent('ceq-sync-tinymce'))"
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
        @endphp

        <article
            class="rft-sample-row-card"
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
                            x-on:click.stop="window.dispatchEvent(new CustomEvent('ceq-sync-tinymce'))"
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
                <div class="row">
                    @foreach($fields as $field)
                        @php
                            $fieldName = (string) ($field['name'] ?? '');
                            if (($field['render_paired'] ?? false) && $fieldName === 'sample_quantity_unit') {
                                continue;
                            }
                            $type = $field['element_type'] ?? 'text';
                            $isFullWidth = in_array($type, ['textarea', 'rich_text'], true)
                                || in_array($fieldName, ['sample_description'], true);
                            $colClass = $isFullWidth ? 'col-12' : 'col-md-6';
                            $wirePrefix = 'sectionRowFieldValuesByType.'.$typeId.'.'.$rowIndex;
                        @endphp
                        <div class="{{ $colClass }}">
                            <div class="form-group">
                                <label>
                                    @if($fieldName === 'sample_quantity')
                                        Qty / Unit
                                    @else
                                        {{ $field['label'] }}
                                    @endif
                                    @if($field['is_required']) <span class="text-danger">*</span> @endif
                                </label>

                                @if($fieldName === 'sample_quantity')
                                    <div class="d-flex ceq-wizard-qty-unit" style="gap: 8px;">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               placeholder="Amount"
                                               wire:model="{{ $wirePrefix }}.sample_quantity"
                                               class="form-control @error($wirePrefix.'.sample_quantity') is-invalid @enderror">
                                        <select wire:model="{{ $wirePrefix }}.sample_quantity_unit"
                                                class="form-control no-select2 @error($wirePrefix.'.sample_quantity_unit') is-invalid @enderror">
                                            <option value="">Unit</option>
                                            @foreach($field['unit_options'] ?? [] as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error($wirePrefix.'.sample_quantity')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error($wirePrefix.'.sample_quantity_unit')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                @elseif(in_array($type, ['select', 'radio'], true) && !empty($field['options']))
                                    <select wire:model="{{ $wirePrefix }}.{{ $fieldName }}"
                                            class="form-control @error($wirePrefix.'.'.$fieldName) is-invalid @enderror">
                                        <option value="">Select...</option>
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'checkbox' && !empty($field['options']))
                                    <div class="d-flex flex-column">
                                        @foreach($field['options'] as $option)
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="ceq-field-{{ $typeId }}-{{ $rowIndex }}-{{ $fieldName }}-{{ $option['value'] }}"
                                                       value="{{ $option['value'] }}"
                                                       wire:model="{{ $wirePrefix }}.{{ $fieldName }}">
                                                <label class="custom-control-label"
                                                       for="ceq-field-{{ $typeId }}-{{ $rowIndex }}-{{ $fieldName }}-{{ $option['value'] }}">
                                                    {{ $option['label'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($type === 'checkbox')
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="ceq-field-{{ $typeId }}-{{ $rowIndex }}-{{ $fieldName }}"
                                               wire:model="{{ $wirePrefix }}.{{ $fieldName }}">
                                        <label class="custom-control-label" for="ceq-field-{{ $typeId }}-{{ $rowIndex }}-{{ $fieldName }}">Yes</label>
                                    </div>
                                @elseif($type === 'rich_text' || $fieldName === 'sample_description')
                                    @include('livewire.partials.livewire-tinymce-field', [
                                        'wireKey' => $wirePrefix.'.sample_description',
                                        'editorId' => 'ceq-desc-'.$typeId.'-'.$trfIndex.'-'.$rowIndex,
                                        'value' => $rowValues['sample_description'] ?? '',
                                        'rowIndex' => $rowIndex,
                                    ])
                                @elseif($type === 'textarea')
                                    <textarea rows="2" wire:model="{{ $wirePrefix }}.{{ $fieldName }}" class="form-control @error($wirePrefix.'.'.$fieldName) is-invalid @enderror"></textarea>
                                @elseif($type === 'date')
                                    <input type="date" wire:model="{{ $wirePrefix }}.{{ $fieldName }}" class="form-control @error($wirePrefix.'.'.$fieldName) is-invalid @enderror">
                                @elseif($type === 'number')
                                    <input type="number" step="0.01" min="0" wire:model="{{ $wirePrefix }}.{{ $fieldName }}" class="form-control @error($wirePrefix.'.'.$fieldName) is-invalid @enderror">
                                @else
                                    <input type="text" wire:model="{{ $wirePrefix }}.{{ $fieldName }}" class="form-control @error($wirePrefix.'.'.$fieldName) is-invalid @enderror">
                                @endif

                                @if($fieldName !== 'sample_quantity')
                                    @error($wirePrefix.'.'.$fieldName)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    @endfor
</div>
