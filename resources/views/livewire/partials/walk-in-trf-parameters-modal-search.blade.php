{{-- Tests modal search + filter row (ls-search-bar detached + Alpine filters) --}}
<div
    class="rft-trf-params-search-toolbar"
    @click.outside="if (filtersOpen) closeFilters()"
    @keydown.escape.window="if (filtersOpen) closeFilters()"
>
    <div class="rft-trf-params-search-toolbar__row">
        @include('layouts.lab.partials.ls-ui.fields.ls-search-bar', [
            'variant' => 'detached',
            'placeholder' => 'Search tests, methods, lab section…',
            'ariaLabel' => 'Search tests',
            'alpineModel' => 'search',
            'filterAlpine' => true,
            'filterOpenAlpine' => 'filtersOpen',
            'filterBadgeAlpine' => 'activeFilterCount',
            'filterClick' => 'toggleFilters()',
            'fullWidth' => true,
        ])
        <button
            type="button"
            class="rft-trf-params-search-toolbar__clear"
            x-show="activeFilterCount > 0 || String(search || '').trim() !== ''"
            x-cloak
            @click.prevent="clearFilters()"
        >
            <i class="mdi mdi-close-circle-outline" aria-hidden="true"></i>
            <span>Clear</span>
        </button>
    </div>

    <div class="ls-quotation-filter-panel rft-trf-params-filter-panel" x-show="filtersOpen" x-cloak>
        <div class="ls-quotation-filter-panel__head">
            <span><i class="mdi mdi-filter-variant" aria-hidden="true"></i> Filter tests</span>
            <button type="button" class="ls-quotation-filter-panel__close" @click.prevent="closeFilters()" aria-label="Close filters">
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <div class="rft-trf-params-filter-panel__sections">
            <section class="rft-trf-params-filter-section">
                <h6 class="rft-trf-params-filter-section__title">
                    <i class="mdi mdi-flask-outline" aria-hidden="true"></i> Analysis type
                </h6>
                <div class="rft-trf-params-filter-chips">
                    <template x-for="item in filterOptions.analysisTypes" :key="'at-' + item.value">
                        <label class="rft-trf-params-filter-chip" :class="filterAnalysisTypes.includes(item.value) && 'is-active'">
                            <input type="checkbox" class="sr-only" :value="item.value" :checked="filterAnalysisTypes.includes(item.value)" @change="toggleFilterValue('filterAnalysisTypes', item.value)">
                            <span x-text="item.label"></span>
                        </label>
                    </template>
                    <span class="small text-muted" x-show="!filterOptions.analysisTypes.length">—</span>
                </div>
            </section>

            <section class="rft-trf-params-filter-section" data-rft-filter-section="lab">
                <h6 class="rft-trf-params-filter-section__title">
                    <i class="mdi mdi-domain" aria-hidden="true"></i> Lab section
                </h6>

                <div class="rft-trf-params-filter-chips" x-show="!useLabSectionSelect" x-cloak>
                    <template x-for="item in filterOptions.labSections" :key="'ls-' + item.value">
                        <label class="rft-trf-params-filter-chip" :class="filterLabSections.includes(item.value) && 'is-active'">
                            <input type="checkbox" class="sr-only" :value="item.value" :checked="filterLabSections.includes(item.value)" @change="toggleFilterValue('filterLabSections', item.value)">
                            <span x-text="item.label"></span>
                        </label>
                    </template>
                    <span class="small text-muted" x-show="!filterOptions.labSections.length">—</span>
                </div>

                <div
                    class="rft-trf-filter-select"
                    x-show="useLabSectionSelect"
                    x-cloak
                    @click.outside="labSectionFilterOpen = false"
                >
                    <button
                        type="button"
                        class="rft-trf-filter-select__trigger"
                        @click.prevent="openLabSectionFilter()"
                        :aria-expanded="labSectionFilterOpen ? 'true' : 'false'"
                    >
                        <span class="rft-trf-filter-select__summary" x-text="labSectionFilterSummary()"></span>
                        <i class="mdi" :class="labSectionFilterOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
                    </button>

                    <div class="rft-trf-filter-select__panel" x-show="labSectionFilterOpen" x-cloak>
                        <div class="rft-trf-filter-select__search">
                            <i class="mdi mdi-magnify" aria-hidden="true"></i>
                            <input
                                type="search"
                                class="form-control form-control-sm"
                                placeholder="Search lab sections…"
                                x-model="labSectionFilterQuery"
                                @keydown.escape.prevent="labSectionFilterOpen = false"
                            >
                        </div>

                        <div class="rft-trf-filter-select__tags" x-show="filterLabSections.length" x-cloak>
                            <template x-for="tag in visibleSelectedFilterTags('filterLabSections')" :key="'ls-tag-' + tag.value">
                                <button
                                    type="button"
                                    class="rft-trf-filter-select__tag"
                                    @click.prevent="toggleFilterValue('filterLabSections', tag.value)"
                                    :title="'Remove ' + tag.label"
                                >
                                    <span x-text="tag.label"></span>
                                    <i class="mdi mdi-close" aria-hidden="true"></i>
                                </button>
                            </template>
                            <span
                                class="rft-trf-filter-select__more"
                                x-show="filterLabSections.length > 3"
                                x-text="'+' + (filterLabSections.length - 3)"
                            ></span>
                        </div>

                        <div class="rft-trf-filter-select__list" role="listbox" aria-label="Lab sections">
                            <template x-for="item in labSectionFilterPicker.choices" :key="'ls-opt-' + item.value">
                                <label class="rft-trf-filter-select__option" :class="filterLabSections.includes(item.value) && 'is-active'">
                                    <input
                                        type="checkbox"
                                        class="sr-only"
                                        :value="item.value"
                                        :checked="filterLabSections.includes(item.value)"
                                        @change="toggleFilterValue('filterLabSections', item.value)"
                                    >
                                    <span class="rft-trf-filter-select__check" aria-hidden="true"></span>
                                    <span x-text="item.label"></span>
                                </label>
                            </template>
                            <p class="rft-trf-filter-select__empty" x-show="!labSectionFilterPicker.choices.length">No lab sections match.</p>
                            <p class="rft-trf-filter-select__hint" x-show="labSectionFilterPicker.truncated">
                                Showing first 50 — type to search more.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="ls-quotation-filter-panel__foot">
            <button type="button" class="ls-btn" @click.prevent="clearFilters()">Clear all</button>
            <button type="button" class="ls-btn ls-btn--secondary-fill" @click.prevent="closeFilters()">Done</button>
        </div>
    </div>
</div>
