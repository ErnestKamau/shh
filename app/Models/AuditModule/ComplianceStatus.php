<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceStatus extends Model implements Auditable
{
    use HasUuids;

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

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('company_id', 0); // Include global/default records
        });
    }
}
