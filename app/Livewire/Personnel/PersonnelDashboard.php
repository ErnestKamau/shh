<?php

namespace App\Livewire\Personnel;

use App\Lab;
use App\ModulePreConfigs;
use App\User;
use App\UserLabRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class PersonnelDashboard extends Component
{
    public int $totalPersonnel = 0;
    public int $activePersonnel = 0;
    public int $inactivePersonnel = 0;
    public int $newThisMonth = 0;

    /** @var array<int, array{lab: string, users: int}> */
    public array $organizationStructure = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $departmentDistribution = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $designationDistribution = [];

    /** @var array<int, array{month: string, count: int}> */
    public array $hireTrend = [];

    /** @var array<int, array{id: int|string, name: string, department: string, date: string}> */
    public array $recentJoiners = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    private function loadDashboardData(): void
    {
        $companyId = getUserCompany();
        $baseQuery = $this->personnelQuery($companyId);

        $totals = (clone $baseQuery)->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN users.active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN COALESCE(users.active, 0) = 0 THEN 1 ELSE 0 END) as inactive_count
            ')->first();

        $this->totalPersonnel = (int) ($totals->total_count ?? 0);
        $this->activePersonnel = (int) ($totals->active_count ?? 0);
        $this->inactivePersonnel = (int) ($totals->inactive_count ?? 0);

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $this->newThisMonth = (clone $baseQuery)
            ->whereRaw(
                'COALESCE(users.employment_date, users.created_at::date) BETWEEN ? AND ?',
                [$startOfMonth, $endOfMonth]
            )
            ->count();

        $this->organizationStructure = $this->buildLabAssignments($companyId);
        $this->departmentDistribution = $this->buildDepartmentDistribution($companyId);
        $this->designationDistribution = $this->buildDesignationDistribution($companyId);
        $this->hireTrend = $this->buildHireTrend($companyId);
        $this->recentJoiners = $this->buildRecentJoiners($companyId);
    }

    /**
     * Personnel accounts for the current company (excludes portal contacts / tablets).
     */
    private function personnelQuery(?string $companyId): Builder
    {
        return User::query()
            ->when($companyId, fn (Builder $query) => $query->where('users.company_id', $companyId))
            ->where(function (Builder $query): void {
                $query->where('users.is_client', 0)->orWhereNull('users.is_client');
            })
            ->where(function (Builder $query): void {
                $query->where('users.is_tablet', 0)->orWhereNull('users.is_tablet');
            })
            ->whereNull('users.crm_contact_id')
            ->whereNull('users.crmcontact_id');
    }

    /**
     * @return array<int, array{lab: string, users: int}>
     */
    private function buildLabAssignments(?string $companyId): array
    {
        $labs = Lab::query()
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $userCountsByLab = collect();
        if ($labs->isNotEmpty() && Schema::hasTable('user_lab_relation')) {
            $activePersonnelIds = $this->personnelQuery($companyId)
                ->where('users.active', 1)
                ->pluck('users.id');

            $userCountsByLab = UserLabRelation::query()
                ->whereIn('lab_id', $labs->pluck('id'))
                ->whereIn('user_id', $activePersonnelIds)
                ->selectRaw('lab_id, COUNT(DISTINCT user_id) as total')
                ->groupBy('lab_id')
                ->pluck('total', 'lab_id');
        }

        return $labs
            ->map(fn (Lab $lab): array => [
                'lab' => (string) $lab->name,
                'users' => (int) ($userCountsByLab[$lab->id] ?? 0),
            ])
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    private function buildDepartmentDistribution(?string $companyId): array
    {
        return $this->personnelQuery($companyId)
            ->leftJoin('inventory_departments as departments', DB::raw('departments.id::text'), '=', DB::raw('users.department_id::text'))
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
    }

    /**
     * Designation is encrypted on users, so aggregate after Eloquent decryption.
     *
     * @return array<int, array{name: string, count: int}>
     */
    private function buildDesignationDistribution(?string $companyId): array
    {
        $users = $this->personnelQuery($companyId)
            ->where('users.active', 1)
            ->get(['users.id', 'users.designation']);

        $designationIds = $users
            ->pluck('designation')
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->unique()
            ->values();

        $designationNames = ModulePreConfigs::query()
            ->whereIn('id', $designationIds)
            ->where('type', 'Job Description')
            ->get(['id', 'name', 'description'])
            ->mapWithKeys(function (ModulePreConfigs $item): array {
                $description = trim((string) ($item->description ?? ''));
                $label = $description !== '' ? $description : (string) $item->name;

                return [(string) $item->id => $label];
            });

        $counts = [];
        foreach ($users as $user) {
            $designationId = is_string($user->designation) ? $user->designation : '';
            $label = ($designationId !== '' && isset($designationNames[$designationId]))
                ? (string) $designationNames[$designationId]
                : 'Not Set';
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        arsort($counts);

        return collect($counts)
            ->take(6)
            ->map(fn (int $count, string $name): array => [
                'name' => $name,
                'count' => $count,
            ])
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    private function buildHireTrend(?string $companyId): array
    {
        $from = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();

        $monthlyRows = $this->personnelQuery($companyId)
            ->whereRaw('COALESCE(users.employment_date, users.created_at::date) >= ?', [$from])
            ->selectRaw("
                TO_CHAR(COALESCE(users.employment_date, users.created_at::date), 'Mon YYYY') as month,
                TO_CHAR(COALESCE(users.employment_date, users.created_at::date), 'YYYY-MM') as sort_key,
                COUNT(*) as count
            ")
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->keyBy('sort_key');

        return collect(range(5, 0))
            ->map(function (int $offset) use ($monthlyRows): array {
                $date = Carbon::now()->subMonths($offset);
                $key = $date->format('Y-m');
                $row = $monthlyRows->get($key);

                return [
                    'month' => $date->format('M Y'),
                    'count' => (int) ($row->count ?? 0),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, array{id: int|string, name: string, department: string, date: string}>
     */
    private function buildRecentJoiners(?string $companyId): array
    {
        return $this->personnelQuery($companyId)
            ->leftJoin('inventory_departments as departments', DB::raw('departments.id::text'), '=', DB::raw('users.department_id::text'))
            ->select(
                'users.id',
                'users.name',
                DB::raw("COALESCE(departments.name, 'Unassigned') as department"),
                DB::raw('COALESCE(users.employment_date, users.created_at::date) as joined_on')
            )
            ->orderByDesc(DB::raw('COALESCE(users.employment_date, users.created_at::date)'))
            ->orderByDesc('users.created_at')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'id' => $row->id,
                'name' => (string) $row->name,
                'department' => (string) $row->department,
                'date' => Carbon::parse($row->joined_on)->format('M d, Y'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.personnel.personnel-dashboard');
    }
}
