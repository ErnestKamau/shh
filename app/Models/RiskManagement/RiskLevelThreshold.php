<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskLevelThreshold extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_level_thresholds';

    protected $fillable = [
        'risk_level',
        'min_rpn',
        'max_rpn',
        'color_code',
        'description',
        'order_index',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'min_rpn' => 'integer',
        'max_rpn' => 'integer',
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'company_id' => 'integer',
    ];

    /**
     * Scope for company filtering
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        });
    }

    /**
     * Scope for active thresholds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if RPN falls within this threshold
     */
    public function matchesRPN(int $rpn): bool
    {
        $minMatch = $this->min_rpn === null || $rpn >= $this->min_rpn;
        $maxMatch = $this->max_rpn === null || $rpn <= $this->max_rpn;
        return $minMatch && $maxMatch;
    }
}

