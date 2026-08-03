<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleType;
use App\AnalysisType;
use App\Analyte;
use App\AnalysisElements;
use App\Lab;
use App\SampleAnalysisStage;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AmspecParametersImporter extends BaseImporter
{
    protected $lastLabSection = null;
    protected $lastSampleType = null;
    protected $lastAnalysisType = null;
    protected ?string $lastParameterCategory = null;

    /** @var array<string, array{model: mixed, match: string, created: bool, input_code: string, input_name: string}> */
    protected array $sampleTypeResolutionCache = [];

    /** @var array<string, array{model: mixed, match: string, created: bool, input_code: string, input_name: string}> */
    protected array $analysisTypeResolutionCache = [];

    /** @var array<string, bool> */
    protected array $loggedResolutionKeys = [];

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
        $this->sampleTypeResolutionCache = [];
        $this->analysisTypeResolutionCache = [];
        $this->loggedResolutionKeys = [];
    }

    /**
     * A rolled-back row may have created sample/analysis types that no longer exist in the
     * database. Drop the resolution caches so later rows re-resolve instead of reusing phantom ids.
     */
    protected function onRowFailed(array $row, \Throwable $exception): void
    {
        $this->sampleTypeResolutionCache = [];
        $this->analysisTypeResolutionCache = [];
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

        if ($underscored === 'equipment' || $underscored === 'equipment_name' || $underscored === 'equipment_code' || $normalized === 'equipment') {
            return 'equipment';
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
            'sub_matrix', 'matrixsubcategory', 'matrix_sub_category',
        ], 'matrix_sub_category');
        $explicitAnalysisTypeCode = $this->resolveFieldFromRow($row, ['analysis_type_code']);
        $explicitAnalysisTypeName = $this->resolveFieldFromRow($row, ['analysis_type_name', 'analysis_type']);
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
        $decimalPlaces = $this->resolveFieldFromRow($row, ['decimal_places', 'decimalplaces']);
        $accreditationScope = $this->resolveFieldFromRow($row, [
            'accreditation_scope', 'accreditationscopeaccreditednonaccredited',
            'accreditation', 'accredited_nonaccredited', 'non_accredited',
        ], 'accreditation');
        $instrument = $this->resolveFieldFromRow($row, [
            'equipment', 'equipment_name', 'instrument', 'instrumentused', 'instrument_used',
            'equipment_code',
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

        $analysisTypeName = $this->normalizeSubMatrixValue($explicitAnalysisTypeName);
        if (($analysisTypeName === null || $analysisTypeName === '') && $subMatrix !== null && $subMatrix !== '') {
            $analysisTypeName = $subMatrix;
        }

        if (($analysisTypeName === null || $analysisTypeName === '') && ! empty($explicitAnalysisTypeCode)) {
            $analysisTypeName = $explicitAnalysisTypeCode;
        }

        if ($analysisTypeName === null || $analysisTypeName === '') {
            if (! empty($parameterCategory)) {
                $analysisTypeName = $parameterCategory;
            } elseif ($this->lastAnalysisType) {
                $analysisTypeName = $this->lastAnalysisType;
            } else {
                $analysisTypeName = 'Default Analysis Type';
            }
        } else {
            $this->lastAnalysisType = $analysisTypeName;
        }

        // Prefer explicit codes from arranged templates; otherwise generate from names
        $sampleTypeCode = ! empty($explicitSampleTypeCode)
            ? $this->normalizeExplicitCode($explicitSampleTypeCode)
            : (! empty($matrixCategory) ? $this->generateCode($matrixCategory) : null);
        $analysisTypeCode = ! empty($explicitAnalysisTypeCode)
            ? $this->normalizeExplicitCode($explicitAnalysisTypeCode)
            : (! empty($analysisTypeName) ? $this->generateCode($analysisTypeName) : null);
        $analyteCode = ! empty($explicitAnalyteCode)
            ? $this->plainReportDisplayValue($explicitAnalyteCode)
            : (! empty($parameterName) ? $this->plainReportDisplayValue($parameterName) : null);
        $labSectionCode = ! empty($explicitLabSectionCode)
            ? $this->normalizeExplicitCode($explicitLabSectionCode)
            : (! empty($sectionDepartment) ? $this->generateCode($sectionDepartment) : null);
        $labSectionName = ! empty($sectionDepartment)
            ? $sectionDepartment
            : (! empty($explicitLabSectionCode) ? $this->humanizeLabel($explicitLabSectionCode) : null);

        // Handle non-accredited flag (supports Accreditation Scope or non_accredited boolean).
        // Stays null when the file provides no value, so existing records are not clobbered.
        $nonAccredited = null;
        if (array_key_exists('non_accredited', $row) && $row['non_accredited'] !== null && $row['non_accredited'] !== '') {
            $flag = strtolower(trim((string) $row['non_accredited']));
            $nonAccredited = in_array($flag, ['1', 'yes', 'y', 'true', 'on'], true) ? 1 : 0;
        } elseif ($accreditationScope !== null && trim($accreditationScope) !== '') {
            $accreditationNormalized = strtolower(trim($accreditationScope));
            $nonAccredited = in_array($accreditationNormalized, ['non-accredited', 'non accredited', 'no', 'false'], true) ? 1 : 0;
        }

        return [
            'section_department' => $sectionDepartment,
            'matrix_category' => $matrixCategory,
            'sub_matrix' => $analysisTypeName,
            'parameter_name' => $parameterName,
            'method' => $method,
            'unit' => $unit,
            'decimal_places' => is_numeric($decimalPlaces) ? (int) $decimalPlaces : null,
            'non_accredited' => $nonAccredited,
            'instrument' => $instrument,
            'lab_code' => $explicitLabCode,
            'lab_name' => $labName,

            'sample_type_code' => $sampleTypeCode,
            'sample_type_name' => $matrixCategory,
            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $analysisTypeName,
            'analyte_code' => $analyteCode,
            'analyte_name' => $parameterName,
            'lab_section_code' => $labSectionCode,
            'lab_section_name' => $labSectionName,
            'equipment' => $instrument,
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

        // 1. Resolve SampleType (code -> name -> normalized name)
        $sampleTypeResult = $this->resolveSampleType(
            (string) $transformedData['sample_type_code'],
            (string) $transformedData['sample_type_name']
        );
        $sampleType = $sampleTypeResult['model'];
        if ($sampleTypeResult['created']) {
            $this->recordUpsert($sampleType->code, 'inserted');
            $hasImportedAny = true;
        }
        $this->logEntityResolution('sample_type', $sampleTypeResult);

        // 2. Resolve Lab by code and/or name, else section mapping
        $lab = $this->resolveLabForSection(
            $transformedData['lab_section_name'] ?? $transformedData['section_department'] ?? '',
            $transformedData['lab_code'] ?? null,
            $transformedData['lab_name'] ?? null
        );
        $labId = $lab->id;

        // 3. Resolve AnalysisType (code -> name -> normalized name -> generated code)
        $analysisTypeResult = $this->resolveAnalysisType(
            $sampleType,
            $transformedData['analysis_type_code'] ?? null,
            $transformedData['analysis_type_name'] ?? null,
            $labId
        );
        $analysisType = $analysisTypeResult['model'] ?? null;

        if ($analysisType === null) {
            \Log::warning('AmSpec import row missing analysis type identifiers and no fallback type found', [
                'row' => $this->rowNumber,
                'batch_id' => $this->batch->id ?? null,
                'company_id' => $this->batch->company_id ?? null,
                'sample_type_id' => $sampleType->id ?? null,
                'analysis_type_code' => $transformedData['analysis_type_code'] ?? null,
                'analysis_type_name' => $transformedData['analysis_type_name'] ?? null,
            ]);

            return $hasImportedAny;
        }

        if ($analysisTypeResult['created']) {
            $this->recordUpsert($analysisType->code, 'inserted');
            $hasImportedAny = true;
        }
        $this->logEntityResolution('analysis_type', $analysisTypeResult, $sampleType);

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

            // analysis_elements.lab_section_id references sample_analysis_stages (operational
            // departments, is_sample_stage = 0), not the Monitoring module's lab_sections table.
            $labSection = SampleAnalysisStage::query()
                ->where('company_id', $this->batch->company_id)
                ->where('is_sample_stage', 0)
                ->where(function ($q) use ($sectionCode, $sectionName) {
                    $q->where('code', $sectionCode)
                        ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower((string) $sectionName)]);
                })
                ->first();

            if (! $labSection) {
                try {
                    // Savepoint keeps a failed insert from poisoning the row transaction (PostgreSQL).
                    $labSection = DB::transaction(fn () => SampleAnalysisStage::create([
                        'code' => $sectionCode,
                        'company_id' => $this->batch->company_id,
                        'name' => $sectionName,
                        'lab_id' => $labId,
                        'is_sample_stage' => 0,
                        'active' => 1,
                    ]));
                    $labSectionId = $labSection->id;
                } catch (\Exception $e) {
                    \Log::warning('Could not create lab section: '.$e->getMessage());
                }
            } else {
                $labSectionId = $labSection->id;
                if ($labId && empty($labSection->lab_id)) {
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
                    'decimal_places' => $transformedData['decimal_places'] ?? 2,
                    'reporting_unit' => $transformedData['reporting_unit'],
                    'non_accredited' => $transformedData['non_accredited'] ?? 0,
                    'non_detectable' => 0,
                    'show_on_report' => 1,
                    'active' => 1
                ]);
                $this->recordUpsert($analyte->code, 'inserted');
                $hasImportedAny = true;
            } else {
                // Refresh only the columns the upload actually provides; blank cells leave existing values intact.
                $analyteUpdates = [];
                if (($transformedData['reporting_unit'] ?? null) !== null
                    && $analyte->reporting_unit !== $transformedData['reporting_unit']) {
                    $analyteUpdates['reporting_unit'] = $transformedData['reporting_unit'];
                }
                if ($transformedData['decimal_places'] !== null
                    && (int) $analyte->decimal_places !== $transformedData['decimal_places']) {
                    $analyteUpdates['decimal_places'] = $transformedData['decimal_places'];
                }
                if ($transformedData['non_accredited'] !== null
                    && (int) $analyte->non_accredited !== $transformedData['non_accredited']) {
                    $analyteUpdates['non_accredited'] = $transformedData['non_accredited'];
                }
                if ($analyteUpdates !== []) {
                    $analyte->update($analyteUpdates);
                    $this->recordUpsert($analyte->code, 'updated');
                    $hasImportedAny = true;
                }
            }

            // 6. Process Method (only if we have the data)
            $methodId = null;
            $methodIds = [];
            if (!empty($transformedData['method'])) {
                $resolver = app(\App\Services\Lab\MethodConfigurationResolver::class);
                $resolver->ensurePointerConfigurations();
                $methodTypeId = $resolver->resolveTypeIdForCategory('ltm');
                $flags = $methodTypeId
                    ? $resolver->legacyFlagsForTypeId($methodTypeId)
                    : ['is_ltm' => 1, 'is_sampling_method' => 0];

                $analysisMethod = AnalysisMethod::where('company_id', $this->batch->company_id)
                    ->where(function ($query) use ($transformedData): void {
                        $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($transformedData['method'])])
                            ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($transformedData['method'])]);
                    })
                    ->first();

                if (!$analysisMethod) {
                    $analysisMethod = AnalysisMethod::create([
                        'code' => $transformedData['method'],
                        'company_id' => $this->batch->company_id,
                        'name' => $transformedData['method'],
                        'description' => $transformedData['method'],
                        'method_type_id' => $methodTypeId,
                        'is_ltm' => $flags['is_ltm'] ?? 1,
                        'is_sampling_method' => $flags['is_sampling_method'] ?? 0,
                        'active' => 1,
                    ]);
                } else {
                    $analysisMethod->update([
                        'method_type_id' => $methodTypeId ?? $analysisMethod->method_type_id,
                        'is_ltm' => $flags['is_ltm'] ?? $analysisMethod->is_ltm,
                        'is_sampling_method' => $flags['is_sampling_method'] ?? $analysisMethod->is_sampling_method,
                        'active' => 1,
                    ]);
                }
                $methodId = $analysisMethod->id;
                $methodIds[] = $methodId;
            }

            // 7. Resolve equipment by number (preferred) and/or name; links primary to AE
            $equipmentIds = $this->resolveAmspecEquipmentIds(
                $transformedData['equipment'] ?? $transformedData['equipment_name'] ?? $transformedData['equipment_code'] ?? null,
                $transformedData['equipment_number'] ?? null
            );
            $equipmentId = $equipmentIds[0] ?? null;

            if ($analyte && $equipmentIds !== []) {
                $analyte->equipmentItems()->syncWithoutDetaching($equipmentIds);
            }
            if ($analyte && $methodIds !== []) {
                $analyte->analysisMethods()->syncWithoutDetaching($methodIds);
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
                'decimal_places' => $transformedData['decimal_places'] ?? 2,
                'lod' => $transformedData['lod'] ?? null,
                'hod' => $transformedData['hod'] ?? null,
                'non_accredited' => $transformedData['non_accredited'] ?? 0,
                'show_on_report' => 1,
                'active' => 1,
            ];

            if (!$existingAE) {
                try {
                    // Savepoint keeps a failed insert from poisoning the row transaction (PostgreSQL).
                    DB::transaction(fn () => AnalysisElements::create(array_merge([
                        'analysis_type_id' => $analysisType->id,
                        'analyte_id' => $analyte->id,
                    ], $elementAttributes)));
                    $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'inserted');
                    $hasImportedAny = true;
                } catch (\Exception $e) {
                    // If analysis elements creation fails due to foreign key constraints, try without lab section
                    try {
                        $fallback = $elementAttributes;
                        unset($fallback['lab_section_id']);
                        DB::transaction(fn () => AnalysisElements::create(array_merge([
                            'analysis_type_id' => $analysisType->id,
                            'analyte_id' => $analyte->id,
                        ], $fallback)));
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
                if ($transformedData['decimal_places'] !== null
                    && (int) $existingAE->decimal_places !== $transformedData['decimal_places']) {
                    $updateData['decimal_places'] = $transformedData['decimal_places'];
                }
                if ($transformedData['non_accredited'] !== null
                    && (int) $existingAE->non_accredited !== $transformedData['non_accredited']) {
                    $updateData['non_accredited'] = $transformedData['non_accredited'];
                }
                if ($updateData !== []) {
                    $existingAE->update($updateData);
                    $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'updated');
                    $hasImportedAny = true;
                }
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
    protected function resolveAmspecEquipmentIds(?string $namesRaw, ?string $numbersRaw = null): array
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
                $createdId = $this->findOrCreateEquipment($number, $name);
                if ($createdId !== null) {
                    $resolvedIds[$createdId] = $createdId;
                } else {
                    \Log::warning('AmSpec import: no equipment matched for name=['.($name ?? '').'] number=['.($number ?? '').']');
                }
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function companyScopedQuery($query)
    {
        $companyId = $this->batch->company_id;

        return $query->where(function ($scopedQuery) use ($companyId) {
            $scopedQuery->where('company_id', $companyId)->orWhereNull('company_id');
        });
    }

    protected function normalizeMatchKey(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = strtolower(trim($value));
        $value = str_replace(['&', '＆'], ' and ', $value);
        $value = preg_replace('/\band\b/', ' and ', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    protected function codesEquivalent(?string $left, ?string $right): bool
    {
        if ($left === null || $right === null || trim($left) === '' || trim($right) === '') {
            return false;
        }

        $leftUpper = strtoupper(trim($left));
        $rightUpper = strtoupper(trim($right));

        if ($leftUpper === $rightUpper) {
            return true;
        }

        return $this->generateCode($left) === $this->generateCode($right)
            || $this->normalizeMatchKey($left) === $this->normalizeMatchKey($right);
    }

    protected function namesEquivalent(?string $left, ?string $right): bool
    {
        if ($left === null || $right === null || trim($left) === '' || trim($right) === '') {
            return false;
        }

        if (strtolower(trim($left)) === strtolower(trim($right))) {
            return true;
        }

        return $this->normalizeMatchKey($left) === $this->normalizeMatchKey($right);
    }

    /**
     * @return array{model: SampleType, match: string, created: bool, input_code: string, input_name: string}
     */
    protected function resolveSampleType(string $code, string $name): array
    {
        $code = trim($code);
        $name = trim($name);
        $cacheKey = $this->entityCacheKey('sample_type', $code, $name);

        if (isset($this->sampleTypeResolutionCache[$cacheKey])) {
            return $this->sampleTypeResolutionCache[$cacheKey];
        }

        $matched = $this->findSampleTypeMatch($code, $name);
        if ($matched !== null) {
            $this->sampleTypeResolutionCache[$cacheKey] = $matched;

            return $matched;
        }

        $sampleType = SampleType::create([
            'code' => $code,
            'company_id' => $this->batch->company_id,
            'name' => $name,
            'is_results_attachable' => 1,
            'disposal_count' => 30,
            'active' => 1,
        ]);

        $result = $this->entityResolutionResult($sampleType, 'created', true, $code, $name);
        $this->sampleTypeResolutionCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @return array{model: SampleType, match: string, created: bool, input_code: string, input_name: string}|null
     */
    protected function findSampleTypeMatch(string $code, string $name): ?array
    {
        foreach ([true, false] as $companyScoped) {
            if ($code !== '') {
                $query = SampleType::query();
                if ($companyScoped) {
                    $query = $this->companyScopedQuery($query);
                }

                $matches = $query->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($code)])->get();
                $sampleType = $this->pickPreferredCompanyMatch($matches);
                if ($sampleType) {
                    return $this->entityResolutionResult(
                        $sampleType,
                        $companyScoped ? 'code' : 'global_code',
                        false,
                        $code,
                        $name
                    );
                }
            }

            if ($name !== '') {
                $query = SampleType::query();
                if ($companyScoped) {
                    $query = $this->companyScopedQuery($query);
                }

                $matches = $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->get();
                $sampleType = $this->pickPreferredCompanyMatch($matches);
                if ($sampleType) {
                    return $this->entityResolutionResult(
                        $sampleType,
                        $companyScoped ? 'name' : 'global_name',
                        false,
                        $code,
                        $name
                    );
                }
            }

            $normalizedName = $this->normalizeMatchKey($name);
            if ($normalizedName !== '' || $code !== '') {
                $query = SampleType::query();
                if ($companyScoped) {
                    $query = $this->companyScopedQuery($query);
                }

                $sampleType = $query->get()->first(function (SampleType $candidate) use ($code, $name, $normalizedName) {
                    return $this->namesEquivalent($name, (string) $candidate->name)
                        || ($code !== '' && $this->codesEquivalent($code, (string) $candidate->code))
                        || ($code !== '' && $this->codesEquivalent($code, (string) $candidate->name))
                        || ($normalizedName !== '' && $this->normalizeMatchKey((string) $candidate->code) === $normalizedName);
                });

                if ($sampleType) {
                    return $this->entityResolutionResult(
                        $sampleType,
                        $companyScoped ? 'normalized_name' : 'global_normalized_name',
                        false,
                        $code,
                        $name
                    );
                }
            }
        }

        return null;
    }

    /**
     * @return array{model: ?AnalysisType, match: string, created: bool, input_code: string, input_name: string}
     */
    protected function resolveAnalysisType(SampleType $sampleType, ?string $code, ?string $name, string $labId): array
    {
        $code = trim((string) $code);
        $name = trim((string) $name);
        $cacheKey = $this->entityCacheKey('analysis_type', $code, $name, (string) $sampleType->id);

        if (isset($this->analysisTypeResolutionCache[$cacheKey])) {
            return $this->analysisTypeResolutionCache[$cacheKey];
        }

        if ($code === '' && $name === '') {
            $analysisType = $this->analysisTypeCandidatesQuery($sampleType)->first();
            if ($analysisType) {
                $result = $this->entityResolutionResult($analysisType, 'sample_type_default', false, $code, $name);
                $this->analysisTypeResolutionCache[$cacheKey] = $result;

                return $result;
            }

            $result = $this->entityResolutionResult(null, 'missing', false, $code, $name);
            $this->analysisTypeResolutionCache[$cacheKey] = $result;

            return $result;
        }

        if ($code !== '') {
            $analysisType = $this->analysisTypeCandidatesQuery($sampleType)
                ->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($code)])
                ->first();

            if ($analysisType) {
                $result = $this->entityResolutionResult($analysisType, 'code', false, $code, $name);
                $this->analysisTypeResolutionCache[$cacheKey] = $result;

                return $result;
            }
        }

        if ($name !== '') {
            $analysisType = $this->analysisTypeCandidatesQuery($sampleType)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->first();

            if ($analysisType) {
                $result = $this->entityResolutionResult($analysisType, 'name', false, $code, $name);
                $this->analysisTypeResolutionCache[$cacheKey] = $result;

                return $result;
            }

            $normalizedName = $this->normalizeMatchKey($name);
            if ($normalizedName !== '') {
                $analysisType = $this->analysisTypeCandidatesQuery($sampleType)
                    ->get()
                    ->first(function (AnalysisType $candidate) use ($name, $normalizedName, $code) {
                        return $this->namesEquivalent($name, (string) $candidate->name)
                            || ($code !== '' && $this->codesEquivalent($code, (string) $candidate->name))
                            || $this->codesEquivalent($name, (string) $candidate->code)
                            || $this->normalizeMatchKey((string) $candidate->code) === $normalizedName;
                    });

                if ($analysisType) {
                    $result = $this->entityResolutionResult($analysisType, 'normalized_name', false, $code, $name);
                    $this->analysisTypeResolutionCache[$cacheKey] = $result;

                    return $result;
                }
            }

            $generatedCode = $this->generateCode($name);
            $analysisType = $this->analysisTypeCandidatesQuery($sampleType)
                ->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($generatedCode)])
                ->first();

            if ($analysisType) {
                $result = $this->entityResolutionResult($analysisType, 'generated_code', false, $code, $name);
                $this->analysisTypeResolutionCache[$cacheKey] = $result;

                return $result;
            }
        }

        $analysisType = AnalysisType::create([
            'code' => $code !== '' ? $code : $this->generateCode($name),
            'company_id' => $this->batch->company_id,
            'name' => $name !== '' ? $name : ($this->humanizeLabel($code) ?? $code),
            'sample_type_id' => $sampleType->id,
            'lab_id' => $labId,
            'has_no_result' => 0,
            'active' => 1,
        ]);

        $result = $this->entityResolutionResult($analysisType, 'created', true, $code, $name);
        $this->analysisTypeResolutionCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<AnalysisType>
     */
    protected function analysisTypeCandidatesQuery(SampleType $sampleType)
    {
        return AnalysisType::query()->where('sample_type_id', $sampleType->id);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SampleType>|\Illuminate\Support\Collection<int, AnalysisType>  $matches
     */
    protected function pickPreferredCompanyMatch($matches): mixed
    {
        if ($matches->isEmpty()) {
            return null;
        }

        if ($matches->count() === 1) {
            return $matches->first();
        }

        return $matches->firstWhere('company_id', $this->batch->company_id) ?? $matches->first();
    }

    protected function entityCacheKey(string $prefix, string $code, string $name, ?string $parentId = null): string
    {
        return implode('|', array_filter([
            $prefix,
            $parentId,
            strtoupper(trim($code)),
            $this->normalizeMatchKey($name),
        ]));
    }

    /**
     * @param  array{model: mixed, match: string, created: bool, input_code: string, input_name: string}  $result
     */
    protected function logEntityResolution(string $entityType, array $result, ?SampleType $sampleType = null): void
    {
        $model = $result['model'];
        if ($model === null) {
            return;
        }

        $logKey = $entityType.'|'.$result['match'].'|'.($model->id ?? 'none');
        if (isset($this->loggedResolutionKeys[$logKey])) {
            return;
        }
        $this->loggedResolutionKeys[$logKey] = true;

        $companyMismatch = (string) ($model->company_id ?? '') !== ''
            && (string) $model->company_id !== (string) $this->batch->company_id;

        $diagnostics = [
            'entity' => $entityType,
            'match_strategy' => $result['match'],
            'input_code' => $result['input_code'],
            'input_name' => $result['input_name'],
            'resolved_id' => $model->id,
            'resolved_code' => $model->code,
            'resolved_name' => $model->name,
            'resolved_company_id' => $model->company_id ?? null,
            'batch_company_id' => $this->batch->company_id,
            'company_mismatch' => $companyMismatch,
            'sample_type_id' => $sampleType?->id,
            'sample_type_code' => $sampleType?->code,
            'sample_type_name' => $sampleType?->name,
        ];

        \Log::info('AmSpec import entity resolution', array_merge([
            'row' => $this->rowNumber,
            'batch_id' => $this->batch->id ?? null,
        ], $diagnostics));

        if ($result['match'] === 'created') {
            $siblingSummary = '';
            if ($entityType === 'analysis_type' && $sampleType) {
                $siblings = $this->analysisTypeCandidatesQuery($sampleType)
                    ->where('id', '!=', $model->id)
                    ->orderBy('name')
                    ->limit(8)
                    ->get(['code', 'name'])
                    ->map(fn (AnalysisType $type) => "{$type->name} ({$type->code})")
                    ->implode(', ');

                if ($siblings !== '') {
                    $siblingSummary = " Existing analysis types under this sample type: {$siblings}.";
                }
            }

            $this->batch->addWarning(
                $this->rowNumber,
                "Row {$this->rowNumber}: created new {$entityType} '{$model->name}' ({$model->code}). Data was saved here, not under an existing record.{$siblingSummary}",
                $diagnostics
            );

            return;
        }

        if ($result['match'] !== 'code') {
            $inputCode = $result['input_code'] !== '' ? $result['input_code'] : 'n/a';
            $companyNote = $companyMismatch ? ' Record belongs to a different company context than this import batch.' : '';
            $this->batch->addWarning(
                $this->rowNumber,
                "Row {$this->rowNumber}: {$entityType} code '{$inputCode}' not found in current company/sample type. Matched existing '{$model->name}' ({$model->code}) by {$result['match']}.{$companyNote}",
                $diagnostics
            );
        }
    }

    /**
     * @return array{model: mixed, match: string, created: bool, input_code: string, input_name: string}
     */
    protected function entityResolutionResult(mixed $model, string $match, bool $created, string $inputCode, string $inputName): array
    {
        return [
            'model' => $model,
            'match' => $match,
            'created' => $created,
            'input_code' => $inputCode,
            'input_name' => $inputName,
        ];
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
        return strtoupper($this->sanitizeImportedString($code));
    }

    protected function generateCode(?string $name): string
    {
        if (empty($name)) {
            return 'CODE-'.uniqid();
        }

        return $this->resolveCodeFromName($name);
    }
}