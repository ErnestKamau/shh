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
            'analysis_type_id' => null,
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
            'sample_code_prefix' => null,
            'customer_sample_id' => '',
            'sample_marking' => '',
            'disposal_date' => '',
            'photo_path' => '',
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

        foreach ($prefillLines as $lineIndex => $line) {
            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $key = $this->resolvePhysicalSampleKey($line, $lineIndex);

            $lineSampleCount = $this->resolvePrefillLineNumberOfSamples($line);

            if (! isset($buckets[$key])) {
                $buckets[$key] = $this->emptyConfig();
                $buckets[$key]['sample_type_id'] = $sampleTypeId !== '' ? $sampleTypeId : null;
                $buckets[$key]['analysis_type_id'] = $analysisTypeId !== '' ? $analysisTypeId : null;
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
            $configs[$index]['parameter_keys'] = $this->resolveElementIdsForAnalysisType(
                is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [],
                (string) ($config['analysis_type_id'] ?? ''),
            );
            $configs[$index]['number_of_samples'] = 1;

            if (empty($config['zone_id']) && $defaultZoneId !== null) {
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
            $key = $this->configGroupingKey(
                $config['sample_type_id'] ?? null,
                $config['analysis_type_id'] ?? null,
            );
            $group = $linesByKey->get($key, collect())->values();

            if ($group->isEmpty()) {
                continue;
            }

            $configsWithSameKey = collect($configs)->filter(
                fn (array $candidate): bool => $this->configGroupingKey(
                    $candidate['sample_type_id'] ?? null,
                    $candidate['analysis_type_id'] ?? null,
                ) === $key
            )->count();

            $paramsPerSample = (int) max(1, (int) floor($group->count() / max(1, $configsWithSameKey)));
            $sampleSlot = $sampleSlotByKey[$key] ?? 0;
            $sampleSlotByKey[$key] = $sampleSlot + 1;

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
            $exists = AnalysisElements::query()->whereKey($candidate)->exists();
            if ($exists) {
                return $candidate;
            }

            return null;
        }

        $query = AnalysisElements::query()->where('active', 1);
        if ($analysisTypeId !== '') {
            $query->where('analysis_type_id', $analysisTypeId);
        }

        $element = $query
            ->whereHas('analyte', fn ($analyteQuery) => $analyteQuery->whereRaw('LOWER(name) = ?', [strtolower($candidate)]))
            ->first();

        if ($element !== null) {
            return (string) $element->id;
        }

        return null;
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

            $analysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));
            $typeKey = $this->configGroupingKey(
                $config['sample_type_id'] ?? null,
                $config['analysis_type_id'] ?? null,
            );

            $requestedIds = $trfIdsByTypeKey[$typeKey] ?? [];
            if ($requestedIds !== []) {
                $requestedIds = $this->resolveElementIdsForAnalysisType($requestedIds, $analysisTypeId);
            }

            if ($requestedIds === [] && count($configs) === 1 && $allTrfIds !== []) {
                $requestedIds = $this->resolveElementIdsForAnalysisType($allTrfIds, $analysisTypeId);
            }

            if ($requestedIds === [] && $byTypeKey->has($typeKey)) {
                $requestedIds = $this->resolveElementIdsFromRequestedAnalyses(
                    $byTypeKey->get($typeKey)->all(),
                    $analysisTypeId,
                );
            }

            if ($requestedIds === [] && count($configs) === 1 && $allRequestedIds !== []) {
                $requestedIds = $this->resolveElementIdsForAnalysisType($allRequestedIds, $analysisTypeId);
            }

            if ($requestedIds === []) {
                foreach (array_unique(array_filter(array_values($labelHints))) as $label) {
                    $resolved = $this->resolveSingleElementId($label, $analysisTypeId);
                    if ($resolved !== null) {
                        $requestedIds[] = $resolved;
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

            $typeKey = $this->configGroupingKey(
                $line['sample_type_id'] ?? null,
                $line['analysis_type_id'] ?? null,
            );
            $ids = $this->extractElementIdsFromSampleLine($line);
            if ($ids === []) {
                continue;
            }

            $byTypeKey[$typeKey] = array_values(array_unique(array_merge(
                $byTypeKey[$typeKey] ?? [],
                $ids,
            )));
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

        $analysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));
        $remappedSampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
        if ($analysisTypeId === '' || AnalysisType::query()->whereKey($analysisTypeId)->doesntExist()) {
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
                $config['analysis_type_id'] = (string) $match->id;
                if (empty($config['lab_section_id'])) {
                    $config['lab_section_id'] = $this->resolveLabSectionIdForAnalysisType((string) $match->id);
                }
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
                'name' => trim($section->name.($section->code ? ' — '.$section->code : '')),
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
            return null;
        }

        return SampleAnalysisStage::query()->whereKey($candidate)->exists() ? $candidate : null;
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
                'name' => trim((string) ($lab->code ?? '').' - '.($lab->name ?? ''), ' -'),
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
            if (empty($config['analysis_type_id'])) {
                $errors["sampleConfigs.{$index}.analysis_type_id"] = "Row {$row}: analysis type is required.";
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
     * @param  list<array<string, mixed>>  $configs
     *
     * @throws ValidationException
     */
    public function validateReceptionConfigs(array $configs): void
    {
        if ($configs === []) {
            throw ValidationException::withMessages([
                'sampleConfigs' => 'Add at least one sample configuration.',
            ]);
        }

        $errors = [];

        foreach ($configs as $index => $config) {
            $row = $index + 1;
            if (empty($config['main_standard_id'])) {
                $errors["sampleConfigs.{$index}.main_standard_id"] = "Row {$row}: main standard is required.";
            }
            if (empty($config['assigned_user_id'])) {
                $errors["sampleConfigs.{$index}.assigned_user_id"] = "Row {$row}: assigned user is required.";
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
            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $configKey = (string) ($config['id'] ?? Str::uuid());
            $sampleDetails = $this->sampleDetailsFromConfig($config);

            $parameters = $this->parametersForConfig($customerId, $sampleTypeId, $analysisTypeId);
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
        $parameters = $this->parametersForConfig(
            $customerId,
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id'] ?? null,
        );

        $selectedKeys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
            ->map(fn (mixed $key): string => trim((string) $key))
            ->filter(fn (string $key): bool => $key !== '');

        if ($parameters === []) {
            $config['parameter_keys'] = $this->resolveElementIdsForAnalysisType(
                $selectedKeys->all(),
                (string) ($config['analysis_type_id'] ?? ''),
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

        $analysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));
        if ($analysisTypeId !== '') {
            $analysisElementIds = AnalysisElements::query()
                ->where('analysis_type_id', $analysisTypeId)
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
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function alignPrefillParameterKeysForConfig(array $config, string $customerId): array
    {
        $parameters = $this->parametersForConfig(
            $customerId,
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id'] ?? null,
        );

        if ($parameters === []) {
            return $config;
        }

        $validKeys = collect($parameters)
            ->flatMap(fn (array $parameter): array => array_values(array_filter([
                (string) ($parameter['analysis_element_id'] ?? ''),
                (string) ($parameter['id'] ?? ''),
            ])))
            ->unique()
            ->values();

        $parametersByLabel = collect($parameters)->keyBy(
            fn (array $parameter): string => strtolower(trim((string) ($parameter['label'] ?? '')))
        );

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
                continue;
            }

            $element = AnalysisElements::query()->with('analyte:id,name')->find($key);
            $label = strtolower(trim((string) ($element?->analyte?->name ?? '')));
            if ($label === '') {
                continue;
            }

            $match = $parametersByLabel->get($label);
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
            if ($customerId !== null && trim($customerId) !== '') {
                $config = $this->reconcileParameterKeysForConfig($config, $customerId);
            }

            $details = $this->sampleDetailsFromConfig($config);

            return [
                'id' => (string) ($config['id'] ?? Str::uuid()),
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'analysis_type_id' => $config['analysis_type_id'] ?? null,
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'main_standard_id' => $config['main_standard_id'] ?? null,
                'secondary_standard_id' => $config['secondary_standard_id'] ?? null,
                'zone_id' => $this->resolveZoneIdFromConfig($config),
                'lab_section_id' => ! empty($config['lab_section_id']) ? (string) $config['lab_section_id'] : null,
                'lab_id' => ! empty($config['lab_id']) ? (string) $config['lab_id'] : null,
                'assigned_user_id' => ! empty($config['assigned_user_id']) ? (string) $config['assigned_user_id'] : null,
                'row_index' => isset($config['row_index']) ? (int) $config['row_index'] : null,
                'number_of_samples' => 1,
                'parameter_keys' => array_values(array_map('strval', $config['parameter_keys'] ?? [])),
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
            $analysisTypeId = (string) ($config['analysis_type_id'] ?? '');
            if ($analysisTypeId === '') {
                continue;
            }

            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $details = $this->sampleDetailsFromConfig($config);
            $elementIds = $this->resolveElementIdsForAnalysisType($parameterKeys, $analysisTypeId);

            $plans[] = [
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'analysis_type_ids' => [$analysisTypeId],
                'analysis_element_ids' => $elementIds,
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'main_standard_id' => $config['main_standard_id'] ?? null,
                'secondary_standard_id' => $config['secondary_standard_id'] ?? null,
                'lab_id' => ! empty($config['lab_id'])
                    ? (string) $config['lab_id']
                    : Lab::defaultLabId(),
                'zone_id' => $this->resolveZoneIdFromConfig($config),
                'lab_section_id' => null,
                'assigned_user_id' => ! empty($config['assigned_user_id']) ? (string) $config['assigned_user_id'] : null,
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

        $element = AnalysisElements::query()->with('analyte:id,name')->find($elementId);
        $label = strtolower(trim((string) ($element?->analyte?->name ?? '')));
        if ($label === '') {
            return null;
        }

        foreach ($parameters as $parameter) {
            if (strtolower(trim((string) ($parameter['label'] ?? ''))) === $label) {
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
            $analysisTypeId = $line['analysis_type_id'] ?? $enquiry->matrix_id ?? null;
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

            return [[
                'row_index' => (int) ($first['row_index'] ?? 0),
                'sample_type_id' => $first['sample_type_id'] ?? null,
                'analysis_type_id' => $first['analysis_type_id'] ?? null,
                'analysis_element_id' => $elementIds[0] ?? null,
                'parameter_label' => $first['parameter_label'] ?? 'Parameter',
                'number_of_samples' => 1,
                'customer_sample_id' => $first['customer_sample_id'] ?? null,
                'sample_condition' => $first['sample_condition'] ?? null,
                'sample_condition_id' => $first['sample_condition_id'] ?? null,
                'attributes' => $elementIds !== [] ? ['analysis_element_ids' => $elementIds] : [],
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
     * @param  array<string, mixed>  $line
     */
    private function resolvePrefillLineNumberOfSamples(array $line): int
    {
        if (isset($line['number_of_samples']) && is_numeric($line['number_of_samples'])) {
            return max(1, (int) $line['number_of_samples']);
        }

        if (isset($line['quantity']) && is_numeric($line['quantity'])) {
            return max(1, (int) $line['quantity']);
        }

        $sampleQuantity = trim((string) ($line['sample_quantity'] ?? ''));
        if ($sampleQuantity !== '' && is_numeric($sampleQuantity)) {
            return max(1, (int) $sampleQuantity);
        }

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
