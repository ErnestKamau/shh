<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class LabStockMovement extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_stock_movement';
}
