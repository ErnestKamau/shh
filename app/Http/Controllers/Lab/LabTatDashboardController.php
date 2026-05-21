<?php

namespace App\Http\Controllers\Lab;

use App\SampleHeader;
use App\SampleDetails;
use App\Result;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LabTatDashboardController extends Controller
{
    protected $pgsqlConnection = 'pgsql';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Lab TAT cockpit dashboard
     */
    public function index()
    {
        $data = [
            'agingBuckets' => $this->getAgingBuckets(),
            'tatBreaches' => $this->getTatBreaches(),
            'stageCycleTime' => $this->getStageCycleTime(),
            'stuckBatches' => $this->getStuckBatches(),
            'tatMetrics' => $this->getTatMetrics(),
        ];

        return view('lab.dashboards.tat-cockpit', $data);
    }

    /**
     * Get aging buckets by workflow stage
     */
    private function getAgingBuckets()
    {
        try {
            $stages = ['Samples Reception', 'Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports for Collection'];
            $buckets = [];

            foreach ($stages as $stage) {
                $agingData = SampleHeader::where('status', $stage)
                    ->where('isactive', 1)
                    ->selectRaw("
                        COUNT(id) as total,
                        COUNT(CASE WHEN (CURRENT_DATE - created_at::date) <= 3 THEN 1 END) as bucket_0_3d,
                        COUNT(CASE WHEN (CURRENT_DATE - created_at::date) > 3 AND (CURRENT_DATE - created_at::date) <= 7 THEN 1 END) as bucket_4_7d,
                        COUNT(CASE WHEN (CURRENT_DATE - created_at::date) > 7 AND (CURRENT_DATE - created_at::date) <= 14 THEN 1 END) as bucket_8_14d,
                        COUNT(CASE WHEN (CURRENT_DATE - created_at::date) > 14 THEN 1 END) as bucket_15plus_d
                    ")
                    ->first();

                $buckets[$stage] = [
                    'total' => (int)$agingData->total,
                    '0-3d' => (int)$agingData->bucket_0_3d,
                    '4-7d' => (int)$agingData->bucket_4_7d,
                    '8-14d' => (int)$agingData->bucket_8_14d,
                    '15+d' => (int)$agingData->bucket_15plus_d,
                ];
            }

            return $buckets;
        } catch (\Exception $e) {
            \Log::warning('Failed to load aging buckets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get TAT breach heatmap data (analyst, analyte, sample type)
     */
    private function getTatBreaches()
    {
        try {
            $breaches = DB::connection($this->pgsqlConnection)
                ->table('public.v_lab_tat_stage_summary')
                ->where('days_until_due', '<', 0)
                ->selectRaw('
                    analyst_name,
                    analyte_name,
                    sample_type_name,
                    COUNT(DISTINCT batch_id) as breach_count,
                    AVG(ABS(days_until_due)) as avg_days_overdue
                ')
                ->groupBy('analyst_name', 'analyte_name', 'sample_type_name')
                ->orderByDesc('breach_count')
                ->limit(50)
                ->get();

            return $breaches->map(function ($row) {
                return [
                    'analyst' => $row->analyst_name ?? 'Unassigned',
                    'analyte' => $row->analyte_name,
                    'sampleType' => $row->sample_type_name,
                    'breachCount' => (int)$row->breach_count,
                    'avgDaysOverdue' => round($row->avg_days_overdue, 2),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load TAT breaches: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get stage cycle time breakdown
     */
    private function getStageCycleTime()
    {
        try {
            $cycleTime = DB::connection($this->pgsqlConnection)
                ->table('public.v_lab_tat_stage_summary')
                ->whereNotNull('stage_name')
                ->selectRaw('
                    stage_name,
                    COUNT(DISTINCT batch_id) as sample_count,
                    ROUND(AVG(CAST(days_in_stage AS DECIMAL)), 2) as avg_cycle_time,
                    ROUND(MAX(CAST(days_in_stage AS DECIMAL)), 2) as max_cycle_time,
                    ROUND(MIN(CAST(days_in_stage AS DECIMAL)), 2) as min_cycle_time
                ')
                ->groupBy('stage_name')
                ->orderBy('avg_cycle_time', 'desc')
                ->get();

            return $cycleTime->map(function ($row) {
                return [
                    'stage' => $row->stage_name,
                    'sampleCount' => (int)$row->sample_count,
                    'avgCycleTime' => (float)$row->avg_cycle_time,
                    'maxCycleTime' => (float)$row->max_cycle_time,
                    'minCycleTime' => (float)$row->min_cycle_time,
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load cycle time: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get stuck batches (not moved for 7+ days in current stage)
     */
    private function getStuckBatches()
    {
        try {
            $stuckBatches = SampleHeader::where('isactive', 1)
                ->whereNotIn('status', ['Finished Sample', 'Completed'])
                ->selectRaw('
                    id,
                    batch_code,
                    status,
                    created_at,
                    (CURRENT_DATE - created_at::date) as days_stuck
                ')
                ->whereRaw('(CURRENT_DATE - created_at::date) >= 7')
                ->orderByDesc('days_stuck')
                ->limit(20)
                ->get();

            return $stuckBatches->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batchCode' => $batch->batch_code,
                    'status' => $batch->status,
                    'createdAt' => $batch->created_at->format('M d, Y'),
                    'daysStuck' => (int)$batch->days_stuck,
                    'priority' => $batch->days_stuck > 21 ? 'critical' : ($batch->days_stuck > 14 ? 'high' : 'medium'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load stuck batches: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get overall TAT metrics
     */
    private function getTatMetrics()
    {
        try {
            $metrics = DB::connection($this->pgsqlConnection)
                ->table('public.v_lab_tat_stage_summary')
                ->selectRaw('
                    COUNT(DISTINCT batch_id) as total_batches,
                    COUNT(DISTINCT CASE WHEN days_until_due < 0 THEN batch_id END) as breached_batches,
                    ROUND(AVG(CAST(days_until_due AS DECIMAL)), 2) as avg_days_until_due,
                    COUNT(DISTINCT analyst_name) as active_analysts
                ')
                ->first();

            $breachRate = $metrics->total_batches > 0 
                ? round(($metrics->breached_batches / $metrics->total_batches) * 100, 2)
                : 0;

            return [
                'totalBatches' => (int)$metrics->total_batches,
                'breachedBatches' => (int)$metrics->breached_batches,
                'breachRate' => (float)$breachRate,
                'avgDaysUntilDue' => (float)$metrics->avg_days_until_due,
                'activeAnalysts' => (int)$metrics->active_analysts,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to load TAT metrics: ' . $e->getMessage());
            return [
                'totalBatches' => 0,
                'breachedBatches' => 0,
                'breachRate' => 0,
                'avgDaysUntilDue' => 0,
                'activeAnalysts' => 0,
            ];
        }
    }

    /**
     * Export TAT data to CSV
     */
    public function exportTatReport(Request $request)
    {
        $fileName = 'lab-tat-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $breaches = $this->getTatBreaches();

        $callback = function () use ($breaches) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Analyst', 'Analyte', 'Sample Type', 'Breach Count', 'Avg Days Overdue']);

            foreach ($breaches as $breach) {
                fputcsv($file, [
                    $breach['analyst'],
                    $breach['analyte'],
                    $breach['sampleType'],
                    $breach['breachCount'],
                    $breach['avgDaysOverdue'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get heatmap data endpoint for AJAX
     */
    public function getHeatmapData()
    {
        $breaches = $this->getTatBreaches();
        
        $heatmapData = [];
        foreach ($breaches as $breach) {
            $heatmapData[] = [
                'x' => $breach['analyst'],
                'y' => $breach['analyte'],
                'value' => $breach['breachCount'],
                'intensity' => min($breach['breachCount'] / 10, 1), // 0-1 scale
            ];
        }

        return response()->json($heatmapData);
    }
}
