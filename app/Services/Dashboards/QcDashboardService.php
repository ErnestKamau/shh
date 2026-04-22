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
            $connection = DB::connection($this->repositoryConnection());
            $schema     = $this->reportingSchema();

            // Read pre-computed ISO 13528 Algorithm A statistics from Postgres
            $rows = $connection->table("{$schema}.v_qc_stability_metrics")
                ->orderByDesc('robust_cv_pct')
                ->get();

            if ($rows->isEmpty()) {
                return $this->emptyQcBoard('No QC data in reporting mart. Run ETL sync to populate.');
            }

            // CV% thresholds (aligned with settings in config/imara_ai.php)
            $warnThreshold     = (float) config('imara_ai.reporting.qc_cv_warning_pct',  15.0);
            $criticalThreshold = (float) config('imara_ai.reporting.qc_cv_critical_pct', 25.0);

            $normalizedDetailRows = $rows->map(function ($row) use ($warnThreshold, $criticalThreshold) {
                $cv = (float) ($row->robust_cv_pct ?? 0);

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
                    'ucl'                        => round((float) ($row->ucl ?? 0), 4),
                    'lcl'                        => round((float) ($row->lcl ?? 0), 4),
                    'total_tests'                => (int) ($row->total_tests  ?? 0),
                    'passed_tests'               => (int) ($row->passed_tests ?? 0),
                    'failed_tests'               => (int) ($row->failed_tests ?? 0),
                    'pass_rate_pct'              => (float) ($row->pass_rate_pct ?? 0),
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
            Log::warning('QcStabilityBoard (Postgres) failed: ' . $exception->getMessage());
            return $this->emptyQcBoard(null);
        }
    }

    public function getParameterPerformanceData(): array
    {
        try {
            $conn   = $this->repositoryConnection();
            $schema = $this->reportingSchema();

            $results = DB::connection($conn)->table("{$schema}.qc_results as qr")
                ->join("{$schema}.analytes as a", "a.source_id", "=", "qr.analyte_id")
                ->whereNotNull('qr.status_code')
                ->whereIn('qr.status_code', ['PASSED', 'FAILED'])
                ->where('qr.source_created_at', '>=', now()->subMonths(6))
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
            Log::warning('QcDashboardService::getParameterPerformanceData failed: ' . $e->getMessage());
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
            'message' => $message ?? 'QC stability reporting mart is unavailable.',
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
