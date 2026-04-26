<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentEvaluation extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'equipment_id',
        'evaluated_by',
        'evaluation_date',
        'physical_condition',
        'last_calibration_date',
        'last_calibration_status',
        'repair_cost_estimate',
        'replacement_cost_estimate',
        'impact_on_testing',
        'recommendation',
        'calibration_history_summary',
        'fault_report_reference',
        'evaluation_notes',
        'company_id',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'last_calibration_date' => 'date',
        'repair_cost_estimate' => 'decimal:2',
        'replacement_cost_estimate' => 'decimal:2',
        'calibration_history_summary' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the equipment this evaluation belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the user who performed the evaluation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Check if evaluation recommends disposal
     *
     * @return bool
     */
    public function recommendsDisposal(): bool
    {
        return $this->recommendation === 'dispose';
    }

    /**
     * Check if evaluation recommends repair
     *
     * @return bool
     */
    public function recommendsRepair(): bool
    {
        return $this->recommendation === 'repair';
    }

    /**
     * Check if evaluation is recent (within 6 months)
     *
     * @return bool
     */
    public function isRecent(): bool
    {
        return $this->evaluation_date->gt(now()->subMonths(6));
    }

    /**
     * Get cost comparison result
     *
     * @return string
     */
    public function getCostComparison(): string
    {
        if (!$this->repair_cost_estimate || !$this->replacement_cost_estimate) {
            return 'N/A';
        }

        $percentage = ($this->repair_cost_estimate / $this->replacement_cost_estimate) * 100;

        if ($percentage >= 70) {
            return 'Replace - Repair cost is ' . number_format($percentage, 0) . '% of replacement';
        } elseif ($percentage >= 50) {
            return 'Consider - Repair cost is ' . number_format($percentage, 0) . '% of replacement';
        } else {
            return 'Repair - More economical';
        }
    }

    /**
     * Scope to filter by equipment
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $equipmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    /**
     * Scope to filter by recommendation
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $recommendation
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecommendation($query, string $recommendation)
    {
        return $query->where('recommendation', $recommendation);
    }

    /**
     * Scope to filter by company
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}


