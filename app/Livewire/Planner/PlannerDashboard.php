<?php

namespace App\Livewire\Planner;

use App\Event;
use App\Models\SamplingSchedule;
use App\Services\Planner\PlannerKpiReportService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class PlannerDashboard extends Component
{
    public int $totalSchedules = 0;

    public int $upcomingSchedules = 0;

    public int $overdueSchedules = 0;

    public int $collectedSchedules = 0;

    public int $pendingSchedules = 0;

    public float $collectionRate = 0.0;

    public int $openTasks = 0;

    public int $completedTasks = 0;

    /** @var list<array{label: string, value: int, color: string}> */
    public array $collectionStatusChart = [];

    /** @var list<array{label: string, value: int, color: string}> */
    public array $frequencyChart = [];

    /** @var list<array{label: string, value: int, color: string}> */
    public array $taskStatusChart = [];

    /** @var list<array{month: string, scheduled: int, collected: int}> */
    public array $monthlyTrend = [];

    /** @var list<array{label: string, value: int}> */
    public array $topClients = [];

    /** @var list<array{label: string, value: int}> */
    public array $personnelWorkload = [];

    /** @var list<array{id: string, title: string, client: string, when: string, location: string, personnel: string}> */
    public array $upcomingList = [];

    /** @var list<array{id: string, title: string, client: string, when: string, location: string}> */
    public array $overdueList = [];

    /** @var list<array{id: string, title: string, client: string, when: string, personnel: string}> */
    public array $recentCollections = [];

    public function mount(PlannerKpiReportService $kpiService): void
    {
        $this->loadDashboardData($kpiService);
    }

    private function loadDashboardData(PlannerKpiReportService $kpiService): void
    {
        $companyId = getUserCompany();
        $now = Carbon::now();
        $weekEnd = $now->copy()->addDays(7)->endOfDay();

        $schedules = SamplingSchedule::query()
            ->where('company_id', $companyId)
            ->with(['client', 'samplePoint'])
            ->orderByDesc('sampling_datetime')
            ->get();

        $this->totalSchedules = $schedules->count();
        $this->collectedSchedules = $schedules->where('is_collected', true)->count();
        $this->pendingSchedules = $schedules->where('is_collected', false)->count();

        $this->upcomingSchedules = $schedules
            ->filter(function (SamplingSchedule $schedule) use ($now, $weekEnd) {
                if ($schedule->is_collected || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->between($now, $weekEnd);
            })
            ->count();

        $this->overdueSchedules = $schedules
            ->filter(function (SamplingSchedule $schedule) use ($now) {
                if ($schedule->is_collected || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->lt($now);
            })
            ->count();

        $kpiRows = $kpiService->getReportRows([
            'date_from' => $now->copy()->subDays(30)->toDateString(),
            'date_to' => $now->copy()->addDays(7)->toDateString(),
        ], $companyId);
        $summary = $kpiService->summarize($kpiRows);
        $this->collectionRate = (float) ($summary['collection_rate'] ?? 0);

        $kpiCollected = (int) ($summary['collected'] ?? 0);
        $kpiPartial = (int) ($summary['partial'] ?? 0);
        $kpiPending = (int) ($summary['pending'] ?? 0);

        if (($kpiCollected + $kpiPartial + $kpiPending) === 0 && $this->totalSchedules > 0) {
            $kpiCollected = $this->collectedSchedules;
            $kpiPending = $this->pendingSchedules;
            $kpiPartial = 0;
        }

        if ($this->collectionRate <= 0.0 && $this->totalSchedules > 0) {
            $this->collectionRate = round(($this->collectedSchedules / $this->totalSchedules) * 100, 1);
        }

        $this->collectionStatusChart = [
            ['label' => 'Collected', 'value' => $kpiCollected, 'color' => '#2e7d32'],
            ['label' => 'Partial', 'value' => $kpiPartial, 'color' => '#f9a825'],
            ['label' => 'Pending', 'value' => $kpiPending, 'color' => '#8a1a1f'],
        ];

        $frequencyColors = [
            'One-time' => '#64748b',
            'Daily' => '#2563eb',
            'Weekly' => '#0d9488',
            'Monthly' => '#8a1a1f',
            'Quarterly' => '#c2410c',
            'Annually' => '#7c3aed',
        ];
        $this->frequencyChart = $schedules
            ->groupBy(fn (SamplingSchedule $s) => $s->frequency ?: 'One-time')
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'value' => $group->count(),
                'color' => $frequencyColors[$label] ?? '#94a3b8',
            ])
            ->sortByDesc('value')
            ->values()
            ->all();

        $this->monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $inMonth = $schedules->filter(function (SamplingSchedule $schedule) use ($monthStart, $monthEnd) {
                return $schedule->sampling_datetime
                    && $schedule->sampling_datetime->between($monthStart, $monthEnd);
            });

            $this->monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'scheduled' => $inMonth->count(),
                'collected' => $inMonth->where('is_collected', true)->count(),
            ];
        }

        $this->topClients = $schedules
            ->groupBy(fn (SamplingSchedule $s) => $s->client->name ?? 'Unknown')
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'value' => $group->count(),
            ])
            ->sortByDesc('value')
            ->take(6)
            ->values()
            ->all();

        $personnelCounts = [];
        foreach ($schedules as $schedule) {
            $ids = $schedule->resolvedPersonnelIds();
            if ($ids === []) {
                continue;
            }
            foreach ($ids as $personnelId) {
                $personnelCounts[$personnelId] = ($personnelCounts[$personnelId] ?? 0) + 1;
            }
        }
        $personnelNames = User::query()
            ->whereIn('id', array_keys($personnelCounts))
            ->pluck('name', 'id');
        $this->personnelWorkload = collect($personnelCounts)
            ->map(fn (int $count, string $id) => [
                'label' => (string) ($personnelNames[$id] ?? 'Unknown'),
                'value' => $count,
            ])
            ->sortByDesc('value')
            ->take(6)
            ->values()
            ->all();

        $this->upcomingList = $schedules
            ->filter(function (SamplingSchedule $schedule) use ($now, $weekEnd) {
                if ($schedule->is_collected || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->between($now, $weekEnd);
            })
            ->sortBy('sampling_datetime')
            ->take(8)
            ->map(fn (SamplingSchedule $schedule) => [
                'id' => (string) $schedule->id,
                'title' => (string) $schedule->title,
                'client' => (string) ($schedule->client->name ?? 'N/A'),
                'when' => $schedule->sampling_datetime?->format('d M Y H:i') ?? '—',
                'location' => $schedule->locationDisplayName(),
                'personnel' => $schedule->personnelNames(),
            ])
            ->values()
            ->all();

        $this->overdueList = $schedules
            ->filter(function (SamplingSchedule $schedule) use ($now) {
                if ($schedule->is_collected || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->lt($now);
            })
            ->sortByDesc('sampling_datetime')
            ->take(8)
            ->map(fn (SamplingSchedule $schedule) => [
                'id' => (string) $schedule->id,
                'title' => (string) $schedule->title,
                'client' => (string) ($schedule->client->name ?? 'N/A'),
                'when' => $schedule->sampling_datetime?->format('d M Y H:i') ?? '—',
                'location' => $schedule->locationDisplayName(),
            ])
            ->values()
            ->all();

        $this->recentCollections = $schedules
            ->where('is_collected', true)
            ->sortByDesc('updated_at')
            ->take(8)
            ->map(fn (SamplingSchedule $schedule) => [
                'id' => (string) $schedule->id,
                'title' => (string) $schedule->title,
                'client' => (string) ($schedule->client->name ?? 'N/A'),
                'when' => $schedule->updated_at?->format('d M Y H:i') ?? '—',
                'personnel' => $schedule->personnelNames(),
            ])
            ->values()
            ->all();

        $taskQuery = Event::query()
            ->where('status', '!=', 'Pending')
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhereColumn('id', 'parent_id')
                    ->orWhere('is_routine', '!=', 1)
                    ->orWhereNotIn('frequency', [1, 7, 30]);
            });

        $taskCounts = (clone $taskQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->whereIn('status', ['Upcoming', 'Complete', 'Delayed', 'Cancelled', 'Expired', 'In-progress'])
            ->groupBy('status')
            ->pluck('count', 'status');

        $this->openTasks = (int) (
            ($taskCounts['Upcoming'] ?? 0)
            + ($taskCounts['In-progress'] ?? 0)
            + ($taskCounts['Delayed'] ?? 0)
        );
        $this->completedTasks = (int) ($taskCounts['Complete'] ?? 0);

        $taskColors = [
            'Upcoming' => '#2196f3',
            'In-progress' => '#1565c0',
            'Delayed' => '#e1b200',
            'Complete' => '#2e7d32',
            'Expired' => '#e65100',
            'Cancelled' => '#c62828',
        ];
        $this->taskStatusChart = collect($taskColors)
            ->map(fn (string $color, string $label) => [
                'label' => $label,
                'value' => (int) ($taskCounts[$label] ?? 0),
                'color' => $color,
            ])
            ->filter(fn (array $row) => $row['value'] > 0)
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.planner.planner-dashboard');
    }
}
