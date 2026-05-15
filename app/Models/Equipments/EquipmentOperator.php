<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentOperator extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'equipment_operators';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'equipment_id',
    ];

    public function operator()
    {
        return \App\User::find($this->user_id);
    }
}
