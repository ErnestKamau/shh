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
}
