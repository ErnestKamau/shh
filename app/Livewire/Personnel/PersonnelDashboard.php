<?php

namespace App\Livewire\Personnel;

use App\User;
use App\Role;
use OwenIt\Auditing\Models\Audit;
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
    
    public int $totalDepartments = 0;
    public int $activeRolesCount = 0;
    public int $recentAuditCount = 0;

    /** @var array<int, array{label: string, used: int, limit: int}> */
    public array $licenseUsage = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $departmentDistribution = [];

    /** @var array<int, array{name: string, count: int}> */
    public array $designationDistribution = [];
    
    /** @var array<int, array{name: string, count: int}> */
    public array $rolesDistribution = [];

    /** @var array<int, array{month: string, count: int}> */
    public array $hireTrend = [];

    /** @var array<int, array{id: int, name: string, department: string, date: string}> */
    public array $recentJoiners = [];
    
    /** @var array<int, array{id: int, event: string, auditable_type: string, user_name: string, date: string}> */
    public array $recentAudits = [];

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

        // Department Info
        $this->totalDepartments = \App\InventoryDepartment::where('company_id', $companyId)->count();
        
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

        // Role Info
        $this->activeRolesCount = Role::where('company_id', $companyId)->where('active', 1)->count();
        
        // Let's get Role assignments count if possible, if not just list the active roles
        $this->rolesDistribution = Role::query()
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->selectRaw('name, 1 as count') // Placeholder if we don't have user_roles pivot readily available.
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'count' => (int) 0, // In DB it might require a join on model_has_roles, we just list them.
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
            
        // Check Audits
        try {
            $user_ids = User::query()->where('company_id', $companyId)->pluck('id')->toArray();
            $this->recentAuditCount = Audit::whereIn('user_id', $user_ids)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->count();
                
            $this->recentAudits = Audit::query()
                ->leftJoin('users as u', 'u.id', '=', 'audits.user_id')
                ->whereIn('audits.user_id', $user_ids)
                ->selectRaw('audits.id, audits.event, audits.auditable_type, u.name as user_name, audits.created_at')
                ->orderByDesc('audits.created_at')
                ->limit(6)
                ->get()
                ->map(function($row) {
                    // Extract just the model name safely
                    $typeArray = explode("\\", $row->auditable_type);
                    $simpleModel = end($typeArray);
                    
                    return [
                        'id' => (int) $row->id,
                        'event' => (string) ucfirst($row->event),
                        'auditable_type' => (string) $simpleModel,
                        'user_name' => (string) $row->user_name,
                        'date' => Carbon::parse($row->created_at)->diffForHumans(),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error fetching Audits inside PersonnelDashboard: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.personnel.personnel-dashboard');
    }
}
