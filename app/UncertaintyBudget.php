<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class UncertaintyBudget extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'analyte_id',
        'method_ids',
        'coverage_factor_k',
        'confidence_level',
        'combined_standard_uncertainty',
        'expanded_uncertainty',
        'version_number',
        'created_by',
        'company_id',
        'active'
    ];

    protected $casts = [
        'coverage_factor_k' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'combined_standard_uncertainty' => 'decimal:6',
        'expanded_uncertainty' => 'decimal:6',
        'active' => 'boolean'
    ];

    // Relationships
    public function analyte()
    {
        return $this->belongsTo(Analyte::class);
    }

    // Method relationship removed - now using method_ids column with comma-separated values

    public function sources()
    {
        return $this->hasMany(UncertaintySource::class);
    }

    public function uncertaintySources()
    {
        return $this->hasMany(UncertaintySource::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getAnalyteNameAttribute()
    {
        return $this->analyte ? $this->analyte->name : 'N/A';
    }

    public function getAnalyteCodeAttribute()
    {
        return $this->analyte ? $this->analyte->code : 'N/A';
    }

    public function getMethodNameAttribute()
    {
        if ($this->method_ids) {
            // Handle comma-separated method IDs
            $methodIds = explode(',', $this->method_ids);
            $methodNames = [];
            foreach ($methodIds as $id) {
                $method = AnalysisMethod::find(trim($id));
                if ($method) {
                    $methodNames[] = $method->name;
                }
            }
            return implode(', ', $methodNames);
        }
        return 'N/A';
    }

    public function getFormattedCombinedUncertaintyAttribute()
    {
        return $this->combined_standard_uncertainty ? number_format($this->combined_standard_uncertainty, 6) : 'N/A';
    }

    public function getFormattedExpandedUncertaintyAttribute()
    {
        return $this->expanded_uncertainty ? number_format($this->expanded_uncertainty, 6) : 'N/A';
    }

    // Methods
    public function calculateUncertainties()
    {
        $sources = $this->sources;
        
        if ($sources->isEmpty()) {
            $this->update([
                'combined_standard_uncertainty' => null,
                'expanded_uncertainty' => null
            ]);
            return;
        }

        // Calculate combined standard uncertainty: uc = √(Σ(ui × ci)²)
        $sumOfSquares = 0;
        $sourceUpdates = [];
        
        foreach ($sources as $source) {
            $contribution = $source->std_uncertainty_value * $source->sensitivity_coefficient;
            $sumOfSquares += pow($contribution, 2);
            
            // Collect updates to avoid individual database calls
            $sourceUpdates[] = [
                'id' => $source->id,
                'contribution_value' => $contribution
            ];
        }

        // Batch update all source contribution values
        if (!empty($sourceUpdates)) {
            foreach ($sourceUpdates as $update) {
                UncertaintySource::where('id', $update['id'])
                    ->update(['contribution_value' => $update['contribution_value']]);
            }
        }

        $combinedStandardUncertainty = sqrt($sumOfSquares);
        $expandedUncertainty = $combinedStandardUncertainty * $this->coverage_factor_k;

        $this->update([
            'combined_standard_uncertainty' => $combinedStandardUncertainty,
            'expanded_uncertainty' => $expandedUncertainty
        ]);
    }

    public function getResultWithUncertainty($result)
    {
        if (!$this->expanded_uncertainty) {
            return $result;
        }

        return sprintf(
            '%s ± %s (k=%s, %s%% confidence)',
            $result,
            number_format($this->expanded_uncertainty, 6),
            $this->coverage_factor_k,
            $this->confidence_level
        );
    }

}
