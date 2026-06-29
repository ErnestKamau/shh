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
            $this->setStatus('error', 'At least one sample configuration is required.');

            return;
        }

        $this->sampleConfigs = array_values(array_filter(
            $this->sampleConfigs,
            fn (array $config) => (string) ($config['id'] ?? '') !== $configId
        ));
    }

    public function updatedSampleConfigs(mixed $value, string $key): void
    {
        if (! preg_match('/^(\d+)\.number_of_samples$/', (string) $key, $matches)) {
            return;
        }

        $this->syncSampleConfigInstances((int) $matches[1]);
    }

    public function onConfigNumberOfSamplesChanged(int $index): void
    {
        $this->syncSampleConfigInstances($index);
    }

    private function syncSampleConfigInstances(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $count = max(1, (int) ($this->sampleConfigs[$index]['number_of_samples'] ?? 1));
        $this->sampleConfigs[$index]['number_of_samples'] = $count;
        $this->sampleConfigs[$index]['instances'] = $configService->syncInstances(
            $this->sampleConfigs[$index]['instances'] ?? [],
            $count
        );
    }

    public function onConfigSampleTypeChanged(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $this->sampleConfigs[$index]['analysis_type_id'] = null;
        $this->sampleConfigs[$index]['parameter_keys'] = [];
        $this->sampleConfigs[$index]['lab_section_id'] = null;
    }

    public function onConfigAnalysisTypeChanged(int $index): void
    {
        if (! isset($this->sampleConfigs[$index])) {
            return;
        }

        $config = $this->sampleConfigs[$index];
        $configService = app(AcceptanceFormSampleConfigService::class);

        if (! empty($config['analysis_type_id']) && empty($config['lab_section_id'])) {
            $this->sampleConfigs[$index]['lab_section_id'] = $configService->resolveLabSectionIdForAnalysisType(
                (string) $config['analysis_type_id']
            );
        }

        if (! $this->crmCustomerId || empty($config['analysis_type_id'])) {
            $this->sampleConfigs[$index]['parameter_keys'] = [];

            return;
        }

        $parameters = app(AcceptanceFormSampleConfigService::class)->parametersForConfig(
            $this->crmCustomerId ?? '',
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id']
        );

        $this->sampleConfigs[$index]['parameter_keys'] = collect($parameters)
            ->pluck('analysis_element_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
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

            if (property_exists($this, 'crmCustomerId')
                && is_string($this->crmCustomerId)
                && trim($this->crmCustomerId) !== '') {
                $this->sampleConfigs[$index] = app(AcceptanceFormSampleConfigService::class)
                    ->reconcileParameterKeysForConfig($this->sampleConfigs[$index], $this->crmCustomerId);
            }

            break;
        }
    }

    public function selectAllConfigParameters(string $configId): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $parameters = $configService->parametersForConfig(
                $this->crmCustomerId ?? '',
                $config['sample_type_id'] ?? null,
                $config['analysis_type_id'] ?? null
            );

            $this->sampleConfigs[$index]['parameter_keys'] = collect($parameters)
                ->pluck('analysis_element_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            break;
        }
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
