<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditType extends Model
{
    use SoftDeletes;

    protected $table = 'audit_types';

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

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'audit_type_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(AuditChecklist::class, 'audit_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
