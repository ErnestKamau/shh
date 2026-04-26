<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SampleImports extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'sample_imports';

    protected $fillable = ['short_code','material_status','variety_name','sample_no','no_of_sample','no_of_pots_plants','standard_tests','compartiment_lot','planting_week','species','results','sample_header_id','sample_condition'];
    

}
