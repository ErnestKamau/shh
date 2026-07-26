<div class="container-fluid planner-kpi-reports-page">
    <style>
        .planner-kpi-reports-page {
            --kpi-primary: #1e40af;
            --kpi-primary-soft: #eff6ff;
            --kpi-success: #059669;
            --kpi-success-soft: #ecfdf5;
            --kpi-warning: #d97706;
            --kpi-warning-soft: #fffbeb;
            --kpi-muted: #64748b;
            --kpi-border: #e2e8f0;
            --kpi-surface: #ffffff;
        }

        .kpi-hero-card {
            border-radius: 16px;
            background: #ffffff;
            color: #0f172a;
            border: 1px solid var(--kpi-border);
            overflow: hidden;
        }

        .kpi-hero-card .subtitle {
            color: var(--kpi-muted);
        }

        .kpi-hero-card h3 i {
            color: var(--kpi-primary);
        }

        .kpi-stat-card {
            border-radius: 14px;
            border: 1px solid var(--kpi-border);
            background: var(--kpi-surface);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .kpi-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .kpi-stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .kpi-stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--kpi-muted);
        }

        .kpi-filter-panel {
            border-radius: 16px;
            border: 1px solid var(--kpi-border);
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .kpi-filter-panel label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--kpi-muted);
            margin-bottom: 0.35rem;
        }

        .kpi-filter-panel .form-control,
        .kpi-filter-panel select.form-control {
            border-radius: 8px;
            border-color: var(--kpi-border);
            font-size: 0.875rem;
        }

        .kpi-table-card {
            border-radius: 16px;
            border: 1px solid var(--kpi-border);
            overflow: hidden;
        }

        .kpi-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid var(--kpi-border) !important;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--kpi-muted);
            white-space: nowrap;
            vertical-align: middle;
        }

        .kpi-table tbody td {
            font-size: 0.84rem;
            color: #334155;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
        }

        .kpi-compare-cell {
            min-width: 140px;
        }

        .kpi-compare-cell .scheduled {
            color: #1e40af;
            font-weight: 600;
        }

        .kpi-compare-cell .collected {
            color: #059669;
            font-weight: 600;
        }

        .kpi-compare-cell .divider {
            color: #cbd5e1;
            margin: 0 0.25rem;
        }

        .kpi-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .kpi-badge-collected {
            background: var(--kpi-success-soft);
            color: var(--kpi-success);
        }

        .kpi-badge-pending {
            background: #f1f5f9;
            color: #475569;
        }

        .kpi-badge-partial {
            background: var(--kpi-warning-soft);
            color: var(--kpi-warning);
        }

        .kpi-more-filters-btn {
            position: relative;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .kpi-filter-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
        }

        .kpi-toolbar .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .kpi-search {
            border-radius: 10px;
            border-color: var(--kpi-border);
        }

        .kpi-param-text {
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: bottom;
        }
    </style>

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card kpi-hero-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 font-weight-bold">
                                <i class="mdi mdi-chart-timeline-variant mr-2"></i>{{ __('planner.kpi_reports') }}
                            </h3>
                            <p class="subtitle mb-0">
                                {{ __('planner.kpi_reports_subtitle') }}
                            </p>
                        </div>
                        <div class="kpi-toolbar d-flex flex-wrap" style="gap: 0.5rem;">
                            <button wire:click="exportToExcel" class="btn btn-success btn-sm">
                                <i class="mdi mdi-file-excel mr-1"></i> {{ __('planner.export_excel') }}
                            </button>
                            <button wire:click="exportToCsv" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-file-delimited mr-1"></i> {{ __('planner.export_csv') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} mr-1"></i> {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="row mb-4">
        <div class="col-6 col-lg mb-3 mb-lg-0">
            <div class="card kpi-stat-card h-100 p-3">
                <div class="kpi-stat-label mb-1">{{ __('planner.total_schedules') }}</div>
                <div class="kpi-stat-value text-dark">{{ $this->summary['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3 mb-lg-0">
            <div class="card kpi-stat-card h-100 p-3">
                <div class="kpi-stat-label mb-1">{{ __('planner.fully_collected') }}</div>
                <div class="kpi-stat-value" style="color: var(--kpi-success);">{{ $this->summary['collected'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3 mb-lg-0">
            <div class="card kpi-stat-card h-100 p-3">
                <div class="kpi-stat-label mb-1">{{ __('planner.partial') }}</div>
                <div class="kpi-stat-value" style="color: var(--kpi-warning);">{{ $this->summary['partial'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3 mb-lg-0">
            <div class="card kpi-stat-card h-100 p-3">
                <div class="kpi-stat-label mb-1">{{ __('planner.pending') }}</div>
                <div class="kpi-stat-value text-secondary">{{ $this->summary['pending'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card kpi-stat-card h-100 p-3">
                <div class="kpi-stat-label mb-1">{{ __('planner.collection_rate') }}</div>
                <div class="kpi-stat-value" style="color: var(--kpi-primary);">{{ $this->summary['collection_rate'] }}%</div>
                <small class="text-muted" style="font-size:0.68rem;">{{ __('planner.samples_collected_vs_scheduled') }}</small>
            </div>
        </div>
    </div>

    {{-- Search & primary filters --}}
    <div class="card kpi-primary-filters shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label class="kpi-stat-label d-block mb-1">{{ __('system.search') }}</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="form-control form-control-sm kpi-search"
                        placeholder="{{ __('planner.kpi_search_placeholder') }}"
                    >
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                    <label class="kpi-stat-label d-block mb-1">{{ __('planner.date_from') }}</label>
                    <input type="date" wire:model.live="filterDateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                    <label class="kpi-stat-label d-block mb-1">{{ __('planner.date_to') }}</label>
                    <input type="date" wire:model.live="filterDateTo" class="form-control form-control-sm">
                </div>
                <div class="col-lg-3 col-md-8 mb-2 mb-md-0">
                    <label class="kpi-stat-label d-block mb-1">{{ __('planner.client') }}</label>
                    <select wire:model.live="filterClientId" class="form-control form-control-sm no-select2">
                        <option value="">{{ __('planner.all_clients') }}</option>
                        @foreach($this->clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 d-flex align-items-end justify-content-lg-end mb-2 mb-md-0" style="gap: 0.5rem;">
                    <button
                        wire:click="toggleMoreFilters"
                        class="btn btn-outline-secondary btn-sm kpi-more-filters-btn"
                        title="More filters"
                    >
                        <i class="mdi mdi-tune-vertical"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('planner.more') }}</span>
                        @if($this->activeMoreFiltersCount > 0)
                            <span class="kpi-filter-badge">{{ $this->activeMoreFiltersCount }}</span>
                        @endif
                    </button>
                    <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm" title="Reset filters">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="text-muted small mt-2">
                <i class="mdi mdi-information-outline mr-1"></i>
                {{ __('planner.showing_records', ['count' => $this->reportRows->count()]) }}
            </div>
        </div>
    </div>

    {{-- More filters (collapsed by default) --}}
    @if($showMoreFilters)
        <div class="card kpi-filter-panel shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 font-weight-bold text-muted text-uppercase" style="font-size:0.72rem;letter-spacing:0.06em;">
                        <i class="mdi mdi-tune-vertical mr-1"></i> {{ __('planner.additional_filters') }}
                    </h6>
                    <button wire:click="toggleMoreFilters" class="btn btn-link btn-sm text-muted p-0">
                        <i class="mdi mdi-close"></i> {{ __('planner.close') }}
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group mb-3">
                        <label>Contact Details</label>
                        <select wire:model.live="filterContactId" class="form-control form-control-sm no-select2">
                            <option value="">All Contacts</option>
                            @foreach($this->contacts as $contact)
                                <option value="{{ $contact->id }}">
                                    {{ trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) }}
                                    @if($contact->email) — {{ $contact->email }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Contract Valid From</label>
                        <input type="date" wire:model.live="filterContractValidFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Contract Valid To</label>
                        <input type="date" wire:model.live="filterContractValidTo" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Frequency</label>
                        <select wire:model.live="filterFrequency" class="form-control form-control-sm no-select2">
                            <option value="">All Frequencies</option>
                            @foreach($frequencies as $freq)
                                <option value="{{ $freq }}">{{ $freq }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group mb-3">
                        <label>Sample Categories</label>
                        <select wire:model.live="filterSampleTypeId" class="form-control form-control-sm no-select2">
                            <option value="">All Categories</option>
                            @foreach($this->sampleTypes as $sampleType)
                                <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Sample Details</label>
                        <select wire:model.live="filterAnalysisTypeId" class="form-control form-control-sm no-select2">
                            <option value="">All Analysis Types</option>
                            @foreach($this->analysisTypes as $analysisType)
                                <option value="{{ $analysisType->id }}">{{ $analysisType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Test Parameters</label>
                        <select wire:model.live="filterParameterId" class="form-control form-control-sm no-select2">
                            <option value="">All Parameters</option>
                            @foreach($this->parameters as $parameter)
                                <option value="{{ $parameter->id }}">{{ $parameter->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label>Collection Status</label>
                        <select wire:model.live="filterCollectionStatus" class="form-control form-control-sm no-select2">
                            <option value="all">All Statuses</option>
                            <option value="collected">Collected</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group mb-0">
                        <label>No. Samples Scheduled</label>
                        <div class="d-flex" style="gap: 0.5rem;">
                            <input type="number" wire:model.live="filterScheduledMin" class="form-control form-control-sm" placeholder="Min" min="0">
                            <input type="number" wire:model.live="filterScheduledMax" class="form-control form-control-sm" placeholder="Max" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label>No. Samples Collected</label>
                        <div class="d-flex" style="gap: 0.5rem;">
                            <input type="number" wire:model.live="filterCollectedMin" class="form-control form-control-sm" placeholder="Min" min="0">
                            <input type="number" wire:model.live="filterCollectedMax" class="form-control form-control-sm" placeholder="Max" min="0">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-md-end mb-0">
                        <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                            <i class="mdi mdi-refresh mr-1"></i> {{ __('planner.reset_all_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Data table --}}
    <div class="card kpi-table-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover kpi-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('planner.date') }}</th>
                        <th>{{ __('planner.client') }}</th>
                        <th>{{ __('planner.contract_validity') }}</th>
                        <th>{{ __('planner.contact') }}</th>
                        <th>{{ __('planner.sample_categories') }}</th>
                        <th>{{ __('planner.sample_details') }}</th>
                        <th>{{ __('planner.no_samples') }}</th>
                        <th>{{ __('planner.frequency') }}</th>
                        <th>{{ __('planner.parameters') }}</th>
                        <th>{{ __('planner.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->reportRows as $row)
                        <tr wire:key="kpi-row-{{ $row['schedule_id'] }}">
                            <td>
                                <div class="font-weight-bold">{{ $row['date'] }}</div>
                                @if($row['time'])
                                    <small class="text-muted">{{ $row['time'] }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $row['client_name'] }}</div>
                                <small class="text-muted">{{ $row['title'] }}</small>
                            </td>
                            <td>
                                <small>{{ $row['contract_validity'] }}</small>
                            </td>
                            <td>
                                <div>{{ $row['contact_name'] }}</div>
                                <small class="text-muted d-block">{{ $row['contact_email'] }}</small>
                                @if($row['contact_phone'] !== 'N/A')
                                    <small class="text-muted">{{ $row['contact_phone'] }}</small>
                                @endif
                            </td>
                            <td class="kpi-compare-cell">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.scheduled') }}</small>
                                    <span class="scheduled">{{ $row['scheduled_categories'] }}</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.collected') }}</small>
                                    <span class="collected">{{ $row['collected_categories'] }}</span>
                                </div>
                            </td>
                            <td class="kpi-compare-cell">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.scheduled') }}</small>
                                    <span class="scheduled">{{ $row['scheduled_details'] }}</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.collected') }}</small>
                                    <span class="collected">{{ $row['collected_details'] }}</span>
                                </div>
                            </td>
                            <td class="kpi-compare-cell text-center">
                                <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.sched_coll') }}</small>
                                <span class="scheduled">{{ $row['scheduled_samples'] }}</span>
                                <span class="divider">/</span>
                                <span class="collected">{{ $row['collected_samples'] }}</span>
                            </td>
                            <td>{{ $row['frequency'] }}</td>
                            <td>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.scheduled') }}</small>
                                    <span class="kpi-param-text scheduled" title="{{ $row['scheduled_parameters'] }}">
                                        {{ $row['scheduled_parameters'] }}
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted d-block" style="font-size:0.65rem;">{{ __('planner.collected') }}</small>
                                    <span class="kpi-param-text collected" title="{{ $row['collected_parameters'] }}">
                                        {{ $row['collected_parameters'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($row['status'] === 'collected')
                                    <span class="kpi-badge kpi-badge-collected">
                                        <i class="mdi mdi-check-circle"></i> {{ __('planner.collected') }}
                                    </span>
                                @elseif($row['status'] === 'partial')
                                        <span
                                        class="kpi-badge kpi-badge-partial"
                                        title="{{ __('planner.samples_collected_progress', ['collected' => $row['collected_samples'], 'scheduled' => $row['scheduled_samples']]) }}"
                                    >
                                        <i class="mdi mdi-alert-circle"></i> {{ __('planner.partial') }}
                                    </span>
                                @else
                                    <span class="kpi-badge kpi-badge-pending">
                                        <i class="mdi mdi-clock-outline"></i> Pending
                                    </span>
                                @endif
                                @if($row['collected_at'])
                                    <div class="text-muted mt-1" style="font-size:0.72rem;">{{ $row['collected_at'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="mdi mdi-chart-box-outline" style="font-size:2.5rem;opacity:0.4;"></i>
                                <p class="mb-0 mt-2">{{ __('planner.no_records_match') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 text-muted small">
        <i class="mdi mdi-information-outline mr-1"></i>
        <span class="scheduled" style="font-weight:600;">Blue</span> = scheduled values &nbsp;·&nbsp;
        <span class="collected" style="font-weight:600;">Green</span> = collected values
    </div>
</div>
