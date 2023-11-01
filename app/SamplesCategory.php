<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SamplesCategory extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'samples_by_category';
    protected $appends = ['analysisTypeNames'];

    public function getSampleByAnalysisType(){
        return SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->orderBy('analysis_level','ASC')->get();
    }
    public function getAccredittedStatus(){
        // $total = CapturedResult::where('sample_detail_id',$this->id)->count();
        return CapturedResult::where('sample_detail_id',$this->id)->where('analyte_status_contracted',0)->count();
       
    }
    public function getAnalysisRelation(){
		return implode(', ',array_unique(SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->pluck('analysis_type_name')->toArray()) ?? []);
	}
    public function getAnalysisTypeNamesAttribute(){
		return implode(', ',array_unique(SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->pluck('analysis_type_name')->toArray() ?? []));
	}
    public function getAcredditedStatus(){
        $allCapturedResultsCount = CapturedResult::where('sample_detail_id',$this->id)->where('sample_header_id',$this->sample_header_id)->get()->count();
            $isAccreditedCount = CapturedResult::where('sample_detail_id',$this->id)->where('sample_header_id',$this->sample_header_id)->where('analyte_accredited',1)->get()->count();
            return $isAccreditedCount >= $allCapturedResultsCount/2 ? 1 : 0;
    }
}
