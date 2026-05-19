{{-- TAT Dashboard Header: Title bar, filters, period switcher, export --}}
@php
    $sectionOptions = $available_sections ?? [];
    $selectedSection = collect($sectionOptions)->first(fn($item) => (string) ($item['id'] ?? '') === (string) ($selectedLabId ?? ''));
    $selectedAnalyst = collect($available_analysts ?? [])->first(fn($item) => (string) ($item['analyst_id'] ?? '') === (string) ($selectedAnalystId ?? ''));
@endphp

<div class="kebs-title-bar">
    <div class="tat-header-top">
        <div class="tat-header-title">
            <h5 class="mb-0 font-weight-bold">TESTING DEPARTMENT PERFORMANCE DASHBOARD</h5>
            <small class="text-white-50">{{ $stats['period_label'] ?? 'Active Workload' }}
                @if($stats['refreshed_at'] ?? null)
                    &mdash; Updated {{ \Carbon\Carbon::parse($stats['refreshed_at'])->diffForHumans() }}
                @endif
            </small>
        </div>

        <div class="tat-header-actions">
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="mdi mdi-download"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="min-width: 200px;">
                    <h6 class="dropdown-header font-weight-bold text-primary">PDF Reports</h6>
                    <a class="dropdown-item" href="#" wire:click.prevent="export('pdf', false)">
                        <i class="mdi mdi-file-pdf text-danger mr-2"></i> PDF Download
                    </a>
                    <a class="dropdown-item" href="#" wire:click.prevent="export('pdf', true)">
                        <i class="mdi mdi-eye text-info mr-2"></i> PDF Preview
                    </a>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header font-weight-bold text-success">Data Exports</h6>
                    <a class="dropdown-item" href="#" wire:click.prevent="export('csv', false)">
                        <i class="mdi mdi-file-delimited text-success mr-2"></i> CSV Export
                    </a>
                    <a class="dropdown-item" href="#" wire:click.prevent="export('csv', true)">
                        <i class="mdi mdi-table-eye text-muted mr-2"></i> CSV Preview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="tat-filter-bar">
        <div class="tat-filter-field">
            <label class="tat-filter-label" for="tat-lab-section">Lab Section</label>
            <select id="tat-lab-section" wire:model="selectedLabId" wire:change="handleLabSelectionChange" class="form-control form-control-sm tat-filter-input">
                <option value="" class="text-dark">All Labs</option>
                @foreach($sectionOptions as $section)
                    <option value="{{ $section['id'] }}" class="text-dark">{{ $section['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="tat-filter-field">
            <label class="tat-filter-label" for="tat-analyst">Analyst</label>
            <select id="tat-analyst" wire:model="selectedAnalystId" class="form-control form-control-sm tat-filter-input">
                <option value="" class="text-dark">All Analysts</option>
                @foreach($available_analysts ?? [] as $analyst)
                    <option value="{{ $analyst['analyst_id'] }}" class="text-dark">{{ $analyst['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="tat-filter-field tat-filter-field-wide">
            <label class="tat-filter-label">Date Range</label>
            <div class="tat-filter-date">
                <input type="date" wire:model="startDate" class="form-control form-control-sm">
                <span>to</span>
                <input type="date" wire:model="endDate" class="form-control form-control-sm">
            </div>
        </div>

        <div class="tat-filter-actions">
            <button type="button" wire:click="applyFilters" class="btn btn-sm btn-light tat-reset-button">
                <i class="mdi mdi-filter-check-outline mr-1"></i> Apply Filters
            </button>
            <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light tat-reset-button">
                <i class="mdi mdi-filter-remove-outline mr-1"></i> Reset Filters
            </button>
        </div>
    </div>

    <div class="tat-filter-summary">
        <span class="tat-filter-chip {{ $selectedSection ? '' : 'tat-filter-chip-muted' }}">
            <strong>Lab</strong>
            <span>{{ $selectedSection['name'] ?? 'All Labs' }}</span>
        </span>

        <span class="tat-filter-chip {{ $selectedAnalyst ? '' : 'tat-filter-chip-muted' }}">
            <strong>Analyst</strong>
            <span>{{ $selectedAnalyst['name'] ?? 'All Analysts' }}</span>
        </span>

        @if($startDate || $endDate)
            <span class="tat-filter-chip">
                <strong>Range</strong>
                <span>{{ $startDate ?: 'Start' }} to {{ $endDate ?: 'Today' }}</span>
            </span>
        @else
            <span class="tat-filter-chip tat-filter-chip-muted">
                <strong>Range</strong>
                <span>No explicit date range</span>
            </span>
        @endif
    </div>
</div>
