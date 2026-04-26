<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuotationDetailAnalysisSplit extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = "quotation_details_analysis_type";
    protected $fillable= ['quotation_detail_id','analysis_type_id'];
}
