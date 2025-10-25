<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleDate extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    //
    protected $fillable = [
        'sample_header_id',
        'name',
        'date',
    ];
}
