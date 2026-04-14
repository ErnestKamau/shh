<?php

namespace App\Models\RiskManagement;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_statuses';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color_code',
        'order_index',
        'workflow_step',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'workflow_step' => 'integer',
    ];

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class, 'status_id');
    }

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
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        });
    }

    public function scopeByWorkflowStep($query, $step)
    {
        return $query->where('workflow_step', $step);
    }
}


