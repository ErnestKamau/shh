<?php

namespace App\Livewire\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentDailyLogEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * EquipmentChecks Component
 * 
 * Renamed from EquipmentDailyLog to better reflect the purpose of tracking
 * equipment checks during analysis workflows. This component handles recording
 * and monitoring equipment readings based on defined frequencies.
 */
class EquipmentChecks extends Component
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

    public string $nonConformanceFromDate = '';

    public string $nonConformanceToDate = '';

    public int $nonConformancePerPage = 10;

    public int $nonConformancePage = 1;

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

    public function updatedNonConformanceFromDate(): void
    {
        $this->nonConformancePage = 1;
    }

    public function updatedNonConformanceToDate(): void
    {
        $this->nonConformancePage = 1;
    }

    public function updatedNonConformancePerPage($value): void
    {
        $this->nonConformancePerPage = max(10, (int) $value);
        $this->nonConformancePage = 1;
    }

    public function previousNonConformancePage(): void
    {
        $this->nonConformancePage = max(1, $this->nonConformancePage - 1);
    }

    public function nextNonConformancePage(): void
    {
        $this->nonConformancePage = min($this->nonConformanceTotalPages, $this->nonConformancePage + 1);
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
            $this->entryValues[$key] = $entry->recorded_value ?? '';
        }
    }

    public function updatedEntryValues($value, $key): void
    {
        if (!$this->isToday()) {
            return;
        }

        if (!preg_match('/^(\d+)_(\d+)$/', (string) $key, $matches)) {
            return;
        }

        $this->saveEntry((int) $matches[1], (int) $matches[2]);
    }

    public function saveEntry(int $equipmentId, int $slot): void
    {
        $key = "{$equipmentId}_{$slot}";
        $value = trim((string) ($this->entryValues[$key] ?? ''));
        $value = $value !== '' ? $value : null;

        EquipmentDailyLogEntry::updateOrCreate(
            [
                'equipment_id' => $equipmentId,
                'log_date'     => $this->logDate,
                'slot_number'  => $slot,
                'company_id'   => getUserCompany(),
            ],
            [
                'recorded_value' => $value,
                'recorded_by'    => Auth::id(),
            ]
        );

        $this->savedFlags[$key] = true;
    }

    public function getReadingRangeStatus(Equipment $equipment, ?string $reading): ?string
    {
        if ($equipment->daily_log_value_type !== 'range') {
            return null;
        }

        if (!is_numeric($reading)) {
            return null;
        }

        if ($equipment->daily_log_expected_min === null || $equipment->daily_log_expected_max === null) {
            return null;
        }

        $readingValue = (float) $reading;
        $expectedMin = (float) $equipment->daily_log_expected_min;
        $expectedMax = (float) $equipment->daily_log_expected_max;

        return ($readingValue >= $expectedMin && $readingValue <= $expectedMax) ? 'within' : 'outside';
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

    public function getNonConformanceRowsProperty(): array
    {
        if ($this->nonConformanceFromDate !== '' && $this->nonConformanceToDate !== ''
            && $this->nonConformanceFromDate > $this->nonConformanceToDate) {
            return [];
        }

        $query = EquipmentDailyLogEntry::where('company_id', getUserCompany())
            ->whereNotNull('recorded_value')
            ->where('recorded_value', '!=', '')
            ->with('equipment')
            ->whereHas('equipment', function ($q) {
                $q->where('requires_daily_log', true)
                    ->where('is_disposal', 0);
            })
            ->orderBy('log_date', 'desc')
            ->orderBy('slot_number', 'desc');

        if ($this->nonConformanceFromDate !== '') {
            $query->whereDate('log_date', '>=', $this->nonConformanceFromDate);
        }

        if ($this->nonConformanceToDate !== '') {
            $query->whereDate('log_date', '<=', $this->nonConformanceToDate);
        }

        $rows = [];

        foreach ($query->get() as $entry) {
            $equipment = $entry->equipment;
            if (!$equipment) {
                continue;
            }

            $reason = $this->getNonConformanceReason($equipment, $entry->recorded_value);

            if ($reason === null) {
                continue;
            }

            $rows[] = [
                'date' => $entry->log_date ? $entry->log_date->format('D, M j Y') : '-',
                'slot' => $entry->slot_number,
                'equipment_name' => $equipment->name ?? '-',
                'equipment_number' => $equipment->equipment_number ?? '-',
                'recorded' => $entry->recorded_value,
                'reason' => $reason,
            ];
        }

        return $rows;
    }

    public function getNonConformanceRowsPageProperty(): array
    {
        $rows = $this->nonConformanceRows;
        $perPage = max(10, $this->nonConformancePerPage);
        $totalPages = max(1, (int) ceil(count($rows) / $perPage));
        $currentPage = min(max(1, $this->nonConformancePage), $totalPages);

        if ($currentPage !== $this->nonConformancePage) {
            $this->nonConformancePage = $currentPage;
        }

        return array_slice($rows, ($currentPage - 1) * $perPage, $perPage);
    }

    public function getNonConformanceTotalPagesProperty(): int
    {
        $perPage = max(10, $this->nonConformancePerPage);

        return max(1, (int) ceil(count($this->nonConformanceRows) / $perPage));
    }

    public function isToday(): bool
    {
        return $this->logDate === now()->toDateString();
    }

    private function getNonConformanceReason(Equipment $equipment, ?string $recordedValue): ?string
    {
        $recordedValue = trim((string) $recordedValue);
        if ($recordedValue === '') {
            return null;
        }

        $type = $equipment->daily_log_value_type;
        $nature = $equipment->daily_log_nature;
        $tol = (int) ($equipment->daily_log_tolerance ?? 0);

        if ($type === 'constant') {
            if ($nature === 'qualitative') {
                $expected = trim((string) ($equipment->daily_log_expected_value ?? ''));
                if (strcasecmp($recordedValue, $expected) !== 0) {
                    return 'Does not match expected "' . $expected . '"';
                }

                return null;
            }

            if ($nature === 'quantitative' && is_numeric($recordedValue) && is_numeric($equipment->daily_log_expected_value)) {
                $expected = (float) $equipment->daily_log_expected_value;
                $actual = (float) $recordedValue;

                if ($tol > 0) {
                    $lo = $expected * (1 - $tol / 100);
                    $hi = $expected * (1 + $tol / 100);
                    if ($actual < $lo || $actual > $hi) {
                        return 'Value ' . $actual . ' outside tolerance ±' . $tol . '% of ' . $expected;
                    }
                } elseif ($actual != $expected) {
                    return 'Recorded ' . $actual . ', expected ' . $expected;
                }

                return null;
            }

            return null;
        }

        if ($type === 'range' && is_numeric($recordedValue)
            && $equipment->daily_log_expected_min !== null
            && $equipment->daily_log_expected_max !== null) {
            $actual = (float) $recordedValue;
            $min = (float) $equipment->daily_log_expected_min;
            $max = (float) $equipment->daily_log_expected_max;
            $lo = $tol > 0 ? $min * (1 - $tol / 100) : $min;
            $hi = $tol > 0 ? $max * (1 + $tol / 100) : $max;

            if ($actual < $lo || $actual > $hi) {
                return 'Recorded ' . $actual . ' outside ' . $min . '–' . $max . ($tol > 0 ? ' (±' . $tol . '% tol)' : '');
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.equipment.equipment-checks', [
            'equipment' => $this->getEquipmentForActiveTab(),
        ]);
    }
}
