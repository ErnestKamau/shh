<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class QuotationDetailAnalysisSplit extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "quotation_details_analysis_type";
    protected $fillable= ['quotation_detail_id','analysis_type_id'];
}
