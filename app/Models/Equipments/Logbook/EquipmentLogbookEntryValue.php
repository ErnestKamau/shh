<?php

namespace App\Models\Equipments\Logbook;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentLogbookEntryValue extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'entry_id',
        'column_id',
        'value',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(EquipmentLogbookEntry::class, 'entry_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(EquipmentLogbookColumn::class, 'column_id');
    }
}
