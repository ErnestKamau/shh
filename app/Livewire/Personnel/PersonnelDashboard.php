<?php

namespace App\Livewire\Personnel;

use App\Lab;
use App\User;
use App\UserLabRelation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class PersonnelDashboard extends Component
{
    public int $totalPersonnel = 0;
    public int $activePersonnel = 0;
    public int $inactivePersonnel = 0;
    public int $newThisMonth = 0;
    /** @var array<int, array{label: string, used: int, limit: int}> */
    public array $analystGazzettedMatrix = [];

    /** @var array<int, array{lab: string, users: int}> */
    public array $organizationStructure = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $departmentDistribution = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $designationDistribution = [];

    /** @var array<int, array{month: string, count: int}> */
    public array $hireTrend = [];

    /** @var array<int, array{id: int, name: string, department: string, date: string}> */
    public array $recentJoiners = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    private function loadDashboardData(): void
    {
        $companyId = getUserCompany();
        $baseQuery = User::query()->where('company_id', $companyId);

        $totals = (clone $baseQuery)->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive_count
            ')->first();

        $this->totalPersonnel = (int) ($totals->total_count ?? 0);
        $this->activePersonnel = (int) ($totals->active_count ?? 0);
        $this->inactivePersonnel = (int) ($totals->inactive_count ?? 0);
        $this->newThisMonth = (clone $baseQuery)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $gazzettedCount = (clone $baseQuery)
            ->where('analyst_is_gazzetted', true)
            ->count();

        $ungazzettedCount = (clone $baseQuery)
            ->where(function ($query): void {
                $query->where('analyst_is_gazzetted', false)
                    ->orWhereNull('analyst_is_gazzetted');
            })
            ->count();

        $matrixTotal = max($this->totalPersonnel, 1);
        $this->analystGazzettedMatrix = [
            [
                'label' => 'Gazzetted',
                'used' => (int) $gazzettedCount,
                'limit' => $matrixTotal,
            ],
            [
                'label' => 'Ungazzetted',
                'used' => (int) $ungazzettedCount,
                'limit' => $matrixTotal,
            ],
        ];

        $labs = Lab::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $userCountsByLab = collect();
        if ($labs->isNotEmpty() && Schema::hasTable('user_lab_relation')) {
            $userCountsByLab = UserLabRelation::query()
                ->whereIn('lab_id', $labs->pluck('id'))
                ->selectRaw('lab_id, COUNT(DISTINCT user_id) as total')
                ->groupBy('lab_id')
                ->pluck('total', 'lab_id');
        }

        $this->organizationStructure = $labs
            ->map(fn (Lab $lab): array => [
                'lab' => (string) $lab->name,
                'users' => (int) ($userCountsByLab[$lab->id] ?? 0),
            ])
            ->values()
            ->toArray();

        $this->departmentDistribution = User::query()
            ->from('users')
            ->leftJoin('inventory_departments as departments', DB::raw('departments.id::text'), '=', DB::raw('users.department_id::text'))
            ->where('users.company_id', $companyId)
            ->where('users.active', 1)
            ->select(DB::raw("COALESCE(departments.name, 'Unassigned') as dept_name"), DB::raw('COUNT(users.id) as dept_count'))
            ->groupBy('departments.name')
            ->orderByDesc('dept_count')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->dept_name,
                'count' => (int) $row->dept_count,
            ])
            ->toArray();

        $this->designationDistribution = User::query()
            ->from('users')
            ->leftJoin('module_pre_configs as designation', function ($join): void {
                $join->on(DB::raw('designation.id::text'), '=', DB::raw('users.designation::text'))
                    ->where('designation.type', '=', 'Designation');
            })
            ->where('users.company_id', $companyId)
            ->where('users.active', 1)
            ->select(DB::raw("COALESCE(designation.name, 'Not Set') as desig_name"), DB::raw('COUNT(users.id) as desig_count'))
            ->groupBy('designation.name')
            ->orderByDesc('desig_count')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->desig_name,
                'count' => (int) $row->desig_count,
            ])
            ->toArray();

        $monthlyRows = User::query()
            ->where('company_id', $companyId)
            ->whereDate('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("TO_CHAR(created_at, 'Mon YYYY') as month, TO_CHAR(created_at, 'YYYY-MM') as sort_key, COUNT(*) as count")
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->keyBy('sort_key');

        $this->hireTrend = collect(range(5, 1))
            ->map(function (int $offset) use ($monthlyRows): array {
                $date = Carbon::now()->subMonths($offset);
                $key = $date->format('Y-m');
                $row = $monthlyRows->get($key);

                return [
                    'month' => $date->format('M Y'),
                    'count' => (int) ($row->count ?? 0),
                ];
            })
            ->push([
                'month' => Carbon::now()->format('M Y'),
                'count' => (int) ($monthlyRows->get(Carbon::now()->format('Y-m'))->count ?? 0),
            ])
            ->values()
            ->toArray();

        $this->recentJoiners = User::query()
            ->from('users')
            ->leftJoin('inventory_departments as departments', DB::raw('departments.id::text'), '=', DB::raw('users.department_id::text'))
            ->where('users.company_id', $companyId)
            ->select('users.id', 'users.name', DB::raw("COALESCE(departments.name, 'Unassigned') as department"), 'users.created_at')
            ->orderByDesc('users.created_at')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'department' => (string) $row->department,
                'date' => Carbon::parse($row->created_at)->format('M d, Y'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.personnel.personnel-dashboard');
    }
}
