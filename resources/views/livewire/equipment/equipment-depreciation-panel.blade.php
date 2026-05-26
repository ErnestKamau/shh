<div class="equipment-depreciation-panel">
    <style>
        .equipment-depreciation-panel .dep-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }
        .equipment-depreciation-panel .dep-kpi-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
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
        .equipment-depreciation-panel .dep-frequency-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
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
        .equipment-depreciation-panel .dep-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 1.25rem;
        }
        .equipment-depreciation-panel .dep-section-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #1e293b;
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
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
            <h5 class="mb-0 fw-bold text-dark"><i class="mdi mdi-finance text-primary"></i> Asset Depreciation</h5>
            <div class="d-flex flex-wrap" style="gap:8px;">
                @can('equipment.components.depreciation.recalculate')
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="recalculateSchedule">
                        <i class="mdi mdi-refresh"></i> Recalculate
                    </button>
                @endcan
                @can('equipment.components.depreciation.appraisal.create')
                    <button type="button" class="btn btn-primary btn-sm" wire:click="openAppraisalModal">
                        <i class="mdi mdi-plus"></i> New Appraisal
                    </button>
                @endcan
            </div>
        </div>

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

        {{-- Frequency tabs --}}
        <div class="dep-frequency-tabs mb-4" role="tablist">
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

        {{-- Depreciation schedule --}}
        <div class="dep-section-card">
            <div class="dep-section-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
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
                <div class="table-responsive">
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
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
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
                <div class="p-4 text-muted">Schedule not generated yet.</div>
            @endif
        </div>

        {{-- Period analysis --}}
        <div class="dep-section-card">
            <div class="dep-section-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
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
            <div class="table-responsive">
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
                <div class="card-footer bg-white border-top">{{ $periodAnalysis->links() }}</div>
            @endif
        </div>

        {{-- Yearly analysis --}}
        <div class="dep-section-card">
            <div class="dep-section-header">Yearly Summary <small class="text-muted fw-normal">({{ ucfirst($activeFrequency) }})</small></div>
            <div class="table-responsive">
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

        {{-- Timeline --}}
        <div class="dep-section-card">
            <div class="dep-section-header">Lifecycle Timeline</div>
            <div class="p-3">
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

        {{-- Appraisals --}}
        <div class="dep-section-card">
            <div class="dep-section-header">Appraisal History</div>
            <div class="table-responsive">
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

    @if($showAppraisalModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Appraisal</h5>
                        <button type="button" class="close" wire:click="$set('showAppraisalModal', false)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Appraisal Date</label>
                            <input type="date" class="form-control" wire:model="appraisalForm.appraisal_date">
                            @error('appraisalForm.appraisal_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>New Appraised Value</label>
                            <input type="number" step="0.01" class="form-control" wire:model="appraisalForm.new_appraised_value">
                            @error('appraisalForm.new_appraised_value') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Useful Life Extension (years)</label>
                            <input type="number" class="form-control" wire:model="appraisalForm.useful_life_extension_years" min="0">
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <input type="text" class="form-control" wire:model="appraisalForm.reason">
                            @error('appraisalForm.reason') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" rows="3" wire:model="appraisalForm.notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showAppraisalModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveAppraisal">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
