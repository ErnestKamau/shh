<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleAnalysisDates extends Model
{
    protected $table = "sample_analysis_dates";
    
    protected $fillable = [
        'sample_detail_id',
        'sample_header_id',
        'start_analysis_date',
        'analysis_dates',
    ];
}
