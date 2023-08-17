<?php

namespace App\Imports;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\Analyte;
use App\ReportingUnit;
use Illuminate\Support\Str; // Import the Str class to modify strings
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class ImportAnalysisElements implements ToModel, WithHeadingRow
{
	use Importable;

	private $analysisType;

	public function __construct($analysis_type)
	{
		$this->analysisType = $analysis_type;
	}

	public function model(array $row)
	{
		// Modify the 'method' column value by converting it to uppercase
		$mname = trim(ucwords($row['method']));
		$method = AnalysisMethod::orWhere('name', $mname)
			->orWhere('code', $mname)->first();

		if(!isset($method->id)){
			$method = ucwords($mname);
			$method = AnalysisMethod::create([
				'name' => $mname, 'code' => $mname, 'description' => $mname, 'company_id' => getUserCompany(), 'active' => 1
			]);
		}

		$rname = ucwords(trim($row['reporting_unit']));
		$reporting_unit = ReportingUnit::where('name', strtolower(trim($row['reporting_unit'])))->first();

		if(!isset($reporting_unit->id)){
			$rname = ucwords($rname);
			$reporting_unit = ReportingUnit::create(['name'=>$rname, 'active'=>1]);
		}

		// Other columns remain as they are
		$parameter = ucwords(trim($row['parameter']));

		$analyte = Analyte::orWhere('name', $parameter)->orWhere('code', $parameter)->first();

		$nonAccredited = $row['accredited'];

		if(!isset($analyte->id)){
			$analyte = Analyte::create([
				'code'=>ucwords($parameter), 
				'name'=>ucwords($parameter), 
				'decimal_places'=>2, 
				'company_id' => getUserCompany(), 
				'method' => $method->id, 
				'reporting_unit' => $reporting_unit->name, 
				'non_accredited' => $nonAccredited
			]);
		}

		// You can modify other columns similarly if needed

		// Create a new AnalysisElements instance and fill the data
		$analysisElement = AnalysisElements::where('analysis_type_id', $this->analysisType->id)
			->where('analyte_id', $analyte->id)->first();
		if(!isset($analysisElement->id)){
			$analysisElement = new AnalysisElements([
				'method' => $method->id,
				'reporting_unit' => $reporting_unit->name,
				'analyte_id' => $analyte->id,
				'company_id' => getUserCompany(), 
				'non_accredited' => $nonAccredited,
				'lab_section_id' => $this->analysisType->lab_id,
				'analysis_type_id' => $this->analysisType->id
			]);
		}
		else{
			$analysisElement->update([
				'method' => $method->id,
				'reporting_unit' => $reporting_unit->name,
				'analyte_id' => $analyte->id,
				'non_accredited' => $nonAccredited,
				'company_id' => getUserCompany(), 
				'lab_section_id' => $this->analysisType->lab_id,
				'analysis_type_id' => $this->analysisType->id
			]);
		}

		return $analysisElement;
	}
}
