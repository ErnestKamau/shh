<div class="container-fluid depreciation-reports-page">
    <style>
        .depreciation-reports-page .depreciation-report-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }
        .depreciation-reports-page .depreciation-report-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
        }
        .depreciation-reports-page .depreciation-report-tab:hover:not(:disabled) {
            background: #fff;
            border-color: #c7d7fc;
            color: #1e293b;
        }
        .depreciation-reports-page .depreciation-report-tab.is-active {
            background: #fff;
            border-color: #3b5fc0;
            color: #1d4ed8;
            box-shadow: 0 1px 4px rgba(59, 95, 192, 0.15);
        }
        .depreciation-reports-page .depreciation-report-tab:disabled {
            opacity: 0.6;
            cursor: wait;
        }
        .depreciation-reports-page .depreciation-report-tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #475569;
        }
        .depreciation-reports-page .depreciation-report-tab.is-active .depreciation-report-tab-badge {
            background: #eff6ff;
            color: #1d4ed8;
        }
    </style>

    @include('livewire.equipment.depreciation.partials.page-header', [
        'icon' => 'mdi-file-chart',
        'title' => 'Depreciation Reports',
        'description' => 'Generate financial depreciation and asset valuation reports',
    ])

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="depreciation-report-tabs mb-3" role="tablist">
                        @foreach($this->reportTypes as $typeKey => $typeLabel)
                            <button type="button"
                                    role="tab"
                                    class="depreciation-report-tab {{ $reportType === $typeKey ? 'is-active' : '' }}"
                                    wire:click="switchReportType('{{ $typeKey }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="switchReportType,loadReport,applyPeriodFilters"
                                    aria-selected="{{ $reportType === $typeKey ? 'true' : 'false' }}">
                                {{ $typeLabel }}
                                @if($reportType === $typeKey && $reportData !== null)
                                    <span class="depreciation-report-tab-badge">{{ $this->recordCount }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    @if(in_array($reportType, ['monthly', 'yearly']))
                        <div class="row align-items-end g-2 pt-2 border-top">
                            @if(in_array($reportType, ['monthly', 'yearly']))
                                <div class="col-auto">
                                    <label class="form-label fw-bold mb-1">Year</label>
                                    <input type="number" class="form-control form-control-sm" wire:model="year" style="width: 100px;">
                                </div>
                            @endif
                            @if($reportType === 'monthly')
                                <div class="col-auto">
                                    <label class="form-label fw-bold mb-1">Month</label>
                                    <input type="number" class="form-control form-control-sm" wire:model="month" min="1" max="12" style="width: 80px;">
                                </div>
                            @endif
                            <div class="col-auto">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        wire:click="applyPeriodFilters"
                                        wire:loading.attr="disabled"
                                        wire:target="applyPeriodFilters,loadReport">
                                    <i class="mdi mdi-refresh"></i> Apply
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap" style="border-radius: 15px 15px 0 0; gap: 8px;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-table"></i>
                        {{ $this->reportTypes[$reportType] ?? 'Report' }}
                    </h6>
                    @can('equipment.components.depreciation.export')
                        @if($reportData !== null)
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                        @endif
                    @endcan
                </div>
                <div class="card-body p-4 position-relative" wire:loading.class="opacity-50" wire:target="switchReportType,loadReport,applyPeriodFilters">
                    <div wire:loading.flex wire:target="switchReportType,loadReport,applyPeriodFilters"
                         class="position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center"
                         style="z-index: 2; background: rgba(255,255,255,0.7); border-radius: 12px;">
                        <div class="text-center text-primary">
                            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                            <div class="small fw-bold">Loading report…</div>
                        </div>
                    </div>

                    @if($reportData !== null)
                        @if($reportType === 'yearly' && isset($reportData['summary']))
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <tbody>
                                        @foreach($reportData['summary'] as $key => $value)
                                            <tr>
                                                <th class="text-muted" style="width: 220px;">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                <td>{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif(!empty($reportData['rows']))
                            <p class="text-muted mb-3">{{ count($reportData['rows']) }} record(s)</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead style="background-color: rgba(0, 0, 0, .03);">
                                        <tr><th style="width: 60px;">#</th><th>Details</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['rows'] as $i => $row)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td><pre class="mb-0 small" style="max-height: 200px; overflow: auto;">{{ json_encode($row instanceof \Illuminate\Database\Eloquent\Model ? $row->toArray() : (array) $row, JSON_PRETTY_PRINT) }}</pre></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0 text-center py-4">No data for the selected criteria.</p>
                        @endif
                    @else
                        <p class="text-muted mb-0 text-center py-4">Select a report type to view data.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
