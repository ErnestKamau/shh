<?php

namespace App\Services\ImportVersioning;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Exports\Templates\FilledExcelExport;
use App\Factories\BulkImportTemplateFactory;
use App\SampleCondition;
use App\SampleType;
use App\SampleTypeCategory;
use App\StandardAnalytes;
use App\Standards;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabMasterCurrentDataExporter
{
    public function __construct(
        private readonly BulkImportTemplateFactory $templateFactory,
    ) {}

    public function download(string $module, string $formType, ?string $companyId): StreamedResponse|BinaryFileResponse
    {
        [$headings, $rows] = $this->build($module, $formType, $companyId);
        $filename = "{$module}_{$formType}_current_".now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new FilledExcelExport($headings, $rows), $filename);
    }

    /**
     * Store current data Excel to the public disk and return the relative path.
     */
    public function storeToPath(string $module, string $formType, ?string $companyId, string $relativePath): string
    {
        [$headings, $rows] = $this->build($module, $formType, $companyId);
        Excel::store(new FilledExcelExport($headings, $rows), $relativePath, 'public');

        return $relativePath;
    }

    /**
     * @return array{0: list<string>, 1: list<list<mixed>>}
     */
    public function build(string $module, string $formType, ?string $companyId): array
    {
        $definition = $this->templateFactory->getTemplateDefinition($module, $formType);
        $rawHeaders = $definition['headers'] ?? [];
        $headings = array_map(
            static fn ($header): string => str_replace('*', '', (string) $header),
            $rawHeaders,
        );

        if (! in_array('Action', $headings, true)) {
            $headings[] = 'Action';
        }

        $rows = match ($formType) {
            'analyte' => $this->analyteRows($companyId),
            'sample_type_category' => $this->sampleTypeCategoryRows($companyId),
            'sample_type' => $this->sampleTypeRows($companyId),
            'analysis_type' => $this->analysisTypeRows($companyId),
            'sample_condition' => $this->sampleConditionRows($companyId),
            'analysis_method' => $this->analysisMethodRows($companyId),
            'standard' => $this->standardRows($companyId),
            'amspec_parameters', 'analysis_elements' => $this->amspecParameterRows($companyId),
            default => [],
        };

        return [$headings, $this->alignRowsToHeadings($headings, $rows, $formType)];
    }

    /**
     * @param  list<string>  $headings
     * @param  list<array<string, mixed>>  $assocRows
     * @return list<list<mixed>>
     */
    protected function alignRowsToHeadings(array $headings, array $assocRows, string $formType): array
    {
        $keyMap = $this->headingKeyMap($formType);

        return array_map(function (array $row) use ($headings, $keyMap): array {
            $line = [];
            foreach ($headings as $heading) {
                $key = $keyMap[$heading] ?? strtolower(str_replace(' ', '_', $heading));
                $line[] = $row[$key] ?? $row[$heading] ?? '';
            }

            return $line;
        }, $assocRows);
    }

    /**
     * @return array<string, string>
     */
    protected function headingKeyMap(string $formType): array
    {
        return match ($formType) {
            'analyte' => [
                'code' => 'code',
                'name' => 'name',
                'decimal_places' => 'decimal_places',
                'reporting_symbol' => 'reporting_symbol',
                'reporting_unit' => 'reporting_unit',
                'equipment_code' => 'equipment_code',
                'equipment' => 'equipment',
                'reference_method' => 'reference_method',
                'test_method_sop' => 'test_method_sop',
                'method_version' => 'method_version',
                'non_detectable' => 'non_detectable',
                'non_accredited' => 'non_accredited',
                'Action' => 'action',
            ],
            'sample_type_category' => [
                'category_name' => 'category_name',
                'active' => 'active',
                'Action' => 'action',
            ],
            'sample_type' => [
                'category_name' => 'category_name',
                'sample_type_code' => 'sample_type_code',
                'sample_type_name' => 'sample_type_name',
                'active' => 'active',
                'Action' => 'action',
            ],
            'analysis_type' => [
                'sample_type_name' => 'sample_type_name',
                'sample_type_code' => 'sample_type_code',
                'analysis_type_code' => 'analysis_type_code',
                'analysis_type_name' => 'analysis_type_name',
                'active' => 'active',
                'Action' => 'action',
            ],
            'sample_condition' => [
                'sample_type_code' => 'sample_type_code',
                'condition_name' => 'condition_name',
                'short_name' => 'short_name',
                'reporting_time' => 'reporting_time',
                'Action' => 'action',
            ],
            'analysis_method' => [
                'Method Name' => 'method_name',
                'Method Code' => 'method_code',
                'Description' => 'description',
                'Method Category' => 'method_category',
                'Reference Method' => 'reference_method',
                'Method Version' => 'method_version',
                'Active' => 'active',
                'Action' => 'action',
            ],
            'standard' => [
                'standard_code' => 'standard_code',
                'standard_name' => 'standard_name',
                'main_standard' => 'main_standard',
                'is_qc_standard' => 'is_qc_standard',
                'qc_type' => 'qc_type',
                'analyte_code' => 'analyte_code',
                'standard_value_type' => 'standard_value_type',
                'standard_low' => 'standard_low',
                'standard_high' => 'standard_high',
                'standard_value' => 'standard_value',
                'standard_matrix_operator' => 'standard_matrix_operator',
                'Action' => 'action',
            ],
            'amspec_parameters', 'analysis_elements' => [
                'sample_type_code' => 'sample_type_code',
                'sample_type_name' => 'sample_type_name',
                'analysis_type_code' => 'analysis_type_code',
                'analysis_type_name' => 'analysis_type_name',
                'lab_name' => 'lab_name',
                'lab_section_name' => 'lab_section_name',
                'analyte_name' => 'analyte_name',
                'reporting_unit' => 'reporting_unit',
                'decimal_places' => 'decimal_places',
                'lod' => 'lod',
                'loq' => 'loq',
                'non_accredited' => 'non_accredited',
                'equipment' => 'equipment',
                'equipment_number' => 'equipment_number',
                'method' => 'method',
                'Action' => 'action',
            ],
            default => ['Action' => 'action'],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function analyteRows(?string $companyId): array
    {
        $query = Analyte::query()->with(['equipmentItems', 'analysisMethods']);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('code')->get()->map(function (Analyte $analyte): array {
            $equipment = $analyte->equipmentItems;
            $methods = $analyte->analysisMethods;
            $references = $methods->filter(static fn ($m) => (int) ($m->is_ltm ?? 0) === 0)->pluck('name')->filter()->implode(', ');
            $ltms = $methods->filter(static fn ($m) => (int) ($m->is_ltm ?? 0) === 1)->pluck('name')->filter()->implode(', ');

            return [
                'code' => (string) ($analyte->code ?? ''),
                'name' => (string) ($analyte->name ?? ''),
                'decimal_places' => (string) ($analyte->decimal_places ?? ''),
                'reporting_symbol' => (string) ($analyte->reporting_symbol ?? ''),
                'reporting_unit' => (string) ($analyte->reporting_unit ?? ''),
                'equipment_code' => $equipment->pluck('equipment_number')->filter()->implode(', '),
                'equipment' => $equipment->pluck('name')->filter()->implode(', '),
                'reference_method' => $references,
                'test_method_sop' => $ltms,
                'method_version' => '',
                'non_detectable' => (string) (int) ($analyte->non_detectable ?? 0),
                'non_accredited' => (string) (int) ($analyte->non_accredited ?? 0),
                'action' => '',
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function sampleTypeCategoryRows(?string $companyId): array
    {
        $query = SampleTypeCategory::query();

        return $query->orderBy('sample_type_category')->get()->map(static fn (SampleTypeCategory $category): array => [
            'category_name' => (string) ($category->sample_type_category ?? ''),
            'active' => (string) (int) ($category->active ?? 1),
            'action' => '',
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function sampleTypeRows(?string $companyId): array
    {
        $query = SampleType::query()->with('sampleTypeCategory');
        if ($companyId && Schema::hasColumn((new SampleType)->getTable(), 'company_id')) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('name')->get()->map(static fn (SampleType $type): array => [
            'category_name' => (string) ($type->sampleTypeCategory?->sample_type_category ?? ''),
            'sample_type_code' => (string) ($type->code ?? ''),
            'sample_type_name' => (string) ($type->name ?? ''),
            'active' => (string) (int) ($type->active ?? 1),
            'action' => '',
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function analysisTypeRows(?string $companyId): array
    {
        $query = AnalysisType::query()->with('sample_type');
        if ($companyId && Schema::hasColumn((new AnalysisType)->getTable(), 'company_id')) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('name')->get()->map(static function (AnalysisType $type): array {
            $sampleType = $type->sample_type;

            return [
                'sample_type_name' => (string) ($sampleType->name ?? ''),
                'sample_type_code' => (string) ($sampleType->code ?? ''),
                'analysis_type_code' => (string) ($type->code ?? ''),
                'analysis_type_name' => (string) ($type->name ?? ''),
                'active' => (string) (int) ($type->active ?? 1),
                'action' => '',
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function sampleConditionRows(?string $companyId): array
    {
        $query = SampleCondition::query()->with('sampleType');
        if ($companyId && Schema::hasColumn((new SampleCondition)->getTable(), 'company_id')) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('name')->get()->map(static fn (SampleCondition $condition): array => [
            'sample_type_code' => (string) ($condition->sampleType?->code ?? $condition->sample_type_id ?? ''),
            'condition_name' => (string) ($condition->name ?? ''),
            'short_name' => (string) ($condition->short_name ?? ''),
            'reporting_time' => (string) ($condition->reporting_time ?? ''),
            'action' => '',
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function analysisMethodRows(?string $companyId): array
    {
        $query = AnalysisMethod::query()->with('referencemethod');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('name')->get()->map(static function (AnalysisMethod $method): array {
            $category = 'Reference Method';
            if ((int) ($method->is_ltm ?? 0) === 1) {
                $category = 'Laboratory Test Method';
            } elseif ((int) ($method->is_sampling_method ?? 0) === 1) {
                $category = 'Sampling Method';
            }

            return [
                'method_name' => (string) ($method->name ?? ''),
                'method_code' => (string) ($method->code ?? ''),
                'description' => (string) ($method->description ?? ''),
                'method_category' => $category,
                'reference_method' => (string) ($method->referencemethod?->name ?? ''),
                'method_version' => '',
                'active' => ((int) ($method->active ?? 1) === 1) ? 'Yes' : 'No',
                'action' => '',
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function standardRows(?string $companyId): array
    {
        $query = Standards::query()->with(['standardAnalytes.analyte', 'standardAnalytes.standardValue']);

        $rows = [];
        foreach ($query->orderBy('code')->get() as $standard) {
            $values = $standard->standardAnalytes ?? collect();
            if ($values->isEmpty()) {
                $rows[] = [
                    'standard_code' => (string) ($standard->code ?? ''),
                    'standard_name' => (string) ($standard->name ?? ''),
                    'main_standard' => (string) (int) ($standard->main_standard ?? 0),
                    'is_qc_standard' => (string) (int) ($standard->is_qc_standard ?? 0),
                    'qc_type' => (string) ($standard->qc_type_id ?? ''),
                    'analyte_code' => '',
                    'standard_value_type' => '',
                    'standard_low' => '',
                    'standard_high' => '',
                    'standard_value' => '',
                    'standard_matrix_operator' => '',
                    'action' => '',
                ];
                continue;
            }

            foreach ($values as $index => $value) {
                /** @var StandardAnalytes $value */
                $rows[] = [
                    'standard_code' => (string) ($standard->code ?? ''),
                    'standard_name' => $index === 0 ? (string) ($standard->name ?? '') : '',
                    'main_standard' => $index === 0 ? (string) (int) ($standard->main_standard ?? 0) : '',
                    'is_qc_standard' => $index === 0 ? (string) (int) ($standard->is_qc_standard ?? 0) : '',
                    'qc_type' => $index === 0 ? (string) ($standard->qc_type_id ?? '') : '',
                    'analyte_code' => (string) ($value->analyte?->code ?? ''),
                    'standard_value_type' => (string) ($value->standardValue?->code ?? $value->standard_value_type ?? ''),
                    'standard_low' => (string) ($value->low ?? ''),
                    'standard_high' => (string) ($value->high ?? ''),
                    'standard_value' => (string) ($value->matrix_value ?? $value->expected_value ?? ''),
                    'standard_matrix_operator' => (string) ($value->matrix_operator ?? ''),
                    'action' => '',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function amspecParameterRows(?string $companyId): array
    {
        $query = AnalysisElements::query()
            ->with(['analyte', 'analysis_type.sample_type', 'labSection.lab', 'equipment', 'mmethod']);

        return $query->orderBy('created_at')->limit(5000)->get()->map(static function (AnalysisElements $element): array {
            $analysisType = $element->analysis_type;
            $sampleType = $analysisType?->sample_type;
            $labSection = $element->labSection;

            return [
                'sample_type_code' => (string) ($sampleType->code ?? ''),
                'sample_type_name' => (string) ($sampleType->name ?? ''),
                'analysis_type_code' => (string) ($analysisType->code ?? ''),
                'analysis_type_name' => (string) ($analysisType->name ?? ''),
                'lab_name' => (string) ($labSection?->lab?->name ?? ''),
                'lab_section_name' => (string) ($labSection?->name ?? ''),
                'analyte_name' => (string) ($element->analyte?->name ?? ''),
                'reporting_unit' => (string) ($element->reporting_unit ?? $element->analyte?->reporting_unit ?? ''),
                'decimal_places' => (string) ($element->decimal_places ?? $element->analyte?->decimal_places ?? ''),
                'lod' => (string) ($element->lod ?? ''),
                'loq' => (string) ($element->hod ?? ''),
                'non_accredited' => (string) (int) ($element->non_accredited ?? 0),
                'equipment' => (string) ($element->equipment?->name ?? ''),
                'equipment_number' => (string) ($element->equipment?->equipment_number ?? ''),
                'method' => (string) ($element->mmethod?->name ?? ''),
                'action' => '',
            ];
        })->all();
    }
}
