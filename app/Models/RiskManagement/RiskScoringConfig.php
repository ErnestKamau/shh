<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskScoringConfig extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_scoring_configs';

    protected $fillable = [
        'name',
        'code',
        'scoring_method',
        'formula',
        'max_likelihood_score',
        'max_severity_score',
        'description',
        'is_default',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'max_likelihood_score' => 'integer',
        'max_severity_score' => 'integer',
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
     * Scope for active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default config
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Calculate RPN based on scoring method
     */
    public function calculateRPN(int $likelihood, int $severity): int
    {
        switch ($this->scoring_method) {
            case 'multiplicative':
                return $likelihood * $severity;
            case 'additive':
                return $likelihood + $severity;
            case 'custom':
                // For custom formulas, you would evaluate the formula string
                // This is a simplified version - in production, use a proper formula parser
                return $likelihood * $severity; // Default fallback
            default:
                return $likelihood * $severity;
        }
    }

    /**
     * Get default scoring config
     */
    public static function getDefault($companyId = null): ?self
    {
        $companyId = $companyId ?? getUserCompany() ?? 0;
        return static::forCompany($companyId)
            ->active()
            ->default()
            ->first();
    }
}

