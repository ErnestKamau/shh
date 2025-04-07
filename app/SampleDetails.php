<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	// public $with = ['sample_detail_lab', 'captured_results'];
	protected $guarded = ['id'];
	public function getSampleHeader(){
		return SampleHeader::find($this->sample_header_id);
	}
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
		return $this->hasMany('App\CapturedResult', 'sample_detail_id');
	}

	public function product()
	{
		// return \App\Models\CRM\CompanyProduct::find($this->company_product_id);
		return $this->belongsTo('App\Models\CRM\CompanyProduct', 'company_product_id');
	}
	public function getAnalysisRelation(){
		return implode(', ',array_unique(SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->pluck('analysis_type_name')->toArray()) ?? []);
	}


	public function sample_detail_lab(){
		return $this->hasOne(SampleAnalysisTypeRelationView::class, 'sample_detail_id');
	}
	public function getAnalysisTestDone(){
		return implode(', ',array_unique(CapturedResult::where('sample_detail_id',$this->id)->where('sample_header_id',$this->sample_header_id)->pluck('analyte_code')->toArray()) ?? []);
	}
	public function targetDateRelation(){
		$target = SampleDate::where('sample_header_id',$this->sample_header_id)->where('name','Target Date')->first();
		return $target->date;
	}
	public function analyteNames(){
		return CapturedResult::where('sample_detail_id',$this->id)->pluck('analyte_code')->toArray();
	}
}
