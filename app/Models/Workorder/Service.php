<?php

namespace App\Models\Workorder;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class Service extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    // protected $table = 'work_orders';
}
