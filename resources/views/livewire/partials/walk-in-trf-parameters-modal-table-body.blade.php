{{-- Declarative analyte rows for Choose tests modal (Alpine x-for; no imperative DOM) --}}
<template x-for="row in flatTableRows" :key="row.key">
    <tr
        :class="{
            'rft-trf-analyte-sample': row.type === 'sample',
            'ls-quote-analyte-group': row.type === 'group',
            'ls-quote-analyte-row': row.type === 'test',
            'is-selected': row.type === 'test' && row.test && isSelected(row.test.id),
        }"
        @click="row.type === 'test' && row.test && toggle(row.test.id)"
    >
        <td colspan="7" x-show="row.type === 'sample' || row.type === 'group'" x-cloak>
            <span x-show="row.type === 'sample'" x-text="row.label || 'Sample type'"></span>
            <div class="rft-trf-analyte-group__inner" x-show="row.type === 'group'">
                <span class="rft-trf-analyte-group__label" x-text="row.label || 'Analysis type'"></span>
                <span
                    class="rft-trf-analyte-group__count"
                    x-text="row.group ? ('(' + groupSelectedCount(row.group) + '/' + groupTests(row.group).length + ')') : ''"
                ></span>
                <button
                    type="button"
                    class="rft-trf-analyte-group__toggle"
                    x-text="row.group && groupSelectionState(row.group) === 'all' ? 'Clear group' : 'Select group'"
                    @click.prevent.stop="row.group && toggleGroupCheckbox(row.group)"
                ></button>
            </div>
        </td>

        <td class="ls-quote-analyte-row__select" x-show="row.type === 'test'" x-cloak @click.stop>
            <label class="ls-quote-check" @click.stop>
                <input
                    type="checkbox"
                    class="rft-trf-analyte-check"
                    :checked="row.test && isSelected(row.test.id)"
                    @change.prevent.stop="row.test && toggle(row.test.id)"
                >
                <span class="ls-quote-check__box" aria-hidden="true"></span>
            </label>
        </td>
        <td class="ls-quote-analyte-row__name" x-show="row.type === 'test'" x-cloak>
            <div class="ls-quote-analyte-row__name-inner">
                <span
                    class="ls-quote-analyte-row__label"
                    x-text="row.test ? (row.test.name || row.test.id || '') : ''"
                    :title="row.test ? (row.test.name || row.test.id || '') : ''"
                ></span>
                <span class="ls-quote-analyte-row__meta">
                    <span
                        class="ls-quote-meta-pill ls-quote-meta-pill--lab"
                        x-show="row.test && row.test.lab_section_code"
                        x-text="row.test ? row.test.lab_section_code : ''"
                        :title="row.test ? ('Lab section: ' + (row.test.lab_section_code || '')) : ''"
                    ></span>
                    <span
                        class="ls-quote-meta-pill ls-quote-meta-pill--method"
                        x-show="row.test && row.test.method"
                        x-text="row.test ? row.test.method : ''"
                        :title="row.test ? ('Method: ' + (row.test.method || '')) : ''"
                    ></span>
                </span>
            </div>
        </td>
        <td class="ls-quote-analyte-row__metric text-center" x-show="row.type === 'test'" x-cloak>
            <span class="ls-quote-metric" x-text="row.test ? formatMetric(row.test.reporting_unit) : '—'"></span>
        </td>
        <td class="ls-quote-analyte-row__metric text-center" x-show="row.type === 'test'" x-cloak>
            <span class="ls-quote-metric" x-text="row.test ? formatMetric(row.test.lod) : '—'"></span>
        </td>
        <td class="ls-quote-analyte-row__metric text-center" x-show="row.type === 'test'" x-cloak>
            <span class="ls-quote-metric" x-text="row.test ? formatMetric(row.test.loq) : '—'"></span>
        </td>
        <td class="ls-quote-analyte-row__mu text-center" x-show="row.type === 'test'" x-cloak>
            <span class="ls-quote-metric" x-text="row.test ? formatMetric(row.test.mu) : '—'"></span>
        </td>
        <td class="ls-quote-analyte-row__tat text-center" x-show="row.type === 'test'" x-cloak title="Turnaround time (days)">
            <span class="ls-quote-tat" x-text="row.test ? formatTat(row.test.tat) : '—'"></span>
        </td>
    </tr>
</template>
