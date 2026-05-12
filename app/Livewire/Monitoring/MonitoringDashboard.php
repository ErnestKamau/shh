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
        if (! in_array($this->activeSection, ['environmental', 'equipment'], true)) {
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

        if (! $template) {
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
        if (! $this->editingTemplateId) {
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

        if (! $template) {
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

        if (! $template) {
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

        if (! $template) {
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

        return Equipment::query()
            ->where('active', true)
            ->where('lab_id', $this->selectedLabId)
            ->orderBy('name')
            ->get(['id', 'name', 'equipment_number']);
    }

    public function getActiveTemplateProperty(): ?MonitoringTemplate
    {
        if (! $this->activeTemplateId) {
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
        if (! in_array($section, $allowed, true)) {
            return;
        }

        $this->activeSection = $section;
    }

    public function selectLab(string $labId): void
    {
        $this->selectedLabId = $labId;
    }

    public function openExecution(string $templateId): void
    {
        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);
        if (! $template) {
            return;
        }

        $this->resetErrorBag();
        $this->activeTemplateId = $template->id;
        $this->executionInputs = [];

        foreach ($template->fields as $field) {
            $this->executionInputs[$field->field_key] = Arr::get($field->field_config ?? [], 'default');
        }

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

        if (! $template) {
            $this->addError('execution', 'Unable to load template.');
            return;
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
            if (! blank($rule->pass_condition_expression)) {
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
