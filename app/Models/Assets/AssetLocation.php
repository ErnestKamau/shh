<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class AssetLocation extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['location_code', 'name', 'is_active', 'lab_id'];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(\App\Lab::class, 'lab_id');
    }
}
