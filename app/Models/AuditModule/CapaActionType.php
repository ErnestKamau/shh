<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapaActionType extends Model
{
    use SoftDeletes;

    protected $table = 'capa_action_types';

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

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'action_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}














