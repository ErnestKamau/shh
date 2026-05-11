<div class="monitoring-shell container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-clipboard-pulse-outline text-primary"></i>
                                Monitoring
                            </h2>
                            <p class="text-muted mb-0">Track environmental conditions and equipment performance with dynamic formula-driven logs and audit-ready records.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4 mb-3">
            <button type="button" class="card monitor-entry-card w-100 text-left {{ $activeSection === 'environmental' ? 'active' : '' }}" wire:click="switchSection('environmental')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-info-soft">
                        <i class="mdi mdi-thermometer"></i>
                    </div>
                    <h5 class="mb-2">Environmental Monitoring</h5>
                    <p class="text-muted mb-0">Execution, daily logs, thresholds, and deviation alerts for laboratory conditions.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 mb-3">
            <button type="button" class="card monitor-entry-card w-100 text-left {{ $activeSection === 'equipment' ? 'active' : '' }}" wire:click="switchSection('equipment')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-success-soft">
                        <i class="mdi mdi-scale-balance"></i>
                    </div>
                    <h5 class="mb-2">Equipment Monitoring</h5>
                    <p class="text-muted mb-0">Intermediate checks, verification runs, dynamic calculations, and pass/fail controls.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 mb-3">
            <button type="button" class="card monitor-entry-card w-100 text-left {{ $activeSection === 'templates' ? 'active' : '' }}" wire:click="switchSection('templates')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-warning-soft">
                        <i class="mdi mdi-file-document-edit-outline"></i>
                    </div>
                    <h5 class="mb-2">Template Engine</h5>
                    <p class="text-muted mb-0">Template versions, variable logic, formula rules, and workflow-ready configurations.</p>
                </div>
            </button>
        </div>
    </div>

    <div class="card monitoring-main-card">
        <div class="card-body">
            @if(in_array($activeSection, ['environmental', 'equipment'], true))
                <div class="row mb-3">
                    <div class="col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong>Assigned Laboratories</strong>
                                <span class="badge badge-secondary">{{ $this->assignedLabs->count() }}</span>
                            </div>
                            <div class="list-group list-group-flush monitor-lab-list">
                                @forelse($this->assignedLabs as $lab)
                                    <button type="button"
                                            class="list-group-item list-group-item-action {{ $selectedLabId === $lab->id ? 'active' : '' }}"
                                            wire:click="selectLab('{{ $lab->id }}')">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $lab->name }}</span>
                                            <small class="text-muted">{{ $lab->code }}</small>
                                        </div>
                                    </button>
                                @empty
                                    <div class="list-group-item text-muted">No labs assigned to your account.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9 mb-3">
                        <div class="row">
                            <div class="col-sm-6 col-lg-3 mb-2">
                                <div class="metric-pill bg-primary-soft">
                                    <div class="metric-pill__label">Due Today</div>
                                    <div class="metric-pill__value">{{ $this->dashboardMetrics['due_today'] }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-2">
                                <div class="metric-pill bg-success-soft">
                                    <div class="metric-pill__label">Completed</div>
                                    <div class="metric-pill__value">{{ $this->dashboardMetrics['completed'] }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-2">
                                <div class="metric-pill bg-warning-soft">
                                    <div class="metric-pill__label">Pending</div>
                                    <div class="metric-pill__value">{{ $this->dashboardMetrics['pending'] }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-2">
                                <div class="metric-pill bg-danger-soft">
                                    <div class="metric-pill__label">Failed</div>
                                    <div class="metric-pill__value">{{ $this->dashboardMetrics['failed'] }}</div>
                                </div>
                            </div>
                        </div>

                        @if($activeSection === 'equipment')
                            <div class="row mt-2">
                                <div class="col-sm-4 mb-2">
                                    <div class="metric-sub-pill">
                                        <span>Overdue Calibrations</span>
                                        <strong>{{ $this->equipmentCalibrationAlerts['overdue'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <div class="metric-sub-pill">
                                        <span>Nearing Calibration Due</span>
                                        <strong>{{ $this->equipmentCalibrationAlerts['nearing_due'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <div class="metric-sub-pill">
                                        <span>Daily Checks Due</span>
                                        <strong>{{ $this->equipmentCalibrationAlerts['due_checks'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>{{ ucfirst($activeSection) }} Templates Due Today</strong>
                        <span class="text-muted">{{ now()->toFormattedDateString() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Doc #</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->templatesDueToday as $template)
                                    @php
                                        $todayLog = $this->todaysLogs->firstWhere('template_id', $template->id);
                                        $statusLabel = $todayLog?->status ? strtoupper($todayLog->status) : 'PENDING';
                                    @endphp
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->document_control_number ?: '-' }}</td>
                                        <td>v{{ $template->version }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $statusLabel === 'COMPLETED' ? 'badge-success' : ($statusLabel === 'FAILED' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-primary" wire:click="openExecution('{{ $template->id }}')">
                                                Execute
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No templates due for this lab today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light"><strong>Today's Monitoring Logs</strong></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Result</th>
                                    <th>Status</th>
                                    <th>Executed By</th>
                                    <th>Executed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->todaysLogs as $log)
                                    <tr>
                                        <td>{{ $log->template->name ?? '-' }}</td>
                                        <td>{{ $log->overall_result ?: '-' }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $log->status === 'completed' ? 'badge-success' : ($log->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ strtoupper($log->status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($log->executedBy)->name ?? '-' }}</td>
                                        <td>{{ optional($log->executed_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No logs captured yet for today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Template Engine + Variables + Formula Engine</h5>
                        <p class="text-muted mb-0">Create versioned templates and dynamic logic without overwriting historical definitions.</p>
                    </div>
                    <a href="{{ route('monitoring.template.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> New Template
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Category</th>
                                <th>Document #</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Fields</th>
                                <th>Formulas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->templateEngineTemplates as $tpl)
                                <tr>
                                    <td>{{ $tpl->name }}</td>
                                    <td>{{ ucfirst($tpl->monitoring_category) }}</td>
                                    <td>{{ $tpl->document_control_number ?: '-' }}</td>
                                    <td>v{{ $tpl->version }}</td>
                                    <td><span class="badge badge-info">{{ strtoupper($tpl->status) }}</span></td>
                                    <td>{{ $tpl->fields_count }}</td>
                                    <td>{{ $tpl->formula_rules_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No monitoring templates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($showExecutionModal && $activeTemplateId)
        @php
            $activeTemplate = $this->activeTemplate;
        @endphp
        @if($activeTemplate)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Execute Monitoring: {{ $activeTemplate->name }}</h5>
                            <button type="button" class="close" wire:click="closeExecutionModal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            @if($errors->has('execution'))
                                <div class="alert alert-danger">{{ $errors->first('execution') }}</div>
                            @endif

                            <div class="form-group">
                                <label>Equipment (optional)</label>
                                <select class="form-control" wire:model.defer="executionInputs.equipment_id">
                                    <option value="">-- Select equipment --</option>
                                    @foreach($this->executionEquipments as $eq)
                                        <option value="{{ $eq->id }}">{{ $eq->name }}{{ $eq->equipment_number ? ' (' . $eq->equipment_number . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                @foreach($activeTemplate->fields as $field)
                                    @php
                                        $type = $field->field_type;
                                        $cfg = $field->field_config ?? [];
                                        $options = is_array($cfg['options'] ?? null) ? $cfg['options'] : [];
                                    @endphp
                                    <div class="col-md-{{ in_array($type, ['textarea', 'table'], true) ? '12' : '6' }} mb-3">
                                        <label class="font-weight-bold">
                                            {{ $field->label }}
                                            @if($field->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @if($type === 'textarea')
                                            <textarea class="form-control" rows="3" wire:model.defer="executionInputs.{{ $field->field_key }}"></textarea>
                                        @elseif($type === 'number' || $type === 'formula')
                                            <input type="number" step="any" class="form-control" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'checkbox')
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                            </div>
                                        @elseif($type === 'date')
                                            <input type="date" class="form-control" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'datetime')
                                            <input type="datetime-local" class="form-control" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'dropdown' || $type === 'radio')
                                            <select class="form-control" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                                <option value="">-- Select --</option>
                                                @foreach($options as $option)
                                                    @php
                                                        $optionValue = is_array($option) ? ($option['value'] ?? '') : $option;
                                                        $optionLabel = is_array($option)
                                                            ? ($option['label'] ?? ($option['value'] ?? ''))
                                                            : $option;
                                                    @endphp
                                                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control" wire:model.defer="executionInputs.{{ $field->field_key }}">
                                        @endif

                                        @error('executionInputs.' . $field->field_key)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeExecutionModal">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="saveExecution">Save Log</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <style>
        .monitoring-shell .monitor-entry-card {
            border: 1px solid #dfe6ef;
            border-radius: 14px;
            transition: all 0.18s ease;
            background: #ffffff;
            cursor: pointer;
        }

        .monitoring-shell .monitor-entry-card:hover,
        .monitoring-shell .monitor-entry-card.active {
            border-color: #3b82f6;
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.14);
            transform: translateY(-1px);
        }

        .monitoring-shell .monitor-entry-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }

        .monitoring-shell .monitor-entry-card h5 {
            color: #0f172a;
            font-weight: 700;
        }

        .monitoring-main-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }

        .monitor-lab-list {
            max-height: 360px;
            overflow-y: auto;
        }

        .metric-pill {
            border-radius: 12px;
            padding: 12px 14px;
            height: 100%;
        }

        .metric-pill__label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            font-weight: 700;
        }

        .metric-pill__value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
        }

        .metric-sub-pill {
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .bg-info-soft { background: #dff4ff; color: #0b6a90; }
        .bg-success-soft { background: #dff8ee; color: #166534; }
        .bg-warning-soft { background: #fff4dd; color: #9a6700; }
        .bg-primary-soft { background: #dbeafe; }
        .bg-danger-soft { background: #fee2e2; }

        @media (max-width: 767px) {
            .monitoring-shell .monitor-entry-card .card-body {
                min-height: auto;
            }
        }
    </style>
</div>
