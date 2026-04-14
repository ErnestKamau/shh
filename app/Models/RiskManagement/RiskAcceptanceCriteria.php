<?php

namespace App\Models\RiskManagement;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskAcceptanceCriteria extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_acceptance_criteria';

    protected $fillable = [
        'name',
        'code',
        'threshold_rpn',
        'criteria_description',
        'requires_treatment_plan',
        'requires_monitoring',
        'can_skip_treatment',
        'applicable_categories',
        'is_default',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'threshold_rpn' => 'integer',
        'requires_treatment_plan' => 'boolean',
        'requires_monitoring' => 'boolean',
        'can_skip_treatment' => 'boolean',
        'applicable_categories' => 'array',
        'is_default' => 'boolean',
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
     * Scope for active criteria
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default criteria
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get applicable acceptance criteria for a risk
     */
    public static function getApplicableCriteria(?int $categoryId = null, $companyId = null): ?self
    {
        $companyId = $companyId ?? getUserCompany() ?? 0;
        
        // First try to find category-specific criteria
        if ($categoryId) {
            $criteria = static::forCompany($companyId)
                ->active()
                ->whereJsonContains('applicable_categories', $categoryId)
                ->first();
            
            if ($criteria) {
                return $criteria;
            }
        }
        
        // Fall back to default criteria
        return static::forCompany($companyId)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Check if RPN is acceptable
     */
    public function isAcceptable(int $rpn): bool
    {
        return $rpn <= $this->threshold_rpn;
    }
}

