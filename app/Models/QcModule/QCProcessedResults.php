<?php

namespace App\Models\QcModule;

use OwenIt\Auditing\Contracts\Auditable;

use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Models\QcModule\Data\QcResults;
use App\SampleType;
use Illuminate\Database\Eloquent\Model;

class QCProcessedResults extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "qc_processed_result";
    protected $guarded = ['id'];

    public function method(){
        return $this->belongsTo(AnalysisMethod::class,'method_id');
    }
    public function sampletype(){
        return $this->belongsTo(SampleType::class,'sample_type_id');
    }
    public function analysistype(){
        return $this->belongsTo(AnalysisType::class,'analysis_type_id');
    }
    public function analyte(){
        return $this->belongsTo(Analyte::class,'analyte_id');
    }
    public function results(){
        return $this->hasMany(QcResults::class,'analyte_processed_id');
    }
    public function getresultsarr(){
        return QcResults::where('analyte_processed_id',$this->id)->pluck('result','sample_detail_code')->toArray();
    }
}

