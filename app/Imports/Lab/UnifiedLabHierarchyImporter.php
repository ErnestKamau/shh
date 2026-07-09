<?php

namespace App\Imports\Lab;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Imports\BaseImporter;
use App\Lab;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\SampleType;
use App\StandardAnalytes;
use App\Standards;
use App\StandardValue;

class UnifiedLabHierarchyImporter extends BaseImporter
{
    /** @var array<string, string> */
    protected array $resolvedLabCache = [];

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
            $errors[] = 'Analyte Code must not exceed 100 characters';
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
        $slug = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');

        return strtoupper(substr($slug, 0, 100));
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
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = $row[$key];
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    protected function transformRow(array $row): mixed
    {
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $cleanKey = preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) $key)));
            $normalizedRow[$cleanKey] = is_string($value) ? trim($value) : $value;
        }
        $row = $normalizedRow;

        $sampleTypeCode = $row['sample_type_code'] ?? $row['sample_code'] ?? null;
        $sampleTypeName = $row['sample_type_name'] ?? $row['sample_name'] ?? null;

        $analysisTypeCode = $row['analysis_type_code'] ?? $row['analysis_type_cod'] ?? $row['analysis_code'] ?? null;
        $analysisTypeName = $row['analysis_type_name'] ?? $row['analysis_name'] ?? null;

        $rawAnalyteCode = $row['analyte_code'] ?? $row['code'] ?? $row['analysis_element_code'] ?? null;
        $analyteCode = $rawAnalyteCode;
        $analyteName = $row['analyte_name'] ?? $row['name'] ?? $row['analysis_element_name'] ?? null;

        $labSectionCode = $row['lab_section_code'] ?? $row['section'] ?? null;
        if (empty($labSectionCode)) {
            $labSectionCode = $row['analysis_element_name'] ?? $row['analysis_element_code'] ?? null;
        }

        $standardCode = $row['standard_code'] ?? null;
        $standardName = $row['standard_name'] ?? null;

        $stdValTypeCode = $row['standard_value_code'] ?? null;
        $stdValTypeName = $row['standard_value_name'] ?? null;
        $stdValType = $row['standard_value_type'] ?? null;

        $low = $row['standard_low'] ?? $row['low'] ?? null;
        $high = $row['standard_high'] ?? $row['high'] ?? null;
        $matrixOperator = $row['standard_matrix_operator'] ?? $row['matrix_operator'] ?? $row['operator'] ?? null;
        $matrixValue = $row['standard_value'] ?? $row['value'] ?? $row['expected_value'] ?? null;

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

        return [
            'sample_type_code' => $sampleTypeCode,
            'sample_type_name' => $sampleTypeName,
            'is_results_attachable' => $this->parseBooleanCell($row['is_results_attachable'] ?? null, 1),
            'disposal_count' => isset($row['disposal_count']) && $row['disposal_count'] !== '' ? (int) $row['disposal_count'] : 30,

            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $analysisTypeName,
            'lab_code' => $row['lab_code'] ?? null,
            'has_no_result' => $this->parseBooleanCell($row['has_no_result'] ?? null, 0),
            'reporting_time' => $row['reporting_time'] ?? null,

            'lab_section_code' => $labSectionCode,
            'equipment_code' => $row['equipment_code'] ?? null,
            'lod' => $row['lod'] ?? null,
            'hod' => $row['loq'] ?? $row['hod'] ?? null,
            'level' => $row['level'] ?? null,
            'method_sequence_name' => $row['method_sequence_name'] ?? null,
            'procedure_worksheet_name' => $row['procedure_worksheet_name'] ?? null,

            'analyte_code' => $analyteCode,
            'analyte_name' => $analyteName,
            'analyte_code_explicit' => $rawAnalyteCode !== null && trim((string) $rawAnalyteCode) !== '',
            'decimal_places' => isset($row['decimal_places']) && $row['decimal_places'] !== '' ? (int) $row['decimal_places'] : 2,
            'reporting_symbol' => $row['reporting_symbol'] ?? null,
            'reporting_unit' => $row['reporting_unit'] ?? null,
            'non_detectable' => $this->parseBooleanCell($row['non_detectable'] ?? null, 0),
            'non_accredited' => $this->parseBooleanCell($row['non_accredited'] ?? null, 0),
            'show_on_report' => $this->parseBooleanCell($row['show_on_report'] ?? $row['show_on_reports'] ?? null, 1),

            'standard_code' => $standardCode,
            'standard_name' => $standardName,
            'is_qc_standard' => $this->parseBooleanCell($row['is_qc_standard'] ?? null, 0),
            'qc_type' => $row['qc_type'] ?? null,

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
            if ($transformedData['analyte_code_explicit']) {
                $updateData = [];
                if (! empty($transformedData['analyte_name']) && $analyte->name !== $transformedData['analyte_name']) {
                    $updateData['name'] = $transformedData['analyte_name'];
                }
                if (! empty($updateData)) {
                    $analyte->update($updateData);
                }
            }

            return $analyte;
        }

        return Analyte::create([
            'code' => $transformedData['analyte_code'],
            'company_id' => $companyId,
            'name' => $transformedData['analyte_name'] ?: 'Analyte '.$transformedData['analyte_code'],
            'decimal_places' => $transformedData['decimal_places'],
            'reporting_symbol' => $transformedData['reporting_symbol'],
            'reporting_unit' => $transformedData['reporting_unit'],
            'non_detectable' => $transformedData['non_detectable'],
            'non_accredited' => $transformedData['non_accredited'],
            'show_on_report' => $transformedData['show_on_report'] ?? 1,
            'active' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildElementPayload(array $transformedData, ?string $labSectionId, ?string $equipmentId): array
    {
        return [
            'lab_section_id' => $labSectionId,
            'equipment_id' => $equipmentId,
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
                    'name' => $transformedData['sample_type_name'] ?: 'Sample Type '.$transformedData['sample_type_code'],
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
                    'name' => $transformedData['analysis_type_name'] ?: 'Analysis Type '.$transformedData['analysis_type_code'],
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

        $analyte = $this->resolveAnalyte($transformedData);
        if ($analyte) {
            $this->recordUpsert($analyte->code, 'upserted');
            $hasImportedAny = true;
        }

        if ($analysisType && $analyte) {
            $labSectionId = null;
            if (! empty($transformedData['lab_section_code'])) {
                $section = LabSection::where('lab_id', $labId)
                    ->where(function ($q) use ($transformedData) {
                        $q->where('code', $transformedData['lab_section_code'])
                            ->orWhere('name', $transformedData['lab_section_code']);
                    })->first();
                if (! $section) {
                    $section = LabSection::create([
                        'lab_id' => $labId,
                        'code' => $this->resolveCodeFromName($transformedData['lab_section_code']),
                        'name' => $transformedData['lab_section_code'],
                        'active' => 1,
                        'company_id' => $this->batch->company_id,
                    ]);
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
                $equip = Equipment::where('equipment_number', $transformedData['equipment_code'])->first();
                if ($equip) {
                    $equipmentId = $equip->id;
                }
            }

            $elementPayload = $this->buildElementPayload($transformedData, $labSectionId, $equipmentId);

            $existingAE = AnalysisElements::where('analysis_type_id', $analysisType->id)
                ->where('analyte_id', $analyte->id)
                ->first();

            if ($existingAE) {
                $updateData = [];
                foreach ($elementPayload as $field => $value) {
                    if ($field === 'active') {
                        continue;
                    }

                    if (in_array($field, ['lod', 'hod', 'level', 'reporting_unit', 'decimal_places', 'non_detectable', 'non_accredited'], true)) {
                        $keys = $field === 'hod' ? ['hod', 'loq'] : [$field];
                        if (! $this->cellProvided($originalRow, $keys)) {
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
                    'name' => $transformedData['standard_name'] ?: 'Standard '.$transformedData['standard_code'],
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
                    'name' => $transformedData['standard_value_name'] ?: 'Standard Value '.$transformedData['standard_value_code'],
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
