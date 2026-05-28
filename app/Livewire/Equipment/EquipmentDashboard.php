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
        // Load all equipment with relationships
        $allEquipment = Equipment::query()
            ->where('company_id', $companyId)
            ->with(['latestCalibration', 'latestMaintenance'])
            ->get();

        // Calculate totals using collections
        $this->totalEquipmentCount = $allEquipment->count();
        $this->activeCount = $allEquipment->where('is_disposal', false)->count();
        $this->disposedCount = $allEquipment->where('is_disposal', true)->count();

        // Get active equipment for metric calculations
        $activeEquipment = $allEquipment->where('is_disposal', false);

        // Calculate status metrics using collections
        $this->overdueCalibrationCount = $activeEquipment
            ->filter(fn($e) => $this->getDaysUntilCalibration($e) < 0)
            ->count();

        $this->overdueMaintainanceCount = $activeEquipment
            ->filter(fn($e) => $this->getDaysUntilMaintenance($e) < 0)
            ->count();

        $this->dueCalibrationCount = $activeEquipment
            ->filter(fn($e) => $this->isDueForCalibration($e))
            ->count();

        $this->dueMaintainanceCount = $activeEquipment
            ->filter(fn($e) => $this->isDueForMaintenance($e))
            ->count();

        $healthyCount = $activeEquipment
            ->filter(fn($e) => $this->isHealthy($e))
            ->count();

        $this->statusDistribution = [
            ['label' => 'Active', 'value' => $this->activeCount, 'color' => '#28a745'],
            ['label' => 'Disposed', 'value' => $this->disposedCount, 'color' => '#6c757d'],
        ];

        $this->dueDistribution = [
            ['label' => 'Calibration Due', 'value' => $this->dueCalibrationCount, 'color' => '#ffc107'],
            ['label' => 'Maintainance Due', 'value' => $this->dueMaintainanceCount, 'color' => '#fd7e14'],
            ['label' => 'Healthy', 'value' => $healthyCount, 'color' => '#20c997'],
        ];

        // Purchase trend grouped by month
        $trendStart = Carbon::now()->subMonths(5)->startOfMonth();

        $this->purchaseTrend = $allEquipment
            ->filter(function ($equipment) use ($trendStart): bool {
                if (empty($equipment->date_purchased)) {
                    return false;
                }

                return Carbon::parse($equipment->date_purchased)->gte($trendStart);
            })
            ->groupBy(fn($equipment) => Carbon::parse($equipment->date_purchased)->format('Y-m'))
            ->map(fn($group, $monthKey) => [
                'month' => Carbon::createFromFormat('Y-m', $monthKey)->format('M Y'),
                'count' => $group->count(),
            ])
            ->values()
            ->toArray();

        // Critical equipment needing attention
        $this->criticalEquipment = $activeEquipment
            ->filter(fn($e) => 
                $this->getDaysUntilCalibration($e) < 1 || 
                $this->getDaysUntilMaintenance($e) < 1
            )
            ->sortBy(fn($e) => min(
                $this->getDaysUntilCalibration($e),
                $this->getDaysUntilMaintenance($e)
            ))
            ->take(8)
            ->map(fn($e): array => [
                'id' => (int) $e->id,
                'name' => (string) $e->name,
                'equipment_number' => (string) $e->equipment_number,
                'calibration_days_left' => (int) $this->getDaysUntilCalibration($e),
                'maintainance_days_left' => (int) $this->getDaysUntilMaintenance($e),
            ])
            ->values()
            ->toArray();
    }

    private function getDaysUntilCalibration(Equipment $equipment): int
    {
        $lastDate = $equipment->latestCalibration?->date ?? $equipment->date_purchased;
        if (!$lastDate) {
            return PHP_INT_MAX;
        }
        
        $dueDate = Carbon::parse($lastDate)->addDays($equipment->calibration_days ?? 0);
        return (int) Carbon::now()->diffInDays($dueDate, false);
    }

    private function getDaysUntilMaintenance(Equipment $equipment): int
    {
        $lastDate = $equipment->latestMaintenance?->date ?? $equipment->date_purchased;
        if (!$lastDate) {
            return PHP_INT_MAX;
        }
        
        $dueDate = Carbon::parse($lastDate)->addDays($equipment->maintainance_days ?? 0);
        return (int) Carbon::now()->diffInDays($dueDate, false);
    }

    private function isDueForCalibration(Equipment $equipment): bool
    {
        $daysLeft = $this->getDaysUntilCalibration($equipment);
        $notificationDays = $equipment->calibration_notification_in_days ?? 0;
        
        return $daysLeft < 0 || ($notificationDays > 0 && $daysLeft <= $notificationDays);
    }

    private function isDueForMaintenance(Equipment $equipment): bool
    {
        $daysLeft = $this->getDaysUntilMaintenance($equipment);
        $notificationDays = $equipment->maintainance_notification_in_days ?? 0;
        
        return $daysLeft < 0 || ($notificationDays > 0 && $daysLeft <= $notificationDays);
    }

    private function isHealthy(Equipment $equipment): bool
    {
        return !$this->isDueForCalibration($equipment) && !$this->isDueForMaintenance($equipment);
    }

    public function render()
    {
        return view('livewire.equipment.equipment-dashboard');
    }
}
