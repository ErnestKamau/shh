<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RootCauseMethod extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'root_cause_methods';

    protected $fillable = [
        'name',
        'code',
        'description',
        'template',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'template' => 'array',
    ];

    public function rootCauseAnalyses(): HasMany
    {
        return $this->hasMany(RootCauseAnalysis::class, 'root_cause_method_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
