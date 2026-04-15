<?php

namespace App\Services\AI\Repository;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ReportingMartRefreshService
{
    public function __construct(
        protected OperationalFoundationSyncService $syncService
    ) {
    }

    /**
     * Refresh reporting mart tables from foundation tables.
     * 
     * @param array $marts List of specific marts to refresh (empty = all)
     * @param bool $syncBeforeRefresh Whether to sync foundation tables first
     * @param int|null $chunkSize Batch size for processing
     * @return array Result summary, keyed by mart name
     */
    public function refresh(array $marts = [], bool $syncBeforeRefresh = false, ?int $chunkSize = null): array
    {
        $connection = DB::connection($this->syncService->repositoryConnection());
        $schema = 'reporting'; // Fixed schema name for reporting tables

        try {
            // Refresh equipment marts if requested (or if no specific marts listed)
            if (empty($marts) || in_array('equipment_reliability', $marts, true)) {
                try {
                    $result = $this->refreshEquipmentMarts($connection, $schema);
                    return [
                        'equipment_reliability' => [
                            'status' => 'completed',
                            'rows_materialized' => $result['rows_materialized'],
                        ],
                    ];
                } catch (Throwable $e) {
                    \Log::error('Equipment reliability mart refresh failed: ' . $e->getMessage());
                    return [
                        'equipment_reliability' => [
                            'status' => 'failed',
                            'rows_materialized' => 0,
                        ],
                    ];
                }
            }

            return [];
        } catch (Throwable $exception) {
            \Log::error('Reporting mart refresh failed: ' . $exception->getMessage());
            return [
                'equipment_reliability' => [
                    'status' => 'failed',
                    'rows_materialized' => 0,
                ],
            ];
        }
    }

    /**
     * Refresh equipment_due_status and equipment_department_summary marts.
     * 
     * @return array
     */
    protected function refreshEquipmentMarts($connection, string $schema): array
    {
        $martsRefreshed = 0;
        $rowsMaterialized = 0;

        // 1. Refresh equipment_due_status (detail rows)
        try {
            $dueStatusRows = $this->calculateEquipmentDueStatus($connection, $schema);
            
            // Truncate and reload
            $connection->statement("TRUNCATE TABLE reporting.equipment_due_status");
            
            if (!empty($dueStatusRows)) {
                $connection->table("reporting.equipment_due_status")->insert($dueStatusRows);
                $rowsMaterialized += count($dueStatusRows);
            }
            
            $martsRefreshed++;
        } catch (Throwable $e) {
            \Log::warning("Failed to refresh equipment_due_status: " . $e->getMessage());
            throw $e;
        }

        // 2. Refresh equipment_department_summary (aggregates)
        try {
            $summaryRows = $this->calculateEquipmentDepartmentSummary($connection, $schema);
            
            // Truncate and reload
            $connection->statement("TRUNCATE TABLE reporting.equipment_department_summary");
            
            if (!empty($summaryRows)) {
                $connection->table("reporting.equipment_department_summary")->insert($summaryRows);
                $rowsMaterialized += count($summaryRows);
            }
            
            $martsRefreshed++;
        } catch (Throwable $e) {
            \Log::warning("Failed to refresh equipment_department_summary: " . $e->getMessage());
            throw $e;
        }

        return [
            'marts_refreshed' => $martsRefreshed,
            'rows_materialized' => $rowsMaterialized,
        ];
    }

    /**
     * Calculate equipment_due_status rows.
     * 
     * Joins equipment_assets with equipment_logs to determine:
     * - Last maintenance/calibration/verification dates
     * - Next due dates
     * - Status (overdue/warning/stable)
     * - Risk window
     */
    protected function calculateEquipmentDueStatus($connection, string $schema): array
    {
        $today = now()->toDateString();
        $rows = [];

        // Get all equipment with their last log dates using query builder
        $query = "
            SELECT 
                ea.source_id,
                ea.name,
                ea.assigned_department,
                ea.date_purchased,
                ea.maintainance_days,
                ea.maintainance_notification_in_days,
                ea.calibration_days,
                ea.calibration_notification_in_days,
                ea.verification_days,
                ea.synced_at,
                ea.payload,
                -- Last dates for each service type
                MAX(CASE WHEN el.event_type = 'maintainance' THEN el.event_date END) as last_maintenance_date,
                MAX(CASE WHEN el.event_type = 'calibration' THEN el.event_date END) as last_calibration_date,
                MAX(CASE WHEN el.event_type = 'verification' THEN el.event_date END) as last_verification_date
            FROM reporting.equipment_assets ea
            LEFT JOIN reporting.equipment_logs el ON el.equipment_id = ea.source_id
            GROUP BY ea.source_id, ea.name, ea.assigned_department, ea.date_purchased,
                     ea.maintainance_days, ea.maintainance_notification_in_days,
                     ea.calibration_days, ea.calibration_notification_in_days,
                     ea.verification_days, ea.synced_at, ea.payload
        ";

        $equipmentData = $connection->select($query);

        foreach ($equipmentData as $eq) {
            // Calculate due dates
            $lastMaintDate = $eq->last_maintenance_date ?: $eq->date_purchased;
            $lastCalibDate = $eq->last_calibration_date ?: $eq->date_purchased;
            $lastVerifDate = $eq->last_verification_date ?: $eq->date_purchased;

            $maintDueDate = date('Y-m-d', strtotime($lastMaintDate . ' +' . ($eq->maintainance_days ?? 365) . ' days'));
            $calibDueDate = date('Y-m-d', strtotime($lastCalibDate . ' +' . ($eq->calibration_days ?? 365) . ' days'));
            $verifDueDate = date('Y-m-d', strtotime($lastVerifDate . ' +' . ($eq->verification_days ?? 365) . ' days'));

            // Calculate status and days until due
            $maintStatus = $this->calculateStatus($maintDueDate, $eq->maintainance_notification_in_days ?? 14, $today);
            $calibStatus = $this->calculateStatus($calibDueDate, $eq->calibration_notification_in_days ?? 14, $today);
            $verifStatus = $this->calculateStatus($verifDueDate, $eq->verification_notification_in_days ?? 14, $today);

            $maintDaysUntil = $this->daysUntil($maintDueDate, $today);
            $calibDaysUntil = $this->daysUntil($calibDueDate, $today);
            $verifDaysUntil = $this->daysUntil($verifDueDate, $today);

            // Nearest due and risk window
            $nearestDue = min(array_filter([$maintDaysUntil, $calibDaysUntil, $verifDaysUntil]));
            $riskWindow = $this->calculateRiskWindow($nearestDue);

            $rows[] = [
                'source_id' => $eq->source_id,
                'name' => $eq->name,
                'assigned_department' => $eq->assigned_department,
                'maintenance_status' => $maintStatus,
                'calibration_status' => $calibStatus,
                'verification_status' => $verifStatus,
                'risk_window' => $riskWindow,
                'maintenance_days_until_due' => $maintDaysUntil,
                'calibration_days_until_due' => $calibDaysUntil,
                'verification_days_until_due' => $verifDaysUntil,
                'maintenance_due_date' => $maintDueDate,
                'calibration_due_date' => $calibDueDate,
                'verification_due_date' => $verifDueDate,
                'last_maintenance_date' => $eq->last_maintenance_date,
                'last_calibration_date' => $eq->last_calibration_date,
                'last_verification_date' => $eq->last_verification_date,
                'refreshed_at' => now()->toDateTimeString(),
                'synced_at' => $eq->synced_at,
                'payload' => $eq->payload,
            ];
        }

        return $rows;
    }

    /**
     * Calculate equipment_department_summary rows.
     */
    protected function calculateEquipmentDepartmentSummary($connection, string $schema): array
    {
        $rows = [];

        // Get aggregates from equipment_due_status
        $summaryData = $connection->select("
            SELECT 
                assigned_department,
                COUNT(*) as total_assets,
                SUM(CASE WHEN maintenance_status = 'overdue' THEN 1 ELSE 0 END) as maintenance_overdue,
                SUM(CASE WHEN maintenance_status = 'warning' THEN 1 ELSE 0 END) as maintenance_warning,
                SUM(CASE WHEN calibration_status = 'overdue' THEN 1 ELSE 0 END) as calibration_overdue,
                SUM(CASE WHEN calibration_status = 'warning' THEN 1 ELSE 0 END) as calibration_warning,
                SUM(CASE WHEN verification_status = 'overdue' THEN 1 ELSE 0 END) as verification_overdue,
                SUM(CASE WHEN verification_status = 'warning' THEN 1 ELSE 0 END) as verification_warning,
                SUM(CASE WHEN risk_window IN ('overdue', '7_days', '14_days', '30_days') THEN 1 ELSE 0 END) as due_within_30_days
            FROM reporting.equipment_due_status
            WHERE assigned_department IS NOT NULL
            GROUP BY assigned_department
        ");

        foreach ($summaryData as $summary) {
            $rows[] = [
                'assigned_department' => $summary->assigned_department,
                'total_assets' => $summary->total_assets,
                'maintenance_overdue' => $summary->maintenance_overdue,
                'maintenance_warning' => $summary->maintenance_warning,
                'calibration_overdue' => $summary->calibration_overdue,
                'calibration_warning' => $summary->calibration_warning,
                'verification_overdue' => $summary->verification_overdue,
                'verification_warning' => $summary->verification_warning,
                'due_within_30_days' => $summary->due_within_30_days,
                'active_count' => $summary->total_assets,
                'disposed_count' => 0,
                'refreshed_at' => now()->toDateTimeString(),
                'synced_at' => now()->toDateTimeString(),
                'payload' => null,
            ];
        }

        return $rows;
    }

    /**
     * Calculate status based on due date and notification days.
     */
    protected function calculateStatus(string $dueDate, int $notificationDays, string $today): string
    {
        $daysUntil = $this->daysUntil($dueDate, $today);
        
        if ($daysUntil < 0) {
            return 'overdue';
        } elseif ($daysUntil <= $notificationDays) {
            return 'warning';
        }
        
        return 'stable';
    }

    /**
     * Calculate days until due date.
     */
    protected function daysUntil(string $dueDate, string $today): int
    {
        $due = strtotime($dueDate);
        $now = strtotime($today);
        return intval(($due - $now) / 86400);
    }

    /**
     * Calculate risk window classification.
     */
    protected function calculateRiskWindow(int $daysUntil): string
    {
        $riskDays1 = config('imara_ai.equipment_risk_days_1', 7);
        $riskDays2 = config('imara_ai.equipment_risk_days_2', 14);
        $riskDays3 = config('imara_ai.equipment_risk_days_3', 30);

        if ($daysUntil < 0) {
            return 'overdue';
        } elseif ($daysUntil <= $riskDays1) {
            return '7_days';
        } elseif ($daysUntil <= $riskDays2) {
            return '14_days';
        } elseif ($daysUntil <= $riskDays3) {
            return '30_days';
        }

        return 'stable';
    }
}
