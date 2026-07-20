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
            $this->sampleConfigs[$index]['suppress_requested_parameter_autofill'] = $keys === [];

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

            return $config;
        }, $this->sampleConfigs));
    }

    public function deselectAllConfigParameters(string $configId): void
    {
        $this->sampleConfigs = array_values(array_map(function (array $config) use ($configId): array {
            if ((string) ($config['id'] ?? '') !== $configId) {
                return $config;
            }

            $config['parameter_keys'] = [];
            // Prevent TRF/requested-analysis autofill from immediately re-selecting after a clear.
            $config['suppress_requested_parameter_autofill'] = true;

            return $config;
        }, $this->sampleConfigs));
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
