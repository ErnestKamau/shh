<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskReviewFrequency extends Model
{
    use SoftDeletes;

    protected $table = 'risk_review_frequencies';

    protected $fillable = [
        'risk_level',
        'frequency_days',
        'frequency_label',
        'description',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'frequency_days' => 'integer',
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
     * Scope for active frequencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get review frequency for a risk level
     */
    public static function getFrequencyForLevel(string $riskLevel, $companyId = null): ?int
    {
        $frequency = static::forCompany($companyId)
            ->active()
            ->where('risk_level', $riskLevel)
            ->first();
        
        return $frequency ? $frequency->frequency_days : null;
    }
}

