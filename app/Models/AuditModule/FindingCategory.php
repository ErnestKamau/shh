<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FindingCategory extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'finding_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'severity',
        'requires_capa',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_capa' => 'boolean',
    ];

    public function findings(): HasMany
    {
        return $this->hasMany(AuditModuleFinding::class, 'finding_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequiresCapa($query)
    {
        return $query->where('requires_capa', true);
    }
}
