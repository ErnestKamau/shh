<?php

namespace App\Imports;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\Analyte;
use App\ReportingUnit;
use Illuminate\Support\Str;
use App\Imports\BaseImporter;

class ImportAnalysisElements extends BaseImporter
{
	private $analysisType;

	public function __construct($analysisType, $batch = null)
	{
        parent::__construct($batch);
		$this->analysisType = $analysisType;
        
        if ($this->batch->module === 'generic') {
            $this->batch->update(['module' => 'lab', 'form_type' => 'analysis_elements']);
        }
	}

    protected function validateRow(array $row): array
    {
        $errors = [];
        if (empty($this->fuzzyGet($row, ['parameter', 'analyte', 'name']))) {
            $errors[] = 'Parameter name is required';
        }
        if (empty($this->fuzzyGet($row, ['method']))) {
            $errors[] = 'Method is required';
        }
        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $mname = trim(ucwords($this->fuzzyGet($row, ['method'])));
        $method = AnalysisMethod::where('name', $mname)->orWhere('code', $mname)->first();
        if (!$method) {
            $method = AnalysisMethod::create([
                'name' => $mname, 'code' => $mname, 'description' => $mname, 
                'company_id' => getUserCompany(), 'active' => 1
            ]);
        }

        $ltmethodname = trim(ucwords($this->fuzzyGet($row, ['ltmethod', 'ltm_method'])));
        $ltmethod = null;
        if ($ltmethodname) {
            $ltmethod = AnalysisMethod::where('is_ltm', 1)
                        ->where(function ($query) use ($ltmethodname) {
                            $query->where('name', $ltmethodname)->orWhere('code', $ltmethodname);
                        })->first();
            if (!$ltmethod) {
                $ltmethod = AnalysisMethod::create([
                    'name' => $ltmethodname, 'code' => $ltmethodname, 'description' => $ltmethodname, 
                    'company_id' => getUserCompany(), 'active' => 1, 'is_ltm' => 1
                ]);
            }
        }

        $rname = ucwords(trim($this->fuzzyGet($row, ['reporting_unit', 'unit'])));
        $reporting_unit = ReportingUnit::where('name', strtolower($rname))->first();
        if (!$reporting_unit && $rname) {
            $reporting_unit = ReportingUnit::create(['name' => $rname, 'active' => 1]);
        }

        $parameter = ucwords(trim($this->fuzzyGet($row, ['parameter', 'analyte', 'name'])));
        $analyte = Analyte::where('name', $parameter)->orWhere('code', $parameter)->first();
        if (!$analyte) {
            $analyte = Analyte::create([
                'code' => $parameter, 
                'name' => $parameter, 
                'decimal_places' => 2, 
                'company_id' => getUserCompany(), 
                'method' => $method->id, 
                'reporting_unit' => $reporting_unit->name ?? null, 
                'non_accredited' => $this->fuzzyGet($row, ['accredited', 'is_accredited'], 0) == 'No' ? 1 : 0,
                'show_on_report' => (
                    $this->fuzzyGet($row, ['show_on_report', 'show_on_reports']) !== null &&
                    in_array(strtolower(trim((string)$this->fuzzyGet($row, ['show_on_report', 'show_on_reports']))), ['0', 'no', 'false', 'off'])
                ) ? 0 : 1,
            ]);
        }

        return [
            'method' => $method->id,
            'reporting_unit' => $reporting_unit->name ?? null,
            'analyte_id' => $analyte->id,
            'company_id' => getUserCompany(), 
            'non_accredited' => $this->fuzzyGet($row, ['accredited', 'is_accredited'], 0) == 'No' ? 1 : 0,
            'lab_section_id' => $this->analysisType->lab_id ?? $this->analysisType->lab_section_id,
            'analysis_type_id' => $this->analysisType->id,
            'ltm_method_id' => $ltmethod->id ?? null,
            'reporting_time' => $this->fuzzyGet($row, ['tat', 'reporting_time']),
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        AnalysisElements::updateOrCreate(
            [
                'analysis_type_id' => $transformedData['analysis_type_id'],
                'analyte_id' => $transformedData['analyte_id']
            ],
            $transformedData
        );

        $this->recordUpsert($transformedData['analyte_id'], 'updated');
        return true;
    }
}
