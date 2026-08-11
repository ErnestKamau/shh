<?php

namespace App\Imports\Lab;

use App\AnalysisMethod;
use App\AnalysisType;
use App\Imports\BaseImporter;
use App\Lab;
use App\SampleType;
use App\Services\Lab\MethodConfigurationResolver;

class AnalysisTypeImporter extends BaseImporter
{
    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch, $selectedZoneId);

        app(MethodConfigurationResolver::class)->ensurePointerConfigurations();
    }

    /**
     * Spreadsheets often leave Analysis Type blank for method-level rows under a
     * sample type (e.g. Swab / Food & Feed). Use the sample type name so those
     * rows still create an Analysis Type instead of being silently skipped.
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = parent::normalizeRow($row);

        $sampleType = trim((string) ($normalized['sample_type']
            ?? $normalized['sample_type_name']
            ?? $normalized['matrix_name']
            ?? $normalized['matrix']
            ?? $normalized['sample_name']
            ?? ''));
        $sampleTypeCode = trim((string) ($normalized['sample_type_code']
            ?? $normalized['matrix_code']
            ?? $normalized['sample_code']
            ?? ''));

        $analysisType = trim((string) ($normalized['analysis_type']
            ?? $normalized['analysis_type_name']
            ?? $normalized['at_name']
            ?? $normalized['name']
            ?? ''));
        $analysisTypeCode = trim((string) ($normalized['analysis_type_code']
            ?? $normalized['at_code']
            ?? $normalized['code']
            ?? ''));

        if ($analysisType === '' && $analysisTypeCode === '') {
            $fallback = $sampleType !== '' ? $sampleType : $sampleTypeCode;
            if ($fallback !== '') {
                $normalized['analysis_type'] = $fallback;
            }
        }

        return $normalized;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        [$sampleTypeCode, $sampleTypeName, $analysisTypeCode, $analysisTypeName, $labCode] = $this->extractHierarchyFields($row);

        if ($analysisTypeCode === '' && $analysisTypeName === '') {
            $errors[] = 'Analysis type code or name is required';
        }

        if ($sampleTypeCode === '' && $sampleTypeName === '') {
            $errors[] = 'Sample type code or name is required';
        }

        if ($labCode !== '' && ! $this->resolveLab($labCode)) {
            $errors[] = "Lab '{$labCode}' does not exist — import Labs first";
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        [$sampleTypeCode, $sampleTypeName, $analysisTypeCode, $analysisTypeName, $labCode] = $this->extractHierarchyFields($row);

        if ($sampleTypeCode === '' && $sampleTypeName !== '') {
            $sampleTypeCode = $this->resolveCodeFromName($sampleTypeName);
        }
        if ($sampleTypeName === '' && $sampleTypeCode !== '') {
            $sampleTypeName = $sampleTypeCode;
        }

        if ($analysisTypeCode === '' && $analysisTypeName !== '') {
            $analysisTypeCode = $this->resolveCodeFromName($analysisTypeName);
        }
        if ($analysisTypeName === '' && $analysisTypeCode !== '') {
            $analysisTypeName = $analysisTypeCode;
        }

        $sampleType = $this->resolveSampleType($sampleTypeCode, $sampleTypeName);
        $lab = $labCode !== '' ? $this->resolveLab($labCode) : null;

        $hasNoResult = $this->fuzzyGet($row, ['has_no_result'], 0);
        $reportingTime = $this->fuzzyGet($row, ['reporting_time']);

        $equipmentName = trim((string) $this->fuzzyGet($row, ['equipment', 'equipment_name'], ''));
        $equipmentCode = trim((string) $this->fuzzyGet($row, ['equipment_code', 'equipment_number'], ''));
        $referenceMethod = $this->normalizeOptionalText($this->fuzzyGet($row, [
            'reference_method', 'reference_methods', 'ref_method',
        ]));
        $testMethod = $this->normalizeOptionalText($this->fuzzyGet($row, [
            'test_method_sop', 'test_method', 'testmethodsop', 'method', 'sop',
        ]));
        $methodVersion = $this->normalizeOptionalText($this->fuzzyGet($row, [
            'method_version', 'version', 'revision',
        ]));

        return [
            'code' => $analysisTypeCode,
            'name' => $analysisTypeName,
            'sample_type_id' => $sampleType?->id,
            'sample_type_code' => $sampleType?->code,
            'lab_id' => $lab?->id,
            'has_no_result' => ! in_array(strtolower((string) $hasNoResult), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'reporting_time' => $reportingTime !== null && trim((string) $reportingTime) !== ''
                ? (string) $reportingTime
                : null,
            'company_id' => $this->batch->company_id,
            'active' => ! in_array(strtolower((string) $this->fuzzyGet($row, ['active', 'is_active', 'status'], 1)), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'equipment_code' => $equipmentCode !== '' ? $equipmentCode : null,
            'equipment_name' => $equipmentName !== '' ? $equipmentName : null,
            'reference_method' => $referenceMethod,
            'test_method_sop' => $testMethod,
            'method_version' => $methodVersion,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            if (empty($transformedData['sample_type_id'])) {
                throw new \Exception('Sample type could not be resolved or created');
            }

            $sampleTypeCode = $transformedData['sample_type_code'] ?? null;
            $equipmentCode = $transformedData['equipment_code'] ?? null;
            $equipmentName = $transformedData['equipment_name'] ?? null;
            $referenceMethod = $transformedData['reference_method'] ?? null;
            $testMethod = $transformedData['test_method_sop'] ?? null;
            $methodVersion = $transformedData['method_version'] ?? null;

            unset(
                $transformedData['sample_type_code'],
                $transformedData['equipment_code'],
                $transformedData['equipment_name'],
                $transformedData['reference_method'],
                $transformedData['test_method_sop'],
                $transformedData['method_version'],
            );

            // Scope by sample type so the same analysis-type code can exist under different matrices.
            $analysisType = AnalysisType::updateOrCreate(
                [
                    'code' => $transformedData['code'],
                    'sample_type_id' => $transformedData['sample_type_id'],
                    'company_id' => $this->batch->company_id,
                ],
                $transformedData
            );

            if (! empty($transformedData['lab_id']) && method_exists($analysisType, 'labs')) {
                try {
                    $analysisType->labs()->sync([$transformedData['lab_id']]);
                } catch (\Throwable $t) {
                    \Log::warning('Could not sync labs for AnalysisType import: '.$t->getMessage());
                }
            }

            // Optional columns on the user's workbook — create into the system when present.
            $this->resolveEquipmentIds($equipmentCode, $equipmentName);
            $this->ensureMethodsExist($referenceMethod, $testMethod, $methodVersion);

            $identifier = ($sampleTypeCode ? $sampleTypeCode.'/' : '').$transformedData['code'];
            $this->recordUpsert($identifier, 'upserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analysis type: {$e->getMessage()}");
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
     */
    protected function extractHierarchyFields(array $row): array
    {
        $hasExplicitSampleTypeCode = $this->hasAnyColumn($row, ['sample_type_code', 'matrix_code', 'sample_code']);
        $hasExplicitSampleTypeName = $this->hasAnyColumn($row, ['sample_type_name', 'matrix_name', 'sample_name', 'matrix']);
        $hasExplicitAnalysisTypeCode = $this->hasAnyColumn($row, ['analysis_type_code', 'code', 'at_code']);
        $hasExplicitAnalysisTypeName = $this->hasAnyColumn($row, ['analysis_type_name', 'name', 'at_name']);

        $sampleTypeCombined = trim((string) $this->fuzzyGet($row, ['sample_type'], ''));
        $analysisTypeCombined = trim((string) $this->fuzzyGet($row, ['analysis_type'], ''));

        $sampleTypeCode = trim((string) $this->fuzzyGet($row, [
            'sample_type_code', 'matrix_code', 'sample_code',
        ], ''));
        $sampleTypeName = trim((string) $this->fuzzyGet($row, [
            'sample_type_name', 'matrix_name', 'sample_name', 'matrix',
        ], ''));
        $analysisTypeCode = trim((string) $this->fuzzyGet($row, [
            'analysis_type_code', 'code', 'at_code',
        ], ''));
        $analysisTypeName = trim((string) $this->fuzzyGet($row, [
            'analysis_type_name', 'name', 'at_name',
        ], ''));
        $labCode = trim((string) $this->fuzzyGet($row, [
            'lab_code', 'lab', 'lab_name',
        ], ''));

        // Spreadsheet headers like "Sample Type" / "Analysis Type" are names, not codes.
        if (! $hasExplicitSampleTypeCode && ! $hasExplicitSampleTypeName && $sampleTypeCombined !== '') {
            $sampleTypeName = $sampleTypeCombined;
            $sampleTypeCode = '';
        }
        if (! $hasExplicitAnalysisTypeCode && ! $hasExplicitAnalysisTypeName && $analysisTypeCombined !== '') {
            $analysisTypeName = $analysisTypeCombined;
            $analysisTypeCode = '';
        }

        return [$sampleTypeCode, $sampleTypeName, $analysisTypeCode, $analysisTypeName, $labCode];
    }

    /**
     * @param  list<string>  $keys
     */
    protected function hasAnyColumn(array $row, array $keys): bool
    {
        foreach ($keys as $key) {
            $normalized = $this->normalizeHeaderName($key);
            if (array_key_exists($normalized, $row) || array_key_exists($key, $row)) {
                $value = $row[$normalized] ?? $row[$key] ?? null;
                if ($value !== null && trim((string) $value) !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    protected function resolveSampleType(string $code, string $name): ?SampleType
    {
        $code = trim($code);
        $name = trim($name);

        if ($code === '' && $name === '') {
            return null;
        }

        $companyId = $this->batch->company_id;

        return SampleType::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($code, $name): void {
                if ($code !== '') {
                    $query->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($code)]);
                }
                if ($name !== '') {
                    $method = $code !== '' ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}('LOWER(TRIM(name)) = ?', [strtolower($name)]);
                }
            })
            ->first();
    }

    /**
     * @deprecated Prefer resolveSampleType — kept for callers that still upsert.
     */
    protected function resolveOrCreateSampleType(string $code, string $name): ?SampleType
    {
        $existing = $this->resolveSampleType($code, $name);
        if ($existing) {
            return $existing;
        }

        $code = trim($code);
        $name = trim($name);
        if ($code === '' && $name === '') {
            return null;
        }
        if ($code === '') {
            $code = $this->resolveCodeFromName($name);
        }
        if ($name === '') {
            $name = $code;
        }

        return SampleType::create([
            'code' => $code,
            'name' => $name,
            'company_id' => $this->batch->company_id,
            'is_results_attachable' => 1,
            'disposal_count' => 30,
            'active' => 1,
        ]);
    }

    protected function resolveLab(string $labCode): ?Lab
    {
        $labCode = trim($labCode);
        if ($labCode === '') {
            return null;
        }

        return Lab::query()
            ->where('company_id', $this->batch->company_id)
            ->where(function ($query) use ($labCode): void {
                $query->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($labCode)])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower($labCode)])
                    ->orWhere('code', 'like', "%{$labCode}%")
                    ->orWhere('name', 'like', "%{$labCode}%");
            })
            ->first();
    }

    protected function ensureMethodsExist(?string $referenceMethod, ?string $testMethod, ?string $methodVersion): void
    {
        if ($referenceMethod) {
            $this->upsertMethod($referenceMethod, 'reference', null, null);
        }

        if ($testMethod) {
            $this->upsertMethod($testMethod, 'ltm', $referenceMethod, $methodVersion);
        }
    }

    protected function upsertMethod(
        string $name,
        string $category,
        ?string $referenceMethodName,
        ?string $version,
    ): ?AnalysisMethod {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $resolver = app(MethodConfigurationResolver::class);
        $methodTypeId = $resolver->resolveTypeIdForCategory($category);
        if ($methodTypeId === null) {
            return null;
        }

        $flags = $resolver->legacyFlagsForTypeId($methodTypeId);
        $referenceTypeId = null;

        if ($resolver->normalizeCategoryLabel($category) === 'ltm' && $referenceMethodName) {
            $reference = $this->upsertMethod($referenceMethodName, 'reference', null, null);
            $referenceTypeId = $reference?->id;
        }

        $description = $name;
        if ($version && stripos($name, $version) === false) {
            $description = trim($name.' '.$version);
        }

        $companyId = $this->batch->company_id;
        $method = AnalysisMethod::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($name): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                    ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($name)]);
            })
            ->first();

        $payload = [
            'name' => $name,
            'code' => $name,
            'description' => $description,
            'method_type_id' => $methodTypeId,
            'reference_type_id' => $referenceTypeId,
            'active' => 1,
            'is_ltm' => $flags['is_ltm'] ?? ($category === 'ltm' ? 1 : 0),
            'is_sampling_method' => $flags['is_sampling_method'] ?? 0,
            'company_id' => $companyId,
        ];

        if ($method) {
            $method->update($payload);

            return $method->fresh();
        }

        return AnalysisMethod::create($payload);
    }

    protected function normalizeOptionalText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
