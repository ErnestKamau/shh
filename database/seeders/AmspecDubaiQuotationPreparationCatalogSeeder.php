<?php

namespace Database\Seeders;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Company;
use App\Lab;
use App\SampleAnalysisStage;
use App\SampleType;
use App\SampleTypeCategory;
use Database\Seeders\Concerns\AmSpecSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Upsert AmSpec Dubai catalogue from “Quotation - preparation” PDF:
 * Hand Swab, Surface Swab, AIR, Condensate Water, Potable Water —
 * with analysis types, analytes, elements, methods, and lab sections.
 *
 * Does not duplicate: matches sample types / analysis types / analytes / elements
 * by stable keys and updates lab_section_id when missing or wrong.
 *
 * Lab sections are SampleAnalysisStage rows (operational departments), not Monitoring LabSection.
 *
 * Run:
 *   php artisan db:seed --class=AmspecDubaiQuotationPreparationCatalogSeeder
 */
class AmspecDubaiQuotationPreparationCatalogSeeder extends Seeder
{
    private const MICRO_SECTION = ['code' => 'Micro', 'name' => 'Microbiology'];

    private const CHEM_SECTION = ['code' => 'CHEMISTRY', 'name' => 'Chemistry'];

    public function run(): void
    {
        $company = $this->resolveCompany();
        if ($company === null) {
            $this->command?->error('AmSpec Dubai company not found (AMSPEC-DXB).');

            return;
        }

        $lab = $this->resolveLab($company);
        if ($lab === null) {
            $this->command?->error('Amspec Dubai Lab not found for company '.$company->id);

            return;
        }

        $microSection = $this->resolveLabSection($company, $lab, self::MICRO_SECTION);
        $chemSection = $this->resolveLabSection($company, $lab, self::CHEM_SECTION);

        $stats = [
            'sample_types' => 0,
            'analysis_types' => 0,
            'analytes' => 0,
            'elements' => 0,
            'methods' => 0,
        ];

        foreach ($this->catalog() as $package) {
            $sampleType = $this->upsertSampleType($company, $package);
            if ($sampleType->wasRecentlyCreated) {
                $stats['sample_types']++;
            }
            $this->command?->info("Sample type: {$sampleType->name} ({$sampleType->code})");

            foreach ($package['analysis_types'] as $analysisDef) {
                $sectionId = $analysisDef['section'] === 'micro'
                    ? $microSection->id
                    : $chemSection->id;

                $analysisType = $this->upsertAnalysisType(
                    $company,
                    $lab,
                    $sampleType,
                    $analysisDef['name'],
                    $sectionId,
                );
                if ($analysisType->wasRecentlyCreated) {
                    $stats['analysis_types']++;
                }

                foreach ($analysisDef['tests'] as $test) {
                    $analyte = $this->upsertAnalyte($company, $test['name']);
                    if ($analyte->wasRecentlyCreated) {
                        $stats['analytes']++;
                    }

                    $methodId = null;
                    if (! empty($test['method'])) {
                        $method = $this->upsertMethod($company, $test['method']);
                        $methodId = $method->id;
                        if ($method->wasRecentlyCreated) {
                            $stats['methods']++;
                        }
                    }

                    $element = $this->upsertElement($analysisType, $analyte, $sectionId, $methodId);
                    if ($element->wasRecentlyCreated) {
                        $stats['elements']++;
                    }
                }
            }
        }

        $this->command?->info(sprintf(
            'Quotation-preparation catalog upsert complete — sample types +%d, analysis types +%d, analytes +%d, elements +%d, methods +%d.',
            $stats['sample_types'],
            $stats['analysis_types'],
            $stats['analytes'],
            $stats['elements'],
            $stats['methods'],
        ));
    }

    /**
     * Catalogue derived from Quotation - preparation (1).pdf.
     *
     * @return list<array{
     *     code: string,
     *     name: string,
     *     category: string,
     *     analysis_types: list<array{name: string, section: 'micro'|'chem', tests: list<array{name: string, method: string}>}>
     * }>
     */
    private function catalog(): array
    {
        return [
            [
                'code' => 'Hand Swab',
                'name' => 'Hand Swab',
                'category' => 'Swab',
                'analysis_types' => [
                    [
                        'name' => 'Microbiology',
                        'section' => 'micro',
                        'tests' => [
                            ['name' => 'Enumeration of E. coli', 'method' => 'AMS/M/SOP/050'],
                            ['name' => 'Enumeration of Enterobacteriaceae', 'method' => 'AMS/M/SOP/049'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'Surface Swab',
                'name' => 'Surface Swab',
                'category' => 'Swab',
                'analysis_types' => [
                    [
                        'name' => 'Microbiology',
                        'section' => 'micro',
                        'tests' => [
                            ['name' => 'Enumeration of E. coli', 'method' => 'AMS/M/SOP/050'],
                            ['name' => 'Enumeration of Enterobacteriaceae', 'method' => 'AMS/M/SOP/049'],
                            ['name' => 'Aerobic plate count', 'method' => 'AMS/M/SOP/047'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'AIR',
                'name' => 'AIR',
                'category' => 'Air',
                'analysis_types' => [
                    [
                        'name' => 'Microbiology',
                        'section' => 'micro',
                        'tests' => [
                            ['name' => 'Total Plate Count', 'method' => 'AMS/M/SOP/046'],
                            ['name' => 'Yeasts and Moulds', 'method' => 'AMS/M/SOP/046'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'Condensate Water',
                'name' => 'Condensate Water',
                'category' => 'Water',
                'analysis_types' => [
                    [
                        'name' => 'Microbiology',
                        'section' => 'micro',
                        'tests' => [
                            ['name' => 'Heterotopic plate count', 'method' => 'AMS/M/SOP/038'],
                            ['name' => 'Enumeration Of Legionella', 'method' => 'AMS/M/SOP/045'],
                        ],
                    ],
                    [
                        'name' => 'Chemical Analysis',
                        'section' => 'chem',
                        'tests' => [
                            ['name' => 'Electrical Conductivity @25°C', 'method' => 'AMS/C/SOP/030'],
                            ['name' => 'Total Suspended Solids', 'method' => 'AMS/C/SOP/032'],
                            ['name' => 'Total Dissolved Solids', 'method' => 'AMS/C/SOP/033'],
                            ['name' => 'Total Alkalinity as CaCO3', 'method' => 'AMS/C/SOP/034'],
                            ['name' => 'Chloride', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Sodium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Calcium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Magnesium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Sulphate', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Nitrite', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Phosphate', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Bromate', 'method' => 'AMS/C/SOP/054'],
                            ['name' => 'Iron', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Lead', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Copper', 'method' => 'AMS/C/SOP/055'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'Potable Water',
                'name' => 'Potable Water',
                'category' => 'Water',
                'analysis_types' => [
                    [
                        'name' => 'Chemical Analysis',
                        'section' => 'chem',
                        'tests' => [
                            ['name' => 'pH', 'method' => 'AMS/C/SOP/031'],
                            ['name' => 'Electrical Conductivity @25°C', 'method' => 'AMS/C/SOP/030'],
                            ['name' => 'Appearance', 'method' => 'AMS/C/SOP/044'],
                            ['name' => 'Taste', 'method' => 'AMS/C/SOP/044'],
                            ['name' => 'Odour', 'method' => 'AMS/C/SOP/044'],
                            ['name' => 'Free Chlorine', 'method' => 'AMS/C/SOP/043'],
                            ['name' => 'Colour', 'method' => 'AMS/C/SOP/037'],
                            ['name' => 'Turbidity', 'method' => 'AMS/C/SOP/089'],
                            ['name' => 'Total Suspended Solids', 'method' => 'AMS/C/SOP/032'],
                            ['name' => 'Total Dissolved Solids', 'method' => 'AMS/C/SOP/033'],
                            ['name' => 'Total Alkalinity as CaCO3', 'method' => 'AMS/C/SOP/034'],
                            ['name' => 'Total Hardness', 'method' => 'AMS/C/SOP/035'],
                            ['name' => 'Calcium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Magnesium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Chloride', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Nitrite', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Nitrate', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Fluoride', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Sulphate', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Phosphate', 'method' => 'AMS/C/SOP/053'],
                            ['name' => 'Sodium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Potassium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Boron', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Chromium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Copper', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Iron', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Lead', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Manganese', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Nickel', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Zinc', 'method' => 'AMS/C/SOP/055'],
                            // Optional Parameters (same PDF)
                            ['name' => 'Antimony', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Arsenic', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Barium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Beryllium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Cadmium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Mercury', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Molybdenum', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Selenium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Silver', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Uranium', 'method' => 'AMS/C/SOP/056'],
                            ['name' => 'Thallium', 'method' => 'AMS/C/SOP/055'],
                            ['name' => 'Ammonia', 'method' => 'AMS/C/SOP/037'],
                            ['name' => 'Cyanide', 'method' => 'AMS/C/SOP/037'],
                            ['name' => 'Bromate', 'method' => 'AMS/C/SOP/054'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function resolveCompany(): ?Company
    {
        return Company::query()->find(AmSpecSeedData::DUBAI_COMPANY_ID)
            ?? Company::query()->where('code', AmSpecSeedData::DUBAI_COMPANY_CODE)->first();
    }

    private function resolveLab(Company $company): ?Lab
    {
        return Lab::query()
            ->where('company_id', $company->id)
            ->where(function ($query): void {
                $query->where('code', 'AMS')
                    ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', ['%dubai%']);
            })
            ->orderByRaw("CASE WHEN code = 'AMS' THEN 0 ELSE 1 END")
            ->first();
    }

    /**
     * @param  array{code: string, name: string}  $def
     */
    private function resolveLabSection(Company $company, Lab $lab, array $def): SampleAnalysisStage
    {
        $section = SampleAnalysisStage::query()
            ->where('company_id', $company->id)
            ->where(function ($query): void {
                $query->where('is_sample_stage', false)->orWhereNull('is_sample_stage');
            })
            ->where(function ($query) use ($def): void {
                $query->where('code', $def['code'])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($def['name']))]);
            })
            ->first();

        if ($section === null) {
            $section = SampleAnalysisStage::query()->create([
                'code' => $def['code'],
                'company_id' => $company->id,
                'name' => $def['name'],
                'title' => $def['name'],
                'lab_id' => $lab->id,
                'is_sample_stage' => false,
                'active' => true,
            ]);
            $this->command?->info("Created lab section {$def['name']} ({$def['code']})");

            return $section;
        }

        $section->fill([
            'name' => trim((string) $section->name) !== '' ? $section->name : $def['name'],
            'title' => trim((string) ($section->title ?? '')) !== '' ? $section->title : $def['name'],
            'lab_id' => $section->lab_id ?: $lab->id,
            'is_sample_stage' => false,
            'active' => true,
        ])->save();

        return $section;
    }

    /**
     * @param  array{code: string, name: string, category: string}  $package
     */
    private function upsertSampleType(Company $company, array $package): SampleType
    {
        $category = SampleTypeCategory::query()->updateOrCreate(
            ['sample_type_category' => $package['category']],
            ['active' => true]
        );

        $existing = SampleType::query()
            ->where('company_id', $company->id)
            ->where(function ($query) use ($package): void {
                $query->where('code', $package['code'])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($package['name']))]);
            })
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'name' => $package['name'],
                'code' => $existing->code ?: $package['code'],
                'description' => $existing->description ?: $package['name'],
                'sample_type_category' => $category->id,
                'active' => true,
                'is_results_attachable' => true,
                'company_id' => $company->id,
            ])->save();

            return $existing;
        }

        return SampleType::query()->updateOrCreate(
            ['code' => $package['code'], 'company_id' => $company->id],
            [
                'name' => $package['name'],
                'description' => $package['name'],
                'sample_type_category' => $category->id,
                'active' => true,
                'is_results_attachable' => true,
            ]
        );
    }

    private function upsertAnalysisType(
        Company $company,
        Lab $lab,
        SampleType $sampleType,
        string $name,
        string $labSectionId,
    ): AnalysisType {
        $aliases = match (strtolower(trim($name))) {
            'microbiology' => ['Microbiology', 'Microbiological', 'Micro Analysis'],
            'chemical analysis' => ['Chemical Analysis', 'Chemical', 'Chemistry'],
            default => [$name],
        };

        $existing = AnalysisType::query()
            ->where('sample_type_id', $sampleType->id)
            ->where('company_id', $company->id)
            ->where(function ($query) use ($aliases): void {
                foreach ($aliases as $alias) {
                    $query->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($alias))])
                        ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower(trim($alias))]);
                }
            })
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'name' => $name,
                'code' => $existing->code ?: $name,
                'lab_id' => $existing->lab_id ?: $lab->id,
                'lab_section_id' => $labSectionId,
                'active' => true,
                'has_no_result' => false,
            ])->save();

            return $existing;
        }

        return AnalysisType::query()->updateOrCreate(
            [
                'code' => $name,
                'sample_type_id' => $sampleType->id,
                'company_id' => $company->id,
            ],
            [
                'name' => $name,
                'lab_id' => $lab->id,
                'lab_section_id' => $labSectionId,
                'active' => true,
                'has_no_result' => false,
            ]
        );
    }

    private function upsertAnalyte(Company $company, string $name): Analyte
    {
        $name = trim($name);
        $code = $this->analyteCode($name);

        $existing = Analyte::query()
            ->where('company_id', $company->id)
            ->where(function ($query) use ($name, $code): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                    ->orWhere('code', $code);
            })
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'name' => $existing->name ?: $name,
                'active' => true,
                'show_on_report' => 1,
                'non_detectable' => 0,
            ])->save();

            return $existing;
        }

        return Analyte::query()->updateOrCreate(
            ['code' => $code, 'company_id' => $company->id],
            [
                'name' => $name,
                'decimal_places' => 2,
                'reporting_unit' => null,
                'non_accredited' => 0,
                'non_detectable' => 0,
                'show_on_report' => 1,
                'active' => 1,
            ]
        );
    }

    private function upsertMethod(Company $company, string $methodName): AnalysisMethod
    {
        $methodName = trim($methodName);
        $code = Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]+/', '-', $methodName) ?? $methodName, 64, ''));

        $existing = AnalysisMethod::query()
            ->where('company_id', $company->id)
            ->where(function ($query) use ($methodName, $code): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($methodName)])
                    ->orWhere('code', $code);
            })
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'name' => $existing->name ?: $methodName,
                'active' => 1,
            ])->save();

            return $existing;
        }

        return AnalysisMethod::query()->updateOrCreate(
            ['code' => $code, 'company_id' => $company->id],
            ['name' => $methodName, 'active' => 1]
        );
    }

    private function upsertElement(
        AnalysisType $analysisType,
        Analyte $analyte,
        string $labSectionId,
        ?string $methodId,
    ): AnalysisElements {
        $element = AnalysisElements::query()->updateOrCreate(
            [
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => $analyte->id,
            ],
            [
                'lab_section_id' => $labSectionId,
                'method' => $methodId,
                'show_on_report' => 1,
                'active' => 1,
                'non_detectable' => 0,
                'non_accredited' => 0,
            ]
        );

        return $element;
    }

    private function analyteCode(string $name): string
    {
        $slug = Str::upper(Str::slug($name, '_'));
        $slug = $slug !== '' ? $slug : 'ANALYTE';

        return Str::limit('AMS_'.$slug, 64, '');
    }
}
