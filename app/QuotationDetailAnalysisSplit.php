<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotationDetailAnalysisSplit extends Model
{
    protected $table = "quotation_details_analysis_type";
    protected $fillable= ['quotation_detail_id','analysis_type_id'];
}
