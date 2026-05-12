<?php

namespace App\Services\Dashboards;

use App\InventoryDepartment;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EquipmentDashboardService
{
    use DashboardHelpers;

    public function getEquipmentReliabilityBoard(): array
    {
        try {
            $connection = DB::connection(config('imara_ai.source_connection', config('database.default')));

            $detailRows = $connection->table("v_equipment_reliability")
                ->orderByRaw("CASE WHEN maintenance_status = 'overdue' OR calibration_status = 'overdue' THEN 1 ELSE 2 END")
                ->get();
            $departmentRows = $connection->table("v_equipment_summary")
                ->orderByDesc('due_soon')
                ->orderByDesc('total_assets')
                ->get();

            $departmentNames = InventoryDepartment::pluck('name', 'id');

            $normalizedDetailRows = $detailRows->map(function ($row) use ($departmentNames) {
                return [
                    'source_id' => (int) $row->equipment_id,
                    'name' => $row->equipment_name ?: ('Equipment #' . $row->equipment_id),
                    'assigned_department' => $this->resolveDepartmentName($row->assigned_department, $departmentNames),
                    'maintenance_status' => $row->maintenance_status,
                    'calibration_status' => $row->calibration_status,
                    'verification_status' => $row->verification_status ?? 'n/a',
                    'risk_window' => $row->maintenance_status == 'overdue' || $row->calibration_status == 'overdue' ? 'overdue' : 'stable',
                    'nearest_due_days' => min(array_filter([$row->maintenance_overdue_days ?? null, $row->calibration_overdue_days ?? null], fn($v) => !is_null($v))) ?? 0,
                    'maintenance_due_date' => $row->next_maintenance_due,
                    'calibration_due_date' => $row->next_calibration_due,
                    'verification_due_date' => null,
                ];
            })->values();

            $normalizedDepartmentRows = $departmentRows->map(function ($row) use ($departmentNames) {
                return [
                    'assigned_department' => $this->resolveDepartmentName($row->department, $departmentNames),
                    'total_assets' => (int) $row->total_assets,
                    'maintenance_overdue' => (int) $row->maint_overdue,
                    'calibration_overdue' => (int) $row->calib_overdue,
                    'verification_overdue' => 0,
                    'due_within_30_days' => (int) $row->due_soon,
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $summary = [
                'total_assets' => (int) $normalizedDepartmentRows->sum('total_assets'),
                'maintenance_overdue' => (int) $normalizedDepartmentRows->sum('maintenance_overdue'),
                'calibration_overdue' => (int) $normalizedDepartmentRows->sum('calibration_overdue'),
                'verification_overdue' => 0,
                'due_within_30_days' => (int) $normalizedDepartmentRows->sum('due_within_30_days'),
            ];

            $priorityAssets = $normalizedDetailRows
                ->filter(fn ($row) => in_array($row['risk_window'], ['overdue', '7_days', '14_days', '30_days'], true))
                ->sort(function ($left, $right) {
                    $priorityCompare = $this->riskWindowPriority($left['risk_window']) <=> $this->riskWindowPriority($right['risk_window']);
                    if ($priorityCompare !== 0) {
                        return $priorityCompare;
                    }
                    return ($left['nearest_due_days'] ?? PHP_INT_MAX) <=> ($right['nearest_due_days'] ?? PHP_INT_MAX);
                })
                ->take(10)
                ->values();

            return [
                'available' => true,
                'message' => null,
                'summary' => $summary,
                'department_rows' => $normalizedDepartmentRows->all(),
                'priority_assets' => $priorityAssets->all(),
                'refreshed_at' => $normalizedDepartmentRows->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load equipment reliability board: ' . $exception->getMessage());

            return $this->emptyEquipmentBoard('Equipment reliability data is unavailable.');
        }
    }

    protected function riskWindowPriority(?string $riskWindow): int
    {
        return match ($riskWindow) {
            'overdue' => 1,
            '7_days' => 2,
            '14_days' => 3,
            '30_days' => 4,
            'stable' => 5,
            default => 6,
        };
    }

    protected function emptyEquipmentBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message ?? 'Equipment reliability data is unavailable.',
            'summary' => [
                'total_assets' => 0,
                'maintenance_overdue' => 0,
                'calibration_overdue' => 0,
                'verification_overdue' => 0,
                'due_within_30_days' => 0,
            ],
            'department_rows' => [],
            'priority_assets' => [],
            'refreshed_at' => null,
        ];
    }
}
