<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceStatus extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'compliance_statuses';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color_code',
        'badge_class',
        'order_index',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc');
    }
}
