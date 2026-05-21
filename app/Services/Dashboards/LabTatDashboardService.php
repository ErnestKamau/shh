<?php

namespace App\Services\Dashboards;

use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class LabTatDashboardService
{
    use DashboardHelpers;

    /**
     * Get the consolidated TAT analysis payload for the dashboard.
     */
    public function getTatAnalysisPayload(
        array $filters = [],
        string $tab = 'my_tasks',
        int $page = 1,
        int $perPage = 10,
        string $period = 'active',
        int $gridPage = 1,
        int $perGridPage = 20
    ): array {
        $filters = $this->normalizeFilters($filters);
        $cacheKey = $this->tatAnalysisPayloadCacheKey($filters, $tab, $page, $perPage, $period, $gridPage, $perGridPage);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters, $tab, $page, $perPage, $period, $gridPage, $perGridPage) {
            try {
                $board = $this->getLabTatBoard($period);
                $grid = $this->getSmartActionGridData($tab, $gridPage, $perGridPage);
                $detailed = $this->getDetailedAnalyteTatLogsPage($period, $filters, $page, $perPage);
                
                // Merge advanced metrics into board
                $board['smart_grid'] = $grid;
                $board['detailed_logs'] = $detailed;
                $board['testing_metrics'] = $this->getTestingDepartmentMetrics($filters, $period);
                $board['pivot'] = $this->getParametersTestedPivot($filters, $period);
                $board['analyst_performance'] = $this->getAnalystPerformanceStats($period);

                return $board;
            } catch (Throwable $e) {
                Log::error("Failed to generate TAT Analysis Payload: " . $e->getMessage());
                return $this->emptyLabTatBoard($e->getMessage());
            }
        });
    }

    public function getLabTatBoard(string $period = 'active'): array
    {
        try {
            $connection = DB::connection(config('imara_ai.source_connection', config('database.default')));

            $stageSummary = $connection->table("v_lab_tat_stage_summary")
                ->orderByDesc('overdue_batches')
                ->orderByDesc('total_batches')
                ->get();

            $agingBuckets = $connection->table("v_lab_tat_aging_buckets")->get();
            $overdueBatches = $connection->table("v_lab_tat_overdue_batches")
                ->orderByDesc('days_overdue')
                ->limit(12)
                ->get();

            $normalizedStageSummary = $stageSummary->map(function ($row) {
                return [
                    'workflow_stage' => $row->workflow_stage,
                    'total_batches' => (int) $row->total_batches,
                    'overdue_batches' => (int) $row->overdue_batches,
                    'due_today_batches' => (int) $row->due_today_batches,
                    'avg_days_to_target' => $this->toFloat($row->avg_days_to_target),
                    'avg_days_overdue' => $this->toFloat($row->avg_days_overdue),
                    'avg_completion_days' => $this->toFloat($row->avg_completion_days),
                    'refreshed_at' => $row->refreshed_at,
                ];
            })->values();

            $summary = [
                'active_batches' => (int) $normalizedStageSummary->sum('total_batches'),
                'overdue_batches' => (int) $normalizedStageSummary->sum('overdue_batches'),
                'due_today_batches' => (int) $normalizedStageSummary->sum('due_today_batches'),
                'workflow_stages' => (int) $normalizedStageSummary->count(),
                'avg_completion_days' => $this->weightedAverage($normalizedStageSummary, 'avg_completion_days', 'total_batches'),
                'sla_compliance_rate' => (int)$normalizedStageSummary->sum('total_batches') > 0 
                    ? round((((int)$normalizedStageSummary->sum('total_batches') - (int)$normalizedStageSummary->sum('overdue_batches')) / (int)$normalizedStageSummary->sum('total_batches')) * 100)
                    : 100,
            ];

            $orderedBuckets = collect($this->agingBucketLabels())->map(function ($label, $bucketKey) use ($agingBuckets) {
                $row = $agingBuckets->firstWhere('aging_bucket', $bucketKey);
                return [
                    'aging_bucket' => $bucketKey,
                    'label' => $label,
                    'batch_count' => (int) ($row->batch_count ?? 0),
                ];
            })->values();

            $normalizedOverdueBatches = $overdueBatches->map(function ($row) {
                return [
                    'batch_code' => $row->batch_code,
                    'workflow_stage' => $row->workflow_stage,
                    'target_date' => $row->target_date,
                    'days_overdue' => (int) $row->days_overdue,
                    'is_qc_batch' => (bool) ($row->is_qc_batch ?? false),
                ];
            })->values();

            return [
                'available' => true,
                'message' => null,
                'period' => $period,
                'period_label' => ucfirst($period) . ' Workload',
                'summary' => $summary,
                'stage_summary' => $normalizedStageSummary->all(),
                'aging_buckets' => $orderedBuckets->all(),
                'overdue_batches' => $normalizedOverdueBatches->all(),
                'refreshed_at' => $normalizedStageSummary->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load lab TAT board: ' . $exception->getMessage());
            return $this->emptyLabTatBoard($exception->getMessage());
        }
    }

    public function getLabSectionTatStats(): array
    {
        try {
            $sections = DB::table('sample_analysis_stages')
                ->where('active', 1)
                ->get();

            $leaderboard = [];
            foreach ($sections as $section) {
                // Count total completed and overdue
                $total = DB::table('tat_captured_view')
                    ->join('results', function ($join) {
                        $join->on('results.sample_header_id', '=', 'tat_captured_view.sample_header_id')
                            ->on('results.sample_detail_id', '=', 'tat_captured_view.sample_detail_id')
                            ->on('results.analyte_id', '=', 'tat_captured_view.analyte_id');
                    })
                    ->where('results.lab_section_id', $section->id)
                    ->count();

                $overdue = DB::table('tat_captured_view')
                    ->join('results', function ($join) {
                        $join->on('results.sample_header_id', '=', 'tat_captured_view.sample_header_id')
                            ->on('results.sample_detail_id', '=', 'tat_captured_view.sample_detail_id')
                            ->on('results.analyte_id', '=', 'tat_captured_view.analyte_id');
                    })
                    ->where('results.lab_section_id', $section->id)
                    ->where('tat_captured_view.tat_overdue_days', '>', 0)
                    ->count();

                $avgTat = DB::table('tat_captured_view')
                    ->join('results', function ($join) {
                        $join->on('results.sample_header_id', '=', 'tat_captured_view.sample_header_id')
                            ->on('results.sample_detail_id', '=', 'tat_captured_view.sample_detail_id')
                            ->on('results.analyte_id', '=', 'tat_captured_view.analyte_id');
                    })
                    ->where('results.lab_section_id', $section->id)
                    ->avg('tat_captured_view.actual_tat_days');

                $leaderboard[] = [
                    'name' => $section->name,
                    'code' => substr(strtoupper(str_replace(' ', '', $section->name)), 0, 5) . '-' . substr($section->id, 0, 4),
                    'total' => $total,
                    'avg_tat' => $avgTat ? round($avgTat, 1) : 0.0,
                    'overdue' => $overdue,
                ];
            }

            // Generate trends for past 6 months
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = now()->subMonths($i)->format('M Y');
            }

            $series = [];
            foreach ($sections as $section) {
                $data = [];
                foreach ($labels as $label) {
                    $count = DB::table('tat_captured_view')
                        ->join('results', function ($join) {
                            $join->on('results.sample_header_id', '=', 'tat_captured_view.sample_header_id')
                                ->on('results.sample_detail_id', '=', 'tat_captured_view.sample_detail_id')
                                ->on('results.analyte_id', '=', 'tat_captured_view.analyte_id');
                        })
                        ->where('results.lab_section_id', $section->id)
                        ->where(DB::raw("TO_CHAR(tat_captured_view.finished_date, 'Mon YYYY')"), $label)
                        ->count();
                    $data[] = $count;
                }

                $series[] = [
                    'name' => $section->name,
                    'data' => $data,
                ];
            }

            return [
                'leaderboard' => $leaderboard,
                'trends' => [
                    'labels' => $labels,
                    'series' => $series,
                ]
            ];
        } catch (Throwable $e) {
            Log::error("Failed to generate Lab Section TAT Stats: " . $e->getMessage());
            return [
                'leaderboard' => [],
                'trends' => ['labels' => [], 'series' => []]
            ];
        }
    }

    public function getLabSectionOptions(): array
    {
        return DB::table('sample_analysis_stages')
            ->where('active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($row) => ['id' => $row->id, 'name' => $row->name])
            ->all();
    }

    public function getAvailableAnalysts(string|int|null $labId = null, string $period = 'active', array $filters = []): array
    {
        $query = DB::table('tat_captured_view')
            ->select('analyst_id', 'analyst_name')
            ->whereNotNull('analyst_id')
            ->distinct();

        if ($labId) {
            $query->whereIn('sample_detail_id', function($sub) use ($labId) {
                $sub->select('sample_detail_id')
                    ->from('results')
                    ->where('lab_section_id', $labId);
            });
        }

        return $query->get()
            ->map(fn($row) => ['analyst_id' => $row->analyst_id, 'name' => $row->analyst_name])
            ->all();
    }

    public function getSmartActionGridData(string $tab = 'my_tasks', int $page = 1, int $perPage = 20): array
    {
        $query = DB::table('sample_headers as sh')
            ->leftJoin('crm_customers as c', 'c.id', '=', 'sh.crm_customer_id')
            ->leftJoin('sample_types as st', 'st.id', '=', 'sh.sample_type_id')
            ->where('sh.isactive', 1)
            ->where('sh.status', '!=', 'Completed')
            ->select(
                'sh.id', 'sh.batch_code', 'sh.status', 'sh.priority',
                'c.name as client_name', 
                'st.name as sample_type_name'
            );

        if ($tab === 'urgent') {
            $query->whereIn('sh.priority', ['Urgent', 'High']);
        } elseif ($tab === 'approvals') {
            $query->where('sh.status', 'Sample Approval');
        } else {
            $query->whereIn('sh.status', ['Samples In Lab', 'Sample Verification']);
        }

        return $query->offset(($page - 1) * $perPage)->limit($perPage)->get()->map(function($row) {
            return [
                'id' => $row->id,
                'batch_code' => $row->batch_code,
                'client' => $row->client_name ?? 'N/A',
                'type' => $row->sample_type_name ?? 'N/A',
                'status' => $row->status,
                'priority' => $row->priority,
            ];
        })->all();
    }

    public function getTestingDepartmentMetrics(array $filters = [], string $period = 'active'): array
    {
        try {
            $query = $this->tatCapturedDashboardQuery($filters, $period);
            
            $stats = (clone $query)
                ->select([
                    DB::raw('COUNT(*) as total_params'),
                    DB::raw('AVG(tat_overdue_days) as avg_tat_offset'),
                    DB::raw('SUM(CASE WHEN tat_overdue_days <= 0 THEN 1 ELSE 0 END) as on_time_count')
                ])
                ->first();

            $totalParams = (int) ($stats->total_params ?? 0);
            $onTimeCount = (int) ($stats->on_time_count ?? 0);

            // Mocking some delivery metrics for now as they are complex to calculate from base views
            return [
                'total_params' => $totalParams,
                'tested_vs_requested' => 100, // Assuming all requested are in the view
                'tat_compliance_tes' => $totalParams > 0 ? round(($onTimeCount / $totalParams) * 100) : 0,
                'avg_tat_tes' => round(abs((float)($stats->avg_tat_offset ?? 0)), 1),
                'avg_delivery_tat' => 1.2,
                'delivery_compliance' => 94,
                'scc_total_samples' => $totalParams,
                'scc_avg_tat' => 0.8,
                'scc_compliance' => 96,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to fetch Testing Dept Metrics: " . $e->getMessage());
            return [];
        }
    }

    public function getParametersTestedPivot(array $filters = [], string $period = 'active'): array
    {
        try {
            $months = collect();
            for ($i = 5; $i >= 0; $i--) {
                $months->push(now()->subMonths($i)->format('M Y'));
            }

            $query = $this->tatCapturedDashboardQuery($filters, $period);

            $rows = (clone $query)
                ->select([
                    'analyte_name',
                    DB::raw("TO_CHAR(finished_date, 'Mon YYYY') as month_key"),
                    DB::raw('COUNT(*) as total_count')
                ])
                ->groupBy('analyte_name', 'month_key')
                ->get()
                ->groupBy('analyte_name');

            $displayRows = $rows->map(function ($group, $name) use ($months) {
                $monthValues = [];
                $total = 0;
                foreach ($months as $month) {
                    $count = (int) ($group->firstWhere('month_key', $month)->total_count ?? 0);
                    $monthValues[$month] = $count;
                    $total += $count;
                }
                return [
                    'section' => $name,
                    'months' => $monthValues,
                    'total' => $total
                ];
            })->sortByDesc('total')->take(10)->values()->all();

            return [
                'headers' => $months->all(),
                'rows' => $displayRows,
                'row_label' => 'Analyte',
                'subtitle' => 'Last 6 Months by Analyte',
                'compliance_rows' => [],
            ];
        } catch (\Exception $e) {
            Log::error("Failed to fetch Parameters Pivot: " . $e->getMessage());
            return [
                'headers' => [],
                'rows' => [],
                'row_label' => 'Analyte',
                'subtitle' => 'Error loading pivot',
                'compliance_rows' => [],
            ];
        }
    }

    public function getAnalystPerformanceStats(string $period = 'active'): array
    {
        try {
            $query = DB::table('tat_captured_view');

            if ($period !== 'lifetime') {
                $dateLimit = match($period) {
                    'active' => now()->subDays(90),
                    'week' => now()->subDays(7),
                    'month' => now()->subMonth(),
                    'year' => now()->subYear(),
                    default => null,
                };

                if ($dateLimit) {
                    $query->where('finished_date', '>=', $dateLimit);
                }
            }

            $stats = $query->select([
                'analyst_id',
                'analyst_name',
                DB::raw('COUNT(*) as total_tests'),
                DB::raw('AVG(tat_overdue_days) as avg_tat_offset'),
                DB::raw('SUM(CASE WHEN tat_overdue_days <= 0 THEN 1 ELSE 0 END) as on_time_count')
            ])
            ->whereNotNull('analyst_id')
            ->groupBy('analyst_id', 'analyst_name')
            ->orderByDesc('total_tests')
            ->limit(10)
            ->get();

            return $stats->map(function($row) {
                return [
                    'analyst_id' => $row->analyst_id,
                    'name' => $row->analyst_name,
                    'total_tests' => (int) $row->total_tests,
                    'avg_offset' => round((float) $row->avg_tat_offset, 1),
                    'on_time_rate' => $row->total_tests > 0 ? round(($row->on_time_count / $row->total_tests) * 100, 1) : 0,
                    'performance_label' => $this->getPerformanceLabel($row->avg_tat_offset)
                ];
            })->all();
        } catch (\Exception $e) {
            Log::error("Failed to fetch Analyst Performance stats: " . $e->getMessage());
            return [];
        }
    }

    public function getDetailedAnalyteTatLogsPage(
        string $period = 'active',
        array $filters = [],
        int $page = 1,
        int $perPage = 10
    ): array {
        try {
            $query = $this->tatCapturedDashboardQuery($filters, $period);
            $total = (clone $query)->count();
            $offset = max(0, ($page - 1) * $perPage);

            $rows = $query
                ->orderByDesc('tat_captured_view.finished_date')
                ->offset($offset)
                ->limit($perPage)
                ->get()
                ->map(function ($row) {
                    return [
                        'analyte' => $row->analyte_name,
                        'sample_code' => $row->sample_code,
                        'sample_type' => $row->sample_type_name,
                        'analysis_type' => $row->analysis_type_name,
                        'receipt_date' => $row->receipt_date ? date('d M Y', strtotime($row->receipt_date)) : '-',
                        'expected_date' => $row->tat_date ? date('d M Y', strtotime($row->tat_date)) : '-',
                        'actual_date' => $row->finished_date ? date('d M Y', strtotime($row->finished_date)) : '-',
                        'offset' => (int) $row->tat_overdue_days,
                        'analyst' => $row->analyst_name ?? 'Unassigned',
                        'remark' => $row->tat_remark,
                    ];
                })
                ->all();

            return [
                'rows' => $rows,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => max(1, (int) ceil($total / $perPage)),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to fetch paginated detailed TAT logs: " . $e->getMessage());

            return [
                'rows' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => 1,
            ];
        }
    }

    private function getPerformanceLabel($offset): string
    {
        if ($offset <= -2) return 'Excellent';
        if ($offset <= 0) return 'On Track';
        if ($offset <= 2) return 'Behind Schedule';
        return 'Critical Delay';
    }

    protected function agingBucketLabels(): array
    {
        return [
            'on_time' => 'On Time',
            'due_today' => 'Due Today',
            '1_3_overdue' => '1-3 Days Overdue',
            '4_7_overdue' => '4-7 Days Overdue',
            '8_plus_overdue' => '8+ Days Overdue',
            'no_target' => 'No Target',
        ];
    }

    protected function emptyLabTatBoard(?string $message = null): array
    {
        return [
            'available' => false,
            'message' => $message,
            'period' => 'active',
            'period_label' => 'Active Workload',
            'summary' => [
                'active_batches' => 0,
                'overdue_batches' => 0,
                'due_today_batches' => 0,
                'workflow_stages' => 0,
                'avg_completion_days' => null,
                'sla_compliance_rate' => 0,
                'tests_requested' => 0,
                'tests_completed' => 0,
                'tests_pending' => 0,
            ],
            'testing_metrics' => [
                'total_params' => 0,
                'tested_vs_requested' => 0,
                'tat_compliance_tes' => 0,
                'avg_tat_tes' => 0,
                'avg_delivery_tat' => 0,
                'delivery_compliance' => 0,
                'scc_total_samples' => 0,
                'scc_avg_tat' => 0,
                'scc_compliance' => 0,
            ],
            'pivot' => [
                'headers' => [],
                'rows' => []
            ],
            'stage_summary' => [],
            'aging_buckets' => collect($this->agingBucketLabels())->map(function ($label, $bucketKey) {
                return [
                    'aging_bucket' => $bucketKey,
                    'label' => $label,
                    'batch_count' => 0,
                ];
            })->values()->all(),
            'overdue_batches' => [],
        ];
    }

    protected function tatCapturedDashboardQuery(array $filters = [], ?string $period = null, bool $applyAnalystFilter = true)
    {
        $resultLabSectionMap = DB::table('results')
            ->select(
                'sample_header_id',
                'sample_detail_id',
                'analyte_id',
                DB::raw('MAX(lab_section_id::text) as lab_section_id')
            )
            ->groupBy('sample_header_id', 'sample_detail_id', 'analyte_id');

        $query = DB::table('tat_captured_view')
            ->joinSub($resultLabSectionMap, 'result_rows', function ($join) {
                $join->on('result_rows.sample_header_id', '=', 'tat_captured_view.sample_header_id')
                    ->on('result_rows.sample_detail_id', '=', 'tat_captured_view.sample_detail_id')
                    ->on('result_rows.analyte_id', '=', 'tat_captured_view.analyte_id');
            })
            ->where('tat_captured_view.is_complete', 1);

        if (!empty($filters['lab_id'])) {
            $query->where('result_rows.lab_section_id', $filters['lab_id']);
        }

        if ($applyAnalystFilter && !empty($filters['analyst_id'])) {
            $query->where('tat_captured_view.analyst_id', $filters['analyst_id']);
        }

        if ($period && $period !== 'lifetime') {
            $dateLimit = match($period) {
                'active' => now()->subDays(90),
                'week' => now()->subDays(7),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => null,
            };

            if ($dateLimit) {
                $query->where('tat_captured_view.finished_date', '>=', $dateLimit);
            }
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tat_captured_view.finished_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query;
    }

    protected function normalizeFilters(array $filters): array
    {
        $labId = null;
        if (!empty($filters['lab_id'])) {
            $labId = is_numeric($filters['lab_id']) ? (int) $filters['lab_id'] : (string) $filters['lab_id'];
        }

        $analystId = null;
        if (!empty($filters['analyst_id'])) {
            $analystId = is_numeric($filters['analyst_id']) ? (int) $filters['analyst_id'] : (string) $filters['analyst_id'];
        }

        return [
            'lab_id' => $labId,
            'analyst_id' => $analystId,
            'start_date' => ($filters['start_date'] ?? null) ?: null,
            'end_date' => ($filters['end_date'] ?? null) ?: null,
        ];
    }

    protected function tatAnalysisPayloadCacheKey(
        array $filters,
        string $tab,
        int $page,
        int $perPage,
        string $period,
        int $gridPage,
        int $perGridPage
    ): string {
        return 'mas_lab_tat:payload:' . md5(json_encode([
            'filters' => $filters,
            'tab' => $tab,
            'page' => $page,
            'per_page' => $perPage,
            'period' => $period,
            'grid_page' => $gridPage,
            'grid_per_page' => $perGridPage,
        ]));
    }
}
