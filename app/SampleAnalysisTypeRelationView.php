<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CapturedResult;

class SampleAnalysisTypeRelationView extends Model
{
    protected $table="samples_to_analysis_relation_view";

    public function getCapturedResults(){
        return CapturedResult::where('sample_detail_id',$this->sample_detail_id)->where('sample_header_id',$this->batch_id)->where('analysis_type_id',$this->analysis_type_id)->distinct('id')->get();
    }
}
