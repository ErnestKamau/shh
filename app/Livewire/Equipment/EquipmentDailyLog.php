<?php

namespace App\Livewire\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Worksheets\MethodSequenceStageEquipmentUsage;
use Carbon\Carbon;
use Livewire\Component;

class EquipmentDailyLog extends Component
{
    public string $logDate = '';

    public string $historyFromDate = '';

    public string $historyToDate = '';

    public ?int $selectedEquipmentId = null;

    public function mount(): void
    {
        $this->logDate = now()->toDateString();
        $this->historyFromDate = now()->subDays(30)->toDateString();
        $this->historyToDate = now()->toDateString();
    }

    public function selectEquipment(int $equipmentId): void
    {
        $equipment = Equipment::where('company_id', getUserCompany())
            ->where('is_disposal', 0)
            ->find($equipmentId);

        if ($equipment) {
            $this->selectedEquipmentId = $equipment->id;
        }
    }

    public function clearSelectedEquipment(): void
    {
        $this->selectedEquipmentId = null;
    }

    public function getDailyUsageRowsProperty(): array
    {
        return MethodSequenceStageEquipmentUsage::query()
            ->whereNotNull('started_at')
            ->whereDate('started_at', $this->logDate)
            ->whereHas('equipment', function ($query) {
                $query->where('company_id', getUserCompany())
                    ->where('is_disposal', 0);
            })
            ->with(['equipment', 'stageData.run.analyst', 'startedByUser', 'completedByUser'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function (MethodSequenceStageEquipmentUsage $usage) {
                $status = $usage->isCompleted() ? 'Completed' : ($usage->isInProgress() ? 'In Progress' : 'Not Started');

                return [
                    'usage_id' => $usage->id,
                    'equipment_id' => $usage->equipment_id,
                    'equipment_name' => $usage->equipment?->name ?? $usage->equipment_name ?? '-',
                    'equipment_number' => $usage->equipment?->equipment_number ?? '-',
                    'time_on' => $usage->started_at?->format('H:i:s') ?? '-',
                    'time_off' => $usage->completed_at?->format('H:i:s') ?? '-',
                    'duration' => $usage->getFormattedDuration() ?? '-',
                    'status' => $status,
                    'analyst' => $usage->stageData?->run?->analyst?->name
                        ?? $usage->startedByUser?->name
                        ?? '-',
                ];
            })
            ->values()
            ->all();
    }

    public function getSelectedEquipmentProperty(): ?Equipment
    {
        if (!$this->selectedEquipmentId) {
            return null;
        }

        return Equipment::where('company_id', getUserCompany())
            ->where('is_disposal', 0)
            ->find($this->selectedEquipmentId);
    }

    public function getSelectedEquipmentUsageRowsProperty(): array
    {
        if (!$this->selectedEquipmentId || !$this->hasValidHistoryRange()) {
            return [];
        }

        return MethodSequenceStageEquipmentUsage::query()
            ->where('equipment_id', $this->selectedEquipmentId)
            ->whereNotNull('started_at')
            ->whereBetween('started_at', [
                $this->historyFromDate . ' 00:00:00',
                $this->historyToDate . ' 23:59:59',
            ])
            ->with(['stageData.run.analyst', 'startedByUser', 'completedByUser'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function (MethodSequenceStageEquipmentUsage $usage) {
                $status = $usage->isCompleted() ? 'Completed' : ($usage->isInProgress() ? 'In Progress' : 'Not Started');

                $durationMinutes = $usage->getDurationMinutes();

                if ($durationMinutes === null && $usage->started_at && !$usage->completed_at) {
                    $durationMinutes = $usage->started_at->diffInMinutes(now());
                }

                return [
                    'date' => $usage->started_at?->format('Y-m-d') ?? '-',
                    'time_on' => $usage->started_at?->format('H:i:s') ?? '-',
                    'time_off' => $usage->completed_at?->format('H:i:s') ?? '-',
                    'duration' => $usage->getFormattedDuration() ?? ($durationMinutes !== null ? $this->formatDurationMinutes($durationMinutes) : '-'),
                    'duration_minutes' => $durationMinutes,
                    'status' => $status,
                    'analyst' => $usage->stageData?->run?->analyst?->name
                        ?? $usage->startedByUser?->name
                        ?? '-',
                    'started_at_raw' => $usage->started_at?->toDateTimeString(),
                ];
            })
            ->values()
            ->all();
    }

    public function getUsageChartDataProperty(): array
    {
        $rows = collect($this->selectedEquipmentUsageRows)
            ->filter(fn ($row) => $row['started_at_raw'] !== null)
            ->sortBy('started_at_raw')
            ->values();

        $labels = $rows->map(function ($row) {
            return Carbon::parse($row['started_at_raw'])->format('M j H:i');
        })->all();

        $durations = $rows->map(function ($row) {
            return $row['duration_minutes'] ?? 0;
        })->all();

        return [
            'labels' => $labels,
            'durations' => $durations,
        ];
    }

    private function hasValidHistoryRange(): bool
    {
        return $this->historyFromDate !== ''
            && $this->historyToDate !== ''
            && $this->historyFromDate <= $this->historyToDate;
    }

    private function formatDurationMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d:00', $hours, $mins);
    }

    public function render()
    {
        return view('livewire.equipment.equipment-daily-log', [
            'dailyUsageRows' => $this->dailyUsageRows,
            'selectedEquipment' => $this->selectedEquipment,
            'selectedEquipmentUsageRows' => $this->selectedEquipmentUsageRows,
            'usageChartData' => $this->usageChartData,
        ]);
    }
}
