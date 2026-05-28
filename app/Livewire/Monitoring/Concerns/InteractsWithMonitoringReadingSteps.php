<?php

namespace App\Livewire\Monitoring\Concerns;

use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\Formulars\LookupTable;
use App\Models\Monitoring\MonitoringFormulaRule;
use App\Models\Monitoring\MonitoringReadingStep;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateField;
use App\Services\Monitoring\FormulaEngineService;
use Illuminate\Support\Facades\Auth;

trait InteractsWithMonitoringReadingSteps
{
    /** @var list<string> */
    public const ENVIRONMENTAL_LIMIT_VARIABLE_SLUGS = [
        'expected_value_type',
        'expected_constant',
        'optimum_level',
        'expected_min',
        'expected_max',
    ];

    /** @var list<array<string, mixed>> */
    public array $readingSteps = [];

    public bool $showCreateReadingStepModal = false;

    public bool $showEditReadingStepModal = false;

    public bool $showDeleteReadingStepModal = false;

    public ?string $editingReadingStepId = null;

    public ?string $deletingReadingStepId = null;

    public int $readingStepNumber = 1;

    public string $readingVariableName = '';

    public string $readingStepType = 'input';

    public string $readingExpression = '';

    public string $readingLabel = '';

    public string $readingDescription = '';

    public string $readingLookupTableId = '';

    /** @var array<string, mixed> */
    public array $readingLookupConfig = [];

    public ?string $readingAnalyteId = null;

    public string $readingVariableSlug = '';

    public bool $readingStepShowInMonitoringLogs = true;

    public string $readingStepMessage = '';

    public string $readingStepMessageType = '';

    public ?string $readingDerivedScopeSourceId = null;

    public string $readingDerivedValueType = '';

    public ?string $readingDerivedExpectedValue = null;

    public ?string $readingDerivedMinimumValue = null;

    public ?string $readingDerivedMaximumValue = null;

    /**
     * @return array<string, string>
     */
    public function getReadingStepTypeOptionsProperty(): array
    {
        return MonitoringReadingStep::stepTypeOptions();
    }

    public function showCreateReadingStepModalInit(): void
    {
        $this->resetReadingStepForm();
        $this->readingStepNumber = count($this->readingSteps) + 1;
        $this->readingStepType = 'input';
        $this->readingStepMessage = '';
        $this->showCreateReadingStepModal = true;
    }

    public function closeCreateReadingStepModal(): void
    {
        $this->showCreateReadingStepModal = false;
        $this->readingStepMessage = '';
    }

    public function showEditReadingStepModalInit(string $stepId): void
    {
        $step = $this->findReadingStepInMemory($stepId);

        if ($step === null) {
            return;
        }

        $this->editingReadingStepId = $stepId;
        $this->readingStepNumber = (int) ($step['step_number'] ?? 1);
        $this->readingVariableName = (string) ($step['variable_name'] ?? '');
        $this->readingStepType = (string) ($step['step_type'] ?? 'input');
        $this->readingExpression = (string) ($step['expression'] ?? '');
        $this->readingLabel = (string) ($step['label'] ?? '');
        $this->readingDescription = (string) ($step['description'] ?? '');
        $this->readingLookupConfig = is_array($step['lookup_config'] ?? null) ? $step['lookup_config'] : [];
        $this->readingLookupTableId = (string) ($this->readingLookupConfig['lookup_table_id'] ?? '');
        $this->readingAnalyteId = $step['analyte_id'] ?? null;
        $this->readingVariableSlug = (string) ($step['variable_slug'] ?? '');
        $this->readingStepShowInMonitoringLogs = (bool) ($step['show_in_monitoring_logs'] ?? true);
        $this->readingStepMessage = '';
        $this->applyDerivedConfigToForm(is_array($step['derived_config'] ?? null) ? $step['derived_config'] : null);
        $this->showEditReadingStepModal = true;
    }

    public function closeEditReadingStepModal(): void
    {
        $this->showEditReadingStepModal = false;
        $this->editingReadingStepId = null;
        $this->readingStepMessage = '';
    }

    public function showDeleteReadingStepModalInit(string $stepId): void
    {
        $this->deletingReadingStepId = $stepId;
        $this->showDeleteReadingStepModal = true;
    }

    public function closeDeleteReadingStepModal(): void
    {
        $this->showDeleteReadingStepModal = false;
        $this->deletingReadingStepId = null;
    }

    public function createReadingStep(): void
    {
        if (! $this->validateReadingStepForm() || ! $this->validateDerivedLimits()) {
            return;
        }

        $this->readingSteps[] = $this->buildReadingStepPayload();
        $this->reindexReadingSteps();
        $this->showCreateReadingStepModal = false;
        $this->resetReadingStepForm();
        $this->setReadingStepMessage('Step created successfully.', 'success');
    }

    public function updateReadingStep(): void
    {
        if ($this->editingReadingStepId === null) {
            return;
        }

        if (! $this->validateReadingStepForm($this->editingReadingStepId) || ! $this->validateDerivedLimits()) {
            return;
        }

        foreach ($this->readingSteps as $index => $step) {
            if (($step['id'] ?? '') === $this->editingReadingStepId) {
                $payload = $this->buildReadingStepPayload();
                $payload['id'] = $this->editingReadingStepId;
                $this->readingSteps[$index] = $payload;
                break;
            }
        }

        $this->reindexReadingSteps();
        $this->closeEditReadingStepModal();
        $this->setReadingStepMessage('Step updated successfully.', 'success');
    }

    public function deleteReadingStep(): void
    {
        if ($this->deletingReadingStepId === null) {
            return;
        }

        $this->readingSteps = array_values(array_filter(
            $this->readingSteps,
            fn (array $step) => ($step['id'] ?? '') !== $this->deletingReadingStepId
        ));

        $this->reindexReadingSteps();
        $this->closeDeleteReadingStepModal();
        $this->setReadingStepMessage('Step removed.', 'success');
    }

    public function updatedReadingStepType(): void
    {
        $this->readingExpression = '';
        $this->readingLookupTableId = '';
        $this->readingLookupConfig = [];
        $this->readingAnalyteId = null;
        $this->readingVariableSlug = '';
        $this->resetDerivedLimitForm();

        if ($this->readingStepType === 'derived') {
            $this->primeDerivedLimitsFromScope();
        }
    }

    public function updatedReadingDerivedScopeSourceId(): void
    {
        if ($this->readingDerivedScopeSourceId && $this->monitoringTemplateType() !== 'environmental') {
            $this->applyDerivedLimitsFromScope($this->readingDerivedScopeSourceId);
        }
    }

    /**
     * @return list<array{slug: string, name: string, variable_type: string}>
     */
    protected function environmentalLimitPredefinedVariables(): array
    {
        return [
            [
                'slug' => 'expected_value_type',
                'name' => 'Expected Value Type (Lab Section)',
                'variable_type' => 'environmental',
            ],
            [
                'slug' => 'expected_constant',
                'name' => 'Expected Constant (Lab Section)',
                'variable_type' => 'environmental',
            ],
            [
                'slug' => 'optimum_level',
                'name' => 'Optimum Level (Lab Section)',
                'variable_type' => 'environmental',
            ],
            [
                'slug' => 'expected_min',
                'name' => 'Low Limit (Lab Section)',
                'variable_type' => 'environmental',
            ],
            [
                'slug' => 'expected_max',
                'name' => 'High Limit (Lab Section)',
                'variable_type' => 'environmental',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function reservedMonitoringVariableSlugs(): array
    {
        return array_merge(
            ['correction_factor', 'uncertainty_of_measure'],
            self::ENVIRONMENTAL_LIMIT_VARIABLE_SLUGS,
        );
    }

    protected function monitoringTemplateType(): string
    {
        return property_exists($this, 'templateType') ? (string) $this->templateType : 'environmental';
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function getDerivedScopeOptionsProperty(): array
    {
        if ($this->monitoringTemplateType() === 'environmental') {
            if (empty($this->selectedSectionIds)) {
                return [];
            }

            return LabSection::query()
                ->whereIn('id', $this->selectedSectionIds)
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn (LabSection $section) => [
                    'id' => (string) $section->id,
                    'label' => $section->name.' ('.$section->code.')',
                ])
                ->values()
                ->all();
        }

        if (empty($this->selectedEquipmentIds)) {
            return [];
        }

        return Equipment::query()
            ->whereIn('id', $this->selectedEquipmentIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Equipment $equipment) => [
                'id' => (string) $equipment->id,
                'label' => $equipment->name,
            ])
            ->values()
            ->all();
    }

    protected function primeDerivedLimitsFromScope(): void
    {
        $options = $this->derivedScopeOptions;

        if ($options === []) {
            $this->readingDerivedScopeSourceId = null;

            return;
        }

        if (! $this->readingDerivedScopeSourceId) {
            $this->readingDerivedScopeSourceId = $options[0]['id'];
        }

        if ($this->monitoringTemplateType() !== 'environmental') {
            $this->applyDerivedLimitsFromScope($this->readingDerivedScopeSourceId);
        }
    }

    protected function resolveEnvironmentalLimitSection(): ?LabSection
    {
        $sourceId = $this->readingDerivedScopeSourceId;

        if (! $sourceId && $this->derivedScopeOptions !== []) {
            $sourceId = $this->derivedScopeOptions[0]['id'];
        }

        if (! $sourceId) {
            return null;
        }

        return LabSection::find($sourceId);
    }

    protected function applyDerivedLimitsFromScope(?string $sourceId): void
    {
        if (! $sourceId) {
            return;
        }

        $equipment = Equipment::find($sourceId);

        if (! $equipment) {
            return;
        }

        $this->readingDerivedValueType = (string) ($equipment->daily_log_value_type ?? '');

        if ($equipment->daily_log_value_type === 'constant') {
            $this->readingDerivedMinimumValue = $equipment->daily_log_expected_value !== null
                ? (string) $equipment->daily_log_expected_value
                : null;
            $this->readingDerivedMaximumValue = $equipment->daily_log_expected_value !== null
                ? (string) $equipment->daily_log_expected_value
                : null;
            $this->readingDerivedExpectedValue = $equipment->daily_log_expected_value !== null
                ? (string) $equipment->daily_log_expected_value
                : null;
        } else {
            $this->readingDerivedMinimumValue = $equipment->daily_log_expected_min !== null
                ? (string) $equipment->daily_log_expected_min
                : null;
            $this->readingDerivedMaximumValue = $equipment->daily_log_expected_max !== null
                ? (string) $equipment->daily_log_expected_max
                : null;
            $this->readingDerivedExpectedValue = null;
        }
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    protected function applyDerivedConfigToForm(?array $config): void
    {
        $this->resetDerivedLimitForm();

        if ($config === null || $config === []) {
            $this->primeDerivedLimitsFromScope();

            return;
        }

        $this->readingDerivedScopeSourceId = isset($config['scope_source_id'])
            ? (string) $config['scope_source_id']
            : null;

        if (($config['scope_type'] ?? $this->monitoringTemplateType()) !== 'environmental') {
            $this->readingDerivedValueType = (string) ($config['value_type'] ?? '');
            $this->readingDerivedExpectedValue = isset($config['expected_value'])
                ? (string) $config['expected_value']
                : null;
            $this->readingDerivedMinimumValue = isset($config['minimum_value'])
                ? (string) $config['minimum_value']
                : null;
            $this->readingDerivedMaximumValue = isset($config['maximum_value'])
                ? (string) $config['maximum_value']
                : null;
        }
    }

    protected function resetDerivedLimitForm(): void
    {
        $this->readingDerivedScopeSourceId = null;
        $this->readingDerivedValueType = '';
        $this->readingDerivedExpectedValue = null;
        $this->readingDerivedMinimumValue = null;
        $this->readingDerivedMaximumValue = null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildDerivedConfigFromForm(): ?array
    {
        if ($this->readingStepType !== 'derived') {
            return null;
        }

        if ($this->monitoringTemplateType() === 'environmental') {
            return [
                'scope_type' => 'environmental',
                'scope_source_id' => $this->readingDerivedScopeSourceId,
            ];
        }

        return [
            'scope_type' => 'equipment',
            'scope_source_id' => $this->readingDerivedScopeSourceId,
            'value_type' => $this->readingDerivedValueType,
            'expected_value' => $this->readingDerivedExpectedValue,
            'minimum_value' => $this->readingDerivedMinimumValue,
            'maximum_value' => $this->readingDerivedMaximumValue,
        ];
    }

    public function equipmentValueTypeLabel(?string $type): string
    {
        return match ($type) {
            'constant' => 'Constant',
            'range' => 'Range',
            default => '—',
        };
    }

    protected function validateDerivedLimits(): bool
    {
        if ($this->readingStepType !== 'derived') {
            return true;
        }

        if ($this->derivedScopeOptions === []) {
            $this->addError('readingDerivedScopeSourceId', 'Select at least one lab section or equipment in step 3 before configuring derived limits.');

            return false;
        }

        if ($this->monitoringTemplateType() === 'environmental') {
            if ($this->readingDerivedScopeSourceId === null || $this->readingDerivedScopeSourceId === '') {
                $this->addError('readingDerivedScopeSourceId', 'Select which lab section supplies environmental limit variables.');

                return false;
            }

            return true;
        }

        if ($this->readingDerivedValueType === '') {
            $this->addError('readingDerivedValueType', 'Value type is required.');

            return false;
        }

        if ($this->readingDerivedMinimumValue === null || $this->readingDerivedMinimumValue === '') {
            $this->addError('readingDerivedMinimumValue', 'Minimum value is required.');

            return false;
        }

        if ($this->readingDerivedMaximumValue === null || $this->readingDerivedMaximumValue === '') {
            $this->addError('readingDerivedMaximumValue', 'Maximum value is required.');

            return false;
        }

        return true;
    }

    public function updatedReadingLookupTableId(): void
    {
        if (! $this->readingLookupTableId) {
            $this->readingLookupConfig = [];

            return;
        }

        $lookupTable = LookupTable::find($this->readingLookupTableId);

        if (! $lookupTable) {
            return;
        }

        if ($lookupTable->isRangeBased()) {
            $this->readingLookupConfig = [
                'lookup_table_id' => $lookupTable->id,
                'range_variable' => '',
                'return_interpretation' => false,
            ];
        } else {
            $this->readingLookupConfig = [
                'lookup_table_id' => $lookupTable->id,
                'key_expressions' => array_fill_keys($lookupTable->key_columns ?? [], ''),
                'key_values' => array_fill_keys($lookupTable->key_columns ?? [], ''),
            ];
        }
    }

    public function updatedReadingExpression(): void
    {
        if ($this->readingStepMessage !== '' && ($this->showCreateReadingStepModal || $this->showEditReadingStepModal)) {
            $this->readingStepMessage = '';
        }
    }

    public function validateReadingStepExpression(FormulaEngineService $formulaEngine): void
    {
        if ($this->readingStepType !== 'derived' || $this->readingExpression === '') {
            return;
        }

        $names = array_column($this->getReadingStepAvailableVariables(), 'name');
        $result = $formulaEngine->validateExpression($this->readingExpression, $names);

        if ($result['valid']) {
            $this->setReadingStepMessage('Expression is valid.', 'success');
        } else {
            $this->setReadingStepMessage('Expression error: '.$result['message'], 'error');
        }
    }

    /**
     * @return list<array{title: string, variables: list<array{name: string, label: string, type: string}>}>
     */
    public function getReadingStepVariableGroupsProperty(): array
    {
        $groups = [];

        $prior = [];
        foreach ($this->readingSteps as $step) {
            if ((int) ($step['step_number'] ?? 0) < $this->readingStepNumber) {
                $prior[] = [
                    'name' => (string) $step['variable_name'],
                    'label' => (string) $step['label'],
                    'type' => (string) $step['step_type'],
                ];
            }
        }

        if ($prior !== []) {
            $groups[] = ['title' => 'Prior steps', 'variables' => $prior];
        }

        if ($this->monitoringTemplateType() === 'environmental'
            && $this->readingStepType === 'derived'
            && $this->derivedScopeOptions !== []) {
            $environmental = [];
            foreach ($this->environmentalLimitPredefinedVariables() as $var) {
                $environmental[] = [
                    'name' => $var['slug'],
                    'label' => $var['name'],
                    'type' => 'environmental',
                ];
            }

            if ($environmental !== []) {
                $groups[] = ['title' => 'Environmental limits', 'variables' => $environmental];
            }
        }

        $monitoring = [];
        foreach ($this->availableVariables as $var) {
            if ($this->monitoringTemplateType() === 'environmental'
                && in_array($var['slug'], self::ENVIRONMENTAL_LIMIT_VARIABLE_SLUGS, true)) {
                continue;
            }

            $monitoring[] = [
                'name' => (string) $var['slug'],
                'label' => (string) $var['name'],
                'type' => (string) ($var['variable_type'] ?? 'constant'),
            ];
        }

        if ($monitoring !== []) {
            $groups[] = ['title' => 'Monitoring variables', 'variables' => $monitoring];
        }

        return $groups;
    }

    /**
     * @return list<array{name: string, label: string, type: string}>
     */
    public function getReadingStepAvailableVariables(): array
    {
        $variables = [];

        foreach ($this->readingStepVariableGroups as $group) {
            foreach ($group['variables'] as $var) {
                $variables[] = $var;
            }
        }

        return $variables;
    }

    protected function validateReadingStepForm(?string $excludeStepId = null): bool
    {
        $rules = [
            'readingStepNumber' => 'required|integer|min:1',
            'readingVariableName' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'readingStepType' => 'required|in:input,derived,lookup,parameter_result',
            'readingLabel' => 'required|string|max:255',
            'readingDescription' => 'nullable|string|max:2000',
            'readingStepShowInMonitoringLogs' => 'boolean',
        ];

        if ($this->readingStepType === 'derived') {
            $rules['readingExpression'] = 'required|string';
        }

        if ($this->readingStepType === 'lookup') {
            $rules['readingLookupTableId'] = 'required|exists:lookup_tables,id';
        }

        if ($this->readingStepType === 'parameter_result') {
            $rules['readingAnalyteId'] = 'required|exists:analytes,id';
        }

        $this->validate($rules, [], [
            'readingVariableName' => 'variable name',
            'readingStepType' => 'step type',
            'readingLabel' => 'display label',
            'readingExpression' => 'expression',
            'readingLookupTableId' => 'lookup table',
            'readingAnalyteId' => 'analyte',
        ]);

        foreach ($this->readingSteps as $step) {
            if ($excludeStepId !== null && ($step['id'] ?? '') === $excludeStepId) {
                continue;
            }

            if (($step['variable_name'] ?? '') === $this->readingVariableName) {
                $this->addError('readingVariableName', 'Variable name already exists in this reading structure.');

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildReadingStepPayload(): array
    {
        $lookupConfig = [];

        if ($this->readingStepType === 'lookup' && $this->readingLookupTableId) {
            $lookupTable = LookupTable::find($this->readingLookupTableId);

            if ($lookupTable && $lookupTable->isRangeBased()) {
                $lookupConfig = [
                    'lookup_table_id' => $this->readingLookupTableId,
                    'range_variable' => $this->readingLookupConfig['range_variable'] ?? '',
                    'return_interpretation' => (bool) ($this->readingLookupConfig['return_interpretation'] ?? false),
                ];
            } else {
                $lookupConfig = [
                    'lookup_table_id' => $this->readingLookupTableId,
                    'key_expressions' => $this->readingLookupConfig['key_expressions'] ?? [],
                    'key_values' => $this->readingLookupConfig['key_values'] ?? [],
                ];
            }
        }

        return [
            'id' => 'rs_'.uniqid(),
            'step_number' => (int) $this->readingStepNumber,
            'variable_name' => $this->readingVariableName,
            'step_type' => $this->readingStepType,
            'expression' => $this->readingStepType === 'derived' ? $this->readingExpression : null,
            'derived_config' => $this->buildDerivedConfigFromForm(),
            'label' => $this->readingLabel,
            'description' => $this->readingDescription ?: null,
            'lookup_config' => $lookupConfig !== [] ? $lookupConfig : null,
            'analyte_id' => $this->readingStepType === 'parameter_result' ? $this->readingAnalyteId : null,
            'show_in_monitoring_logs' => (bool) $this->readingStepShowInMonitoringLogs,
            'variable_slug' => $this->readingStepType === 'input' && $this->readingVariableSlug !== ''
                ? $this->readingVariableSlug
                : null,
        ];
    }

    protected function resetReadingStepForm(): void
    {
        $this->readingStepNumber = count($this->readingSteps) + 1;
        $this->readingVariableName = '';
        $this->readingStepType = 'input';
        $this->readingExpression = '';
        $this->readingLabel = '';
        $this->readingDescription = '';
        $this->readingLookupTableId = '';
        $this->readingLookupConfig = [];
        $this->readingAnalyteId = null;
        $this->readingVariableSlug = '';
        $this->readingStepShowInMonitoringLogs = true;
        $this->editingReadingStepId = null;
        $this->resetDerivedLimitForm();
        $this->resetErrorBag();
    }

    protected function reindexReadingSteps(): void
    {
        usort($this->readingSteps, fn (array $a, array $b) => ($a['step_number'] ?? 0) <=> ($b['step_number'] ?? 0));

        foreach ($this->readingSteps as $index => &$step) {
            $step['step_number'] = $index + 1;
        }

        unset($step);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findReadingStepInMemory(string $stepId): ?array
    {
        foreach ($this->readingSteps as $step) {
            if (($step['id'] ?? '') === $stepId) {
                return $step;
            }
        }

        return null;
    }

    protected function setReadingStepMessage(string $message, string $type): void
    {
        $this->readingStepMessage = $message;
        $this->readingStepMessageType = $type;
    }

    public function readingStepMessageAlertClass(): string
    {
        return match ($this->readingStepMessageType) {
            'success' => 'success',
            'warning' => 'warning',
            default => 'danger',
        };
    }

    protected function loadReadingStepsFromTemplate(MonitoringTemplate $template): void
    {
        $steps = MonitoringReadingStep::query()
            ->where('template_id', $template->id)
            ->orderBy('step_number')
            ->get();

        if ($steps->isNotEmpty()) {
            $this->readingSteps = $steps->map(fn (MonitoringReadingStep $step) => [
                'id' => $step->id,
                'step_number' => $step->step_number,
                'variable_name' => $step->variable_name,
                'step_type' => $step->step_type,
                'expression' => $step->expression,
                'label' => $step->label,
                'description' => $step->description,
                'lookup_config' => $step->lookup_config,
                'derived_config' => $step->derived_config,
                'analyte_id' => $step->analyte_id,
                'variable_slug' => $step->variable_slug,
                'show_in_monitoring_logs' => (bool) $step->show_in_monitoring_logs,
            ])->values()->all();

            return;
        }

        $this->loadReadingStepsFromLegacyTemplateFields($template);
    }

    protected function loadReadingStepsFromLegacyTemplateFields(MonitoringTemplate $template): void
    {
        $fields = MonitoringTemplateField::with('formulaRule')
            ->where('template_id', $template->id)
            ->where('field_key', '!=', '__meta_scope_items')
            ->orderBy('sort_order')
            ->get();

        $this->readingSteps = [];
        $stepNumber = 1;

        foreach ($fields as $field) {
            $config = is_array($field->field_config) ? $field->field_config : [];

            if ($field->field_type === 'formula' && $field->formulaRule) {
                $this->readingSteps[] = [
                    'id' => 'rs_'.uniqid(),
                    'step_number' => $stepNumber++,
                    'variable_name' => $field->field_key,
                    'step_type' => 'derived',
                    'expression' => $field->formulaRule->expression,
                    'label' => $field->label,
                    'description' => null,
                    'lookup_config' => null,
                    'analyte_id' => null,
                    'variable_slug' => null,
                    'show_in_monitoring_logs' => true,
                ];

                continue;
            }

            $this->readingSteps[] = [
                'id' => 'rs_'.uniqid(),
                'step_number' => $stepNumber++,
                'variable_name' => $field->field_key,
                'step_type' => 'input',
                'expression' => null,
                'label' => $field->label,
                'description' => null,
                'lookup_config' => null,
                'analyte_id' => null,
                'variable_slug' => $config['variable_slug'] ?? null,
                'show_in_monitoring_logs' => true,
            ];
        }
    }

    protected function persistReadingStructure(MonitoringTemplate $template): void
    {
        MonitoringReadingStep::where('template_id', $template->id)->delete();

        MonitoringFormulaRule::where('template_id', $template->id)->delete();

        MonitoringTemplateField::where('template_id', $template->id)
            ->where('field_key', '!=', '__meta_scope_items')
            ->delete();

        $sortOrder = 1;
        $usedKeys = [];

        foreach ($this->readingSteps as $step) {
            MonitoringReadingStep::create([
                'template_id' => $template->id,
                'step_number' => (int) $step['step_number'],
                'variable_name' => $step['variable_name'],
                'step_type' => $step['step_type'],
                'expression' => $step['expression'],
                'label' => $step['label'],
                'description' => $step['description'],
                'lookup_config' => $step['lookup_config'],
                'derived_config' => $step['derived_config'] ?? null,
                'analyte_id' => $step['analyte_id'],
                'variable_slug' => $step['variable_slug'],
                'show_in_monitoring_logs' => (bool) ($step['show_in_monitoring_logs'] ?? true),
            ]);

            $fieldKey = (string) $step['variable_name'];
            $baseKey = $fieldKey;
            $counter = 1;

            while (in_array($fieldKey, $usedKeys, true)) {
                $fieldKey = $baseKey.'_'.$counter++;
            }

            $usedKeys[] = $fieldKey;

            $stepType = (string) $step['step_type'];
            $formulaId = null;
            $fieldType = 'number';

            if ($stepType === 'derived' && ! empty($step['expression'])) {
                $formula = MonitoringFormulaRule::create([
                    'template_id' => $template->id,
                    'name' => $step['label'],
                    'output_key' => $fieldKey,
                    'expression' => $step['expression'],
                    'pass_condition_expression' => null,
                    'is_active' => true,
                    'company_id' => Auth::user()?->company_id,
                ]);
                $formulaId = $formula->id;
                $fieldType = 'formula';
            } elseif ($stepType === 'lookup') {
                $fieldType = 'lookup';
            } elseif ($stepType === 'parameter_result') {
                $fieldType = 'parameter_result';
            }

            MonitoringTemplateField::create([
                'template_id' => $template->id,
                'formula_rule_id' => $formulaId,
                'field_key' => $fieldKey,
                'label' => $step['label'],
                'field_type' => $fieldType,
                'is_required' => $stepType === 'input',
                'is_readonly' => in_array($stepType, ['derived', 'lookup', 'parameter_result'], true),
                'sort_order' => $sortOrder++,
                'field_config' => [
                    'reading_step_id' => $step['id'] ?? null,
                    'step_type' => $stepType,
                    'variable_slug' => $step['variable_slug'] ?? '',
                    'lookup_config' => $step['lookup_config'] ?? null,
                    'derived_config' => $step['derived_config'] ?? null,
                    'analyte_id' => $step['analyte_id'] ?? null,
                    'show_in_monitoring_logs' => (bool) ($step['show_in_monitoring_logs'] ?? true),
                ],
            ]);
        }
    }

    public function appendToReadingExpression(string $variableName): void
    {
        $variableName = trim($variableName);

        if ($variableName === '') {
            return;
        }

        $this->readingExpression = trim($this->readingExpression.' '.$variableName);
    }

    protected function readingStructureValidationRules(): array
    {
        return [
            'readingSteps' => 'required|array|min:1',
        ];
    }
}
