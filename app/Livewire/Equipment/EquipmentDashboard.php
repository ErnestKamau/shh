<?php

namespace App\Livewire\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use Carbon\Carbon;
use Livewire\Component;

class EquipmentDashboard extends Component
{
    public int $totalEquipmentCount = 0;
    public int $activeCount = 0;
    public int $disposedCount = 0;
    public int $dueCalibrationCount = 0;
    public int $dueMaintainanceCount = 0;
    public int $overdueCalibrationCount = 0;
    public int $overdueMaintainanceCount = 0;

    /** @var array<int, array{label: string, value: int, color: string}> */
    public array $statusDistribution = [];
    /** @var array<int, array{label: string, value: int, color: string}> */
    public array $dueDistribution = [];
    /** @var array<int, array{month: string, count: int}> */
    public array $purchaseTrend = [];
    /** @var array<int, array{id: int, name: string, equipment_number: string, calibration_days_left: int, maintainance_days_left: int}> */
    public array $criticalEquipment = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    private function loadDashboardData(): void
    {
        $companyId = getUserCompany();
        $calibrationDiffExpr = "DATEDIFF(DATE_ADD(COALESCE(lc.last_calibration_date, equipment.date_purchased), INTERVAL COALESCE(equipment.calibration_days, 0) DAY), CURDATE())";
        $maintainanceDiffExpr = "DATEDIFF(DATE_ADD(COALESCE(lm.last_maintainance_date, equipment.date_purchased), INTERVAL COALESCE(equipment.maintainance_days, 0) DAY), CURDATE())";

        $totalsRow = Equipment::query()
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN is_disposal = 0 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN is_disposal = 1 THEN 1 ELSE 0 END) as disposed_count
            ')
            ->first();

        $this->totalEquipmentCount = (int) ($totalsRow->total_count ?? 0);
        $this->activeCount = (int) ($totalsRow->active_count ?? 0);
        $this->disposedCount = (int) ($totalsRow->disposed_count ?? 0);

        $activeWithLatestLogs = $this->activeEquipmentWithLatestLogsQuery($companyId);

        $metricsRow = (clone $activeWithLatestLogs)
            ->selectRaw("
                SUM(CASE WHEN {$calibrationDiffExpr} < 0 THEN 1 ELSE 0 END) as overdue_calibration_count,
                SUM(CASE WHEN {$maintainanceDiffExpr} < 0 THEN 1 ELSE 0 END) as overdue_maintainance_count,
                SUM(
                    CASE
                        WHEN {$calibrationDiffExpr} < 0
                            OR (
                                COALESCE(equipment.calibration_notification_in_days, 0) > 0
                                AND {$calibrationDiffExpr} <= COALESCE(equipment.calibration_notification_in_days, 0)
                            )
                        THEN 1 ELSE 0
                    END
                ) as due_calibration_count,
                SUM(
                    CASE
                        WHEN {$maintainanceDiffExpr} < 0
                            OR (
                                COALESCE(equipment.maintainance_notification_in_days, 0) > 0
                                AND {$maintainanceDiffExpr} <= COALESCE(equipment.maintainance_notification_in_days, 0)
                            )
                        THEN 1 ELSE 0
                    END
                ) as due_maintainance_count
            ")
            ->first();

        $this->overdueCalibrationCount = (int) ($metricsRow->overdue_calibration_count ?? 0);
        $this->overdueMaintainanceCount = (int) ($metricsRow->overdue_maintainance_count ?? 0);
        $this->dueCalibrationCount = (int) ($metricsRow->due_calibration_count ?? 0);
        $this->dueMaintainanceCount = (int) ($metricsRow->due_maintainance_count ?? 0);

        $healthyCount = max($this->activeCount - ($this->dueCalibrationCount + $this->dueMaintainanceCount), 0);

        $this->statusDistribution = [
            ['label' => 'Active', 'value' => $this->activeCount, 'color' => '#28a745'],
            ['label' => 'Disposed', 'value' => $this->disposedCount, 'color' => '#6c757d'],
        ];

        $this->dueDistribution = [
            ['label' => 'Calibration Due', 'value' => $this->dueCalibrationCount, 'color' => '#ffc107'],
            ['label' => 'Maintainance Due', 'value' => $this->dueMaintainanceCount, 'color' => '#fd7e14'],
            ['label' => 'Healthy', 'value' => $healthyCount, 'color' => '#20c997'],
        ];

        $this->purchaseTrend = Equipment::query()
            ->where('company_id', $companyId)
            ->whereNotNull('date_purchased')
            ->whereDate('date_purchased', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date_purchased, '%b %Y') as month, COUNT(*) as count, DATE_FORMAT(date_purchased, '%Y-%m') as sort_key")
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->map(fn ($row): array => [
                'month' => (string) $row->month,
                'count' => (int) $row->count,
            ])
            ->toArray();

        $criticalRows = (clone $activeWithLatestLogs)
            ->selectRaw("
                equipment.id,
                equipment.name,
                equipment.equipment_number,
                {$calibrationDiffExpr} as calibration_days_left,
                {$maintainanceDiffExpr} as maintainance_days_left
            ")
            ->where(function ($query) use ($calibrationDiffExpr, $maintainanceDiffExpr) {
                $query->whereRaw("{$calibrationDiffExpr} < 1")
                    ->orWhereRaw("{$maintainanceDiffExpr} < 1");
            })
            ->orderByRaw("LEAST({$calibrationDiffExpr}, {$maintainanceDiffExpr}) asc")
            ->limit(8)
            ->get();

        $this->criticalEquipment = $criticalRows->map(function ($row): array {
            return [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'equipment_number' => (string) $row->equipment_number,
                'calibration_days_left' => (int) $row->calibration_days_left,
                'maintainance_days_left' => (int) $row->maintainance_days_left,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.equipment.equipment-dashboard');
    }

    private function activeEquipmentWithLatestLogsQuery(int $companyId)
    {
        $latestCalibration = MaintainanceCalibrationLog::query()
            ->selectRaw('equipment_id, MAX(date) as last_calibration_date')
            ->where('type', 'calibration')
            ->groupBy('equipment_id');

        $latestMaintainance = MaintainanceCalibrationLog::query()
            ->selectRaw('equipment_id, MAX(date) as last_maintainance_date')
            ->where('type', 'maintainance')
            ->groupBy('equipment_id');

        return Equipment::query()
            ->from('equipment')
            ->where('equipment.company_id', $companyId)
            ->where('equipment.is_disposal', 0)
            ->leftJoinSub($latestCalibration, 'lc', function ($join) {
                $join->on('lc.equipment_id', '=', 'equipment.id');
            })
            ->leftJoinSub($latestMaintainance, 'lm', function ($join) {
                $join->on('lm.equipment_id', '=', 'equipment.id');
            });
    }
}
