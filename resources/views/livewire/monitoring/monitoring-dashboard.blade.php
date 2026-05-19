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
                            <p class="text-muted mb-0">Track environmental conditions and equipment performance with
                                dynamic formula-driven logs and audit-ready records.</p>
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
            <button type="button"
                class="card monitor-entry-card w-100 text-left {{ $activeSection === 'environmental' ? 'active' : '' }}"
                wire:click="switchSection('environmental')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-info-soft">
                        <i class="mdi mdi-thermometer"></i>
                    </div>
                    <h5 class="mb-2">Environmental Monitoring</h5>
                    <p class="text-muted mb-0">Execution, daily logs, thresholds, and deviation alerts for laboratory
                        conditions.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 mb-3">
            <button type="button"
                class="card monitor-entry-card w-100 text-left {{ $activeSection === 'equipment' ? 'active' : '' }}"
                wire:click="switchSection('equipment')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-success-soft">
                        <i class="mdi mdi-scale-balance"></i>
                    </div>
                    <h5 class="mb-2">Equipment Monitoring</h5>
                    <p class="text-muted mb-0">Intermediate checks, verification runs, dynamic calculations, and
                        pass/fail controls.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 mb-3">
            <button type="button"
                class="card monitor-entry-card w-100 text-left {{ $activeSection === 'templates' ? 'active' : '' }}"
                wire:click="switchSection('templates')">
                <div class="card-body">
                    <div class="monitor-entry-card__icon bg-warning-soft">
                        <i class="mdi mdi-file-document-edit-outline"></i>
                    </div>
                    <h5 class="mb-2">Template Engine</h5>
                    <p class="text-muted mb-0">Template versions, variable logic, formula rules, and workflow-ready
                        configurations.</p>
                </div>
            </button>
        </div>
    </div>

    <div class="alert alert-warning">
        DEBUG INFO:
        Active Section: {{ $activeSection }} |
        Selected Lab ID: {{ $selectedLabId }} |
        Templates Due Today Count: {{ $this->templatesDueToday->count() }} |
        Assigned Labs Count: {{ $this->assignedLabs->count() }}
    </div>

    <div class="card monitoring-main-card">
        <div class="card-body">
            @if(in_array($activeSection, ['environmental', 'equipment'], true))
                <div class="row mb-3">
                    <div class="col-lg-3 mb-3">
                        <div class="card h-100 monitor-assigned-card">
                            <div class="card-body">
                                <div class="monitor-assigned-card__label">Assigned Laboratories</div>
                                <div class="monitor-assigned-card__count">{{ $this->assignedLabs->count() }}</div>
                                <div class="text-muted small">Use the tabs below to switch between labs.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9 mb-3">
                        @if($selectedLabId)
                            @php $metricsLab = $this->assignedLabs->firstWhere('id', $selectedLabId); @endphp
                            <div class="monitor-metrics-lab-badge mb-2">
                                <i class="mdi mdi-flask-outline"></i>
                                <span>{{ $metricsLab?->name ?? 'Selected Lab' }}</span>
                                <span class="monitor-metrics-lab-code">{{ $metricsLab?->code }}</span>
                                <span class="monitor-metrics-lab-date ml-auto">{{ now()->toFormattedDateString() }}</span>
                            </div>
                        @endif
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

                <div class="monitor-lab-tabs-wrap mb-3">
                    @if($this->assignedLabs->count() > 0)
                        <ul class="nav nav-tabs monitor-lab-tabs" role="tablist">
                            @foreach($this->assignedLabs as $lab)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $selectedLabId === $lab->id ? 'active' : '' }}"
                                        wire:click="selectLab('{{ $lab->id }}')">
                                        <span>{{ $lab->name }}</span>
                                        <small>{{ $lab->code }}</small>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-light border mb-0">No labs assigned to your account.</div>
                    @endif
                </div>

                @if($selectedLabId)
                    @php
                        $selectedLab = $this->assignedLabs->firstWhere('id', $selectedLabId);
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong>
                                {{ $selectedLab?->name ?? 'Selected Lab' }}
                                <span class="text-muted">| {{ ucfirst($activeSection) }} Due Today</span>
                            </strong>
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
                                                <span
                                                    class="badge badge-pill {{ $statusLabel === 'COMPLETED' ? 'badge-success' : ($statusLabel === 'FAILED' ? 'badge-danger' : 'badge-warning') }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    wire:click="openExecution('{{ $template->id }}')">
                                                    Execute
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No templates due for this lab today.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>{{ ucfirst($activeSection) }} Monitoring Logs</strong>
                        </div>
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
                                                <span
                                                    class="badge badge-pill {{ $log->status === 'completed' ? 'badge-success' : ($log->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
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
                @endif
            @else
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Template Engine + Variables + Formula Engine</h5>
                        <p class="text-muted mb-0">Create versioned templates and dynamic logic without overwriting
                            historical definitions.</p>
                    </div>
                    <a href="{{ route('monitoring.template.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> New Template
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-right">Actions</th>
                                <th>Template</th>
                                <th>Category</th>
                                <th>Document #</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Fields</th>
                                <th>Formulas</th>
                                <th>Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->templateEngineTemplates as $tpl)
                                <tr>
                                    <td class="text-right">
                                        <a href="{{ route('monitoring.template.edit', $tpl->id) }}"
                                            class="rm-act-btn rm-act-btn--edit" title="Edit template">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="rm-act-btn rm-act-btn--warning"
                                            wire:click="clearTemplateLogs('{{ $tpl->id }}')"
                                            onclick="return confirm('Clear all captured logs for this template? This cannot be undone.')"
                                            title="Clear captured logs">
                                            <i class="mdi mdi-delete-sweep"></i>
                                        </button>
                                        <button type="button" class="rm-act-btn rm-act-btn--delete"
                                            wire:click="deleteTemplate('{{ $tpl->id }}')"
                                            onclick="return confirm('Delete this template and all captured logs? This cannot be undone.')"
                                            title="Delete template">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    </td>
                                    <td>{{ $tpl->name }}</td>
                                    <td>{{ ucfirst($tpl->monitoring_category) }}</td>
                                    <td>{{ $tpl->document_control_number ?: '-' }}</td>
                                    <td>v{{ $tpl->version }}</td>
                                    <td><span class="badge badge-info">{{ strtoupper($tpl->status) }}</span></td>
                                    <td>{{ $tpl->fields_count }}</td>
                                    <td>{{ $tpl->formula_rules_count }}</td>
                                    <td>{{ $tpl->logs_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No monitoring templates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($showTemplateEditModal && $editingTemplateId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Monitoring Template</h5>
                        <button type="button" class="close"
                            wire:click="closeTemplateEditModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Template Name</label>
                                <input type="text" class="form-control" wire:model.defer="templateEditInputs.name">
                                @error('templateEditInputs.name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Document #</label>
                                <input type="text" class="form-control"
                                    wire:model.defer="templateEditInputs.document_control_number">
                                @error('templateEditInputs.document_control_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">Version</label>
                                <input type="number" min="1" class="form-control"
                                    wire:model.defer="templateEditInputs.version">
                                @error('templateEditInputs.version')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">Category</label>
                                <select class="form-control" wire:model.defer="templateEditInputs.monitoring_category">
                                    <option value="environmental">Environmental</option>
                                    <option value="equipment">Equipment</option>
                                </select>
                                @error('templateEditInputs.monitoring_category')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">Status</label>
                                <input type="text" class="form-control" wire:model.defer="templateEditInputs.status"
                                    placeholder="draft/active/archived">
                                @error('templateEditInputs.status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="template-active-toggle"
                                        wire:model.defer="templateEditInputs.is_active">
                                    <label class="form-check-label" for="template-active-toggle">Template is active</label>
                                </div>
                                @error('templateEditInputs.is_active')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="closeTemplateEditModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTemplateEdit">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                <select class="form-control" wire:model.live="executionInputs.equipment_id">
                                    <option value="">-- Select equipment --</option>
                                    @foreach($this->executionEquipments as $eq)
                                        <option value="{{ $eq->id }}">
                                            {{ $eq->name }}{{ $eq->equipment_number ? ' (' . $eq->equipment_number . ')' : '' }}
                                        </option>
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
                                            @if(!empty($cfg['variable_slug']))
                                                @php
                                                    $isDynamic = in_array($cfg['variable_slug'], ['correction_factor', 'uncertainty_of_measure'], true);
                                                @endphp
                                                <span class="badge {{ $isDynamic ? 'badge-primary' : 'badge-success' }} ml-1 px-2 py-1"
                                                    style="font-size: 0.75rem; color: #fff;">
                                                    <i class="mdi {{ $isDynamic ? 'mdi-sine-wave' : 'mdi-lock' }} mr-1"></i>
                                                    {{ $isDynamic ? 'Dynamic: ' : 'Constant: ' }}{{ $cfg['variable_slug'] }}
                                                </span>
                                            @endif
                                        </label>

                                        @if($type === 'textarea')
                                            <textarea class="form-control" rows="3"
                                                wire:model.defer="executionInputs.{{ $field->field_key }}"></textarea>
                                        @elseif($type === 'formula')
                                            @php $computedVal = $this->executionInputs[$field->field_key] ?? null; @endphp
                                            <div class="exec-formula-display {{ $computedVal !== null ? 'exec-formula-display--filled' : 'exec-formula-display--pending' }}">
                                                <span class="exec-formula-badge">
                                                    <i class="mdi mdi-function-variant"></i> Formula
                                                </span>
                                                <span class="exec-formula-value">
                                                    {{ $computedVal !== null ? $computedVal : '— waiting for inputs —' }}
                                                </span>
                                                <i class="mdi mdi-lock-outline exec-formula-lock"></i>
                                            </div>
                                            <input type="hidden" wire:model="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'number')
                                            <input type="number" step="any" class="form-control"
                                                wire:model.live.debounce.500ms="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'checkbox')
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    wire:model.defer="executionInputs.{{ $field->field_key }}">
                                            </div>
                                        @elseif($type === 'date')
                                            <input type="date" class="form-control"
                                                wire:model.live="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'datetime')
                                            <input type="datetime-local" class="form-control"
                                                wire:model.live="executionInputs.{{ $field->field_key }}">
                                        @elseif($type === 'dropdown' || $type === 'radio')
                                            <select class="form-control" wire:model.live="executionInputs.{{ $field->field_key }}">
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
                                            <input type="text" class="form-control"
                                                wire:model.live="executionInputs.{{ $field->field_key }}">
                                        @endif

                                        @error('executionInputs.' . $field->field_key)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="closeExecutionModal">Cancel</button>
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

        .monitor-assigned-card {
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        .monitor-assigned-card__label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #475569;
        }

        .monitor-assigned-card__count {
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 800;
            color: #0f172a;
            margin: 8px 0;
        }

        .monitor-lab-tabs-wrap {
            border-bottom: 1px solid #dbe3ef;
        }

        .monitor-lab-tabs {
            border-bottom: 0;
            gap: 6px;
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 2px;
        }

        .monitor-lab-tabs .nav-item {
            flex: 0 0 auto;
        }

        .monitor-lab-tabs .nav-link {
            border: 1px solid #dbe3ef;
            border-radius: 10px 10px 0 0;
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .monitor-lab-tabs .nav-link small {
            color: #64748b;
            font-weight: 500;
        }

        .monitor-lab-tabs .nav-link.active {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e3a8a;
        }

        .monitor-lab-tabs .nav-link.active small {
            color: #1d4ed8;
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

        .monitor-metrics-lab-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f0f6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #1e3a8a;
        }

        .monitor-metrics-lab-badge .mdi {
            font-size: 15px;
            color: #3b82f6;
        }

        .monitor-metrics-lab-code {
            background: #dbeafe;
            border-radius: 5px;
            padding: 1px 7px;
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 0.04em;
        }

        .monitor-metrics-lab-date {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        .bg-info-soft {
            background: #dff4ff;
            color: #0b6a90;
        }

        .bg-success-soft {
            background: #dff8ee;
            color: #166534;
        }

        .bg-warning-soft {
            background: #fff4dd;
            color: #9a6700;
        }

        .bg-primary-soft {
            background: #dbeafe;
        }

        .bg-danger-soft {
            background: #fee2e2;
        }

        /* Action button styling for monitoring tables */
        /* Computed formula field display in Execute Monitoring modal */
        .exec-formula-display {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px dashed #93c5fd;
            border-radius: 8px;
            padding: 9px 14px;
            background: #f0f7ff;
            min-height: 38px;
            transition: all 0.2s ease;
        }

        .exec-formula-display--pending {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .exec-formula-display--pending .exec-formula-value {
            color: #94a3b8;
            font-style: italic;
            font-size: 0.85rem;
        }

        .exec-formula-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 5px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .exec-formula-badge .mdi {
            font-size: 0.85rem;
        }

        .exec-formula-value {
            flex: 1;
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
            letter-spacing: 0.01em;
        }

        .exec-formula-lock {
            color: #94a3b8;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .exec-formula-display--filled {
            border-color: #6ee7b7;
            background: #f0fdf4;
        }

        .exec-formula-display--filled .exec-formula-badge {
            background: #d1fae5;
            color: #065f46;
        }

        .exec-formula-display--filled .exec-formula-lock {
            color: #10b981;
        }

        .rm-act-btn {
            border-radius: 7px;
            padding: 6px;
            margin-right: 3px;
            font-size: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            white-space: nowrap;
            width: 32px;
            height: 32px;
            min-width: 32px;
            min-height: 32px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        .rm-act-btn i {
            font-size: 1rem;
            margin: 0;
        }

        /* EDIT Button - Blue */
        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(29, 78, 216, 0.15);
        }

        /* WARNING Button - Yellow/Amber */
        .rm-act-btn--warning {
            border: 1px solid #fcd34d;
            color: #b45309;
            background: #fffbeb;
        }

        .rm-act-btn--warning:hover {
            background: #fef3c7;
            border-color: #fbbf24;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(180, 83, 9, 0.15);
        }

        /* DELETE Button - Red */
        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.15);
        }

        @media (max-width: 767px) {
            .monitoring-shell .monitor-entry-card .card-body {
                min-height: auto;
            }
        }
    </style>
</div>
</style>
</div>