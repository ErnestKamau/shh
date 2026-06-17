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

class AmspecParametersImporter extends BaseImporter
{
    protected $lastLabSection = null;
    protected $lastSampleType = null;
    protected $lastAnalysisType = null;

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

    protected function validateRow(array $row): array
    {
        $errors = [];
        $row = $this->normalizeImporterRowKeys($row);

        $matrixCategory = $this->resolveFieldFromRow($row, [
            'matrix_category', 'sample_type', 'matrixcategory',
        ], 'matrixcategory');
        $parameterName = $this->resolveFieldFromRow($row, [
            'name_of_parameters_as_in_report_coa', 'nameofparametersasinreportcoa',
            'parameters', 'parameter_name', 'internalparametersname', 'internal_parameters_name',
        ], 'nameofparameters');
        $method = $this->resolveFieldFromRow($row, [
            'test_method_sop', 'testmethodsop', 'method',
        ], 'testmethod');

        if (empty($matrixCategory) && empty($this->lastSampleType)) {
            $errors[] = 'Sample Type is required';
        }

        if (empty($parameterName)) {
            $errors[] = 'Parameter Name is required';
        }

        if (empty($method)) {
            $errors[] = 'Method is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $row = $this->normalizeImporterRowKeys($row);

        $sectionDepartment = $this->resolveFieldFromRow($row, [
            'section_department', 'sectiondepartment', 'lab_section',
        ]);
        $matrixCategory = $this->resolveFieldFromRow($row, [
            'matrix_category', 'sample_type', 'matrixcategory',
        ], 'matrixcategory');
        $subMatrix = $this->resolveFieldFromRow($row, [
            'sub_matrix', 'analysis_type', 'matrixsubcategory', 'matrix_sub_category',
        ], 'matrixsubcategory');
        $parameterName = $this->resolveFieldFromRow($row, [
            'name_of_parameters_as_in_report_coa', 'nameofparametersasinreportcoa',
            'parameters', 'parameter_name', 'internalparametersname', 'internal_parameters_name',
        ], 'nameofparameters');
        $method = $this->resolveFieldFromRow($row, [
            'test_method_sop', 'testmethodsop', 'method',
        ], 'testmethod');
        $unit = $this->resolveFieldFromRow($row, ['unit', 'reporting_unit']);
        $decimalPlaces = $this->resolveFieldFromRow($row, ['decimal_places', 'decimalplaces']) ?? 2;
        $accreditationScope = $this->resolveFieldFromRow($row, [
            'accreditation_scope', 'accreditationscopeaccreditednonaccredited',
            'accreditation', 'accredited_nonaccredited',
        ], 'accreditationscope') ?? 'Accredited';
        $instrument = $this->resolveFieldFromRow($row, [
            'instrument', 'instrumentused', 'equipment',
        ], 'instrument');

        // Handle continuation pattern: empty cells mean "same as above"
        // Only inherit if the current value is truly empty (not just whitespace)
        if (empty($sectionDepartment) && $this->lastLabSection) {
            $sectionDepartment = $this->lastLabSection;
        } elseif (!empty($sectionDepartment)) {
            $this->lastLabSection = $sectionDepartment;
        }

        if (empty($matrixCategory) && $this->lastSampleType) {
            $matrixCategory = $this->lastSampleType;
        } elseif (!empty($matrixCategory)) {
            $this->lastSampleType = $matrixCategory;
        }

        if (empty($subMatrix) || $subMatrix === '—' || $subMatrix === '-') {
            if ($this->lastAnalysisType) {
                $subMatrix = $this->lastAnalysisType;
            } else {
                $subMatrix = 'Default Analysis Type';
            }
        } else {
            $this->lastAnalysisType = $subMatrix;
        }

        // Generate codes from names (only if not null)
        $sampleTypeCode = !empty($matrixCategory) ? $this->generateCode($matrixCategory) : null;
        $analysisTypeCode = !empty($subMatrix) ? $this->generateCode($subMatrix) : null;
        $analyteCode = !empty($parameterName) ? $this->generateCode($parameterName) : null;
        $labSectionCode = !empty($sectionDepartment) ? $this->generateCode($sectionDepartment) : null;

        // Handle non-accredited flag
        $nonAccredited = 0;
        $accreditationNormalized = strtolower(trim((string) $accreditationScope));
        if (in_array($accreditationNormalized, ['non-accredited', 'non accredited', 'no', 'false'], true)) {
            $nonAccredited = 1;
        }

        return [
            'section_department' => $sectionDepartment,
            'matrix_category' => $matrixCategory,
            'sub_matrix' => $subMatrix,
            'parameter_name' => $parameterName,
            'method' => $method,
            'unit' => $unit,
            'decimal_places' => is_numeric($decimalPlaces) ? (int)$decimalPlaces : 2,
            'non_accredited' => $nonAccredited,
            'instrument' => $instrument,

            'sample_type_code' => $sampleTypeCode,
            'sample_type_name' => $matrixCategory,
            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $subMatrix,
            'analyte_code' => $analyteCode,
            'analyte_name' => $parameterName,
            'lab_section_code' => $labSectionCode,
            'lab_section_name' => $sectionDepartment,
            'equipment_code' => $instrument,
            'reporting_unit' => $unit,
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

        // 2. Resolve Lab by section department
        $lab = $this->resolveLabForSection($transformedData['section_department'] ?? $transformedData['lab_section_name'] ?? '');
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

        // 4. Process Lab Section
        $labSectionId = null;
        if (!empty($transformedData['lab_section_code']) && !empty($transformedData['lab_section_name'])) {
            $labSection = LabSection::where('code', $transformedData['lab_section_code'])
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$labSection) {
                try {
                    $labSection = LabSection::create([
                        'code' => $transformedData['lab_section_code'],
                        'company_id' => $this->batch->company_id,
                        'name' => $transformedData['lab_section_name'],
                        'lab_id' => $labId,
                        'active' => 1
                    ]);
                    $labSectionId = $labSection->id;
                } catch (\Exception $e) {
                    // If lab section creation fails, continue without it
                    \Log::warning("Could not create lab section: " . $e->getMessage());
                }
            } else {
                $labSectionId = $labSection->id;
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

            // 7. Process Equipment
            $equipmentId = null;
            if (!empty($transformedData['equipment_code'])) {
                $equipment = Equipment::where('equipment_number', $transformedData['equipment_code'])
                    ->first();
                if (!$equipment) {
                    $equipment = Equipment::create([
                        'equipment_number' => $transformedData['equipment_code'],
                        'name' => $transformedData['equipment_code'],
                        'active' => 1
                    ]);
                }
                $equipmentId = $equipment->id;
            }

            // 8. Process AnalysisElements
            $existingAE = AnalysisElements::where('analysis_type_id', $analysisType->id)
                ->where('analyte_id', $analyte->id)
                ->first();

            if (!$existingAE) {
                try {
                    AnalysisElements::create([
                        'analysis_type_id' => $analysisType->id,
                        'analyte_id' => $analyte->id,
                        'lab_section_id' => $labSectionId,
                        'method' => $methodId,
                        'equipment_id' => $equipmentId,
                        'reporting_unit' => $transformedData['reporting_unit'],
                        'decimal_places' => $transformedData['decimal_places'],
                        'non_accredited' => $transformedData['non_accredited'],
                        'show_on_report' => 1,
                        'active' => 1
                    ]);
                    $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'inserted');
                    $hasImportedAny = true;
                } catch (\Exception $e) {
                    // If analysis elements creation fails due to foreign key constraints, try without lab section
                    try {
                        AnalysisElements::create([
                            'analysis_type_id' => $analysisType->id,
                            'analyte_id' => $analyte->id,
                            'method' => $methodId,
                            'equipment_id' => $equipmentId,
                            'reporting_unit' => $transformedData['reporting_unit'],
                            'decimal_places' => $transformedData['decimal_places'],
                            'non_accredited' => $transformedData['non_accredited'],
                            'show_on_report' => 1,
                            'active' => 1
                        ]);
                        $this->recordUpsert("{$transformedData['analysis_type_code']}/{$transformedData['analyte_code']}", 'inserted');
                        $hasImportedAny = true;
                    } catch (\Exception $e2) {
                        \Log::warning("Could not create analysis elements: " . $e2->getMessage());
                    }
                }
            }
        }

        return $hasImportedAny;
    }

    protected function resolveLabForSection(?string $section): Lab
    {
        $companyId = $this->batch->company_id;
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
            foreach ($row as $key => $value) {
                if (str_starts_with($key, $prefix) && $value !== null && $value !== '') {
                    return is_string($value) ? trim($value) : (string) $value;
                }
            }
        }

        return null;
    }

    /**
     * Generate a code from a name by removing special characters and uppercasing
     */
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