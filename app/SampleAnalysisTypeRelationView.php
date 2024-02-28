<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CapturedResult;

class SampleAnalysisTypeRelationView extends Model
{
    protected $table="samples_to_analysis_relation_view";

    public function getCapturedResults(){
        return CapturedResult::where('sample_detail_id',$this->sample_detail_id)->where('sample_header_id',$this->batch_id)->where('analysis_type_id',$this->analysis_type_id)->join('analysis_elements as ae','ae.analysis_type_id','=','captured_results.analysis_type_id')->where('ae.analyte_id','captured_results.analyte_id')->where('ae.active',1)->distinct('id')->get();
    }
}
