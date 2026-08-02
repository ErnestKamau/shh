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

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs[$index] = $configService->syncSampleTypeIdsOnConfig(
            $this->sampleConfigs[$index],
            [trim((string) ($this->sampleConfigs[$index]['sample_type_id'] ?? ''))],
        );
        $this->sampleConfigs[$index]['analysis_type_id'] = null;
        $this->sampleConfigs[$index]['analysis_type_ids'] = [];
        $this->sampleConfigs[$index]['parameter_keys'] = [];
        $this->sampleConfigs[$index]['lab_section_id'] = null;
        $this->sampleConfigs[$index]['parameter_lab_sections'] = [];
        $this->sampleConfigs[$index]['analysts_by_lab_section'] = [];
    }

    public function setConfigSampleType(int $index, string $sampleTypeId): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $this->sampleConfigs[$index]['sample_type_id'] = trim($sampleTypeId);
        $this->onConfigSampleTypeChanged($index);
    }

    public function toggleConfigSampleType(string $configId, string $sampleTypeId): void
    {
        $sampleTypeId = trim($sampleTypeId);
        if ($sampleTypeId === '') {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $ids = $configService->sampleTypeIdsFromConfig($config);
            if (in_array($sampleTypeId, $ids, true)) {
                $ids = array_values(array_filter($ids, fn (string $id): bool => $id !== $sampleTypeId));
            } else {
                $ids[] = $sampleTypeId;
            }

            $this->sampleConfigs[$index] = $configService->syncSampleTypeIdsOnConfig($config, $ids);

            $availableAnalysisTypeIds = collect(
                $configService->allAnalysisTypesForSampleTypes($ids),
            )->pluck('id')->map(fn ($id): string => (string) $id)->all();

            $keptAnalysisTypeIds = array_values(array_filter(
                $configService->analysisTypeIdsFromConfig($this->sampleConfigs[$index]),
                fn (string $id): bool => in_array($id, $availableAnalysisTypeIds, true),
            ));
            $this->sampleConfigs[$index] = $configService->syncAnalysisTypeIdsOnConfig(
                $this->sampleConfigs[$index],
                $keptAnalysisTypeIds,
            );
            $this->sampleConfigs[$index]['parameter_keys'] = [];
            $this->sampleConfigs[$index]['parameter_lab_sections'] = [];
            $this->sampleConfigs[$index]['analysts_by_lab_section'] = [];

            break;
        }
    }

    public function onConfigAnalysisTypeChanged(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs[$index] = $configService->syncAnalysisTypeIdsOnConfig($this->sampleConfigs[$index]);
        $config = $this->sampleConfigs[$index];
        $analysisTypeIds = $configService->analysisTypeIdsFromConfig($config);

        $this->sampleConfigs[$index]['lab_section_id'] = $analysisTypeIds !== []
            ? $configService->resolveLabSectionIdForAnalysisType($analysisTypeIds[0])
            : null;

        // Start empty so the user picks parameters from the dropdown (or Select all).
        $this->sampleConfigs[$index]['parameter_keys'] = [];
        $this->sampleConfigs[$index]['parameter_lab_sections'] = [];
        $this->sampleConfigs[$index]['analysts_by_lab_section'] = [];
    }

    public function toggleConfigAnalysisType(string $configId, string $analysisTypeId): void
    {
        $analysisTypeId = trim($analysisTypeId);
        if ($analysisTypeId === '') {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $ids = $configService->analysisTypeIdsFromConfig($config);
            if (in_array($analysisTypeId, $ids, true)) {
                $ids = array_values(array_filter($ids, fn (string $id): bool => $id !== $analysisTypeId));
            } else {
                $ids[] = $analysisTypeId;
            }

            $this->sampleConfigs[$index] = $configService->syncAnalysisTypeIdsOnConfig($config, $ids);
            $this->sampleConfigs[$index]['lab_section_id'] = $ids !== []
                ? $configService->resolveLabSectionIdForAnalysisType($ids[0])
                : null;

            if (property_exists($this, 'crmCustomerId')
                && is_string($this->crmCustomerId)
                && trim($this->crmCustomerId) !== '') {
                $this->sampleConfigs[$index] = $configService->reconcileParameterKeysForConfig(
                    $this->sampleConfigs[$index],
                    $this->crmCustomerId,
                );
            } else {
                $this->sampleConfigs[$index]['parameter_keys'] = [];
            }

            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
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
                $configService->analysisTypeIdsFromConfig($config),
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
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $sections = is_array($config['parameter_lab_sections'] ?? null)
                ? $config['parameter_lab_sections']
                : [];
            $sections[$parameterKey] = $configService->normalizeLabSectionIds($labSectionId);
            $this->sampleConfigs[$index]['parameter_lab_sections'] = $sections;
            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
    }

    /**
     * @param  list<string>  $labSectionIds
     */
    public function setParameterLabSections(string $configId, string $parameterKey, array $labSectionIds): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $sections = is_array($config['parameter_lab_sections'] ?? null)
                ? $config['parameter_lab_sections']
                : [];
            $sections[$parameterKey] = $configService->normalizeLabSectionIds($labSectionIds);
            $this->sampleConfigs[$index]['parameter_lab_sections'] = $sections;
            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);

            break;
        }
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
     * @return list<array{id: string, name: string, code?: string, label?: string, lab_section_id: ?string}>
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

            $rows[] = [
                'id' => $elementId,
                'analysis_element_id' => $elementId,
                'name' => (string) ($param['label'] ?? $param['code'] ?? 'Parameter'),
                'code' => (string) ($param['code'] ?? ''),
                'label' => (string) ($param['label'] ?? ''),
                'lab_section_id' => app(AcceptanceFormSampleConfigService::class)
                    ->primaryLabSectionId($sectionMap[$elementId] ?? null),
                'lab_section_ids' => app(AcceptanceFormSampleConfigService::class)
                    ->normalizeLabSectionIds($sectionMap[$elementId] ?? null),
            ];
        }

        return $rows;
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

        $config = $this->sampleConfigs[$index];
        $configService = app(AcceptanceFormSampleConfigService::class);
        $sampleTypeIds = $configService->sampleTypeIdsFromConfig($config);
        if ($sampleTypeIds === []) {
            return [];
        }

        $types = count($sampleTypeIds) > 1 || (bool) ($config['allows_multiple_sample_types'] ?? false)
            ? $configService->allAnalysisTypesForSampleTypes($sampleTypeIds)
            : $configService->allAnalysisTypesForPicker($sampleTypeIds[0]);

        return $configService->withSelectedAnalysisTypes(
            $types,
            $configService->analysisTypeIdsFromConfig($config),
        );
    }

    public function parametersForConfigIndex(int $index): array
    {
        if (! isset($this->sampleConfigs[$index])) {
            return [];
        }

        $config = $this->sampleConfigs[$index];
        $configService = app(AcceptanceFormSampleConfigService::class);

        return $configService->parametersForConfig(
            $this->crmCustomerId ?? '',
            $config['sample_type_id'] ?? null,
            $configService->analysisTypeIdsFromConfig($config),
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
