@php
    $searchPlaceholder = $searchPlaceholder ?? 'Search...';
    $perPageOptions = $perPageOptions ?? [25, 50, 75, 100];
@endphp

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 sm-list-filters-card" style="border-radius: 15px;">
            <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                <h6 class="mb-0 text-muted">
                    <i class="mdi mdi-filter-variant"></i> Filter Options
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="sm-list-filters-row">
                    <div class="sm-list-filters-fields">
                        <div class="sm-list-filter-field sm-list-filter-search">
                            <label class="form-label fw-bold" for="sm-list-search">Search</label>
                            <input
                                type="text"
                                id="sm-list-search"
                                wire:model.live.debounce.300ms="search"
                                class="form-control"
                                placeholder="{{ $searchPlaceholder }}"
                            >
                        </div>
                        @isset($filters)
                            {{ $filters }}
                        @endisset
                        <div class="sm-list-filter-field sm-list-filter-per-page">
                            <label class="form-label fw-bold" for="sm-list-per-page">Per page</label>
                            <select wire:model.live="perPage" id="sm-list-per-page" class="form-select">
                                @foreach($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="sm-list-filter-actions">
                        <label class="form-label fw-bold d-block">&nbsp;</label>
                        <button type="button" wire:click="clearListFilters" class="btn btn-outline-secondary">
                            <i class="mdi mdi-refresh"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
