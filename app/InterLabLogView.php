<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class InterLabLogView extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table ="samples_inter_lab_log_view";
}
