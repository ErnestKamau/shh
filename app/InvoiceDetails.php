<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\AnalysisType;

class InvoiceDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    
    public function analysisType(){
        return $this->belongsTo(AnalysisType::class,'analysis_type');
    }
    public function getZohoID(){
        $analysis = AnalysisType::find($this->analysis_type);
        $sample_type_category = SampleTypeCategory::find($analysis->sample_type()->sample_type_category);
        return $sample_type_category;

    }
}
