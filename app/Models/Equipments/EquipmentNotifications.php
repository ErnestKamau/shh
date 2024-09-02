<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentNotifications extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "equipment_notification";
}
