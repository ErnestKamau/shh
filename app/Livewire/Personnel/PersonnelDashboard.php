<?php

namespace App\Livewire\Personnel;

use App\User;
use Carbon\Carbon;
use Livewire\Component;

class PersonnelDashboard extends Component
{
    public int $totalPersonnel = 0;
    public int $activePersonnel = 0;
    public int $inactivePersonnel = 0;
    public int $newThisMonth = 0;
    public int $namedUsers = 0;
    public int $sharedUsers = 0;

    /** @var array<int, array{label: string, used: int, limit: int}> */
    public array $licenseUsage = [];

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

        $totals = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive_count,
                SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month
            ', [now()->month, now()->year])
            ->first();

        $this->totalPersonnel = (int) ($totals->total_count ?? 0);
        $this->activePersonnel = (int) ($totals->active_count ?? 0);
        $this->inactivePersonnel = (int) ($totals->inactive_count ?? 0);
        $this->newThisMonth = (int) ($totals->new_this_month ?? 0);

        $this->namedUsers = (clone $baseQuery)->where('license_type', 'named_user')->count();
        $this->sharedUsers = (clone $baseQuery)->where('license_type', 'shared_user')->count();

        $availableLicenses = getUserLicenses();
        $licenseCounts = [
            'named_user' => $this->namedUsers,
            'shared_user' => $this->sharedUsers,
        ];

        $this->licenseUsage = collect($availableLicenses)
            ->map(function (string $label, string $key) use ($licenseCounts): array {
                $limit = (int) mamboSawa($key . 's');

                return [
                    'label' => $label,
                    'used' => (int) ($licenseCounts[$key] ?? 0),
                    'limit' => $limit,
                ];
            })
            ->values()
            ->toArray();

        $this->departmentDistribution = User::query()
            ->from('users')
            ->leftJoin('inventory_departments as departments', 'departments.id', '=', 'users.department_id')
            ->where('users.company_id', $companyId)
            ->where('users.active', 1)
            ->selectRaw('COALESCE(departments.name, "Unassigned") as name, COUNT(users.id) as count')
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'count' => (int) $row->count,
            ])
            ->toArray();

        $this->designationDistribution = User::query()
            ->from('users')
            ->leftJoin('module_pre_configs as designation', function ($join): void {
                $join->on('designation.id', '=', 'users.designation')
                    ->where('designation.type', '=', 'Designation');
            })
            ->where('users.company_id', $companyId)
            ->where('users.active', 1)
            ->selectRaw('COALESCE(designation.name, "Not Set") as name, COUNT(users.id) as count')
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'count' => (int) $row->count,
            ])
            ->toArray();

        $monthlyRows = User::query()
            ->where('company_id', $companyId)
            ->whereDate('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%b %Y') as month, DATE_FORMAT(created_at, '%Y-%m') as sort_key, COUNT(*) as count")
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
            ->leftJoin('inventory_departments as departments', 'departments.id', '=', 'users.department_id')
            ->where('users.company_id', $companyId)
            ->selectRaw('users.id, users.name, COALESCE(departments.name, "Unassigned") as department, users.created_at')
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

