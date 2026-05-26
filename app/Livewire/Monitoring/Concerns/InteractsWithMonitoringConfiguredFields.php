<?php

namespace App\Livewire\Monitoring\Concerns;

use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateConfiguredField;
use Illuminate\Validation\Rule;

trait InteractsWithMonitoringConfiguredFields
{
    /** @var list<array<string, mixed>> */
    public array $configuredFields = [];

    public bool $showCreateConfiguredFieldModal = false;

    public bool $showEditConfiguredFieldModal = false;

    public bool $showDeleteConfiguredFieldModal = false;

    public string $configuredFieldPlacement = 'top';

    public ?string $editingConfiguredFieldId = null;

    public ?string $deletingConfiguredFieldId = null;

    public string $configuredFieldLabel = '';

    public string $configuredFieldType = '';

    public int $configuredFieldOrder = 1;

    public string $configuredFieldHelpText = '';

    public string $configuredFieldModelTiedTo = '';

    public bool $configuredFieldIsRequired = true;

    public string $configuredFieldValueName = '';

    public string $configuredFieldSearch = '';

    /** @var list<string> */
    public array $configuredFieldCalibrationAttributes = [];

    public string $configuredFieldManagerSource = 'lab_section';

    public bool $showConfiguredFieldTypeDropdown = false;

    /**
     * @return array<string, string>
     */
    public function getConfiguredFieldPlacementOptionsProperty(): array
    {
        return MonitoringTemplateConfiguredField::placementOptions();
    }

    /**
     * @return array<string, string>
     */
    public function getConfiguredFieldTypeOptionsProperty(): array
    {
        return MonitoringTemplateConfiguredField::fieldTypeOptions();
    }

    public function getSelectedConfiguredFieldTypeLabelProperty(): ?string
    {
        $options = $this->configuredFieldTypeOptions;

        return $options[$this->configuredFieldType] ?? null;
    }

    public function openConfiguredFieldTypeDropdown(): void
    {
        $this->showConfiguredFieldTypeDropdown = ! $this->showConfiguredFieldTypeDropdown;
    }

    public function closeConfiguredFieldTypeDropdown(): void
    {
        $this->showConfiguredFieldTypeDropdown = false;
    }

    public function selectConfiguredFieldType(string $type): void
    {
        $options = $this->configuredFieldTypeOptions;

        if (! array_key_exists($type, $options)) {
            return;
        }

        $this->configuredFieldType = $type;
        $this->closeConfiguredFieldTypeDropdown();
        $this->updatedConfiguredFieldType();
    }

    /**
     * @return array<string, string>
     */
    public function getConfiguredFieldDatasetOptionsProperty(): array
    {
        return MonitoringTemplateConfiguredField::datasetModelOptions();
    }

    /**
     * @return array<string, string>
     */
    public function getConfiguredFieldCalibrationOptionsProperty(): array
    {
        return MonitoringTemplateConfiguredField::calibrationAttributeOptions();
    }

    /**
     * @return array<string, string>
     */
    public function getConfiguredFieldManagerSourceOptionsProperty(): array
    {
        return MonitoringTemplateConfiguredField::managerSourceOptions();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTopConfiguredFieldsProperty(): array
    {
        return $this->configuredFieldsForPlacement('top');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getBottomConfiguredFieldsProperty(): array
    {
        return $this->configuredFieldsForPlacement('bottom');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function configuredFieldsForPlacement(string $placement): array
    {
        $fields = array_values(array_filter(
            $this->configuredFields,
            fn (array $field) => ($field['placement'] ?? '') === $placement
        ));

        usort($fields, fn (array $a, array $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        if ($this->configuredFieldSearch === '') {
            return $fields;
        }

        $needle = strtolower($this->configuredFieldSearch);

        return array_values(array_filter($fields, function (array $field) use ($needle) {
            return str_contains(strtolower((string) ($field['label'] ?? '')), $needle)
                || str_contains(strtolower((string) ($field['field_value_name'] ?? '')), $needle)
                || str_contains(strtolower((string) ($field['help_text'] ?? '')), $needle);
        }));
    }

    public function showCreateConfiguredFieldModalInit(string $placement = 'top'): void
    {
        $this->configuredFieldPlacement = in_array($placement, ['top', 'bottom'], true) ? $placement : 'top';
        $this->resetConfiguredFieldForm();
        $this->configuredFieldOrder = $this->nextConfiguredFieldOrder($this->configuredFieldPlacement);
        $this->showCreateConfiguredFieldModal = true;
    }

    public function closeCreateConfiguredFieldModal(): void
    {
        $this->showCreateConfiguredFieldModal = false;
        $this->closeConfiguredFieldTypeDropdown();
    }

    public function showEditConfiguredFieldModalInit(string $fieldId): void
    {
        $field = $this->findConfiguredFieldInMemory($fieldId);

        if ($field === null) {
            return;
        }

        $this->editingConfiguredFieldId = $fieldId;
        $this->configuredFieldPlacement = (string) ($field['placement'] ?? 'top');
        $this->configuredFieldLabel = (string) ($field['label'] ?? '');
        $this->configuredFieldType = (string) ($field['field_type'] ?? 'input');
        $this->configuredFieldOrder = (int) ($field['order'] ?? 1);
        $this->configuredFieldHelpText = (string) ($field['help_text'] ?? '');
        $this->configuredFieldModelTiedTo = (string) ($field['model_tied_to'] ?? '');
        $this->configuredFieldIsRequired = (bool) ($field['is_required'] ?? true);
        $this->configuredFieldValueName = (string) ($field['field_value_name'] ?? '');

        $config = is_array($field['field_config'] ?? null) ? $field['field_config'] : [];
        $this->configuredFieldCalibrationAttributes = $config['calibration_attributes'] ?? [];
        $this->configuredFieldManagerSource = (string) ($config['manager_source'] ?? $this->defaultManagerSource());

        $this->showEditConfiguredFieldModal = true;
    }

    public function closeEditConfiguredFieldModal(): void
    {
        $this->showEditConfiguredFieldModal = false;
        $this->editingConfiguredFieldId = null;
        $this->closeConfiguredFieldTypeDropdown();
    }

    public function showDeleteConfiguredFieldModalInit(string $fieldId): void
    {
        $this->deletingConfiguredFieldId = $fieldId;
        $this->showDeleteConfiguredFieldModal = true;
    }

    public function closeDeleteConfiguredFieldModal(): void
    {
        $this->showDeleteConfiguredFieldModal = false;
        $this->deletingConfiguredFieldId = null;
    }

    public function createConfiguredField(): void
    {
        $this->validate($this->configuredFieldRules());

        $this->configuredFields[] = $this->buildConfiguredFieldPayload('cf_'.uniqid());
        $this->reindexConfiguredFields();
        $this->closeCreateConfiguredFieldModal();
    }

    public function updateConfiguredField(): void
    {
        if ($this->editingConfiguredFieldId === null) {
            return;
        }

        $this->validate($this->configuredFieldRules($this->editingConfiguredFieldId));

        foreach ($this->configuredFields as $index => $field) {
            if (($field['id'] ?? '') === $this->editingConfiguredFieldId) {
                $this->configuredFields[$index] = $this->buildConfiguredFieldPayload($this->editingConfiguredFieldId);
                break;
            }
        }

        $this->reindexConfiguredFields();
        $this->closeEditConfiguredFieldModal();
    }

    public function deleteConfiguredField(): void
    {
        if ($this->deletingConfiguredFieldId === null) {
            return;
        }

        $this->configuredFields = array_values(array_filter(
            $this->configuredFields,
            fn (array $field) => ($field['id'] ?? '') !== $this->deletingConfiguredFieldId
        ));

        $this->reindexConfiguredFields();
        $this->closeDeleteConfiguredFieldModal();
    }

    public function updatedConfiguredFieldType(): void
    {
        if ($this->configuredFieldType !== 'dataset_related') {
            $this->configuredFieldModelTiedTo = '';
        }

        if ($this->configuredFieldType !== 'equipment_calibration') {
            $this->configuredFieldCalibrationAttributes = [];
        }

        if (! in_array($this->configuredFieldType, ['manager_dropdown', 'user_signature'], true)) {
            $this->configuredFieldManagerSource = $this->defaultManagerSource();
        }
    }

    public function clearConfiguredFieldSearch(): void
    {
        $this->configuredFieldSearch = '';
    }

    public function configuredFieldTypeSummary(array $field): string
    {
        $type = (string) ($field['field_type'] ?? '');
        $config = is_array($field['field_config'] ?? null) ? $field['field_config'] : [];

        return match ($type) {
            'dataset_related' => 'Dataset: '.ucfirst((string) ($field['model_tied_to'] ?? $config['model'] ?? '—')),
            'equipment_calibration' => 'Calibration: '.implode(', ', $config['calibration_attributes'] ?? []) ?: '—',
            'manager_dropdown', 'user_signature' => 'Manager from: '.str_replace('_', ' ', (string) ($config['manager_source'] ?? '—')),
            'monitoring_equipment' => 'Selected monitoring equipment',
            'lab_section_select' => 'Active lab section at capture',
            default => MonitoringTemplateConfiguredField::fieldTypeOptions()[$type] ?? ucfirst(str_replace('_', ' ', $type)),
        };
    }

    protected function loadConfiguredFieldsFromTemplate(MonitoringTemplate $template): void
    {
        $this->configuredFields = MonitoringTemplateConfiguredField::query()
            ->where('template_id', $template->id)
            ->orderBy('placement')
            ->orderBy('order')
            ->get()
            ->map(fn (MonitoringTemplateConfiguredField $field) => [
                'id' => (string) $field->id,
                'placement' => $field->placement,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'order' => $field->order,
                'help_text' => $field->help_text,
                'model_tied_to' => $field->model_tied_to,
                'field_config' => $field->field_config ?? [],
                'is_required' => $field->is_required,
                'field_value_name' => $field->field_value_name,
            ])
            ->values()
            ->all();
    }

    protected function persistConfiguredFields(MonitoringTemplate $template): void
    {
        MonitoringTemplateConfiguredField::where('template_id', $template->id)->delete();

        foreach ($this->configuredFields as $field) {
            MonitoringTemplateConfiguredField::create([
                'template_id' => $template->id,
                'placement' => $field['placement'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'order' => (int) ($field['order'] ?? 0),
                'help_text' => $field['help_text'] ?? null,
                'model_tied_to' => $field['model_tied_to'] ?? null,
                'field_config' => $field['field_config'] ?? null,
                'is_required' => (bool) ($field['is_required'] ?? false),
                'field_value_name' => $field['field_value_name'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildConfiguredFieldPayload(string $id): array
    {
        return [
            'id' => $id,
            'placement' => $this->configuredFieldPlacement,
            'label' => $this->configuredFieldLabel,
            'field_type' => $this->configuredFieldType,
            'order' => $this->configuredFieldOrder,
            'help_text' => $this->configuredFieldHelpText ?: null,
            'model_tied_to' => $this->configuredFieldType === 'dataset_related' ? $this->configuredFieldModelTiedTo : null,
            'field_config' => $this->buildConfiguredFieldConfigFromForm(),
            'is_required' => $this->configuredFieldIsRequired,
            'field_value_name' => $this->configuredFieldValueName,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildConfiguredFieldConfigFromForm(): ?array
    {
        return match ($this->configuredFieldType) {
            'dataset_related' => [
                'model' => $this->configuredFieldModelTiedTo,
            ],
            'equipment_calibration' => [
                'calibration_attributes' => array_values($this->configuredFieldCalibrationAttributes),
                'use_reading_date' => true,
            ],
            'manager_dropdown', 'user_signature' => [
                'manager_source' => $this->configuredFieldManagerSource ?: $this->defaultManagerSource(),
                'auto_resolve' => true,
            ],
            'monitoring_equipment' => [
                'source' => 'template_scope',
            ],
            'lab_section_select' => [
                'source' => 'template_scope',
            ],
            default => null,
        };
    }

    protected function defaultManagerSource(): string
    {
        return property_exists($this, 'templateType') && $this->templateType === 'equipment'
            ? 'equipment_location'
            : 'lab_section';
    }

    /**
     * @return array<string, mixed>
     */
    protected function configuredFieldRules(?string $excludeFieldId = null): array
    {
        $types = array_keys(MonitoringTemplateConfiguredField::fieldTypeOptions());

        return [
            'configuredFieldPlacement' => 'required|in:top,bottom',
            'configuredFieldLabel' => 'required|string|max:255',
            'configuredFieldType' => ['required', Rule::in($types)],
            'configuredFieldValueName' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z][a-z0-9_]*$/',
                function ($attribute, $value, $fail) use ($excludeFieldId) {
                    foreach ($this->configuredFields as $field) {
                        if ($excludeFieldId !== null && ($field['id'] ?? '') === $excludeFieldId) {
                            continue;
                        }

                        if (($field['placement'] ?? '') === $this->configuredFieldPlacement
                            && ($field['field_value_name'] ?? '') === $value) {
                            $fail('Value name already exists for this placement section.');
                        }
                    }
                },
            ],
            'configuredFieldHelpText' => 'nullable|string|max:2000',
            'configuredFieldModelTiedTo' => 'nullable|required_if:configuredFieldType,dataset_related|in:equipments,users,methods',
            'configuredFieldCalibrationAttributes' => 'nullable|required_if:configuredFieldType,equipment_calibration|array|min:1',
            'configuredFieldCalibrationAttributes.*' => Rule::in(array_keys(MonitoringTemplateConfiguredField::calibrationAttributeOptions())),
            'configuredFieldManagerSource' => 'nullable|required_if:configuredFieldType,manager_dropdown|required_if:configuredFieldType,user_signature|in:lab_section,equipment_location',
            'configuredFieldOrder' => 'required|integer|min:1',
            'configuredFieldIsRequired' => 'boolean',
        ];
    }

    protected function nextConfiguredFieldOrder(string $placement): int
    {
        $max = 0;

        foreach ($this->configuredFields as $field) {
            if (($field['placement'] ?? '') !== $placement) {
                continue;
            }

            $max = max($max, (int) ($field['order'] ?? 0));
        }

        return $max + 1;
    }

    protected function reindexConfiguredFields(): void
    {
        foreach (['top', 'bottom'] as $placement) {
            $order = 1;

            foreach ($this->configuredFields as $index => $field) {
                if (($field['placement'] ?? '') !== $placement) {
                    continue;
                }

                $this->configuredFields[$index]['order'] = $order++;
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findConfiguredFieldInMemory(string $fieldId): ?array
    {
        foreach ($this->configuredFields as $field) {
            if (($field['id'] ?? '') === $fieldId) {
                return $field;
            }
        }

        return null;
    }

    protected function resetConfiguredFieldForm(): void
    {
        $this->configuredFieldLabel = '';
        $this->configuredFieldType = '';
        $this->configuredFieldOrder = 1;
        $this->configuredFieldHelpText = '';
        $this->configuredFieldModelTiedTo = '';
        $this->configuredFieldIsRequired = true;
        $this->configuredFieldValueName = '';
        $this->configuredFieldCalibrationAttributes = [];
        $this->configuredFieldManagerSource = $this->defaultManagerSource();
        $this->editingConfiguredFieldId = null;
        $this->closeConfiguredFieldTypeDropdown();
        $this->resetErrorBag([
            'configuredFieldLabel',
            'configuredFieldType',
            'configuredFieldValueName',
            'configuredFieldHelpText',
            'configuredFieldModelTiedTo',
            'configuredFieldOrder',
            'configuredFieldCalibrationAttributes',
            'configuredFieldManagerSource',
        ]);
    }
}
