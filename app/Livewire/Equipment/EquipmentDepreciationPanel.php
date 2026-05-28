<?php

namespace App\Livewire\Equipment;

use App\Enums\Equipment\AppraisalStatus;
use App\Jobs\Equipment\ProcessEquipmentAppraisalJob;
use App\Jobs\Equipment\RecalculateDepreciationScheduleJob;
use App\Models\Equipments\Depreciation\DepreciationSchedule;
use App\Models\Equipments\Depreciation\DepreciationScheduleVersion;
use App\Models\Equipments\Depreciation\EquipmentAppraisal;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Models\Equipments\Equipment;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EquipmentDepreciationPanel extends Component
{
    use WithPagination;

    public string $equipmentId;

    public string $analysisYear = '';

    public string $analysisQuarter = '';

    public string $activeFrequency = '';

    public string $activeSection = 'schedule';

    public ?string $selectedVersionId = null;

    public bool $showAppraisalModal = false;

    public int $perPage = 25;

    public array $appraisalForm = [
        'appraisal_date' => '',
        'appraisal_value' => '',
        'useful_life_extension_years' => 0,
        'reason' => '',
        'notes' => '',
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount(string $equipmentId, DepreciationService $depreciationService): void
    {
        $this->equipmentId = $equipmentId;
        $this->analysisYear = (string) now()->year;
        $this->appraisalForm['appraisal_date'] = now()->format('Y-m-d');

        $config = Equipment::query()
            ->with('depreciationConfig.activeScheduleVersion')
            ->findOrFail($equipmentId)
            ->depreciationConfig;

        if ($config?->enable_depreciation && $config->active_schedule_version_id) {
            $depreciationService->syncBookValueSnapshot($config);
            $config->refresh();
        }

        if ($config) {
            $this->activeFrequency = $config->resolvedFrequencies()[0] ?? 'monthly';
        }
    }

    public function getEquipmentProperty(): Equipment
    {
        return Equipment::query()
            ->with([
                'depreciationConfig.method',
                'depreciationConfig.activeScheduleVersion.schedules',
                'depreciationConfig.scheduleVersions',
                'appraisals' => fn ($q) => $q->orderByDesc('appraisal_date'),
            ])
            ->findOrFail($this->equipmentId);
    }

    public function getConfigProperty(): ?EquipmentDepreciationConfig
    {
        return $this->equipment->depreciationConfig;
    }

    public function setActiveFrequency(string $frequency): void
    {
        $this->activeFrequency = $frequency;
        $this->resetPage('schedulePage');
        $this->resetPage('analysisPage');
    }

    public function setActiveSection(string $section): void
    {
        $allowed = ['schedule', 'analysis', 'yearly', 'timeline', 'appraisals'];
        if (in_array($section, $allowed, true)) {
            $this->activeSection = $section;
        }
    }

    public function updatedSelectedVersionId(): void
    {
        $this->resetPage('schedulePage');
        $this->resetPage('analysisPage');
    }

    public function updatedAnalysisYear(): void
    {
        $this->resetPage('analysisPage');
    }

    public function updatedAnalysisQuarter(): void
    {
        $this->resetPage('analysisPage');
    }

    public function updatedPerPage(): void
    {
        $this->perPage = max(10, min(100, $this->perPage));
        $this->resetPage('schedulePage');
        $this->resetPage('analysisPage');
    }

    public function getAppraisalCurrentBookValueProperty(): float
    {
        $config = $this->config;

        return (float) ($config?->current_book_value ?? $config?->capitalized_amount ?? 0);
    }

    public function getAppraisalNewBookValueProperty(): ?float
    {
        if ($this->appraisalForm['appraisal_value'] === '' || $this->appraisalForm['appraisal_value'] === null) {
            return null;
        }

        $appraisalAmount = (float) $this->appraisalForm['appraisal_value'];

        return max(0, round($this->appraisalCurrentBookValue + $appraisalAmount, 2));
    }

    public function getAppraisalValueChangeProperty(): ?float
    {
        if ($this->appraisalForm['appraisal_value'] === '' || $this->appraisalForm['appraisal_value'] === null) {
            return null;
        }

        return round((float) $this->appraisalForm['appraisal_value'], 2);
    }

    public function openAppraisalModal(): void
    {
        $this->appraisalForm = [
            'appraisal_date' => now()->format('Y-m-d'),
            'appraisal_value' => '',
            'useful_life_extension_years' => 0,
            'reason' => '',
            'notes' => '',
        ];
        $this->showAppraisalModal = true;
    }

    public function saveAppraisal(): void
    {
        $this->validate([
            'appraisalForm.appraisal_date' => 'required|date',
            'appraisalForm.appraisal_value' => 'required|numeric|min:0',
            'appraisalForm.useful_life_extension_years' => 'nullable|integer|min:0|max:50',
            'appraisalForm.reason' => 'required|string|max:500',
            'appraisalForm.notes' => 'nullable|string',
        ]);

        $config = $this->config;
        if (! $config || ! $config->enable_depreciation) {
            session()->flash('depreciation_error', 'Depreciation must be enabled for this equipment.');

            return;
        }

        $newBookValue = $this->appraisalNewBookValue;
        if ($newBookValue === null) {
            session()->flash('depreciation_error', 'Enter a valid appraisal value.');

            return;
        }

        EquipmentAppraisal::query()->create([
            'equipment_id' => $this->equipmentId,
            'equipment_depreciation_config_id' => $config->id,
            'appraisal_date' => $this->appraisalForm['appraisal_date'],
            'prior_book_value' => $this->appraisalCurrentBookValue,
            'new_appraised_value' => $newBookValue,
            'useful_life_extension_years' => (int) ($this->appraisalForm['useful_life_extension_years'] ?? 0),
            'reason' => $this->appraisalForm['reason'],
            'notes' => $this->appraisalForm['notes'] ?? null,
            'status' => AppraisalStatus::Pending->value,
            'created_by' => Auth::id(),
        ]);

        $this->showAppraisalModal = false;
        session()->flash('depreciation_message', 'Appraisal submitted for approval.');
    }

    public function approveAppraisal(string $appraisalId): void
    {
        if (! Auth::user()?->can('equipment.components.depreciation.appraisal.approve')) {
            return;
        }

        $appraisal = EquipmentAppraisal::query()->findOrFail($appraisalId);
        $appraisal->update([
            'status' => AppraisalStatus::Approved->value,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        ProcessEquipmentAppraisalJob::dispatch($appraisal->id, Auth::id());
        session()->flash('depreciation_message', 'Appraisal approved; schedule recalculation queued.');
    }

    public function rejectAppraisal(string $appraisalId): void
    {
        if (! Auth::user()?->can('equipment.components.depreciation.appraisal.approve')) {
            return;
        }

        EquipmentAppraisal::query()->where('id', $appraisalId)->update([
            'status' => AppraisalStatus::Rejected->value,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
    }

    public function recalculateSchedule(): void
    {
        if (! Auth::user()?->can('equipment.components.depreciation.recalculate')) {
            return;
        }

        $config = $this->config;
        if ($config) {
            RecalculateDepreciationScheduleJob::dispatch($config->id, Auth::id(), 'manual');
            session()->flash('depreciation_message', 'Recalculation queued.');
        }
    }

    public function currentPeriodScheduleIdForFrequency(string $frequency): ?string
    {
        $config = $this->config;
        $version = $this->activeVersion;
        if (! $config || ! $version || $frequency === '') {
            return null;
        }

        $schedules = $version->schedules
            ->where('frequency', $frequency)
            ->sortBy('period_index')
            ->values();

        $current = app(DepreciationService::class)->resolveCurrentPeriodSchedule(
            $config,
            $schedules,
            now()->startOfDay()
        );

        return $current?->id;
    }

    public function isCurrentSchedulePeriod(DepreciationSchedule $schedule): bool
    {
        if ($this->activeFrequency !== ($schedule->frequency ?? '')) {
            return false;
        }

        return $schedule->id === $this->currentPeriodScheduleIdForFrequency($this->activeFrequency);
    }

    public function getTimelineEventsProperty(): array
    {
        $events = [];
        $equipment = $this->equipment;
        $config = $this->config;

        if ($equipment->date_purchased) {
            $events[] = ['date' => $equipment->date_purchased, 'label' => 'Purchase', 'type' => 'purchase'];
        }

        if ($config?->depreciation_start_date) {
            $events[] = [
                'date' => $config->depreciation_start_date->format('Y-m-d'),
                'label' => 'Depreciation started',
                'type' => 'start',
            ];
        }

        foreach ($this->equipment->appraisals as $appraisal) {
            $events[] = [
                'date' => $appraisal->appraisal_date->format('Y-m-d'),
                'label' => 'Appraisal: ' . $appraisal->reason . ' (' . $appraisal->status->value . ')',
                'type' => 'appraisal',
            ];
        }

        if ($config) {
            foreach ($config->scheduleVersions as $version) {
                $events[] = [
                    'date' => $version->created_at->format('Y-m-d'),
                    'label' => 'Schedule v' . $version->version_number . ': ' . ($version->reason ?? 'update'),
                    'type' => 'version',
                ];
            }
        }

        usort($events, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        return $events;
    }

    /**
     * @param  Collection<int, DepreciationSchedule>  $rows
     * @return Collection<int, DepreciationSchedule>
     */
    protected function filterSchedulesByContext(Collection $rows): Collection
    {
        $rows = $rows->filter(fn ($s) => ($s->frequency ?? '') === $this->activeFrequency);

        if ($this->analysisYear) {
            $rows = $rows->filter(fn ($s) => $s->period_date->format('Y') === $this->analysisYear);
        }
        if ($this->analysisQuarter) {
            $q = (int) $this->analysisQuarter;
            $rows = $rows->filter(fn ($s) => (int) ceil($s->period_date->month / 3) === $q);
        }

        return $rows;
    }

    public function getYearlyAnalysisProperty(): array
    {
        $version = $this->activeVersion;
        if (! $version) {
            return [];
        }

        $rows = $this->filterSchedulesByContext($version->schedules);

        return $rows
            ->groupBy(fn ($s) => $s->period_date->format('Y'))
            ->map(function ($group, $year) {
                return [
                    'year' => $year,
                    'depreciation' => $group->sum('depreciation_amount'),
                    'closing_value' => $group->last()->closing_book_value ?? 0,
                    'accumulated' => $group->last()->accumulated_depreciation ?? 0,
                ];
            })
            ->values()
            ->all();
    }

    public function getActiveVersionProperty(): ?DepreciationScheduleVersion
    {
        if ($this->selectedVersionId) {
            return DepreciationScheduleVersion::query()
                ->with('schedules')
                ->find($this->selectedVersionId);
        }

        $config = $this->config;

        return $config?->activeScheduleVersion?->load('schedules');
    }

    /**
     * @param  Collection<int, DepreciationSchedule>  $items
     */
    protected function paginateCollection(Collection $items, string $pageName): LengthAwarePaginator
    {
        $page = max(1, (int) $this->getPage($pageName));
        $perPage = max(10, min(100, $this->perPage));
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    public function render()
    {
        $scheduleQuery = $this->activeVersion
            ? $this->activeVersion->schedules()
            : null;

        if ($scheduleQuery && $this->activeFrequency !== '') {
            $scheduleQuery->where('frequency', $this->activeFrequency);
        }

        $schedules = $scheduleQuery
            ? $scheduleQuery->orderBy('period_index')->paginate($this->perPage, ['*'], 'schedulePage')
            : null;

        $periodAnalysis = null;
        $version = $this->activeVersion;
        if ($version) {
            $analysisRows = $this->filterSchedulesByContext($version->schedules)->sortBy('period_index')->values();
            $periodAnalysis = $this->paginateCollection($analysisRows, 'analysisPage');
        }

        return view('livewire.equipment.equipment-depreciation-panel', [
            'schedules' => $schedules,
            'periodAnalysis' => $periodAnalysis,
            'versions' => $this->config?->scheduleVersions ?? collect(),
        ]);
    }
}
