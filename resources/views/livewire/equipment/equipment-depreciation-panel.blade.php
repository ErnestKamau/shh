<div class="equipment-depreciation-panel">
    <style>
        .equipment-depreciation-panel .dep-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }
        .equipment-depreciation-panel .dep-kpi-card {
            background: linear-gradient(145deg, #ffffff 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .equipment-depreciation-panel .dep-kpi-card.is-primary {
            border-color: #93c5fd;
            background: linear-gradient(145deg, #eff6ff 0%, #ffffff 100%);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
        }
        .equipment-depreciation-panel .dep-kpi-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .equipment-depreciation-panel .dep-kpi-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .equipment-depreciation-panel .dep-kpi-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 4px;
        }
        .equipment-depreciation-panel .dep-frequency-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .equipment-depreciation-panel .dep-frequency-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            flex: 1;
            min-width: 0;
        }
        .equipment-depreciation-panel .dep-frequency-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            flex-shrink: 0;
        }
        .equipment-depreciation-panel .dep-frequency-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .equipment-depreciation-panel .dep-frequency-tab:hover {
            background: #fff;
            border-color: #c7d7fc;
            color: #1e293b;
        }
        .equipment-depreciation-panel .dep-frequency-tab.is-active {
            background: #fff;
            border-color: #3b5fc0;
            color: #1d4ed8;
            box-shadow: 0 1px 4px rgba(59, 95, 192, 0.15);
        }
        .equipment-depreciation-panel .dep-content-shell {
            background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }
        .equipment-depreciation-panel .dep-section-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .equipment-depreciation-panel .dep-section-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.75);
            color: #475569;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        }
        .equipment-depreciation-panel .dep-section-tab:hover {
            background: #fff;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .equipment-depreciation-panel .dep-section-tab.is-active {
            background: #fff;
            border-color: #3b82f6;
            color: #1d4ed8;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.12);
        }
        .equipment-depreciation-panel .dep-section-tab i {
            font-size: 1rem;
            opacity: 0.85;
        }
        .equipment-depreciation-panel .dep-widget {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .equipment-depreciation-panel .dep-widget-toolbar {
            padding: 12px 16px;
            background: rgba(248, 250, 252, 0.9);
            border-bottom: 1px solid #e8edf3;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.875rem;
        }
        .equipment-depreciation-panel .dep-widget-body {
            background: rgba(255, 255, 255, 0.65);
        }
        .equipment-depreciation-panel .dep-widget-footer {
            padding: 10px 16px;
            background: rgba(248, 250, 252, 0.95);
            border-top: 1px solid #e8edf3;
        }
        .equipment-depreciation-panel .dep-table thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            border-top: none;
            background: #f8fafc;
        }
        .equipment-depreciation-panel .dep-row-current {
            background: #eff6ff !important;
            box-shadow: inset 3px 0 0 #2563eb;
        }
        .equipment-depreciation-panel .dep-row-current td {
            font-weight: 600;
            color: #1e3a8a;
        }
        .equipment-depreciation-panel .dep-badge-current {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background: #2563eb;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }
        .dep-appraisal-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1050;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .dep-appraisal-modal {
            width: 100%;
            max-width: 520px;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
            border: 1px solid #e2e8f0;
        }
        .dep-appraisal-modal__header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #ffffff 100%);
            border-bottom: 1px solid #d1fae5;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .dep-appraisal-modal__title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
        }
        .dep-appraisal-modal__subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
        }
        .dep-appraisal-modal__close {
            border: none;
            background: rgba(255, 255, 255, 0.8);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: #64748b;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }
        .dep-appraisal-modal__close:hover {
            background: #fff;
            color: #0f172a;
        }
        .dep-appraisal-modal__body {
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .dep-appraisal-value-flow {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
            align-items: center;
            margin-bottom: 1.25rem;
            padding: 14px;
            background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .dep-appraisal-value-box {
            text-align: center;
        }
        .dep-appraisal-value-box__label {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .dep-appraisal-value-box__amount {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }
        .dep-appraisal-value-box__amount.is-new {
            color: #15803d;
        }
        .dep-appraisal-value-box__amount.is-muted {
            color: #94a3b8;
            font-weight: 500;
        }
        .dep-appraisal-value-arrow {
            color: #22c55e;
            font-size: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .dep-appraisal-value-change {
            font-size: 0.7rem;
            font-weight: 600;
            color: #16a34a;
        }
        .dep-appraisal-value-change.is-negative {
            color: #dc2626;
        }
        .dep-appraisal-modal .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .dep-appraisal-modal .form-control {
            border-radius: 10px;
            border-color: #e2e8f0;
        }
        .dep-appraisal-modal .form-control:focus {
            border-color: #86efac;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
        }
        .dep-appraisal-modal .form-control[readonly] {
            background: #f8fafc;
            color: #475569;
        }
        .dep-appraisal-modal__footer {
            padding: 1rem 1.5rem 1.25rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
    </style>

    @if(session('depreciation_message'))
        <div class="alert alert-success">{{ session('depreciation_message') }}</div>
    @endif
    @if(session('depreciation_error'))
        <div class="alert alert-danger">{{ session('depreciation_error') }}</div>
    @endif

    @php $config = $this->config; @endphp

    @if(!$config || !$config->enable_depreciation)
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Asset depreciation is not enabled for this equipment. Edit the equipment and configure step 5 (Asset Depreciation).
        </div>
    @else
        <h5 class="mb-3 fw-bold text-dark"><i class="mdi mdi-finance text-primary"></i> Asset Depreciation</h5>

        {{-- KPI summary --}}
        <div class="dep-kpi-grid mb-4">
            <div class="dep-kpi-card is-primary">
                <div class="dep-kpi-label">Current Book Value</div>
                <div class="dep-kpi-value">{{ number_format((float) ($config->current_book_value ?? 0), 2) }}</div>
                <div class="dep-kpi-sub">{{ $config->currency }}</div>
            </div>
            <div class="dep-kpi-card">
                <div class="dep-kpi-label">Capitalized</div>
                <div class="dep-kpi-value">{{ number_format((float) $config->capitalized_amount, 2) }}</div>
                <div class="dep-kpi-sub">{{ $config->currency }}</div>
            </div>
            <div class="dep-kpi-card">
                <div class="dep-kpi-label">Accumulated</div>
                <div class="dep-kpi-value">{{ number_format((float) $config->accumulated_depreciation, 2) }}</div>
            </div>
            <div class="dep-kpi-card">
                <div class="dep-kpi-label">This Period</div>
                <div class="dep-kpi-value">{{ number_format((float) $config->current_period_depreciation, 2) }}</div>
            </div>
            <div class="dep-kpi-card">
                <div class="dep-kpi-label">Method</div>
                <div class="dep-kpi-value" style="font-size:0.95rem;">{{ $config->method?->name ?? '—' }}</div>
            </div>
            <div class="dep-kpi-card">
                <div class="dep-kpi-label">Status</div>
                <div class="dep-kpi-value" style="font-size:0.9rem;">
                    <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $config->status?->value ?? $config->status)) }}</span>
                </div>
                <div class="dep-kpi-sub">Useful life: {{ $config->useful_life_years }} yrs · Salvage {{ number_format((float) $config->salvage_value, 2) }}</div>
            </div>
        </div>

        {{-- Frequency tabs + actions --}}
        <div class="dep-frequency-bar">
            <div class="dep-frequency-tabs" role="tablist" aria-label="Depreciation frequency">
                @foreach($config->resolvedFrequencies() as $freq)
                    <button type="button"
                            role="tab"
                            class="dep-frequency-tab {{ $activeFrequency === $freq ? 'is-active' : '' }}"
                            wire:click="setActiveFrequency('{{ $freq }}')"
                            aria-selected="{{ $activeFrequency === $freq ? 'true' : 'false' }}">
                        {{ ucfirst($freq) }}
                    </button>
                @endforeach
            </div>
            <div class="dep-frequency-actions">
                @can('equipment.components.depreciation.recalculate')
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="recalculateSchedule">
                        <i class="mdi mdi-refresh"></i> Recalculate
                    </button>
                @endcan
                @can('equipment.components.depreciation.appraisal.create')
                    <button type="button" class="btn btn-outline-success btn-sm" wire:click="openAppraisalModal">
                        <i class="mdi mdi-arrow-up-bold"></i> New Appraisal
                    </button>
                @endcan
            </div>
        </div>

        <div class="dep-content-shell">
            {{-- Section badge tabs --}}
            <div class="dep-section-tabs" role="tablist" aria-label="Depreciation views">
                <button type="button" role="tab"
                        class="dep-section-tab {{ $activeSection === 'schedule' ? 'is-active' : '' }}"
                        wire:click="setActiveSection('schedule')"
                        aria-selected="{{ $activeSection === 'schedule' ? 'true' : 'false' }}">
                    <i class="mdi mdi-table-large"></i> Depreciation Schedule
                </button>
                <button type="button" role="tab"
                        class="dep-section-tab {{ $activeSection === 'analysis' ? 'is-active' : '' }}"
                        wire:click="setActiveSection('analysis')"
                        aria-selected="{{ $activeSection === 'analysis' ? 'true' : 'false' }}">
                    <i class="mdi mdi-chart-line"></i> Period Analysis
                </button>
                <button type="button" role="tab"
                        class="dep-section-tab {{ $activeSection === 'yearly' ? 'is-active' : '' }}"
                        wire:click="setActiveSection('yearly')"
                        aria-selected="{{ $activeSection === 'yearly' ? 'true' : 'false' }}">
                    <i class="mdi mdi-calendar-range"></i> Yearly Summary
                </button>
                <button type="button" role="tab"
                        class="dep-section-tab {{ $activeSection === 'timeline' ? 'is-active' : '' }}"
                        wire:click="setActiveSection('timeline')"
                        aria-selected="{{ $activeSection === 'timeline' ? 'true' : 'false' }}">
                    <i class="mdi mdi-timeline-clock-outline"></i> Lifecycle Timeline
                </button>
                <button type="button" role="tab"
                        class="dep-section-tab {{ $activeSection === 'appraisals' ? 'is-active' : '' }}"
                        wire:click="setActiveSection('appraisals')"
                        aria-selected="{{ $activeSection === 'appraisals' ? 'true' : 'false' }}">
                    <i class="mdi mdi-clipboard-text-search-outline"></i> Appraisal History
                </button>
            </div>

            @if($activeSection === 'schedule')
                <div class="dep-widget">
                    <div class="dep-widget-toolbar d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                        <span>Depreciation Schedule <small class="text-muted fw-normal">({{ ucfirst($activeFrequency) }})</small></span>
                        @if($versions->isNotEmpty())
                            <select class="form-control form-control-sm" style="max-width:280px" wire:model.live="selectedVersionId">
                                <option value="">Active version</option>
                                @foreach($versions as $v)
                                    <option value="{{ $v->id }}">v{{ $v->version_number }} — {{ $v->reason }} {{ $v->is_archived ? '(archived)' : '' }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @if($schedules)
                        <div class="dep-widget-body table-responsive">
                            <table class="table table-sm mb-0 dep-table">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Period</th><th>Date</th><th>Opening</th><th>Amount</th><th>Accumulated</th><th>Closing</th><th>Posted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $s)
                                        <tr class="{{ $this->isCurrentSchedulePeriod($s) ? 'dep-row-current' : '' }}">
                                            <td>{{ $s->period_index + 1 }}</td>
                                            <td>
                                                {{ $s->period_label }}
                                                @if($this->isCurrentSchedulePeriod($s))
                                                    <span class="dep-badge-current">Current</span>
                                                @endif
                                            </td>
                                            <td>{{ $s->period_date->format('Y-m-d') }}</td>
                                            <td>{{ number_format((float) $s->opening_book_value, 2) }}</td>
                                            <td>{{ number_format((float) $s->depreciation_amount, 2) }}</td>
                                            <td>{{ number_format((float) $s->accumulated_depreciation, 2) }}</td>
                                            <td>{{ number_format((float) $s->closing_book_value, 2) }}</td>
                                            <td>@if($s->is_posted)<span class="badge badge-success">Yes</span>@else<span class="badge badge-light border">No</span>@endif</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="dep-widget-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <div class="d-flex align-items-center">
                                <label for="depPanelPerPage" class="form-label mb-0 me-2 text-muted small">Show:</label>
                                <select wire:model.live="perPage" id="depPanelPerPage" class="form-select form-select-sm" style="width:auto;">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="75">75</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            {{ $schedules->links() }}
                        </div>
                    @else
                        <div class="dep-widget-body p-4 text-muted">Schedule not generated yet.</div>
                    @endif
                </div>
            @endif

            @if($activeSection === 'analysis')
                <div class="dep-widget">
                    <div class="dep-widget-toolbar d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                        <span>Period Analysis <small class="text-muted fw-normal">({{ ucfirst($activeFrequency) }})</small></span>
                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <input type="number" class="form-control form-control-sm" style="width:100px" wire:model.live="analysisYear" placeholder="Year">
                            <select class="form-control form-control-sm" wire:model.live="analysisQuarter" style="width:120px">
                                <option value="">All quarters</option>
                                <option value="1">Q1</option>
                                <option value="2">Q2</option>
                                <option value="3">Q3</option>
                                <option value="4">Q4</option>
                            </select>
                        </div>
                    </div>
                    <div class="dep-widget-body table-responsive">
                        <table class="table table-sm mb-0 dep-table">
                            <thead><tr><th>Period</th><th>Opening</th><th>Depreciation</th><th>Closing</th></tr></thead>
                            <tbody>
                                @forelse($periodAnalysis ?? [] as $row)
                                    <tr class="{{ $this->isCurrentSchedulePeriod($row) ? 'dep-row-current' : '' }}">
                                        <td>
                                            {{ $row->period_label }}
                                            @if($this->isCurrentSchedulePeriod($row))
                                                <span class="dep-badge-current">Current</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format((float) $row->opening_book_value, 2) }}</td>
                                        <td>{{ number_format((float) $row->depreciation_amount, 2) }}</td>
                                        <td>{{ number_format((float) $row->closing_book_value, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center py-3">No schedule data for this frequency.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($periodAnalysis && $periodAnalysis->hasPages())
                        <div class="dep-widget-footer">{{ $periodAnalysis->links() }}</div>
                    @endif
                </div>
            @endif

            @if($activeSection === 'yearly')
                <div class="dep-widget">
                    <div class="dep-widget-toolbar">
                        Yearly Summary <small class="text-muted fw-normal">({{ ucfirst($activeFrequency) }})</small>
                    </div>
                    <div class="dep-widget-body table-responsive">
                        <table class="table table-sm mb-0 dep-table">
                            <thead><tr><th>Year</th><th>Annual Depreciation</th><th>Year-end Book Value</th><th>Accumulated</th></tr></thead>
                            <tbody>
                                @forelse($this->yearlyAnalysis as $row)
                                    <tr>
                                        <td>{{ $row['year'] }}</td>
                                        <td>{{ number_format((float) $row['depreciation'], 2) }}</td>
                                        <td>{{ number_format((float) $row['closing_value'], 2) }}</td>
                                        <td>{{ number_format((float) $row['accumulated'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center py-3">No yearly data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($activeSection === 'timeline')
                <div class="dep-widget">
                    <div class="dep-widget-toolbar">Lifecycle Timeline</div>
                    <div class="dep-widget-body p-3">
                        @forelse($this->timelineEvents as $event)
                            <div class="d-flex align-items-start mb-2">
                                <span class="badge badge-light border mr-2">{{ $event['date'] }}</span>
                                <span>{{ $event['label'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No timeline events yet.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            @if($activeSection === 'appraisals')
                <div class="dep-widget">
                    <div class="dep-widget-toolbar">Appraisal History</div>
                    <div class="dep-widget-body table-responsive">
                        <table class="table table-sm mb-0 dep-table">
                            <thead>
                                <tr><th>Date</th><th>Prior</th><th>New</th><th>Life +</th><th>Reason</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($this->equipment->appraisals as $appraisal)
                                    <tr>
                                        <td>{{ $appraisal->appraisal_date->format('Y-m-d') }}</td>
                                        <td>{{ number_format((float) $appraisal->prior_book_value, 2) }}</td>
                                        <td>{{ number_format((float) $appraisal->new_appraised_value, 2) }}</td>
                                        <td>{{ $appraisal->useful_life_extension_years }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($appraisal->reason, 40) }}</td>
                                        <td><span class="badge badge-secondary">{{ $appraisal->status->value }}</span></td>
                                        <td>
                                            @can('equipment.components.depreciation.appraisal.approve')
                                                @if($appraisal->status === \App\Enums\Equipment\AppraisalStatus::Pending)
                                                    <button type="button" class="btn btn-success btn-xs btn-sm" wire:click="approveAppraisal('{{ $appraisal->id }}')">Approve</button>
                                                    <button type="button" class="btn btn-outline-danger btn-xs btn-sm" wire:click="rejectAppraisal('{{ $appraisal->id }}')">Reject</button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-muted text-center py-3">No appraisals recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($showAppraisalModal && $config)
        <div class="dep-appraisal-modal-backdrop" wire:keydown.escape.window="$set('showAppraisalModal', false)">
            <div class="dep-appraisal-modal" role="dialog" aria-labelledby="depAppraisalModalTitle" aria-modal="true">
                <div class="dep-appraisal-modal__header">
                    <div>
                        <h2 class="dep-appraisal-modal__title" id="depAppraisalModalTitle">
                            <i class="mdi mdi-arrow-up-bold-circle text-success"></i> Create Appraisal
                        </h2>
                        <p class="dep-appraisal-modal__subtitle">Submit a revaluation for approval. Depreciation will recalculate after approval.</p>
                    </div>
                    <button type="button" class="dep-appraisal-modal__close" wire:click="$set('showAppraisalModal', false)" aria-label="Close">&times;</button>
                </div>
                <div class="dep-appraisal-modal__body">
                    <div class="dep-appraisal-value-flow">
                        <div class="dep-appraisal-value-box">
                            <div class="dep-appraisal-value-box__label">Current Book Value</div>
                            <div class="dep-appraisal-value-box__amount">
                                {{ number_format($this->appraisalCurrentBookValue, 2) }}
                                <small class="text-muted">{{ $config->currency }}</small>
                            </div>
                        </div>
                        <div class="dep-appraisal-value-arrow" aria-hidden="true">
                            <i class="mdi mdi-arrow-up-bold"></i>
                            @if($this->appraisalValueChange !== null)
                                <span class="dep-appraisal-value-change {{ $this->appraisalValueChange < 0 ? 'is-negative' : '' }}">
                                    {{ $this->appraisalValueChange >= 0 ? '+' : '' }}{{ number_format($this->appraisalValueChange, 2) }}
                                </span>
                            @endif
                        </div>
                        <div class="dep-appraisal-value-box">
                            <div class="dep-appraisal-value-box__label">New Book Value</div>
                            <div class="dep-appraisal-value-box__amount {{ $this->appraisalNewBookValue !== null ? 'is-new' : 'is-muted' }}">
                                @if($this->appraisalNewBookValue !== null)
                                    {{ number_format($this->appraisalNewBookValue, 2) }}
                                    <small class="text-muted">{{ $config->currency }}</small>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="appraisalDate">Appraisal Date</label>
                        <input type="date" id="appraisalDate" class="form-control" wire:model="appraisalForm.appraisal_date">
                        @error('appraisalForm.appraisal_date') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="appraisalCurrentBook">Current Book Value ({{ $config->currency }})</label>
                        <input type="text" id="appraisalCurrentBook" class="form-control" readonly
                               value="{{ number_format($this->appraisalCurrentBookValue, 2, '.', '') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="appraisalValue">Appraisal Value ({{ $config->currency }})</label>
                        <input type="number" step="0.01" min="0" id="appraisalValue" class="form-control"
                               wire:model.live="appraisalForm.appraisal_value" placeholder="Amount to add to current book value">
                        <small class="text-muted">Added to current book value to calculate the new book value when approved.</small>
                        @error('appraisalForm.appraisal_value') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="appraisalNewBook">New Book Value ({{ $config->currency }})</label>
                        <input type="text" id="appraisalNewBook" class="form-control" readonly
                               value="{{ $this->appraisalNewBookValue !== null ? number_format($this->appraisalNewBookValue, 2, '.', '') : '' }}"
                               placeholder="Current book value + appraisal value">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" for="appraisalLifeExt">Useful Life Extension (years)</label>
                                <input type="number" id="appraisalLifeExt" class="form-control" wire:model="appraisalForm.useful_life_extension_years" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" for="appraisalReason">Reason</label>
                                <input type="text" id="appraisalReason" class="form-control" wire:model="appraisalForm.reason" placeholder="e.g. Market revaluation">
                                @error('appraisalForm.reason') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label" for="appraisalNotes">Notes</label>
                        <textarea id="appraisalNotes" class="form-control" rows="3" wire:model="appraisalForm.notes" placeholder="Optional supporting details"></textarea>
                    </div>
                </div>
                <div class="dep-appraisal-modal__footer">
                    <button type="button" class="btn btn-light btn-sm" wire:click="$set('showAppraisalModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" wire:click="saveAppraisal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAppraisal"><i class="mdi mdi-check"></i> Submit for Approval</span>
                        <span wire:loading wire:target="saveAppraisal">Submitting…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
