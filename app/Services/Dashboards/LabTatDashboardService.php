<?php

namespace App\Services\Dashboards;

use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LabTatDashboardService
{
    use DashboardHelpers;

    public function getLabTatBoard(string $period = 'active'): array
    {
        try {
            $connection = DB::connection(config('imara_ai.source_connection', config('database.default')));

            // --- Original Batch-Level Data ---
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
                    'source_id' => (int) $row->source_id,
                    'batch_code' => $row->batch_code,
                    'workflow_stage' => $row->workflow_stage,
                    'target_date' => $row->target_date,
                    'days_overdue' => (int) $row->days_overdue,
                    'is_qc_batch' => (bool) $row->is_qc_batch,
                ];
            })->values();

            $sampleTypeDistribution = $connection->table('sample_headers as sh')
                ->join('sample_types as st', 'st.id', '=', 'sh.sample_type_id')
                ->where('sh.isactive', 1)
                ->whereNotIn('sh.status', ['Completed', 'Finished Sample'])
                ->select('st.name as sample_type', DB::raw('COUNT(sh.id) as total_batches'))
                ->groupBy('st.name')
                ->orderByDesc('total_batches')
                ->get();

            // --- Test-Level (Parameter) Analytics with Filtering ---
            $activeSampleIds = $connection->table('sample_headers')
                ->where('isactive', 1)
                ->whereNotIn('status', ['Completed', 'Finished Sample'])
                ->pluck('id');

            $totalTestsRequested = $connection->table('sample_details')->whereIn('sample_header_id', $activeSampleIds)->count();
            $totalTestsCompleted = $connection->table('results')->whereIn('sample_header_id', $activeSampleIds)->count();

            // Throughput Volume (Filtered by historical period)
            $throughputQuery = $connection->table('results')
                ->join('analytes', 'analytes.id', '=', 'results.analyte_id');

            if ($period === 'active') {
                $throughputQuery->whereIn('results.sample_header_id', $activeSampleIds);
                $periodLabel = __('mas/lab.period_active_workload');
            } else {
                $dateLimit = match($period) {
                    'week' => now()->subDays(7),
                    'month' => now()->subMonth(),
                    'year' => now()->subYear(),
                    default => null,
                };
                
                if ($dateLimit) {
                    $throughputQuery->where('results.created_at', '>=', $dateLimit);
                    $periodLabel = __('mas/lab.last_period', ['period' => ucfirst($period)]);
                } else {
                    $periodLabel = __('mas/lab.lifetime_total');
                }
            }

            $throughputByAnalyte = $throughputQuery
                ->select('analytes.name as analyte_name', DB::raw('COUNT(results.id) as total_tests'))
                ->groupBy('analytes.name')
                ->orderByDesc('total_tests')
                ->limit(8)
                ->get();

            $masterStages = [
                'Sample Registration',
                'Sample Logged',
                'Samples In Lab',
                'Sample Verification',
                'Sample Approval',
                'Completed'
            ];

            $stageDataMap = $normalizedStageSummary->keyBy('workflow_stage');
            
            $finalStageSummary = collect($masterStages)->map(function($stageName) use ($stageDataMap) {
                if ($stageDataMap->has($stageName)) {
                    return $stageDataMap->get($stageName);
                }
                return [
                    'workflow_stage' => $stageName,
                    'total_batches' => 0,
                    'overdue_batches' => 0,
                    'due_today_batches' => 0,
                    'avg_days_to_target' => null,
                    'avg_days_overdue' => null,
                    'avg_completion_days' => null,
                    'refreshed_at' => now()->toDateTimeString(),
                ];
            });

            return [
                'available' => true,
                'message' => null,
                'period' => $period,
                'period_label' => $periodLabel,
                'summary' => array_merge($summary, [
                    'tests_requested' => $totalTestsRequested,
                    'tests_completed' => $totalTestsCompleted,
                    'tests_pending' => max(0, $totalTestsRequested - $totalTestsCompleted),
                ]),
                'stage_summary' => $finalStageSummary->all(),
                'stage_counts' => $finalStageSummary->mapWithKeys(fn($s) => [$this->translateStatus($s['workflow_stage']) => $s['total_batches']])->all(),
                'aging_buckets' => $orderedBuckets->all(),
                'overdue_batches' => $normalizedOverdueBatches->all(),
                'sample_type_distribution' => $sampleTypeDistribution->all(),
                'charts' => [
                    'stage_labels' => $finalStageSummary->map(fn($s) => $this->translateStatus($s['workflow_stage']))->all(),
                    'stage_totals' => $finalStageSummary->pluck('total_batches')->all(),
                    'stage_overdue' => $finalStageSummary->pluck('overdue_batches')->all(),
                    'aging_labels' => $orderedBuckets->pluck('label')->all(),
                    'aging_counts' => $orderedBuckets->pluck('batch_count')->all(),
                    'type_labels' => $sampleTypeDistribution->pluck('sample_type')->all(),
                    'type_counts' => $sampleTypeDistribution->pluck('total_batches')->all(),
                    'completion_labels' => [__('mas/lab.completed'), __('mas/lab.pending')],
                    'completion_counts' => [(int) $totalTestsCompleted, max(0, $totalTestsRequested - $totalTestsCompleted)],
                    'throughput_labels' => $throughputByAnalyte->pluck('analyte_name')->all(),
                    'throughput_counts' => $throughputByAnalyte->pluck('total_tests')->all(),
                ],
                'refreshed_at' => $normalizedStageSummary->pluck('refreshed_at')->filter()->first(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to load lab TAT board: ' . $exception->getMessage());

            return $this->emptyLabTatBoard('Reporting mart unavailable. Lab TAT cockpit is showing fallback data only.');
        }
    }

    /**
     * Get Smart Action Grid tasks (Urgent, My Tasks, Approvals).
     */
    public function getSmartActionGridData(string $tab = 'my_tasks'): array
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

        return $query->limit(15)->get()->map(function($row) {
            return [
                'id' => $row->id,
                'batch_code' => $row->batch_code,
                'client' => $row->client_name ?? 'N/A',
                'type' => $row->sample_type_name ?? 'N/A',
                'status' => $this->translateStatus($row->status),
                'priority' => $row->priority,
                'overdue' => $row->priority === 'Urgent'
            ];
        })->all();
    }

    /**
     * Get detailed TAT analytics broken down by Lab Section.
     */
    public function getLabSectionTatStats(): array
    {
        try {
            $sections = DB::table('sample_analysis_stages as sas')
                ->where('active', 1)
                ->get(['id', 'name', 'code']);

            $leaderboard = DB::table('sample_interlab_log as sil')
                ->join('sample_analysis_stages as sas', 'sas.id', '=', 'sil.to_lab_section_id')
                ->select([
                    'sas.id',
                    'sas.name',
                    'sas.code',
                    DB::raw('COUNT(sil.id) as total_batches'),
                    DB::raw('SUM(CASE WHEN sil.date_received IS NULL AND sil.expected_date < NOW() THEN 1 ELSE 0 END) as overdue_count'),
                    DB::raw('AVG((COALESCE(sil.date_received, NOW())::date - sil.date_submitted::date)) as avg_tat')
                ])
                ->groupBy('sas.id', 'sas.name', 'sas.code')
                ->orderByDesc('total_batches')
                ->get();

            // Workload: Try to get latest log entry for active samples first
            $activeWorkload = DB::table('sample_interlab_log as sil')
                ->join('sample_headers as sh', 'sh.id', '=', 'sil.sample_id')
                ->where('sh.isactive', 1)
                ->whereNotIn('sh.status', ['Completed', 'Finished Sample'])
                ->select('sil.to_lab_section_id', DB::raw('COUNT(sil.id) as count'))
                ->whereIn('sil.id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                          ->from('sample_interlab_log')
                          ->groupBy('sample_id');
                })
                ->groupBy('sil.to_lab_section_id')
                ->get()
                ->keyBy('to_lab_section_id');

            // Fallback for workload if interlab log is empty: use sample_headers.lab_section_ids
            $workloadData = [];
            foreach ($sections as $sec) {
                $workloadData[$sec->id] = (int) ($activeWorkload[$sec->id]->count ?? 0);
            }

            if (array_sum($workloadData) === 0) {
                $samplesInLab = DB::table('sample_headers')
                    ->where('isactive', 1)
                    ->whereIn('status', ['Samples In Lab', 'Sample Verification', 'Sample Approval'])
                    ->whereNotNull('lab_section_ids')
                    ->get(['lab_section_ids']);

                foreach ($samplesInLab as $sample) {
                    $sids = explode(',', $sample->lab_section_ids);
                    foreach ($sids as $sid) {
                        $sid = trim($sid);
                        if (isset($workloadData[$sid])) {
                            $workloadData[$sid]++;
                        }
                    }
                }
            }

            // Fallback for leaderboard if interlab log is empty
            $finalLeaderboard = $leaderboard->map(function($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'code' => $row->code,
                    'total' => (int) $row->total_batches,
                    'overdue' => (int) $row->overdue_count,
                    'avg_tat' => round((float) $row->avg_tat, 1)
                ];
            });

            if ($finalLeaderboard->isEmpty()) {
                $sectionVolumes = DB::table('sample_headers')
                    ->where('isactive', 1)
                    ->whereNotNull('lab_section_ids')
                    ->get(['lab_section_ids']);

                $volumes = [];
                foreach ($sectionVolumes as $sample) {
                    $sids = explode(',', $sample->lab_section_ids);
                    foreach ($sids as $sid) {
                        $sid = trim($sid);
                        $volumes[$sid] = ($volumes[$sid] ?? 0) + 1;
                    }
                }

                $finalLeaderboard = $sections->map(function($sec) use ($volumes) {
                    $total = $volumes[$sec->id] ?? 0;
                    if ($total === 0) return null;

                    return [
                        'id' => $sec->id,
                        'name' => $sec->name,
                        'code' => $sec->code,
                        'total' => $total,
                        'overdue' => 0,
                        'avg_tat' => 0
                    ];
                })->filter()->values();
            }

            // Trends: Last 6 months avg TAT per section
            $months = collect();
            for ($i = 5; $i >= 0; $i--) {
                $months->push(now()->subMonths($i)->format('M Y'));
            }

            $trends = [];
            foreach ($sections as $section) {
                $sectionTrends = [];
                $hasAnyData = false;
                for ($i = 5; $i >= 0; $i--) {
                    $start = now()->subMonths($i)->startOfMonth();
                    $end = now()->subMonths($i)->endOfMonth();

                    $avg = DB::table('sample_interlab_log')
                        ->where('to_lab_section_id', $section->id)
                        ->whereBetween('date_submitted', [$start, $end])
                        ->avg(DB::raw('(COALESCE(date_received, NOW())::date - date_submitted::date)'));
                    
                    $val = round((float) $avg, 1);
                    if ($val > 0) $hasAnyData = true;
                    $sectionTrends[] = $val;
                }
                
                if ($hasAnyData) {
                    $trends[] = [
                        'name' => $section->name,
                        'data' => $sectionTrends
                    ];
                }
            }

            return [
                'leaderboard' => $finalLeaderboard->all(),
                'workload' => $sections->map(function($sec) use ($workloadData) {
                    return [
                        'name' => $sec->name,
                        'value' => $workloadData[$sec->id]
                    ];
                })->all(),
                'trends' => [
                    'labels' => $months->all(),
                    'series' => $trends
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Failed to fetch Lab Section TAT stats: " . $e->getMessage());
            return [
                'leaderboard' => [],
                'workload' => [],
                'trends' => ['labels' => [], 'series' => []]
            ];
        }
    }

    /**
     * Get Analyst performance stats based on captured TAT data.
     */
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

    /**
     * Get granular TAT logs for each analyte completed.
     */
    public function getDetailedAnalyteTatLogs(string $period = 'active', int $limit = 100): array
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

            return $query->orderByDesc('finished_date')
                ->limit($limit)
                ->get()
                ->map(function($row) {
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
                        'remark' => $row->tat_remark
                    ];
                })->all();
        } catch (\Exception $e) {
            Log::error("Failed to fetch detailed TAT logs: " . $e->getMessage());
            return [];
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
            'on_time' => __('mas/lab.bucket_on_time'),
            'due_today' => __('mas/lab.bucket_due_today'),
            '1_3_overdue' => __('mas/lab.bucket_1_3_overdue'),
            '4_7_overdue' => __('mas/lab.bucket_4_7_overdue'),
            '8_plus_overdue' => __('mas/lab.bucket_8_plus_overdue'),
            'no_target' => __('mas/lab.bucket_no_target'),
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
                'tests_requested' => 0,
                'tests_completed' => 0,
                'tests_pending' => 0,
            ],
            'stage_summary' => [],
            'stage_counts' => [],
            'sample_type_distribution' => [],
            'aging_buckets' => collect($this->agingBucketLabels())->map(function ($label, $bucketKey) {
                return [
                    'aging_bucket' => $bucketKey,
                    'label' => $label,
                    'batch_count' => 0,
                ];
            })->values()->all(),
            'overdue_batches' => [],
            'charts' => [
                'stage_labels' => [],
                'stage_totals' => [],
                'stage_overdue' => [],
                'aging_labels' => array_values($this->agingBucketLabels()),
                'aging_counts' => array_fill(0, count($this->agingBucketLabels()), 0),
                'type_labels' => [],
                'type_counts' => [],
                'completion_labels' => ['Completed', 'Pending'],
                'completion_counts' => [0, 0],
                'throughput_labels' => [],
                'throughput_counts' => [],
            ],
            'refreshed_at' => null,
        ];
    }
}
