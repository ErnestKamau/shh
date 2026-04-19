<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UncertaintySource extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uncertainty_budget_id',
        'source_name',
        'type',
        'std_uncertainty_value',
        'sensitivity_coefficient',
        'contribution_value',
        'notes'
    ];

    protected $casts = [
        'std_uncertainty_value' => 'decimal:6',
        'sensitivity_coefficient' => 'decimal:6',
        'contribution_value' => 'decimal:6'
    ];

    // Relationships
    public function uncertaintyBudget()
    {
        return $this->belongsTo(UncertaintyBudget::class);
    }

    // Accessors
    public function getFormattedStdUncertaintyAttribute()
    {
        return number_format($this->std_uncertainty_value, 6);
    }

    public function getFormattedSensitivityCoefficientAttribute()
    {
        return number_format($this->sensitivity_coefficient, 6);
    }

    public function getFormattedContributionAttribute()
    {
        return $this->contribution_value ? number_format($this->contribution_value, 6) : 'N/A';
    }

    // Methods
    public function calculateContribution()
    {
        $contribution = $this->std_uncertainty_value * $this->sensitivity_coefficient;
        $this->update(['contribution_value' => $contribution]);
        
        // Note: Recalculation should be handled manually in controllers to avoid recursive loops
        
        return $contribution;
    }

    // Boot method removed to prevent recursive loops
    // Recalculation should be handled manually in controllers
}
