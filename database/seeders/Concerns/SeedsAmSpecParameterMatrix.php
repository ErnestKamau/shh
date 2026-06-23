<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Company;
use App\Lab;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\SampleType;

trait SeedsAmSpecParameterMatrix
{
    use ClearsAmSpecTaxonomyData;
    use ReadsAmSpecParametersSpreadsheet;

    /** @var array<string, string> */
    private const SECTION_LAB_CODE_MAP = [
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

    /**
     * @return array{rows: int, sample_types: int, analysis_types: int, analytes: int, elements: int, methods: int}
     */
    protected function seedAmSpecParameterMatrix(Company $company): array
    {
        $this->clearAmSpecTaxonomyData($company);

        $rows = $this->readAmSpecParameterRows();
        $stats = [
            'rows' => $rows->count(),
            'sample_types' => 0,
            'analysis_types' => 0,
            'analytes' => 0,
            'elements' => 0,
            'methods' => 0,
        ];

        foreach ($rows as $row) {
            $sampleType = SampleType::query()->updateOrCreate(
                ['code' => $row['sample_type_code'], 'company_id' => $company->id],
                [
                    'name' => $row['sample_type_name'],
                    'is_results_attachable' => 1,
                    'disposal_count' => 30,
                    'active' => 1,
                ]
            );
            if ($sampleType->wasRecentlyCreated) {
                $stats['sample_types']++;
            }

            $lab = $this->resolveAmSpecLabForSection($company, $row['section_department'] ?? '');

            $analysisType = AnalysisType::query()->updateOrCreate(
                [
                    'code' => $row['analysis_type_code'],
                    'sample_type_id' => $sampleType->id,
                    'company_id' => $company->id,
                ],
                [
                    'name' => $row['analysis_type_name'],
                    'lab_id' => $lab->id,
                    'has_no_result' => 0,
                    'active' => 1,
                ]
            );
            if ($analysisType->wasRecentlyCreated) {
                $stats['analysis_types']++;
            }

            $labSectionId = null;
            if (! empty($row['lab_section_code']) && ! empty($row['lab_section_name'])) {
                $labSection = LabSection::query()->updateOrCreate(
                    ['code' => $row['lab_section_code'], 'company_id' => $company->id],
                    [
                        'name' => $row['lab_section_name'],
                        'lab_id' => $lab->id,
                        'active' => 1,
                    ]
                );
                $labSectionId = $labSection->id;
            }

            $nonAccredited = $this->isNonAccredited($row['accreditation'] ?? 'Accredited');

            $analyte = Analyte::query()->updateOrCreate(
                ['code' => $row['analyte_code'], 'company_id' => $company->id],
                [
                    'name' => $row['analyte_name'],
                    'decimal_places' => $row['decimal_places'],
                    'reporting_unit' => $row['reporting_unit'],
                    'non_accredited' => $nonAccredited,
                    'non_detectable' => 0,
                    'show_on_report' => 1,
                    'active' => 1,
                ]
            );
            if ($analyte->wasRecentlyCreated) {
                $stats['analytes']++;
            }

            $methodId = null;
            if (! empty($row['method'])) {
                $methodCode = $this->generateAmSpecCode($row['method']);
                $method = AnalysisMethod::query()->updateOrCreate(
                    ['code' => $methodCode, 'company_id' => $company->id],
                    ['name' => $row['method'], 'active' => 1]
                );
                $methodId = $method->id;
                if ($method->wasRecentlyCreated) {
                    $stats['methods']++;
                }
            }

            $equipmentId = null;
            if (! empty($row['instrument'])) {
                $equipmentId = Equipment::query()
                    ->where('equipment_number', $row['instrument'])
                    ->value('id');
            }

            $element = AnalysisElements::query()->updateOrCreate(
                [
                    'analysis_type_id' => $analysisType->id,
                    'analyte_id' => $analyte->id,
                ],
                [
                    'lab_section_id' => $labSectionId,
                    'method' => $methodId,
                    'equipment_id' => $equipmentId,
                    'reporting_unit' => $row['reporting_unit'],
                    'decimal_places' => $row['decimal_places'],
                    'non_accredited' => $nonAccredited,
                    'show_on_report' => 1,
                    'active' => 1,
                ]
            );
            if ($element->wasRecentlyCreated) {
                $stats['elements']++;
            }
        }

        return $stats;
    }

    protected function resolveAmSpecLabForSection(Company $company, ?string $section): Lab
    {
        $labCode = $this->labCodeForAmSpecSection($section);

        if ($labCode) {
            $lab = Lab::query()
                ->where('company_id', $company->id)
                ->where('code', $labCode)
                ->where('active', true)
                ->first();

            if ($lab) {
                return $lab;
            }
        }

        $lab = Lab::query()
            ->where('company_id', $company->id)
            ->where('active', true)
            ->orderBy('code')
            ->first();

        if ($lab) {
            return $lab;
        }

        return Lab::query()->updateOrCreate(
            ['code' => 'LAB-DEFAULT', 'company_id' => $company->id],
            ['name' => 'Default Lab', 'active' => 1]
        );
    }

    protected function labCodeForAmSpecSection(?string $section): ?string
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

    protected function isNonAccredited(?string $accreditationScope): int
    {
        $normalized = strtolower(trim((string) $accreditationScope));

        return in_array($normalized, ['non-accredited', 'non accredited', 'no', 'false'], true) ? 1 : 0;
    }
}
