<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleType;
use App\AnalysisType;
use App\Analyte;
use App\AnalysisElements;
use App\Lab;
use App\LabSection;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use Illuminate\Support\Collection;

class AmspecParametersImporter extends BaseImporter
{
    protected $lastLabSection = null;
    protected $lastSampleType = null;
    protected $lastAnalysisType = null;
    protected ?string $lastParameterCategory = null;

    /** @var array<string, string> */
    protected const SECTION_LAB_CODE_MAP = [
        'chemical' => 'LAB-CHM',
        'chemistry' => 'LAB-CHM',
        'microbiology' => 'LAB-AGF',
        'fuels' => 'LAB-FUEL',
        'fuel' => 'LAB-FUEL',
        'lpg' => 'LAB-FUEL',
        'crude' => 'LAB-CRD',
        'marine' => 'LAB-BNK',
        'bunker' => 'LAB-BNK',
        'agri' => 'LAB-AGF',
        'food' => 'LAB-AGF',
        'environmental' => 'LAB-ENV',
        'environment' => 'LAB-ENV',
        'technical' => 'LAB-TSU',
        'calibration' => 'LAB-TSU',
    ];

    protected function shouldSkipRow(array $row): bool
    {
        if (parent::shouldSkipRow($row)) {
            return true;
        }

        $row = $this->normalizeImporterRowKeys($row);

        $hasSection = $this->resolveFieldFromRow($row, [
            'section_department', 'sectiondepartment', 'lab_section', 'lab_section_code', 'lab_section_name',
        ]);
        $hasLab = $this->resolveFieldFromRow($row, [
            'lab_name', 'lab_code', 'lab',
        ]);
        $hasMatrix = $this->resolveFieldFromRow($row, [
            'matrix_category', 'sample_type', 'sample_type_name', 'sample_type_code', 'matrixcategory',
        ], 'matrix_category');
        $hasParameter = $this->resolveFieldFromRow($row, [
            'name_of_parameters_as_in_report_coa', 'internal_parameters_name',
            'parameters', 'parameter_name', 'internalparametersname',
            'analyte_name', 'analyte_code',
        ], 'name_of_parameters');

        return empty($hasSection) && empty($hasLab) && empty($hasMatrix) && empty($hasParameter);
    }

    protected function onSheetLoaded(string $title): void
    {
        $this->lastLabSection = null;
        $this->lastSampleType = null;
        $this->lastAnalysisType = null;
        $this->lastParameterCategory = null;
    }

    /**
     * Map real AmSpec spreadsheet columns to stable keys (avoids collisions on "parameters"/"method").
     *
     * @param  array<int, string>  $headerMap
     */
    protected function afterHeaderRowDetected(array &$headerMap, Collection $rows): void
    {
        $headerRow = $rows->get($this->headerRowIndex);
        $headerRow = $headerRow instanceof Collection ? $headerRow->toArray() : (array) $headerRow;

        $canonicalMap = [];
        foreach ($headerRow as $colIndex => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $canonical = $this->canonicalHeaderForAmspecColumn((string) $value);
            if ($canonical !== null) {
                $canonicalMap[$colIndex] = $canonical;
            }
        }

        // Merge canonical AmSpec names onto matching columns only.
        // Do not wipe headers the base detector already resolved (e.g. analyte_name).
        foreach ($canonicalMap as $colIndex => $canonical) {
            $headerMap[$colIndex] = $canonical;
        }
    }

    protected function canonicalHeaderForAmspecColumn(string $header): ?string
    {
        $normalized = strtolower(trim(str_replace("\xA0", ' ', $header)));
        $underscored = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';
        $underscored = trim(preg_replace('/_+/', '_', $underscored) ?? '', '_');

        if (str_contains($normalized, 'section') && str_contains($normalized, 'department')) {
            return 'section_department';
        }

        if (str_contains($normalized, 'matrix sub category') || str_contains($normalized, 'matrix sub')) {
            return 'matrix_sub_category';
        }

        if (str_contains($normalized, 'matrix category') || str_contains($normalized, 'matrixcategory')) {
            return 'matrix_category';
        }

        if (str_contains($normalized, 'parameter category')) {
            return 'parameter_category';
        }

        if (str_contains($normalized, 'parameter code') || $underscored === 'parameter_code') {
            return 'parameter_code';
        }

        if (str_contains($normalized, 'internal parameters name')) {
            return 'internal_parameters_name';
        }

        if (str_contains($normalized, 'name of parameters as in report')) {
            return 'name_of_parameters_as_in_report_coa';
        }

        if (str_contains($normalized, 'reference method')) {
            return 'reference_method';
        }

        if (str_contains($normalized, 'test method')) {
            return 'test_method_sop';
        }

        if (str_contains($normalized, 'method version')) {
            return 'method_version';
        }

        if ($normalized === 'unit' || str_starts_with($normalized, 'unit ') || $underscored === 'unit') {
            return 'unit';
        }

        if (str_contains($normalized, 'decimal place') || str_contains($underscored, 'decimal_place')) {
            return 'decimal_places';
        }

        if (str_contains($normalized, 'accreditation') || str_contains($underscored, 'accreditation')) {
            return 'accreditation_scope';
        }

        if (str_contains($normalized, 'instrument') || $underscored === 'instrument') {
            return 'instrument';
        }

        if (preg_match('/^sl\.?\s*no/', $normalized)) {
            return 'sl_no';
        }

        if (in_array($underscored, ['lab_section', 'lab_section_code', 'lab_section_name'], true)
            || in_array($normalized, ['lab section', 'lab_section'], true)
            || str_contains($normalized, 'lab section')) {
            if ($underscored === 'lab_section_code' || str_contains($underscored, 'section_code')) {
                return 'lab_section_code';
            }

            return 'lab_section_name';
        }

        if (in_array($underscored, ['lab', 'lab_name', 'lab_code'], true)
            || in_array($normalized, ['lab', 'lab name', 'lab code'], true)) {
            if ($underscored === 'lab_code' || str_contains($normalized, 'lab code')) {
                return 'lab_code';
            }

            return 'lab_name';
        }

        if (in_array($underscored, ['sample_type_code', 'sample_type_name', 'sample_type'], true)
            || in_array($normalized, ['sample type', 'sample_type'], true)) {
            return $underscored === 'sample_type_code' ? 'sample_type_code'
                : ($underscored === 'sample_type_name' ? 'sample_type_name' : 'sample_type');
        }

        if (in_array($underscored, ['analysis_type_code', 'analysis_type_name', 'analysis_type'], true)
            || in_array($normalized, ['analysis type', 'analysis_type'], true)) {
            return $underscored === 'analysis_type_code' ? 'analysis_type_code'
                : ($underscored === 'analysis_type_name' ? 'analysis_type_name' : 'analysis_type');
        }

        if (in_array($underscored, ['analyte_name', 'analyte_code'], true)) {
            return $underscored;
        }

        if (in_array($normalized, ['parameters', 'parameter'], true) || $underscored === 'parameters') {
            return 'parameters';
        }

        if ($normalized === 'method' || $underscored === 'method') {
            return 'method';
        }

        if (str_contains($normalized, 'reporting unit') || str_starts_with($underscored, 'reporting_unit')) {
            return 'reporting_unit';
        }

        if ($underscored === 'equipment' || $underscored === 'equipment_code' || $underscored === 'equipment_name' || $normalized === 'equipment') {
            return $underscored === 'equipment_name' ? 'equipment_name' : 'equipment';
        }

        if (in_array($underscored, ['equipment_number', 'equipment_no', 'equipment_numbers'], true)
            || str_contains($normalized, 'equipment number')) {
            return 'equipment_number';
        }

        if ($underscored === 'non_accredited') {
            return 'non_accredited';
        }

        if (in_array($underscored, ['lod', 'loq', 'hod'], true)) {
            return $underscored;
        }

        if ($underscored === 'lab_code') {
            return 'lab_code';
        }

        if ($underscored === 'lab_name') {
            return 'lab_name';
        }

        return null;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        $row = $this->normalizeImporterRowKeys($row);

        $matrixCategory = $this->resolveFieldFromRow($row, [
            'matrix_category', 'sample_type', 'sample_type_name', 'sample_type_code', 'matrixcategory',
        ], 'matrix_category');
        $parameterName = $this->resolveFieldFromRow($row, [
            'name_of_parameters_as_in_report_coa', 'nameofparametersasinreportcoa',
            'parameters', 'parameter_name', 'internal_parameters_name', 'internalparametersname',
            'analyte_name', 'analyte_code',
        ], 'name_of_parameters');

        if (empty($matrixCategory) && empty($this->lastSampleType)) {
            $errors[] = 'Sample Type is required';
        }

        if (empty($parameterName)) {
            $errors[] = 'Parameter Name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $row = $this->normalizeImporterRowKeys($row);

        $sectionDepartment = $this->resolveFieldFromRow($row, [
            'lab_section_name', 'section_department', 'sectiondepartment', 'lab_section',
        ]);
        $explicitLabSectionCode = $this->resolveFieldFromRow($row, ['lab_section_code']);
        $labName = $this->resolveFieldFromRow($row, ['lab_name', 'lab']);
        $explicitLabCode = $this->resolveFieldFromRow($row, ['lab_code']);
        $matrixCategory = $this->resolveFieldFromRow($row, [
            'matrix_category', 'sample_type_name', 'sample_type', 'matrixcategory',
        ], 'matrix_category');
        $explicitSampleTypeCode = $this->resolveFieldFromRow($row, ['sample_type_code']);
        $subMatrix = $this->resolveFieldFromRow($row, [
            'sub_matrix', 'analysis_type_name', 'analysis_type', 'matrixsubcategory', 'matrix_sub_category',
        ], 'matrix_sub_category');
        $explicitAnalysisTypeCode = $this->resolveFieldFromRow($row, ['analysis_type_code']);
        $parameterCategory = $this->resolveFieldFromRow($row, [
            'parameter_category',
        ], 'parameter_category');
        $parameterName = $this->resolveFieldFromRow($row, [
            'name_of_parameters_as_in_report_coa', 'nameofparametersasinreportcoa',
            'parameters', 'parameter_name', 'internal_parameters_name', 'internalparametersname',
            'analyte_name',
        ], 'name_of_parameters');
        $explicitAnalyteCode = $this->resolveFieldFromRow($row, ['analyte_code', 'parameter_code']);
        if (empty($parameterName) && ! empty($explicitAnalyteCode)) {
            $parameterName = $explicitAnalyteCode;
        }
        $method = $this->resolveFieldFromRow($row, [
            'test_method_sop', 'testmethodsop', 'method',
        ], 'test_method');
        $unit = $this->resolveFieldFromRow($row, ['unit', 'reporting_unit', 'reporting_unit_code']);
        $decimalPlaces = $this->resolveFieldFromRow($row, ['decimal_places', 'decimalplaces']) ?? 2;
        $accreditationScope = $this->resolveFieldFromRow($row, [
            'accreditation_scope', 'accreditationscopeaccreditednonaccredited',
            'accreditation', 'accredited_nonaccredited', 'non_accredited',
        ], 'accreditation') ?? 'Accredited';
        $instrument = $this->resolveFieldFromRow($row, [
            'equipment_name', 'instrument', 'instrumentused', 'instrument_used',
            'equipment', 'equipment_code',
        ], 'instrument');
        $equipmentNumber = $this->resolveFieldFromRow($row, [
            'equipment_number', 'equipment_no', 'equipment_numbers',
        ]);
        $lod = $this->resolveFieldFromRow($row, ['lod']);
        $loq = $this->resolveFieldFromRow($row, ['loq', 'hod']);

        // Handle continuation pattern: empty cells mean "same as above"
        // Only inherit if the current value is truly empty (not just whitespace)
        if (empty($sectionDepartment) && $this->lastLabSection) {
            $sectionDepartment = $this->lastLabSection;
        } elseif (!empty($sectionDepartment)) {
            $this->lastLabSection = $sectionDepartment;
        }

        if (empty($matrixCategory) && ! empty($explicitSampleTypeCode)) {
            $matrixCategory = $explicitSampleTypeCode;
        }

        if (empty($matrixCategory) && $this->lastSampleType) {
            $matrixCategory = $this->lastSampleType;
        } elseif (!empty($matrixCategory)) {
            $this->lastSampleType = $matrixCategory;
        }

        if (! empty($parameterCategory)) {
            $this->lastParameterCategory = $parameterCategory;
        } elseif ($this->lastParameterCategory) {
            $parameterCategory = $this->lastParameterCategory;
        }

        $subMatrix = $this->normalizeSubMatrixValue($subMatrix);

        if (($subMatrix === null || $subMatrix === '') && ! empty($explicitAnalysisTypeCode)) {
            $subMatrix = $explicitAnalysisTypeCode;
        }

        if ($subMatrix === null || $subMatrix === '') {
            if (! empty($parameterCategory)) {
                $subMatrix = $parameterCategory;
            } elseif ($this->lastAnalysisType) {
                $subMatrix = $this->lastAnalysisType;
            } else {
                $subMatrix = 'Default Analysis Type';
            }
        } else {
            $this->lastAnalysisType = $subMatrix;
        }

        // Prefer explicit codes from arranged templates; otherwise generate from names
        $sampleTypeCode = ! empty($explicitSampleTypeCode)
            ? $this->normalizeExplicitCode($explicitSampleTypeCode)
            : (! empty($matrixCategory) ? $this->generateCode($matrixCategory) : null);
        $analysisTypeCode = ! empty($explicitAnalysisTypeCode)
            ? $this->normalizeExplicitCode($explicitAnalysisTypeCode)
            : (! empty($subMatrix) ? $this->generateCode($subMatrix) : null);
        $analyteCode = ! empty($explicitAnalyteCode)
            ? $this->normalizeExplicitCode($explicitAnalyteCode)
            : (! empty($parameterName) ? $this->generateCode($parameterName) : null);
        $labSectionCode = ! empty($explicitLabSectionCode)
            ? $this->normalizeExplicitCode($explicitLabSectionCode)
            : (! empty($sectionDepartment) ? $this->generateCode($sectionDepartment) : null);
        $labSectionName = ! empty($sectionDepartment)
            ? $sectionDepartment
            : (! empty($explicitLabSectionCode) ? $this->humanizeLabel($explicitLabSectionCode) : null);

        // Handle non-accredited flag (supports Accreditation Scope or non_accredited boolean)
        $nonAccredited = 0;
        if (array_key_exists('non_accredited', $row) && $row['non_accredited'] !== null && $row['non_accredited'] !== '') {
            $flag = strtolower(trim((string) $row['non_accredited']));
            $nonAccredited = in_array($flag, ['1', 'yes', 'y', 'true', 'on'], true) ? 1 : 0;
        } else {
            $accreditationNormalized = strtolower(trim((string) $accreditationScope));
            if (in_array($accreditationNormalized, ['non-accredited', 'non accredited', 'no', 'false'], true)) {
                $nonAccredited = 1;
            }
        }

        return [
            'section_department' => $sectionDepartment,
            'matrix_category' => $matrixCategory,
            'sub_matrix' => $subMatrix,
            'parameter_name' => $parameterName,
            'method' => $method,
            'unit' => $unit,
            'decimal_places' => is_numeric($decimalPlaces) ? (int) $decimalPlaces : 2,
            'non_accredited' => $nonAccredited,
            'instrument' => $instrument,
            'lab_code' => $explicitLabCode,
            'lab_name' => $labName,

            'sample_type_code' => $sampleTypeCode,
            'sample_type_name' => $matrixCategory,
            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $subMatrix,
            'analyte_code' => $analyteCode,
            'analyte_name' => $parameterName,
            'lab_section_code' => $labSectionCode,
            'lab_section_name' => $labSectionName,
            'equipment_name' => $instrument,
            'equipment_number' => $equipmentNumber,
            'equipment_code' => $instrument,
            'reporting_unit' => $unit,
            'lod' => $lod !== null && $lod !== '' && is_numeric($lod) ? (float) $lod : null,
            'hod' => $loq !== null && $loq !== '' && is_numeric($loq) ? (float) $loq : null,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $hasImportedAny = false;

        // Skip if essential data is missing (after continuation handling)
        if (empty($transformedData['sample_type_code']) || empty($transformedData['sample_type_name'])) {
            return false;
        }

        // 1. Process SampleType
        $sampleType = SampleType::where('code', $transformedData['sample_type_code'])
            ->where('company_id', $this->batch->company_id)
            ->first();

        if (!$sampleType) {
            $sampleType = SampleType::create([
                'code' => $transformedData['sample_type_code'],
                'company_id' => $this->batch->company_id,
                'name' => $transformedData['sample_type_name'],
                'is_results_attachable' => 1,
                'disposal_count' => 30,
                'active' => 1
            ]);
            $this->recordUpsert($sampleType->code, 'inserted');
            $hasImportedAny = true;
        }

        // 2. Resolve Lab by code and/or name, else section mapping
        $lab = $this->resolveLabForSection(
            $transformedData['lab_section_name'] ?? $transformedData['section_department'] ?? '',
            $transformedData['lab_code'] ?? null,
            $transformedData['lab_name'] ?? null
        );
        $labId = $lab->id;

        // 3. Process AnalysisType (only if we have the data)
        if (!empty($transformedData['analysis_type_code']) && !empty($transformedData['analysis_type_name'])) {
            $analysisType = AnalysisType::where('code', $transformedData['analysis_type_code'])
                ->where('sample_type_id', $sampleType->id)
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$analysisType) {
                $analysisType = AnalysisType::create([
                    'code' => $transformedData['analysis_type_code'],
                    'company_id' => $this->batch->company_id,
                    'name' => $transformedData['analysis_type_name'],
                    'sample_type_id' => $sampleType->id,
                    'lab_id' => $labId,
                    'has_no_result' => 0,
                    'active' => 1
                ]);
                $this->recordUpsert($analysisType->code, 'inserted');
                $hasImportedAny = true;
            }
        } else {
            // Try to find existing analysis type for this sample type
            $analysisType = AnalysisType::where('sample_type_id', $sampleType->id)
                ->where('company_id', $this->batch->company_id)
                ->first();
            
            if (!$analysisType) {
                // If no analysis type exists, we can't proceed with the rest
                return $hasImportedAny;
            }
        }

        // 4. Process Lab Section (name and/or code)
        $labSectionId = null;
        $sectionName = $transformedData['lab_section_name'] ?? null;
        $sectionCode = $transformedData['lab_section_code'] ?? null;

        if (! empty($sectionName) || ! empty($sectionCode)) {
            if (empty($sectionCode) && ! empty($sectionName)) {
                $sectionCode = $this->generateCode($sectionName);
            }
            if (empty($sectionName) && ! empty($sectionCode)) {
                $sectionName = $this->humanizeLabel($sectionCode);
            }

            $labSection = LabSection::query()
                ->where('company_id', $this->batch->company_id)
                ->where(function ($q) use ($sectionCode, $sectionName, $labId) {
                    $q->where('code', $sectionCode)
                        ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower((string) $sectionName)]);
                    if ($labId) {
                        $q->orWhere(function ($inner) use ($sectionCode, $labId) {
                            $inner->where('lab_id', $labId)->where('code', $sectionCode);
                        });
                    }
                })
                ->first();

            if (! $labSection) {
                try {
                    $labSection = LabSection::create([
                        'code' => $sectionCode,
                        'company_id' => $this->batch->company_id,
                        'name' => $sectionName,
                        'lab_id' => $labId,
                        'active' => 1,
                    ]);
                    $labSectionId = $labSection->id;
                } catch (\Exception $e) {
                    \Log::warning('Could not create lab section: '.$e->getMessage());
                }
            } else {
                $labSectionId = $labSection->id;
                if ($labId && $labSection->lab_id !== $labId) {
                    $labSection->update(['lab_id' => $labId]);
                }
            }
        }

        // 5. Process Analyte (only if we have the data)
        if (!empty($transformedData['analyte_code']) && !empty($transformedData['analyte_name'])) {
            $analyte = Analyte::where('code', $transformedData['analyte_code'])
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$analyte) {
                $analyte = Analyte::create([
                    'code' => $transformedData['analyte_code'],
                    'company_id' => $this->batch->company_id,
                    'name' => $transformedData['analyte_name'],
                    'decimal_places' => $transformedData['decimal_places'],
                    'reporting_unit' => $transformedData['reporting_unit'],
                    'non_accredited' => $transformedData['non_accredited'],
                    'non_detectable' => 0,
                    'show_on_report' => 1,
                    'active' => 1
                ]);
                $this->recordUpsert($analyte->code, 'inserted');
                $hasImportedAny = true;
            }

            // 6. Process Method (only if we have the data)
            $methodId = null;
            if (!empty($transformedData['method'])) {
                $analysisMethod = AnalysisMethod::where('name', $transformedData['method'])
                    ->where('company_id', $this->batch->company_id)
                    ->first();

                if (!$analysisMethod) {
                    $methodCode = $this->generateCode($transformedData['method']);
                    $analysisMethod = AnalysisMethod::create([
                        'code' => $methodCode,
                        'company_id' => $this->batch->company_id,
                        'name' => $transformedData['method'],
                        'active' => 1
                    ]);
                }
                $methodId = $analysisMethod->id;
            }

            // 7. Resolve equipment by number (preferred) and/or name; links primary to AE
            $equipmentIds = $this->resolveEquipmentIds(
                $transformedData['equipment_name'] ?? $transformedData['equipment_code'] ?? null,
                $transformedData['equipment_number'] ?? null
            );
            $equipmentId = $equipmentIds[0] ?? null;

            if ($analyte && $equipmentIds !== []) {
                $analyte->equipmentItems()->syncWithoutDetaching($equipmentIds);
            }

            // 8. Process AnalysisElements
            $existingAE = AnalysisElements::where('analysis_type_id', $analysisType->id)
                ->where('analyte_id', $analyte->id)
                ->first();

            $elementAttributes = [
                'lab_section_id' => $labSectionId,
                'method' => $methodId,
                'equipment_id' => $equipmentId,
                'reporting_unit' => $transformedData['reporting_unit'],
                'decimal_places' => $transformedData['decimal_places'],
                'lod' => $transformedData['lod'] ?? null,
                'hod' => $transformedData['hod'] ?? null,
                'non_accredited' => $transformedData['non_accredited'],
                'show_on_report' => 1,
                'active' => 1,
            ];

            if (!$existingAE) {
                try {
                    AnalysisElements::create(array_merge([
                        'analysis_type_id' => $analysisType->id,
                        'analyte_id' => $analyte->id,
                    ], $elementAttributes));
                    $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'inserted');
                    $hasImportedAny = true;
                } catch (\Exception $e) {
                    // If analysis elements creation fails due to foreign key constraints, try without lab section
                    try {
                        $fallback = $elementAttributes;
                        unset($fallback['lab_section_id']);
                        AnalysisElements::create(array_merge([
                            'analysis_type_id' => $analysisType->id,
                            'analyte_id' => $analyte->id,
                        ], $fallback));
                        $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'inserted');
                        $hasImportedAny = true;
                    } catch (\Exception $e2) {
                        \Log::warning("Could not create analysis elements: " . $e2->getMessage());
                    }
                }
            } else {
                $updateData = [];
                if ($equipmentId && $existingAE->equipment_id !== $equipmentId) {
                    $updateData['equipment_id'] = $equipmentId;
                }
                if ($methodId && $existingAE->method !== $methodId) {
                    $updateData['method'] = $methodId;
                }
                if ($labSectionId && $existingAE->lab_section_id !== $labSectionId) {
                    $updateData['lab_section_id'] = $labSectionId;
                }
                if (($transformedData['reporting_unit'] ?? null) !== null
                    && $existingAE->reporting_unit !== $transformedData['reporting_unit']) {
                    $updateData['reporting_unit'] = $transformedData['reporting_unit'];
                }
                if (array_key_exists('lod', $transformedData) && $transformedData['lod'] !== null
                    && (float) $existingAE->lod !== (float) $transformedData['lod']) {
                    $updateData['lod'] = $transformedData['lod'];
                }
                if (array_key_exists('hod', $transformedData) && $transformedData['hod'] !== null
                    && (float) $existingAE->hod !== (float) $transformedData['hod']) {
                    $updateData['hod'] = $transformedData['hod'];
                }
                if ($updateData !== []) {
                    $existingAE->update($updateData);
                    $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'updated');
                }
                $hasImportedAny = true;
            }
        }

        return $hasImportedAny;
    }

    /**
     * Resolve equipment IDs from optional name and/or number cells.
     * Numbers are preferred when names are ambiguous (duplicate equipment names).
     * Lists: use commas (or | ;) — not slashes — because equipment numbers contain `/`.
     * Name-only legacy AmSpec cells may still use `/` as a separator.
     *
     * @return list<string>
     */
    protected function resolveEquipmentIds(?string $namesRaw, ?string $numbersRaw = null): array
    {
        $companyId = $this->batch->company_id;
        $nameTokens = $this->splitEquipmentList($namesRaw, allowSlashSeparator: true);
        $numberTokens = $this->splitEquipmentList($numbersRaw, allowSlashSeparator: false);

        $resolvedIds = [];
        $pairCount = max(count($nameTokens), count($numberTokens));

        for ($i = 0; $i < $pairCount; $i++) {
            $name = $nameTokens[$i] ?? null;
            $number = $numberTokens[$i] ?? null;

            if ($name === null && $number === null) {
                continue;
            }

            $equipment = $this->findEquipmentByNameAndNumber($name, $number, $companyId);
            if ($equipment) {
                $resolvedIds[$equipment->id] = $equipment->id;
            } else {
                \Log::warning('AmSpec import: no equipment matched for name=['.($name ?? '').'] number=['.($number ?? '').']');
            }
        }

        return array_values($resolvedIds);
    }

    /**
     * @return list<string>
     */
    protected function splitEquipmentList(?string $raw, bool $allowSlashSeparator = true): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $raw = trim($raw);

        // Equipment numbers like AMS/M/INS/037 must not be split on `/`.
        $looksLikeNumberList = (bool) preg_match('/\bAMS\s*\/|[A-Z]{2,}\/[A-Z0-9]+\/[A-Z0-9]+/i', $raw);

        if ($looksLikeNumberList || ! $allowSlashSeparator) {
            $tokens = preg_split('/[,|;]+/', $raw) ?: [];
        } else {
            $tokens = preg_split('/[,\/|;]+|\.(?=[A-Za-z_])/', $raw) ?: [];
        }

        $normalized = [];
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token !== '') {
                $normalized[] = $token;
            }
        }

        return $normalized;
    }

    protected function findEquipmentByNameAndNumber(?string $name, ?string $number, ?string $companyId): ?Equipment
    {
        $number = $number !== null ? trim($number) : null;
        $name = $name !== null ? trim($name) : null;

        if ($number === '' ) {
            $number = null;
        }
        if ($name === '') {
            $name = null;
        }

        // 1) Exact equipment_number match (unique identifier)
        if ($number !== null) {
            $byNumber = Equipment::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->where(function ($q) {
                    $q->where('active', true)->orWhereNull('active');
                })
                ->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($number)])
                ->first();

            if ($byNumber) {
                return $byNumber;
            }

            // Combined token in name cell: "Incubator (AMS/M/INS/037)"
            $embedded = $this->extractEmbeddedEquipmentNumber($name ?? $number);
            if ($embedded !== null && strtolower($embedded) !== strtolower($number)) {
                $byEmbedded = Equipment::query()
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->where(function ($q) {
                        $q->where('active', true)->orWhereNull('active');
                    })
                    ->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($embedded)])
                    ->first();
                if ($byEmbedded) {
                    return $byEmbedded;
                }
            }
        }

        // 2) Name cell may itself be an equipment number
        if ($name !== null) {
            $embedded = $this->extractEmbeddedEquipmentNumber($name);
            if ($embedded !== null) {
                $byEmbedded = Equipment::query()
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->where(function ($q) {
                        $q->where('active', true)->orWhereNull('active');
                    })
                    ->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($embedded)])
                    ->first();
                if ($byEmbedded) {
                    return $byEmbedded;
                }
            }

            $byNumberAsName = Equipment::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->where(function ($q) {
                    $q->where('active', true)->orWhereNull('active');
                })
                ->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($name)])
                ->first();
            if ($byNumberAsName) {
                return $byNumberAsName;
            }
        }

        // 3) Fall back to name matching (may be ambiguous when duplicates exist)
        if ($name !== null) {
            $plainName = $this->stripEmbeddedEquipmentNumber($name);

            return $this->findEquipmentByLabel($plainName, $companyId);
        }

        return null;
    }

    protected function extractEmbeddedEquipmentNumber(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (preg_match('/\(([^)]+)\)\s*$/', $value, $matches)) {
            $inner = trim($matches[1]);
            if ($inner !== '' && preg_match('/[A-Za-z0-9].*\/.*[A-Za-z0-9]/', $inner)) {
                return $inner;
            }
        }

        $trimmed = trim($value);
        if (preg_match('/^[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)+$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }

    protected function stripEmbeddedEquipmentNumber(string $value): string
    {
        $stripped = preg_replace('/\s*\([^)]*\/[^)]*\)\s*$/', '', $value) ?? $value;

        return trim($stripped);
    }

    /**
     * Resolve equipment by fuzzy name when no equipment_number is available.
     */
    protected function findEquipmentByLabel(string $label, ?string $companyId): ?Equipment
    {
        $exact = strtolower(trim($label));
        $humanized = strtolower(trim(preg_replace('/[_\s]+/', ' ', $label) ?? $label));
        $compact = strtolower(preg_replace('/[^a-z0-9]/', '', $label) ?? '');

        $query = Equipment::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(function ($q) {
                $q->where('active', true)->orWhereNull('active');
            });

        $candidates = (clone $query)
            ->where(function ($q) use ($exact, $humanized, $compact) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [$exact])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [$humanized])
                    ->orWhereRaw('LOWER(TRIM(equipment_number)) = ?', [$exact])
                    ->orWhereRaw('LOWER(TRIM(equipment_number)) = ?', [$humanized])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$humanized.'%']);

                foreach ($this->equipmentAliasTerms($exact, $humanized, $compact) as $alias) {
                    $q->orWhereRaw('LOWER(TRIM(name)) = ?', [$alias])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$alias.'%']);
                }
            })
            ->limit(50)
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $aliasTerms = $this->equipmentAliasTerms($exact, $humanized, $compact);

        $scored = $candidates->map(function (Equipment $equipment) use ($exact, $humanized, $compact, $aliasTerms) {
            $name = strtolower(trim((string) $equipment->name));
            $number = strtolower(trim((string) $equipment->equipment_number));
            $nameCompact = strtolower(preg_replace('/[^a-z0-9]/', '', $name) ?? '');

            $score = 0;
            if ($name === $exact || $name === $humanized) {
                $score = 100;
            } elseif ($number === $exact || $number === $humanized) {
                $score = 95;
            } elseif ($nameCompact === $compact && $compact !== '') {
                $score = 90;
            } elseif (str_starts_with($name, $humanized)) {
                $score = 80;
            } elseif (str_contains($name, $humanized)) {
                $score = 70;
            } elseif ($compact !== '' && str_contains($nameCompact, $compact)) {
                $score = 60;
            }

            foreach ($aliasTerms as $alias) {
                if ($name === $alias) {
                    $score = max($score, 100);
                } elseif (str_contains($name, $alias)) {
                    $score = max($score, 75);
                }
            }

            return ['equipment' => $equipment, 'score' => $score];
        })->sortByDesc('score')->first();

        return ($scored['score'] ?? 0) > 0
            ? $scored['equipment']
            : $candidates->first();
    }

    /**
     * @return list<string>
     */
    protected function equipmentAliasTerms(string $exact, string $humanized, string $compact): array
    {
        $aliases = [
            'rtpcr' => 'real time pcr',
            'rtpcr machine' => 'real time pcr',
            'rt pcr' => 'real time pcr',
            'rt-pcr' => 'real time pcr',
            'rt pcr machine' => 'real time pcr',
            'colony_counter' => 'colony counter',
            'biosafety_cabinet' => 'biosafety cabinet',
        ];

        $terms = [];
        foreach ([$exact, $humanized, str_replace(' ', '_', $humanized)] as $key) {
            if (isset($aliases[$key])) {
                $terms[] = $aliases[$key];
            }
        }

        if (str_contains($compact, 'pcr')) {
            $terms[] = 'pcr';
            $terms[] = 'real time pcr';
        }

        return $terms;
    }

    protected function resolveLabForSection(?string $section, ?string $explicitLabCode = null, ?string $explicitLabName = null): Lab
    {
        $companyId = $this->batch->company_id;
        $explicitLabCode = $explicitLabCode !== null ? trim($explicitLabCode) : null;
        $explicitLabName = $explicitLabName !== null ? trim($explicitLabName) : null;

        if ($explicitLabCode === '') {
            $explicitLabCode = null;
        }
        if ($explicitLabName === '') {
            $explicitLabName = null;
        }

        if ($explicitLabCode !== null) {
            $lab = Lab::query()
                ->where('company_id', $companyId)
                ->where('code', $explicitLabCode)
                ->first();

            if ($lab) {
                if ($explicitLabName !== null && $lab->name !== $explicitLabName) {
                    $lab->update(['name' => $explicitLabName]);
                }

                return $lab;
            }
        }

        if ($explicitLabName !== null) {
            $lab = Lab::query()
                ->where('company_id', $companyId)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($explicitLabName)])
                ->first();

            if ($lab) {
                return $lab;
            }

            $lab = Lab::query()
                ->where('company_id', $companyId)
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($explicitLabName).'%'])
                ->orderBy('code')
                ->first();

            if ($lab) {
                return $lab;
            }
        }

        if ($explicitLabCode !== null || $explicitLabName !== null) {
            $code = $explicitLabCode ?: $this->generateCode((string) $explicitLabName);

            return Lab::create([
                'code' => $code,
                'name' => $explicitLabName ?: ('Lab '.$code),
                'phone1' => 'N/A',
                'active' => 1,
                'company_id' => $companyId,
            ]);
        }

        $labCode = $this->labCodeForSection($section);

        if ($labCode) {
            $lab = Lab::query()
                ->where('company_id', $companyId)
                ->where('code', $labCode)
                ->where('active', true)
                ->first();

            if ($lab) {
                return $lab;
            }
        }

        $lab = Lab::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('code')
            ->first();

        if ($lab) {
            return $lab;
        }

        return Lab::create([
            'code' => 'LAB-DEFAULT',
            'name' => 'Default Lab',
            'phone1' => 'N/A',
            'active' => 1,
            'company_id' => $companyId,
        ]);
    }

    protected function labCodeForSection(?string $section): ?string
    {
        if (empty($section)) {
            return null;
        }

        $normalized = strtolower(trim($section));

        foreach (self::SECTION_LAB_CODE_MAP as $keyword => $code) {
            if (str_contains($normalized, $keyword)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeImporterRowKeys(array $row): array
    {
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $cleanKey = preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) $key))) ?? '';
            if ($cleanKey === '') {
                continue;
            }
            $normalizedRow[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalizedRow;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $aliases
     */
    protected function resolveFieldFromRow(array $row, array $aliases, ?string $prefix = null): ?string
    {
        foreach ($aliases as $alias) {
            if (! empty($row[$alias])) {
                return is_string($row[$alias]) ? trim($row[$alias]) : (string) $row[$alias];
            }
        }

        if ($prefix !== null) {
            $prefix = strtolower($prefix);
            foreach ($row as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                if (str_starts_with($key, $prefix)) {
                    return is_string($value) ? trim($value) : (string) $value;
                }
            }
        }

        return null;
    }

    protected function normalizeSubMatrixValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $value === '—' || $value === '-') {
            return null;
        }

        return $value;
    }

    /**
     * Generate a code from a name by removing special characters and uppercasing
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

        $normalized = str_replace(['_', '-'], ' ', $value);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        if ($normalized === '') {
            return $value;
        }

        return ucwords(strtolower($normalized));
    }

    protected function normalizeExplicitCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    protected function generateCode(?string $name): string
    {
        if (empty($name)) {
            return 'CODE-' . uniqid();
        }

        $slug = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        return strtoupper(substr($slug, 0, 100));
    }
}