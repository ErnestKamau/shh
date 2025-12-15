<?php

namespace App\Services\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentEvaluation;
use App\Models\Equipments\MaintainanceCalibrationLog;
use Carbon\Carbon;

class EquipmentEvaluationService
{
    /**
     * Create equipment evaluation report
     *
     * @param Equipment $equipment
     * @param array $data
     * @return EquipmentEvaluation
     */
    public function createEvaluation(Equipment $equipment, array $data): EquipmentEvaluation
    {
        // Auto-fill calibration history summary if not provided
        if (!isset($data['calibration_history_summary'])) {
            $data['calibration_history_summary'] = $this->getCalibrationHistory($equipment)->map(function ($log) {
                return [
                    'date' => $log->date->format('Y-m-d'),
                    'status' => 'completed', // You may have a status field
                    'notes' => $log->notes ?? '',
                ];
            })->toArray();
        }

        // Auto-fill last calibration info if not provided
        if (!isset($data['last_calibration_date'])) {
            $lastCalibration = $this->getLastCalibration($equipment);
            if ($lastCalibration) {
                $data['last_calibration_date'] = $lastCalibration->date;
            }
        }

        // Add company ID
        $data['company_id'] = $equipment->company_id;

        return EquipmentEvaluation::create($data);
    }

    /**
     * Check if equipment can be disposed based on evaluation
     *
     * @param Equipment $equipment
     * @return bool
     */
    public function canEquipmentBeDisposed(Equipment $equipment): bool
    {
        $latestEvaluation = $equipment->evaluations()->latest()->first();

        if (!$latestEvaluation) {
            return false; // Requires evaluation before disposal
        }

        // Evaluation must be recent (within 6 months)
        if (!$latestEvaluation->isRecent()) {
            return false;
        }

        // Must recommend disposal
        return $latestEvaluation->recommendsDisposal();
    }

    /**
     * Get calibration history for equipment
     *
     * @param Equipment $equipment
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getCalibrationHistory(Equipment $equipment, int $limit = 10)
    {
        return MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'calibration')
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get maintenance history for equipment
     *
     * @param Equipment $equipment
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getMaintenanceHistory(Equipment $equipment, int $limit = 10)
    {
        return MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'maintainance')
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get last calibration log
     *
     * @param Equipment $equipment
     * @return MaintainanceCalibrationLog|null
     */
    public function getLastCalibration(Equipment $equipment): ?MaintainanceCalibrationLog
    {
        return MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'calibration')
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Get failure history (failed calibrations)
     *
     * @param Equipment $equipment
     * @return \Illuminate\Support\Collection
     */
    public function getFailureHistory(Equipment $equipment)
    {
        // This would depend on your calibration log structure
        // Assuming there's a status or notes field indicating failure
        return MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'calibration')
            ->where(function($query) {
                $query->where('notes', 'LIKE', '%fail%')
                      ->orWhere('notes', 'LIKE', '%non-conform%')
                      ->orWhere('notes', 'LIKE', '%reject%');
            })
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Calculate repair vs replacement cost analysis
     *
     * @param Equipment $equipment
     * @param float $repairCost
     * @param float|null $replacementCost
     * @return array
     */
    public function calculateRepairVsReplacementCost(
        Equipment $equipment,
        float $repairCost,
        ?float $replacementCost = null
    ): array {
        // Use market value as replacement cost if not provided
        if (!$replacementCost && $equipment->market_value) {
            $replacementCost = (float) $equipment->market_value;
        }

        if (!$replacementCost) {
            return [
                'recommendation' => 'insufficient_data',
                'reason' => 'Replacement cost not available',
            ];
        }

        $percentage = ($repairCost / $replacementCost) * 100;

        if ($percentage >= 70) {
            return [
                'recommendation' => 'replace',
                'reason' => "Repair cost is {$percentage}% of replacement cost - not economical",
                'repair_cost' => $repairCost,
                'replacement_cost' => $replacementCost,
                'percentage' => $percentage,
            ];
        } elseif ($percentage >= 50) {
            return [
                'recommendation' => 'evaluate_further',
                'reason' => "Repair cost is {$percentage}% of replacement cost - marginal decision",
                'repair_cost' => $repairCost,
                'replacement_cost' => $replacementCost,
                'percentage' => $percentage,
            ];
        } else {
            return [
                'recommendation' => 'repair',
                'reason' => "Repair cost is {$percentage}% of replacement cost - economical",
                'repair_cost' => $repairCost,
                'replacement_cost' => $replacementCost,
                'percentage' => $percentage,
            ];
        }
    }

    /**
     * Generate evaluation PDF report (placeholder)
     *
     * @param EquipmentEvaluation $evaluation
     * @return string
     */
    public function generateEvaluationPDF(EquipmentEvaluation $evaluation): string
    {
        // This would use the same PDF service as disposal reports
        // For now, return a placeholder
        return '/storage/evaluations/evaluation-' . $evaluation->id . '.pdf';
    }

    /**
     * Get evaluation summary for disposal request
     *
     * @param EquipmentEvaluation $evaluation
     * @return array
     */
    public function getEvaluationSummary(EquipmentEvaluation $evaluation): array
    {
        return [
            'id' => $evaluation->id,
            'date' => $evaluation->evaluation_date->format('Y-m-d'),
            'evaluator' => $evaluation->evaluator->name,
            'physical_condition' => ucfirst($evaluation->physical_condition),
            'recommendation' => ucfirst(str_replace('_', ' ', $evaluation->recommendation)),
            'cost_comparison' => $evaluation->getCostComparison(),
            'impact_summary' => \Str::limit($evaluation->impact_on_testing, 100),
        ];
    }
}


