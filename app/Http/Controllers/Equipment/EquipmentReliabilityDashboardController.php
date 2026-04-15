<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EquipmentReliabilityDashboardController extends Controller
{
    protected $mysqlConnection = null; // Default MySQL ground truth

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Equipment Reliability board
     */
    public function index()
    {
        $data = [
            'overdueCalibrationsMaintenances' => $this->getOverduceCalibrationsMaintenances(),
            'serviceMetrics' => $this->getServiceMetrics(),
            'maintenanceTrends' => $this->getMaintenanceTrends(),
            'verificationTrends' => $this->getVerificationTrends(),
            'assetsAtRisk' => $this->getAssetsAtRisk(),
            'equipmentReliability' => $this->getEquipmentReliability(),
        ];

        return view('equipment.dashboards.reliability', $data);
    }

    /**
     * Get overdue calibrations and maintenance
     */
    private function getOverduceCalibrationsMaintenances()
    {
        try {
            $overdue = DB::connection($this->mysqlConnection)
                ->table('v_equipment_reliability')
                ->where('is_overdue', 1)
                ->selectRaw('
                    equipment_id,
                    equipment_name,
                    last_calibration_date,
                    next_calibration_due,
                    last_maintenance_date,
                    next_maintenance_due,
                    calibration_overdue_days,
                    maintenance_overdue_days,
                    status
                ')
                ->orderByDesc('calibration_overdue_days')
                ->limit(50)
                ->get();

            return $overdue->map(function ($item) {
                $priority = $item->calibration_overdue_days > 30 ? 'critical' 
                    : ($item->calibration_overdue_days > 14 ? 'high' : 'medium');

                return [
                    'equipment' => $item->equipment_name,
                    'equipmentId' => $item->equipment_id,
                    'lastCalibration' => $item->last_calibration_date ? Carbon::parse($item->last_calibration_date)->format('M d, Y') : 'N/A',
                    'calibrationOverdueDays' => (int)$item->calibration_overdue_days,
                    'lastMaintenance' => $item->last_maintenance_date ? Carbon::parse($item->last_maintenance_date)->format('M d, Y') : 'N/A',
                    'maintenanceOverdueDays' => (int)$item->maintenance_overdue_days,
                    'priority' => $priority,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load overdue calibrations: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get service turnaround metrics
     */
    private function getServiceMetrics()
    {
        try {
            $metrics = DB::connection($this->mysqlConnection)
                ->table('v_equipment_reliability')
                ->selectRaw('
                    COUNT(DISTINCT equipment_id) as total_equipment,
                    COUNT(DISTINCT CASE WHEN is_overdue = 1 THEN equipment_id END) as overdue_equipment,
                    ROUND(AVG(CAST(days_since_maintenance AS DECIMAL)), 2) as avg_days_since_maintenance,
                    ROUND(AVG(CAST(maintenance_frequency_days AS DECIMAL)), 2) as avg_maintenance_frequency,
                    ROUND(AVG(CAST(calibration_cycle_days AS DECIMAL)), 2) as avg_calibration_cycle,
                    COUNT(DISTINCT CASE WHEN status = "IN_SERVICE" THEN equipment_id END) as in_service_count
                ')
                ->first();

            return [
                'totalEquipment' => (int)$metrics->total_equipment,
                'overdueEquipment' => (int)$metrics->overdue_equipment,
                'avgDaysSinceMaintenance' => (float)$metrics->avg_days_since_maintenance,
                'avgMaintenanceFrequency' => (float)$metrics->avg_maintenance_frequency,
                'avgCalibrationCycle' => (float)$metrics->avg_calibration_cycle,
                'inServiceCount' => (int)$metrics->in_service_count,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load service metrics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Maintenance frequency trend (30-day rolling)
     */
    private function getMaintenanceTrends()
    {
        try {
            $trends = DB::connection($this->mysqlConnection)
                ->table('v_equipment_reliability')
                ->where('last_maintenance_date', '>=', Carbon::now()->subDays(30))
                ->selectRaw('
                    DATE(last_maintenance_date) as maintenance_date,
                    COUNT(DISTINCT equipment_id) as equipment_count,
                    COUNT(*) as maintenance_events
                ')
                ->groupBy('maintenance_date')
                ->orderBy('maintenance_date')
                ->get();

            return $trends->map(function ($row) {
                return [
                    'date' => $row->maintenance_date,
                    'equipmentCount' => (int)$row->equipment_count,
                    'maintenanceEvents' => (int)$row->maintenance_events,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load maintenance trends: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verification pass/fail trend
     */
    private function getVerificationTrends()
    {
        try {
            $trends = DB::connection($this->mysqlConnection)
                ->table('v_equipment_reliability')
                ->selectRaw('
                    DATE(updated_at) as verification_date,
                    COUNT(DISTINCT CASE WHEN verification_status = "PASSED" THEN equipment_id END) as passed,
                    COUNT(DISTINCT CASE WHEN verification_status = "FAILED" THEN equipment_id END) as failed
                ')
                ->groupBy('verification_date')
                ->orderByDesc('verification_date')
                ->limit(30)
                ->get();

            return $trends->map(function ($row) {
                $total = $row->passed + $row->failed;
                $passRate = $total > 0 ? round(($row->passed / $total) * 100, 2) : 0;

                return [
                    'date' => $row->verification_date,
                    'passed' => (int)$row->passed,
                    'failed' => (int)$row->failed,
                    'passRate' => (float)$passRate,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load verification trends: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assets at risk in 7/14/30 days
     */
    private function getAssetsAtRisk()
    {
        try {
            $atRisk = DB::connection($this->mysqlConnection)
                ->select('
                    SELECT 
                        "7_days" as risk_period,
                        COUNT(*) as assets_at_risk
                    FROM v_equipment_reliability
                    WHERE calibration_overdue_days >= -7 AND calibration_overdue_days <= 0
                    UNION ALL
                    SELECT 
                        "14_days",
                        COUNT(*)
                    FROM v_equipment_reliability
                    WHERE calibration_overdue_days >= -14 AND calibration_overdue_days <= 0
                    UNION ALL
                    SELECT 
                        "30_days",
                        COUNT(*)
                    FROM v_equipment_reliability
                    WHERE calibration_overdue_days >= -30 AND calibration_overdue_days <= 0
                ');

            $result = [];
            foreach ($atRisk as $row) {
                $result[$row->risk_period] = (int)$row->assets_at_risk;
            }

            return [
                '7_days' => $result['7_days'] ?? 0,
                '14_days' => $result['14_days'] ?? 0,
                '30_days' => $result['30_days'] ?? 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load assets at risk: ' . $e->getMessage());
            return ['7_days' => 0, '14_days' => 0, '30_days' => 0];
        }
    }

    /**
     * Equipment reliability scores
     */
    private function getEquipmentReliability()
    {
        try {
            $equipment = DB::connection($this->mysqlConnection)
                ->table('v_equipment_reliability')
                ->selectRaw('
                    equipment_id,
                    equipment_name,
                    ROUND((
                        (100 - CAST(calibration_overdue_days AS SIGNED)) +
                        (100 - CAST(maintenance_overdue_days AS SIGNED)) +
                        (verification_status = "PASSED" ? 100 : 0)
                    ) / 3, 2) as reliability_score,
                    status,
                    verification_status
                ')
                ->orderByDesc('reliability_score')
                ->limit(20)
                ->get();

            return $equipment->map(function ($item) {
                $score = max(0, min(100, (float)$item->reliability_score));
                $risk = $score > 75 ? 'low' : ($score > 50 ? 'medium' : 'high');

                return [
                    'equipment' => $item->equipment_name,
                    'reliabilityScore' => $score,
                    'status' => $item->status,
                    'verificationStatus' => $item->verification_status,
                    'risk' => $risk,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load equipment reliability: ' . $e->getMessage());
            return [];
        }
    }
}
