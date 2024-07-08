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
}
