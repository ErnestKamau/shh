<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Directorate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'directorates';

    protected $fillable = [
        'name',
        'code',
        'head_id',
        'section_head_user_id',
        'zone_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo('App\User', 'head_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo('App\Zone', 'zone_id');
    }

    /**
     * Supplementary zone links (the primary zone remains on `directorates.zone_id`).
     */
    public function additionalZones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'directorate_zone', 'directorate_id', 'zone_id')
            ->withTimestamps();
    }

    public function labs(): HasMany
    {
        return $this->hasMany('App\Lab', 'directorate_id');
    }

    public function sectionHeadUser(): BelongsTo
    {
        return $this->belongsTo('App\\User', 'section_head_user_id');
    }
}
