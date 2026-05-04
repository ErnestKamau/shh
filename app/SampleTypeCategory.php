<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleTypeCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "sample_type_categories";

    protected $fillable = [
        'sample_type_category',
        'active',
        'zoho_id',
    ];
}
