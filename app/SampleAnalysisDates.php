<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SampleAnalysisDates extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "sample_analysis_dates";
    
    protected $fillable = [
        'sample_detail_id',
        'sample_header_id',
        'start_analysis_date',
        'analysis_dates',
    ];
}
