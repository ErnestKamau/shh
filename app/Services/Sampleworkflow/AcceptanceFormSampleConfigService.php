<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Lab;
use App\Models\SampleSubmissionRequest;
use App\Models\SampleSubmissionRequestRequestedAnalysis;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use App\SampleAnalysisStage;
use App\SampleCondition;
use App\SampleType;
use App\Standards;
use App\User;
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
     * @return array<string, mixed>
     */
    public function emptyConfig(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'sample_type_id' => null,
            /** @var list<string> */
            'sample_type_ids' => [],
            'allows_multiple_sample_types' => false,
            'analysis_type_id' => null,
            /** @var list<string> */
            'analysis_type_ids' => [],
            'sample_condition_id' => null,
            'main_standard_id' => null,
            'secondary_standard_id' => null,
            'zone_id' => null,
            'lab_section_id' => null,
            'lab_id' => Lab::defaultLabId(),
            'assigned_user_id' => null,
            'row_index' => null,
            'number_of_samples' => 1,
            'parameter_keys' => [],
            'parameter_search' => '',
            /** @var array<string, string> analysis_element_id => lab_section_id */
            'parameter_lab_sections' => [],
            /** @var list<string> analysis element IDs marked for subcontracting */
            'subcontracted_parameter_keys' => [],
            /** @var array<string, list<string>> lab_section_id => user ids */
            'analysts_by_lab_section' => [],
            /** @var array<string, array<string, list<string>>> analysis_element_id => lab_section_id => user ids */
            'analysts_by_element' => [],
            'sample_code_prefix' => null,
            'customer_sample_id' => '',
            'sample_marking' => '',
            'disposal_date' => '',
            'photo_path' => '',
        ];
    }

    /**
     * Normalize singular/plural sample type fields on a sample config.
     *
     * @param  array<string, mixed>  $config
     * @param  list<string>|null  $sampleTypeIds
     * @return array<string, mixed>
     */
    public function syncSampleTypeIdsOnConfig(array $config, ?array $sampleTypeIds = null): array
    {
        $ids = $sampleTypeIds ?? $this->sampleTypeIdsFromConfig($config);
        $normalized = [];

        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id === '' || in_array($id, $normalized, true)) {
                continue;
            }
            $normalized[] = $id;
        }

        $config['sample_type_ids'] = $normalized;
        $config['sample_type_id'] = $normalized[0] ?? null;

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    public function sampleTypeIdsFromConfig(array $config): array
    {
        $ids = [];
        $plural = $config['sample_type_ids'] ?? null;

        if (is_array($plural)) {
            foreach ($plural as $id) {
                $id = trim((string) $id);
                if ($id !== '' && ! in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        } elseif (is_string($plural) && trim($plural) !== '') {
            foreach (preg_split('/\s*,\s*/', trim($plural)) ?: [] as $id) {
                $id = trim($id);
                if ($id !== '' && ! in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        }

        $singular = trim((string) ($config['sample_type_id'] ?? ''));
        if ($singular !== '' && ! in_array($singular, $ids, true)) {
            array_unshift($ids, $singular);
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    public function sampleTypeIdsFromPrefillLine(array $line): array
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        return $this->sampleTypeIdsFromConfig([
            'sample_type_id' => $line['sample_type_id'] ?? null,
            'sample_type_ids' => $attributes['sample_type_ids'] ?? $line['sample_type_ids'] ?? [],
        ]);
    }

    /**
     * Normalize singular/plural analysis type fields on a sample config.
     *
     * @param  array<string, mixed>  $config
     * @param  list<string>|null  $analysisTypeIds
     * @return array<string, mixed>
     */
    public function syncAnalysisTypeIdsOnConfig(array $config, ?array $analysisTypeIds = null): array
    {
        $ids = $analysisTypeIds ?? $this->analysisTypeIdsFromConfig($config);
        $normalized = [];

        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id === '' || in_array($id, $normalized, true)) {
                continue;
            }
            $normalized[] = $id;
        }

        $config['analysis_type_ids'] = $normalized;
        $config['analysis_type_id'] = $normalized[0] ?? null;

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    public function analysisTypeIdsFromConfig(array $config): array
    {
        $ids = [];

        $fromPlural = $config['analysis_type_ids'] ?? null;
        if (is_array($fromPlural)) {
            foreach ($fromPlural as $id) {
                $id = trim((string) $id);
                if ($id !== '' && ! in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        } elseif (is_string($fromPlural) && trim($fromPlural) !== '') {
            foreach (preg_split('/\s*,\s*/', trim($fromPlural)) ?: [] as $token) {
                $token = trim((string) $token);
                if ($token !== '' && ! in_array($token, $ids, true)) {
                    $ids[] = $token;
                }
            }
        }

        $singular = trim((string) ($config['analysis_type_id'] ?? ''));
        if ($singular !== '' && ! in_array($singular, $ids, true)) {
            array_unshift($ids, $singular);
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    public function analysisTypeIdsFromPrefillLine(array $line): array
    {
        $ids = [];
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];

        $fromAttributes = $attributes['analysis_type_ids'] ?? null;
        if (is_array($fromAttributes)) {
            foreach ($fromAttributes as $id) {
                $id = trim((string) $id);
                if ($id !== '' && ! in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        } elseif (is_string($fromAttributes) && trim($fromAttributes) !== '') {
            foreach (preg_split('/\s*,\s*/', trim($fromAttributes)) ?: [] as $token) {
                $token = trim((string) $token);
                if ($token !== '' && ! in_array($token, $ids, true)) {
                    $ids[] = $token;
                }
            }
        }

        $singular = trim((string) ($line['analysis_type_id'] ?? ''));
        if ($singular !== '' && ! in_array($singular, $ids, true)) {
            // Prefer explicit multi-ids order when present; otherwise singular leads.
            if ($ids === []) {
                $ids[] = $singular;
            } else {
                array_unshift($ids, $singular);
                $ids = array_values(array_unique($ids));
            }
        }

        return $ids;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function normalizeConfigsAnalysisTypeIds(array $configs): array
    {
        return array_values(array_map(
            fn (array $config): array => $this->syncAnalysisTypeIdsOnConfig($config),
            $configs,
        ));
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
        $allowsMultipleSampleTypes = false;
        if ($instance !== null) {
            $instance->loadMissing('submissionForm.sampleTypes');
            $allowsMultipleSampleTypes = $instance->submissionForm?->sampleTypes?->isEmpty() ?? false;
        }
        $buckets = [];

        foreach ($prefillLines as $lineIndex => $line) {
            $sampleTypeIds = $this->sampleTypeIdsFromPrefillLine($line);
            $sampleTypeId = $sampleTypeIds[0] ?? '';
            $analysisTypeIds = $this->analysisTypeIdsFromPrefillLine($line);
            $analysisTypeId = $analysisTypeIds[0] ?? '';
            $key = $this->resolvePhysicalSampleKey($line, $lineIndex);

            $lineSampleCount = $this->resolvePrefillLineNumberOfSamples($line);

            if (! isset($buckets[$key])) {
                $buckets[$key] = $this->emptyConfig();
                $buckets[$key] = $this->syncSampleTypeIdsOnConfig($buckets[$key], $sampleTypeIds);
                $buckets[$key]['allows_multiple_sample_types'] = $allowsMultipleSampleTypes
                    || (bool) ($line['allows_multiple_sample_types'] ?? false)
                    || count($sampleTypeIds) > 1;
                $buckets[$key] = $this->syncAnalysisTypeIdsOnConfig($buckets[$key], $analysisTypeIds);
                $buckets[$key]['zone_id'] = $defaultZoneId;
                $buckets[$key]['lab_section_id'] = $this->resolveLabSectionIdForAnalysisType($analysisTypeId);
                $buckets[$key]['number_of_samples'] = $lineSampleCount;
                $buckets[$key]['row_index'] = isset($line['row_index']) ? (int) $line['row_index'] : null;
                $buckets[$key]['sample_condition_id'] = $this->resolveSampleConditionId(
                    $line['sample_condition_id'] ?? null,
                    $line['sample_condition'] ?? null,
                    $sampleTypeId !== '' ? $sampleTypeId : null
                );
                if (! empty($line['sample_code_prefix'])) {
                    $buckets[$key]['sample_code_prefix'] = $line['sample_code_prefix'];
                }
            } else {
                $buckets[$key] = $this->syncSampleTypeIdsOnConfig(
                    $buckets[$key],
                    array_merge($this->sampleTypeIdsFromConfig($buckets[$key]), $sampleTypeIds),
                );
                $buckets[$key]['allows_multiple_sample_types'] =
                    (bool) ($buckets[$key]['allows_multiple_sample_types'] ?? false)
                    || $allowsMultipleSampleTypes
                    || (bool) ($line['allows_multiple_sample_types'] ?? false)
                    || count($this->sampleTypeIdsFromConfig($buckets[$key])) > 1;
                $buckets[$key] = $this->syncAnalysisTypeIdsOnConfig(
                    $buckets[$key],
                    array_merge($this->analysisTypeIdsFromConfig($buckets[$key]), $analysisTypeIds),
                );
                $buckets[$key]['number_of_samples'] = max(
                    (int) $buckets[$key]['number_of_samples'],
                    $lineSampleCount,
                );
            }

            foreach ($this->elementIdsFromPrefillLine($line) as $paramKey) {
                if (! in_array($paramKey, $buckets[$key]['parameter_keys'], true)) {
                    $buckets[$key]['parameter_keys'][] = $paramKey;
                }
            }

            $customerSampleId = trim((string) ($line['customer_sample_id'] ?? ''));
            if ($customerSampleId !== '' && trim((string) ($buckets[$key]['customer_sample_id'] ?? '')) === '') {
                $buckets[$key]['customer_sample_id'] = $customerSampleId;
            }
        }

        $configs = $this->explodeBucketedConfigsToPerSample(array_values($buckets));

        if ($configs === []) {
            $empty = $this->emptyConfig();
            $empty['zone_id'] = $defaultZoneId;

            return [$empty];
        }

        return $configs;
    }

    /**
     * Build prefill rows for sample configuration from enquiry TRF data.
     * TRF sample_lines represent physical samples; quotation lines are per-parameter.
     *
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    public function buildPrefillLinesFromEnquiry(
        SampleSubmissionRequest $enquiry,
        array $quotationLines = [],
        ?SubmissionFormInstance $instance = null,
    ): array {
        // Linked TRF instance is the source of truth for requested samples/tests.
        $sampleLines = $this->resolveTrfSampleLines($enquiry, $instance);

        if ($sampleLines !== []) {
            return $this->buildPrefillLinesFromTrfSampleLines($enquiry, $sampleLines, $quotationLines);
        }

        if ($quotationLines !== []) {
            return $this->buildPrefillLinesFromQuotationLines($enquiry, $quotationLines);
        }

        return [];
    }

    /**
     * Resolve sample lines from the linked TRF instance when present; otherwise enquiry sample_lines.
     *
     * @return list<array<string, mixed>>
     */
    public function resolveTrfSampleLines(
        SampleSubmissionRequest $enquiry,
        ?SubmissionFormInstance $instance = null,
    ): array {
        $instance ??= $enquiry->submissionFormInstance;
        if ($instance !== null) {
            $fromInstance = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);
            if ($fromInstance !== []) {
                return $fromInstance;
            }
        }

        return is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
    }

    /**
     * Build acceptance sample configs from accepted quotation lines, merging reception fields
     * saved during Process Enquiry onto each per-sample row.
     *
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    public function prepareAcceptanceConfigsFromQuotation(
        SampleSubmissionRequest $enquiry,
        array $quotationLines,
        ?SubmissionFormInstance $instance = null,
    ): array {
        $defaultZoneId = $this->resolveZoneIdFromInstance($instance);

        $stored = is_array($enquiry->enquiry_sample_configuration)
            ? $this->flattenToPerSampleConfigs($enquiry->enquiry_sample_configuration)
            : [];

        if ($stored !== []) {
            $configs = $stored;
        } else {
            $prefillLines = $this->quotationLinesToPrefillLines($quotationLines);
            $configs = $this->buildConfigsFromPrefill($prefillLines, $instance, $defaultZoneId);
        }

        $configs = $this->syncParameterKeysFromQuotationLines($configs, $quotationLines);

        foreach ($configs as $index => $config) {
            $configs[$index] = $this->syncAnalysisTypeIdsOnConfig($config);
            $analysisTypeIds = $this->analysisTypeIdsFromConfig($configs[$index]);
            $configs[$index]['parameter_keys'] = $this->resolveElementIdsForAnalysisTypes(
                is_array($configs[$index]['parameter_keys'] ?? null) ? $configs[$index]['parameter_keys'] : [],
                $analysisTypeIds,
            );
            $configs[$index]['number_of_samples'] = 1;

            if (empty($configs[$index]['zone_id']) && $defaultZoneId !== null) {
                $configs[$index]['zone_id'] = $defaultZoneId;
            }
        }

        if ($configs === []) {
            $empty = $this->emptyConfig();
            $empty['zone_id'] = $defaultZoneId;

            return [$empty];
        }

        return $configs;
    }

    /**
     * Fill empty parameter_keys from quotation lines. Existing selections from
     * Process Enquiry / enquiry_sample_configuration are preserved so quotation
     * expansion (analysis-type-only lines → all elements) cannot overwrite them.
     *
     * @param  list<array<string, mixed>>  $configs
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    public function syncParameterKeysFromQuotationLines(array $configs, array $quotationLines): array
    {
        if ($configs === [] || $quotationLines === []) {
            return $configs;
        }

        $linesByKey = collect($quotationLines)->groupBy(
            fn (array $line): string => $this->configGroupingKey(
                $line['sample_type_id'] ?? null,
                $line['analysis_type_id'] ?? null,
            )
        );

        $sampleSlotByKey = [];

        foreach ($configs as $index => $config) {
            $existingKeys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '')
                ->unique()
                ->values()
                ->all();

            if ($existingKeys !== []) {
                $configs[$index]['parameter_keys'] = $existingKeys;

                continue;
            }

            $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
            if ($analysisTypeIds === []) {
                $analysisTypeIds = [null];
            }

            $group = collect();
            foreach ($analysisTypeIds as $analysisTypeId) {
                $key = $this->configGroupingKey(
                    $config['sample_type_id'] ?? null,
                    $analysisTypeId,
                );
                $group = $group->merge($linesByKey->get($key, collect())->values());
            }
            $group = $group->unique(function (array $line): string {
                return implode('::', [
                    (string) ($line['analysis_type_id'] ?? ''),
                    (string) ($line['analysis_element_id'] ?? ''),
                    (string) ($line['parameter_label'] ?? ''),
                ]);
            })->values();

            if ($group->isEmpty()) {
                continue;
            }

            $primaryKey = $this->configGroupingKey(
                $config['sample_type_id'] ?? null,
                $analysisTypeIds[0] ?? null,
            );

            $configsWithSameKey = collect($configs)->filter(
                function (array $candidate) use ($analysisTypeIds, $config): bool {
                    $candidateIds = $this->analysisTypeIdsFromConfig($candidate);
                    if ((string) ($candidate['sample_type_id'] ?? '') !== (string) ($config['sample_type_id'] ?? '')) {
                        return false;
                    }

                    return $candidateIds === $analysisTypeIds
                        || ($candidateIds[0] ?? null) === ($analysisTypeIds[0] ?? null);
                }
            )->count();

            $paramsPerSample = (int) max(1, (int) floor($group->count() / max(1, $configsWithSameKey)));
            $sampleSlot = $sampleSlotByKey[$primaryKey] ?? 0;
            $sampleSlotByKey[$primaryKey] = $sampleSlot + 1;

            $keys = $group
                ->slice($sampleSlot * $paramsPerSample, $paramsPerSample)
                ->flatMap(fn (array $line): array => $this->elementIdsFromQuotationLine($line))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($keys !== []) {
                $configs[$index]['parameter_keys'] = $keys;
            }
        }

        return $configs;
    }

    /**
     * @param  list<string>  $keys
     * @param  list<string>  $analysisTypeIds
     * @return list<string>
     */
    public function resolveElementIdsForAnalysisTypes(array $keys, array $analysisTypeIds): array
    {
        if ($analysisTypeIds === []) {
            return $this->resolveElementIdsForAnalysisType($keys, '');
        }

        $resolved = [];
        foreach ($analysisTypeIds as $analysisTypeId) {
            $resolved = array_merge(
                $resolved,
                $this->resolveElementIdsForAnalysisType($keys, (string) $analysisTypeId),
            );
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    public function resolveElementIdsForAnalysisType(array $keys, string $analysisTypeId): array
    {
        return collect($keys)
            ->map(fn (mixed $key): ?string => $this->resolveSingleElementId(trim((string) $key), $analysisTypeId))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $line
     */
    public function resolveQuotationLineElementId(array $line): ?string
    {
        $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
        $candidate = trim((string) ($line['analysis_element_id'] ?? ''));

        if ($candidate === '') {
            return null;
        }

        return $this->resolveSingleElementId($candidate, $analysisTypeId);
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    public function elementIdsFromQuotationLine(array $line): array
    {
        if (! empty($line['is_package']) && is_array($line['package_element_ids'] ?? null)) {
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

            return collect($line['package_element_ids'])
                ->map(fn (mixed $id): ?string => $this->resolveSingleElementId(trim((string) $id), $analysisTypeId))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $resolved = $this->resolveQuotationLineElementId($line);

        return $resolved !== null ? [$resolved] : [];
    }

    /**
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    private function quotationLinesToPrefillLines(array $quotationLines): array
    {
        $paramsPerSampleByKey = [];
        $linesByKey = collect($quotationLines)->groupBy(
            fn (array $line): string => $this->configGroupingKey(
                $line['sample_type_id'] ?? null,
                $line['analysis_type_id'] ?? null,
            )
        );

        foreach ($linesByKey as $key => $group) {
            $uniqueElements = $group
                ->flatMap(fn (array $line): array => $this->elementIdsFromQuotationLine($line))
                ->filter()
                ->unique()
                ->count();
            $paramsPerSampleByKey[$key] = max(1, $uniqueElements);
        }

        $slotByKey = [];
        $prefillLines = [];

        foreach ($quotationLines as $index => $line) {
            $key = $this->configGroupingKey(
                $line['sample_type_id'] ?? null,
                $line['analysis_type_id'] ?? null,
            );
            $paramsPerSample = $paramsPerSampleByKey[$key] ?? 1;
            $slot = $slotByKey[$key] ?? 0;
            $slotByKey[$key] = $slot + 1;

            $prefillLines[] = [
                'line_no' => $index + 1,
                'row_index' => (int) floor($slot / $paramsPerSample),
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_element_id' => $this->resolveQuotationLineElementId($line),
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'number_of_samples' => 1,
                'customer_sample_id' => $line['customer_sample_id'] ?? null,
            ];
        }

        return $prefillLines;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @param  list<array<string, mixed>>  $receptionConfigs
     * @return list<array<string, mixed>>
     */
    private function mergeReceptionFieldsOntoConfigs(array $configs, array $receptionConfigs): array
    {
        foreach ($configs as $index => &$config) {
            $reception = $receptionConfigs[$index] ?? null;
            if ($reception === null) {
                continue;
            }

            $receptionDetails = $this->sampleDetailsFromConfig($reception);

            foreach ([
                'main_standard_id',
                'secondary_standard_id',
                'lab_id',
                'sample_condition_id',
                'zone_id',
                'lab_section_id',
                'sample_code_prefix',
            ] as $field) {
                if (! empty($reception[$field])) {
                    $config[$field] = $reception[$field];
                }
            }

            foreach (['customer_sample_id', 'sample_marking', 'disposal_date', 'photo_path'] as $field) {
                if ($receptionDetails[$field] !== '') {
                    $config[$field] = $receptionDetails[$field];
                }
            }
        }
        unset($config);

        return $configs;
    }

    private function configGroupingKey(?string $sampleTypeId, ?string $analysisTypeId): string
    {
        return (string) ($sampleTypeId ?? '').'::'.(string) ($analysisTypeId ?? '');
    }

    public function resolveSingleElementId(string $candidate, string $analysisTypeId): ?string
    {
        if ($candidate === '') {
            return null;
        }

        if (Str::isUuid($candidate)) {
            $element = AnalysisElements::query()->with('analyte:id,name,code')->find($candidate);
            if ($element === null) {
                return null;
            }

            if ($analysisTypeId === '' || (string) $element->analysis_type_id === $analysisTypeId) {
                return (string) $element->id;
            }

            // Keep the requested analysis type: remap orphan UUIDs from another type
            // onto the same analyte under the config's analysis type.
            $mapped = $this->mapElementToAnalysisType($element, $analysisTypeId);
            if ($mapped !== null) {
                return $mapped;
            }

            return (string) $element->id;
        }

        $normalized = strtolower(trim($candidate));
        $query = AnalysisElements::query()->where('active', 1);
        if ($analysisTypeId !== '') {
            $query->where('analysis_type_id', $analysisTypeId);
        }

        $element = (clone $query)
            ->whereHas('analyte', fn ($analyteQuery) => $analyteQuery->whereRaw('LOWER(name) = ?', [$normalized]))
            ->first();

        if ($element !== null) {
            return (string) $element->id;
        }

        $element = $query
            ->whereHas('analyte', fn ($analyteQuery) => $analyteQuery->whereRaw('LOWER(code) = ?', [$normalized]))
            ->first();

        return $element !== null ? (string) $element->id : null;
    }

    /**
     * @return ?string Mapped analysis_element_id under $analysisTypeId
     */
    private function mapElementToAnalysisType(AnalysisElements $element, string $analysisTypeId): ?string
    {
        $analyteId = trim((string) ($element->analyte_id ?? ''));
        if ($analyteId !== '') {
            $mapped = AnalysisElements::query()
                ->where('analysis_type_id', $analysisTypeId)
                ->where('analyte_id', $analyteId)
                ->where('active', 1)
                ->orderBy('level')
                ->first();
            if ($mapped !== null) {
                return (string) $mapped->id;
            }
        }

        $name = strtolower(trim((string) ($element->analyte?->name ?? '')));
        $code = strtolower(trim((string) ($element->analyte?->code ?? '')));
        if ($name === '' && $code === '') {
            return null;
        }

        $mapped = AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->whereHas('analyte', function ($analyteQuery) use ($name, $code): void {
                $analyteQuery->where(function ($match) use ($name, $code): void {
                    if ($name !== '') {
                        $match->whereRaw('LOWER(name) = ?', [$name]);
                    }
                    if ($code !== '') {
                        $match->orWhereRaw('LOWER(code) = ?', [$code]);
                    }
                });
            })
            ->orderBy('level')
            ->first();

        return $mapped !== null ? (string) $mapped->id : null;
    }

    /**
     * After lab-hierarchy replace imports, portal/walk-in enquiries still hold deleted
     * sample-type / analysis-type / analysis-element UUIDs. Remap them onto the current
     * hierarchy using stored type names and analyte labels so Process Enquiry step 2
     * keeps the same requested tests.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function remapConfigsToCurrentHierarchy(array $configs, SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing('requestedAnalyses');
        $labelHintsByOrphanId = $this->parameterLabelHintsFromEnquiry($enquiry);
        $fallbackLabels = array_values(array_unique(array_filter(array_values($labelHintsByOrphanId))));

        return array_values(array_map(function (array $config) use ($enquiry, $labelHintsByOrphanId, $fallbackLabels): array {
            $config = $this->remapConfigSampleAndAnalysisTypes($config, $enquiry);
            $analysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));

            $resolvedKeys = [];
            foreach (is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [] as $key) {
                $key = trim((string) $key);
                if ($key === '') {
                    continue;
                }

                $resolved = $this->resolveSingleElementId($key, $analysisTypeId);
                if ($resolved !== null) {
                    $resolvedKeys[] = $resolved;

                    continue;
                }

                $label = trim((string) ($labelHintsByOrphanId[$key] ?? ''));
                if ($label === '') {
                    continue;
                }

                $resolved = $this->resolveSingleElementId($label, $analysisTypeId);
                if ($resolved !== null) {
                    $resolvedKeys[] = $resolved;
                }
            }

            if ($resolvedKeys === [] && $fallbackLabels !== []) {
                foreach ($fallbackLabels as $label) {
                    $resolved = $this->resolveSingleElementId($label, $analysisTypeId);
                    if ($resolved !== null) {
                        $resolvedKeys[] = $resolved;
                    }
                }
            }

            $config['parameter_keys'] = array_values(array_unique($resolvedKeys));

            return $config;
        }, $configs));
    }

    /**
     * Merge requested-analysis element ids onto sample configs so Process Enquiry
     * step 2 pre-selects the same parameters shown in step 1.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function applyRequestedParameterKeysFromEnquiry(array $configs, SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing(['requestedAnalyses', 'submissionFormInstance']);

        if ($configs === []) {
            return $configs;
        }

        $trfLines = $this->resolveTrfSampleLines($enquiry);
        $trfIdsByTypeKey = $this->elementIdsByTypeKeyFromSampleLines($trfLines);
        $allTrfIds = $this->flattenElementIdsFromSampleLines($trfLines);

        $byTypeKey = $enquiry->requestedAnalyses->groupBy(
            fn (SampleSubmissionRequestRequestedAnalysis $analysis): string => $this->configGroupingKey(
                $analysis->sample_type_id ? (string) $analysis->sample_type_id : null,
                $analysis->analysis_type_id ? (string) $analysis->analysis_type_id : null,
            )
        );

        $allRequestedIds = $this->resolveElementIdsFromRequestedAnalyses(
            $enquiry->requestedAnalyses->all(),
            '',
        );

        $labelHints = $this->parameterLabelHintsFromEnquiry($enquiry);

        return array_values(array_map(function (array $config) use (
            $byTypeKey,
            $allRequestedIds,
            $allTrfIds,
            $trfIdsByTypeKey,
            $labelHints,
            $configs,
        ): array {
            $existingKeys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '')
                ->values()
                ->all();

            // Preserve intentional lab selections; only fill empty keys from TRF.
            if ($existingKeys !== []) {
                return $config;
            }

            // User explicitly cleared parameters (Deselect all / last toggle off).
            if (! empty($config['suppress_requested_parameter_autofill'])) {
                return $config;
            }

            $config = $this->syncAnalysisTypeIdsOnConfig($config);
            $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
            $sampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));

            $requestedIds = [];
            foreach ($analysisTypeIds as $analysisTypeId) {
                $typeKey = $this->configGroupingKey(
                    $sampleTypeId !== '' ? $sampleTypeId : null,
                    $analysisTypeId,
                );
                $typeIds = $trfIdsByTypeKey[$typeKey] ?? [];
                if ($typeIds !== []) {
                    $requestedIds = array_merge(
                        $requestedIds,
                        $this->resolveElementIdsForAnalysisType($typeIds, $analysisTypeId),
                    );
                }
            }

            if ($requestedIds === [] && count($configs) === 1 && $allTrfIds !== []) {
                $requestedIds = $this->resolveElementIdsForAnalysisTypes($allTrfIds, $analysisTypeIds);
            }

            if ($requestedIds === []) {
                foreach ($analysisTypeIds as $analysisTypeId) {
                    $typeKey = $this->configGroupingKey(
                        $sampleTypeId !== '' ? $sampleTypeId : null,
                        $analysisTypeId,
                    );
                    if (! $byTypeKey->has($typeKey)) {
                        continue;
                    }
                    $requestedIds = array_merge(
                        $requestedIds,
                        $this->resolveElementIdsFromRequestedAnalyses(
                            $byTypeKey->get($typeKey)->all(),
                            $analysisTypeId,
                        ),
                    );
                }
            }

            if ($requestedIds === [] && count($configs) === 1 && $allRequestedIds !== []) {
                $requestedIds = $this->resolveElementIdsForAnalysisTypes($allRequestedIds, $analysisTypeIds);
            }

            if ($requestedIds === []) {
                foreach (array_unique(array_filter(array_values($labelHints))) as $label) {
                    foreach ($analysisTypeIds as $analysisTypeId) {
                        $resolved = $this->resolveSingleElementId($label, $analysisTypeId);
                        if ($resolved !== null) {
                            $requestedIds[] = $resolved;
                            break;
                        }
                    }
                }
            }

            $config['parameter_keys'] = array_values(array_unique($requestedIds));

            return $config;
        }, $configs));
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @return array<string, list<string>>
     */
    private function elementIdsByTypeKeyFromSampleLines(array $sampleLines): array
    {
        $byTypeKey = [];

        foreach ($sampleLines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $ids = $this->extractElementIdsFromSampleLine($line);
            if ($ids === []) {
                continue;
            }

            $analysisTypeIds = $this->analysisTypeIdsFromPrefillLine($line);
            if ($analysisTypeIds === []) {
                $analysisTypeIds = [null];
            }

            foreach ($analysisTypeIds as $analysisTypeId) {
                $typeKey = $this->configGroupingKey(
                    $line['sample_type_id'] ?? null,
                    $analysisTypeId,
                );
                $byTypeKey[$typeKey] = array_values(array_unique(array_merge(
                    $byTypeKey[$typeKey] ?? [],
                    $ids,
                )));
            }
        }

        return $byTypeKey;
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @return list<string>
     */
    private function flattenElementIdsFromSampleLines(array $sampleLines): array
    {
        $ids = [];
        foreach ($sampleLines as $line) {
            if (! is_array($line)) {
                continue;
            }
            $ids = array_merge($ids, $this->extractElementIdsFromSampleLine($line));
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function extractElementIdsFromSampleLine(array $line): array
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $fromAttributes = $attributes['analysis_element_ids'] ?? [];

        if (is_array($fromAttributes) && $fromAttributes !== []) {
            return array_values(array_filter(array_map(
                static fn (mixed $id): string => trim((string) $id),
                $fromAttributes,
            )));
        }

        $elementId = trim((string) ($line['analysis_element_id'] ?? ''));

        return $elementId !== '' ? [$elementId] : [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function remapConfigSampleAndAnalysisTypes(array $config, SampleSubmissionRequest $enquiry): array
    {
        $sampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
        if ($sampleTypeId === '' || SampleType::query()->whereKey($sampleTypeId)->doesntExist()) {
            $sampleTypeName = $this->firstSampleTypeNameHint($enquiry);
            if ($sampleTypeName !== '') {
                $match = SampleType::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($sampleTypeName)])
                    ->first();

                if ($match === null) {
                    $match = SampleType::query()
                        ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($sampleTypeName).'%'])
                        ->orderBy('name')
                        ->first();
                }

                if ($match !== null) {
                    $config['sample_type_id'] = (string) $match->id;
                }
            }
        }

        $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
        $remappedSampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
        $validExisting = [];
        foreach ($analysisTypeIds as $analysisTypeId) {
            if (AnalysisType::query()->whereKey($analysisTypeId)->exists()) {
                $validExisting[] = $analysisTypeId;
            }
        }

        if ($validExisting !== []) {
            return $this->syncAnalysisTypeIdsOnConfig($config, $validExisting);
        }

        $analysisTypeName = $this->firstAnalysisTypeNameHint($enquiry);
        $query = AnalysisType::query();
        if ($remappedSampleTypeId !== '') {
            $query->where('sample_type_id', $remappedSampleTypeId);
        }

        $match = null;
        if ($analysisTypeName !== '') {
            $match = (clone $query)
                ->whereRaw('LOWER(name) = ?', [strtolower($analysisTypeName)])
                ->first();
        }

        if ($match === null && $remappedSampleTypeId !== '') {
            $match = AnalysisType::query()
                ->where('sample_type_id', $remappedSampleTypeId)
                ->orderBy('name')
                ->first();
        }

        if ($match !== null) {
            $config = $this->syncAnalysisTypeIdsOnConfig($config, [(string) $match->id]);
            if (empty($config['lab_section_id'])) {
                $config['lab_section_id'] = $this->resolveLabSectionIdForAnalysisType((string) $match->id);
            }
        }

        return $config;
    }

    /**
     * @return array<string, string> orphanElementId => analyte label
     */
    private function parameterLabelHintsFromEnquiry(SampleSubmissionRequest $enquiry): array
    {
        $hints = [];

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? $analysis->analysis_key ?? ''));
            $label = trim((string) ($analysis->analysis_label ?? ''));
            if ($label === '') {
                continue;
            }

            if (! str_contains($label, ',') && ! Str::isUuid($label)) {
                if ($elementId !== '') {
                    $hints[$elementId] = $label;
                }
                $hints[strtolower($label)] = $label;

                continue;
            }

            foreach (preg_split('/\s*,\s*/', $label) ?: [] as $token) {
                $token = trim((string) $token);
                if ($token === '' || Str::isUuid($token)) {
                    continue;
                }
                $hints[strtolower($token)] = $token;
            }
        }

        foreach ($this->resolveTrfSampleLines($enquiry) as $line) {
            if (! is_array($line)) {
                continue;
            }

            $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $elementIds = $this->extractElementIdsFromSampleLine($line);

            $labelTokens = [];
            foreach ([$line['parameter_label'] ?? null, $attributes['parameters'] ?? null] as $raw) {
                if ($raw === null || $raw === '') {
                    continue;
                }

                foreach (preg_split('/\s*,\s*/', (string) $raw) ?: [] as $token) {
                    $token = trim($token);
                    if ($token === '' || Str::isUuid($token)) {
                        continue;
                    }
                    $labelTokens[] = $token;
                    $hints[strtolower($token)] = $token;
                }
            }

            foreach ($elementIds as $index => $elementId) {
                $elementId = trim((string) $elementId);
                if ($elementId === '' || isset($hints[$elementId])) {
                    continue;
                }

                if (isset($labelTokens[$index])) {
                    $hints[$elementId] = $labelTokens[$index];
                }
            }
        }

        return $hints;
    }

    private function firstSampleTypeNameHint(SampleSubmissionRequest $enquiry): string
    {
        foreach (is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [] as $line) {
            if (! is_array($line)) {
                continue;
            }
            $name = trim((string) ($line['sample_type_name'] ?? ''));
            if ($name !== '') {
                if (str_contains($name, ' — ')) {
                    $name = trim((string) Str::before($name, ' — '));
                }

                return $name;
            }
        }

        $enquiry->loadMissing('submissionFormInstance.submissionForm.sampleTypes');
        $linked = $enquiry->submissionFormInstance?->submissionForm?->sampleTypes?->first();
        if ($linked !== null && trim((string) $linked->name) !== '') {
            return trim((string) $linked->name);
        }

        $formName = trim((string) ($enquiry->submissionFormInstance?->submissionForm?->name ?? ''));
        if (preg_match('/test request form\s*[-–:]\s*(.+)$/i', $formName, $matches) === 1) {
            return trim($matches[1]);
        }

        $documentCode = strtoupper(trim((string) ($enquiry->submissionFormInstance?->submissionForm?->document_code ?? '')));
        if (preg_match('/^TRF-([A-Z0-9]+)/', $documentCode, $matches) === 1) {
            $token = str_replace('_', ' ', $matches[1]);
            if (! in_array($token, ['001', 'GENERAL'], true)) {
                return Str::title(strtolower($token));
            }
        }

        return '';
    }

    private function firstAnalysisTypeNameHint(SampleSubmissionRequest $enquiry): string
    {
        foreach (is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [] as $line) {
            if (! is_array($line)) {
                continue;
            }
            $name = trim((string) ($line['analysis_type_name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function flattenToPerSampleConfigs(array $configs): array
    {
        $flat = [];

        foreach ($configs as $config) {
            $instances = is_array($config['instances'] ?? null) ? $config['instances'] : [];
            $count = max(
                max(1, (int) ($config['number_of_samples'] ?? 1)),
                count($instances),
            );
            $normalizedInstances = $this->syncInstances($instances, $count);
            $rootDetails = $this->sampleDetailsFromConfig($config);

            if ($count <= 1) {
                $flat[] = $this->mergeSampleDetailsIntoConfig($config, $rootDetails);
                $flat[array_key_last($flat)]['number_of_samples'] = 1;

                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                $split = $config;
                $split['id'] = (string) Str::uuid();
                $split['number_of_samples'] = 1;
                $split = $this->mergeSampleDetailsIntoConfig($split, [
                    'customer_sample_id' => $normalizedInstances[$i]['customer_sample_id'] ?? '',
                    'sample_marking' => $normalizedInstances[$i]['sample_marking'] ?? '',
                    'disposal_date' => $normalizedInstances[$i]['disposal_date'] ?? '',
                    'photo_path' => $normalizedInstances[$i]['photo_path'] ?? '',
                ]);
                unset($split['instances']);
                $flat[] = $split;
            }
        }

        return $flat;
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
     * @return list<array{id: string, name: string}>
     */
    public function allSampleTypesForPicker(): array
    {
        return $this->pricingService->allSampleTypesForPicker();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function allAnalysisTypesForPicker(?string $sampleTypeId): array
    {
        return $this->pricingService->allAnalysisTypesForPicker($sampleTypeId);
    }

    /**
     * @param  list<string>  $sampleTypeIds
     * @return list<array{id: string, name: string}>
     */
    public function allAnalysisTypesForSampleTypes(array $sampleTypeIds): array
    {
        $sampleTypeIds = array_values(array_unique(array_filter(array_map(
            static fn (mixed $id): string => trim((string) $id),
            $sampleTypeIds,
        ))));

        if ($sampleTypeIds === []) {
            return [];
        }

        return AnalysisType::query()
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AnalysisType $type): array => [
                'id' => (string) $type->id,
                'name' => trim((string) $type->name),
            ])
            ->values()
            ->all();
    }

    /**
     * A TRF without tied sample types lets the customer pick analysis types across
     * several sample types, so a card can hold selections outside its own sample
     * type's picker list. Append those so they render as names instead of raw ids.
     *
     * @param  list<array{id: string, name: string}>  $analysisTypes
     * @param  list<string>  $selectedIds
     * @return list<array{id: string, name: string}>
     */
    public function withSelectedAnalysisTypes(array $analysisTypes, array $selectedIds): array
    {
        $known = [];
        foreach ($analysisTypes as $type) {
            $known[(string) ($type['id'] ?? '')] = true;
        }

        $missing = [];
        foreach ($selectedIds as $selectedId) {
            $selectedId = trim((string) $selectedId);
            if ($selectedId !== '' && ! isset($known[$selectedId]) && ! in_array($selectedId, $missing, true)) {
                $missing[] = $selectedId;
            }
        }

        if ($missing === []) {
            return $analysisTypes;
        }

        $resolved = AnalysisType::query()
            ->whereIn('id', $missing)
            ->get(['id', 'name'])
            ->mapWithKeys(fn (AnalysisType $type): array => [
                (string) $type->id => trim((string) $type->name),
            ])
            ->all();

        foreach ($missing as $missingId) {
            $name = $resolved[$missingId] ?? '';
            if ($name === '') {
                continue;
            }

            $analysisTypes[] = [
                'id' => $missingId,
                'name' => $name,
            ];
        }

        return array_values($analysisTypes);
    }

    /**
     * @param  string|list<string>|null  $analysisTypeId
     * @return list<array<string, mixed>>
     */
    public function parametersForConfig(string $customerId, ?string $sampleTypeId, string|array|null $analysisTypeId): array
    {
        $analysisTypeIds = is_array($analysisTypeId)
            ? array_values(array_filter(array_map(
                static fn (mixed $id): string => trim((string) $id),
                $analysisTypeId,
            )))
            : (trim((string) ($analysisTypeId ?? '')) !== '' ? [trim((string) $analysisTypeId)] : []);

        if ($analysisTypeIds === []) {
            return [];
        }

        $merged = [];
        $seen = [];

        foreach ($analysisTypeIds as $typeId) {
            $ownerSampleTypeId = trim((string) (
                AnalysisType::query()->whereKey($typeId)->value('sample_type_id') ?? $sampleTypeId
            ));
            $parameters = $this->parametersForSingleAnalysisType(
                $customerId,
                $ownerSampleTypeId !== '' ? $ownerSampleTypeId : $sampleTypeId,
                $typeId,
            );
            foreach ($parameters as $parameter) {
                $key = (string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? '');
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $merged[] = $parameter;
            }
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parametersForSingleAnalysisType(string $customerId, ?string $sampleTypeId, string $analysisTypeId): array
    {
        $fromPricelist = $this->pricingService->parametersForAddLineSelection($customerId, $sampleTypeId, $analysisTypeId);
        $fromElements = $this->analysisElementParameterOptions($sampleTypeId, $analysisTypeId);

        if ($fromPricelist === []) {
            return $fromElements;
        }

        // Prefer pricelist rows (pricing), but keep analysis elements so TRF-selected
        // parameters still appear when they are not on the customer pricelist yet.
        $merged = [];
        $seen = [];

        foreach (array_merge($fromPricelist, $fromElements) as $parameter) {
            $key = (string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? '');
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $parameter;
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function analysisElementParameterOptions(?string $sampleTypeId, string $analysisTypeId): array
    {
        return AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->with(['analyte:id,name,code'])
            ->orderBy('level')
            ->get()
            ->map(function (AnalysisElements $element) use ($analysisTypeId, $sampleTypeId): array {
                $code = trim((string) ($element->analyte?->code ?? ''));
                $label = (string) ($element->analyte?->name ?? $element->method ?? 'Parameter');

                return [
                    'id' => (string) $element->id,
                    'analysis_element_id' => (string) $element->id,
                    'analysis_type_id' => (string) $analysisTypeId,
                    'sample_type_id' => $sampleTypeId,
                    'code' => $code,
                    'label' => $label,
                    'unit_amount' => 0.0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Zones removed from Labs organization — picker returns empty list.
     *
     * @return list<array{id: string, name: string}>
     */
    public function zonesForPicker(): array
    {
        return [];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function labSectionsForPicker(): array
    {
        return SampleAnalysisStage::query()
            ->where('is_sample_stage', 0)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (SampleAnalysisStage $section) => [
                'id' => (string) $section->id,
                'name' => (string) $section->name,
            ])
            ->values()
            ->all();
    }

    public function resolveLabSectionIdForAnalysisType(?string $analysisTypeId): ?string
    {
        if ($analysisTypeId === null || $analysisTypeId === '' || ! Str::isUuid($analysisTypeId)) {
            return null;
        }

        $analysisType = AnalysisType::query()->find($analysisTypeId);
        if ($analysisType === null) {
            return null;
        }

        $candidate = $analysisType->lab_section_id;

        if (empty($candidate)) {
            $candidate = \Illuminate\Support\Facades\DB::table('analysis_elements')
                ->where('analysis_type_id', $analysisType->id)
                ->where('active', 1)
                ->whereNotNull('lab_section_id')
                ->value('lab_section_id');
        }

        if (empty($candidate) || ! Str::isUuid($candidate)) {
            $labId = trim((string) ($analysisType->lab_id ?? ''));
            if ($labId === '') {
                $labId = trim((string) (Lab::defaultLabId() ?? ''));
            }

            if ($labId !== '') {
                $candidate = SampleAnalysisStage::query()
                    ->where('lab_id', $labId)
                    ->where(function ($query): void {
                        $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
                    })
                    ->where('active', true)
                    ->orderBy('name')
                    ->value('id');
            }
        }

        if (empty($candidate) || ! Str::isUuid($candidate)) {
            return null;
        }

        return SampleAnalysisStage::query()->whereKey($candidate)->exists() ? $candidate : null;
    }

    public function resolveLabSectionIdForElement(?string $elementId, ?string $analysisTypeId = null): ?string
    {
        if ($elementId !== null && $elementId !== '' && Str::isUuid($elementId)) {
            $elementSectionId = AnalysisElements::query()
                ->whereKey($elementId)
                ->value('lab_section_id');

            if (! empty($elementSectionId) && Str::isUuid((string) $elementSectionId)) {
                $resolved = (string) $elementSectionId;
                if (SampleAnalysisStage::query()->whereKey($resolved)->exists()) {
                    return $resolved;
                }
            }
        }

        return $this->resolveLabSectionIdForAnalysisType($analysisTypeId);
    }

    /**
     * Normalize a parameter_lab_sections value (legacy string or multi-section list) to UUID list.
     *
     * @return list<string>
     */
    public function normalizeLabSectionIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_filter(
                array_map('strval', $value),
                fn (string $id): bool => $id !== '' && Str::isUuid($id)
            )));
        }

        $id = trim((string) $value);

        return ($id !== '' && Str::isUuid($id)) ? [$id] : [];
    }

    /**
     * @param  mixed  $value
     * @param  list<string>  $parameterKeys
     * @return list<string>
     */
    public function normalizeSubcontractedParameterKeys(mixed $value, array $parameterKeys = []): array
    {
        $ids = [];
        if (is_array($value)) {
            foreach ($value as $raw) {
                $id = trim((string) $raw);
                if ($id !== '' && Str::isUuid($id)) {
                    $ids[] = $id;
                }
            }
        }

        $ids = array_values(array_unique($ids));
        if ($parameterKeys === []) {
            return $ids;
        }

        $allowed = array_flip(array_map('strval', $parameterKeys));

        return array_values(array_filter(
            $ids,
            static fn (string $id): bool => isset($allowed[$id])
        ));
    }

    public function primaryLabSectionId(mixed $value): ?string
    {
        $ids = $this->normalizeLabSectionIds($value);

        return $ids[0] ?? null;
    }

    /**
     * Keep parameter → lab section map aligned with selected parameters.
     * Values are lists of lab section IDs (multi-section per test); legacy strings are accepted.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function syncParameterLabSections(array $config): array
    {
        $parameterKeys = array_values(array_filter(array_map(
            'strval',
            is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : []
        )));
        $config = $this->syncAnalysisTypeIdsOnConfig($config);
        $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
        $primaryAnalysisTypeId = $analysisTypeIds[0] ?? '';
        $existing = is_array($config['parameter_lab_sections'] ?? null)
            ? $config['parameter_lab_sections']
            : [];

        $synced = [];
        foreach ($parameterKeys as $elementId) {
            $currentIds = $this->normalizeLabSectionIds($existing[$elementId] ?? null);
            if ($currentIds !== []) {
                $synced[$elementId] = $currentIds;

                continue;
            }

            $resolved = null;
            foreach ($analysisTypeIds as $analysisTypeId) {
                $resolved = $this->resolveLabSectionIdForElement($elementId, $analysisTypeId);
                if ($resolved !== null) {
                    break;
                }
            }
            $resolved ??= $this->resolveLabSectionIdForElement($elementId, $primaryAnalysisTypeId);
            if ($resolved !== null) {
                $synced[$elementId] = [$resolved];
            }
        }

        $config['parameter_lab_sections'] = $synced;

        $sectionIds = [];
        foreach ($synced as $sectionValue) {
            foreach ($this->normalizeLabSectionIds($sectionValue) as $sectionId) {
                $sectionIds[] = $sectionId;
            }
        }
        $sectionIds = array_values(array_unique($sectionIds));

        $analystsBySection = is_array($config['analysts_by_lab_section'] ?? null)
            ? $config['analysts_by_lab_section']
            : [];

        $pruned = [];
        foreach ($sectionIds as $sectionId) {
            $assigned = array_values(array_unique(array_filter(array_map(
                'strval',
                is_array($analystsBySection[$sectionId] ?? null) ? $analystsBySection[$sectionId] : []
            ))));
            $pruned[$sectionId] = $assigned;
        }
        $config['analysts_by_lab_section'] = $pruned;

        $analystsByElement = is_array($config['analysts_by_element'] ?? null)
            ? $config['analysts_by_element']
            : [];
        $normalizedByElement = [];
        foreach ($parameterKeys as $elementId) {
            if (! array_key_exists($elementId, $analystsByElement)
                || ! is_array($analystsByElement[$elementId])) {
                continue;
            }

            $elementSectionIds = $this->normalizeLabSectionIds($synced[$elementId] ?? null);
            $normalizedByElement[$elementId] = [];
            foreach ($elementSectionIds as $sectionId) {
                $normalizedByElement[$elementId][$sectionId] = array_values(array_unique(array_filter(array_map(
                    'strval',
                    is_array($analystsByElement[$elementId][$sectionId] ?? null)
                        ? $analystsByElement[$elementId][$sectionId]
                        : []
                ))));
            }
        }
        $config['analysts_by_element'] = $normalizedByElement;

        $primaryAnalyst = $this->primaryAssignedAnalystId($config);
        if ($primaryAnalyst !== null) {
            $config['assigned_user_id'] = $primaryAnalyst;
        }

        $config['lab_section_id'] = $sectionIds[0] ?? ($config['lab_section_id'] ?? null);

        return $config;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function syncParameterLabSectionsForConfigs(array $configs): array
    {
        return array_values(array_map(
            fn (array $config): array => $this->syncParameterLabSections($config),
            $configs
        ));
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    public function distinctLabSectionIdsFromConfig(array $config): array
    {
        $config = $this->syncParameterLabSections($config);
        $sections = is_array($config['parameter_lab_sections'] ?? null)
            ? $config['parameter_lab_sections']
            : [];

        $ids = [];
        foreach ($sections as $sectionValue) {
            foreach ($this->normalizeLabSectionIds($sectionValue) as $sectionId) {
                $ids[] = $sectionId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Analysts whose personnel lab section assignment includes the given stage.
     *
     * @return list<array{id: string, name: string}>
     */
    public function analystsForLabSectionPicker(string $labSectionId): array
    {
        if ($labSectionId === '' || ! Str::isUuid($labSectionId)) {
            return [];
        }

        return User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_client', 0)->orWhereNull('is_client');
            })
            ->where(function ($query): void {
                $query->where('is_support_staff', 0)->orWhereNull('is_support_staff');
            })
            ->whereNotNull('lab_section_id')
            ->where('lab_section_id', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'lab_section_id'])
            ->filter(function (User $user) use ($labSectionId): bool {
                $ids = collect(explode(',', (string) $user->lab_section_id))
                    ->map(fn ($id) => trim((string) $id))
                    ->filter()
                    ->all();

                return in_array($labSectionId, $ids, true);
            })
            ->map(fn (User $user) => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function primaryAssignedAnalystId(array $config): ?string
    {
        $bySection = is_array($config['analysts_by_lab_section'] ?? null)
            ? $config['analysts_by_lab_section']
            : [];

        foreach ($bySection as $analystIds) {
            if (! is_array($analystIds)) {
                continue;
            }

            foreach ($analystIds as $analystId) {
                $analystId = (string) $analystId;
                if ($analystId !== '' && Str::isUuid($analystId)) {
                    return $analystId;
                }
            }
        }

        $fallback = (string) ($config['assigned_user_id'] ?? '');

        return $fallback !== '' && Str::isUuid($fallback) ? $fallback : null;
    }

    /**
     * Flatten unique analyst ids across all sample configs.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return list<string>
     */
    public function collectAssignedAnalystIds(array $configs): array
    {
        $ids = [];

        foreach ($configs as $config) {
            $bySection = is_array($config['analysts_by_lab_section'] ?? null)
                ? $config['analysts_by_lab_section']
                : [];

            foreach ($bySection as $analystIds) {
                if (! is_array($analystIds)) {
                    continue;
                }

                foreach ($analystIds as $analystId) {
                    $analystId = (string) $analystId;
                    if ($analystId !== '' && Str::isUuid($analystId)) {
                        $ids[] = $analystId;
                    }
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Build analyst_id => lab_section_ids CSV map for batch approver sync.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return array<string, string>
     */
    public function collectAnalystLabSectionAssignments(array $configs): array
    {
        /** @var array<string, list<string>> $byAnalyst */
        $byAnalyst = [];

        foreach ($configs as $config) {
            $bySection = is_array($config['analysts_by_lab_section'] ?? null)
                ? $config['analysts_by_lab_section']
                : [];

            foreach ($bySection as $sectionId => $analystIds) {
                $sectionId = (string) $sectionId;
                if ($sectionId === '' || ! Str::isUuid($sectionId) || ! is_array($analystIds)) {
                    continue;
                }

                foreach ($analystIds as $analystId) {
                    $analystId = (string) $analystId;
                    if ($analystId === '' || ! Str::isUuid($analystId)) {
                        continue;
                    }

                    $byAnalyst[$analystId] ??= [];
                    if (! in_array($sectionId, $byAnalyst[$analystId], true)) {
                        $byAnalyst[$analystId][] = $sectionId;
                    }
                }
            }
        }

        $mapped = [];
        foreach ($byAnalyst as $analystId => $sectionIds) {
            $mapped[$analystId] = implode(',', $sectionIds);
        }

        return $mapped;
    }

    private function resolveZoneIdFromConfig(array $config): ?string
    {
        return null;
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
     * @return list<array{id: string, name: string}>
     */
    public function labsForPicker(): array
    {
        return Lab::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Lab $lab) => [
                'id' => (string) $lab->id,
                'name' => trim((string) ($lab->name ?? '')),
            ])
            ->all();
    }

    /**
     * Active lab users available for per-sample assignment on Analysis Acceptance.
     *
     * @return list<array{id: string, name: string}>
     */
    public function assignableUsersForPicker(): array
    {
        return User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_client', 0)
                    ->orWhereNull('is_client');
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $instances
     * @return list<array{customer_sample_id: string, sample_marking: string, disposal_date: string, photo_path: string}>
     */
    public function syncInstances(array $instances, int $numberOfSamples): array
    {
        $numberOfSamples = max(1, $numberOfSamples);
        $normalized = [];

        for ($i = 0; $i < $numberOfSamples; $i++) {
            $normalized[] = [
                'customer_sample_id' => trim((string) ($instances[$i]['customer_sample_id'] ?? '')),
                'sample_marking' => trim((string) ($instances[$i]['sample_marking'] ?? '')),
                'disposal_date' => trim((string) ($instances[$i]['disposal_date'] ?? '')),
                'photo_path' => trim((string) ($instances[$i]['photo_path'] ?? '')),
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     *
     * @throws ValidationException
     */
    public function validateConfigs(array $configs, bool $requireLabSection = false): void
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
            $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
            if ($analysisTypeIds === []) {
                $errors["sampleConfigs.{$index}.analysis_type_id"] = "Row {$row}: at least one analysis type is required.";
            }
            if (empty($config['parameter_keys']) || !is_array($config['parameter_keys'])) {
                $errors["sampleConfigs.{$index}.parameter_keys"] = "Row {$row}: select at least one parameter.";
            }
            if ($requireLabSection && empty($config['lab_section_id'])) {
                $errors["sampleConfigs.{$index}.lab_section_id"] = "Row {$row}: lab section is required.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate reception-specific fields for Analysis Acceptance step 1.
     *
     * When `$requireParameterAssignments` is false (Receive Samples handoff), parameter
     * assignments are optional. When `$requireMainStandard` is false, specification is optional.
     *
     * @param  list<array<string, mixed>>  $configs
     *
     * @throws ValidationException
     */
    public function validateReceptionConfigs(
        array $configs,
        bool $requireParameterAssignments = true,
        bool $requireMainStandard = true,
    ): void
    {
        if ($configs === []) {
            throw ValidationException::withMessages([
                'sampleConfigs' => 'Add at least one sample configuration.',
            ]);
        }

        $errors = [];

        foreach ($configs as $index => $config) {
            $row = $index + 1;
            $config = $this->syncParameterLabSections($config);

            if ($requireMainStandard && empty($config['main_standard_id'])) {
                $errors["sampleConfigs.{$index}.main_standard_id"] = "Row {$row}: specification is required.";
            }

            if (! $requireParameterAssignments) {
                continue;
            }

            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            if ($parameterKeys === []) {
                $errors["sampleConfigs.{$index}.parameter_keys"] = "Sample {$row}: select at least one test parameter.";
            }

            // Lab section + analyst assignment is validated on Sample Integrity Check, not here.
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
            $config = $this->syncSampleTypeIdsOnConfig($config);
            $config = $this->syncAnalysisTypeIdsOnConfig($config);
            $sampleTypeId = $config['sample_type_id'] ?? null;
            $sampleTypeIds = $this->sampleTypeIdsFromConfig($config);
            $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $configKey = (string) ($config['id'] ?? Str::uuid());
            $sampleDetails = $this->sampleDetailsFromConfig($config);

            $parameters = $this->parametersForConfig($customerId, $sampleTypeId, $analysisTypeIds);
            $parameterMap = collect($parameters)->keyBy(
                fn (array $parameter): string => (string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? ''),
            );

            foreach ($parameterKeys as $paramKey) {
                $paramKey = (string) $paramKey;
                $parameter = $parameterMap->get($paramKey);
                if ($parameter === null) {
                    $parameter = $this->resolveParameterOptionByElementId($parameters, $paramKey);
                }
                if ($parameter === null) {
                    continue;
                }

                $lineAnalysisTypeId = trim((string) ($parameter['analysis_type_id'] ?? ''));
                if ($lineAnalysisTypeId === '') {
                    $lineAnalysisTypeId = $analysisTypeIds[0] ?? '';
                }
                $lineSampleTypeId = trim((string) ($parameter['sample_type_id'] ?? ''));
                if ($lineSampleTypeId === '' && $lineAnalysisTypeId !== '') {
                    $lineSampleTypeId = trim((string) (
                        AnalysisType::query()->whereKey($lineAnalysisTypeId)->value('sample_type_id') ?? ''
                    ));
                }
                if ($lineSampleTypeId === '') {
                    $lineSampleTypeId = trim((string) $sampleTypeId);
                }

                $lines[] = [
                    'line_no' => count($lines) + 1,
                    'sample_type_id' => $lineSampleTypeId !== '' ? $lineSampleTypeId : null,
                    'sample_type_name' => $parameter['sample_type_name']
                        ?? optional(SampleType::find($lineSampleTypeId))->name
                        ?? '',
                    'analysis_type_id' => $lineAnalysisTypeId,
                    'analysis_type_name' => $parameter['analysis_type_name']
                        ?? optional(AnalysisType::find($lineAnalysisTypeId))->name
                        ?? '',
                    'analysis_element_id' => $parameter['analysis_element_id'] ?? $paramKey,
                    'parameter_label' => (string) ($parameter['label'] ?? 'Parameter'),
                    'unit_amount' => (float) ($parameter['unit_amount'] ?? 0),
                    'number_of_samples' => 1,
                    'is_approved' => true,
                    'sort_order' => $sortOrder++,
                    'acceptance_config_key' => $configKey,
                    'sample_condition_id' => $config['sample_condition_id'] ?? null,
                    'main_standard_id' => $config['main_standard_id'] ?? null,
                    'zone_id' => $this->resolveZoneIdFromConfig($config),
                    'lab_section_id' => null,
                    'customer_sample_id' => $sampleDetails['customer_sample_id'] !== ''
                        ? $sampleDetails['customer_sample_id']
                        : null,
                    'attributes' => count($sampleTypeIds) > 1
                        ? ['sample_type_ids' => $sampleTypeIds]
                        : [],
                ];
            }
        }

        return $this->pricingService->deduplicateRedundantAnalysisTypeLines($lines);
    }

    /**
     * Total physical sample count (one config row = one sample).
     *
     * @param  list<array<string, mixed>>  $configs
     */
    public function totalSampleCount(array $configs): int
    {
        return count($this->flattenToPerSampleConfigs($configs));
    }

    /**
     * Keep only parameter keys that exist for this config's analysis type on the customer pricelist.
     * Maps stale TRF element ids to pricelist keys by matching analyte label when possible.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function reconcileParameterKeysForConfig(array $config, string $customerId): array
    {
        $config = $this->syncAnalysisTypeIdsOnConfig($config);
        $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
        $parameters = $this->parametersForConfig(
            $customerId,
            $config['sample_type_id'] ?? null,
            $analysisTypeIds,
        );

        $selectedKeys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
            ->map(fn (mixed $key): string => trim((string) $key))
            ->filter(fn (string $key): bool => $key !== '');

        if ($parameters === []) {
            $config['parameter_keys'] = $this->resolveElementIdsForAnalysisTypes(
                $selectedKeys->all(),
                $analysisTypeIds,
            );

            return $config;
        }

        $validKeys = collect($parameters)
            ->flatMap(fn (array $parameter): array => array_values(array_filter([
                (string) ($parameter['analysis_element_id'] ?? ''),
                (string) ($parameter['id'] ?? ''),
            ])))
            ->unique()
            ->values();

        if ($analysisTypeIds !== []) {
            $analysisElementIds = AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds)
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id);
            $validKeys = $validKeys->merge($analysisElementIds)->unique()->values();
        }

        $config['parameter_keys'] = $selectedKeys
            ->filter(fn (string $key): bool => $validKeys->contains($key))
            ->unique()
            ->values()
            ->all();

        return $config;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function alignPrefillParameterKeysForConfigs(array $configs, string $customerId): array
    {
        return array_values(array_map(
            fn (array $config): array => $this->alignPrefillParameterKeysForConfig($config, $customerId),
            $configs,
        ));
    }

    /**
     * Map TRF analysis element ids onto pricelist parameter keys when the analyte label matches.
     * TRF-selected analysis elements for the config's analysis type(s) are preserved even when
     * they are not yet on the customer pricelist (Process Enquiry step 2 must keep requested tests).
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function alignPrefillParameterKeysForConfig(array $config, string $customerId): array
    {
        $config = $this->syncAnalysisTypeIdsOnConfig($config);
        $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
        $parameters = $this->parametersForConfig(
            $customerId,
            $config['sample_type_id'] ?? null,
            $analysisTypeIds,
        );

        $validKeys = collect($parameters)
            ->flatMap(fn (array $parameter): array => array_values(array_filter([
                (string) ($parameter['analysis_element_id'] ?? ''),
                (string) ($parameter['id'] ?? ''),
            ])))
            ->unique()
            ->values();

        // Same as reconcile: analysis elements under the selected type(s) stay selectable.
        if ($analysisTypeIds !== []) {
            $analysisElementIds = AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds)
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id);
            $validKeys = $validKeys->merge($analysisElementIds)->unique()->values();
        }

        if ($validKeys->isEmpty()) {
            return $config;
        }

        $parametersByLabel = collect($parameters)->keyBy(
            fn (array $parameter): string => strtolower(trim((string) ($parameter['label'] ?? '')))
        );
        $parametersByCode = collect($parameters)
            ->filter(fn (array $parameter): bool => trim((string) ($parameter['code'] ?? '')) !== '')
            ->keyBy(fn (array $parameter): string => strtolower(trim((string) ($parameter['code'] ?? ''))));

        $aligned = [];

        foreach (is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [] as $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            if ($validKeys->contains($key)) {
                $aligned[] = $key;

                continue;
            }

            if (! Str::isUuid($key)) {
                foreach ($analysisTypeIds as $analysisTypeId) {
                    $resolved = $this->resolveSingleElementId($key, $analysisTypeId);
                    if ($resolved !== null && $validKeys->contains($resolved)) {
                        $aligned[] = $resolved;
                        break;
                    }
                }

                continue;
            }

            $element = AnalysisElements::query()->with('analyte:id,name,code')->find($key);
            $label = strtolower(trim((string) ($element?->analyte?->name ?? '')));
            $code = strtolower(trim((string) ($element?->analyte?->code ?? '')));
            if ($label === '' && $code === '') {
                continue;
            }

            $match = ($label !== '' ? $parametersByLabel->get($label) : null)
                ?? ($code !== '' ? $parametersByCode->get($code) : null);
            if ($match === null) {
                continue;
            }

            $mappedKey = trim((string) ($match['analysis_element_id'] ?? $match['id'] ?? ''));
            if ($mappedKey !== '' && $validKeys->contains($mappedKey)) {
                $aligned[] = $mappedKey;
            }
        }

        $config['parameter_keys'] = collect($aligned)->unique()->values()->all();

        return $config;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function reconcileConfigsParameterKeys(array $configs, string $customerId): array
    {
        return array_values(array_map(
            fn (array $config): array => $this->reconcileParameterKeysForConfig($config, $customerId),
            $configs,
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function normalizeConfigsForStorage(array $configs, ?string $customerId = null): array
    {
        $configs = $this->flattenToPerSampleConfigs($configs);

        return collect($configs)->map(function (array $config) use ($customerId) {
            $config = $this->syncSampleTypeIdsOnConfig($config);
            $config = $this->syncAnalysisTypeIdsOnConfig($config);

            if ($customerId !== null && trim($customerId) !== '') {
                $config = $this->reconcileParameterKeysForConfig($config, $customerId);
            }

            $config = $this->syncParameterLabSections($config);
            $details = $this->sampleDetailsFromConfig($config);

            $parameterLabSections = [];
            foreach (is_array($config['parameter_lab_sections'] ?? null) ? $config['parameter_lab_sections'] : [] as $elementId => $sectionValue) {
                $elementId = (string) $elementId;
                $sectionIds = $this->normalizeLabSectionIds($sectionValue);
                if ($elementId !== '' && Str::isUuid($elementId) && $sectionIds !== []) {
                    $parameterLabSections[$elementId] = $sectionIds;
                }
            }

            $analystsBySection = [];
            foreach (is_array($config['analysts_by_lab_section'] ?? null) ? $config['analysts_by_lab_section'] : [] as $sectionId => $analystIds) {
                $sectionId = (string) $sectionId;
                if ($sectionId === '' || ! Str::isUuid($sectionId) || ! is_array($analystIds)) {
                    continue;
                }

                $analystsBySection[$sectionId] = array_values(array_unique(array_filter(
                    array_map('strval', $analystIds),
                    fn (string $id) => $id !== '' && Str::isUuid($id)
                )));
            }

            $analystsByElement = [];
            foreach (is_array($config['analysts_by_element'] ?? null) ? $config['analysts_by_element'] : [] as $elementId => $sectionAssignments) {
                $elementId = (string) $elementId;
                if ($elementId === '' || ! Str::isUuid($elementId) || ! is_array($sectionAssignments)) {
                    continue;
                }

                $analystsByElement[$elementId] = [];
                foreach ($sectionAssignments as $sectionId => $analystIds) {
                    $sectionId = (string) $sectionId;
                    if ($sectionId === '' || ! Str::isUuid($sectionId) || ! is_array($analystIds)) {
                        continue;
                    }

                    $analystsByElement[$elementId][$sectionId] = array_values(array_unique(array_filter(
                        array_map('strval', $analystIds),
                        fn (string $id) => $id !== '' && Str::isUuid($id)
                    )));
                }
            }

            return [
                'id' => (string) ($config['id'] ?? Str::uuid()),
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'sample_type_ids' => $this->sampleTypeIdsFromConfig($config),
                'allows_multiple_sample_types' => (bool) ($config['allows_multiple_sample_types'] ?? false),
                'analysis_type_id' => $config['analysis_type_id'] ?? null,
                'analysis_type_ids' => $this->analysisTypeIdsFromConfig($config),
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'main_standard_id' => $config['main_standard_id'] ?? null,
                'secondary_standard_id' => $config['secondary_standard_id'] ?? null,
                'zone_id' => $this->resolveZoneIdFromConfig($config),
                'lab_section_id' => ! empty($config['lab_section_id']) ? (string) $config['lab_section_id'] : null,
                'lab_id' => ! empty($config['lab_id']) ? (string) $config['lab_id'] : null,
                'assigned_user_id' => $this->primaryAssignedAnalystId($config),
                'row_index' => isset($config['row_index']) ? (int) $config['row_index'] : null,
                'number_of_samples' => 1,
                'parameter_keys' => array_values(array_map('strval', $config['parameter_keys'] ?? [])),
                'parameter_lab_sections' => $parameterLabSections,
                'subcontracted_parameter_keys' => $this->normalizeSubcontractedParameterKeys(
                    $config['subcontracted_parameter_keys'] ?? [],
                    array_values(array_map('strval', $config['parameter_keys'] ?? [])),
                ),
                'analysts_by_lab_section' => $analystsBySection,
                'analysts_by_element' => $analystsByElement,
                'sample_code_prefix' => $config['sample_code_prefix'] ?? null,
                'customer_sample_id' => $details['customer_sample_id'],
                'sample_marking' => $details['sample_marking'],
                'disposal_date' => $details['disposal_date'],
                'photo_path' => $details['photo_path'],
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
     *     secondary_standard_id: ?string,
     *     lab_id: ?string,
     *     zone_id: ?string,
     *     lab_section_id: ?string,
     *     assigned_user_id: ?string,
     *     parameter_lab_sections: array<string, string>,
     *     analysts_by_lab_section: array<string, list<string>>,
     *     analysts_by_element: array<string, array<string, list<string>>>,
     *     customer_sample_id: ?string,
     *     sample_marking: ?string,
     *     disposal_date: ?string,
     *     photo_path: ?string
     * }>
     */
    public function buildDetailPlansFromConfigs(array $configs): array
    {
        $plans = [];
        $configs = $this->flattenToPerSampleConfigs($configs);

        foreach ($configs as $config) {
            $config = $this->syncAnalysisTypeIdsOnConfig($config);
            $analysisTypeIds = $this->analysisTypeIdsFromConfig($config);
            if ($analysisTypeIds === []) {
                continue;
            }

            $config = $this->syncParameterLabSections($config);
            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $details = $this->sampleDetailsFromConfig($config);
            $elementIds = $this->resolveElementIdsForAnalysisTypes($parameterKeys, $analysisTypeIds);
            $parameterLabSections = is_array($config['parameter_lab_sections'] ?? null)
                ? $config['parameter_lab_sections']
                : [];
            $analystsBySection = is_array($config['analysts_by_lab_section'] ?? null)
                ? $config['analysts_by_lab_section']
                : [];
            $analystsByElement = is_array($config['analysts_by_element'] ?? null)
                ? $config['analysts_by_element']
                : [];

            // Captured-result creation still expects one primary section per element.
            $primaryLabSections = [];
            foreach ($parameterLabSections as $elementId => $sectionValue) {
                $primary = $this->primaryLabSectionId($sectionValue);
                if ($primary !== null) {
                    $primaryLabSections[(string) $elementId] = $primary;
                }
            }

            $plans[] = [
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'analysis_type_ids' => $analysisTypeIds,
                'analysis_element_ids' => $elementIds,
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'main_standard_id' => $config['main_standard_id'] ?? null,
                'secondary_standard_id' => $config['secondary_standard_id'] ?? null,
                'lab_id' => ! empty($config['lab_id'])
                    ? (string) $config['lab_id']
                    : Lab::defaultLabId(),
                'zone_id' => $this->resolveZoneIdFromConfig($config),
                'lab_section_id' => ! empty($config['lab_section_id'])
                    ? (string) $config['lab_section_id']
                    : null,
                'assigned_user_id' => $this->primaryAssignedAnalystId($config),
                'parameter_lab_sections' => $primaryLabSections,
                'parameter_lab_section_ids' => $parameterLabSections,
                'analysts_by_lab_section' => $analystsBySection,
                'analysts_by_element' => $analystsByElement,
                'customer_sample_id' => $details['customer_sample_id'] !== ''
                    ? $details['customer_sample_id']
                    : null,
                'sample_marking' => $details['sample_marking'] !== ''
                    ? $details['sample_marking']
                    : null,
                'disposal_date' => $details['disposal_date'] !== ''
                    ? $details['disposal_date']
                    : null,
                'photo_path' => $details['photo_path'] !== ''
                    ? $details['photo_path']
                    : null,
                'sample_code_prefix' => $config['sample_code_prefix'] ?? null,
            ];
        }

        return $plans;
    }

    /**
     * @param  list<array<string, mixed>>  $parameters
     * @return array<string, mixed>|null
     */
    private function resolveParameterOptionByElementId(array $parameters, string $elementId): ?array
    {
        if ($elementId === '' || ! Str::isUuid($elementId)) {
            return null;
        }

        foreach ($parameters as $parameter) {
            $candidate = (string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? '');
            if ($candidate === $elementId) {
                return $parameter;
            }
        }

        $element = AnalysisElements::query()->with('analyte:id,name,code')->find($elementId);
        $label = strtolower(trim((string) ($element?->analyte?->name ?? '')));
        $code = strtolower(trim((string) ($element?->analyte?->code ?? '')));
        $analyteId = trim((string) ($element?->analyte_id ?? ''));
        if ($label === '' && $code === '' && $analyteId === '') {
            return null;
        }

        $parameterElementIds = collect($parameters)
            ->map(fn (array $parameter): string => trim((string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? '')))
            ->filter(fn (string $id): bool => $id !== '' && Str::isUuid($id))
            ->unique()
            ->values()
            ->all();

        $analyteIdsByElement = $parameterElementIds === []
            ? collect()
            : AnalysisElements::query()
                ->whereIn('id', $parameterElementIds)
                ->pluck('analyte_id', 'id')
                ->map(fn (mixed $id): string => trim((string) $id));

        foreach ($parameters as $parameter) {
            $parameterElementId = trim((string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? ''));
            if ($analyteId !== '' && $parameterElementId !== '') {
                $parameterAnalyteId = (string) ($analyteIdsByElement[$parameterElementId] ?? '');
                if ($parameterAnalyteId !== '' && $parameterAnalyteId === $analyteId) {
                    return $parameter;
                }
            }

            if ($label !== '' && strtolower(trim((string) ($parameter['label'] ?? ''))) === $label) {
                return $parameter;
            }

            if ($code !== '' && strtolower(trim((string) ($parameter['code'] ?? ''))) === $code) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    private function buildPrefillLinesFromTrfSampleLines(
        SampleSubmissionRequest $enquiry,
        array $sampleLines,
        array $quotationLines,
    ): array {
        $enquiry->loadMissing('requestedAnalyses');
        $parametersByTypeKey = $enquiry->requestedAnalyses->groupBy(
            fn ($analysis): string => (string) ($analysis->sample_type_id ?? '').'::'.(string) ($analysis->analysis_type_id ?? ''),
        );

        $prefill = [];

        foreach (array_values($sampleLines) as $index => $line) {
            $rowIndex = array_key_exists('sort_order', $line)
                ? (int) $line['sort_order']
                : (int) ($line['row_index'] ?? $index);
            $sampleTypeId = $line['sample_type_id'] ?? $enquiry->sample_type_id ?? null;
            $analysisTypeIds = $this->analysisTypeIdsFromPrefillLine($line);
            if ($analysisTypeIds === [] && ! empty($enquiry->matrix_id)) {
                $analysisTypeIds = [(string) $enquiry->matrix_id];
            }
            $analysisTypeId = $analysisTypeIds[0] ?? null;
            $typeKey = (string) ($sampleTypeId ?? '').'::'.(string) ($analysisTypeId ?? '');
            $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $elementIds = is_array($attributes['analysis_element_ids'] ?? null)
                ? array_values(array_filter(array_map('strval', $attributes['analysis_element_ids'])))
                : [];

            if ($elementIds === [] && $parametersByTypeKey->has($typeKey)) {
                $elementIds = $this->resolveElementIdsFromRequestedAnalyses(
                    $parametersByTypeKey->get($typeKey)->all(),
                    (string) ($analysisTypeId ?? ''),
                );
            }

            // When the TRF row carries multiple analysis types, also collect parameters
            // keyed under each analysis type.
            if ($elementIds === [] && count($analysisTypeIds) > 1) {
                foreach ($analysisTypeIds as $typeId) {
                    $multiKey = (string) ($sampleTypeId ?? '').'::'.$typeId;
                    if (! $parametersByTypeKey->has($multiKey)) {
                        continue;
                    }
                    $elementIds = array_merge(
                        $elementIds,
                        $this->resolveElementIdsFromRequestedAnalyses(
                            $parametersByTypeKey->get($multiKey)->all(),
                            $typeId,
                        ),
                    );
                }
                $elementIds = array_values(array_unique($elementIds));
            }

            if ($elementIds === [] && count($sampleLines) === 1 && $enquiry->requestedAnalyses->isNotEmpty()) {
                $elementIds = $this->resolveElementIdsFromRequestedAnalyses(
                    $enquiry->requestedAnalyses->all(),
                    (string) ($analysisTypeId ?? ''),
                );
            }

            if ($elementIds === [] && count($sampleLines) === 1 && $quotationLines !== []) {
                $elementIds = collect($quotationLines)
                    ->pluck('analysis_element_id')
                    ->filter()
                    ->map(fn ($id): string => (string) $id)
                    ->unique()
                    ->values()
                    ->all();
            }

            if ($elementIds !== []) {
                $attributes['analysis_element_ids'] = $elementIds;
            }
            if (count($analysisTypeIds) > 1) {
                $attributes['analysis_type_ids'] = $analysisTypeIds;
            }

            $prefill[] = [
                'row_index' => $rowIndex,
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $elementIds[0] ?? $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? 'Parameter',
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? $enquiry->number_of_samples ?? 1)),
                'customer_sample_id' => $line['sample_id'] ?? $line['customer_sample_id'] ?? null,
                'sample_condition' => $line['sample_condition'] ?? null,
                'sample_condition_id' => $line['sample_condition_id'] ?? null,
                'attributes' => $attributes,
            ];
        }

        return $prefill;
    }

    /**
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    private function buildPrefillLinesFromQuotationLines(
        SampleSubmissionRequest $enquiry,
        array $quotationLines,
    ): array {
        $physicalSampleCount = max(1, (int) ($enquiry->number_of_samples ?? 1));

        if ($physicalSampleCount === 1 && count($quotationLines) > 1) {
            $first = $quotationLines[0];
            $elementIds = collect($quotationLines)
                ->pluck('analysis_element_id')
                ->filter()
                ->map(fn ($id): string => (string) $id)
                ->unique()
                ->values()
                ->all();
            $analysisTypeIds = collect($quotationLines)
                ->pluck('analysis_type_id')
                ->filter()
                ->map(fn ($id): string => (string) $id)
                ->unique()
                ->values()
                ->all();

            $attributes = [];
            if ($elementIds !== []) {
                $attributes['analysis_element_ids'] = $elementIds;
            }
            if (count($analysisTypeIds) > 1) {
                $attributes['analysis_type_ids'] = $analysisTypeIds;
            }

            return [[
                'row_index' => (int) ($first['row_index'] ?? 0),
                'sample_type_id' => $first['sample_type_id'] ?? null,
                'analysis_type_id' => $analysisTypeIds[0] ?? ($first['analysis_type_id'] ?? null),
                'analysis_element_id' => $elementIds[0] ?? null,
                'parameter_label' => $first['parameter_label'] ?? 'Parameter',
                'number_of_samples' => 1,
                'customer_sample_id' => $first['customer_sample_id'] ?? null,
                'sample_condition' => $first['sample_condition'] ?? null,
                'sample_condition_id' => $first['sample_condition_id'] ?? null,
                'attributes' => $attributes,
            ]];
        }

        return collect($quotationLines)->map(function (array $line, int $index): array {
            return [
                'row_index' => array_key_exists('row_index', $line) ? (int) $line['row_index'] : $index,
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? 'Parameter',
                'number_of_samples' => max(1, (int) ($line['quantity'] ?? $line['number_of_samples'] ?? 1)),
                'customer_sample_id' => $line['customer_sample_id'] ?? null,
                'sample_condition' => $line['sample_condition'] ?? null,
                'sample_condition_id' => $line['sample_condition_id'] ?? null,
                'attributes' => is_array($line['attributes'] ?? null) ? $line['attributes'] : [],
            ];
        })->all();
    }

    /**
     * One sample line = one physical sample.
     * Qty / unit (`sample_quantity`) must not inflate sample count.
     *
     * @param  array<string, mixed>  $line
     */
    private function resolvePrefillLineNumberOfSamples(array $line): int
    {
        if (isset($line['number_of_samples']) && is_numeric($line['number_of_samples'])) {
            return max(1, (int) $line['number_of_samples']);
        }

        unset($line);

        return 1;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function elementIdsFromPrefillLine(array $line): array
    {
        $ids = [];
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $fromAttributes = $attributes['analysis_element_ids'] ?? [];

        if (is_array($fromAttributes)) {
            foreach ($fromAttributes as $elementId) {
                $elementId = trim((string) $elementId);
                if ($elementId !== '') {
                    $ids[] = $elementId;
                }
            }
        }

        $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
        if ($elementId !== '') {
            array_unshift($ids, $elementId);
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  list<array<string, mixed>>  $bucketedConfigs
     * @return list<array<string, mixed>>
     */
    private function explodeBucketedConfigsToPerSample(array $bucketedConfigs): array
    {
        $configs = [];

        foreach ($bucketedConfigs as $config) {
            $instances = is_array($config['instances'] ?? null) ? $config['instances'] : [];
            $count = max(
                max(1, (int) ($config['number_of_samples'] ?? 1)),
                count($instances),
            );
            $normalizedInstances = $this->syncInstances($instances, $count);
            $rootDetails = $this->sampleDetailsFromConfig($config);

            if ($count <= 1) {
                $merged = $this->mergeSampleDetailsIntoConfig($config, $rootDetails);
                $merged['number_of_samples'] = 1;
                unset($merged['instances']);
                $configs[] = $merged;

                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                $split = $config;
                $split['id'] = (string) Str::uuid();
                $split['number_of_samples'] = 1;
                $split = $this->mergeSampleDetailsIntoConfig($split, [
                    'customer_sample_id' => $normalizedInstances[$i]['customer_sample_id'] ?? '',
                    'sample_marking' => $normalizedInstances[$i]['sample_marking'] ?? '',
                    'disposal_date' => $normalizedInstances[$i]['disposal_date'] ?? '',
                    'photo_path' => $normalizedInstances[$i]['photo_path'] ?? '',
                ]);
                unset($split['instances']);
                $configs[] = $split;
            }
        }

        foreach ($configs as $index => &$config) {
            $config['row_index'] = $index;
        }
        unset($config);

        return $configs;
    }

    /**
     * @param  list<SampleSubmissionRequestRequestedAnalysis>  $analyses
     * @return list<string>
     */
    private function resolveElementIdsFromRequestedAnalyses(array $analyses, string $analysisTypeId): array
    {
        $ids = [];

        foreach ($analyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? ''));
            if ($elementId === '') {
                $elementId = trim((string) ($analysis->analysis_key ?? ''));
            }

            if ($elementId !== '') {
                $resolved = $this->resolveSingleElementId($elementId, $analysisTypeId);
                if ($resolved !== null) {
                    $ids[] = $resolved;

                    continue;
                }
            }

            $label = trim((string) ($analysis->analysis_label ?? ''));
            if ($label === '') {
                continue;
            }

            foreach (preg_split('/\s*,\s*/', $label) ?: [] as $token) {
                $token = trim((string) $token);
                if ($token === '') {
                    continue;
                }

                $resolved = $this->resolveSingleElementId($token, $analysisTypeId);
                if ($resolved !== null) {
                    $ids[] = $resolved;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolvePhysicalSampleKey(array $line, int $sequentialIndex): string
    {
        if (array_key_exists('row_index', $line) && $line['row_index'] !== null && $line['row_index'] !== '') {
            return 'row:'.(int) $line['row_index'];
        }

        if (isset($line['line_no']) && $line['line_no'] !== null && $line['line_no'] !== '') {
            return 'line:'.(int) $line['line_no'];
        }

        $customerSampleId = trim((string) ($line['customer_sample_id'] ?? ''));
        if ($customerSampleId !== '') {
            return 'cust:'.$customerSampleId;
        }

        return 'seq:'.$sequentialIndex;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{customer_sample_id: string, sample_marking: string, disposal_date: string, photo_path: string}
     */
    public function sampleDetailsFromConfig(array $config): array
    {
        $details = [
            'customer_sample_id' => trim((string) ($config['customer_sample_id'] ?? '')),
            'sample_marking' => trim((string) ($config['sample_marking'] ?? '')),
            'disposal_date' => trim((string) ($config['disposal_date'] ?? '')),
            'photo_path' => trim((string) ($config['photo_path'] ?? '')),
        ];

        $instances = is_array($config['instances'] ?? null) ? $config['instances'] : [];
        if ($instances === []) {
            return $details;
        }

        foreach (['customer_sample_id', 'sample_marking', 'disposal_date', 'photo_path'] as $field) {
            if ($details[$field] !== '') {
                continue;
            }

            $legacyValue = trim((string) ($instances[0][$field] ?? ''));
            if ($legacyValue !== '') {
                $details[$field] = $legacyValue;
            }
        }

        return $details;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array{customer_sample_id: string, sample_marking: string, disposal_date: string, photo_path: string}  $details
     * @return array<string, mixed>
     */
    private function mergeSampleDetailsIntoConfig(array $config, array $details): array
    {
        $config['customer_sample_id'] = $details['customer_sample_id'];
        $config['sample_marking'] = $details['sample_marking'];
        $config['disposal_date'] = $details['disposal_date'];
        $config['photo_path'] = $details['photo_path'];
        unset($config['instances']);

        return $config;
    }
}
