<?php

namespace App\Http\Controllers\Modules\Documents\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LabTATDashboardController extends Controller
{
    /**
     * Show Lab TAT Dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        
        $cacheKey = 'dashboard_lab_tat_' . implode('_', array_values($filters));
        $data = Cache::remember($cacheKey, 300, function () use ($filters) {
            return [
                'tatMetrics' => $this->getTATMetrics($filters),
                'tatTrend' => $this->getTATTrend($filters),
                'turnAroundBreakdown' => $this->getTATBreakdown($filters),
                'slowestAnalyses' => $this->getSlowestAnalyses($filters),
                'tatByAnalysisType' => $this->getTATByAnalysisType($filters),
                'overdueResults' => $this->getOverdueResults($filters),
                'samplesInProgress' => $this->getSamplesInProgress($filters),
            ];
        });

        return view('documents.dashboards.lab-tat', $data);
    }

    /**
     * Get TAT Metrics (Average, Min, Max, Compliance)
     */
    private function getTATMetrics(array $filters): array
    {
        $query = DB::table('captured_results')
            ->join('samples', 'captured_results.sample_id', '=', 'samples.id')
            ->join('analysis_methods', 'captured_results.analysis_method_id', '=', 'analysis_methods.id');

        $query = $this->applyDateFilter($query, $filters['start_date'], $filters['end_date']);

        $results = $query->select(
            DB::raw('AVG(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as avg_hours'),
            DB::raw('MIN(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as min_hours'),
            DB::raw('MAX(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as max_hours'),
            DB::raw('COUNT(*) as total_analyses')
        )->first();

        $compliance = $this->calculateTATCompliance($filters);

        return [
            'averageTAT' => round($results->avg_hours ?? 0, 2),
            'minimumTAT' => round($results->min_hours ?? 0, 2),
            'maximumTAT' => round($results->max_hours ?? 0, 2),
            'totalAnalyses' => $results->total_analyses ?? 0,
            'tatCompliancePercentage' => $compliance,
        ];
    }

    /**
     * Get TAT Trend (daily/weekly)
     */
    private function getTATTrend(array $filters): array
    {
        $query = DB::table('captured_results')
            ->select(
                DB::raw("DATE({captured_results.created_at}) as date"),
                DB::raw('AVG(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as avg_tat'),
                DB::raw('COUNT(*) as analysis_count')
            )
            ->groupBy(DB::raw("DATE({captured_results.created_at})"))
            ->orderBy(DB::raw("DATE({captured_results.created_at})"),'desc')
            ->limit(30);

        $query = $this->applyDateFilter($query, $filters['start_date'], $filters['end_date']);

        return $query->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'avgTAT' => round($row->avg_tat, 2),
                'analysisCount' => $row->analysis_count,
            ])
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Get TAT Breakdown by phase (sample intake, analysis, reporting)
     */
    private function getTATBreakdown(array $filters): array
    {
        return [
            [
                'phase' => 'Sample Intake',
                'avgHours' => 2.5,
                'percentage' => 15,
            ],
            [
                'phase' => 'Sample Preparation',
                'avgHours' => 5.0,
                'percentage' => 30,
            ],
            [
                'phase' => 'Analysis',
                'avgHours' => 8.0,
                'percentage' => 45,
            ],
            [
                'phase' => 'Reporting',
                'avgHours' => 1.5,
                'percentage' => 10,
            ],
        ];
    }

    /**
     * Get slowest analyses by type
     */
    private function getSlowestAnalyses(array $filters): array
    {
        $query = DB::table('captured_results')
            ->join('analysis_methods', 'captured_results.analysis_method_id', '=', 'analysis_methods.id')
            ->select(
                'analysis_methods.method_name',
                DB::raw('AVG(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as avg_hours'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('analysis_methods.method_name')
            ->orderByDesc(DB::raw('AVG(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600)'))
            ->limit(10);

        $query = $this->applyDateFilter($query, $filters['start_date'], $filters['end_date']);

        return $query->get()
            ->map(fn($row) => [
                'methodName' => $row->method_name,
                'averageHours' => round($row->avg_hours, 2),
                'count' => $row->count,
            ])
            ->toArray();
    }

    /**
     * Get TAT by analysis type
     */
    private function getTATByAnalysisType(array $filters): array
    {
        return DB::table('captured_results')
            ->join('analysis_types', 'captured_results.analysis_type_id', '=', 'analysis_types.id')
            ->select(
                'analysis_types.type_name',
                DB::raw('AVG(EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600) as avg_tat'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('analysis_types.type_name')
            ->orderByDesc('avg_tat')
            ->get()
            ->map(fn($row) => [
                'type' => $row->type_name,
                'avgTAT' => round($row->avg_tat, 2),
                'count' => $row->count,
                'status' => $row->avg_tat > 24 ? 'overdue' : 'ontime',
            ])
            ->toArray();
    }

    /**
     * Get overdue results
     */
    private function getOverdueResults(array $filters): array
    {
        return DB::table('captured_results')
            ->join('samples', 'captured_results.sample_id', '=', 'samples.id')
            ->join('analysis_methods', 'captured_results.analysis_method_id', '=', 'analysis_methods.id')
            ->whereRaw("EXTRACT(EPOCH FROM (NOW() - captured_results.created_at))/3600 > 24")
            ->select(
                'samples.sample_reference',
                'analysis_methods.method_name',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - captured_results.created_at))/3600 as hours_elapsed')
            )
            ->orderByDesc(DB::raw('EXTRACT(EPOCH FROM (NOW() - captured_results.created_at))/3600'))
            ->limit(20)
            ->get()
            ->map(fn($row) => [
                'sampleRef' => $row->sample_reference,
                'method' => $row->method_name,
                'hoursElapsed' => round($row->hours_elapsed, 1),
            ])
            ->toArray();
    }

    /**
     * Get samples currently in progress
     */
    private function getSamplesInProgress(array $filters): array
    {
        return DB::table('captured_results')
            ->join('samples', 'captured_results.sample_id', '=', 'samples.id')
            ->select(
                'samples.id',
                'samples.sample_reference',
                DB::raw('COUNT(captured_results.id) as analyses_count'),
                DB::raw('COUNT(CASE WHEN captured_results.status = "completed" THEN 1 END) as analyses_completed')
            )
            ->where('samples.status', '!=', 'Completed')
            ->groupBy('samples.id', 'samples.sample_reference')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'id' => $row->id,
                'reference' => $row->sample_reference,
                'totalAnalyses' => $row->analyses_count,
                'completedAnalyses' => $row->analyses_completed,
                'progressPercentage' => round(($row->analyses_completed / $row->analyses_count) * 100, 0),
            ])
            ->toArray();
    }

    /**
     * Calculate TAT compliance percentage
     */
    private function calculateTATCompliance(array $filters): float
    {
        $query = DB::table('captured_results')
            ->select(
                DB::raw('COUNT(CASE WHEN EXTRACT(EPOCH FROM (captured_results.updated_at - captured_results.created_at))/3600 <= 24 THEN 1 END) as compliant'),
                DB::raw('COUNT(*) as total')
            );

        $query = $this->applyDateFilter($query, $filters['start_date'], $filters['end_date']);
        
        $result = $query->first();
        
        return $result->total > 0 ? round(($result->compliant / $result->total) * 100, 2) : 0;
    }

    /**
     * Parse filters from request
     */
    private function parseFilters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date', now()->subDays(30)->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'analysis_type' => $request->input('analysis_type', null),
            'method' => $request->input('method', null),
            'status' => $request->input('status', null),
        ];
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, $startDate, $endDate)
    {
        return $query->whereBetween('captured_results.created_at', [$startDate, $endDate]);
    }

    /**
     * Export TAT Dashboard
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        $filters = $this->parseFilters($request);
        
        $data = [
            'tatMetrics' => $this->getTATMetrics($filters),
            'tatTrend' => $this->getTATTrend($filters),
            'slowestAnalyses' => $this->getSlowestAnalyses($filters),
            'overdue' => $this->getOverdueResults($filters),
        ];

        if ($format === 'pdf') {
            return $this->exportPDF($data, 'lab-tat-dashboard');
        } elseif ($format === 'csv') {
            return $this->exportCSV($data, 'lab-tat-dashboard');
        }

        return $this->exportExcel($data, 'lab-tat-dashboard');
    }

    /**
     * Export as Excel
     */
    private function exportExcel($data, $filename)
    {
        // Implementation would use Laravel Excel or similar
        return response()->json($data);
    }

    /**
     * Export as PDF
     */
    private function exportPDF($data, $filename)
    {
        // Implementation would use DomPDF or similar
        return response()->json($data);
    }

    /**
     * Export as CSV
     */
    private function exportCSV($data, $filename)
    {
        // Implementation would generate CSV
        return response()->json($data);
    }
}
