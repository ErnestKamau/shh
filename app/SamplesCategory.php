<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SamplesCategory extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'samples_by_category';

    public function getSampleByAnalysisType(){
        return SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->orderBy('analysis_level','ASC')->get();
    }
    public function getAccredittedStatus(){
        // $total = CapturedResult::where('sample_detail_id',$this->id)->count();
        return CapturedResult::where('sample_detail_id',$this->id)->where('analyte_status_contracted',0)->count();
       
    }
    public function getAnalysisRelation(){
		return implode(', ',SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->pluck('analysis_type_name')->toArray() ?? []);
	}
}
