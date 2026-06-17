<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SubmissionFormInstance;
use App\SampleCondition;
use App\SampleType;
use App\Standards;
use App\Zone;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcceptanceFormSampleConfigService
{
    public function __construct(
        private readonly AcceptanceFormPricingService $pricingService,
        private readonly InterzoneTransferService $interzoneTransferService,
    ) {}

    /**
     * @return array{id: string, sample_type_id: string|null, analysis_type_id: string|null, sample_condition_id: string|null, main_standard_id: string|null, zone_id: string|null, number_of_samples: int, parameter_keys: list<string>, parameter_search: string, instances: list<array{customer_sample_id: string, sample_marking: string}>}
     */
    public function emptyConfig(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'sample_type_id' => null,
            'analysis_type_id' => null,
            'sample_condition_id' => null,
            'main_standard_id' => null,
            'zone_id' => null,
            'number_of_samples' => 1,
            'parameter_keys' => [],
            'parameter_search' => '',
            'instances' => [
                ['customer_sample_id' => '', 'sample_marking' => ''],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $prefillLines
     * @return list<array<string, mixed>>
     */
    public function buildConfigsFromPrefill(
        array $prefillLines,
        ?SubmissionFormInstance $instance = null,
        ?string $defaultZoneId = null
    ): array {
        $defaultZoneId = $defaultZoneId ?? $this->resolveZoneIdFromInstance($instance);
        $buckets = [];

        foreach ($prefillLines as $line) {
            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $key = $sampleTypeId . '::' . $analysisTypeId;

            if (!isset($buckets[$key])) {
                $buckets[$key] = $this->emptyConfig();
                $buckets[$key]['sample_type_id'] = $sampleTypeId !== '' ? $sampleTypeId : null;
                $buckets[$key]['analysis_type_id'] = $analysisTypeId !== '' ? $analysisTypeId : null;
                $buckets[$key]['zone_id'] = $defaultZoneId;
                $buckets[$key]['number_of_samples'] = max(1, (int) ($line['number_of_samples'] ?? 1));
                $buckets[$key]['sample_condition_id'] = $this->resolveSampleConditionId(
                    $line['sample_condition_id'] ?? null,
                    $line['sample_condition'] ?? null,
                    $sampleTypeId !== '' ? $sampleTypeId : null
                );
            }

            $elementId = $line['analysis_element_id'] ?? null;
            if ($elementId) {
                $paramKey = (string) $elementId;
                if (!in_array($paramKey, $buckets[$key]['parameter_keys'], true)) {
                    $buckets[$key]['parameter_keys'][] = $paramKey;
                }
            }

            $customerSampleId = trim((string) ($line['customer_sample_id'] ?? ''));
            if ($customerSampleId !== '') {
                $instances = $buckets[$key]['instances'];
                $emptyIndex = null;
                foreach ($instances as $idx => $inst) {
                    if (trim((string) ($inst['customer_sample_id'] ?? '')) === '') {
                        $emptyIndex = $idx;
                        break;
                    }
                }
                if ($emptyIndex !== null) {
                    $instances[$emptyIndex]['customer_sample_id'] = $customerSampleId;
                } else {
                    $instances[] = ['customer_sample_id' => $customerSampleId, 'sample_marking' => ''];
                }
                $buckets[$key]['instances'] = $instances;
            }
        }

        $configs = array_values($buckets);

        foreach ($configs as &$config) {
            $config['instances'] = $this->syncInstances(
                $config['instances'],
                max(1, (int) $config['number_of_samples'])
            );
        }
        unset($config);

        if ($configs === []) {
            $empty = $this->emptyConfig();
            $empty['zone_id'] = $defaultZoneId;

            return [$empty];
        }

        return $configs;
    }

    public function resolveZoneIdFromInstance(?SubmissionFormInstance $instance): ?string
    {
        if ($instance === null) {
            return null;
        }

        if (! empty($instance->processing_zone_id)) {
            return (string) $instance->processing_zone_id;
        }

        if (! empty($instance->zone_id)) {
            return (string) $instance->zone_id;
        }

        return $this->interzoneTransferService->resolveInstanceOriginZoneId($instance);
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function sampleTypesForCustomer(string $customerId, array $existingLines = []): array
    {
        $types = $this->pricingService->sampleTypesForAddLinePicker($customerId, $existingLines);
        if ($types !== []) {
            return $types;
        }

        return SampleType::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (SampleType $type) => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function analysisTypesForSampleType(string $customerId, ?string $sampleTypeId, array $existingLines = []): array
    {
        if ($sampleTypeId === null || $sampleTypeId === '') {
            return [];
        }

        $analysisTypes = $this->pricingService->analysisTypesForAddLinePicker($customerId, $sampleTypeId, $existingLines);
        if ($analysisTypes !== []) {
            return $analysisTypes;
        }

        return AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AnalysisType $type) => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parametersForConfig(string $customerId, ?string $sampleTypeId, ?string $analysisTypeId): array
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return [];
        }

        $parameters = $this->pricingService->parametersForAddLineSelection($customerId, $sampleTypeId, $analysisTypeId);
        if ($parameters !== []) {
            return $parameters;
        }

        return AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->with(['analyte:id,name'])
            ->orderBy('level')
            ->get()
            ->map(function (AnalysisElements $element) use ($analysisTypeId, $sampleTypeId): array {
                $label = (string) ($element->analyte?->name ?? $element->method ?? 'Parameter');

                return [
                    'id' => (string) $element->id,
                    'analysis_element_id' => (string) $element->id,
                    'analysis_type_id' => (string) $analysisTypeId,
                    'sample_type_id' => $sampleTypeId,
                    'label' => $label,
                    'unit_amount' => 0.0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    /**
     * @return list<array{id: string, name: string}>
     */
    public function zonesForPicker(): array
    {
        return Zone::query()
            ->orderBy('value')
            ->get(['id', 'key', 'value'])
            ->map(fn (Zone $zone) => [
                'id' => (string) $zone->id,
                'name' => trim(($zone->key ? $zone->key . ' — ' : '') . $zone->value),
            ])
            ->all();
    }

    private function resolveZoneIdFromConfig(array $config): ?string
    {
        $zoneId = $config['zone_id'] ?? $config['lab_id'] ?? null;

        return $zoneId !== null && (string) $zoneId !== '' ? (string) $zoneId : null;
    }

    private function resolveSampleConditionId(
        mixed $conditionId,
        mixed $conditionLabel,
        ?string $sampleTypeId = null
    ): ?string {
        if (is_string($conditionId) && trim($conditionId) !== '') {
            return trim($conditionId);
        }

        $label = trim((string) $conditionLabel);
        if ($label === '') {
            return null;
        }

        $query = SampleCondition::query()->orderBy('name');

        if (Schema::hasColumn('sample_conditions', 'active')) {
            $query->where(function ($activeQuery): void {
                $activeQuery->where('active', 1)->orWhereNull('active');
            });
        }

        if ($sampleTypeId !== null && $sampleTypeId !== '') {
            $query->where(function ($typeQuery) use ($sampleTypeId): void {
                $typeQuery
                    ->where('sample_type_id', $sampleTypeId)
                    ->orWhereNull('sample_type_id');
            });
        }

        $match = $query
            ->where(function ($labelQuery) use ($label): void {
                $labelQuery
                    ->whereRaw('name ILIKE ?', [$label])
                    ->orWhereRaw('short_name ILIKE ?', [$label]);
            })
            ->value('id');

        return $match !== null ? (string) $match : null;
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function sampleConditionsForPicker(?string $sampleTypeId = null): array
    {
        $query = SampleCondition::query()->orderBy('name');

        if (Schema::hasColumn('sample_conditions', 'active')) {
            $query->where(function ($activeQuery): void {
                $activeQuery->where('active', 1)->orWhereNull('active');
            });
        }

        if ($sampleTypeId !== null && $sampleTypeId !== '') {
            $query->where(function ($typeQuery) use ($sampleTypeId): void {
                $typeQuery
                    ->where('sample_type_id', $sampleTypeId)
                    ->orWhereNull('sample_type_id');
            });
        }

        $conditions = $query->get(['id', 'name']);

        if ($conditions->isEmpty() && $sampleTypeId !== null && $sampleTypeId !== '') {
            $conditions = SampleCondition::query()
                ->orderBy('name')
                ->when(
                    Schema::hasColumn('sample_conditions', 'active'),
                    fn ($fallback) => $fallback->where(function ($activeQuery): void {
                        $activeQuery->where('active', 1)->orWhereNull('active');
                    })
                )
                ->get(['id', 'name']);
        }

        return $conditions
            ->map(fn (SampleCondition $c) => ['id' => (string) $c->id, 'name' => (string) $c->name])
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function standardsForPicker(): array
    {
        return Standards::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Standards $s) => ['id' => (string) $s->id, 'name' => (string) $s->name])
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $instances
     * @return list<array{customer_sample_id: string, sample_marking: string}>
     */
    public function syncInstances(array $instances, int $numberOfSamples): array
    {
        $numberOfSamples = max(1, $numberOfSamples);
        $normalized = [];

        for ($i = 0; $i < $numberOfSamples; $i++) {
            $normalized[] = [
                'customer_sample_id' => trim((string) ($instances[$i]['customer_sample_id'] ?? '')),
                'sample_marking' => trim((string) ($instances[$i]['sample_marking'] ?? '')),
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     *
     * @throws ValidationException
     */
    public function validateConfigs(array $configs): void
    {
        if ($configs === []) {
            throw ValidationException::withMessages([
                'sampleConfigs' => 'Add at least one sample configuration.',
            ]);
        }

        $errors = [];

        foreach ($configs as $index => $config) {
            $row = $index + 1;
            if (empty($config['sample_type_id'])) {
                $errors["sampleConfigs.{$index}.sample_type_id"] = "Row {$row}: sample type is required.";
            }
            if (empty($config['analysis_type_id'])) {
                $errors["sampleConfigs.{$index}.analysis_type_id"] = "Row {$row}: analysis type is required.";
            }
            if (empty($config['parameter_keys']) || !is_array($config['parameter_keys'])) {
                $errors["sampleConfigs.{$index}.parameter_keys"] = "Row {$row}: select at least one parameter.";
            }
            $count = max(1, (int) ($config['number_of_samples'] ?? 1));
            $instances = $config['instances'] ?? [];
            if (count($instances) !== $count) {
                $errors["sampleConfigs.{$index}.instances"] = "Row {$row}: sample instance count must match number of samples.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function expandConfigsToLines(array $configs, string $customerId): array
    {
        $lines = [];
        $sortOrder = 0;

        foreach ($configs as $config) {
            $sampleTypeId = $config['sample_type_id'] ?? null;
            $analysisTypeId = (string) ($config['analysis_type_id'] ?? '');
            $numberOfSamples = max(1, (int) ($config['number_of_samples'] ?? 1));
            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $configKey = (string) ($config['id'] ?? Str::uuid());

            $parameters = $this->parametersForConfig($customerId, $sampleTypeId, $analysisTypeId);
            $parameterMap = collect($parameters)->keyBy(fn (array $p) => (string) ($p['analysis_element_id'] ?? $p['id']));

            foreach ($parameterKeys as $paramKey) {
                $paramKey = (string) $paramKey;
                $parameter = $parameterMap->get($paramKey);
                if ($parameter === null) {
                    continue;
                }

                $lines[] = [
                    'line_no' => count($lines) + 1,
                    'sample_type_id' => $sampleTypeId,
                    'sample_type_name' => $parameter['sample_type_name']
                        ?? optional(SampleType::find($sampleTypeId))->name
                        ?? '',
                    'analysis_type_id' => $analysisTypeId,
                    'analysis_type_name' => $parameter['analysis_type_name']
                        ?? optional(AnalysisType::find($analysisTypeId))->name
                        ?? '',
                    'analysis_element_id' => $parameter['analysis_element_id'] ?? $paramKey,
                    'parameter_label' => (string) ($parameter['label'] ?? 'Parameter'),
                    'unit_amount' => (float) ($parameter['unit_amount'] ?? 0),
                    'number_of_samples' => $numberOfSamples,
                    'is_approved' => true,
                    'sort_order' => $sortOrder++,
                    'acceptance_config_key' => $configKey,
                    'sample_condition_id' => $config['sample_condition_id'] ?? null,
                    'main_standard_id' => $config['main_standard_id'] ?? null,
                    'zone_id' => $this->resolveZoneIdFromConfig($config),
                    'instances' => $config['instances'] ?? [],
                ];
            }
        }

        return $this->pricingService->deduplicateRedundantAnalysisTypeLines($lines);
    }

    /**
     * Total sample count across all configs (sum of instances).
     *
     * @param  list<array<string, mixed>>  $configs
     */
    public function totalSampleCount(array $configs): int
    {
        return (int) collect($configs)->sum(fn (array $c) => max(1, (int) ($c['number_of_samples'] ?? 1)));
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function normalizeConfigsForStorage(array $configs): array
    {
        return collect($configs)->map(function (array $config) {
            $count = max(1, (int) ($config['number_of_samples'] ?? 1));

            return [
                'id' => (string) ($config['id'] ?? Str::uuid()),
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'analysis_type_id' => $config['analysis_type_id'] ?? null,
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'main_standard_id' => $config['main_standard_id'] ?? null,
                'zone_id' => $this->resolveZoneIdFromConfig($config),
                'number_of_samples' => $count,
                'parameter_keys' => array_values(array_map('strval', $config['parameter_keys'] ?? [])),
                'instances' => $this->syncInstances($config['instances'] ?? [], $count),
            ];
        })->values()->all();
    }

    /**
     * Build detail creation plans from stored configuration payload.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return list<array{
     *     sample_type_id: ?string,
     *     analysis_type_ids: list<string>,
     *     analysis_element_ids: list<string>,
     *     sample_condition_id: ?string,
     *     main_standard_id: ?string,
     *     zone_id: ?string,
     *     customer_sample_id: ?string,
     *     sample_marking: ?string
     * }>
     */
    public function buildDetailPlansFromConfigs(array $configs): array
    {
        $plans = [];

        foreach ($configs as $config) {
            $analysisTypeId = (string) ($config['analysis_type_id'] ?? '');
            if ($analysisTypeId === '') {
                continue;
            }

            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $instances = $this->syncInstances(
                $config['instances'] ?? [],
                max(1, (int) ($config['number_of_samples'] ?? 1))
            );

            foreach ($instances as $instance) {
                $plans[] = [
                    'sample_type_id' => $config['sample_type_id'] ?? null,
                    'analysis_type_ids' => [$analysisTypeId],
                    'analysis_element_ids' => array_map('strval', $parameterKeys),
                    'sample_condition_id' => $config['sample_condition_id'] ?? null,
                    'main_standard_id' => $config['main_standard_id'] ?? null,
                    'zone_id' => $this->resolveZoneIdFromConfig($config),
                    'customer_sample_id' => $instance['customer_sample_id'] !== ''
                        ? $instance['customer_sample_id']
                        : null,
                    'sample_marking' => $instance['sample_marking'] !== ''
                        ? $instance['sample_marking']
                        : null,
                ];
            }
        }

        return $plans;
    }
}
