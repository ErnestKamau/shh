<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SampleAnalysisTypeRelation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "sample_analysis_type_relation";
}
