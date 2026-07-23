<?php

namespace App\Livewire\Monitoring;

use App\Actions\Monitoring\CloneMonitoringTemplateAction;
use App\Actions\Monitoring\StoreMonitoringLogAction;
use App\Actions\Monitoring\UpdateMonitoringLogAction;
use App\Models\Monitoring\MonitoringLog;
use App\Services\Monitoring\MonitoringLogValueResolver;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\Monitoring\MonitoringTemplate;
use App\Repositories\Monitoring\MonitoringLogRepository;
use App\Repositories\Monitoring\MonitoringTemplateRepository;
use App\Services\Monitoring\FormulaEngineService;
use App\Services\Monitoring\MonitoringAssignmentService;
use App\Services\Monitoring\MonitoringSectionChartService;
use App\Services\Monitoring\MonitoringSectionLogMatrixService;
use App\Services\Monitoring\MonitoringStatusService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MonitoringDashboard extends Component
{
    public string $activeSection = 'environmental';

    public string $module = 'lab';

    public ?string $selectedLabId = null;

    public ?string $selectedSectionId = null;

    public string $logsDateRange = '30';

    public string $chartsDateRange = '30';

    public bool $chartIncludeUncertaintyOnOptimum = true;

    public string $sectionDetailTab = 'logs';

    /** @var array<string, array<string, mixed>> */
    public array $inlineCaptureInputsByTemplate = [];

    /** @var array<string, string> */
    public array $inlineCaptureRemarksByTemplate = [];

    /** @var array<string, bool> */
    public array $inlineCaptureShowDerivedByTemplate = [];

    /** @var array<string, string|null> */
    public array $inlineCaptureStatusPreviewByTemplate = [];

    public bool $showInlineFormulaTimelineModal = false;

    public ?string $inlineFormulaTimelineTemplateId = null;

    public ?string $inlineFormulaTimelineLogId = null;

    /** @var array<string, string> */
    public array $savedLogRemarksById = [];

    public ?string $editingSavedLogId = null;

    /** @var array<string, array<string, mixed>> */
    public array $editingSavedLogInputsById = [];

    /** @var array<string, string|null> */
    public array $editingSavedLogStatusPreviewById = [];

    public bool $showExecutionModal = false;

    public ?string $activeTemplateId = null;

    public array $executionInputs = [];

    public string $executionRemark = '';

    public ?int $executionFrequencySlot = null;

    public ?string $executionSectionId = null;

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

    public function mount(?string $activeSection = null, ?string $module = null): void
    {
        if ($activeSection !== null) {
            $this->activeSection = $activeSection;
        }
        if ($module !== null) {
            $this->module = $module;
        }
        $firstLab = $this->assignedLabs->first();
        $this->selectedLabId = $firstLab?->id;
        $this->autoSelectFirstEnvironmentalSection();
    }

    public function getAssignedLabsProperty()
    {
        return app(MonitoringAssignmentService::class)->monitoringLabsForUser(Auth::user());
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

    public function getEnvironmentalSectionsForLabProperty()
    {
        if ($this->selectedLabId === null || $this->selectedLabId === '') {
            return collect();
        }

        return LabSection::query()
            ->where('lab_id', $this->selectedLabId)
            ->where('does_environmental_analysis', true)
            ->where('active', true)
            ->with(['equipment.latestCalibration', 'reportingUnit'])
            ->orderBy('name')
            ->get();
    }

    public function getSelectedSectionProperty(): ?LabSection
    {
        if ($this->selectedSectionId === null || $this->selectedSectionId === '') {
            return null;
        }

        return LabSection::query()
            ->with(['equipment.latestCalibration', 'reportingUnit', 'lab'])
            ->where('id', $this->selectedSectionId)
            ->where('lab_id', $this->selectedLabId)
            ->where('does_environmental_analysis', true)
            ->first();
    }

    /**
     * @return list<array{template: \App\Models\Monitoring\MonitoringTemplate, matrix: array, chart: array}>
     */
    public function getSectionTemplateWorkspacesProperty(): array
    {
        $section = $this->selectedSection;

        if ($section === null) {
            return [];
        }

        $days = max(7, min(90, (int) $this->logsDateRange));
        $templates = app(MonitoringTemplateRepository::class)->activeForSection(
            $section->id,
            $this->selectedLabId,
        );

        $matrixService = app(MonitoringSectionLogMatrixService::class);
        $chartService = app(MonitoringSectionChartService::class);

        $workspaces = [];

        foreach ($templates as $template) {
            $matrix = $matrixService->build($section, $template, $days);
            $this->syncSavedLogRemarksFromMatrix($matrix);
            $workspaces[] = [
                'template' => $template,
                'matrix' => $matrix,
                'chart' => $chartService->build(
                    $section,
                    $template,
                    $this->chartsDateRange,
                    $this->chartIncludeUncertaintyOnOptimum,
                ),
            ];
        }

        return $workspaces;
    }

    /**
     * @param  array<string, mixed>  $matrix
     */
    protected function syncSavedLogRemarksFromMatrix(array $matrix): void
    {
        foreach ($matrix['rows'] ?? [] as $row) {
            foreach ($row['cells'] ?? [] as $cell) {
                if (! ($cell['filled'] ?? false) || empty($cell['log_id'])) {
                    continue;
                }

                $logId = (string) $cell['log_id'];

                if (! array_key_exists($logId, $this->savedLogRemarksById)) {
                    $this->savedLogRemarksById[$logId] = (string) ($cell['remark'] ?? '');
                }
            }
        }
    }

    /**
     * @return list<array{slot: int, label: string}>
     */
    public function getExecutionFrequencyOptionsProperty(): array
    {
        $section = $this->selectedSection;

        if ($section === null && $this->executionSectionId) {
            $section = LabSection::query()->find($this->executionSectionId);
        }

        if ($section === null) {
            return [];
        }

        return collect($section->normalizedReadingFrequencySchedule())
            ->map(fn (array $row): array => [
                'slot' => (int) $row['frequency'],
                'label' => filled($row['label']) ? $row['label'] : 'Reading '.(int) $row['frequency'],
            ])
            ->values()
            ->all();
    }

    public function getEnvironmentalGraphDataProperty(): array
    {
        if (!in_array($this->activeSection, ['environmental', 'equipment'], true)) {
            return [
                'labels' => [],
                'actual' => [],
                'optimum' => [],
                'min' => [],
                'max' => [],
                'hasData' => false,
            ];
        }

        $scope = $this->activeSection;

        // Fetch all logs for this lab and scope over the last 30 days
        $logs = \App\Models\Monitoring\MonitoringLog::query()
            ->with(['template.fields', 'entries'])
            ->where('monitoring_scope', $scope)
            ->where('lab_id', $this->selectedLabId)
            ->where('status', 'completed')
            ->orderBy('executed_at', 'asc')
            ->limit(15)
            ->get();

        // Fetch active lab sections for the selected lab with reportingUnit
        $sections = \App\LabSection::with('reportingUnit')
            ->where('lab_id', $this->selectedLabId)
            ->where('active', true)
            ->get()
            ->keyBy('id');

        $labels = [];
        $actualValues = [];
        $optimumLevels = [];
        $minLevels = [];
        $maxLevels = [];
        $unitName = '';

        foreach ($logs as $log) {
            // Find the metadata field on the template to get the section
            $metaField = $log->template->fields->firstWhere('field_type', 'metadata');
            $sectionIds = Arr::get($metaField->field_config ?? [], 'sections', []);
            
            $section = null;
            foreach ($sectionIds as $sid) {
                if (isset($sections[$sid])) {
                    $section = $sections[$sid];
                    break;
                }
            }

            // Extract numeric value from entry fields
            $numericVal = null;
            // Prioritize final_value or value-related keys
            foreach ($log->entries as $entry) {
                if (str_contains(strtolower($entry->field_key), 'final') || str_contains(strtolower($entry->field_key), 'value') || str_contains(strtolower($entry->field_key), 'result')) {
                    $val = $entry->computed_value !== null ? $entry->computed_value : $entry->raw_value;
                    if (is_numeric($val)) {
                        $numericVal = (float) $val;
                        break;
                    }
                }
            }
            // Fallback to first numeric entry
            if ($numericVal === null) {
                foreach ($log->entries as $entry) {
                    if ($entry->field_key === '__meta_scope_items') {
                        continue;
                    }
                    $val = $entry->computed_value !== null ? $entry->computed_value : $entry->raw_value;
                    if (is_numeric($val)) {
                        $numericVal = (float) $val;
                        break;
                    }
                }
            }

            if ($numericVal !== null) {
                $labels[] = optional($log->executed_at)->format('M d H:i') ?? $log->created_at->format('M d H:i');
                $actualValues[] = $numericVal;

                if ($section) {
                    $optimumLevels[] = is_numeric($section->optimum_level) ? (float) $section->optimum_level : null;
                    $minLevels[] = $section->expected_min !== null ? (float) $section->expected_min : null;
                    $maxLevels[] = $section->expected_max !== null ? (float) $section->expected_max : null;
                    if ($section->reportingUnit && empty($unitName)) {
                        $unitName = $section->reportingUnit->name;
                    }
                } else {
                    $optimumLevels[] = null;
                    $minLevels[] = null;
                    $maxLevels[] = null;
                }
            }
        }

        return [
            'labels' => $labels,
            'actual' => $actualValues,
            'optimum' => $optimumLevels,
            'min' => $minLevels,
            'max' => $maxLevels,
            'unit' => $unitName,
            'hasData' => count($labels) > 0,
        ];
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

    /**
     * Equipment available for LWS-011 export on the equipment monitoring tab.
     */
    public function getEquipmentExportOptionsProperty()
    {
        if ($this->activeSection !== 'equipment' || blank($this->selectedLabId)) {
            return collect();
        }

        $labId = (string) $this->selectedLabId;
        $idsFromTemplates = [];

        foreach ($this->templatesDueToday as $template) {
            $metaField = $template->fields->firstWhere('field_key', '__meta_scope_items');
            $cfg = is_array($metaField?->field_config) ? $metaField->field_config : [];
            foreach ((array) ($cfg['equipment'] ?? []) as $equipmentId) {
                if (filled($equipmentId)) {
                    $idsFromTemplates[] = (string) $equipmentId;
                }
            }
        }

        return Equipment::query()
            ->where('active', true)
            ->where(function ($query) use ($labId, $idsFromTemplates) {
                $query->inLabs([$labId]);

                if ($idsFromTemplates !== []) {
                    $query->orWhereIn('id', array_values(array_unique($idsFromTemplates)));
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'equipment_number']);
    }

    public function getTemplateEngineTemplatesProperty()
    {
        return $this->managedTemplatesQuery()
            ->withCount(['fields', 'formulaRules', 'logs'])
            ->latest()
            ->limit(50)
            ->get();
    }

    public function openTemplateEdit(string $templateId): void
    {
        $template = $this->findManagedTemplate($templateId);

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
            'templateEditInputs.version' => 'required|string|max:255',
            'templateEditInputs.status' => 'required|string|max:32',
            'templateEditInputs.monitoring_category' => 'required|in:environmental,equipment',
            'templateEditInputs.is_active' => 'required|boolean',
        ]);

        $template = $this->findManagedTemplate((string) $this->editingTemplateId);

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
        $template = $this->findManagedTemplate($templateId);

        if (!$template) {
            session()->flash('error', 'Template not found.');

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
        $template = $this->findManagedTemplate($templateId);

        if (!$template) {
            session()->flash('error', 'Template not found.');

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

    public function cloneTemplate(string $templateId): void
    {
        // Resolve by id only — list may include templates from shared/legacy company rows.
        // The clone is always owned by the current user's company.
        $template = MonitoringTemplate::query()->find($templateId);

        if (! $template) {
            session()->flash('error', 'Template not found.');

            return;
        }

        try {
            $clone = app(CloneMonitoringTemplateAction::class)->execute($template);
            session()->flash('success', 'Template cloned as "'.$clone->name.'".');
            $this->activeSection = 'templates';
        } catch (\Throwable $e) {
            Log::error('Failed to clone monitoring template', [
                'template_id' => $templateId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Could not clone template: '.$e->getMessage());
        }
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
        $cfg = [];
        if ($metaField) {
            $cfg = $metaField->field_config;
            if (is_string($cfg)) {
                $cfg = json_decode($cfg, true) ?: [];
            }
        }
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
            ->where('active', true);

        // Filter by configured equipment list if any are configured in the template metadata
        if (!empty($equipmentIds)) {
            $equipmentIds = array_filter((array) $equipmentIds);
            if (!empty($equipmentIds)) {
                $query->whereIn('id', $equipmentIds);
            } else {
                $query->where('lab_id', $this->selectedLabId);
            }
        } else {
            $query->where('lab_id', $this->selectedLabId);
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

        if ($section === 'environmental') {
            $this->autoSelectFirstEnvironmentalSection();
        }
    }

    public function selectLab(string $labId): void
    {
        $this->selectedLabId = $labId;
        $this->autoSelectFirstEnvironmentalSection();
    }

    public function selectSection(string $sectionId): void
    {
        $this->selectedSectionId = $sectionId;
        $this->sectionDetailTab = 'logs';
        $this->initializeInlineCaptureForSection();
    }

    public function setSectionDetailTab(string $tab): void
    {
        if (in_array($tab, ['logs', 'charts'], true)) {
            $this->sectionDetailTab = $tab;

            if ($tab === 'charts') {
                $this->dispatchMonitoringChartsRender();
            }
        }
    }

    public function updatedChartsDateRange(): void
    {
        $this->dispatchMonitoringChartsRender();
    }

    public function updatedChartIncludeUncertaintyOnOptimum(): void
    {
        $this->dispatchMonitoringChartsRender();
    }

    protected function dispatchMonitoringChartsRender(): void
    {
        $this->dispatch('monitoring-charts-render');
    }

    protected function autoSelectFirstEnvironmentalSection(): void
    {
        if ($this->activeSection !== 'environmental') {
            return;
        }

        $first = $this->environmentalSectionsForLab->first();

        if ($first === null) {
            $this->selectedSectionId = null;

            return;
        }

        if ($this->selectedSectionId === null
            || ! $this->environmentalSectionsForLab->contains('id', $this->selectedSectionId)) {
            $this->selectSection($first->id);
        }
    }

    public function toggleInlineCaptureDerived(string $templateId): void
    {
        $current = $this->inlineCaptureShowDerivedByTemplate[$templateId] ?? false;
        $this->inlineCaptureShowDerivedByTemplate[$templateId] = ! $current;
    }

    public function openInlineFormulaTimeline(string $templateId, ?string $logId = null): void
    {
        $this->inlineFormulaTimelineTemplateId = $templateId;
        $this->inlineFormulaTimelineLogId = $logId;
        $this->showInlineFormulaTimelineModal = true;

        if ($logId === null) {
            $this->recomputeInlineFormulaForTemplate($templateId);
        }
    }

    public function openSavedLogFormulaTimeline(string $logId): void
    {
        $log = MonitoringLog::query()->find($logId);

        if ($log === null) {
            return;
        }

        $this->openInlineFormulaTimeline((string) $log->template_id, $logId);
    }

    public function closeInlineFormulaTimeline(): void
    {
        $this->showInlineFormulaTimelineModal = false;
        $this->inlineFormulaTimelineTemplateId = null;
        $this->inlineFormulaTimelineLogId = null;
    }

    public function toggleEditSavedLog(string $logId): void
    {
        if ($this->editingSavedLogId === $logId) {
            $this->editingSavedLogId = null;
            unset($this->editingSavedLogInputsById[$logId], $this->editingSavedLogStatusPreviewById[$logId]);

            return;
        }

        $this->beginEditSavedLog($logId);
    }

    public function beginEditSavedLog(string $logId): void
    {
        $log = MonitoringLog::query()
            ->with(['entries', 'template.fields.formulaRule'])
            ->find($logId);

        if ($log === null) {
            return;
        }

        if (! array_key_exists($logId, $this->savedLogRemarksById)) {
            $this->savedLogRemarksById[$logId] = (string) ($log->resolvedRemark() ?? '');
        }

        $inputs = app(MonitoringLogValueResolver::class)->captureInputsFromLog($log);

        $this->resetErrorBag();
        $this->editingSavedLogId = $logId;
        $this->editingSavedLogInputsById[$logId] = $inputs;
        $this->recomputeEditingSavedLog($logId);
    }

    public function cancelEditSavedLog(): void
    {
        if ($this->editingSavedLogId === null) {
            return;
        }

        $logId = $this->editingSavedLogId;
        $this->editingSavedLogId = null;
        unset($this->editingSavedLogInputsById[$logId], $this->editingSavedLogStatusPreviewById[$logId]);
    }

    public function autoUpdateSavedLogCapture(string $logId): void
    {
        if ($this->editingSavedLogId !== $logId) {
            return;
        }

        $this->recomputeEditingSavedLog($logId);

        $log = MonitoringLog::query()->with('template.fields.formulaRule')->find($logId);

        if ($log === null || $log->template === null) {
            return;
        }

        $inputs = $this->editingSavedLogInputsById[$logId] ?? [];
        $remark = $this->savedLogRemarksById[$logId] ?? '';

        $this->persistMonitoringLogCapture(
            $log->template,
            $inputs,
            $remark,
            (int) ($log->resolvedFrequencySlot() ?? 0),
            $log->resolvedLabSectionId(),
            $log,
        );

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->savedLogRemarksById[$logId] = $remark;
    }

    public function recomputeEditingSavedLog(string $logId): void
    {
        $log = MonitoringLog::query()
            ->with('template.formulaRules')
            ->find($logId);

        if ($log === null || $log->template === null) {
            return;
        }

        $template = $log->template;
        $inputs = $this->editingSavedLogInputsById[$logId] ?? [];
        $formulaEngine = app(FormulaEngineService::class);
        $vars = $this->buildFormulaVariablesFromInputs($template, $inputs);

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $computed = $formulaEngine->evaluateSafe($rule->expression, $vars, null);

            if ($rule->output_key !== null && $rule->output_key !== '') {
                if ($computed !== null && is_numeric($computed)) {
                    $vars[$rule->output_key] = (float) $computed;
                    $inputs[$rule->output_key] = (float) $computed;
                } elseif ($computed !== null) {
                    $vars[$rule->output_key] = $computed;
                    $inputs[$rule->output_key] = $computed;
                } else {
                    $inputs[$rule->output_key] = null;
                }
            }
        }

        $statusPreview = $this->computeInlinePreviewStatus($template, $inputs, $vars);
        $this->editingSavedLogStatusPreviewById[$logId] = $statusPreview;

        if (
            array_key_exists('remark', $inputs)
            && ($inputs['remark'] === null || trim((string) $inputs['remark']) === '' || in_array(trim((string) $inputs['remark']), ['-', '—'], true))
            && $statusPreview !== null
        ) {
            $inputs['remark'] = $statusPreview;
        }

        $this->editingSavedLogInputsById[$logId] = $inputs;
    }

    public function autoSaveSavedLogRemark(string $logId): void
    {
        $this->saveSavedLogRemark($logId, true);
    }

    public function saveSavedLogRemark(string $logId, bool $silent = false): void
    {
        $log = MonitoringLog::query()->find($logId);

        if ($log === null) {
            return;
        }

        $remark = $this->savedLogRemarksById[$logId] ?? '';

        app(UpdateMonitoringLogAction::class)->updateRemark($log, $remark !== '' ? $remark : null);

        if (! $silent) {
            session()->flash('success', 'Comment saved.');
        }
    }

    public function updateSavedLogCapture(string $logId): void
    {
        $this->autoUpdateSavedLogCapture($logId);
    }

    /**
     * @return array{
     *   template: \App\Models\Monitoring\MonitoringTemplate|null,
     *   preview_status: string|null,
     *   section_range: array{min: float|null, max: float|null}|null,
     *   timeline: list<array{title: string, detail: string, meta: array<string, mixed>, tone: string}>
     * }
     */
    public function getInlineFormulaTimelineDataProperty(): array
    {
        $templateId = $this->inlineFormulaTimelineTemplateId;
        if (blank($templateId)) {
            return [
                'template' => null,
                'preview_status' => null,
                'section_range' => null,
                'timeline' => [],
            ];
        }

        $savedLog = null;
        if (filled($this->inlineFormulaTimelineLogId)) {
            $savedLog = MonitoringLog::query()
                ->with(['entries', 'template.formulaRules'])
                ->find($this->inlineFormulaTimelineLogId);
        }

        $template = $savedLog?->template
            ?? app(MonitoringTemplateRepository::class)->findTemplate((string) $templateId);

        if ($template === null) {
            return [
                'template' => null,
                'preview_status' => null,
                'section_range' => null,
                'timeline' => [],
                'is_saved_snapshot' => false,
            ];
        }

        if ($savedLog !== null) {
            $inputs = app(MonitoringLogValueResolver::class)->captureInputsFromLog($savedLog);
        } else {
            $inputs = $this->inlineCaptureInputsByTemplate[$template->id] ?? [];
        }

        $formulaEngine = app(FormulaEngineService::class);
        $vars = $this->buildFormulaVariablesFromInputs($template, $inputs);

        $timeline = [];
        $inputSnapshot = collect($inputs)
            ->except(['equipment_id'])
            ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
            ->map(fn ($value) => is_scalar($value) ? (string) $value : json_encode($value))
            ->all();

        $timeline[] = [
            'title' => $savedLog !== null ? 'Saved capture snapshot' : 'Input capture snapshot',
            'detail' => empty($inputSnapshot)
                ? ($savedLog !== null ? 'No stored inputs on this log.' : 'No operator inputs yet.')
                : ($savedLog !== null ? 'Values stored on this log entry.' : 'Active user inputs loaded for formula evaluation.'),
            'meta' => $inputSnapshot,
            'tone' => 'neutral',
        ];

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $computed = $formulaEngine->evaluateSafe($rule->expression, $vars, null);
            if (filled($rule->output_key)) {
                $vars[$rule->output_key] = $computed;
            }

            $ruleMeta = [
                'Expression' => $rule->expression,
                'Output key' => $rule->output_key ?: 'n/a',
                'Computed value' => $computed === null ? 'null' : (is_scalar($computed) ? (string) $computed : json_encode($computed)),
            ];

            if (filled($rule->pass_condition_expression)) {
                $rulePass = $formulaEngine->normalizeBooleanResult(
                    $formulaEngine->evaluateSafe($rule->pass_condition_expression, $vars, false)
                );
                $ruleMeta['Pass condition'] = $rule->pass_condition_expression;
                $ruleMeta['Pass result'] = $rulePass ? 'PASS' : 'FAIL';
            }

            $timeline[] = [
                'title' => $rule->name ?: 'Formula rule',
                'detail' => $savedLog !== null
                    ? 'Formula re-evaluated from saved inputs.'
                    : 'Formula evaluated using current live inputs.',
                'meta' => $ruleMeta,
                'tone' => 'formula',
            ];
        }

        $section = $this->selectedSection;
        $sectionRange = null;
        if ($section !== null && $section->expected_min !== null && $section->expected_max !== null) {
            $sectionRange = [
                'min' => (float) $section->expected_min,
                'max' => (float) $section->expected_max,
            ];

            $candidate = $this->extractInlineNumericResultCandidate($inputs, $vars);
            $timeline[] = [
                'title' => 'Section range check',
                'detail' => $candidate === null
                    ? 'No numeric final/result/value candidate available yet.'
                    : 'Live result compared against section minimum/maximum.',
                'meta' => [
                    'Candidate value' => $candidate === null ? 'n/a' : (string) $candidate,
                    'Expected min' => (string) $sectionRange['min'],
                    'Expected max' => (string) $sectionRange['max'],
                ],
                'tone' => 'range',
            ];
        }

        $previewStatus = $savedLog === null
            ? ($this->inlineCaptureStatusPreviewByTemplate[$template->id]
                ?? $this->computeInlinePreviewStatus($template, $inputs, $vars))
            : $this->computeInlinePreviewStatus($template, $inputs, $vars);

        $timeline[] = [
            'title' => $savedLog !== null ? 'Recorded status' : 'Live status preview',
            'detail' => $previewStatus
                ? ($savedLog !== null ? 'Verdict from saved values.' : 'Current inline verdict is ready.')
                : 'Status cannot be resolved yet.',
            'meta' => ['Status' => $previewStatus ?? 'Pending'],
            'tone' => $previewStatus === 'Fail' ? 'fail' : ($previewStatus === 'Pass' ? 'pass' : 'neutral'),
        ];

        return [
            'template' => $template,
            'preview_status' => $previewStatus,
            'section_range' => $sectionRange,
            'timeline' => $timeline,
            'is_saved_snapshot' => $savedLog !== null,
        ];
    }

    protected function initializeInlineCaptureForSection(): void
    {
        $this->inlineCaptureInputsByTemplate = [];
        $this->inlineCaptureRemarksByTemplate = [];
        $this->inlineCaptureShowDerivedByTemplate = [];
        $this->inlineCaptureStatusPreviewByTemplate = [];
        $this->savedLogRemarksById = [];
        $this->editingSavedLogId = null;
        $this->editingSavedLogInputsById = [];
        $this->editingSavedLogStatusPreviewById = [];
        $this->inlineFormulaTimelineLogId = null;

        $section = $this->selectedSection;

        if ($section === null) {
            return;
        }

        $templates = app(MonitoringTemplateRepository::class)->activeForSection(
            $section->id,
            $this->selectedLabId,
        );

        foreach ($templates as $template) {
            $this->resetInlineCaptureForTemplate($template->id);
        }
    }

    protected function resetInlineCaptureForTemplate(string $templateId): void
    {
        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);

        if ($template === null) {
            return;
        }

        $equipmentId = $this->selectedSection?->equipment_id ?? '';

        $inputs = ['equipment_id' => $equipmentId];

        foreach ($template->fields as $field) {
            if (in_array($field->field_type, ['metadata'], true) || $field->field_key === '__meta_scope_items') {
                continue;
            }

            if ($field->field_type === 'formula') {
                continue;
            }

            $inputs[$field->field_key] = Arr::get($field->field_config ?? [], 'default');
        }

        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');

            if ($variableSlug && ! in_array($variableSlug, ['correction_factor', 'uncertainty_of_measure'], true)) {
                $resolved = $this->resolveVariable($variableSlug, $equipmentId ?: null);

                if ($resolved !== null) {
                    $inputs[$field->field_key] = $resolved;
                }
            }
        }

        if ($equipmentId) {
            foreach ($template->fields as $field) {
                $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');

                if (in_array($variableSlug, ['correction_factor', 'uncertainty_of_measure'], true)) {
                    $resolved = $this->resolveVariable($variableSlug, $equipmentId);

                    if ($resolved !== null) {
                        $inputs[$field->field_key] = $resolved;
                    }
                }
            }
        }

        $this->inlineCaptureInputsByTemplate[$templateId] = $inputs;
        $this->inlineCaptureRemarksByTemplate[$templateId] = '';
        unset($this->inlineCaptureShowDerivedByTemplate[$templateId]);
        unset($this->inlineCaptureStatusPreviewByTemplate[$templateId]);
        $this->recomputeInlineFormulaForTemplate($templateId);
    }

    public function updatedInlineCaptureInputsByTemplate($value, string $name): void
    {
        if (str_contains($name, 'equipment_id')) {
            $parts = explode('.', $name);
            $templateId = $parts[1] ?? null;

            if ($templateId) {
                $this->recomputeInlineFormulaForTemplate($templateId);
            }
        }
    }

    public function recomputeInlineFormulaForTemplate(string $templateId): void
    {
        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);

        if ($template === null) {
            return;
        }

        $inputs = $this->inlineCaptureInputsByTemplate[$templateId] ?? [];
        $formulaEngine = app(FormulaEngineService::class);
        $vars = $this->buildFormulaVariablesFromInputs($template, $inputs);

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            $computed = $formulaEngine->evaluateSafe($rule->expression, $vars, null);

            if ($rule->output_key !== null && $rule->output_key !== '') {
                if ($computed !== null && is_numeric($computed)) {
                    $vars[$rule->output_key] = (float) $computed;
                    $inputs[$rule->output_key] = (float) $computed;
                } elseif ($computed !== null) {
                    $vars[$rule->output_key] = $computed;
                    $inputs[$rule->output_key] = $computed;
                } else {
                    $inputs[$rule->output_key] = null;
                }
            }
        }

        $statusPreview = $this->computeInlinePreviewStatus($template, $inputs, $vars);
        $this->inlineCaptureStatusPreviewByTemplate[$templateId] = $statusPreview;

        if (
            array_key_exists('remark', $inputs)
            && ($inputs['remark'] === null || trim((string) $inputs['remark']) === '' || in_array(trim((string) $inputs['remark']), ['-', '—'], true))
            && $statusPreview !== null
        ) {
            $inputs['remark'] = $statusPreview;
        }

        $this->inlineCaptureInputsByTemplate[$templateId] = $inputs;

        Log::info('Monitoring inline capture recompute', [
            'template_id' => $templateId,
            'template_name' => $template->name,
            'section_id' => $this->selectedSectionId,
            'lab_id' => $this->selectedLabId,
            'inputs' => $inputs,
            'formula_variables' => $vars,
            'formula_context' => [
                'expected_value_type' => $vars['expected_value_type'] ?? null,
                'expected_min' => $vars['expected_min'] ?? null,
                'expected_max' => $vars['expected_max'] ?? null,
                'expected_value' => $vars['expected_value'] ?? null,
                'optimum_level' => $vars['optimum_level'] ?? null,
            ],
            'status_preview' => $statusPreview,
            'remark_preview' => $inputs['remark'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $formulaVars
     */
    protected function computeInlinePreviewStatus(MonitoringTemplate $template, array $inputs, array $formulaVars): ?string
    {
        $formulaEngine = app(FormulaEngineService::class);
        $statusEngine = app(MonitoringStatusService::class);
        $aggregatedStatuses = [];
        $statusSources = [];

        $equipmentId = Arr::get($inputs, 'equipment_id');
        if (blank($equipmentId) && $this->selectedSection && filled($this->selectedSection->equipment_id)) {
            $equipmentId = (string) $this->selectedSection->equipment_id;
        }

        foreach ($template->fields as $field) {
            if (in_array($field->field_type, ['metadata', 'formula'], true) || $field->field_key === '__meta_scope_items') {
                continue;
            }

            $rawValue = Arr::get($inputs, $field->field_key);
            $fieldConfig = $field->field_config ?? [];
            $status = null;

            // Resolve input_config limits
            $resolvedInputConfig = $this->resolveFieldInputConfig($field, $equipmentId);
            if ($resolvedInputConfig && !blank($rawValue)) {
                $vType = $resolvedInputConfig['value_type'] ?? 'text';
                if ($vType === 'constant') {
                    $expected = trim((string)($resolvedInputConfig['expected_value'] ?? ''));
                    $actual = trim((string)$rawValue);
                    if (strcasecmp($actual, $expected) === 0) {
                        $status = 'IN RANGE';
                    } else {
                        $status = 'OUT OF RANGE';
                    }
                } elseif ($vType === 'range') {
                    $min = isset($resolvedInputConfig['min_value']) && $resolvedInputConfig['min_value'] !== '' ? (float)$resolvedInputConfig['min_value'] : null;
                    $max = isset($resolvedInputConfig['max_value']) && $resolvedInputConfig['max_value'] !== '' ? (float)$resolvedInputConfig['max_value'] : null;
                    
                    if (is_numeric($rawValue)) {
                        $valFloat = (float)$rawValue;
                        $isOut = false;
                        if ($min !== null && $valFloat < $min) {
                            $isOut = true;
                        }
                        if ($max !== null && $valFloat > $max) {
                            $isOut = true;
                        }
                        
                        if ($isOut) {
                            $status = 'OUT OF RANGE';
                        } else {
                            $status = 'IN RANGE';
                        }
                    } else {
                        $status = 'OUT OF RANGE';
                    }
                }
            }

            if ($status === null && is_numeric($rawValue) && isset($fieldConfig['min'], $fieldConfig['max'])) {
                $status = $statusEngine->statusFromRange(
                    (float) $rawValue,
                    (float) $fieldConfig['min'],
                    (float) $fieldConfig['max'],
                    isset($fieldConfig['warning_margin']) ? (float) $fieldConfig['warning_margin'] : null,
                );
            }

            if ($status !== null) {
                $aggregatedStatuses[] = $status;
                $statusSources[] = 'field_range:'.$field->field_key;
            }
        }

        foreach ($template->formulaRules->where('is_active', true) as $rule) {
            if (blank($rule->pass_condition_expression)) {
                continue;
            }

            $rulePass = $formulaEngine->normalizeBooleanResult(
                $formulaEngine->evaluateSafe($rule->pass_condition_expression, $formulaVars, false)
            );
            $aggregatedStatuses[] = $rulePass ? 'PASS' : 'FAIL';
            $statusSources[] = 'rule_pass_condition:'.$rule->id;
        }

        if ($aggregatedStatuses === []) {
            $formulaVerdict = $this->extractFormulaVerdictStatus($inputs, $formulaVars);

            if ($formulaVerdict !== null) {
                $aggregatedStatuses[] = $formulaVerdict;
                $statusSources[] = 'formula_output_verdict';
            }
        }

        if ($aggregatedStatuses === []) {
            $section = $this->selectedSection;
            $candidateValue = $this->extractInlineNumericResultCandidate($inputs, $formulaVars);

            if (
                $section !== null
                && $candidateValue !== null
                && $section->expected_min !== null
                && $section->expected_max !== null
            ) {
                $aggregatedStatuses[] = $statusEngine->statusFromRange(
                    $candidateValue,
                    (float) $section->expected_min,
                    (float) $section->expected_max,
                    null,
                );
                $statusSources[] = 'section_range_fallback';
            }
        }

        if ($aggregatedStatuses === []) {
            Log::info('Monitoring inline status preview unavailable', [
                'template_id' => $template->id,
                'section_id' => $this->selectedSectionId,
                'inputs_keys' => array_keys($inputs),
                'formula_variable_keys' => array_keys($formulaVars),
                'status_sources' => $statusSources,
            ]);
            return null;
        }

        $overallResult = $statusEngine->aggregate($aggregatedStatuses);

        if (in_array($overallResult, ['OUT OF RANGE', 'CRITICAL', 'FAIL', 'FAILED'], true)) {
            return 'Fail';
        }

        if (in_array($overallResult, ['IN RANGE', 'WARNING', 'PASS', 'PASSED'], true)) {
            return 'Pass';
        }

        return ucfirst(strtolower((string) $overallResult));
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $formulaVars
     */
    protected function extractInlineNumericResultCandidate(array $inputs, array $formulaVars): ?float
    {
        $preferredNeedles = ['final', 'result', 'value'];

        foreach ($preferredNeedles as $needle) {
            foreach ($formulaVars as $key => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                if (str_contains(strtolower((string) $key), $needle)) {
                    return (float) $value;
                }
            }
        }

        foreach ($preferredNeedles as $needle) {
            foreach ($inputs as $key => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                if (str_contains(strtolower((string) $key), $needle)) {
                    return (float) $value;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $formulaVars
     */
    protected function extractFormulaVerdictStatus(array $inputs, array $formulaVars): ?string
    {
        $verdictKeys = ['remark', 'status', 'result', 'final_status', 'overall_result'];

        foreach ($verdictKeys as $key) {
            $candidate = $formulaVars[$key] ?? $inputs[$key] ?? null;

            if (! is_scalar($candidate)) {
                continue;
            }

            $normalized = strtoupper(trim((string) $candidate));
            if (in_array($normalized, ['PASS', 'PASSED'], true)) {
                return 'PASS';
            }

            if (in_array($normalized, ['FAIL', 'FAILED'], true)) {
                return 'FAIL';
            }
        }

        return null;
    }

    public function autoSaveInlineCapture(string $templateId): void
    {
        $this->saveInlineCapture($templateId, true);
    }

    public function saveInlineCapture(string $templateId, bool $silent = false): void
    {
        if ($this->editingSavedLogId !== null) {
            $this->autoUpdateSavedLogCapture($this->editingSavedLogId);

            return;
        }

        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);
        $section = $this->selectedSection;

        if ($template === null || $section === null) {
            return;
        }

        $matrixService = app(MonitoringSectionLogMatrixService::class);
        $nextCapture = $matrixService->nextCaptureSlot($section, $template);

        if ($nextCapture === null) {
            if (! $silent) {
                session()->flash('success', 'All readings for today have been captured for this template.');
            }

            return;
        }

        $inputs = $this->inlineCaptureInputsByTemplate[$templateId] ?? [];
        $remark = $this->inlineCaptureRemarksByTemplate[$templateId] ?? '';

        $this->persistMonitoringLogCapture(
            $template,
            $inputs,
            $remark,
            (int) $nextCapture['slot'],
            $section->id,
        );

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->resetInlineCaptureForTemplate($templateId);
        if (! $silent) {
            session()->flash('success', 'Reading saved: '.$nextCapture['label'].'.');
        }
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
    protected function getFormulaVariables(): array
    {
        $template = $this->activeTemplate;

        if (! $template) {
            return [];
        }

        return $this->buildFormulaVariablesFromInputs($template, $this->executionInputs);
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    protected function buildFormulaVariablesFromInputs(MonitoringTemplate $template, array $inputs): array
    {
        $equipmentId = Arr::get($inputs, 'equipment_id');

        $vars = [
            'correction_factor' => 0.0,
            'uncertainty_of_measure' => 0.0,
        ];

        $dbVars = \App\Models\Monitoring\MonitoringVariable::where('is_active', true)->get();

        foreach ($dbVars as $dbVar) {
            if ($dbVar->variable_type === 'constant') {
                $vars[$dbVar->slug] = (float) Arr::get($dbVar->value ?? [], 'constant_value', 0.0);
            } else {
                $vars[$dbVar->slug] = 0.0;
            }
        }

        $resolvedCf = $this->resolveVariable('correction_factor', $equipmentId);

        if ($resolvedCf !== null) {
            $vars['correction_factor'] = (float) $resolvedCf;
        }

        $resolvedUom = $this->resolveVariable('uncertainty_of_measure', $equipmentId);

        if ($resolvedUom !== null) {
            $vars['uncertainty_of_measure'] = (float) $resolvedUom;
        }

        $contextSection = $this->selectedSection;
        if ($contextSection === null && filled($this->executionSectionId)) {
            $contextSection = LabSection::query()->find($this->executionSectionId);
        }

        if ($contextSection !== null) {
            if (filled($contextSection->expected_value_type)) {
                $vars['expected_value_type'] = (string) $contextSection->expected_value_type;
            }

            if ($contextSection->expected_min !== null) {
                $vars['expected_min'] = (float) $contextSection->expected_min;
            }

            if ($contextSection->expected_max !== null) {
                $vars['expected_max'] = (float) $contextSection->expected_max;
            }

            if ($contextSection->expected_value !== null && is_numeric($contextSection->expected_value)) {
                $vars['expected_value'] = (float) $contextSection->expected_value;
            }

            if ($contextSection->optimum_level !== null && is_numeric($contextSection->optimum_level)) {
                $vars['optimum_level'] = (float) $contextSection->optimum_level;
            } elseif (
                $contextSection->expected_min !== null
                && $contextSection->expected_max !== null
            ) {
                $vars['optimum_level'] = ((float) $contextSection->expected_min + (float) $contextSection->expected_max) / 2;
            }
        }

        foreach ($inputs as $key => $value) {
            if ($key === 'equipment_id') {
                continue;
            }

            if (is_numeric($value) && $value !== '') {
                $vars[$key] = (float) $value;
            } elseif ($value !== null && $value !== '') {
                $vars[$key] = $value;
            }
        }

        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');

            if ($variableSlug && $variableSlug !== '') {
                $fieldVal = Arr::get($inputs, $field->field_key);

                if (is_numeric($fieldVal) && $fieldVal !== '') {
                    $vars[$variableSlug] = (float) $fieldVal;
                } elseif ($fieldVal !== null && $fieldVal !== '') {
                    $vars[$variableSlug] = $fieldVal;
                }
            }
        }

        return $vars;
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function persistMonitoringLogCapture(
        MonitoringTemplate $template,
        array $inputs,
        string $remark,
        int $frequencySlot,
        ?string $labSectionId,
        ?MonitoringLog $existingLog = null,
    ): void {
        $equipmentId = Arr::get($inputs, 'equipment_id');

        if (blank($equipmentId) && filled($this->selectedSection?->equipment_id)) {
            $equipmentId = (string) $this->selectedSection->equipment_id;
            $inputs['equipment_id'] = $equipmentId;
        }

        foreach ($template->fields as $field) {
            $variableSlug = Arr::get($field->field_config ?? [], 'variable_slug');

            if ($variableSlug && blank(Arr::get($inputs, $field->field_key))) {
                $resolved = $this->resolveVariable($variableSlug, $equipmentId);

                if ($resolved !== null) {
                    $inputs[$field->field_key] = $resolved;
                }
            }
        }

        $inputErrorPrefix = $existingLog !== null && $this->editingSavedLogId === $existingLog->id
            ? 'editingSavedLogInputsById.'.$existingLog->id.'.'
            : 'inlineCaptureInputsByTemplate.'.$template->id.'.';

        foreach ($template->fields as $field) {
            if ($field->is_required && blank(Arr::get($inputs, $field->field_key))
                && ! in_array($field->field_type, ['metadata', 'formula'], true)
                && $field->field_key !== '__meta_scope_items') {
                $this->addError($inputErrorPrefix.$field->field_key, $field->label.' is required.');

                return;
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $formulaEngine = app(FormulaEngineService::class);
        $statusEngine = app(MonitoringStatusService::class);
        $formulaVars = $this->buildFormulaVariablesFromInputs($template, $inputs);
        $entries = [];
        $aggregatedStatuses = [];

        foreach ($template->fields as $field) {
            if (in_array($field->field_type, ['metadata', 'formula'], true) || $field->field_key === '__meta_scope_items') {
                continue;
            }

            $rawValue = Arr::get($inputs, $field->field_key);
            $fieldConfig = $field->field_config ?? [];
            $status = null;
            $pass = null;

            // Resolve input_config limits
            $resolvedInputConfig = $this->resolveFieldInputConfig($field, $equipmentId);
            if ($resolvedInputConfig && !blank($rawValue)) {
                $vType = $resolvedInputConfig['value_type'] ?? 'text';
                if ($vType === 'constant') {
                    $expected = trim((string)($resolvedInputConfig['expected_value'] ?? ''));
                    $actual = trim((string)$rawValue);
                    if (strcasecmp($actual, $expected) === 0) {
                        $status = 'IN RANGE';
                        $pass = true;
                    } else {
                        $status = 'OUT OF RANGE';
                        $pass = false;
                    }
                } elseif ($vType === 'range') {
                    $min = isset($resolvedInputConfig['min_value']) && $resolvedInputConfig['min_value'] !== '' ? (float)$resolvedInputConfig['min_value'] : null;
                    $max = isset($resolvedInputConfig['max_value']) && $resolvedInputConfig['max_value'] !== '' ? (float)$resolvedInputConfig['max_value'] : null;
                    
                    if (is_numeric($rawValue)) {
                        $valFloat = (float)$rawValue;
                        $isOut = false;
                        if ($min !== null && $valFloat < $min) {
                            $isOut = true;
                        }
                        if ($max !== null && $valFloat > $max) {
                            $isOut = true;
                        }
                        
                        if ($isOut) {
                            $status = 'OUT OF RANGE';
                            $pass = false;
                        } else {
                            $status = 'IN RANGE';
                            $pass = true;
                        }
                    } else {
                        $status = 'OUT OF RANGE';
                        $pass = false;
                    }
                }
            }

            if ($status === null && is_numeric($rawValue) && isset($fieldConfig['min'], $fieldConfig['max'])) {
                $status = $statusEngine->statusFromRange(
                    (float) $rawValue,
                    (float) $fieldConfig['min'],
                    (float) $fieldConfig['max'],
                    isset($fieldConfig['warning_margin']) ? (float) $fieldConfig['warning_margin'] : null,
                );
                $pass = in_array($status, ['IN RANGE', 'WARNING'], true);
            }

            if ($status !== null) {
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
                'meta' => ['field_type' => $field->field_type],
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
                'field_key' => $rule->output_key ?: 'formula.'.$rule->id,
                'field_label' => $rule->name,
                'raw_value' => null,
                'computed_value' => $computed,
                'status' => $rulePass === null ? null : ($rulePass ? 'PASS' : 'FAIL'),
                'pass' => $rulePass,
                'meta' => ['type' => 'formula', 'expression' => $rule->expression],
            ];
        }

        $overallResult = $statusEngine->aggregate($aggregatedStatuses);
        $logStatus = in_array($overallResult, ['OUT OF RANGE', 'CRITICAL'], true) ? 'failed' : 'completed';

        $payload = [
            'lab_id' => $this->selectedLabId,
            'lab_section_id' => $labSectionId ?: $this->selectedSectionId,
            'frequency_slot' => $frequencySlot,
            'remark' => $remark ?: null,
            'equipment_id' => blank($equipmentId) ? null : $equipmentId,
            'monitoring_scope' => $this->activeScope,
            'status' => $logStatus,
            'overall_result' => $overallResult,
            'deviation_triggered' => in_array($overallResult, ['OUT OF RANGE', 'CRITICAL'], true),
            'payload' => [
                'inputs' => $inputs,
                'formula_variables' => $formulaVars,
            ],
            'entries' => $entries,
            'company_id' => Auth::user()?->company_id,
        ];

        if ($existingLog !== null) {
            app(UpdateMonitoringLogAction::class)->execute($existingLog, $template, $payload);

            return;
        }

        app(StoreMonitoringLogAction::class)->execute($template, $payload);
    }

    protected function recomputeFormulaFields(): void
    {
        $template = $this->activeTemplate;
        if (!$template) {
            return;
        }

        $formulaEngine = app(FormulaEngineService::class);
        $vars = $this->getFormulaVariables();

        // Evaluate formulas sequentially
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
                    $vars[$rule->output_key] = null;
                    $this->executionInputs[$rule->output_key] = null;
                }
            }
        }
    }



    public function openExecution(string $templateId, ?string $sectionId = null, ?int $frequencySlot = null): void
    {
        $template = app(MonitoringTemplateRepository::class)->findTemplate($templateId);
        if (!$template) {
            return;
        }

        $this->resetErrorBag();
        $this->activeTemplateId = $template->id;
        $this->executionInputs = [];
        $this->executionRemark = '';
        $this->executionSectionId = $sectionId ?? $this->selectedSectionId;
        $this->executionFrequencySlot = $frequencySlot;

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

        // Preselect section equipment when available
        $section = $this->executionSectionId
            ? LabSection::query()->with('equipment')->find($this->executionSectionId)
            : null;

        if ($section?->equipment_id) {
            $this->executionInputs['equipment_id'] = $section->equipment_id;
            $this->resolveDynamicVariables($section->equipment_id);
        } elseif ($this->executionEquipments->count() === 1) {
            $singleEqId = $this->executionEquipments->first()->id;
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
        $this->executionRemark = '';
        $this->executionFrequencySlot = null;
        $this->executionSectionId = null;
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

        if ($this->activeSection === 'environmental'
            && $this->executionSectionId
            && count($this->executionFrequencyOptions) > 0
            && $this->executionFrequencySlot === null) {
            $this->addError('executionFrequencySlot', 'Please select a reading frequency.');

            return;
        }

        $this->persistMonitoringLogCapture(
            $template,
            $this->executionInputs,
            $this->executionRemark,
            (int) ($this->executionFrequencySlot ?? 1),
            $this->executionSectionId ?: $this->selectedSectionId,
        );

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        session()->flash('success', 'Monitoring log captured successfully.');
        $this->closeExecutionModal();
    }

    public function resolveFieldInputConfig($field, ?string $equipmentId = null): ?array
    {
        if (!$field) {
            return null;
        }

        $fieldConfig = $field->field_config ?? [];
        $inputConfig = $fieldConfig['input_config'] ?? null;
        
        if (!is_array($inputConfig)) {
            return null;
        }

        // If there's an override for the specific equipment, use it.
        if (filled($equipmentId) && isset($inputConfig['equipment_configs'][$equipmentId])) {
            $eqConfig = $inputConfig['equipment_configs'][$equipmentId];
            if (is_array($eqConfig) && ($eqConfig['value_type'] ?? 'text') !== 'text') {
                return $eqConfig;
            }
        }

        // Otherwise return the default/top-level configuration.
        if (($inputConfig['value_type'] ?? 'text') !== 'text') {
            return $inputConfig;
        }

        return null;
    }

    public function resolveFieldInputConfigForFieldKey(string $fieldKey, ?string $equipmentId = null): ?array
    {
        $template = $this->activeTemplate;
        if (!$template) {
            return null;
        }

        $field = $template->fields->firstWhere('field_key', $fieldKey);
        if (!$field) {
            return null;
        }

        return $this->resolveFieldInputConfig($field, $equipmentId);
    }

    /**
     * Templates shown/managed on the Template Engine tab.
     */
    protected function managedTemplatesQuery()
    {
        return MonitoringTemplate::query();
    }

    protected function findManagedTemplate(string $templateId): ?MonitoringTemplate
    {
        return $this->managedTemplatesQuery()->where('id', $templateId)->first();
    }

    public function render()
    {
        return view('livewire.monitoring.monitoring-dashboard');
    }
}
