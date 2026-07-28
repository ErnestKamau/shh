<div class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <a href="{{ route('shelf-life.studies.index') }}" class="text-muted small d-inline-block mb-2">
                                <i class="mdi mdi-arrow-left"></i> Back to studies
                            </a>
                            <h2 class="mb-1">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                {{ $study->code }} — {{ $study->title }}
                            </h2>
                            <p class="text-muted mb-0">
                                Job {{ $study->sampleHeader?->batch_code ?? '—' }}
                                · {{ $study->customer?->name ?? '—' }}
                                · {{ $study->pullPoints->count() }} pull point(s)
                                · {{ $samples->count() }} sample(s)
                            </p>
                        </div>
                        <div class="mt-2">
                            <span class="badge badge-{{ $study->isAccelerated() ? 'warning' : 'info' }} p-2">
                                {{ $study->studyTypeLabel() }}
                            </span>
                            <span class="badge badge-secondary p-2">{{ ucfirst(str_replace('_', ' ', $study->status)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'definition' ? 'active' : '' }}" wire:click="setTab('definition')">Definition</button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'specs' ? 'active' : '' }}" wire:click="setTab('specs')">Parameter specs</button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'pulls' ? 'active' : '' }}" wire:click="setTab('pulls')">Pull schedule</button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'results' ? 'active' : '' }}" wire:click="setTab('results')">Pull results</button>
        </li>
    </ul>

    @if($activeTab === 'definition')
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" wire:model="title">
                            @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Study type</label>
                            <select class="form-control" wire:model="studyType">
                                <option value="real_time">Real-time</option>
                                <option value="accelerated">Accelerated</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Storage temp (°C)</label>
                            <input type="number" step="0.01" class="form-control" wire:model="storageTempC">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Storage RH (%)</label>
                            <input type="number" step="0.01" class="form-control" wire:model="storageRhPercent">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Storage condition label</label>
                            <input type="text" class="form-control" wire:model="storageConditionLabel" placeholder="e.g. 40°C / 75% RH">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Target duration</label>
                            <input type="number" min="0" class="form-control" wire:model="targetDurationValue">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Duration unit</label>
                            <select class="form-control" wire:model="targetDurationUnit">
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start date</label>
                            <input type="date" class="form-control" wire:model="startDate">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" wire:model="status">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On hold</option>
                                <option value="completed">Completed</option>
                                <option value="aborted">Aborted</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Batch / lot no.</label>
                            <input type="text" class="form-control" wire:model="batchLotNo">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mfg date</label>
                            <input type="date" class="form-control" wire:model="mfgDate">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" wire:click="saveDefinition">
                    <i class="mdi mdi-content-save"></i> Save definition
                </button>
            </div>
        </div>
    @endif

    @if($activeTab === 'specs')
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
            <div class="card-body p-4">
                <h5 class="mb-3">Add parameter specification</h5>
                <div class="row">
                    <div class="col-md-4">
                        <label>Analyte</label>
                        <select class="form-control" wire:model="specAnalyteId">
                            <option value="">Select analyte…</option>
                            @foreach($analytes as $analyte)
                                <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                            @endforeach
                        </select>
                        @error('specAnalyteId') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label>Method</label>
                        <select class="form-control" wire:model="specMethodId">
                            <option value="">Optional</option>
                            @foreach($methods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Reporting unit</label>
                        <select class="form-control" wire:model="specReportingUnitId">
                            <option value="">Optional</option>
                            @foreach($reportingUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mt-2">
                        <label>Spec type</label>
                        <select class="form-control" wire:model="specType">
                            <option value="range">Range</option>
                            <option value="max">Max</option>
                            <option value="min">Min</option>
                            <option value="delta_from_baseline">Delta from baseline</option>
                            <option value="panel_score_max">Panel score max</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-2">
                        <label>Low</label>
                        <input type="number" step="any" class="form-control" wire:model="specLow">
                    </div>
                    <div class="col-md-2 mt-2">
                        <label>High</label>
                        <input type="number" step="any" class="form-control" wire:model="specHigh">
                    </div>
                    <div class="col-md-2 mt-2">
                        <label>Safety margin %</label>
                        <input type="number" step="0.01" class="form-control" wire:model="specSafetyMargin">
                    </div>
                    <div class="col-md-3 mt-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" wire:click="addParameterSpec">Add spec</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Method</th>
                                <th>Unit</th>
                                <th>Type</th>
                                <th>Low</th>
                                <th>High</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($study->parameterSpecs as $spec)
                                <tr>
                                    <td>{{ $spec->parameter_label }}</td>
                                    <td>{{ $spec->method?->name ?? '—' }}</td>
                                    <td>{{ $spec->reportingUnit?->name ?? '—' }}</td>
                                    <td>{{ str_replace('_', ' ', $spec->spec_type) }}</td>
                                    <td>{{ $spec->spec_low ?? '—' }}</td>
                                    <td>{{ $spec->spec_high ?? '—' }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeParameterSpec('{{ $spec->id }}')">Remove</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-center py-3">No parameter specs yet. These define pass/fail for the shelf-life claim.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'pulls')
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h5 class="mb-0">Pull schedule</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="generateDefaultPullSchedule">
                        Generate default (0–12 months)
                    </button>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>Label</label>
                        <input type="text" class="form-control" wire:model="manualPullLabel" placeholder="Month 3">
                    </div>
                    <div class="col-md-2">
                        <label>Offset</label>
                        <input type="number" min="0" class="form-control" wire:model="manualPullOffset">
                    </div>
                    <div class="col-md-2">
                        <label>Unit</label>
                        <select class="form-control" wire:model="manualPullUnit">
                            <option value="days">Days</option>
                            <option value="weeks">Weeks</option>
                            <option value="months">Months</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="custom-control custom-checkbox mt-4">
                            <input type="checkbox" class="custom-control-input" id="manual-baseline" wire:model="manualPullIsBaseline">
                            <label class="custom-control-label" for="manual-baseline">Baseline (T0)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary" wire:click="addManualPullPoint">Add pull point</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Offset</th>
                                <th>Scheduled</th>
                                <th>Actual pull</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($study->pullPoints as $pull)
                                <tr>
                                    <td>
                                        {{ $pull->label }}
                                        @if($pull->is_baseline)
                                            <span class="badge badge-light">Baseline</span>
                                        @endif
                                    </td>
                                    <td>{{ $pull->offset_value }} {{ $pull->offset_unit }}</td>
                                    <td>{{ optional($pull->scheduled_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ optional($pull->actual_pull_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $pull->status }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-outline-success" wire:click="selectPullPoint('{{ $pull->id }}')">Capture results</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removePullPoint('{{ $pull->id }}')">Remove</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted text-center py-3">No pull points yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    Each pull uses the same physical samples from this job: pull from storage → test → record → put back.
                </p>
            </div>
        </div>
    @endif

    @if($activeTab === 'results')
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="form-group">
                    <label>Pull point</label>
                    <select class="form-control" wire:model.live="selectedPullPointId">
                        <option value="">Select pull point…</option>
                        @foreach($study->pullPoints as $pull)
                            <option value="{{ $pull->id }}">{{ $pull->label }} ({{ optional($pull->scheduled_date)->format('Y-m-d') }})</option>
                        @endforeach
                    </select>
                </div>

                @if($selectedPullPoint && $study->parameterSpecs->isNotEmpty() && $samples->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Sample</th>
                                    @foreach($study->parameterSpecs as $spec)
                                        <th>{{ $spec->parameter_label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($samples as $sample)
                                    <tr>
                                        <td>{{ $sample->sample_code }}</td>
                                        @foreach($study->parameterSpecs as $spec)
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    wire:model.defer="resultInputs.{{ $sample->id }}.{{ $spec->analyte_id }}"
                                                >
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-primary" wire:click="savePullPointResults">
                        <i class="mdi mdi-content-save"></i> Save results for this pull
                    </button>
                @elseif($selectedPullPoint)
                    <div class="alert alert-info mb-0">
                        Add parameter specs and ensure the job has samples before capturing results.
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        Select a pull point to enter results for the study samples.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
