<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Directorate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'directorates';

    protected $fillable = [
        'name',
        'code',
        'head_id',
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

    public function labs(): HasMany
    {
        return $this->hasMany('App\Lab', 'directorate_id');
    }
}
