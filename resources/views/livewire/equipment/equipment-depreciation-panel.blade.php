<div>
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
            <h5 class="mb-0"><i class="mdi mdi-finance"></i> Asset Depreciation</h5>
            <div>
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

        {{-- Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Purchase / Capitalized</small>
                        <strong>{{ number_format((float) $config->capitalized_amount, 2) }} {{ $config->currency }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Current Book Value</small>
                        <strong>{{ number_format((float) ($config->current_book_value ?? 0), 2) }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Accumulated Depreciation</small>
                        <strong>{{ number_format((float) $config->accumulated_depreciation, 2) }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Method</small>
                        <strong>{{ $config->method?->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Frequencies</small>
                        <strong>{{ $config->frequenciesLabel() }}</strong>
                        <small class="text-muted d-block">Summary uses {{ ucfirst($config->resolvedFrequencies()[0] ?? 'monthly') }} (primary)</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Useful Life</small>
                        <strong>{{ $config->useful_life_years }} years</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Salvage Value</small>
                        <strong>{{ number_format((float) $config->salvage_value, 2) }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Current Period Depreciation</small>
                        <strong>{{ number_format((float) $config->current_period_depreciation, 2) }}</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $config->status?->value ?? $config->status)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Lifecycle Timeline</strong></div>
            <div class="card-body">
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

        {{-- Monthly analysis --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <strong>Monthly Depreciation Analysis</strong>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <select class="form-control form-control-sm" wire:model.live="scheduleFrequencyFilter" style="width:130px">
                        <option value="">All frequencies</option>
                        @foreach($config->resolvedFrequencies() as $freq)
                            <option value="{{ $freq }}">{{ ucfirst($freq) }}</option>
                        @endforeach
                    </select>
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
                <table class="table table-sm mb-0">
                    <thead><tr><th>Frequency</th><th>Period</th><th>Opening</th><th>Depreciation</th><th>Closing</th></tr></thead>
                    <tbody>
                        @forelse($this->monthlyAnalysis as $row)
                            <tr>
                                <td><span class="badge badge-light border">{{ ucfirst($row->frequency ?? '—') }}</span></td>
                                <td>{{ $row->period_label }}</td>
                                <td>{{ number_format((float) $row->opening_book_value, 2) }}</td>
                                <td>{{ number_format((float) $row->depreciation_amount, 2) }}</td>
                                <td>{{ number_format((float) $row->closing_book_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center">No schedule data. Run recalculation or wait for queue processing.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Yearly --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Yearly Depreciation Analysis</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
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
                            <tr><td colspan="4" class="text-muted text-center">No yearly data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Schedule viewer --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <strong>Depreciation Schedule</strong>
                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    <select class="form-control form-control-sm" wire:model.live="scheduleFrequencyFilter" style="max-width:140px">
                        <option value="">All frequencies</option>
                        @foreach($config->resolvedFrequencies() as $freq)
                            <option value="{{ $freq }}">{{ ucfirst($freq) }}</option>
                        @endforeach
                    </select>
                    @if($versions->isNotEmpty())
                        <select class="form-control form-control-sm" style="max-width:280px" wire:model.live="selectedVersionId">
                            <option value="">Active version</option>
                            @foreach($versions as $v)
                                <option value="{{ $v->id }}">v{{ $v->version_number }} — {{ $v->reason }} {{ $v->is_archived ? '(archived)' : '' }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
            @if($schedules)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th><th>Frequency</th><th>Period</th><th>Date</th><th>Opening</th><th>Amount</th><th>Accumulated</th><th>Closing</th><th>Posted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $s)
                                <tr>
                                    <td>{{ $s->period_index + 1 }}</td>
                                    <td>{{ ucfirst($s->frequency ?? '—') }}</td>
                                    <td>{{ $s->period_label }}</td>
                                    <td>{{ $s->period_date->format('Y-m-d') }}</td>
                                    <td>{{ number_format((float) $s->opening_book_value, 2) }}</td>
                                    <td>{{ number_format((float) $s->depreciation_amount, 2) }}</td>
                                    <td>{{ number_format((float) $s->accumulated_depreciation, 2) }}</td>
                                    <td>{{ number_format((float) $s->closing_book_value, 2) }}</td>
                                    <td>@if($s->is_posted)<span class="badge badge-success">Yes</span>@else<span class="badge badge-light">No</span>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $schedules->links() }}</div>
            @else
                <div class="card-body text-muted">Schedule not generated yet.</div>
            @endif
        </div>

        {{-- Appraisals --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Appraisal History</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
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
                            <tr><td colspan="7" class="text-muted text-center">No appraisals recorded.</td></tr>
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
