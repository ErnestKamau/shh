<?php

namespace App\Livewire\Monitoring;

use App\Actions\Monitoring\StoreMonitoringLogAction;
use App\Models\Equipments\Equipment;
use App\Models\Monitoring\MonitoringTemplate;
use App\Repositories\Monitoring\MonitoringLogRepository;
use App\Repositories\Monitoring\MonitoringTemplateRepository;
use App\Services\Monitoring\FormulaEngineService;
use App\Services\Monitoring\MonitoringAssignmentService;
use App\Services\Monitoring\MonitoringStatusService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MonitoringDashboard extends Component
{
    public string $activeSection = 'environmental';

    public ?string $selectedLabId = null;

    public bool $showExecutionModal = false;

    public ?string $activeTemplateId = null;

    public array $executionInputs = [];

    public bool $showTemplateEditModal = false;

    public ?string $editingTemplateId = null;

    public array $templateEditInputs = [
        'name' => '',
        'document_control_number' => '',
        'version' => 1,
        'status' => 'draft',
        'monitoring_category' => 'environmental',
        'is_active' => true,
    ];

    protected array $scopeMap = [
        'environmental' => 'environmental',
        'equipment' => 'equipment',
    ];

    public function mount(): void
    {
        $firstLab = $this->assignedLabs->first();
        $this->selectedLabId = $firstLab?->id;
    }

    public function getAssignedLabsProperty()
    {
        return app(MonitoringAssignmentService::class)->assignedLabsForUser(Auth::user());
    }

    public function getActiveScopeProperty(): string
    {
        return $this->scopeMap[$this->activeSection] ?? 'environmental';
    }

    public function getTodaysLogsProperty()
    {
        return app(MonitoringLogRepository::class)->todayByScopeAndLab(
            $this->activeScope,
            $this->selectedLabId,
        );
    }

    public function getTemplatesDueTodayProperty()
    {
        if (!in_array($this->activeSection, ['environmental', 'equipment'], true)) {
            return collect();
        }

        return app(MonitoringTemplateRepository::class)->activeByCategoryForLab(
            $this->activeSection,
            $this->selectedLabId,
        );
    }

    public function getTemplateEngineTemplatesProperty()
    {
        return MonitoringTemplate::query()
            ->withCount(['fields', 'formulaRules', 'logs'])
            ->latest()
            ->limit(20)
            ->get();
    }

    public function openTemplateEdit(string $templateId): void
    {
        $template = MonitoringTemplate::query()
            ->where('id', $templateId)
            ->where('company_id', Auth::user()?->company_id)
            ->first();

        if (!$template) {
            return;
        }

        $this->resetErrorBag();
        $this->editingTemplateId = $template->id;
        $this->templateEditInputs = [
            'name' => (string) $template->name,
            'document_control_number' => (string) ($template->document_control_number ?? ''),
            'version' => (int) $template->version,
            'status' => (string) $template->status,
            'monitoring_category' => (string) $template->monitoring_category,
            'is_active' => (bool) $template->is_active,
        ];

        $this->showTemplateEditModal = true;
    }

    public function closeTemplateEditModal(): void
    {
        $this->showTemplateEditModal = false;
        $this->editingTemplateId = null;
        $this->templateEditInputs = [
            'name' => '',
            'document_control_number' => '',
            'version' => 1,
            'status' => 'draft',
            'monitoring_category' => 'environmental',
            'is_active' => true,
        ];
        $this->resetErrorBag();
    }

    public function saveTemplateEdit(): void
    {
        if (!$this->editingTemplateId) {
            return;
        }

        $validated = $this->validate([
            'templateEditInputs.name' => 'required|string|max:255',
            'templateEditInputs.document_control_number' => 'nullable|string|max:255',
            'templateEditInputs.version' => 'required|integer|min:1',
            'templateEditInputs.status' => 'required|string|max:32',
            'templateEditInputs.monitoring_category' => 'required|in:environmental,equipment',
            'templateEditInputs.is_active' => 'required|boolean',
        ]);

        $template = MonitoringTemplate::query()
            ->where('id', $this->editingTemplateId)
            ->where('company_id', Auth::user()?->company_id)
            ->first();

        if (!$template) {
            $this->addError('templateEditInputs.name', 'Template not found.');
            return;
        }

        $template->fill([
            'name' => $validated['templateEditInputs']['name'],
            'document_control_number' => blank($validated['templateEditInputs']['document_control_number'])
                ? null
                : $validated['templateEditInputs']['document_control_number'],
            'version' => (int) $validated['templateEditInputs']['version'],
            'status' => $validated['templateEditInputs']['status'],
            'monitoring_category' => $validated['templateEditInputs']['monitoring_category'],
            'is_active' => (bool) $validated['templateEditInputs']['is_active'],
            'updated_by' => Auth::id(),
        ]);
        $template->save();

        session()->flash('success', 'Template updated successfully.');
        $this->closeTemplateEditModal();
    }

    public function clearTemplateLogs(string $templateId): void
    {
        $template = MonitoringTemplate::query()
            ->where('id', $templateId)
            ->where('company_id', Auth::user()?->company_id)
            ->first();

        if (!$template) {
            return;
        }

        $deletedCount = DB::transaction(function () use ($template): int {
            return (int) $template->logs()->delete();
        });

        if ($this->activeTemplateId === $template->id) {
            $this->closeExecutionModal();
        }

        session()->flash('success', "Cleared {$deletedCount} captured log(s) for template {$template->name}.");
    }

    public function deleteTemplate(string $templateId): void
    {
        $template = MonitoringTemplate::query()
            ->where('id', $templateId)
            ->where('company_id', Auth::user()?->company_id)
            ->first();

        if (!$template) {
            return;
        }

        DB::transaction(function () use ($template): void {
            $template->logs()->delete();
            $template->delete();
        });

        if ($this->activeTemplateId === $template->id) {
            $this->closeExecutionModal();
        }

        if ($this->editingTemplateId === $template->id) {
            $this->closeTemplateEditModal();
        }

        session()->flash('success', 'Template deleted successfully, including captured logs.');
    }

    public function getExecutionEquipmentsProperty()
    {
        if ($this->selectedLabId === null || $this->selectedLabId === '') {
            return collect();
        }

        $template = $this->activeTemplate;
        if (!$template) {
            return collect();
        }

        $metaField = $template->fields->firstWhere('field_key', '__meta_scope_items');
        $cfg = $metaField ? ($metaField->field_config ?? []) : [];
        $equipmentIds = [];

        if ($template->monitoring_category === 'equipment') {
            $equipmentIds = $cfg['equipment'] ?? [];
        } else {
            // For environmental templates, get equipment belonging to configured sections
            $sectionIds = $cfg['sections'] ?? [];
            if (!empty($sectionIds)) {
                $equipmentIds = \Illuminate\Support\Facades\DB::table('lab_sections')
                    ->whereIn('id', $sectionIds)
                    ->whereNotNull('equipment_id')
                    ->pluck('equipment_id')
                    ->toArray();
            }
        }

        $query = Equipment::query()
            ->where('active', true)
            ->where('lab_id', $this->selectedLabId);

        // Filter by configured equipment list if any are configured in the template metadata
        if (!empty($equipmentIds)) {
            $query->whereIn('id', $equipmentIds);
        }

        return $query->orderBy('name')->get(['id', 'name', 'equipment_number']);
    }

    public function getActiveTemplateProperty(): ?MonitoringTemplate
    {
        if (!$this->activeTemplateId) {
            return null;
        }

        return app(MonitoringTemplateRepository::class)->findTemplate($this->activeTemplateId);
    }

    public function getDashboardMetricsProperty(): array
    {
        $statusTotals = app(MonitoringLogRepository::class)->groupedStatusTotals(
            $this->activeScope,
            $this->selectedLabId,
        );

        $dueCount = $this->templatesDueToday->count();
        $completed = (int) ($statusTotals['completed'] ?? 0);
        $failed = (int) ($statusTotals['failed'] ?? 0);
        $pending = max($dueCount - $completed, 0);

        return [
            'due_today' => $dueCount,
            'completed' => $completed,
            'pending' => $pending,
            'failed' => $failed,
            'missing_submissions' => max($dueCount - ($completed + $failed), 0),
            'alerts' => $failed,
            'out_of_range' => $this->todaysLogs->whereIn('overall_result', ['OUT OF RANGE', 'CRITICAL'])->count(),
        ];
    }

    public function getEquipmentCalibrationAlertsProperty(): array
    {
        if ($this->selectedLabId === null || $this->selectedLabId === '') {
            return [
                'overdue' => 0,
                'nearing_due' => 0,
                'due_checks' => 0,
            ];
        }

        $equipmentItems = Equipment::query()
            ->where('active', true)
            ->where('lab_id', $this->selectedLabId)
            ->get();

        $overdue = 0;
        $nearing = 0;

        foreach ($equipmentItems as $equipment) {
            $calibrationState = $equipment->calibration_date();
            $remaining = (int) Arr::get($calibrationState, 'remaining_days', 0);

            if ($remaining < 0) {
                $overdue++;
                continue;
            }

            if ($equipment->calibration_notification_in_days !== null && $remaining <= (int) $equipment->calibration_notification_in_days) {
                $nearing++;
            }
        }

        return [
            'overdue' => $overdue,
            'nearing_due' => $nearing,
            'due_checks' => $equipmentItems->where('requires_daily_log', true)->count(),
        ];
    }

    public function switchSection(string $section): void
    {
        $allowed = ['environmental', 'equipment', 'templates'];
        if (!in_array($section, $allowed, true)) {
            return;
        }

        $this->activeSection = $section;
    }

    public function selectLab(string $labId): void
    {
        $this->selectedLabId = $labId;
    }

    public function resolveVariable(string $slug, ?string $equipmentId = null): mixed
    {
        if (empty($slug)) {
            return null;
        }

        // Handle predefined equipment dynamic variables using the dedicated snapshot service
        if (in_array($slug, ['correction_factor', 'uncertainty_of_measure'], true)) {
            if (empty($equipmentId)) {
                return null;
            }

            $snapshot = app(\App\Services\Monitoring\CalibrationSnapshotService::class)->latestForEquipment($equipmentId);
            if (!$snapshot) {
                return null;
            }

            if ($slug === 'correction_factor') {
                return $snapshot->correctionFactor;
            }

            if ($slug === 'uncertainty_of_measure') {
                return $snapshot->uncertaintyOfMeasure;
            }

            return null;
        }

        // Handle custom global constant variables
        $dbVar = \App\Models\Monitoring\MonitoringVariable::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($dbVar) {
            if ($dbVar->variable_type === 'constant') {
                return Arr::get($dbVar->value ?? [], 'constant_value');
            }
        }

        return null;
    }

    public function updatedExecutionInputsEquipmentId($value): void
    {
        $this->resolveDynamicVariables($value);
        $this->recomputeFormulaFields();
    }

    public function updatedExecutionInputs($value, $key): void
    {
        if ($key === 'equipment_id') {
            $this->resolveDynamicVariables($value);
        }
        // Always re-evaluate computed formula fields when any input changes
        $this->recomputeFormulaFields();
    }

    protected function resolveDynamicVariables(?string $equipmentId): void
    {
        $template = $this->activeTemplate;
        if (!$template) {
            return;
        }

        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');
            if ($variableSlug) {
                $resolved = $this->resolveVariable($variableSlug, $equipmentId);
                if ($resolved !== null) {
                    $this->executionInputs[$field->field_key] = $resolved;
                }
            }
        }
    }

    /**
     * Evaluate all active formula rules against current executionInputs
     * and write results back into executionInputs so computed fields
     * auto-fill in the modal.
     */
    protected function recomputeFormulaFields(): void
    {
        $template = $this->activeTemplate;
        if (!$template) {
            return;
        }

        $formulaEngine = app(FormulaEngineService::class);
        $equipmentId = Arr::get($this->executionInputs, 'equipment_id');

        // 1. Initialize all known dynamic and custom constant variables to safe defaults
        // so that formulas do not crash when equipment is unselected.
        $vars = [
            'correction_factor' => 0.0,
            'uncertainty_of_measure' => 0.0,
        ];

        // Fetch custom db-defined variables and set their constant values
        $dbVars = \App\Models\Monitoring\MonitoringVariable::where('is_active', true)->get();
        foreach ($dbVars as $dbVar) {
            if ($dbVar->variable_type === 'constant') {
                $vars[$dbVar->slug] = (float) Arr::get($dbVar->value ?? [], 'constant_value', 0.0);
            } else {
                $vars[$dbVar->slug] = 0.0;
            }
        }

        // Resolve equipment dynamic variables if an equipment is selected
        $resolvedCf = $this->resolveVariable('correction_factor', $equipmentId);
        if ($resolvedCf !== null) {
            $vars['correction_factor'] = (float) $resolvedCf;
        }
        $resolvedUom = $this->resolveVariable('uncertainty_of_measure', $equipmentId);
        if ($resolvedUom !== null) {
            $vars['uncertainty_of_measure'] = (float) $resolvedUom;
        }

        // 2. Add manual user inputs to vars ONLY if they have been filled.
        // If they are empty/null, we omit them so the formula engine
        // gracefully returns null ("waiting for inputs") until all inputs are supplied.
        foreach ($this->executionInputs as $key => $value) {
            if ($key === 'equipment_id') {
                continue;
            }
            if (is_numeric($value) && $value !== '') {
                $vars[$key] = (float) $value;
            } elseif ($value !== null && $value !== '') {
                $vars[$key] = $value;
            }
        }

        // 3. Evaluate formulas sequentially
        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $computed = $formulaEngine->evaluateSafe($rule->expression, $vars, null);
            if ($rule->output_key !== null && $rule->output_key !== '') {
                if ($computed !== null) {
                    if (is_numeric($computed)) {
                        $vars[$rule->output_key] = (float) $computed;
                        $this->executionInputs[$rule->output_key] = (float) $computed;
                    } else {
                        $vars[$rule->output_key] = $computed;
                        $this->executionInputs[$rule->output_key] = $computed;
                    }
                } else {
                    $this->executionInputs[$rule->output_key] = null;
                }
            }
        }
    }



    public function openExecution(string $templateId): void
    {
        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);
        if (!$template) {
            return;
        }

        $this->resetErrorBag();
        $this->activeTemplateId = $template->id;
        $this->executionInputs = [];

        // Set default equipment_id to blank
        $this->executionInputs['equipment_id'] = '';

        foreach ($template->fields as $field) {
            $this->executionInputs[$field->field_key] = Arr::get($field->field_config ?? [], 'default');
        }

        // Auto-resolve non-equipment (static) custom constant variables immediately
        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');
            if ($variableSlug && !in_array($variableSlug, ['correction_factor', 'uncertainty_of_measure'], true)) {
                $resolved = $this->resolveVariable($variableSlug);
                if ($resolved !== null) {
                    $this->executionInputs[$field->field_key] = $resolved;
                }
            }
        }

        // Auto-preselect equipment if exactly one matching equipment is available
        $equipments = $this->executionEquipments;
        if ($equipments->count() === 1) {
            $singleEqId = $equipments->first()->id;
            $this->executionInputs['equipment_id'] = $singleEqId;
            $this->resolveDynamicVariables($singleEqId);
        }

        // Eagerly evaluate formula rules so computed fields are pre-filled on open
        $this->recomputeFormulaFields();

        $this->showExecutionModal = true;
    }

    public function closeExecutionModal(): void
    {
        $this->showExecutionModal = false;
        $this->activeTemplateId = null;
        $this->executionInputs = [];
        $this->resetErrorBag();
    }

    public function saveExecution(): void
    {
        $template = $this->activeTemplateId
            ? app(MonitoringTemplateRepository::class)->findTemplate($this->activeTemplateId)
            : null;

        if (!$template) {
            $this->addError('execution', 'Unable to load template.');
            return;
        }

        $equipmentId = Arr::get($this->executionInputs, 'equipment_id');

        // Auto-resolve variable-bound fields before running validation
        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');
            if ($variableSlug && blank(Arr::get($this->executionInputs, $field->field_key))) {
                $resolved = $this->resolveVariable($variableSlug, $equipmentId);
                if ($resolved !== null) {
                    $this->executionInputs[$field->field_key] = $resolved;
                }
            }
        }

        foreach ($template->fields as $field) {
            if ($field->is_required && blank(Arr::get($this->executionInputs, $field->field_key))) {
                $this->addError('executionInputs.' . $field->field_key, $field->label . ' is required.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $formulaEngine = app(FormulaEngineService::class);
        $statusEngine = app(MonitoringStatusService::class);

        $entries = [];
        $aggregatedStatuses = [];
        $formulaVars = $this->executionInputs;

        foreach ($template->fields as $field) {
            $rawValue = Arr::get($this->executionInputs, $field->field_key);
            $fieldConfig = $field->field_config ?? [];

            $status = null;
            $pass = null;

            if (is_numeric($rawValue) && isset($fieldConfig['min'], $fieldConfig['max'])) {
                $status = $statusEngine->statusFromRange(
                    (float) $rawValue,
                    (float) $fieldConfig['min'],
                    (float) $fieldConfig['max'],
                    isset($fieldConfig['warning_margin']) ? (float) $fieldConfig['warning_margin'] : null,
                );
                $pass = in_array($status, ['IN RANGE', 'WARNING'], true);
                $aggregatedStatuses[] = $status;
            }

            $entries[] = [
                'template_field_id' => $field->id,
                'field_key' => $field->field_key,
                'field_label' => $field->label,
                'raw_value' => $rawValue,
                'computed_value' => null,
                'status' => $status,
                'pass' => $pass,
                'meta' => [
                    'field_type' => $field->field_type,
                ],
            ];
        }

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $computed = $formulaEngine->evaluateSafe($rule->expression, $formulaVars, null);
            if ($rule->output_key) {
                $formulaVars[$rule->output_key] = $computed;
            }

            $rulePass = null;
            if (!blank($rule->pass_condition_expression)) {
                $rulePass = $formulaEngine->normalizeBooleanResult(
                    $formulaEngine->evaluateSafe($rule->pass_condition_expression, $formulaVars, false)
                );
                $aggregatedStatuses[] = $rulePass ? 'PASS' : 'FAIL';
            }

            $entries[] = [
                'template_field_id' => null,
                'field_key' => $rule->output_key ?: 'formula.' . $rule->id,
                'field_label' => $rule->name,
                'raw_value' => null,
                'computed_value' => $computed,
                'status' => $rulePass === null ? null : ($rulePass ? 'PASS' : 'FAIL'),
                'pass' => $rulePass,
                'meta' => [
                    'type' => 'formula',
                    'expression' => $rule->expression,
                ],
            ];
        }

        $overallResult = $statusEngine->aggregate($aggregatedStatuses);
        $logStatus = in_array($overallResult, ['OUT OF RANGE', 'CRITICAL'], true) ? 'failed' : 'completed';

        app(StoreMonitoringLogAction::class)->execute($template, [
            'lab_id' => $this->selectedLabId,
            'equipment_id' => Arr::get($this->executionInputs, 'equipment_id'),
            'monitoring_scope' => $this->activeScope,
            'status' => $logStatus,
            'overall_result' => $overallResult,
            'deviation_triggered' => in_array($overallResult, ['OUT OF RANGE', 'CRITICAL'], true),
            'payload' => [
                'inputs' => $this->executionInputs,
                'formula_variables' => $formulaVars,
            ],
            'entries' => $entries,
            'company_id' => Auth::user()?->company_id,
        ]);

        session()->flash('success', 'Monitoring log captured successfully.');
        $this->closeExecutionModal();
    }

    public function render()
    {
        return view('livewire.monitoring.monitoring-dashboard');
    }
}
