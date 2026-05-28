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

    <div class="row g-3 mb-3 monitor-entry-cards-row">
        <div class="col-md-4 d-flex">
            <button type="button"
                class="card monitor-entry-card w-100 h-100 text-left {{ $activeSection === 'environmental' ? 'active' : '' }}"
                wire:click="switchSection('environmental')">
                <div class="card-body d-flex flex-column">
                    <div class="monitor-entry-card__icon bg-info-soft">
                        <i class="mdi mdi-thermometer"></i>
                    </div>
                    <h5 class="mb-2">Environmental Monitoring</h5>
                    <p class="text-muted mb-0">Execution, daily logs, thresholds, and deviation alerts for laboratory
                        conditions.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 d-flex">
            <button type="button"
                class="card monitor-entry-card w-100 h-100 text-left {{ $activeSection === 'equipment' ? 'active' : '' }}"
                wire:click="switchSection('equipment')">
                <div class="card-body d-flex flex-column">
                    <div class="monitor-entry-card__icon bg-success-soft">
                        <i class="mdi mdi-scale-balance"></i>
                    </div>
                    <h5 class="mb-2">Equipment Monitoring</h5>
                    <p class="text-muted mb-0">Intermediate checks, verification runs, dynamic calculations, and
                        pass/fail controls.</p>
                </div>
            </button>
        </div>
        <div class="col-md-4 d-flex">
            <button type="button"
                class="card monitor-entry-card w-100 h-100 text-left {{ $activeSection === 'templates' ? 'active' : '' }}"
                wire:click="switchSection('templates')">
                <div class="card-body d-flex flex-column">
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
                    @if($activeSection === 'environmental')
                        @include('livewire.monitoring.partials.environmental.section-badge-tabs')
                        @if($selectedSectionId)
                            @include('livewire.monitoring.partials.environmental.section-workspace')
                        @endif
                    @elseif($activeSection === 'equipment')
                        @php
                            $selectedLab = $this->assignedLabs->firstWhere('id', $selectedLabId);
                        @endphp

                        <div class="card mb-3">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong>
                                    {{ $selectedLab?->name ?? 'Selected Lab' }}
                                    <span class="text-muted">| Equipment Due Today</span>
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
                                                <td colspan="5" class="text-center text-muted py-4">No templates due for this lab today.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light">
                                <strong>Equipment Monitoring Logs</strong>
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

                        <div class="card mt-4" style="border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); overflow: hidden;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border: none; padding: 15px 20px;">
                                <h5 class="mb-0 font-weight-bold" style="font-size: 1.1rem; color: white;">
                                    <i class="mdi mdi-chart-line-variant mr-1"></i> Equipment Logs vs. Optimum Level
                                </h5>
                            </div>
                            <div class="card-body bg-white" style="padding: 24px;">
                                @if($this->environmentalGraphData['hasData'])
                                    <div style="height: 320px; position: relative;">
                                        <canvas id="environmentalTrendsChart"
                                                class="monitoring-legacy-chart"
                                                data-chart-data='@json($this->environmentalGraphData)'></canvas>
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted">
                                        <i class="mdi mdi-chart-bubble" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <h6 class="mt-3 font-weight-bold text-dark">No equipment logs captured yet</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            @else
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Template Engine + Variables + Formula Engine</h5>
                        <p class="text-muted mb-0">Create versioned templates and dynamic logic without overwriting
                            historical definitions.</p>
                    </div>
                    <a href="{{ $module === 'equipment' ? route('equipment.monitoring.template.create') : route('monitoring.template.create') }}"
                       class="btn btn-outline-primary monitor-new-template-btn">
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
                                        <a href="{{ $module === 'equipment' ? route('equipment.monitoring.template.edit', $tpl->id) : route('monitoring.template.edit', $tpl->id) }}"
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

                            @if($activeSection === 'environmental' && count($this->executionFrequencyOptions) > 0)
                                <div class="form-group">
                                    <label>Reading frequency <span class="text-danger">*</span></label>
                                    <select class="form-control @error('executionFrequencySlot') is-invalid @enderror"
                                            wire:model="executionFrequencySlot">
                                        <option value="">-- Select reading --</option>
                                        @foreach($this->executionFrequencyOptions as $freq)
                                            <option value="{{ $freq['slot'] }}">{{ $freq['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('executionFrequencySlot')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <label>Remark</label>
                                <textarea class="form-control" rows="2" wire:model="executionRemark"
                                          placeholder="Optional note for this reading"></textarea>
                            </div>

                            <div class="row">
                                @foreach($activeTemplate->fields as $field)
                                    @if($field->field_type === 'metadata' || $field->field_key === '__meta_scope_items')
                                        @continue
                                    @endif
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

    @if($showInlineFormulaTimelineModal && $inlineFormulaTimelineTemplateId)
        @php
            $formulaTimeline = $this->inlineFormulaTimelineData;
            $timelineTemplate = $formulaTimeline['template'];
            $timelineRows = $formulaTimeline['timeline'] ?? [];
            $timelineStatus = $formulaTimeline['preview_status'] ?? null;
            $isSavedSnapshot = $formulaTimeline['is_saved_snapshot'] ?? false;
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15,23,42,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content env-formula-modal">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Formula execution timeline</h5>
                            <p class="env-formula-modal__subtitle mb-0">
                                {{ $timelineTemplate?->name ?? 'Monitoring template' }}
                            </p>
                        </div>
                        <button type="button" class="close" wire:click="closeInlineFormulaTimeline"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="env-formula-modal__status-row">
                            <span class="env-formula-status-pill {{ $timelineStatus === 'Fail' ? 'is-fail' : ($timelineStatus === 'Pass' ? 'is-pass' : 'is-pending') }}">
                                <i class="mdi {{ $timelineStatus === 'Fail' ? 'mdi-alert-circle-outline' : ($timelineStatus === 'Pass' ? 'mdi-check-circle-outline' : 'mdi-timer-sand') }}"></i>
                                @if($timelineStatus)
                                    {{ ($isSavedSnapshot ? 'Status' : 'Live status').': '.$timelineStatus }}
                                @else
                                    {{ $isSavedSnapshot ? 'Status: Pending' : 'Live status: Pending' }}
                                @endif
                            </span>
                        </div>

                        <div class="env-formula-timeline">
                            @foreach($timelineRows as $row)
                                <article class="env-formula-step is-{{ $row['tone'] ?? 'neutral' }}">
                                    <div class="env-formula-step__dot"></div>
                                    <div class="env-formula-step__card">
                                        <h6 class="env-formula-step__title">{{ $row['title'] ?? 'Step' }}</h6>
                                        <p class="env-formula-step__detail">{{ $row['detail'] ?? '' }}</p>
                                        @if(!empty($row['meta']) && is_array($row['meta']))
                                            <div class="env-formula-step__meta">
                                                @foreach($row['meta'] as $metaLabel => $metaValue)
                                                    <div class="env-formula-step__meta-row">
                                                        <span class="env-formula-step__meta-key">{{ $metaLabel }}</span>
                                                        <span class="env-formula-step__meta-value">{{ $metaValue }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeInlineFormulaTimeline">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .monitoring-shell .monitor-entry-cards-row {
            align-items: stretch;
        }

        .monitoring-shell .monitor-entry-card {
            border: 1px solid #dfe6ef;
            border-radius: 14px;
            transition: all 0.18s ease;
            background: #ffffff;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .monitoring-shell .monitor-entry-card .card-body {
            flex: 1 1 auto;
        }

        .monitoring-shell .monitor-entry-card .card-body p {
            flex: 1 1 auto;
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

        .monitoring-shell .monitor-new-template-btn {
            border-radius: 8px;
            font-weight: 600;
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

        /* Environmental section workspace */
        .env-section-nav {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 12px;
            padding: 0.85rem 1rem 0.75rem;
        }

        .env-section-nav__toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.65rem;
        }

        .env-section-nav__heading {
            display: flex;
            align-items: baseline;
            gap: 0.45rem;
            min-width: 0;
        }

        .env-section-nav__lab {
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.01em;
        }

        .env-section-nav__subtitle {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
        }

        .env-section-nav__history {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
        }

        .env-section-nav__history-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        .env-section-nav__empty {
            padding: 0.75rem;
            text-align: center;
            font-size: 0.82rem;
            color: #94a3b8;
            background: #f8fafc;
            border-radius: 8px;
        }

        .env-section-chip-track {
            display: flex;
            gap: 0.4rem;
            overflow-x: auto;
            padding: 0.15rem 0 0.35rem;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .env-section-chip-track::-webkit-scrollbar {
            height: 4px;
        }

        .env-section-chip-track::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .env-section-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
            max-width: 280px;
            padding: 0.42rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
            text-align: left;
        }

        .env-section-chip:hover {
            border-color: #cbd5e1;
            background: #fff;
        }

        .env-section-chip.is-active {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.12), 0 4px 12px rgba(37, 99, 235, 0.08);
        }

        .env-section-chip__code {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            flex-shrink: 0;
        }

        .env-section-chip.is-active .env-section-chip__code {
            color: #2563eb;
        }

        .env-section-chip__name {
            font-size: 0.78rem;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .env-section-chip__meta {
            font-size: 0.68rem;
            font-weight: 600;
            color: #059669;
            white-space: nowrap;
            flex-shrink: 0;
            padding-left: 0.35rem;
            border-left: 1px solid #e2e8f0;
        }

        .env-history-select {
            width: auto;
            min-width: 88px;
            border-radius: 8px;
            border-color: #e2e8f0;
            font-size: 0.78rem;
        }

        .env-workspace__hero {
            margin-bottom: 0.85rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eef2f6;
        }

        .env-workspace__title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.25rem;
            line-height: 1.3;
        }

        .env-workspace__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .env-workspace__code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 600;
            color: #475569;
            background: #f1f5f9;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
        }

        .env-workspace__dot {
            color: #cbd5e1;
        }

        .env-detail-tabs {
            border-bottom: 1px solid #e8edf3;
            gap: 0.25rem;
        }

        .env-detail-tabs .nav-link {
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 0.55rem 0.95rem;
            cursor: pointer;
            background: transparent;
            border-radius: 6px 6px 0 0;
            margin-bottom: -1px;
        }

        .env-detail-tabs .nav-link:hover {
            color: #334155;
            background: #f8fafc;
        }

        .env-detail-tabs .nav-link.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: transparent;
        }

        .env-workspace-inset {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
            margin-left: 0.35rem;
            margin-right: 0.35rem;
            border-left: 2px solid #e8edf3;
        }

        .env-section-detail-body {
            padding-top: 0.25rem;
            padding-bottom: 1rem;
            margin-bottom: 0.75rem;
        }

        .env-summary-panels-row {
            margin-top: 0.25rem;
            margin-bottom: 0.35rem;
        }

        .env-summary-panel-col {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .env-summary-panel-col--left {
            padding-right: 1.15rem;
        }

        .env-summary-panel-col--right {
            padding-left: 1.15rem;
        }

        @media (max-width: 991.98px) {
            .env-summary-panel-col,
            .env-summary-panel-col--left,
            .env-summary-panel-col--right {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        .env-detail-tab-body {
            padding-top: 0.75rem;
            padding-bottom: 0.25rem;
        }

        .env-template-block {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 12px;
            padding: 1rem 1.1rem 1.1rem;
        }

        .env-template-block__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .env-template-block__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.15rem;
        }

        .env-template-block__doc {
            font-size: 0.74rem;
            color: #94a3b8;
            margin: 0;
        }

        .env-next-capture-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.76rem;
            background: #f0f7ff;
            border: 1px solid #dbeafe;
            color: #1e40af;
            white-space: nowrap;
        }

        .env-next-capture-pill__count {
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            background: #fff;
            padding: 0.08rem 0.35rem;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .env-next-capture-pill--complete {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .env-col-capture-badge {
            display: inline-block;
            margin-left: 0.3rem;
            padding: 0.08rem 0.38rem;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #2563eb;
            color: #fff;
            border-radius: 4px;
            vertical-align: middle;
        }

        .env-matrix-row--today {
            background: #f8fbff;
        }

        .env-matrix-cell--capture {
            background: #fff;
            border: 1px solid #93c5fd !important;
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
            min-width: 200px;
        }

        .env-matrix-cell--waiting {
            background: #fafbfc;
        }

        .env-inline-capture {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .env-inline-capture__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .env-inline-capture__banner {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.74rem;
            color: #334155;
        }

        .env-inline-capture__step {
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.08rem 0.35rem;
            border-radius: 4px;
        }

        .env-more-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.65rem;
            height: 1.65rem;
            padding: 0;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .env-more-btn:hover,
        .env-more-btn.is-open {
            border-color: #93c5fd;
            color: #2563eb;
            background: #eff6ff;
        }

        .env-inline-capture__fields {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .env-inline-field__label {
            display: block;
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 0.2rem;
        }

        .env-inline-input {
            border-radius: 6px;
            border-color: #e2e8f0;
            font-size: 0.82rem;
        }

        .env-inline-input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .env-inline-field__error {
            font-size: 0.65rem;
            color: #dc2626;
            margin-top: 0.15rem;
        }

        .env-inline-derived {
            padding: 0.55rem 0.65rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
        }

        .env-inline-derived__title {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin: 0 0 0.4rem;
        }

        .env-inline-derived__grid {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .env-inline-derived__item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.5rem;
            font-size: 0.74rem;
        }

        .env-inline-derived__label {
            color: #64748b;
            flex-shrink: 0;
        }

        .env-inline-derived__value {
            font-weight: 600;
            color: #334155;
            text-align: right;
        }

        .env-inline-derived__value.is-primary {
            color: #4338ca;
            font-weight: 700;
        }

        .env-inline-capture__actions {
            margin-top: 0.1rem;
        }

        .env-inline-save {
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
        }

        .env-cell-more {
            margin-top: 0.25rem;
        }

        .env-cell-more__trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.4rem;
            height: 1.4rem;
            border-radius: 4px;
            color: #94a3b8;
            cursor: pointer;
            list-style: none;
            transition: all 0.15s ease;
        }

        .env-cell-more__trigger::-webkit-details-marker {
            display: none;
        }

        .env-cell-more__trigger:hover {
            color: #2563eb;
            background: #eff6ff;
        }

        .env-cell-more[open] .env-cell-more__trigger {
            color: #2563eb;
            background: #eff6ff;
        }

        .env-cell-more__body {
            margin-top: 0.35rem;
            padding-top: 0.35rem;
            border-top: 1px dashed #e2e8f0;
        }

        .env-matrix-cell__value--primary {
            font-weight: 700;
            color: #0f172a;
        }

        .env-matrix-cell__row--muted .env-matrix-cell__key,
        .env-matrix-cell__row--muted .env-matrix-cell__value {
            color: #94a3b8;
            font-size: 0.72rem;
        }

        .env-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .env-section-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.1);
        }

        .env-section-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }

        .env-section-card__code {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .env-section-card__title {
            font-weight: 700;
            color: #0f172a;
        }

        .env-section-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 0.35rem;
        }

        .env-section-card__schedule {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .env-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem 0.55rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .env-badge--optimum { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .env-badge--freq { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .env-badge--equipment { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
        .env-badge--um { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }

        .env-panel {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 10px;
            padding: 1rem 1.15rem;
            height: 100%;
        }

        .env-panel__title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.85rem;
        }

        .env-stat__label {
            display: block;
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .env-stat__value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        .env-stat__meta {
            font-weight: 500;
            color: #64748b;
        }

        .env-stat__value--with-action {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .env-cert-open {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.65rem;
            height: 1.65rem;
            border-radius: 6px;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            text-decoration: none;
            flex-shrink: 0;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .env-cert-open:hover {
            color: #1d4ed8;
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .env-cert-open .mdi {
            font-size: 1.05rem;
            line-height: 1;
        }

        .env-breadcrumb__link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .env-template-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        }

        .env-matrix-wrap { max-height: 420px; overflow: auto; }

        .env-matrix--nested {
            border-collapse: separate;
            border-spacing: 0;
        }

        .env-matrix-date-head {
            min-width: 88px;
            vertical-align: bottom;
        }

        .env-matrix-freq-head {
            text-align: center;
            font-weight: 700;
            font-size: 0.78rem;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .env-matrix-freq-head--active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .env-matrix-field-head {
            text-align: center;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            min-width: 72px;
            white-space: nowrap;
            background: #fff;
        }

        .env-matrix-subcell {
            vertical-align: middle;
            text-align: center;
            padding: 0.4rem 0.35rem;
            min-width: 72px;
            background: #fff;
        }

        .env-matrix-subcell--filled {
            background: #fafbfc;
        }

        .env-matrix-subcell--pass,
        .env-matrix-subcell--filled.env-matrix-subcell--pass {
            background: #ecfdf3;
        }

        .env-matrix-subcell--fail,
        .env-matrix-subcell--filled.env-matrix-subcell--fail {
            background: #fef2f2;
        }

        .env-matrix-subcell--saved-footer {
            background: #fafbfc;
            vertical-align: top;
            padding: 0.5rem 0.65rem;
        }

        .env-matrix-capture-footer--saved {
            gap: 0.45rem;
        }

        .env-matrix-capture-footer__actions--saved {
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
        }

        .env-matrix-capture-footer__captured-by {
            font-size: 0.72rem;
            color: #64748b;
        }

        .env-matrix-capture-footer__captured-by strong {
            color: #334155;
            font-weight: 600;
        }

        .env-matrix-capture-footer__edit {
            flex-shrink: 0;
            padding: 0.2rem 0.35rem;
            line-height: 1;
            color: #64748b;
        }

        .env-matrix-capture-footer__edit:hover,
        .env-matrix-capture-footer__edit.is-active {
            color: #2563eb;
        }

        .env-matrix-capture-footer__edit .mdi {
            font-size: 1.1rem;
        }

        .env-matrix-subcell--capture {
            background: #fff;
            border-top: 2px solid #93c5fd;
            vertical-align: top;
            padding: 0.35rem;
        }

        .env-matrix-subcell--capture-footer {
            background: #f8fbff;
            border-bottom: 2px solid #93c5fd;
            padding: 0.45rem 0.5rem;
        }

        .env-matrix-subcell--spacer {
            background: #fafbfc;
            border-bottom: none;
            padding: 0;
            height: 0;
        }

        .env-matrix-field-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            min-height: 1.5rem;
            width: 100%;
        }

        .env-matrix-field-cell--remark {
            justify-content: space-between;
            gap: 0.35rem;
        }

        .env-matrix-field-cell--remark .env-matrix-field-value {
            flex: 1 1 auto;
            min-width: 0;
        }

        .env-matrix-field-cell--remark .env-more-btn--remark {
            margin-left: auto;
            flex-shrink: 0;
        }

        .env-matrix-field-value {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }

        .env-matrix-field-value.is-primary {
            color: #0f172a;
            font-weight: 700;
        }

        .env-matrix-field-value--empty {
            color: #cbd5e1;
            font-weight: 400;
        }

        .env-matrix-field-remark {
            color: #94a3b8;
            font-size: 0.85rem;
            line-height: 1;
        }

        .env-matrix-capture-field .env-inline-input {
            text-align: center;
            padding: 0.3rem 0.35rem;
            font-size: 0.82rem;
        }

        .env-matrix-capture-field--remark {
            width: 100%;
            justify-content: space-between;
            gap: 0.45rem;
        }

        .env-matrix-capture-field--remark .env-more-btn {
            margin-left: auto;
        }

        .env-matrix-capture-field--derived {
            background: #f8fafc;
            border-radius: 6px;
            padding: 0.35rem 0.25rem;
        }

        .env-matrix-capture-footer__meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }

        .env-matrix-capture-footer__freq {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            color: #334155;
            font-weight: 600;
        }

        .env-matrix-capture-footer__actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .env-matrix-capture-footer__remark {
            flex: 1;
            min-width: 0;
        }

        .env-inline-derived--compact {
            margin-top: 0.4rem;
            padding: 0.4rem 0.5rem;
        }

        .env-cell-more--inline {
            margin-top: 0;
            flex-shrink: 0;
        }

        .env-matrix-capture-footer-row td {
            border-top: none;
        }

        .env-matrix-cell {
            vertical-align: top;
            min-width: 120px;
            background: #fff;
        }

        .env-matrix-cell__content { font-size: 0.76rem; }

        .env-matrix-cell__row {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.3rem;
        }

        .env-matrix-cell__key {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
            font-weight: 600;
        }

        .env-matrix-cell__value {
            color: #334155;
        }

        .env-matrix-cell--empty {
            font-size: 0.72rem;
            color: #cbd5e1;
            font-style: italic;
        }

        .env-matrix-date {
            font-weight: 600;
            white-space: nowrap;
            background: #fff;
        }

        .env-chart-wrap {
            height: 360px;
            position: relative;
        }

        .env-chart-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            padding: 1rem 1.15rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .env-chart-toolbar__group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            min-width: 200px;
        }

        .env-chart-toolbar__label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin: 0;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .env-chart-toolbar__label .mdi {
            font-size: 1rem;
            color: #3b82f6;
        }

        .env-chart-toolbar__select {
            min-width: 220px;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #0f172a;
            background-color: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.65rem center;
        }

        .env-chart-toolbar__select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .env-chart-toolbar__divider {
            align-self: stretch;
            width: 1px;
            min-height: 2.5rem;
            background: #e2e8f0;
        }

        .env-chart-toolbar__toggle {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin: 0;
            cursor: pointer;
            user-select: none;
        }

        .env-chart-toolbar__toggle-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .env-chart-toolbar__toggle-track {
            flex-shrink: 0;
            position: relative;
            width: 44px;
            height: 24px;
            margin-top: 0.1rem;
            background: #cbd5e1;
            border-radius: 999px;
            transition: background 0.2s ease;
        }

        .env-chart-toolbar__toggle-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
            transition: transform 0.2s ease;
        }

        .env-chart-toolbar__toggle-input:checked + .env-chart-toolbar__toggle-track {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .env-chart-toolbar__toggle-input:checked + .env-chart-toolbar__toggle-track .env-chart-toolbar__toggle-thumb {
            transform: translateX(20px);
        }

        .env-chart-toolbar__toggle-input:focus-visible + .env-chart-toolbar__toggle-track {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        .env-chart-toolbar__toggle-text {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .env-chart-toolbar__toggle-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.3;
        }

        .env-chart-toolbar__toggle-hint {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.35;
            max-width: 28rem;
        }

        @media (max-width: 767.98px) {
            .env-chart-toolbar__divider {
                display: none;
            }

            .env-chart-toolbar__group {
                width: 100%;
            }

            .env-chart-toolbar__select {
                width: 100%;
            }
        }

        .env-formula-modal {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
        }

        .env-formula-modal .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem 0.85rem;
        }

        .env-formula-modal__subtitle {
            font-size: 0.78rem;
            color: #64748b;
        }

        .env-formula-modal__status-row {
            margin-bottom: 0.8rem;
        }

        .env-formula-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.3rem 0.65rem;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
        }

        .env-formula-status-pill.is-pass {
            background: #ecfdf5;
            border-color: #86efac;
            color: #166534;
        }

        .env-formula-status-pill.is-fail {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .env-formula-status-pill.is-pending {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }

        .env-formula-timeline {
            position: relative;
            padding-left: 1rem;
        }

        .env-formula-timeline::before {
            content: '';
            position: absolute;
            left: 0.38rem;
            top: 0.15rem;
            bottom: 0.15rem;
            width: 2px;
            background: linear-gradient(180deg, #bfdbfe 0%, #dbeafe 100%);
        }

        .env-formula-step {
            position: relative;
            padding-left: 1rem;
            margin-bottom: 0.8rem;
        }

        .env-formula-step:last-child {
            margin-bottom: 0;
        }

        .env-formula-step__dot {
            position: absolute;
            left: -0.05rem;
            top: 0.58rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #93c5fd;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #dbeafe;
        }

        .env-formula-step.is-pass .env-formula-step__dot {
            background: #22c55e;
            box-shadow: 0 0 0 2px #dcfce7;
        }

        .env-formula-step.is-fail .env-formula-step__dot {
            background: #ef4444;
            box-shadow: 0 0 0 2px #fee2e2;
        }

        .env-formula-step__card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 0.7rem 0.8rem;
        }

        .env-formula-step__title {
            margin: 0;
            font-size: 0.83rem;
            font-weight: 700;
            color: #0f172a;
        }

        .env-formula-step__detail {
            margin: 0.2rem 0 0;
            font-size: 0.74rem;
            color: #64748b;
        }

        .env-formula-step__meta {
            margin-top: 0.55rem;
            padding-top: 0.45rem;
            border-top: 1px dashed #e2e8f0;
            display: grid;
            gap: 0.25rem;
        }

        .env-formula-step__meta-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
        }

        .env-formula-step__meta-key {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 600;
        }

        .env-formula-step__meta-value {
            font-size: 0.74rem;
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }
    </style>

</div>

@once
    @push('script2')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const monitoringChartInstances = {};

                function pointCount(rawData) {
                    return (rawData.labels || []).length;
                }

                function constantSeries(value, count) {
                    if (value === null || value === undefined || Number.isNaN(value)) {
                        return [];
                    }

                    return Array(count).fill(value);
                }

                function hasNumericSeries(values) {
                    return Array.isArray(values) && values.some(function (v) {
                        return v !== null && v !== undefined && !Number.isNaN(v);
                    });
                }

                function computeYScaleBounds(rawData) {
                    const candidates = []
                        .concat(rawData.actual || [])
                        .concat(rawData.actual_um_high || [])
                        .concat(rawData.actual_um_low || [])
                        .concat(rawData.optimum_um_high || [])
                        .concat(rawData.optimum_um_low || [])
                        .concat(rawData.min_um_low || [])
                        .concat(rawData.max_um_high || []);

                    if (rawData.range_fill_min !== null && rawData.range_fill_min !== undefined) {
                        candidates.push(rawData.range_fill_min);
                    }
                    if (rawData.range_fill_max !== null && rawData.range_fill_max !== undefined) {
                        candidates.push(rawData.range_fill_max);
                    }
                    if (rawData.reference) {
                        ['min', 'max', 'optimum', 'constant'].forEach(function (key) {
                            if (rawData.reference[key] !== null && rawData.reference[key] !== undefined) {
                                candidates.push(rawData.reference[key]);
                            }
                        });
                    }

                    const numeric = candidates.filter(function (v) {
                        return v !== null && v !== undefined && !Number.isNaN(v);
                    });

                    if (!numeric.length) {
                        return {};
                    }

                    const min = Math.min.apply(null, numeric);
                    const max = Math.max.apply(null, numeric);
                    const pad = Math.max((max - min) * 0.12, 0.5);

                    return {
                        min: min - pad,
                        max: max + pad,
                    };
                }

                function buildMonitoringChartDatasets(rawData) {
                    const datasets = [];
                    const count = pointCount(rawData);
                    const referenceMode = rawData.reference_mode || 'optimum';
                    const unitSuffix = rawData.unit ? ' ' + rawData.unit : '';

                    if (referenceMode === 'range'
                        && rawData.range_fill_min !== null
                        && rawData.range_fill_max !== null
                        && count > 0) {
                        datasets.push({
                            label: 'Acceptable range (lower)',
                            data: constantSeries(rawData.range_fill_min, count),
                            borderColor: 'rgba(34, 197, 94, 0)',
                            backgroundColor: 'rgba(34, 197, 94, 0.15)',
                            borderWidth: 0,
                            pointRadius: 0,
                            fill: false,
                            order: 20,
                        });
                        datasets.push({
                            label: 'Acceptable range',
                            data: constantSeries(rawData.range_fill_max, count),
                            borderColor: 'rgba(34, 197, 94, 0)',
                            backgroundColor: 'rgba(34, 197, 94, 0.15)',
                            borderWidth: 0,
                            pointRadius: 0,
                            fill: '-1',
                            order: 21,
                        });
                    }

                    if (hasNumericSeries(rawData.actual_um_high) && hasNumericSeries(rawData.actual_um_low)) {
                        datasets.push({
                            label: 'Measurement uncertainty (lower)',
                            data: rawData.actual_um_low,
                            borderColor: 'rgba(59, 130, 246, 0)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderWidth: 0,
                            pointRadius: 0,
                            fill: false,
                            order: 18,
                        });
                        datasets.push({
                            label: 'Measurement uncertainty (±U.M' + unitSuffix + ')',
                            data: rawData.actual_um_high,
                            borderColor: 'rgba(59, 130, 246, 0)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderWidth: 0,
                            pointRadius: 0,
                            fill: '-1',
                            order: 19,
                        });
                    }

                    if (referenceMode === 'range') {
                        if (hasNumericSeries(rawData.min)) {
                            datasets.push({
                                label: 'Lower limit (' + (rawData.reference?.min ?? '') + unitSuffix + ')',
                                data: rawData.min,
                                borderColor: '#f59e0b',
                                borderWidth: 2,
                                borderDash: [8, 4],
                                pointRadius: 0,
                                fill: false,
                                order: 12,
                            });
                        }

                        if (hasNumericSeries(rawData.max)) {
                            datasets.push({
                                label: 'Upper limit (' + (rawData.reference?.max ?? '') + unitSuffix + ')',
                                data: rawData.max,
                                borderColor: '#ef4444',
                                borderWidth: 2,
                                borderDash: [8, 4],
                                pointRadius: 0,
                                fill: false,
                                order: 11,
                            });
                        }

                        if (rawData.optimum_level !== null && rawData.optimum_level !== undefined && count > 0) {
                            datasets.push({
                                label: 'Optimum level (' + rawData.optimum_level + unitSuffix + ')',
                                data: constantSeries(rawData.optimum_level, count),
                                borderColor: '#15803d',
                                borderWidth: 2,
                                borderDash: [6, 4],
                                pointRadius: 0,
                                fill: false,
                                order: 10,
                            });
                        }
                    } else if (hasNumericSeries(rawData.optimum)) {
                        const optimumLabel = referenceMode === 'constant' ? 'Target value' : 'Optimum level';
                        datasets.push({
                            label: optimumLabel,
                            data: rawData.optimum,
                            borderColor: '#dc2626',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false,
                            order: 10,
                        });
                    }

                    if (rawData.include_uncertainty_on_optimum) {
                        if (hasNumericSeries(rawData.min_um_low)) {
                            datasets.push({
                                label: 'Lower limit − U.M',
                                data: rawData.min_um_low,
                                borderColor: 'rgba(245, 158, 11, 0.9)',
                                borderWidth: 1.5,
                                borderDash: [3, 3],
                                pointRadius: 0,
                                fill: false,
                                order: 9,
                            });
                        }

                        if (hasNumericSeries(rawData.max_um_high)) {
                            datasets.push({
                                label: 'Upper limit + U.M',
                                data: rawData.max_um_high,
                                borderColor: 'rgba(239, 68, 68, 0.9)',
                                borderWidth: 1.5,
                                borderDash: [3, 3],
                                pointRadius: 0,
                                fill: false,
                                order: 9,
                            });
                        }

                        if (hasNumericSeries(rawData.optimum_um_high)) {
                            datasets.push({
                                label: 'Optimum + U.M',
                                data: rawData.optimum_um_high,
                                borderColor: 'rgba(14, 165, 233, 0.85)',
                                borderWidth: 1.5,
                                borderDash: [4, 3],
                                pointRadius: 0,
                                fill: false,
                                order: 8,
                            });
                        }

                        if (hasNumericSeries(rawData.optimum_um_low)) {
                            datasets.push({
                                label: 'Optimum − U.M',
                                data: rawData.optimum_um_low,
                                borderColor: 'rgba(14, 165, 233, 0.85)',
                                borderWidth: 1.5,
                                borderDash: [4, 3],
                                pointRadius: 0,
                                fill: false,
                                order: 7,
                            });
                        }
                    }

                    datasets.push({
                        label: 'Measured (final reading)',
                        data: rawData.actual,
                        borderColor: '#2563eb',
                        backgroundColor: '#2563eb',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        tension: 0.25,
                        fill: false,
                        order: 1,
                    });

                    return datasets;
                }

                function scheduleMonitoringChartsInit() {
                    window.requestAnimationFrame(function () {
                        window.requestAnimationFrame(initMonitoringCharts);
                    });
                }

                function initMonitoringCharts() {
                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    document.querySelectorAll('.section-monitoring-chart[data-chart-data], .monitoring-legacy-chart[data-chart-data]').forEach(function (canvas) {
                        const chartId = canvas.id;
                        if (!chartId) {
                            return;
                        }

                        if (monitoringChartInstances[chartId]) {
                            monitoringChartInstances[chartId].destroy();
                            delete monitoringChartInstances[chartId];
                        }

                        const dataStr = canvas.getAttribute('data-chart-data');
                        if (!dataStr) {
                            return;
                        }

                        let rawData;
                        try {
                            rawData = JSON.parse(dataStr);
                        } catch (e) {
                            return;
                        }

                        if (!rawData || !rawData.hasData) {
                            return;
                        }

                        const yBounds = computeYScaleBounds(rawData);

                        const chart = new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels: rawData.labels,
                                datasets: buildMonitoringChartDatasets(rawData),
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        align: 'start',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 12,
                                            font: { size: 10 },
                                            filter: function (item) {
                                                return item.text !== 'Acceptable range (lower)';
                                            },
                                        },
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                const value = context.parsed.y;
                                                if (value === null || value === undefined) {
                                                    return null;
                                                }
                                                const suffix = rawData.unit ? ' ' + rawData.unit : '';

                                                return context.dataset.label + ': ' + value + suffix;
                                            },
                                        },
                                    },
                                },
                                scales: {
                                    x: {
                                        grid: { color: '#f1f5f9' },
                                        ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } },
                                    },
                                    y: {
                                        grid: { color: '#f1f5f9' },
                                        suggestedMin: yBounds.min,
                                        suggestedMax: yBounds.max,
                                        title: {
                                            display: !!rawData.unit,
                                            text: rawData.unit || '',
                                        },
                                    },
                                },
                            },
                        });

                        monitoringChartInstances[chartId] = chart;

                        window.requestAnimationFrame(function () {
                            chart.resize();
                        });
                    });
                }

                window.initMonitoringCharts = initMonitoringCharts;

                document.addEventListener('DOMContentLoaded', scheduleMonitoringChartsInit);

                document.addEventListener('livewire:navigated', scheduleMonitoringChartsInit);

                document.addEventListener('livewire:updated', function () {
                    setTimeout(scheduleMonitoringChartsInit, 100);
                });

                function registerMonitoringChartLivewireHooks() {
                    if (typeof Livewire === 'undefined') {
                        return;
                    }

                    Livewire.on('monitoring-charts-render', scheduleMonitoringChartsInit);

                    Livewire.hook('morph.updated', function () {
                        if (document.querySelector('.section-monitoring-chart[data-chart-data], .monitoring-legacy-chart[data-chart-data]')) {
                            scheduleMonitoringChartsInit();
                        }
                    });
                }

                document.addEventListener('livewire:initialized', registerMonitoringChartLivewireHooks);

                if (typeof Livewire !== 'undefined') {
                    registerMonitoringChartLivewireHooks();
                }
            })();
        </script>
    @endpush
@endonce