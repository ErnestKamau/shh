<?php

namespace App\Livewire\Sampleworkflow\Concerns;

use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;

trait ManagesSampleConfigurationWizard
{
    /** @var list<array<string, mixed>> */
    public array $sampleConfigs = [];

    public ?string $crmCustomerId = null;

    public function addSampleConfig(): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $empty = $configService->emptyConfig();
        $empty['zone_id'] = $configService->resolveZoneIdFromInstance(
            isset($this->submissionFormInstanceId) && $this->submissionFormInstanceId
                ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
                : null
        );
        $this->sampleConfigs[] = $empty;
    }

    public function removeSampleConfig(string $configId): void
    {
        if (count($this->sampleConfigs) <= 1) {
            if (method_exists($this, 'setStatus')) {
                $this->setStatus('error', 'At least one sample configuration is required.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'At least one sample configuration is required.');
            }

            return;
        }

        $this->sampleConfigs = array_values(array_filter(
            $this->sampleConfigs,
            fn (array $config) => (string) ($config['id'] ?? '') !== $configId
        ));
    }

    public function onConfigSampleTypeChanged(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $this->sampleConfigs[$index]['analysis_type_id'] = null;
        $this->sampleConfigs[$index]['parameter_keys'] = [];
        $this->sampleConfigs[$index]['lab_section_id'] = null;
        $this->sampleConfigs[$index]['parameter_lab_sections'] = [];
        $this->sampleConfigs[$index]['analysts_by_lab_section'] = [];
    }

    public function onConfigAnalysisTypeChanged(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $config = $this->sampleConfigs[$index];
        $configService = app(AcceptanceFormSampleConfigService::class);

        $this->sampleConfigs[$index]['lab_section_id'] = ! empty($config['analysis_type_id'])
            ? $configService->resolveLabSectionIdForAnalysisType((string) $config['analysis_type_id'])
            : null;

        // Start empty so the user picks parameters from the dropdown (or Select all).
        $this->sampleConfigs[$index]['parameter_keys'] = [];
        $this->sampleConfigs[$index]['parameter_lab_sections'] = [];
        $this->sampleConfigs[$index]['analysts_by_lab_section'] = [];
    }

    public function toggleConfigParameter(string $configId, string $parameterKey): void
    {
        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $keys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            if (in_array($parameterKey, $keys, true)) {
                $keys = array_values(array_filter($keys, fn ($k) => (string) $k !== $parameterKey));
            } else {
                $keys[] = $parameterKey;
            }
            $this->sampleConfigs[$index]['parameter_keys'] = $keys;
            $this->sampleConfigs[$index]['suppress_requested_parameter_autofill'] = $keys === [];

            if (property_exists($this, 'crmCustomerId')
                && is_string($this->crmCustomerId)
                && trim($this->crmCustomerId) !== '') {
                $this->sampleConfigs[$index] = app(AcceptanceFormSampleConfigService::class)
                    ->reconcileParameterKeysForConfig($this->sampleConfigs[$index], $this->crmCustomerId);
            }

            $this->sampleConfigs[$index] = app(AcceptanceFormSampleConfigService::class)
                ->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
    }

    public function selectAllConfigParameters(string $configId): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        $this->sampleConfigs = array_values(array_map(function (array $config) use ($configId, $configService): array {
            if ((string) ($config['id'] ?? '') !== $configId) {
                return $config;
            }

            $parameters = $configService->parametersForConfig(
                $this->crmCustomerId ?? '',
                $config['sample_type_id'] ?? null,
                $config['analysis_type_id'] ?? null
            );

            $config['parameter_keys'] = collect($parameters)
                ->pluck('analysis_element_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
            $config['suppress_requested_parameter_autofill'] = false;

            return $configService->syncParameterLabSections($config);
        }, $this->sampleConfigs));
    }

    public function deselectAllConfigParameters(string $configId): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        $this->sampleConfigs = array_values(array_map(function (array $config) use ($configId, $configService): array {
            if ((string) ($config['id'] ?? '') !== $configId) {
                return $config;
            }

            $config['parameter_keys'] = [];
            // Prevent TRF/requested-analysis autofill from immediately re-selecting after a clear.
            $config['suppress_requested_parameter_autofill'] = true;

            return $configService->syncParameterLabSections($config);
        }, $this->sampleConfigs));
    }

    public function setParameterLabSection(string $configId, string $parameterKey, string $labSectionId): void
    {
        $this->toggleParameterLabSection($configId, $parameterKey, $labSectionId, replace: true);
    }

    public function toggleParameterLabSection(
        string $configId,
        string $parameterKey,
        string $labSectionId,
        bool $replace = false,
    ): void {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $sections = is_array($config['parameter_lab_sections'] ?? null)
                ? $config['parameter_lab_sections']
                : [];
            $current = $configService->normalizeLabSectionIdList($sections[$parameterKey] ?? []);

            if ($replace) {
                $current = $labSectionId !== '' ? [$labSectionId] : [];
            } elseif (in_array($labSectionId, $current, true)) {
                $current = array_values(array_filter($current, fn (string $id) => $id !== $labSectionId));
            } else {
                $current[] = $labSectionId;
            }

            $sections[$parameterKey] = array_values(array_unique($current));
            $this->sampleConfigs[$index]['parameter_lab_sections'] = $sections;
            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
    }

    public function toggleSampleLabSection(string $configId, string $labSectionId): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $current = $configService->normalizeLabSectionIdList($config['lab_section_ids'] ?? []);

            if (in_array($labSectionId, $current, true)) {
                $current = array_values(array_filter($current, fn (string $id) => $id !== $labSectionId));
            } else {
                $current[] = $labSectionId;
            }

            $this->sampleConfigs[$index]['lab_section_ids'] = array_values(array_unique($current));
            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
    }

    /**
     * @return list<string>
     */
    public function sampleLabSectionIdsForConfigIndex(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $config = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

        return $configService->normalizeLabSectionIdList($config['lab_section_ids'] ?? []);
    }

    /**
     * @return list<array{id: string, name: string, code?: string, label?: string, lab_section_id: ?string, lab_section_ids: list<string>}>
     */
    public function selectedParametersWithLabSections(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $config = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

        $selectedKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
        $sectionMap = is_array($config['parameter_lab_sections'] ?? null) ? $config['parameter_lab_sections'] : [];
        $parameters = $this->parametersForConfigIndex($index);
        $byId = collect($parameters)->keyBy(
            fn (array $param): string => (string) ($param['analysis_element_id'] ?? $param['id'] ?? '')
        );

        $rows = [];
        foreach ($selectedKeys as $elementId) {
            $elementId = (string) $elementId;
            $param = $byId->get($elementId) ?? [
                'id' => $elementId,
                'analysis_element_id' => $elementId,
                'label' => 'Parameter',
                'code' => '',
            ];

            $sectionIds = $configService->normalizeLabSectionIdList($sectionMap[$elementId] ?? []);

            $rows[] = [
                'id' => $elementId,
                'analysis_element_id' => $elementId,
                'name' => (string) ($param['label'] ?? $param['code'] ?? 'Parameter'),
                'code' => (string) ($param['code'] ?? ''),
                'label' => (string) ($param['label'] ?? ''),
                'lab_section_id' => $sectionIds[0] ?? null,
                'lab_section_ids' => $sectionIds,
            ];
        }

        return $rows;
    }

    public function toggleSectionAnalyst(string $configId, string $labSectionId, string $userId): void
    {
        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $bySection = is_array($config['analysts_by_lab_section'] ?? null)
                ? $config['analysts_by_lab_section']
                : [];
            $assigned = array_values(array_map(
                'strval',
                is_array($bySection[$labSectionId] ?? null) ? $bySection[$labSectionId] : []
            ));

            if (in_array($userId, $assigned, true)) {
                $assigned = array_values(array_filter($assigned, fn (string $id) => $id !== $userId));
            } else {
                $assigned[] = $userId;
            }

            $bySection[$labSectionId] = array_values(array_unique($assigned));
            $this->sampleConfigs[$index]['analysts_by_lab_section'] = $bySection;
            $this->sampleConfigs[$index] = app(AcceptanceFormSampleConfigService::class)
                ->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
    }

    /**
     * @return list<array{id: string, name: string, analyst_ids: list<string>}>
     */
    public function labSectionsForAnalystAssignment(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $config = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

        $sectionIds = $configService->distinctLabSectionIdsFromConfig($config);
        $sectionNames = collect($this->configLabSections)->keyBy('id');
        $bySection = is_array($config['analysts_by_lab_section'] ?? null)
            ? $config['analysts_by_lab_section']
            : [];

        return array_values(array_map(function (string $sectionId) use ($sectionNames, $bySection): array {
            $match = $sectionNames->get($sectionId);

            return [
                'id' => $sectionId,
                'name' => (string) ($match['name'] ?? 'Lab section'),
                'analyst_ids' => array_values(array_map(
                    'strval',
                    is_array($bySection[$sectionId] ?? null) ? $bySection[$sectionId] : []
                )),
            ];
        }, $sectionIds));
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function analystsForLabSection(string $labSectionId): array
    {
        return app(AcceptanceFormSampleConfigService::class)->analystsForLabSectionPicker($labSectionId);
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getConfigSampleTypesProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->allSampleTypesForPicker();
    }

    public function getConfigZonesProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->zonesForPicker();
    }

    public function getConfigLabSectionsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->labSectionsForPicker();
    }

    public function getConfigSampleConditionsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->sampleConditionsForPicker();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function sampleConditionsForConfigIndex(int $index): array
    {
        $sampleTypeId = $this->sampleConfigs[$index]['sample_type_id'] ?? null;

        return app(AcceptanceFormSampleConfigService::class)->sampleConditionsForPicker(
            is_string($sampleTypeId) && $sampleTypeId !== '' ? $sampleTypeId : null
        );
    }

    public function getConfigStandardsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->standardsForPicker();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getConfigLabsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->labsForPicker();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getConfigAssignableUsersProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->assignableUsersForPicker();
    }

    public function instancePhotoUploadKey(string $configId): string
    {
        return $configId;
    }

    public function labelForConfigSampleType(?string $sampleTypeId): string
    {
        if ($sampleTypeId === null || $sampleTypeId === '') {
            return '—';
        }

        $match = collect($this->configSampleTypes)->firstWhere('id', $sampleTypeId);

        return (string) ($match['name'] ?? '—');
    }

    public function analysisTypesForConfigIndex(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $sampleTypeId = $this->sampleConfigs[$index]['sample_type_id'] ?? null;
        if (! $sampleTypeId) {
            return [];
        }

        return app(AcceptanceFormSampleConfigService::class)
            ->allAnalysisTypesForPicker((string) $sampleTypeId);
    }

    public function parametersForConfigIndex(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $config = $this->sampleConfigs[$index];

        return app(AcceptanceFormSampleConfigService::class)->parametersForConfig(
            $this->crmCustomerId ?? '',
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id'] ?? null
        );
    }

    public function filteredParametersForConfigIndex(int $index): array
    {
        $parameters = $this->parametersForConfigIndex($index);
        $search = strtolower(trim((string) ($this->sampleConfigs[$index]['parameter_search'] ?? '')));

        if ($search === '') {
            return $parameters;
        }

        return array_values(array_filter(
            $parameters,
            fn (array $param) => str_contains(strtolower((string) ($param['label'] ?? '')), $search)
                || str_contains(strtolower((string) ($param['analysis_type_name'] ?? '')), $search)
        ));
    }
}
