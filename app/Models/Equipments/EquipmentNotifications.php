<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentNotifications extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_notification';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'frequency',
        'value',
        'next_date',
        'is_sent',
        'notification_type',
    ];
}
