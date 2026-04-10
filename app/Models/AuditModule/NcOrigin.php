<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NcOrigin extends Model
{
    use SoftDeletes;

    protected $table = 'nc_origins';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function nonConformances(): HasMany
    {
        return $this->hasMany(NonConformance::class, 'origin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}














