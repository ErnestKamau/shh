<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use Illuminate\Support\Collection;

class DashboardAnalyticsService
{
    private const ROLE_CHART_COLORS = ['#1D4ED8', '#7C3AED', '#0F766E', '#B45309', '#6B7280', '#DC2626', '#0891B2'];

    public function __construct(
        protected MatrixQueryService $matrixQuery,
        protected GapAnalysisService $gapAnalysis,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(?string $capabilityId = null): array
    {
        if ($capabilityId === null || $capabilityId === '') {
            return $this->placeholderDashboard();
        }

        $capability = CapabilityMatrix::with('skillmatrix')->find($capabilityId);
        if (! $capability) {
            return $this->placeholderDashboard();
        }

        $metrics = $this->metrics($capability->id);

        $roles = $this->matrixQuery->activeCapabilityRoles($capability->id);
        $roleColumns = $this->roleColumns($roles);
        $details = CapabilityMatrixDetail::with([
            'competency.competencyarea',
            'proficiency',
        ])->where('capability_id', $capability->id)->get();

        $domains = $this->distinctDomains($details);
        $metrics['domain_count'] = count($domains);
        $metrics['roles_with_backup'] = $roles->groupBy('role_id')->filter(fn ($g) => $g->count() >= 2)->count();
        $metrics['training_gaps_pct'] = $metrics['skills_tracked'] > 0
            ? min(100, round(($metrics['training_gaps'] / $metrics['skills_tracked']) * 100))
            : 0;
        $metrics['avg_competency_pct'] = min(100, round(($metrics['avg_competency'] / 3) * 100));

        return [
            'capability' => $capability,
            'metrics' => $metrics,
            'charts' => $this->buildCharts($capability, $roles, $roleColumns, $details, $domains),
            'heatmap' => $this->buildDomainHeatmap($capability, $roles, $roleColumns, $details, $domains),
            'domain_count' => count($domains),
            'roles_with_backup' => $metrics['roles_with_backup'],
            'is_placeholder' => false,
        ];
    }

    /**
     * Sample dashboard matching docs/lab_skills_capability_system.html until a capability is selected.
     *
     * @return array<string, mixed>
     */
    public function placeholderDashboard(): array
    {
        $metrics = [
            'team_members' => 5,
            'skills_tracked' => 97,
            'training_gaps' => 18,
            'critical_gaps' => 8,
            'minor_gaps' => 10,
            'exceeding' => 12,
            'avg_competency' => 2.1,
            'domain_count' => 7,
            'roles_with_backup' => 2,
            'training_gaps_pct' => 32,
            'avg_competency_pct' => 70,
        ];

        $roleLabels = ['Lab Lead', 'Lab Tech', 'Lab Asst 1', 'Lab Asst 2', 'Lab GW'];
        $roleColors = ['#1D4ED8', '#7C3AED', '#0F766E', '#B45309', '#6B7280'];

        $charts = [
            'byRole' => [
                'labels' => $roleLabels,
                'data' => [2.6, 2.1, 1.9, 1.8, 1.7],
                'colors' => $roleColors,
            ],
            'byDomain' => [
                'labels' => ['Gen Lab', 'Admin', 'Basic Skills', 'Diagnostics', 'Management', 'IT'],
                'actual' => [2.6, 2.2, 2.5, 2.1, 1.8, 1.5],
                'required' => [2.8, 2.4, 2.7, 2.3, 2.4, 1.9],
            ],
            'gapsByRole' => [
                'labels' => ['Lab Lead', 'Lab Tech', 'Lab Asst', 'Lab GW'],
                'critical' => [2, 3, 5, 7],
                'minor' => [3, 5, 6, 4],
                'exceed' => [5, 4, 6, 3],
            ],
            'levelBreakdown' => [
                'counts' => [34, 38, 28],
                'percents' => [34, 38, 28],
            ],
        ];

        $heatmap = [
            'role_labels' => $roleLabels,
            'rows' => [
                $this->placeholderHeatmapRow('General Laboratory', [3, 2.8, 2.6, 2.4, 2.4], 2.6),
                $this->placeholderHeatmapRow('Administration', [2.8, 2.2, 2.0, 2.0, 1.8], 2.2),
                $this->placeholderHeatmapRow('Basic Lab Skills', [2.9, 2.5, 2.4, 2.3, 2.3], 2.5),
                $this->placeholderHeatmapRow('Diagnostics', [2.7, 2.4, 2.0, 1.8, 1.8], 2.1),
                $this->placeholderHeatmapRow('Management', [2.9, 2.0, 1.6, 1.3, 1.1], 1.8),
                $this->placeholderHeatmapRow('IT & Software', [2.0, 1.5, 1.4, 1.4, 1.0], 1.5),
            ],
        ];

        return [
            'capability' => null,
            'metrics' => $metrics,
            'charts' => $charts,
            'heatmap' => $heatmap,
            'domain_count' => 7,
            'roles_with_backup' => 2,
            'is_placeholder' => true,
        ];
    }

    /**
     * @param  array<int, float>  $roleValues
     * @return array{domain: string, cells: array<int, array{value: float, class: string}>, team_avg: array{value: float, class: string}}
     */
    protected function placeholderHeatmapRow(string $domain, array $roleValues, float $teamAvg): array
    {
        return [
            'domain' => $domain,
            'cells' => array_map(fn (float $v) => [
                'value' => $v,
                'class' => $this->levelClassForAverage($v),
            ], $roleValues),
            'team_avg' => [
                'value' => $teamAvg,
                'class' => $this->levelClassForAverage($teamAvg),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(?string $capabilityId = null): array
    {
        $capabilities = $capabilityId
            ? CapabilityMatrix::where('id', $capabilityId)->get()
            : CapabilityMatrix::where('status', 1)->whereNull('deleted_at')->get();

        $teamCount = CapabilityMatrixRoles::whereNull('deleted_at')
            ->when($capabilityId, fn ($q) => $q->where('capability_id', $capabilityId))
            ->distinct('user_id')
            ->count('user_id');

        $skillCount = CapabilityMatrixDetail::query()
            ->when($capabilityId, fn ($q) => $q->where('capability_id', $capabilityId))
            ->count();

        $gaps = ['critical' => 0, 'minor' => 0, 'exceeding' => 0];
        foreach ($capabilities as $cap) {
            $analysis = $this->gapAnalysis->analyzeCapability($cap);
            $gaps['critical'] += $analysis['critical'];
            $gaps['minor'] += $analysis['minor'];
            $gaps['exceeding'] += $analysis['exceeding'];
        }

        $avgCompetency = $this->averageProficiencyCode($capabilityId);

        return [
            'team_members' => $teamCount,
            'skills_tracked' => $skillCount,
            'training_gaps' => $gaps['critical'] + $gaps['minor'],
            'critical_gaps' => $gaps['critical'],
            'minor_gaps' => $gaps['minor'],
            'exceeding' => $gaps['exceeding'],
            'avg_competency' => $avgCompetency,
            'domain_count' => 0,
            'roles_with_backup' => 0,
            'training_gaps_pct' => 0,
            'avg_competency_pct' => min(100, round(($avgCompetency / 3) * 100)),
        ];
    }

    /**
     * @param  Collection<int, CapabilityMatrixRoles>  $roles
     * @return array<int, array{role_id: string, label: string, color: string}>
     */
    protected function roleColumns(Collection $roles): array
    {
        $columns = [];
        $i = 0;
        foreach ($roles->groupBy('role_id') as $roleId => $group) {
            $first = $group->first();
            $columns[] = [
                'role_id' => (string) $roleId,
                'label' => $first->jobdescription->name ?? 'Role',
                'color' => self::ROLE_CHART_COLORS[$i % count(self::ROLE_CHART_COLORS)],
            ];
            $i++;
        }

        return $columns;
    }

    /**
     * @param  Collection<int, CapabilityMatrixDetail>  $details
     * @return array<int, string>
     */
    protected function distinctDomains(Collection $details): array
    {
        return $details
            ->map(fn ($d) => $d->competency->competencyarea->description ?? 'Other')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{role_id: string, label: string, color: string}>  $roleColumns
     * @param  array<int, string>  $domains
     * @return array<string, mixed>
     */
    protected function buildCharts(
        CapabilityMatrix $capability,
        Collection $roles,
        array $roleColumns,
        Collection $details,
        array $domains,
    ): array {
        $gapAnalysis = $this->gapAnalysis->analyzeCapability($capability);

        $byRoleLabel = [];
        $criticalByRole = [];
        $minorByRole = [];
        $exceedByRole = [];
        foreach ($roleColumns as $col) {
            $byRoleLabel[$col['label']] = ['sum' => 0, 'count' => 0];
            $criticalByRole[$col['label']] = 0;
            $minorByRole[$col['label']] = 0;
            $exceedByRole[$col['label']] = 0;
        }

        foreach ($details as $detail) {
            $role = $roles->firstWhere('user_id', $detail->user_id);
            if (! $role) {
                continue;
            }
            $label = $role->jobdescription->name ?? 'Role';
            if (! isset($byRoleLabel[$label])) {
                continue;
            }
            $code = (int) ($detail->proficiency->code ?? 0);
            $byRoleLabel[$label]['sum'] += $code;
            $byRoleLabel[$label]['count']++;
        }

        foreach ($gapAnalysis['rows'] as $row) {
            foreach ($row['gaps'] as $gapCell) {
                $name = $gapCell['role_name'] ?? '';
                if ($name === '' || ! isset($criticalByRole[$name])) {
                    continue;
                }
                $gap = $gapCell['gap'];
                if ($gap === null) {
                    continue;
                }
                if ($gap < -1) {
                    $criticalByRole[$name]++;
                } elseif ($gap < 0) {
                    $minorByRole[$name]++;
                } elseif ($gap > 0) {
                    $exceedByRole[$name]++;
                }
            }
        }

        $roleLabels = array_column($roleColumns, 'label');
        $roleAvgs = [];
        foreach ($roleLabels as $label) {
            $stats = $byRoleLabel[$label] ?? ['sum' => 0, 'count' => 0];
            $roleAvgs[] = $stats['count'] > 0 ? round($stats['sum'] / $stats['count'], 1) : 0;
        }

        $domainActual = [];
        $domainRequired = [];
        foreach ($domains as $domain) {
            $domainDetails = $details->filter(
                fn ($d) => ($d->competency->competencyarea->description ?? 'Other') === $domain
            );
            $domainActual[] = $this->averageCodeFromDetails($domainDetails);

            $competencyIds = $domainDetails->pluck('competency_id')->unique();
            $requiredCodes = [];
            foreach ($competencyIds as $competencyId) {
                $reqRoles = SkillMatrixDetailRole::with('proficiency')
                    ->where('matrix_detail_id', $competencyId)
                    ->get();
                foreach ($reqRoles as $rr) {
                    if ($rr->proficiency) {
                        $requiredCodes[] = (int) $rr->proficiency->code;
                    }
                }
            }
            $domainRequired[] = $requiredCodes !== []
                ? round(array_sum($requiredCodes) / count($requiredCodes), 1)
                : 0;
        }

        $levelCounts = [1 => 0, 2 => 0, 3 => 0];
        foreach ($details as $detail) {
            $code = (int) ($detail->proficiency->code ?? 0);
            if ($code >= 1 && $code <= 3) {
                $levelCounts[$code]++;
            }
        }
        $totalLevels = array_sum($levelCounts) ?: 1;

        return [
            'byRole' => [
                'labels' => $roleLabels,
                'data' => $roleAvgs,
                'colors' => array_column($roleColumns, 'color'),
            ],
            'byDomain' => [
                'labels' => array_map(fn ($d) => $this->shortDomainLabel($d), $domains),
                'actual' => $domainActual,
                'required' => $domainRequired,
            ],
            'gapsByRole' => [
                'labels' => $roleLabels,
                'critical' => array_map(fn ($l) => $criticalByRole[$l] ?? 0, $roleLabels),
                'minor' => array_map(fn ($l) => $minorByRole[$l] ?? 0, $roleLabels),
                'exceed' => array_map(fn ($l) => $exceedByRole[$l] ?? 0, $roleLabels),
            ],
            'levelBreakdown' => [
                'counts' => [$levelCounts[3], $levelCounts[2], $levelCounts[1]],
                'percents' => [
                    round(($levelCounts[3] / $totalLevels) * 100),
                    round(($levelCounts[2] / $totalLevels) * 100),
                    round(($levelCounts[1] / $totalLevels) * 100),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{role_id: string, label: string, color: string}>  $roleColumns
     * @param  array<int, string>  $domains
     * @return array{role_labels: array<int, string>, rows: array<int, array{domain: string, cells: array<int, array{value: float|null, class: string}>, team_avg: array{value: float, class: string}}>}
     */
    protected function buildDomainHeatmap(
        CapabilityMatrix $capability,
        Collection $roles,
        array $roleColumns,
        Collection $details,
        array $domains,
    ): array {
        $rows = [];
        foreach ($domains as $domain) {
            $domainDetails = $details->filter(
                fn ($d) => ($d->competency->competencyarea->description ?? 'Other') === $domain
            );
            $cells = [];
            foreach ($roleColumns as $col) {
                $roleUsers = $roles->where('role_id', $col['role_id'])->pluck('user_id');
                $subset = $domainDetails->whereIn('user_id', $roleUsers);
                $avg = $this->averageCodeFromDetails($subset);
                $cells[] = [
                    'value' => $avg,
                    'class' => $this->levelClassForAverage($avg),
                ];
            }
            $teamAvg = $this->averageCodeFromDetails($domainDetails);
            $rows[] = [
                'domain' => $domain,
                'cells' => $cells,
                'team_avg' => [
                    'value' => $teamAvg,
                    'class' => $this->levelClassForAverage($teamAvg),
                ],
            ];
        }

        return [
            'role_labels' => array_column($roleColumns, 'label'),
            'rows' => $rows,
        ];
    }

    protected function averageProficiencyCode(?string $capabilityId): float
    {
        $details = CapabilityMatrixDetail::with('proficiency')
            ->when($capabilityId, fn ($q) => $q->where('capability_id', $capabilityId))
            ->get();

        if ($details->isEmpty()) {
            return 0.0;
        }

        $sum = $details->sum(fn ($d) => (int) ($d->proficiency->code ?? 0));

        return round($sum / $details->count(), 1);
    }

    /**
     * @param  Collection<int, CapabilityMatrixDetail>  $details
     */
    protected function averageCodeFromDetails(Collection $details): float
    {
        if ($details->isEmpty()) {
            return 0.0;
        }

        $sum = $details->sum(fn ($d) => (int) ($d->proficiency->code ?? 0));

        return round($sum / $details->count(), 1);
    }

    protected function levelClassForAverage(float $avg): string
    {
        if ($avg >= 2.5) {
            return 'level-3';
        }
        if ($avg >= 2.0) {
            return 'level-2';
        }
        if ($avg <= 0) {
            return 'gap-neutral';
        }

        return 'level-1';
    }

    protected function shortDomainLabel(string $domain): string
    {
        $map = [
            'General Laboratory' => 'Gen Lab',
            'Basic Lab Skills' => 'Basic Skills',
            'IT & Software' => 'IT',
        ];

        return $map[$domain] ?? (strlen($domain) > 12 ? substr($domain, 0, 11).'…' : $domain);
    }
}
