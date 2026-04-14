<?php

namespace App\Models\Workorder;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStatusHistory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    //
}
