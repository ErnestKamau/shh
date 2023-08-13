<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public $fillable = ['sample_code','sample_header_id','disposal_date'];
  public function analysis()
	{
		$analysisIDs = explode(",", $this->analysis_type_id);
		$analysis = array();

		foreach($analysisIDs as $id){
			if(trim($id)!=""){
				$analysis[] = AnalysisType::find($id);
			}
		}

		return $analysis;
	}

	public function labs(){
		$analysisTypes = $this->analysis();
		$labs = array();
		if(sizeof($analysisTypes) > 0) {
			foreach($analysisTypes as $a){
				$labs[] = array($a->lab->code, $a->lab->name);
			}
		}
		

		return $labs;
	}

	public function sample_point()
	{
		// return \App\Models\CRM\SamplePoint::find($this->sample_point_id);
		return $this->belongsTo('App\Models\CRM\SamplePoint', 'sample_point_id');
	}

	public function captured_results()
	{
		return $this->hasMany('App\CapturedResult');
	}

	public function product()
	{
		// return \App\Models\CRM\CompanyProduct::find($this->company_product_id);
		return $this->belongsTo('App\Models\CRM\CompanyProduct', 'company_product_id');
	}
	public function getAnalysisRelation(){
		return implode(', ',SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->pluck('analysis_type_name')->toArray() ?? []);
	}
}
