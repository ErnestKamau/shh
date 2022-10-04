<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Pricelist extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function items(){
		$analysis = AnalysisType::leftJoin('pricelist_items as pi', function($join){
			$join->on('pi.analysis_id', 'analysis_types.id');
			$join->where('pi.pricelist_id', $this->id);
		})
			->join('sample_types as st', 'st.id', 'analysis_types.sample_type_id')
			->selectRaw('pi.*, st.name as sample_type_name, st.id as sample_type_id, analysis_types.name as analysis_type_name, analysis_types.code as analysis_type_code, analysis_types.id as analysis_type_id');

		if($this->is_master !=1){
			$analysis = $analysis->where('pi.pricelist_id', $this->id);
		}

		return $analysis->orderBy('pi.sample_type_id', 'asc')->orderBy('pi.level', 'asc')->get();
	}

	public function items_by_sample_type(){
		$analysis = AnalysisType::leftJoin('pricelist_items as pi', function($join){
			$join->on('pi.analysis_id', 'analysis_types.id');
			$join->where('pi.pricelist_id', $this->id);
		})->join('sample_types as st', 'st.id', 'analysis_types.sample_type_id')
		->join('analysis_elements as ae', 'analysis_types.id', 'ae.analysis_type_id')
		->join('analytes as a2', 'a2.id', 'ae.analyte_id')
			->selectRaw('a2.code as analyte, pi.*, st.name as sample_type_name, st.id as sample_type_id, analysis_types.name as analysis_type_name, analysis_types.code as analysis_type_code, analysis_types.reporting_time, analysis_types.id as analysis_type_id');

		if($this->is_master !=1){
			$analysis = $analysis->where('pi.pricelist_id', $this->id);
		}

		$arr = array();
		$analytes = array();

		$analysis = $analysis->orderBy('pi.level', 'asc')->orderBy('st.name', 'asc')->get();

		foreach($analysis as $an){
			if(!isset($arr[$an->sample_type_name])){
				$arr[$an->sample_type_name] = array();
				$analytes[$an->sample_type_name] = array();
			}

			if(!isset($arr[$an->sample_type_name][$an->analysis_type_code])){
				$arr[$an->sample_type_name][$an->analysis_type_code] = $an;
				$analytes[$an->sample_type_name][$an->analysis_type_code] = array();
			}

			$analytes[$an->sample_type_name][$an->analysis_type_code][] = trim($an->analyte);
		}

		return array("analysis"=>$arr, "analytes"=>$analytes);
	}

	public function currency(){
		return \App\ModulePreConfigs::find($this->currency_id);
	}

	public function customers(){
		$customers = PricelistCustomer::join('crm_customers as cc', 'cc.id', 'pricelist_customers.customer_id')
			->where('pricelist_customers.pricelist_id', $this->id)
			->selectRaw('pricelist_customers.id, cc.name, cc.code, cc.email, cc.website, cc.telephone1 as phone')->orderBy('cc.name', 'asc')->get();

		return $customers;
	}
}
