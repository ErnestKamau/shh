<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Model;

class TatCaptured extends Model
{
    protected $table = "tat_captured";

    protected $fillable = ['captured_result_id','analysis_type_id','analyte_id','sample_type_id','result','analyst_id','tat_overdue_days','tat_date','finished_date','is_complete','sample_header_id'];
}
