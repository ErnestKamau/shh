<?php

namespace App\Services\Dashboards;

use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QcDashboardService
{
    use DashboardHelpers;

    public function getQcStabilityBoard(): array
    {
        try {
            $connection = DB::connection('mysql');

            // Robust Statistics are pulled from the pre-computed qc_processed_result table
            // We join with analytes and aggregate pass/fail counts from qc_results
            $rows = $connection->table('qc_processed_result as pr')
                ->join('analytes as a', 'a.id', '=', 'pr.analyte_id')
                ->leftJoin('qc_results as qr', function($join) {
                    $join->on('qr.analyte_id', '=', 'pr.analyte_id')
                         ->where('qr.is_qc_processed', 1);
                })
                ->select([
                    'a.name as analyte_name',
                    'a.code as analyte_code',
                    'pr.robust_mean',
                    'pr.robust_standard_deviation as robust_sd',
                    'pr.robust_cv_percentage as robust_cv_pct',
                    DB::raw('COUNT(qr.id) as total_tests'),
                    DB::raw("SUM(CASE WHEN qr.status_code = 'PASSED' THEN 1 ELSE 0 END) as passed_tests"),
                    DB::raw("SUM(CASE WHEN qr.status_code IN ('FAILED', 'OUT_OF_CONTROL') THEN 1 ELSE 0 END) as failed_tests")
                ])
                ->groupBy('a.name', 'a.code', 'pr.robust_mean', 'pr.robust_standard_deviation', 'pr.robust_cv_percentage')
                ->orderByDesc('pr.robust_cv_percentage')
                ->get();

            if ($rows->isEmpty()) {
                return $this->emptyQcBoard('No processed QC statistical data found in legacy tables.');
            }

            // CV% thresholds (aligned with settings in config/imara_ai.php)
            $warnThreshold     = (float) config('imara_ai.reporting.qc_cv_warning_pct',  15.0);
            $criticalThreshold = (float) config('imara_ai.reporting.qc_cv_critical_pct', 25.0);

            $normalizedDetailRows = $rows->map(function ($row) use ($warnThreshold, $criticalThreshold) {
                $cv = (float) ($row->robust_cv_pct ?? 0);
                $total = (int) $row->total_tests;
                $passed = (int) $row->passed_tests;
                
                $passRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

                $status = 'stable';
                if ($cv >= $criticalThreshold) $status = 'critical';
                elseif ($cv >= $warnThreshold)  $status = 'warning';

                return [
                    'source_id'                  => null,
                    'sample_type_name'           => '—',
                    'analysis_type_name'         => '—',
                    'analyte_name'               => $row->analyte_name,
                    'analyte_code'               => $row->analyte_code,
                    'robust_mean'                => round((float) ($row->robust_mean ?? 0), 4),
                    'robust_standard_deviation'  => round((float) ($row->robust_sd   ?? 0), 4),
                    'robust_cv_percentage'       => round($cv, 2),
                    'ucl'                        => round((float) (($row->robust_mean ?? 0) + (3 * ($row->robust_sd ?? 0))), 4),
                    'lcl'                        => round((float) (($row->robust_mean ?? 0) - (3 * ($row->robust_sd ?? 0))), 4),
                    'total_tests'                => $total,
                    'passed_tests'               => $passed,
                    'failed_tests'               => (int) ($row->failed_tests ?? 0),
                    'pass_rate_pct'              => $passRate,
                    'stability_status'           => $status,
                    'status_label'               => $this->qcStatusLabels()[$status] ?? ucfirst($status),
                    'refreshed_at'               => now()->toDateTimeString(),
                ];
            });

            $summary = [
                'total_records'   => $normalizedDetailRows->count(),
                'stable_records'  => $normalizedDetailRows->where('stability_status', 'stable')->count(),
                'warning_records' => $normalizedDetailRows->where('stability_status', 'warning')->count(),
                'critical_records'=> $normalizedDetailRows->where('stability_status', 'critical')->count(),
                'unknown_records' => 0,
                'avg_cv_percentage' => round((float) ($normalizedDetailRows->avg('robust_cv_percentage') ?? 0), 2),
            ];

            return [
                'available' => true,
                'message'   => null,
                'summary'   => $summary,
                'status_summary' => collect($this->qcStatusLabels())->map(function ($label, $key) use ($normalizedDetailRows) {
                    $subset = $normalizedDetailRows->where('stability_status', $key);
                    return [
                        'stability_status' => $key,
                        'label'            => $label,
                        'record_count'     => $subset->count(),
                        'avg_cv_percentage'=> round((float) ($subset->avg('robust_cv_percentage') ?? 0), 2),
                    ];
                })->all(),
                'exception_rows' => $normalizedDetailRows->take(12)->values()->all(),
                'charts' => [
                    'status_labels'  => array_values($this->qcStatusLabels()),
                    'status_counts'  => collect($this->qcStatusLabels())
                        ->map(fn ($l, $k) => $normalizedDetailRows->where('stability_status', $k)->count())
                        ->values()->all(),
                    'top_labels'     => $normalizedDetailRows->take(8)
                        ->map(fn ($r) => $r['analyte_name'])
                        ->values()->all(),
                    'top_cv_values'  => $normalizedDetailRows->take(8)
                        ->pluck('robust_cv_percentage')
                        ->values()->all(),
                ],
                'refreshed_at' => now()->toDateTimeString(),
            ];
        } catch (\Throwable $exception) {
            Log::warning('QcStabilityBoard (MySQL) failed: ' . $exception->getMessage());
            return $this->emptyQcBoard(null);
        }
    }

    public function getParameterPerformanceData(): array
    {
        try {
            $results = DB::connection('mysql')->table("qc_results as qr")
                ->join("analytes as a", "a.id", "=", "qr.analyte_id")
                ->whereNotNull('qr.status_code')
                ->whereIn('qr.status_code', ['PASSED', 'FAILED', 'OUT_OF_CONTROL'])
                ->where('qr.created_at', '>=', now()->subMonths(6))
                ->select('a.name', 'qr.status_code', DB::raw('count(*) as total'))
                ->groupBy('a.name', 'qr.status_code')
                ->get();

            $performance = [];
            foreach ($results as $res) {
                if (!isset($performance[$res->name])) {
                    $performance[$res->name] = ['name' => $res->name, 'pass' => 0, 'fail' => 0];
                }
                if ($res->status_code === 'PASSED') {
                    $performance[$res->name]['pass'] += (int) $res->total;
                } else {
                    $performance[$res->name]['fail'] += (int) $res->total;
                }
            }

            return collect($performance)->map(function ($p) {
                $total = $p['pass'] + $p['fail'];
                $p['rate']  = $total > 0 ? round(($p['pass'] / $total) * 100, 1) : 0;
                $p['total'] = $total;
                return $p;
            })->sortByDesc('total')->take(10)->values()->all();

        } catch (\Throwable $e) {
            Log::warning('QcDashboardService::getParameterPerformanceData (MySQL) failed: ' . $e->getMessage());
            return [];
        }
    }

    protected function qcStatusLabels(): array
    {
        return [
            'stable' => __('mas/qc.stable'),
            'warning' => __('mas/qc.warning'),
            'critical' => __('mas/qc.critical'),
            'unknown' => __('mas/qc.unknown'),
        ];
    }

    protected function emptyQcBoard(?string $message = null): array
    {
        $statusRows = collect($this->qcStatusLabels())->map(function ($label, $statusKey) {
            return [
                'stability_status' => $statusKey,
                'label' => $label,
                'record_count' => 0,
                'avg_cv_percentage' => null,
                'max_cv_percentage' => null,
            ];
        })->values();

        return [
            'available' => false,
            'message' => $message ?? 'QC stability metrics are currently unavailable.',
            'summary' => [
                'total_records' => 0,
                'stable_records' => 0,
                'warning_records' => 0,
                'critical_records' => 0,
                'unknown_records' => 0,
                'avg_cv_percentage' => 0,
            ],
            'status_summary' => $statusRows->all(),
            'exception_rows' => [],
            'charts' => [
                'status_labels' => $statusRows->pluck('label')->all(),
                'status_counts' => $statusRows->pluck('record_count')->all(),
                'top_labels' => [],
                'top_cv_values' => [],
            ],
            'refreshed_at' => null,
        ];
    }
}
