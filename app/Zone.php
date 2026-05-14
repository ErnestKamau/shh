<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'module',
        'inventory_location_id',
        'section_head_user_id',
        'is_hq_zone',
    ];

    protected $casts = [
        'is_hq_zone' => 'boolean',
    ];

    public function sectionHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'section_head_user_id');
    }

    public function directorates(): HasMany
    {
        return $this->hasMany(Directorate::class, 'zone_id');
    }

    /**
     * Directorates linked through `directorate_zone` in addition to `directorates.zone_id`.
     */
    public function directoratesViaAdditionalZonePivot(): BelongsToMany
    {
        return $this->belongsToMany(Directorate::class, 'directorate_zone', 'zone_id', 'directorate_id')
            ->withTimestamps();
    }
}
