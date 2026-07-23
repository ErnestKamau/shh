<?php

namespace App\Imports\Lab;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Imports\BaseImporter;
use App\Lab;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Procedures\ProcedureWorksheet;
use App\ReportingUnit;
use App\SampleType;
use App\StandardAnalytes;
use App\Standards;
use App\StandardValue;
use Illuminate\Support\Str;

class UnifiedLabHierarchyImporter extends BaseImporter
{
    /** @var array<string, string> */
    protected array $resolvedLabCache = [];

    /** @var list<string> */
    private const SMALL_WORDS = ['and', 'or', 'of', 'the', 'in', 'on', 'for', 'to', 'a', 'an', 'at', 'by', 'via', 'with'];

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (! empty($row['sample_type_code']) && strlen($row['sample_type_code']) > 100) {
            $errors[] = 'Sample Type Code must not exceed 100 characters';
        }

        if (! empty($row['analysis_type_code']) && strlen($row['analysis_type_code']) > 100) {
            $errors[] = 'Analysis Type Code must not exceed 100 characters';
        }

        if (! empty($row['analyte_code']) && strlen($row['analyte_code']) > 100) {
            $errors[] = 'Report Display must not exceed 100 characters';
        }

        if (! empty($row['standard_code']) && strlen($row['standard_code']) > 100) {
            $errors[] = 'Standard Code must not exceed 100 characters';
        }

        if (! empty($row['standard_value_code']) && strlen($row['standard_value_code']) > 100) {
            $errors[] = 'Standard Value Code must not exceed 100 characters';
        }

        if (! empty($row['decimal_places']) && ! is_numeric($row['decimal_places'])) {
            $errors[] = 'Decimal places must be numeric';
        }

        if (! empty($row['lod']) && ! is_numeric($row['lod'])) {
            $errors[] = 'LOD must be numeric';
        }

        $loqValue = $row['loq'] ?? $row['hod'] ?? null;
        if (! empty($loqValue) && ! is_numeric($loqValue)) {
            $errors[] = 'LOQ must be numeric';
        }

        return $errors;
    }

    protected function resolveCodeFromName(string $name): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $name);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');

        return strtoupper(substr($slug, 0, 100));
    }

    /**
     * Turn snake/kebab labels into readable multi-word names.
     * e.g. moisture_and_water / moisture-and-water → Moisture and Water
     */
    protected function humanizeLabel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        // Keep compact technical units as-entered after light trim.
        if (preg_match('/^[A-Za-z0-9.%µμ°\/±²³⁻⁺]+$/', $value) === 1 && ! str_contains($value, '_') && ! str_contains($value, '-')) {
            return $value;
        }

        $normalized = str_replace(['_', '-'], ' ', $value);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        if ($normalized === '') {
            return $value;
        }

        $words = preg_split('/\s+/', strtolower($normalized)) ?: [];
        $out = [];

        foreach ($words as $index => $word) {
            if ($word === '') {
                continue;
            }

            if ($index > 0 && in_array($word, self::SMALL_WORDS, true)) {
                $out[] = $word;

                continue;
            }

            if (preg_match('/^[a-z]+$/', $word) !== 1) {
                $out[] = $word;

                continue;
            }

            $out[] = Str::ucfirst($word);
        }

        return implode(' ', $out);
    }

    /**
     * Methods often use standard codes (ISO-4833-1). Expand underscores to spaces
     * but keep hyphens/case for technical identifiers.
     */
    protected function normalizeMethodLabel(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }

        $normalized = trim(preg_replace('/\s+/', ' ', str_replace('_', ' ', $value)) ?? $value);
        if ($normalized === '') {
            return $value;
        }

        // Technical codes / already cased labels: keep as entered (after underscore expand).
        if (preg_match('/[A-Z0-9\-\\/]/', $normalized) === 1) {
            return $normalized;
        }

        return $this->humanizeLabel($normalized);
    }

    protected function parseBooleanCell(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return (int) $value > 0 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'yes', 'y', 'true', 'on'], true)) {
            return 1;
        }

        if (in_array($normalized, ['0', 'no', 'n', 'false', 'off'], true)) {
            return 0;
        }

        return $default;
    }

    protected function cellProvided(array $row, array $keys): bool
    {
        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeHeaderName((string) $key);
            foreach ([$key, $normalizedKey] as $candidate) {
                if (! array_key_exists($candidate, $row)) {
                    continue;
                }

                $value = $row[$candidate];
                if ($value !== null && trim((string) $value) !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeImporterRowKeys(array $row): array
    {
        $normalizedRow = [];

        foreach ($row as $key => $value) {
            $cleanKey = $this->normalizeHeaderName((string) $key);
            if ($cleanKey === '') {
                continue;
            }

            $normalizedRow[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalizedRow;
    }

    protected function firstFilled(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = $row[$key];
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return is_string($value) ? trim($value) : $value;
        }

        return null;
    }

    protected function transformRow(array $row): mixed
    {
        $row = $this->normalizeImporterRowKeys($row);

        $sampleTypeCode = $this->firstFilled($row, ['sample_type_code', 'sample_code']);
        $sampleTypeName = $this->humanizeLabel($this->firstFilled($row, ['sample_type_name', 'sample_name']));

        $analysisTypeCode = $this->firstFilled($row, ['analysis_type_code', 'analysis_type_cod', 'analysis_code']);
        $analysisTypeName = $this->humanizeLabel($this->firstFilled($row, ['analysis_type_name', 'analysis_name']));

        $rawAnalyteCode = $this->firstFilled($row, ['analyte_code', 'code', 'analysis_element_code']);
        $analyteCode = $rawAnalyteCode;
        $analyteName = $this->humanizeLabel($this->firstFilled($row, ['analyte_name', 'name', 'analysis_element_name', 'parameter', 'parameter_name']));

        $labSectionCode = $this->firstFilled($row, ['lab_section_code', 'section', 'lab_section']);
        if (empty($labSectionCode)) {
            $labSectionCode = $this->firstFilled($row, ['analysis_element_name', 'analysis_element_code']);
        }

        $methodName = $this->normalizeMethodLabel($this->firstFilled($row, [
            'method',
            'method_name',
            'test_method',
            'test_method_sop',
            'reference_method',
        ]));

        $reportingUnit = $this->firstFilled($row, ['reporting_unit', 'unit', 'units', 'reporting_units']);
        if (is_string($reportingUnit)) {
            $reportingUnit = trim($reportingUnit);
        }

        $standardCode = $this->firstFilled($row, ['standard_code']);
        $standardName = $this->humanizeLabel($this->firstFilled($row, ['standard_name']));

        $stdValTypeCode = $this->firstFilled($row, ['standard_value_code']);
        $stdValTypeName = $this->humanizeLabel($this->firstFilled($row, ['standard_value_name']));
        $stdValType = $this->firstFilled($row, ['standard_value_type']);

        $low = $this->firstFilled($row, ['standard_low', 'low']);
        $high = $this->firstFilled($row, ['standard_high', 'high']);
        $matrixOperator = $this->firstFilled($row, ['standard_matrix_operator', 'matrix_operator', 'operator']);
        $matrixValue = $this->firstFilled($row, ['standard_value', 'value', 'expected_value']);

        if (empty($sampleTypeCode) && ! empty($sampleTypeName)) {
            $sampleTypeCode = $this->resolveCodeFromName($sampleTypeName);
        }
        if (empty($analysisTypeCode) && ! empty($analysisTypeName)) {
            $analysisTypeCode = $this->resolveCodeFromName($analysisTypeName);
        }
        if (empty($analyteCode) && ! empty($analyteName)) {
            $analyteCode = $this->resolveCodeFromName($analyteName);
        }
        if (empty($standardCode) && ! empty($standardName)) {
            $standardCode = $this->resolveCodeFromName($standardName);
        }
        if (empty($stdValTypeCode) && ! empty($stdValTypeName)) {
            $stdValTypeCode = $this->resolveCodeFromName($stdValTypeName);
        }
        if (empty($stdValTypeCode) && ! empty($standardCode)) {
            $stdValTypeCode = 'VAL_'.$standardCode;
            if (empty($stdValTypeName)) {
                $stdValTypeName = 'Value for '.($standardName ?: $standardCode);
            }
        }

        if (empty($sampleTypeName) && ! empty($sampleTypeCode)) {
            $sampleTypeName = $this->humanizeLabel((string) $sampleTypeCode);
        }
        if (empty($analysisTypeName) && ! empty($analysisTypeCode)) {
            $analysisTypeName = $this->humanizeLabel((string) $analysisTypeCode);
        }
        if (empty($analyteName) && ! empty($analyteCode)) {
            $analyteName = $this->humanizeLabel((string) $analyteCode);
        }
        if (empty($standardName) && ! empty($standardCode)) {
            $standardName = $this->humanizeLabel((string) $standardCode);
        }

        return [
            'sample_type_code' => $sampleTypeCode,
            'sample_type_name' => $sampleTypeName,
            'is_results_attachable' => $this->parseBooleanCell($row['is_results_attachable'] ?? null, 1),
            'disposal_count' => isset($row['disposal_count']) && $row['disposal_count'] !== '' ? (int) $row['disposal_count'] : 30,

            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $analysisTypeName,
            'lab_code' => $this->firstFilled($row, ['lab_code']),
            'has_no_result' => $this->parseBooleanCell($row['has_no_result'] ?? null, 0),
            'reporting_time' => $this->firstFilled($row, ['reporting_time']),

            'lab_section_code' => $labSectionCode,
            'equipment_code' => $this->firstFilled($row, ['equipment_code', 'equipment']),
            'lod' => $this->firstFilled($row, ['lod']),
            'hod' => $this->firstFilled($row, ['loq', 'hod']),
            'level' => $this->firstFilled($row, ['level']),
            'method' => $methodName,
            'method_sequence_name' => $this->humanizeLabel($this->firstFilled($row, ['method_sequence_name', 'method_sequence'])),
            'procedure_worksheet_name' => $this->humanizeLabel($this->firstFilled($row, ['procedure_worksheet_name', 'procedure_worksheet'])),

            'analyte_code' => $analyteCode,
            'analyte_name' => $analyteName,
            'analyte_code_explicit' => $rawAnalyteCode !== null && trim((string) $rawAnalyteCode) !== '',
            'decimal_places' => isset($row['decimal_places']) && $row['decimal_places'] !== '' ? (int) $row['decimal_places'] : 2,
            'reporting_symbol' => $this->firstFilled($row, ['reporting_symbol']),
            'reporting_unit' => $reportingUnit,
            'non_detectable' => $this->parseBooleanCell($row['non_detectable'] ?? null, 0),
            'non_accredited' => $this->parseBooleanCell($row['non_accredited'] ?? null, 0),
            'show_on_report' => $this->parseBooleanCell($row['show_on_report'] ?? $row['show_on_reports'] ?? null, 1),

            'standard_code' => $standardCode,
            'standard_name' => $standardName,
            'is_qc_standard' => $this->parseBooleanCell($row['is_qc_standard'] ?? null, 0),
            'qc_type' => $this->firstFilled($row, ['qc_type']),

            'standard_value_code' => $stdValTypeCode,
            'standard_value_name' => $stdValTypeName,
            'standard_value_type' => $stdValType,
            'standard_low' => $low,
            'standard_high' => $high,
            'standard_matrix_operator' => $matrixOperator,
            'standard_value' => $matrixValue,
        ];
    }

    protected function resolveLab(?string $labCode): ?string
    {
        $companyId = $this->batch->company_id;

        if (! empty($labCode)) {
            $cacheKey = $companyId.':'.$labCode;
            if (isset($this->resolvedLabCache[$cacheKey])) {
                return $this->resolvedLabCache[$cacheKey];
            }

            $lab = Lab::query()
                ->where('company_id', $companyId)
                ->where('code', $labCode)
                ->first();

            if (! $lab) {
                $lab = Lab::create([
                    'code' => $labCode,
                    'name' => 'Lab '.$labCode,
                    'phone1' => 'N/A',
                    'active' => 1,
                    'company_id' => $companyId,
                ]);
            }

            $this->resolvedLabCache[$cacheKey] = $lab->id;

            return $lab->id;
        }

        $fallbackCacheKey = $companyId.':__fallback__';
        if (isset($this->resolvedLabCache[$fallbackCacheKey])) {
            return $this->resolvedLabCache[$fallbackCacheKey];
        }

        $firstLab = Lab::query()
            ->where('company_id', $companyId)
            ->orderBy('code')
            ->first();

        if ($firstLab) {
            $this->resolvedLabCache[$fallbackCacheKey] = $firstLab->id;

            return $firstLab->id;
        }

        $defaultLab = Lab::create([
            'code' => 'LAB-DEFAULT',
            'name' => 'Default Lab',
            'phone1' => 'N/A',
            'active' => 1,
            'company_id' => $companyId,
        ]);

        $this->resolvedLabCache[$fallbackCacheKey] = $defaultLab->id;

        return $defaultLab->id;
    }

    protected function resolveAnalyte(array $transformedData): ?Analyte
    {
        if (empty($transformedData['analyte_code']) && empty($transformedData['analyte_name'])) {
            return null;
        }

        $companyId = $this->batch->company_id;

        $analyte = Analyte::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($transformedData): void {
                if (! empty($transformedData['analyte_code'])) {
                    $query->where('code', $transformedData['analyte_code']);
                }

                if (! empty($transformedData['analyte_name'])) {
                    $method = ! empty($transformedData['analyte_code']) ? 'orWhere' : 'where';
                    $query->{$method}('name', $transformedData['analyte_name']);
                }
            })
            ->first();

        if ($analyte) {
            $updateData = [];
            if (! empty($transformedData['analyte_name']) && $analyte->name !== $transformedData['analyte_name']) {
                $updateData['name'] = $transformedData['analyte_name'];
            }
            if ($transformedData['reporting_unit'] !== null && $transformedData['reporting_unit'] !== ''
                && $analyte->reporting_unit !== $transformedData['reporting_unit']) {
                $updateData['reporting_unit'] = $transformedData['reporting_unit'];
            }
            if ($transformedData['reporting_symbol'] !== null && $transformedData['reporting_symbol'] !== ''
                && $analyte->reporting_symbol !== $transformedData['reporting_symbol']) {
                $updateData['reporting_symbol'] = $transformedData['reporting_symbol'];
            }
            if (! empty($updateData)) {
                $analyte->update($updateData);
            }

            return $analyte;
        }

        return Analyte::create([
            'code' => $transformedData['analyte_code'],
            'company_id' => $companyId,
            'name' => $transformedData['analyte_name'] ?: $this->humanizeLabel((string) $transformedData['analyte_code']),
            'decimal_places' => $transformedData['decimal_places'],
            'reporting_symbol' => $transformedData['reporting_symbol'],
            'reporting_unit' => $transformedData['reporting_unit'],
            'non_detectable' => $transformedData['non_detectable'],
            'non_accredited' => $transformedData['non_accredited'],
            'show_on_report' => $transformedData['show_on_report'] ?? 1,
            'active' => 1,
        ]);
    }

    protected function resolveAnalysisMethod(?string $methodName): ?string
    {
        $methodName = trim((string) $methodName);
        if ($methodName === '') {
            return null;
        }

        $companyId = $this->batch->company_id;

        $analysisMethod = AnalysisMethod::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($methodName): void {
                $query->where('name', $methodName)
                    ->orWhere('code', $methodName);
            })
            ->first();

        if ($analysisMethod) {
            if ($analysisMethod->name !== $methodName) {
                $analysisMethod->update(['name' => $methodName]);
            }

            return (string) $analysisMethod->id;
        }

        $analysisMethod = AnalysisMethod::create([
            'code' => $this->resolveCodeFromName($methodName),
            'company_id' => $companyId,
            'name' => $methodName,
            'description' => $methodName,
            'active' => 1,
        ]);

        return (string) $analysisMethod->id;
    }

    protected function resolveReportingUnit(?string $unit): ?string
    {
        $unit = trim((string) $unit);
        if ($unit === '') {
            return null;
        }

        $reportingUnit = ReportingUnit::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($unit)])
            ->first();

        if (! $reportingUnit) {
            $reportingUnit = ReportingUnit::create([
                'name' => $unit,
                'active' => 1,
            ]);
        }

        return (string) $reportingUnit->name;
    }

    protected function resolveMethodSequenceId(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $sequence = MethodSequence::query()->where('name', $name)->first();

        return $sequence?->id ? (string) $sequence->id : null;
    }

    protected function resolveProcedureWorksheetId(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $worksheet = ProcedureWorksheet::query()->where('name', $name)->first();

        return $worksheet?->id ? (string) $worksheet->id : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildElementPayload(
        array $transformedData,
        ?string $labSectionId,
        ?string $equipmentId,
        ?string $methodId,
        ?string $methodSequenceId,
        ?string $procedureWorksheetId,
    ): array {
        return [
            'lab_section_id' => $labSectionId,
            'equipment_id' => $equipmentId,
            'method' => $methodId,
            'method_sequence_id' => $methodSequenceId,
            'has_method_sequence' => $methodSequenceId !== null,
            'procedure_worksheet_id' => $procedureWorksheetId,
            'lod' => $transformedData['lod'],
            'hod' => $transformedData['hod'],
            'level' => $transformedData['level'],
            'reporting_unit' => $transformedData['reporting_unit'],
            'decimal_places' => $transformedData['decimal_places'],
            'non_detectable' => $transformedData['non_detectable'],
            'non_accredited' => $transformedData['non_accredited'],
            'active' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $originalRow = $this->normalizeImporterRowKeys($originalRow);
        $hasImportedAny = false;

        $sampleType = null;
        if (! empty($transformedData['sample_type_code'])) {
            $sampleType = SampleType::where('code', $transformedData['sample_type_code'])
                ->where('company_id', $this->batch->company_id)
                ->first();
            if ($sampleType) {
                $updateData = [];
                if (! empty($transformedData['sample_type_name']) && $sampleType->name !== $transformedData['sample_type_name']) {
                    $updateData['name'] = $transformedData['sample_type_name'];
                }
                if ($this->cellProvided($originalRow, ['is_results_attachable']) && $sampleType->is_results_attachable != $transformedData['is_results_attachable']) {
                    $updateData['is_results_attachable'] = $transformedData['is_results_attachable'];
                }
                if ($this->cellProvided($originalRow, ['disposal_count']) && $sampleType->disposal_count != $transformedData['disposal_count']) {
                    $updateData['disposal_count'] = $transformedData['disposal_count'];
                }
                if (! empty($updateData)) {
                    $sampleType->update($updateData);
                }
            } else {
                $sampleType = SampleType::create([
                    'code' => $transformedData['sample_type_code'],
                    'company_id' => $this->batch->company_id,
                    'name' => $transformedData['sample_type_name']
                        ?: $this->humanizeLabel((string) $transformedData['sample_type_code']),
                    'is_results_attachable' => $transformedData['is_results_attachable'],
                    'disposal_count' => $transformedData['disposal_count'],
                    'active' => 1,
                ]);
            }
            $this->recordUpsert($sampleType->code, 'upserted');
            $hasImportedAny = true;
        }

        $labId = $this->resolveLab($transformedData['lab_code'] ?? null);

        $analysisType = null;
        if (! empty($transformedData['analysis_type_code'])) {
            $resolvedSampleTypeId = null;
            if ($sampleType) {
                $resolvedSampleTypeId = $sampleType->id;
            } else {
                $firstST = SampleType::where('company_id', $this->batch->company_id)->first();
                if ($firstST) {
                    $resolvedSampleTypeId = $firstST->id;
                } else {
                    $defaultST = SampleType::create([
                        'code' => 'ST-DEFAULT',
                        'name' => 'Default Sample Type',
                        'is_results_attachable' => 1,
                        'disposal_count' => 30,
                        'active' => 1,
                        'company_id' => $this->batch->company_id,
                    ]);
                    $resolvedSampleTypeId = $defaultST->id;
                }
            }

            $analysisType = AnalysisType::where('code', $transformedData['analysis_type_code'])
                ->where('sample_type_id', $resolvedSampleTypeId)
                ->where('company_id', $this->batch->company_id)
                ->first();

            if ($analysisType) {
                $updateData = [];
                if (! empty($transformedData['analysis_type_name']) && $analysisType->name !== $transformedData['analysis_type_name']) {
                    $updateData['name'] = $transformedData['analysis_type_name'];
                }
                if ($resolvedSampleTypeId && $analysisType->sample_type_id !== $resolvedSampleTypeId) {
                    $updateData['sample_type_id'] = $resolvedSampleTypeId;
                }
                if ($labId && $analysisType->lab_id !== $labId) {
                    $updateData['lab_id'] = $labId;
                }
                if ($this->cellProvided($originalRow, ['has_no_result']) && $analysisType->has_no_result != $transformedData['has_no_result']) {
                    $updateData['has_no_result'] = $transformedData['has_no_result'];
                }
                if (! empty($transformedData['reporting_time']) && $analysisType->reporting_time !== $transformedData['reporting_time']) {
                    $updateData['reporting_time'] = $transformedData['reporting_time'];
                }
                if (! empty($updateData)) {
                    $analysisType->update($updateData);
                }
            } else {
                $analysisType = AnalysisType::create([
                    'code' => $transformedData['analysis_type_code'],
                    'company_id' => $this->batch->company_id,
                    'name' => $transformedData['analysis_type_name']
                        ?: $this->humanizeLabel((string) $transformedData['analysis_type_code']),
                    'sample_type_id' => $resolvedSampleTypeId,
                    'lab_id' => $labId,
                    'has_no_result' => $transformedData['has_no_result'],
                    'reporting_time' => $transformedData['reporting_time'],
                    'active' => 1,
                ]);
            }

            try {
                if ($labId && method_exists($analysisType, 'labs')) {
                    $analysisType->labs()->sync([$labId]);
                }
            } catch (\Throwable $t) {
                \Log::warning('Could not sync labs for AnalysisType: '.$t->getMessage());
            }

            $this->recordUpsert($analysisType->code, 'upserted');
            $hasImportedAny = true;
        }

        $transformedData['reporting_unit'] = $this->resolveReportingUnit(
            is_string($transformedData['reporting_unit'] ?? null) ? $transformedData['reporting_unit'] : null
        );

        $analyte = $this->resolveAnalyte($transformedData);
        if ($analyte) {
            $this->recordUpsert($analyte->code, 'upserted');
            $hasImportedAny = true;
        }

        if ($analysisType && $analyte) {
            $labSectionId = null;
            if (! empty($transformedData['lab_section_code'])) {
                $sectionLabel = $this->humanizeLabel((string) $transformedData['lab_section_code'])
                    ?: (string) $transformedData['lab_section_code'];
                $sectionCode = $this->resolveCodeFromName($sectionLabel);

                $section = LabSection::where('lab_id', $labId)
                    ->where(function ($q) use ($transformedData, $sectionCode, $sectionLabel) {
                        $q->where('code', $transformedData['lab_section_code'])
                            ->orWhere('code', $sectionCode)
                            ->orWhere('name', $transformedData['lab_section_code'])
                            ->orWhere('name', $sectionLabel);
                    })->first();
                if (! $section) {
                    $section = LabSection::create([
                        'lab_id' => $labId,
                        'code' => $sectionCode,
                        'name' => $sectionLabel,
                        'active' => 1,
                        'company_id' => $this->batch->company_id,
                    ]);
                } elseif ($section->name !== $sectionLabel) {
                    $section->update(['name' => $sectionLabel]);
                }
                $labSectionId = $section->id;
            }
            if (! $labSectionId) {
                $firstSection = LabSection::where('lab_id', $labId)->first();
                if ($firstSection) {
                    $labSectionId = $firstSection->id;
                } else {
                    $defaultSection = LabSection::create([
                        'lab_id' => $labId,
                        'code' => 'LS-DEFAULT',
                        'name' => 'Default Lab Section',
                        'active' => 1,
                        'company_id' => $this->batch->company_id,
                    ]);
                    $labSectionId = $defaultSection->id;
                }
            }

            $equipmentId = null;
            if (! empty($transformedData['equipment_code'])) {
                $equip = Equipment::where('equipment_number', $transformedData['equipment_code'])
                    ->orWhere('name', $transformedData['equipment_code'])
                    ->first();
                if ($equip) {
                    $equipmentId = $equip->id;
                }
            }

            $methodId = $this->resolveAnalysisMethod($transformedData['method'] ?? null);
            $methodSequenceId = $this->resolveMethodSequenceId($transformedData['method_sequence_name'] ?? null);
            $procedureWorksheetId = $this->resolveProcedureWorksheetId($transformedData['procedure_worksheet_name'] ?? null);

            $elementPayload = $this->buildElementPayload(
                $transformedData,
                $labSectionId,
                $equipmentId,
                $methodId,
                $methodSequenceId,
                $procedureWorksheetId,
            );

            $existingAE = AnalysisElements::where('analysis_type_id', $analysisType->id)
                ->where('analyte_id', $analyte->id)
                ->first();

            if ($existingAE) {
                $updateData = [];
                foreach ($elementPayload as $field => $value) {
                    if ($field === 'active') {
                        continue;
                    }

                    if (in_array($field, ['lod', 'hod', 'level', 'reporting_unit', 'decimal_places', 'non_detectable', 'non_accredited', 'method', 'method_sequence_id', 'procedure_worksheet_id', 'has_method_sequence'], true)) {
                        $keys = match ($field) {
                            'hod' => ['hod', 'loq'],
                            'reporting_unit' => ['reporting_unit', 'unit', 'units', 'reporting_units'],
                            'method' => ['method', 'method_name', 'test_method', 'test_method_sop', 'reference_method'],
                            'method_sequence_id', 'has_method_sequence' => ['method_sequence_name', 'method_sequence'],
                            'procedure_worksheet_id' => ['procedure_worksheet_name', 'procedure_worksheet'],
                            default => [$field],
                        };
                        if (! $this->cellProvided($originalRow, $keys) && $value === null) {
                            continue;
                        }
                        if (! $this->cellProvided($originalRow, $keys) && in_array($field, ['lod', 'hod', 'level', 'reporting_unit', 'decimal_places', 'non_detectable', 'non_accredited'], true)) {
                            continue;
                        }
                    }

                    if ($existingAE->{$field} != $value) {
                        $updateData[$field] = $value;
                    }
                }

                if ($labSectionId && $existingAE->lab_section_id !== $labSectionId) {
                    $updateData['lab_section_id'] = $labSectionId;
                }
                if ($equipmentId && $existingAE->equipment_id !== $equipmentId) {
                    $updateData['equipment_id'] = $equipmentId;
                }

                if (! empty($updateData)) {
                    $existingAE->update($updateData);
                }
            } else {
                AnalysisElements::create(array_merge([
                    'analysis_type_id' => $analysisType->id,
                    'analyte_id' => $analyte->id,
                ], $elementPayload));
            }
            $hasImportedAny = true;
        }

        $standard = null;
        if (! empty($transformedData['standard_code'])) {
            $isQcBool = (bool) ($transformedData['is_qc_standard'] ?? false);
            $standard = Standards::where('code', $transformedData['standard_code'])->first();

            if ($standard) {
                $updateData = [];
                if (! empty($transformedData['standard_name']) && $standard->name !== $transformedData['standard_name']) {
                    $updateData['name'] = $transformedData['standard_name'];
                }
                if ($this->cellProvided($originalRow, ['is_qc_standard']) && $standard->is_qc_standard != $isQcBool) {
                    $updateData['is_qc_standard'] = $isQcBool;
                    $updateData['main_standard'] = ! $isQcBool;
                }
                if (! empty($updateData)) {
                    $standard->update($updateData);
                }
            } else {
                $standard = Standards::create([
                    'code' => $transformedData['standard_code'],
                    'name' => $transformedData['standard_name']
                        ?: $this->humanizeLabel((string) $transformedData['standard_code']),
                    'main_standard' => ! $isQcBool,
                    'is_qc_standard' => $isQcBool,
                    'status' => 1,
                    'edited_by' => $this->batch->user_id,
                ]);
            }
            $this->recordUpsert($standard->code, 'upserted');
            $hasImportedAny = true;
        }

        $standardValue = null;
        if (! empty($transformedData['standard_value_code'])) {
            $standardValue = StandardValue::where('code', $transformedData['standard_value_code'])->first();

            if ($standardValue) {
                $updateData = [];
                if (! empty($transformedData['standard_value_name']) && $standardValue->name !== $transformedData['standard_value_name']) {
                    $updateData['name'] = $transformedData['standard_value_name'];
                }
                if (! empty($updateData)) {
                    $standardValue->update($updateData);
                }
            } else {
                $standardValue = StandardValue::create([
                    'code' => $transformedData['standard_value_code'],
                    'name' => $transformedData['standard_value_name']
                        ?: $this->humanizeLabel((string) $transformedData['standard_value_code']),
                    'status' => 1,
                    'edited_by' => $this->batch->user_id,
                ]);
            }
            $hasImportedAny = true;
        }

        if ($standard && $analyte) {
            $low = $transformedData['standard_low'] ?? null;
            $high = $transformedData['standard_high'] ?? null;
            $matrixVal = $transformedData['standard_value'] ?? null;

            $valTypeInput = strtolower(trim((string) ($transformedData['standard_value_type'] ?? '')));

            $valueType = 'range';
            if (in_array($valTypeInput, ['use_value', 'is_standard_value', 'value', 'is_max_value', 'is_min_value', 'max', 'min'], true)) {
                if (in_array($valTypeInput, ['is_min_value', 'min'], true)) {
                    $valueType = 'min';
                } else {
                    $valueType = strtolower(trim((string) ($transformedData['standard_matrix_operator'] ?? 'max')));
                }
            } elseif (in_array($valTypeInput, ['range', 'is_range'], true)) {
                $valueType = 'range';
            } elseif (! empty($transformedData['standard_matrix_operator'])) {
                $valueType = strtolower(trim($transformedData['standard_matrix_operator']));
            } elseif (! empty($matrixVal)) {
                $valueType = 'max';
            }

            $stdValType = ($valueType === 'range') ? 'is_range' : 'is_standard_value';

            $expectedValue = null;
            if ($valueType === 'range') {
                $expectedValue = "{$low} - {$high}";
            } else {
                $expectedValue = "{$valueType} {$matrixVal}";
            }

            StandardAnalytes::updateOrCreate(
                [
                    'standard_id' => $standard->id,
                    'analyte_id' => $analyte->id,
                ],
                [
                    'standard_value_id' => $standardValue ? $standardValue->id : null,
                    'standard_value_type' => $stdValType,
                    'low' => $low,
                    'high' => $high,
                    'standard_is_value' => $matrixVal,
                    'matrix_operator' => ($valueType === 'range') ? null : $valueType,
                    'matrix_value' => $matrixVal,
                    'value_type' => $valueType,
                    'expected_value' => $expectedValue,
                    'is_active' => 1,
                ]
            );
            $hasImportedAny = true;
        }

        return $hasImportedAny;
    }
}
