<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditType extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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

    public function auditModuleAudits(): HasMany
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
}
