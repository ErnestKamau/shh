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
                        ['number' => 4, 'title' => 'Reading Structure', 'icon' => 'mdi-format-list-numbered'],
                        ['number' => 5, 'title' => 'Configured Fields', 'icon' => 'mdi-text-box-check'],
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
                                    @php $labSelected = in_array((string) $lab->id, $selectedLabIds, true); @endphp
                                    <div class="col-md-6 mb-3">
                                        <label for="lab_{{ $lab->id }}" class="lab-selector-card mb-0 {{ $labSelected ? 'selected' : '' }}">
                                            <div class="form-check mb-0">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       id="lab_{{ $lab->id }}"
                                                       value="{{ $lab->id }}"
                                                       wire:model.live="selectedLabIds">
                                                <span class="form-check-label w-100 d-block">
                                                    <strong>{{ $lab->name }}</strong>
                                                    @if($lab->location)
                                                        <span class="d-block small text-muted">{{ $lab->location }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </label>
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
                                                <th>Optimum Level</th>
                                                <th>Result Nature</th>
                                                <th>Frequency</th>
                                                <th>Equipment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->environmentalSectionsByLab as $section)
                                                <tr class="{{ in_array($section->id, $selectedSectionIds) ? 'table-active' : '' }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="section_{{ $section->id }}" {{ in_array($section->id, $selectedSectionIds) ? 'checked' : '' }} wire:change="toggleSection('{{ $section->id }}')">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="cmt-badge cmt-badge--lab">{{ $section->lab?->name }}</span>
                                                    </td>
                                                    <td><strong>{{ $section->name }}</strong></td>
                                                    <td><code class="cmt-code">{{ $section->code }}</code></td>
                                                    <td><span class="cmt-optimum">{{ $section->formattedOptimumLevel() }}</span></td>
                                                    <td>{{ $section->result_nature ?? '—' }}</td>
                                                    <td>
                                                        @php
                                                            $frequency = $section->reading_frequency ?? $section->equipment?->daily_log_frequency ?? 1;
                                                            $frequencyLabel = $section->reading_frequency
                                                                ? $section->readingFrequencyLabel()
                                                                : match($frequency) {
                                                                    1 => 'Once Daily',
                                                                    2 => 'Twice Daily',
                                                                    3 => 'Three Times Daily',
                                                                    4 => 'Four Times Daily',
                                                                    5 => 'Five Times Daily',
                                                                    6 => 'Six Times Daily',
                                                                    default => 'As Set (' . $frequency . '×)',
                                                                };
                                                        @endphp
                                                        <span class="cmt-badge cmt-badge--frequency">{{ $frequencyLabel }}</span>
                                                        @php $scheduleSummary = $section->formattedReadingFrequencySchedule(); @endphp
                                                        @if($scheduleSummary !== '—')
                                                            <div class="small text-muted mt-1">{{ $scheduleSummary }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($section->equipment)
                                                            <span class="cmt-badge cmt-badge--equipment">{{ $section->equipment->name }}</span>
                                                            @if($section->equipment->latestCalibration)
                                                                <div class="small text-muted mt-1">CF: {{ $section->equipment->latestCalibration->correction_factor ?? '—' }}</div>
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

                    @if($currentStep === 4)
                        @include('livewire.monitoring.partials.reading-structure-step')
                    @endif

                    @if($currentStep === 5)
                        @include('livewire.monitoring.partials.configured-fields-step')
                    @endif

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" @if($currentStep === 1) disabled @endif wire:click="previousStep">
                            <i class="mdi mdi-arrow-left"></i> Previous
                        </button>

                        <div class="step-indicator text-muted">
                            Step {{ $currentStep }} of 5
                        </div>

                        @if($currentStep === 5)
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
            display: block;
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

        .monitoring-create-shell .cmt-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.3;
            border: 1px solid transparent;
            white-space: normal;
        }

        .monitoring-create-shell .cmt-badge--lab {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .monitoring-create-shell .cmt-badge--frequency {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .monitoring-create-shell .cmt-badge--equipment {
            background: #ecfeff;
            color: #0e7490;
            border-color: #a5f3fc;
        }

        .monitoring-create-shell .cmt-code {
            color: #be185d;
            background: #fdf2f8;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            font-size: 0.85em;
        }

        .monitoring-create-shell .cmt-optimum {
            font-weight: 600;
            color: #0f172a;
        }
    </style>
</div>
