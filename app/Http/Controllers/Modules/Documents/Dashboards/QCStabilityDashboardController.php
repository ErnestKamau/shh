<?php

namespace App\Http\Controllers\Modules\Documents\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * QC Stability Board Dashboard Controller
 *
 * All data is read from the Postgres AI/Reporting database (pgsql_ai).
 * Operational tables are not queried here — all QC data arrives via the Python ETL pipeline.
 *
 * Postgres views used:
 *   reporting.v_qc_stability_metrics  — per-analyte robust stats + UCL/LCL
 *   reporting.v_qc_drift_trends       — Levey-Jennings daily series
 *   reporting.v_qc_pareto_analysis    — Pareto failure ranking
 *   reporting.v_qc_ooc_events         — OOC / Westgard event log
 */
class QCStabilityDashboardController extends Controller
{
    protected string $pg = 'pgsql';
    protected int    $cacheTtl = 600; // seconds

    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Main entry point
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 14, 30, 60, 90]) ? $days : 30;

        $cacheKey = "qc_stability_board_{$days}";

        $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($days) {
            return [
                'kpis'          => $this->getKpis(),
                'driftTrends'   => $this->getDriftTrends($days),
                'paretoData'    => $this->getParetoData(),
                'oocEvents'     => $this->getOocEvents($days),
                'analyteStats'  => $this->getAnalyteStats(),
            ];
        });

        $data['days'] = $days;

        return view('documents.dashboards.qc-stability', $data);
    }

    public function export(Request $request)
    {
        // CSV export of OOC events
        $days = (int) $request->get('days', 30);
        $rows = $this->getOocEvents($days);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="qc_stability_export.csv"',
        ];

        $cb = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Analyte', 'Result', 'UCL', 'LCL', 'Westgard Rule', 'Status', 'Date']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->analyte_name,
                    $r->result_value,
                    $r->ucl,
                    $r->lcl,
                    $r->westgard_rule,
                    $r->status_code,
                    $r->occurred_at,
                ]);
            }
            fclose($out);
        };

        return response()->stream($cb, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────
    // KPI cards
    // ─────────────────────────────────────────────────────────────────────

    private function getKpis(): array
    {
        try {
            $row = DB::connection($this->pg)
                ->table('public.v_qc_stability_metrics')
                ->selectRaw('
                    SUM(total_tests)   AS total_tests,
                    SUM(passed_tests)  AS passed_tests,
                    SUM(failed_tests)  AS failed_tests,
                    COUNT(*)           AS total_analytes
                ')
                ->first();

            $total    = (int) ($row->total_tests  ?? 0);
            $passed   = (int) ($row->passed_tests ?? 0);
            $failed   = (int) ($row->failed_tests ?? 0);
            $analytes = (int) ($row->total_analytes ?? 0);

            $passRate = $total > 0 ? round($passed / $total * 100, 2) : 0;
            $failRate = $total > 0 ? round($failed / $total * 100, 2) : 0;

            // Count analytes whose robust_cv_pct exceeds warning threshold (15 %)
            $drifting = DB::connection($this->pg)
                ->table('public.v_qc_stability_metrics')
                ->where('robust_cv_pct', '>', 15)
                ->count();

            return compact('total', 'passed', 'failed', 'passRate', 'failRate', 'analytes', 'drifting');
        } catch (\Throwable $e) {
            \Log::error('QCStabilityBoard@getKpis: ' . $e->getMessage());
            return ['total' => 0, 'passed' => 0, 'failed' => 0,
                    'passRate' => 0, 'failRate' => 0, 'analytes' => 0, 'drifting' => 0];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Levey-Jennings drift trends
    // ─────────────────────────────────────────────────────────────────────

    private function getDriftTrends(int $days): array
    {
        try {
            $since = now()->subDays($days)->toDateString();

            $rows = DB::connection($this->pg)
                ->table('public.v_qc_drift_trends')
                ->where('test_date', '>=', $since)
                ->orderBy('analyte_name')
                ->orderBy('test_date')
                ->limit(500)
                ->get();

            // Group by analyte so the chart renders one series per analyte
            $grouped = [];
            foreach ($rows as $r) {
                $grouped[$r->analyte_name][] = [
                    'date'          => $r->test_date,
                    'avgValue'      => (float) ($r->avg_value   ?? 0),
                    'ucl'           => (float) ($r->ucl          ?? 0),
                    'lcl'           => (float) ($r->lcl          ?? 0),
                    'centerLine'    => (float) ($r->center_line  ?? 0),
                    'testCount'     => (int)   ($r->test_count   ?? 0),
                    'controlStatus' => $r->control_status,
                ];
            }
            return $grouped;
        } catch (\Throwable $e) {
            \Log::error('QCStabilityBoard@getDriftTrends: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Pareto analysis
    // ─────────────────────────────────────────────────────────────────────

    private function getParetoData(): array
    {
        try {
            return DB::connection($this->pg)
                ->table('public.v_qc_pareto_analysis')
                ->orderByDesc('failure_count')
                ->limit(20)
                ->get()
                ->map(fn ($r) => [
                    'analyte'        => $r->analyte_name,
                    'analyteCode'    => $r->analyte_code,
                    'failureReason'  => $r->failure_reason,
                    'failureCount'   => (int)   $r->failure_count,
                    'pctOfTotal'     => (float) $r->pct_of_total,
                    'cumulativePct'  => (float) $r->cumulative_pct,
                    'category'       => $r->pareto_category, // vital_few | useful_many
                ])
                ->toArray();
        } catch (\Throwable $e) {
            \Log::error('QCStabilityBoard@getParetoData: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Out-of-control events log
    // ─────────────────────────────────────────────────────────────────────

    private function getOocEvents(int $days): mixed
    {
        try {
            $since = now()->subDays($days)->toDateString();

            return DB::connection($this->pg)
                ->table('public.v_qc_ooc_events')
                ->where('occurred_at', '>=', $since)
                ->orderByDesc('occurred_at')
                ->limit(100)
                ->get();
        } catch (\Throwable $e) {
            \Log::error('QCStabilityBoard@getOocEvents: ' . $e->getMessage());
            return collect();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Per-analyte robust stats table (UCL / LCL / CV%)
    // ─────────────────────────────────────────────────────────────────────

    private function getAnalyteStats(): array
    {
        try {
            return DB::connection($this->pg)
                ->table('public.v_qc_stability_metrics')
                ->orderBy('analyte_name')
                ->get()
                ->map(fn ($r) => [
                    'analyte'       => $r->analyte_name,
                    'code'          => $r->analyte_code,
                    'unit'          => $r->reporting_unit,
                    'robustMean'    => (float) ($r->robust_mean   ?? 0),
                    'robustSd'      => (float) ($r->robust_sd     ?? 0),
                    'robustCvPct'   => (float) ($r->robust_cv_pct ?? 0),
                    'ucl'           => (float) ($r->ucl           ?? 0),
                    'lcl'           => (float) ($r->lcl           ?? 0),
                    'totalTests'    => (int)   ($r->total_tests   ?? 0),
                    'passedTests'   => (int)   ($r->passed_tests  ?? 0),
                    'failedTests'   => (int)   ($r->failed_tests  ?? 0),
                    'passRate'      => (float) ($r->pass_rate_pct ?? 0),
                    'status'        => $this->resolveStatus((float) ($r->robust_cv_pct ?? 0)),
                ])
                ->toArray();
        } catch (\Throwable $e) {
            \Log::error('QCStabilityBoard@getAnalyteStats: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    private function resolveStatus(float $cvPct): string
    {
        if ($cvPct >= 25) return 'critical';
        if ($cvPct >= 15) return 'warning';
        return 'in_control';
    }
}
