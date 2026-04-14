<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapaPriority extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'capa_priorities';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color_code',
        'priority_level',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority_level' => 'integer',
    ];

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'priority_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_level', 'asc');
    }
}














