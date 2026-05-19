<div class="monitoring-create-shell container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                Edit Monitoring Template
                            </h2>
                            <p class="text-muted mb-0">Edit an existing template for environmental or equipment monitoring with dynamic field configuration.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="template-creation-progress">
                @php
                    $steps = [
                        ['number' => 1, 'title' => 'Basic Info', 'icon' => 'mdi-information-outline'],
                        ['number' => 2, 'title' => 'Select Labs', 'icon' => 'mdi-labs'],
                        ['number' => 3, 'title' => 'Select Items', 'icon' => 'mdi-checkbox-multiple-marked-outline'],
                        ['number' => 4, 'title' => 'Column Structure', 'icon' => 'mdi-table-large'],
                    ];
                @endphp

                @foreach($steps as $step)
                    <div class="progress-step {{ $currentStep >= $step['number'] ? 'active' : '' }} {{ $currentStep === $step['number'] ? 'current' : '' }}">
                        <div class="progress-step__badge">
                            @if($currentStep > $step['number'])
                                <i class="mdi mdi-check"></i>
                            @else
                                {{ $step['number'] }}
                            @endif
                        </div>
                        <div class="progress-step__content">
                            <h6 class="progress-step__title mb-0">{{ $step['title'] }}</h6>
                        </div>
                    </div>

                    @if(!$loop->last)
                        <div class="progress-connector {{ $currentStep > $step['number'] ? 'active' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <!-- Step 1: Basic Information -->
                    @if($currentStep === 1)
                        <div class="step-content">
                            <h5 class="mb-3">
                                <i class="mdi mdi-information-outline text-primary"></i>
                                Template Information
                            </h5>
                            <p class="text-muted mb-4">Enter basic information about your monitoring template.</p>

                            <form wire:submit="nextStep">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-label--modern">Template Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.defer="name" class="form-control form-control--modern" placeholder="e.g. Daily Temperature Log" required>
                                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-label--modern">Document Control Number</label>
                                        <input type="text" wire:model.defer="documentControlNumber" class="form-control form-control--modern" placeholder="e.g. MON-2026-001">
                                        @error('documentControlNumber') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label--modern">Version <span class="text-danger">*</span></label>
                                        <input type="number" wire:model.defer="version" class="form-control form-control--modern" min="1" value="1" required>
                                        @error('version') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label--modern">Effective Date</label>
                                        <input type="date" wire:model.defer="effectiveDate" class="form-control form-control--modern">
                                        @error('effectiveDate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label--modern">Status <span class="text-danger">*</span></label>
                                        <select wire:model.defer="status" class="form-control form-control--modern" required>
                                            <option value="draft">Draft</option>
                                            <option value="active">Active</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3">Monitoring Type <span class="text-danger">*</span></h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card type-selector {{ $templateType === 'environmental' ? 'active' : '' }}" wire:click="setTemplateType('environmental')" style="cursor: pointer;">
                                            <div class="card-body text-center p-4">
                                                <i class="mdi mdi-thermometer" style="font-size: 2.5rem; color: #0b6a90;"></i>
                                                <h6 class="mt-3 mb-1">Environmental</h6>
                                                <p class="text-muted small mb-0">Monitor lab conditions and environment</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="card type-selector {{ $templateType === 'equipment' ? 'active' : '' }}" wire:click="setTemplateType('equipment')" style="cursor: pointer;">
                                            <div class="card-body text-center p-4">
                                                <i class="mdi mdi-scale-balance" style="font-size: 2.5rem; color: #166534;"></i>
                                                <h6 class="mt-3 mb-1">Equipment</h6>
                                                <p class="text-muted small mb-0">Monitor equipment performance</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Step 2: Lab Selection -->
                    @if($currentStep === 2)
                        <div class="step-content">
                            <h5 class="mb-3">
                                <i class="mdi mdi-labs text-primary"></i>
                                Select Laboratories
                            </h5>
                            <p class="text-muted mb-4">Choose which laboratories this template applies to.</p>

                            <div class="row">
                                @forelse($this->assignedLabs as $lab)
                                    <div class="col-md-6 mb-3">
                                        <div class="lab-selector-card {{ in_array($lab->id, $selectedLabIds) ? 'selected' : '' }}" wire:click="toggleLab('{{ $lab->id }}')">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="lab_{{ $lab->id }}" {{ in_array($lab->id, $selectedLabIds) ? 'checked' : '' }} readonly>
                                                <label class="form-check-label w-100" for="lab_{{ $lab->id }}">
                                                    <strong>{{ $lab->name }}</strong>
                                                    @if($lab->location)
                                                        <div class="small text-muted">{{ $lab->location }}</div>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline"></i>
                                            No laboratories available. Please ensure you have lab access.
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            @error('selectedLabIds') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Step 3a: Environmental Sections Selection -->
                    @if($currentStep === 3 && $templateType === 'environmental')
                        <div class="step-content">
                            <h5 class="mb-3">
                                <i class="mdi mdi-table-large text-primary"></i>
                                Select Lab Sections
                            </h5>
                            <p class="text-muted mb-4">Choose sections within the selected labs to monitor.</p>

                            @if($this->environmentalSectionsByLab->count())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;"></th>
                                                <th>Lab</th>
                                                <th>Section Name</th>
                                                <th>Code</th>
                                                <th>Expected Value</th>
                                                <th>Range</th>
                                                <th>Unit & Reporting Unit</th>
                                                <th>Frequency</th>
                                                <th>Equipment</th>
                                            </tr>
                                        </thead>
                                        <!-- Columns: Checkbox, Lab, Section Name, Code, Expected Value, Range, Unit+Reporting Unit, Frequency -->
                                        <tbody>
                                            @foreach($this->environmentalSectionsByLab as $section)
                                                <tr class="{{ in_array($section->id, $selectedSectionIds) ? 'table-active' : '' }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="section_{{ $section->id }}" {{ in_array($section->id, $selectedSectionIds) ? 'checked' : '' }} wire:change="toggleSection('{{ $section->id }}')">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $section->lab?->name }}</span>
                                                    </td>
                                                    <td><strong>{{ $section->name }}</strong></td>
                                                    <td><code>{{ $section->code }}</code></td>
                                                    <td>{{ $section->expected_value ?? '—' }}</td>
                                                    <td>
                                                        @if($section->expected_min || $section->expected_max)
                                                            {{ $section->expected_min ?? '∞' }} – {{ $section->expected_max ?? '∞' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>{{ $section->result_nature ?? '—' }}</div>
                                                        @if($section->reportingUnit)
                                                            <small class="text-muted">Reporting: {{ $section->reportingUnit->name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $frequency = $section->equipment?->daily_log_frequency ?? 1;
                                                            $frequencyLabel = match($frequency) {
                                                                1 => 'Once Daily',
                                                                2 => 'Twice Daily',
                                                                3 => 'Three Times Daily',
                                                                4 => 'Four Times Daily',
                                                                5 => 'Five Times Daily',
                                                                6 => 'Six Times Daily',
                                                                default => 'As Set (' . $frequency . '×)',
                                                            };
                                                        @endphp
                                                        <span class="badge badge-pill badge-primary">{{ $frequencyLabel }}</span>
                                                    </td>
                                                    <td>
                                                        @if($section->equipment)
                                                            <span class="badge badge-info">{{ $section->equipment->name }}</span>
                                                            @if($section->equipment->latestCalibration)
                                                                <div class="small text-muted">CF: {{ $section->equipment->latestCalibration->correction_factor ?? '—' }}</div>
                                                            @endif
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert-outline"></i>
                                    No environmental monitoring sections found in the selected labs.
                                </div>
                            @endif

                            @error('selectedSectionIds') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Step 3b: Equipment Selection -->
                    @if($currentStep === 3 && $templateType === 'equipment')
                        <div class="step-content">
                            <h5 class="mb-3">
                                <i class="mdi mdi-tools text-primary"></i>
                                Select Equipment
                            </h5>
                            <p class="text-muted mb-4">Choose equipment within the selected labs to monitor.</p>

                            @if($this->equipmentByLab->count())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;"></th>
                                                <th>Lab</th>
                                                <th>Equipment Name</th>
                                                <th>Model</th>
                                                <th>Serial Number</th>
                                                <th>Expected Value</th>
                                                <th>Min / Max</th>
                                                <th>Correction Factor</th>
                                                <th>Unit</th>
                                                <th>Nature</th>
                                                <th>Frequency</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->equipmentByLab as $equipment)
                                                <tr class="{{ in_array($equipment->id, $selectedEquipmentIds) ? 'table-active' : '' }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="equip_{{ $equipment->id }}" {{ in_array($equipment->id, $selectedEquipmentIds) ? 'checked' : '' }} wire:change="toggleEquipment('{{ $equipment->id }}')">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $equipment->lab?->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td><strong>{{ $equipment->name }}</strong></td>
                                                    <td>{{ $equipment->model ?? '—' }}</td>
                                                    <td><code>{{ $equipment->serial_number ?? '—' }}</code></td>
                                                    <td>{{ $equipment->daily_log_expected_value ?? '—' }}</td>
                                                    <td>
                                                        @if($equipment->daily_log_expected_min !== null || $equipment->daily_log_expected_max !== null)
                                                            {{ $equipment->daily_log_expected_min ?? '∞' }} – {{ $equipment->daily_log_expected_max ?? '∞' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($equipment->latestCalibration)
                                                            <code>{{ $equipment->latestCalibration->correction_factor ?? '—' }}</code>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>{{ $equipment->daily_log_reporting_unit ?? '—' }}</td>
                                                    <td>{{ $equipment->daily_log_nature ?? '—' }}</td>
                                                    <td>
                                                        @php
                                                            $frequency = $equipment->daily_log_frequency ?? 1;
                                                            $frequencyLabel = match($frequency) {
                                                                1 => 'Once Daily',
                                                                2 => 'Twice Daily',
                                                                3 => 'Three Times Daily',
                                                                4 => 'Four Times Daily',
                                                                5 => 'Five Times Daily',
                                                                6 => 'Six Times Daily',
                                                                default => 'As Set (' . $frequency . '×)',
                                                            };
                                                        @endphp
                                                        <span class="badge badge-pill badge-primary">{{ $frequencyLabel }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert-outline"></i>
                                    No equipment with daily logging enabled found in the selected labs.
                                </div>
                            @endif

                            @error('selectedEquipmentIds') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Step 4: Column Structure -->
                    @if($currentStep === 4)
                        <div class="step-content">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="mdi mdi-table-large text-primary"></i>
                                        Define Column Structure
                                    </h5>
                                    <p class="text-muted mb-0 small">Create columns for your monitoring template (e.g., AM | PM with Initial/Final values).</p>
                                </div>
                                <button type="button" class="btn btn-outline-success btn-sm shadow-sm" wire:click="openVariableModal">
                                    <i class="mdi mdi-plus-box-outline"></i> Define Custom Variable
                                </button>
                            </div>

                            <div class="column-builder">
                                @forelse($columnStructure as $index => $column)
                                    <div class="column-card mb-4 p-4 border rounded shadow-sm transition-all" style="background: #ffffff; border-left: 5px solid #3b82f6; border-radius: 12px !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;">
                                        <!-- Column Card Header Toolbar -->
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-light text-primary rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="mdi mdi-view-column-outline fs-5"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark text-uppercase small tracking-wider" style="font-size: 0.8rem; letter-spacing: 0.05em;">Column Matrix #{{ $index + 1 }}</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-link text-danger p-0 border-0 d-flex align-items-center gap-1 text-decoration-none small hover-scale" wire:click="removeColumn('{{ $column['id'] }}')" title="Remove column" style="transition: all 0.2s; font-size: 0.85rem; font-weight: 500;">
                                                <i class="mdi mdi-delete-outline" style="font-size: 1.1rem;"></i> Remove Column
                                            </button>
                                        </div>

                                        <!-- Column Name Input Group -->
                                        <div class="mb-4">
                                            <label class="form-label text-secondary small fw-semibold mb-1">Column Name / Interval</label>
                                            <div class="input-group shadow-sm rounded-3">
                                                <span class="input-group-text bg-light border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                                                    <i class="mdi mdi-rename-box text-muted"></i>
                                                </span>
                                                <input type="text" value="{{ $column['name'] }}" wire:change="updateColumnName('{{ $column['id'] }}', $event.target.value)" class="form-control border-start-0 ps-2" placeholder="e.g. Morning (AM) / Afternoon (PM)" style="height: 42px; border-top-right-radius: 8px; border-bottom-right-radius: 8px; font-weight: 500; font-size: 0.95rem;">
                                            </div>
                                        </div>

                                        <!-- Rows Segment -->
                                        <div class="rows-container pt-3 border-top">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                                                    <i class="mdi mdi-format-list-bulleted-type text-muted me-1"></i> Row Fields Configuration
                                                </h6>
                                                <span class="badge bg-light text-secondary rounded-pill px-2 py-1 small" style="font-size: 0.75rem;">{{ count($column['rows']) }} Rows</span>
                                            </div>

                                            @forelse($column['rows'] as $rowIndex => $row)
                                                <div class="row-item p-3 mb-2 bg-light rounded border" id="row-item-{{ $row['id'] }}" style="border-radius: 8px !important; transition: all 0.3s ease;">
                                                    {{-- Row fields grid: drag handle | label | type | variable/formula | remove --}}
                                                    <div class="row-fields-grid">
                                                        <!-- Drag handle + index -->
                                                        <div class="row-field-handle text-muted d-flex align-items-center gap-1">
                                                            <i class="mdi mdi-drag-vertical fs-5" style="cursor: grab;" title="Drag to reorder"></i>
                                                            <span class="small fw-bold" style="font-size: 0.75rem;">#{{ $rowIndex + 1 }}</span>
                                                        </div>

                                                        <!-- Row Label & Key -->
                                                        <div class="row-field-label">
                                                            <label class="form-label text-secondary small fw-semibold mb-1">Row Label</label>
                                                            <input type="text" value="{{ $row['label'] }}" wire:change="updateRowLabel('{{ $column['id'] }}', '{{ $row['id'] }}', $event.target.value)" class="form-control form-control-sm shadow-sm" placeholder="e.g. Temperature / Reading" style="height: 38px; border-radius: 6px; font-size: 0.875rem;">
                                                            @if(!empty($row['field_key']))
                                                                <div class="mt-1">
                                                                    <span class="badge bg-white border text-secondary shadow-sm" style="font-family: monospace; font-size: 0.7rem;"><i class="mdi mdi-key-variant text-muted me-1"></i>{{ $row['field_key'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Row Type Selection -->
                                                        <div class="row-field-type">
                                                            <label class="form-label text-secondary small fw-semibold mb-1">Row Type</label>
                                                            <select wire:change="updateRowType('{{ $column['id'] }}', '{{ $row['id'] }}', $event.target.value)" class="form-select form-select-sm shadow-sm text-secondary" style="height: 38px; border-radius: 6px; font-size: 0.875rem; font-weight: 500;">
                                                                <option value="input" {{ ($row['type'] ?? 'input') === 'input' ? 'selected' : '' }}>Data Input (Manual)</option>
                                                                <option value="formula" {{ ($row['type'] ?? 'input') === 'formula' ? 'selected' : '' }}>Computed Formula</option>
                                                            </select>
                                                        </div>

                                                        <!-- Variable / Formula button -->
                                                        <div class="row-field-variable">
                                                            @if(($row['type'] ?? 'input') === 'formula')
                                                                <label class="form-label text-secondary small fw-semibold mb-1">Expression</label>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1 fw-semibold"
                                                                    style="height: 38px; background: #eff6ff; color: #1d4ed8; border: 1.5px solid #93c5fd; border-radius: 6px; font-size: 0.82rem; transition: all 0.2s;"
                                                                    onclick="(function(){
                                                                        var target = document.getElementById('expr-panel-{{ $row['id'] }}');
                                                                        if(target){
                                                                            target.scrollIntoView({behavior:'smooth', block:'center'});
                                                                            target.classList.add('expr-panel-flash');
                                                                            setTimeout(function(){ target.classList.remove('expr-panel-flash'); }, 1400);
                                                                        }
                                                                    })()"
                                                                    title="Click to jump to expression editor below"
                                                                >
                                                                    <i class="mdi mdi-function-variant"></i> Configure expression below
                                                                </button>
                                                            @else
                                                                <label class="form-label text-secondary small fw-semibold mb-1">Attached Variable <span class="text-muted fw-normal">(Optional)</span></label>
                                                                <select wire:change="updateRowVariable('{{ $column['id'] }}', '{{ $row['id'] }}', $event.target.value)" class="form-select form-select-sm shadow-sm text-secondary" style="height: 38px; border-radius: 6px; font-size: 0.875rem; font-weight: 500;">
                                                                    <option value="">-- None (Manual Input) --</option>
                                                                    @foreach($this->availableVariables as $var)
                                                                        <option value="{{ $var['slug'] }}" {{ ($row['variable_slug'] ?? '') === $var['slug'] ? 'selected' : '' }}>
                                                                            {{ $var['name'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @endif
                                                        </div>

                                                        <!-- Remove row button -->
                                                        <div class="row-field-remove d-flex align-items-end pb-0">
                                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm" wire:click="removeRowFromColumn('{{ $column['id'] }}', '{{ $row['id'] }}')" title="Remove row" style="width: 34px; height: 34px; flex-shrink: 0; border-color: #fca5a5; background: #fff; transition: all 0.2s;">
                                                                <i class="mdi mdi-close" style="font-size: 0.95rem;"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    @if(($row['type'] ?? 'input') === 'formula')
                                                        <!-- Derived Expression Configuration Full-Width Block -->
                                                        <div class="card bg-white border shadow-sm mt-3 mb-2" id="expr-panel-{{ $row['id'] }}">
                                                            <div class="card-header bg-light border-bottom py-2">
                                                                <h6 class="mb-0 text-muted fw-bold" style="font-size: 0.9rem;">
                                                                    <i class="mdi mdi-function text-primary me-1"></i> Expression Configuration
                                                                </h6>
                                                            </div>
                                                            <div class="card-body p-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold small text-dark mb-1">Expression <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <textarea wire:model.lazy="columnStructure.{{ $index }}.rows.{{ $rowIndex }}.formula_expression" wire:change="updateRowFormulaExpression('{{ $column['id'] }}', '{{ $row['id'] }}', $event.target.value)" class="form-control font-monospace text-primary bg-light @if(isset($row['expression_valid'])) {{ $row['expression_valid'] ? 'is-valid' : 'is-invalid' }} @endif" rows="2" placeholder="e.g., (temperature * 1.8) + 32">{{ $row['formula_expression'] ?? '' }}</textarea>
                                                                        <button type="button" wire:click="validateRowExpression('{{ $column['id'] }}', '{{ $row['id'] }}')" class="btn btn-outline-info d-flex flex-column align-items-center justify-content-center px-4" style="border-top-right-radius: 6px; border-bottom-right-radius: 6px;">
                                                                            <i class="mdi mdi-check-circle-outline fs-5 mb-1"></i> Validate
                                                                        </button>
                                                                    </div>
                                                                    @if(isset($row['expression_valid']) && !$row['expression_valid'])
                                                                        <div class="invalid-feedback d-block mt-2 fw-semibold">
                                                                            <i class="mdi mdi-alert-circle outline"></i> {{ $row['expression_message'] }}
                                                                        </div>
                                                                    @elseif(isset($row['expression_valid']) && $row['expression_valid'])
                                                                        <div class="valid-feedback d-block mt-2 fw-semibold">
                                                                            <i class="mdi mdi-check-circle outline"></i> {{ $row['expression_message'] }}
                                                                        </div>
                                                                    @endif
                                                                    <div class="form-text small mt-1">Use variable names from previous steps in your expression. Available variables are listed below.</div>
                                                                </div>
                                                                
                                                                <!-- Available Variables Reference -->
                                                                <div class="row g-3">
                                                                    <div class="col-12">
                                                                        <h6 class="text-muted mb-2 small fw-bold border-bottom pb-1">Available Variables</h6>
                                                                        @php
                                                                            $rowVars = $this->getAvailableRowVariables($column['id'], $row['id']);
                                                                        @endphp
                                                                        @if(count($rowVars) > 0)
                                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                                @foreach($rowVars as $var)
                                                                                    <div class="badge bg-light text-dark border p-2 d-flex align-items-center shadow-sm" style="cursor: pointer;" onclick="document.querySelector('textarea[wire\\:model\\.lazy=\\'columnStructure.{{ $index }}.rows.{{ $rowIndex }}.formula_expression\\']').value += ' {{ $var['name'] }} '; document.querySelector('textarea[wire\\:model\\.lazy=\\'columnStructure.{{ $index }}.rows.{{ $rowIndex }}.formula_expression\\']').dispatchEvent(new Event('change'));" title="Click to insert">
                                                                                        <div class="me-2 text-start">
                                                                                            <code class="text-primary fw-bold">{{ $var['name'] }}</code>
                                                                                            <small class="text-muted d-block" style="font-size: 0.65rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $var['label'] }}</small>
                                                                                        </div>
                                                                                        <span class="badge badge-{{ $var['type'] === 'input' ? 'success' : ($var['type'] === 'formula' ? 'info' : 'secondary') }} badge-sm rounded-pill ms-auto" style="font-size: 0.6rem;">
                                                                                            {{ ucfirst($var['type']) }}
                                                                                        </span>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <p class="text-muted small mb-0"><i class="mdi mdi-information-outline"></i> No variables available. Add input rows first.</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="alert alert-sm alert-info py-2 px-3 rounded-3 mb-2" style="font-size: 0.85rem;">
                                                    <i class="mdi mdi-information-outline me-1"></i> No rows defined in this column yet. Click below to add one.
                                                </div>
                                            @endforelse

                                            <!-- Add Row Button link-style -->
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 mt-1 shadow-sm d-inline-flex align-items-center gap-1 fw-semibold hover-scale" wire:click="addRowToColumn('{{ $column['id'] }}')" style="font-size: 0.8rem; transition: all 0.2s;">
                                                    <i class="mdi mdi-plus-circle-outline fs-6"></i> Add Field Row
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info py-4 px-4 text-center rounded-3 mb-4 shadow-sm" style="border-left: 4px solid #10b981;">
                                        <div class="fs-1 text-muted mb-2">
                                            <i class="mdi mdi-table-row-plus-before"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">No Columns Defined Yet</h6>
                                        <p class="text-muted small mb-0">Add your first grid column to design the monitoring checklist structure.</p>
                                    </div>
                                @endforelse

                                <!-- Add New Column Primary Gradient Action -->
                                <div class="text-start">
                                    <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 py-2 mt-2 shadow d-inline-flex align-items-center gap-2 fw-semibold border-0 hover-scale" wire:click="addColumn" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); font-size: 0.95rem; transition: all 0.25s;">
                                        <i class="mdi mdi-table-column-plus-after fs-5"></i> Add New Column Matrix
                                    </button>
                                </div>
                            </div>


                            @error('columnStructure') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                            <!-- Define Custom Variable Modal Overlay -->
                            @if($showVariableModal)
                                <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1050;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                                            <div class="modal-header bg-light border-0 p-4" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                                <h5 class="modal-title font-weight-bold">
                                                    <i class="mdi mdi-plus-box-outline text-primary me-2"></i> Define New Variable
                                                </h5>
                                                <button type="button" class="btn-close" wire:click="closeVariableModal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <form wire:submit.prevent="createCustomVariable">
                                                    <div class="mb-3">
                                                        <label class="form-label form-label--modern">Variable Name <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model.defer="newVariable.name" class="form-control form-control--modern" placeholder="e.g. Ambient Humidity Correction" required>
                                                        @error('newVariable.name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label form-label--modern">Variable Slug / Identifier <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model.defer="newVariable.slug" class="form-control form-control--modern" placeholder="e.g. ambient_humidity_corr" required>
                                                        <small class="text-muted">Use lowercase alphanumeric characters and underscores only.</small>
                                                        @error('newVariable.slug') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label form-label--modern">Constant Value <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model.defer="newVariable.constant_value" class="form-control form-control--modern" placeholder="e.g. 0.05" required>
                                                        @error('newVariable.constant_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label form-label--modern">Description</label>
                                                        <textarea wire:model.defer="newVariable.description" class="form-control form-control--modern" rows="3" placeholder="Optional description..."></textarea>
                                                        @error('newVariable.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                                        <button type="button" class="btn btn-outline-secondary" wire:click="closeVariableModal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="mdi mdi-check me-1"></i> Define Variable
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="alert alert-info mt-4">
                                <strong>Example Structure:</strong><br>
                                Column "AM" with rows: Initial Value, Final Value<br>
                                Column "PM" with rows: Initial Value, Final Value
                            </div>
                        </div>
                    @endif

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" @if($currentStep === 1) disabled @endif wire:click="previousStep">
                            <i class="mdi mdi-arrow-left"></i> Previous
                        </button>

                        <div class="step-indicator text-muted">
                            Step {{ $currentStep }} of 4
                        </div>

                        @if($currentStep === 4)
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" wire:click="cancel">
                                    Cancel
                                </button>
                                <button type="button" class="btn btn-success" wire:click="saveTemplate">
                                    <i class="mdi mdi-check"></i> Update Template
                                </button>
                            </div>
                        @else
                            <button type="button" class="btn btn-primary" wire:click="nextStep">
                                Next <i class="mdi mdi-arrow-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .template-creation-progress {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .progress-step__badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
        }

        .progress-step.active .progress-step__badge {
            background: #dbeafe;
            color: #0b6a90;
        }

        .progress-step.current .progress-step__badge {
            background: #3b82f6;
            color: white;
        }

        .progress-step__title {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
        }

        .progress-step.active .progress-step__title,
        .progress-step.current .progress-step__title {
            color: #1f2937;
        }

        .progress-connector {
            height: 2px;
            background: #e5e7eb;
            flex: 0 0 20px;
            margin: 0 10px;
        }

        .progress-connector.active {
            background: #3b82f6;
        }

        .type-selector {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .type-selector:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .type-selector.active {
            border-color: #3b82f6;
            background: #dbeafe;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .lab-selector-card {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .lab-selector-card:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .lab-selector-card.selected {
            border-color: #3b82f6;
            background: #dbeafe;
            font-weight: 500;
        }

        .column-card {
            transition: all 0.2s ease;
        }

        .column-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left-color: #10b981 !important;
        }

        .row-item {
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .rows-container {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        /* Row fields CSS grid — evenly spaced columns */
        .row-fields-grid {
            display: grid;
            grid-template-columns: 44px 2fr 1.4fr 2fr 44px;
            gap: 12px;
            align-items: end;
        }

        .row-field-handle {
            display: flex;
            align-items: flex-end;
            padding-bottom: 4px;
        }

        /* Flash highlight animation for expression panel */
        @keyframes exprPanelFlash {
            0%   { box-shadow: 0 0 0 3px rgba(59,130,246,0); background: #fff; }
            20%  { box-shadow: 0 0 0 4px rgba(59,130,246,0.55); background: #eff6ff; }
            60%  { box-shadow: 0 0 0 4px rgba(59,130,246,0.35); background: #eff6ff; }
            100% { box-shadow: 0 0 0 0px rgba(59,130,246,0); background: #fff; }
        }

        .expr-panel-flash {
            animation: exprPanelFlash 1.4s ease forwards;
            border-radius: 8px;
        }
    </style>
</div>
