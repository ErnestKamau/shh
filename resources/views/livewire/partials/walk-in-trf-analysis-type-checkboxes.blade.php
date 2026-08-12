@php
    $wirePrefix = $wirePrefix ?? 'formData.analysis_type_id';
    $fieldId = $fieldId ?? 'analysis_type_checks';
    $rowIndex = $rowIndex ?? null;
    $selectedRaw = data_get($this->formData ?? [], str_replace('formData.', '', $wirePrefix), []);
    if (! is_array($selectedRaw)) {
        $selectedRaw = filled($selectedRaw) ? [(string) $selectedRaw] : [];
    }
    $selectedIds = array_values(array_filter(array_map('strval', $selectedRaw)));
    $analysisOptions = $this->analysisTypesForRow($rowIndex)
        ->map(function ($at) {
            $sampleTypeName = trim((string) (optional($at->sample_type)->name ?? ''));

            return [
                'id' => (string) $at->id,
                'label' => (string) $at->name,
                'sample_type' => $sampleTypeName,
            ];
        })
        ->values()
        ->all();
@endphp

<div
    class="rft-analysis-type-checks"
    wire:key="at-checks-{{ $fieldId }}-{{ md5(json_encode([collect($analysisOptions)->pluck('id')->all(), $selectedIds])) }}"
    x-data="{
        search: '',
        options: @js($analysisOptions),
        selected: @js($selectedIds),
        get filtered() {
            const query = String(this.search || '').trim().toLowerCase();
            if (!query) {
                return this.options;
            }
            return this.options.filter((opt) => {
                const label = String(opt.label || '').toLowerCase();
                const sample = String(opt.sample_type || '').toLowerCase();
                return label.includes(query) || sample.includes(query);
            });
        },
        isSelected(id) {
            return this.selected.includes(String(id));
        },
        async toggle(id) {
            await $wire.toggleWalkInAnalysisType(@js($wirePrefix), String(id));
        },
    }"
>
    @if ($analysisOptions === [])
        <p class="small text-muted mb-0">Select sample type first.</p>
    @else
        <div class="rft-analysis-type-checks__toolbar">
            <input
                type="search"
                class="form-control form-control-sm"
                placeholder="Search analysis types…"
                x-model="search"
                @keydown.stop
            >
            <span class="small text-muted" x-text="selected.length + '/' + options.length + ' selected'"></span>
        </div>

        <div class="rft-analysis-type-checks__panel" role="group" aria-label="Analysis types">
            <template x-for="opt in filtered" :key="opt.id">
                <label class="rft-analysis-type-checks__row" :class="isSelected(opt.id) && 'is-selected'">
                    <input
                        type="checkbox"
                        class="rft-analysis-type-checks__input"
                        :checked="isSelected(opt.id)"
                        @click.prevent="toggle(opt.id)"
                    >
                    <span class="rft-analysis-type-checks__body">
                        <span class="rft-analysis-type-checks__label" x-text="opt.label"></span>
                        <span class="rft-analysis-type-checks__meta" x-show="opt.sample_type" x-text="opt.sample_type"></span>
                    </span>
                </label>
            </template>
            <p class="small text-muted mb-0 px-2 py-2" x-show="filtered.length === 0">No analysis types match your search.</p>
        </div>

        <p class="small text-muted mb-0 mt-1">Selecting an analysis type auto-picks all of its tests.</p>
    @endif
</div>
