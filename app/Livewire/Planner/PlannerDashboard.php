<?php

namespace App\Livewire\Planner;

use App\Event;
use App\Models\SamplingSchedule;
use App\Services\Planner\PlannerKpiReportService;
use App\Services\Planner\SamplingScheduleVisibility;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

    public bool $showMySchedules = false;

    public bool $canViewAllData = false;

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

    /** @var list<array{id: string, title: string, client: string, when: string, location: string, personnel: string, status: string, progress: string, scheduled: int, collected: int, is_collected: bool}> */
    public array $mySchedules = [];

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
        $userId = (string) (Auth::id() ?? '');
        $this->canViewAllData = SamplingScheduleVisibility::canViewAll();

        $schedules = SamplingSchedule::query()
            ->where('company_id', $companyId)
            ->visibleTo()
            ->with(['client', 'samplePoint', 'submissionFormInstances.values.element'])
            ->orderByDesc('sampling_datetime')
            ->get();

        $assignedSchedules = $userId !== ''
            ? SamplingScheduleVisibility::assignedQuery($userId, $companyId)
                ->with(['client', 'samplePoint', 'submissionFormInstances.values.element'])
                ->orderBy('sampling_datetime')
                ->get()
            : collect();

        // Non-admins always work from their assigned schedules for list widgets.
        $scopedSchedules = $this->canViewAllData ? $schedules : $assignedSchedules;

        $this->showMySchedules = $assignedSchedules->isNotEmpty();
        $this->mySchedules = $assignedSchedules
            ->take(12)
            ->map(function (SamplingSchedule $schedule) use ($now) {
                $progress = $schedule->collectionProgress();
                $status = match ($progress['status']) {
                    'collected' => 'Collected',
                    'partial' => 'Partial',
                    default => ($schedule->sampling_datetime && $schedule->sampling_datetime->lt($now) ? 'Overdue' : 'Upcoming'),
                };

                $sampleTypeId = ! empty($schedule->sample_type_id)
                    ? (string) $schedule->sample_type_id
                    : (string) (collect($schedule->sample_details ?? [])->pluck('sample_type_id')->filter()->first() ?? '');

                return [
                    'id' => (string) $schedule->id,
                    'title' => (string) $schedule->title,
                    'client' => (string) ($schedule->client->name ?? 'N/A'),
                    'when' => $schedule->sampling_datetime?->format('d M Y H:i') ?? '—',
                    'location' => $schedule->locationDisplayName(),
                    'personnel' => $schedule->personnelNames(),
                    'status' => $status,
                    'progress' => $progress['label'],
                    'scheduled' => $progress['scheduled'],
                    'collected' => $progress['collected'],
                    'is_collected' => $progress['is_complete'],
                    'sample_type_id' => $sampleTypeId,
                    'fill_url' => route('system-planner.fill-sampling-forms'),
                ];
            })
            ->values()
            ->all();

        $this->totalSchedules = $scopedSchedules->count();
        $progressStatuses = $scopedSchedules->map(fn (SamplingSchedule $schedule) => $schedule->collectionStatus());
        $this->collectedSchedules = $progressStatuses->filter(fn ($status) => $status === 'collected')->count();
        $partialSchedules = $progressStatuses->filter(fn ($status) => $status === 'partial')->count();
        $this->pendingSchedules = $progressStatuses->filter(fn ($status) => $status === 'pending')->count() + $partialSchedules;

        $this->upcomingSchedules = $scopedSchedules
            ->filter(function (SamplingSchedule $schedule) use ($now, $weekEnd) {
                if ($schedule->collectionStatus() === 'collected' || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->between($now, $weekEnd);
            })
            ->count();

        $this->overdueSchedules = $scopedSchedules
            ->filter(function (SamplingSchedule $schedule) use ($now) {
                if ($schedule->collectionStatus() === 'collected' || ! $schedule->sampling_datetime) {
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
            $kpiPartial = $partialSchedules;
            $kpiPending = $progressStatuses->filter(fn ($status) => $status === 'pending')->count();
        }

        if ($this->collectionRate <= 0.0) {
            $scheduledSamples = (int) $scopedSchedules->sum(fn (SamplingSchedule $s) => $s->collectionProgress()['scheduled']);
            $collectedSamples = (int) $scopedSchedules->sum(fn (SamplingSchedule $s) => $s->collectionProgress()['collected']);
            $this->collectionRate = $scheduledSamples > 0
                ? round($collectedSamples / $scheduledSamples * 100, 1)
                : 0.0;
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
        $this->frequencyChart = $scopedSchedules
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
            $inMonth = $scopedSchedules->filter(function (SamplingSchedule $schedule) use ($monthStart, $monthEnd) {
                return $schedule->sampling_datetime
                    && $schedule->sampling_datetime->between($monthStart, $monthEnd);
            });

            $this->monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'scheduled' => $inMonth->count(),
                'collected' => $inMonth->filter(fn (SamplingSchedule $s) => $s->collectionStatus() === 'collected')->count(),
            ];
        }

        $this->topClients = $scopedSchedules
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
        foreach ($scopedSchedules as $schedule) {
            $ids = $schedule->resolvedPersonnelIds();
            if ($ids === []) {
                continue;
            }
            foreach ($ids as $personnelId) {
                if (! $this->canViewAllData && $personnelId !== $userId) {
                    continue;
                }
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

        $this->upcomingList = $scopedSchedules
            ->filter(function (SamplingSchedule $schedule) use ($now, $weekEnd) {
                if ($schedule->collectionStatus() === 'collected' || ! $schedule->sampling_datetime) {
                    return false;
                }

                return $schedule->sampling_datetime->between($now, $weekEnd);
            })
            ->sortBy('sampling_datetime')
            ->take(8)
            ->map(function (SamplingSchedule $schedule) {
                $sampleTypeId = ! empty($schedule->sample_type_id)
                    ? (string) $schedule->sample_type_id
                    : (string) (collect($schedule->sample_details ?? [])->pluck('sample_type_id')->filter()->first() ?? '');

                return [
                    'id' => (string) $schedule->id,
                    'title' => (string) $schedule->title,
                    'client' => (string) ($schedule->client->name ?? 'N/A'),
                    'when' => $schedule->sampling_datetime?->format('d M Y H:i') ?? '—',
                    'location' => $schedule->locationDisplayName(),
                    'personnel' => $schedule->personnelNames(),
                    'sample_type_id' => $sampleTypeId,
                    'fill_url' => route('system-planner.fill-sampling-forms'),
                ];
            })
            ->values()
            ->all();

        $this->overdueList = $scopedSchedules
            ->filter(function (SamplingSchedule $schedule) use ($now) {
                if ($schedule->collectionStatus() === 'collected' || ! $schedule->sampling_datetime) {
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

        $this->recentCollections = $scopedSchedules
            ->filter(fn (SamplingSchedule $schedule) => $schedule->collectionStatus() === 'collected')
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

        if (! $this->canViewAllData && $userId !== '') {
            $taskQuery->where(function ($query) use ($userId): void {
                $query->where('responsible_id', $userId)
                    ->orWhere('created_by', $userId);
            });
        }

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
