<div class="pricelist-items-filter-bar mb-3" wire:key="pricelist-items-filter-bar">
    <div class="row align-items-end g-2">
        <div class="col-lg-4 col-md-6">
            <label class="soft-label mb-1">Search</label>
            <input type="text"
                   wire:model.live.debounce.300ms="itemSearch"
                   class="form-control modern-input form-control-sm"
                   placeholder="Analyte, analysis type, or sample type…"
                   autocomplete="off">
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="soft-label mb-1">Sample Type</label>
            <div class="item-tag-select tag-select-container pricelist-filter-tag-select"
                 wire:click="$set('showItemFilterSampleTypeDropdown', true)">
                <div class="tag-select-input item-tag-select-input">
                    @foreach($itemSampleTypeFilterIds as $selectedSampleTypeId)
                        @php $selectedSt = $itemFilterSampleTypes->firstWhere('id', $selectedSampleTypeId); @endphp
                        @if($selectedSt)
                            <span class="tag-badge">
                                {{ $selectedSt->name }}
                                <i class="mdi mdi-close-circle" wire:click.stop="removeItemFilterSampleType(@js($selectedSampleTypeId))" role="button" tabindex="0"></i>
                            </span>
                        @endif
                    @endforeach
                    <input type="text"
                           wire:model.live.debounce.300ms="itemFilterSampleTypeSearch"
                           wire:click.stop="$set('showItemFilterSampleTypeDropdown', true)"
                           class="tag-input"
                           placeholder="{{ count($itemSampleTypeFilterIds) > 0 ? '' : 'Select sample type…' }}"
                           autocomplete="off">
                </div>
                @if($showItemFilterSampleTypeDropdown)
                    <div class="tag-dropdown" wire:click.outside="$set('showItemFilterSampleTypeDropdown', false)">
                        @forelse($filteredItemFilterSampleTypes as $sampleType)
                            @php $isSelected = in_array((string) $sampleType->id, $itemSampleTypeFilterIds, true); @endphp
                            <div class="tag-dropdown-item {{ $isSelected ? 'tag-dropdown-item--selected' : '' }}"
                                 wire:click.stop="toggleItemFilterSampleType(@js($sampleType->id))">
                                <span class="tag-dropdown-name">{{ $sampleType->name }}</span>
                                @if($isSelected)
                                    <i class="mdi mdi-check ml-auto text-primary"></i>
                                @endif
                            </div>
                        @empty
                            <div class="tag-dropdown-item text-muted small">No sample types on this pricelist.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="soft-label mb-1">Per page</label>
            <select wire:model.live="itemsPerPage" class="form-control modern-input form-control-sm">
                @foreach($itemsPerPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-8">
            <label class="soft-label mb-1 d-none d-lg-block">&nbsp;</label>
            <button type="button"
                    class="btn btn-outline-secondary btn-sm w-100 pricelist-more-filters-btn"
                    wire:click="toggleItemAdvancedFilters">
                <i class="mdi mdi-filter-variant"></i>
                {{ $showItemAdvancedFilters ? 'Fewer filters' : 'More filters' }}
            </button>
        </div>
    </div>

    @if($showItemAdvancedFilters)
        <div class="pricelist-items-filter-advanced mt-3 pt-3">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="soft-label mb-1">Analysis Type</label>
                    <div class="item-tag-select tag-select-container pricelist-filter-tag-select {{ $canFilterByAnalysisType ? '' : 'is-locked' }}"
                         @if($canFilterByAnalysisType) wire:click="$set('showItemFilterAnalysisTypeDropdown', true)" @endif>
                        <div class="tag-select-input item-tag-select-input {{ $canFilterByAnalysisType ? '' : 'item-tag-select-input--disabled' }}">
                            @if($canFilterByAnalysisType)
                                @foreach($itemAnalysisTypeFilterIds as $selectedAnalysisTypeId)
                                    @php $selectedAt = $itemFilterAnalysisTypes->firstWhere('id', $selectedAnalysisTypeId); @endphp
                                    @if($selectedAt)
                                        <span class="tag-badge">
                                            {{ $selectedAt->code }} — {{ $selectedAt->name }}
                                            <i class="mdi mdi-close-circle" wire:click.stop="removeItemFilterAnalysisType(@js($selectedAnalysisTypeId))" role="button" tabindex="0"></i>
                                        </span>
                                    @endif
                                @endforeach
                                <input type="text"
                                       wire:model.live.debounce.300ms="itemFilterAnalysisTypeSearch"
                                       wire:click.stop="$set('showItemFilterAnalysisTypeDropdown', true)"
                                       class="tag-input"
                                       placeholder="{{ count($itemAnalysisTypeFilterIds) > 0 ? '' : 'Select analysis type…' }}"
                                       autocomplete="off">
                            @else
                                <span class="tag-input-placeholder text-muted">Select a sample type first…</span>
                            @endif
                        </div>
                        @if($canFilterByAnalysisType && $showItemFilterAnalysisTypeDropdown)
                            <div class="tag-dropdown" wire:click.outside="$set('showItemFilterAnalysisTypeDropdown', false)">
                                @forelse($filteredItemFilterAnalysisTypes as $analysisType)
                                    @php $isSelected = in_array((string) $analysisType->id, $itemAnalysisTypeFilterIds, true); @endphp
                                    <div class="tag-dropdown-item {{ $isSelected ? 'tag-dropdown-item--selected' : '' }}"
                                         wire:click.stop="toggleItemFilterAnalysisType(@js($analysisType->id))">
                                        <span class="tag-dropdown-code">{{ $analysisType->code }}</span>
                                        <span class="tag-dropdown-name">{{ $analysisType->name }}</span>
                                        @if($isSelected)
                                            <i class="mdi mdi-check ml-auto text-primary"></i>
                                        @endif
                                    </div>
                                @empty
                                    <div class="tag-dropdown-item text-muted small">No analysis types for the selected sample type(s).</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="soft-label mb-1">Parameter</label>
                    <div class="item-tag-select tag-select-container pricelist-filter-tag-select {{ $canFilterByParameter ? '' : 'is-locked' }}"
                         @if($canFilterByParameter) wire:click="$set('showItemFilterAnalysisElementDropdown', true)" @endif>
                        <div class="tag-select-input item-tag-select-input {{ $canFilterByParameter ? '' : 'item-tag-select-input--disabled' }}">
                            @if($canFilterByParameter)
                                @foreach($itemAnalysisElementFilterIds as $selectedElementId)
                                    @php $selectedEl = $itemFilterAnalysisElements->firstWhere('id', $selectedElementId); @endphp
                                    @if($selectedEl)
                                        <span class="tag-badge">
                                            {{ $selectedEl->label }}
                                            <i class="mdi mdi-close-circle" wire:click.stop="removeItemFilterAnalysisElement(@js($selectedElementId))" role="button" tabindex="0"></i>
                                        </span>
                                    @endif
                                @endforeach
                                <input type="text"
                                       wire:model.live.debounce.300ms="itemFilterAnalysisElementSearch"
                                       wire:click.stop="$set('showItemFilterAnalysisElementDropdown', true)"
                                       class="tag-input"
                                       placeholder="{{ count($itemAnalysisElementFilterIds) > 0 ? '' : 'Select parameter…' }}"
                                       autocomplete="off">
                            @else
                                <span class="tag-input-placeholder text-muted">
                                    {{ $canFilterByAnalysisType ? 'Select an analysis type first…' : 'Select sample type, then analysis type…' }}
                                </span>
                            @endif
                        </div>
                        @if($canFilterByParameter && $showItemFilterAnalysisElementDropdown)
                            <div class="tag-dropdown" wire:click.outside="$set('showItemFilterAnalysisElementDropdown', false)">
                                @forelse($filteredItemFilterAnalysisElements as $element)
                                    @php $isSelected = in_array((string) $element->id, $itemAnalysisElementFilterIds, true); @endphp
                                    <div class="tag-dropdown-item {{ $isSelected ? 'tag-dropdown-item--selected' : '' }}"
                                         wire:click.stop="toggleItemFilterAnalysisElement(@js($element->id))">
                                        <span class="tag-dropdown-name">{{ $element->label }}</span>
                                        @if($isSelected)
                                            <i class="mdi mdi-check ml-auto text-primary"></i>
                                        @endif
                                    </div>
                                @empty
                                    <div class="tag-dropdown-item text-muted small">No parameters for the selected analysis type(s).</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="pricelist-filter-fields-panel">
                        <div class="pricelist-filter-fields-panel__header">
                            <span class="soft-label mb-0">Item fields</span>
                            <span class="pricelist-filter-fields-panel__hint text-muted">Filter by status flags</span>
                        </div>
                        <div class="pricelist-filter-fields-grid">
                            <div class="pricelist-filter-field-tile">
                                <label class="pricelist-filter-field-tile__label" for="itemActiveFilter">Active</label>
                                <select id="itemActiveFilter" wire:model.live="itemActiveFilter" class="form-control form-control-sm modern-input">
                                    <option value="">All</option>
                                    <option value="1">Active only</option>
                                    <option value="0">Inactive only</option>
                                </select>
                            </div>
                            <div class="pricelist-filter-field-tile">
                                <label class="pricelist-filter-field-tile__label" for="itemVatFilter">VAT</label>
                                <select id="itemVatFilter" wire:model.live="itemVatFilter" class="form-control form-control-sm modern-input">
                                    <option value="">All</option>
                                    <option value="1">VAT applies</option>
                                    <option value="0">No VAT</option>
                                </select>
                            </div>
                            <div class="pricelist-filter-field-tile">
                                <label class="pricelist-filter-field-tile__label" for="itemInternalUseFilter">Internal use</label>
                                <select id="itemInternalUseFilter" wire:model.live="itemInternalUseFilter" class="form-control form-control-sm modern-input">
                                    <option value="">All</option>
                                    <option value="1">Internal only</option>
                                    <option value="0">Not internal</option>
                                </select>
                            </div>
                            <div class="pricelist-filter-field-tile">
                                <label class="pricelist-filter-field-tile__label" for="itemExternalViewFilter">External view</label>
                                <select id="itemExternalViewFilter" wire:model.live="itemExternalViewFilter" class="form-control form-control-sm modern-input">
                                    <option value="">All</option>
                                    <option value="1">Visible externally</option>
                                    <option value="0">Hidden externally</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="clearItemFilters">
                        <i class="mdi mdi-filter-off-outline"></i> Clear filters
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
