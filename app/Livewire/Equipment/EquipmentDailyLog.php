<?php

namespace App\Livewire\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentDailyLogEntry;
use Illuminate\Support\Collection;
use Livewire\Component;

class EquipmentDailyLog extends Component
{
    public int $activeFrequency = 1;

    public string $logDate = '';

    /** @var array<string, string> entryValues keyed as "{equipmentId}_{slot}" */
    public array $entryValues = [];

    /** @var array<string, bool> track which entries were just saved */
    public array $savedFlags = [];

    /** @var array<int, string> */
    public array $freqLabels = [
        1 => 'Once a Day',
        2 => 'Twice a Day',
        3 => 'Three Times a Day',
        4 => 'Four Times a Day',
        5 => 'Five Times a Day',
        6 => 'Six Times a Day',
    ];

    /** @var array<int, int> Counts per frequency */
    public array $freqCounts = [];

    public function mount(): void
    {
        $this->logDate = now()->toDateString();

        $companyId = getUserCompany();

        $counts = Equipment::where('company_id', $companyId)
            ->where('requires_daily_log', true)
            ->where('is_disposal', 0)
            ->selectRaw('daily_log_frequency, COUNT(*) as cnt')
            ->groupBy('daily_log_frequency')
            ->pluck('cnt', 'daily_log_frequency')
            ->toArray();

        foreach (array_keys($this->freqLabels) as $freq) {
            $this->freqCounts[$freq] = (int) ($counts[$freq] ?? 0);
        }

        // Default to first non-empty tab
        foreach (array_keys($this->freqLabels) as $freq) {
            if ($this->freqCounts[$freq] > 0) {
                $this->activeFrequency = $freq;
                break;
            }
        }

        $this->loadEntries();
    }

    public function setFrequency(int $freq): void
    {
        if (array_key_exists($freq, $this->freqLabels)) {
            $this->activeFrequency = $freq;
        }
    }

    public function updatedLogDate(): void
    {
        $this->savedFlags = [];
        $this->loadEntries();
    }

    private function loadEntries(): void
    {
        $this->entryValues = [];

        $entries = EquipmentDailyLogEntry::where('company_id', getUserCompany())
            ->where('log_date', $this->logDate)
            ->with('equipment')
            ->get();

        foreach ($entries as $entry) {
            $key = "{$entry->equipment_id}_{$entry->slot_number}";
            $isRange = $entry->equipment && $entry->equipment->daily_log_value_type === 'range';

            if ($isRange && $entry->recorded_value !== null) {
                // Split stored "min – max" back into separate fields
                $parts = array_map('trim', explode('–', $entry->recorded_value));
                $this->entryValues[$key . '_min'] = $parts[0] ?? '';
                $this->entryValues[$key . '_max'] = $parts[1] ?? '';
            } else {
                $this->entryValues[$key] = $entry->recorded_value ?? '';
            }
        }
    }

    public function saveEntry(int $equipmentId, int $slot, bool $isRange = false): void
    {
        $key = "{$equipmentId}_{$slot}";

        if ($isRange) {
            $min = trim($this->entryValues[$key . '_min'] ?? '');
            $max = trim($this->entryValues[$key . '_max'] ?? '');
            $value = ($min !== '' || $max !== '') ? "{$min} – {$max}" : null;
        } else {
            $value = $this->entryValues[$key] ?? null;
        }

        EquipmentDailyLogEntry::updateOrCreate(
            [
                'equipment_id' => $equipmentId,
                'log_date'     => $this->logDate,
                'slot_number'  => $slot,
                'company_id'   => getUserCompany(),
            ],
            [
                'recorded_value' => $value,
                'recorded_by'    => auth()->id(),
            ]
        );

        $this->savedFlags[$key] = true;
    }

    public function getEquipmentForActiveTab(): Collection
    {
        return Equipment::where('company_id', getUserCompany())
            ->where('requires_daily_log', true)
            ->where('is_disposal', 0)
            ->where('daily_log_frequency', $this->activeFrequency)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function isToday(): bool
    {
        return $this->logDate === now()->toDateString();
    }

    public function render()
    {
        return view('livewire.equipment.equipment-daily-log', [
            'equipment' => $this->getEquipmentForActiveTab(),
        ]);
    }
}
