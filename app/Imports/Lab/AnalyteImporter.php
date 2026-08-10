<?php

namespace App\Imports\Lab;

use App\AnalysisMethod;
use App\Analyte;
use App\Imports\BaseImporter;
use App\Services\Lab\MethodConfigurationResolver;

class AnalyteImporter extends BaseImporter
{
    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch, $selectedZoneId);

        app(MethodConfigurationResolver::class)->ensurePointerConfigurations();
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        $code = $this->fuzzyGet($row, ['code', 'analyte_code', 'parameter_code', 'coa_name', 'id']);
        $name = $this->fuzzyGet($row, ['name', 'analyte_name', 'analyte', 'parameter', 'parameter_name', 'title']);

        if (empty($code) && empty($name)) {
            $errors[] = 'Either Code or Name is required';
        }

        $decimalPlaces = $this->fuzzyGet($row, ['decimal_places', 'decimals']);
        if ($decimalPlaces !== null && $decimalPlaces !== '' && ! is_numeric($decimalPlaces)) {
            $errors[] = 'Decimal places must be numeric';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = $this->sanitizeImportedString(trim((string) $this->fuzzyGet($row, ['code', 'analyte_code', 'parameter_code', 'coa_name', 'id'], '')));
        $name = $this->sanitizeImportedString(trim((string) $this->fuzzyGet($row, ['name', 'analyte_name', 'analyte', 'parameter', 'parameter_name', 'title'], '')));

        if ($code === '' && $name !== '') {
            $code = $this->plainReportDisplayValue($name) ?: ('AN-'.uniqid());
        } elseif ($code !== '') {
            $code = $this->plainReportDisplayValue($code);
        }

        if ($name === '' && $code !== '') {
            $name = $code;
        }

        $nonDetectable = $this->fuzzyGet($row, ['non_detectable'], 0);
        $nonAccredited = $this->fuzzyGet($row, ['non_accredited'], 0);
        $showOnReport = $this->fuzzyGet($row, ['show_on_report', 'show_on_reports'], 1);

        $equipmentCode = trim((string) $this->fuzzyGet($row, ['equipment_code', 'equipment_number'], ''));
        $equipmentName = trim((string) $this->fuzzyGet($row, ['equipment', 'equipment_name'], ''));
        $equipmentIds = $this->resolveEquipmentIds(
            $equipmentCode !== '' ? $equipmentCode : null,
            $equipmentName !== '' ? $equipmentName : null,
        );

        $referenceMethod = $this->normalizeMethodCell($this->fuzzyGet($row, [
            'reference_method',
            'reference_methods',
            'ref_method',
        ]));
        $testMethodSop = $this->normalizeMethodCell($this->fuzzyGet($row, [
            'test_method_sop',
            'test_method',
            'testmethodsop',
            'method',
            'method_name',
            'sop',
        ]));
        $methodVersion = $this->normalizeMethodCell($this->fuzzyGet($row, [
            'method_version',
            'version',
            'revision',
        ]));

        return [
            'code' => $code,
            'name' => $name,
            'decimal_places' => is_numeric($this->fuzzyGet($row, ['decimal_places', 'decimals']))
                ? (int) $this->fuzzyGet($row, ['decimal_places', 'decimals'])
                : 2,
            'reporting_symbol' => $this->fuzzyGet($row, ['reporting_symbol', 'symbol']),
            'reporting_unit' => $this->fuzzyGet($row, ['reporting_unit', 'unit', 'units']),
            'equipment_id' => $equipmentIds[0] ?? null,
            'equipment_ids' => $equipmentIds,
            'reference_method' => $referenceMethod,
            'test_method_sop' => $testMethodSop,
            'method_version' => $methodVersion,
            'non_detectable' => ! in_array(strtolower((string) $nonDetectable), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'non_accredited' => ! in_array(strtolower((string) $nonAccredited), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'show_on_report' => in_array(strtolower((string) $showOnReport), ['0', 'no', 'false', 'off'], true) ? 0 : 1,
            'active' => 1,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $equipmentIds = $transformedData['equipment_ids'] ?? [];
            $referenceMethod = $transformedData['reference_method'] ?? null;
            $testMethodSop = $transformedData['test_method_sop'] ?? null;
            $methodVersion = $transformedData['method_version'] ?? null;

            unset(
                $transformedData['equipment_ids'],
                $transformedData['reference_method'],
                $transformedData['test_method_sop'],
                $transformedData['method_version'],
            );

            $analyte = Analyte::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            if ($equipmentIds !== []) {
                $analyte->equipmentItems()->sync($equipmentIds);
            }

            $methodIds = $this->resolveAndCreateMethodIds($referenceMethod, $testMethodSop, $methodVersion);
            if ($methodIds !== []) {
                $analyte->analysisMethods()->syncWithoutDetaching($methodIds);
            }

            $this->recordUpsert($transformedData['code'], 'upserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analyte: {$e->getMessage()}");
        }
    }

    /**
     * @return list<string>
     */
    protected function resolveAndCreateMethodIds(?string $referenceMethod, ?string $testMethodSop, ?string $methodVersion): array
    {
        $ids = [];

        if ($referenceMethod !== null) {
            foreach ($this->splitMethodNames($referenceMethod) as $referenceName) {
                $reference = $this->upsertAnalysisMethod(
                    name: $referenceName,
                    category: 'reference',
                    referenceMethodName: null,
                    version: null,
                );
                if ($reference) {
                    $ids[$reference->id] = (string) $reference->id;
                }
            }
        }

        if ($testMethodSop !== null) {
            foreach ($this->splitMethodNames($testMethodSop) as $sopName) {
                $ltm = $this->upsertAnalysisMethod(
                    name: $sopName,
                    category: 'ltm',
                    referenceMethodName: $referenceMethod,
                    version: $methodVersion,
                );
                if ($ltm) {
                    $ids[$ltm->id] = (string) $ltm->id;
                }
            }
        }

        return array_values($ids);
    }

    protected function upsertAnalysisMethod(
        string $name,
        string $category,
        ?string $referenceMethodName,
        ?string $version,
    ): ?AnalysisMethod {
        $resolver = app(MethodConfigurationResolver::class);
        $methodTypeId = $resolver->resolveTypeIdForCategory($category);

        if ($methodTypeId === null) {
            \Log::warning("Analyte import: method type configuration missing for category {$category}");

            return null;
        }

        $flags = $resolver->legacyFlagsForTypeId($methodTypeId);
        $referenceTypeId = null;

        if ($resolver->normalizeCategoryLabel($category) === 'ltm' && $referenceMethodName) {
            // Prefer the first reference token when multiple are listed.
            $primaryReference = $this->splitMethodNames($referenceMethodName)[0] ?? $referenceMethodName;
            $reference = $this->upsertAnalysisMethod(
                name: $primaryReference,
                category: 'reference',
                referenceMethodName: null,
                version: null,
            );
            $referenceTypeId = $reference?->id;
        }

        $description = $name;
        if ($version !== null && $version !== '' && stripos($name, $version) === false) {
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
            'is_ltm' => $flags['is_ltm'],
            'is_sampling_method' => $flags['is_sampling_method'],
            'company_id' => $companyId,
        ];

        if ($method) {
            $method->update($payload);

            return $method->fresh();
        }

        return AnalysisMethod::create($payload);
    }

    /**
     * @return list<string>
     */
    protected function splitMethodNames(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        // SOP codes like AMS/C/SOP/075 must stay intact; only split on commas / pipes / semicolons.
        $looksLikeSopCode = (bool) preg_match('/\bAMS\s*\/|[A-Z]{2,}\/[A-Z0-9]+\/[A-Z0-9]+/i', $raw);

        if ($looksLikeSopCode) {
            $tokens = preg_split('/[,|;]+/', $raw) ?: [];
        } else {
            // Reference lists often use "/" between alternate standards.
            $tokens = preg_split('/[,|;]+/', $raw) ?: [];
        }

        $normalized = [];
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token !== '') {
                $normalized[] = $token;
            }
        }

        return $normalized !== [] ? $normalized : [$raw];
    }

    protected function normalizeMethodCell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
