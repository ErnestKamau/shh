<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskLevel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_levels';

    protected $fillable = [
        'name',
        'code',
        'description',
        'severity_score',
        'color_code',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'severity_score' => 'integer',
    ];

    public function findings(): HasMany
    {
        return $this->hasMany(AuditModuleFinding::class, 'risk_level_id');
    }

    public function nonConformances(): HasMany
    {
        return $this->hasMany(NonConformance::class, 'risk_level_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('severity_score', 'asc');
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('company_id', 0); // Include global/default records
        });
    }
}
