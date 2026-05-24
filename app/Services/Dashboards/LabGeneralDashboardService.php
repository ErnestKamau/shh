<?php

namespace App\Services\Dashboards;

use App\Services\Dashboards\Concerns\DashboardHelpers;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LabGeneralDashboardService
{
    use DashboardHelpers;

    private const GCLA_ZONE_GPS = [
        'EZO' => '-6.8161, 39.2888',
        'CZO' => '-6.1630, 35.7516',
        'LZO' => '-2.5164, 32.9175',
        'NZO' => '-3.3869, 36.6830',
        'SZO' => '-10.2697, 40.1811',
        'SHZO' => '-8.9094, 33.4608',
    ];

    /**
     * Get date-filtered summary figures for the General Analytics page.
     */
    public function getSummary(array $filters = []): array
    {
        $batches = DB::table('sample_headers')
            ->where('isactive', 1);

        $this->applyDateRange($batches, $filters);

        $activeBatches = (clone $batches)
            ->where('status', '!=', 'Completed')
            ->whereNotNull('status')
            ->count();

        $overdueBatches = (clone $batches)
            ->where('status', '!=', 'Completed')
            ->whereNotNull('status')
            ->whereNotNull('date_expected')
            ->whereDate('date_expected', '<', now()->toDateString())
            ->count();

        $dueTodayBatches = (clone $batches)
            ->where('status', '!=', 'Completed')
            ->whereNotNull('status')
            ->whereDate('date_expected', now()->toDateString())
            ->count();

        $workflowStages = (clone $batches)
            ->where('status', '!=', 'Completed')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->count('status');

        $avgCompletionDays = (clone $batches)
            ->where('status', 'Completed')
            ->avg(DB::raw('DATE_PART(\'day\', updated_at::timestamp - created_at::timestamp)'));

        $testsRequested = DB::table('sample_details')
            ->join('sample_headers', 'sample_headers.id', '=', 'sample_details.sample_header_id')
            ->where('sample_headers.isactive', 1);

        $this->applyDateRange($testsRequested, $filters);

        $testsCompleted = DB::table('tat_captured')
            ->join('sample_headers', 'sample_headers.id', '=', 'tat_captured.sample_header_id')
            ->where('sample_headers.isactive', 1)
            ->where('tat_captured.is_complete', 1);

        $this->applyDateRange($testsCompleted, $filters);

        $testsRequestedCount = (int) $testsRequested->count();
        $testsCompletedCount = (int) $testsCompleted->count();

        return [
            'active_batches' => (int) $activeBatches,
            'overdue_batches' => (int) $overdueBatches,
            'due_today_batches' => (int) $dueTodayBatches,
            'workflow_stages' => (int) $workflowStages,
            'avg_completion_days' => $avgCompletionDays === null ? null : round((float) $avgCompletionDays, 2),
            'sla_compliance_rate' => $activeBatches > 0
                ? (int) round((($activeBatches - $overdueBatches) / $activeBatches) * 100)
                : 100,
            'tests_requested' => $testsRequestedCount,
            'tests_completed' => $testsCompletedCount,
            'tests_pending' => max($testsRequestedCount - $testsCompletedCount, 0),
        ];
    }

    /**
     * Get date-filtered workflow stage figures for the General Analytics page.
     */
    public function getWorkflowStageSummary(array $filters = []): array
    {
        $query = DB::table('sample_headers')
            ->where('isactive', 1)
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->whereNotIn('status', ['Received', 'Reports', 'Completed']);

        $this->applyDateRange($query, $filters);

        return $query
            ->select(
                'status as workflow_stage',
                DB::raw('COUNT(id) as total_batches'),
                DB::raw("SUM(CASE WHEN date_expected IS NOT NULL AND date_expected::date < CURRENT_DATE THEN 1 ELSE 0 END) as overdue_batches"),
                DB::raw("SUM(CASE WHEN date_expected::date = CURRENT_DATE THEN 1 ELSE 0 END) as due_today_batches"),
                DB::raw("AVG(COALESCE(date_expected::date, CURRENT_DATE) - created_at::date) as avg_days_to_target"),
                DB::raw("AVG(CASE WHEN date_expected IS NOT NULL AND CURRENT_DATE > date_expected::date THEN CURRENT_DATE - date_expected::date ELSE NULL END) as avg_days_overdue"),
                DB::raw('MAX(updated_at) as refreshed_at')
            )
            ->groupBy('status')
            ->orderByDesc('overdue_batches')
            ->orderByDesc('total_batches')
            ->get()
            ->map(fn ($row) => [
                'workflow_stage' => $row->workflow_stage,
                'total_batches' => (int) $row->total_batches,
                'overdue_batches' => (int) $row->overdue_batches,
                'due_today_batches' => (int) $row->due_today_batches,
                'avg_days_to_target' => $this->toFloat($row->avg_days_to_target),
                'avg_days_overdue' => $this->toFloat($row->avg_days_overdue),
                'avg_completion_days' => null,
                'refreshed_at' => $row->refreshed_at,
            ])
            ->values()
            ->all();
    }

    public function getWorkflowStageCounts(array $filters = []): array
    {
        $query = DB::table('sample_headers')
            ->where('isactive', 1)
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->whereNotIn('status', ['Received', 'Reports', 'Completed']);

        $this->applyDateRange($query, $filters);

        return $query
            ->select('status', DB::raw('COUNT(id) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * Get geographical distribution of samples for mapping.
     */
    public function getLabGeographicData($year = null, array $filters = []): array
    {
        $year = $year ?? date('Y');
        $results = [];
        
        $samples = DB::table('sample_headers')
            ->where('isactive', 1)
            ->where('status', '!=', 'Completed')
            ->when(empty($filters), fn($query) => $query->whereYear('created_at', $year));

        $this->applyDateRange($samples, $filters);

        $samples = $samples->get();
        $zoneKeysById = DB::table('zones')->pluck('key', 'id');

        foreach ($samples as $sample) {
            $details = DB::table('sample_details')
                ->where('sample_header_id', $sample->id)
                ->get();
                
            foreach ($details as $detail) {
                $point = DB::table('sample_points')->find($detail->sample_point_id);
                $gps = null;
                
                if ($point && $point->gps) {
                    $gps = $point->gps;
                } else {
                    $unit = DB::table('crm_company_units')->where('name', $sample->crm_unit_name)->first();
                    if ($unit) {
                        $unitPoint = DB::table('sample_points')->where('crm_company_unit_id', $unit->id)->first();
                        if ($unitPoint && $unitPoint->gps) {
                            $gps = $unitPoint->gps;
                        }
                    }
                }

                if (!$gps && isset($sample->zone_id)) {
                    $zoneKey = $zoneKeysById[$sample->zone_id] ?? null;
                    $gps = $zoneKey ? (self::GCLA_ZONE_GPS[$zoneKey] ?? null) : null;
                }

                if ($gps) {
                    if (!isset($results[$gps])) {
                        $results[$gps] = 0;
                    }
                    $results[$gps]++;
                }
            }
        }

        // Format for Leaflet/Heatchart: [[lat, lng, intensity], ...]
        $formatted = [];
        foreach ($results as $gpsStr => $count) {
            $parts = explode(',', $gpsStr);
            if (count($parts) === 2) {
                $formatted[] = [
                    'lat' => (float) trim($parts[0]),
                    'lng' => (float) trim($parts[1]),
                    'intensity' => $count
                ];
            }
        }

        return $formatted;
    }

    /**
     * Get monthly registration trends (Historical Throughput).
     */
    public function getLabMonthlyTrends($year = null, array $filters = []): array
    {
        $range = $this->normalizeDateRange($filters, $year);
        $months = [];
        $counts = [];

        $period = CarbonPeriod::create(
            Carbon::parse($range['start_date'])->startOfMonth(),
            '1 month',
            Carbon::parse($range['end_date'])->startOfMonth()
        );

        foreach ($period as $month) {
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $months[] = $month->format('M Y');

            $counts[] = DB::table('sample_headers')
                ->where('isactive', 1)
                ->whereBetween('receipt_date', [
                    $monthStart->toDateTimeString(),
                    $monthEnd->toDateTimeString(),
                ])
                ->count();
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }

    /**
     * Get top clients by sample volume.
     */
    public function getTopClientsData(int $limit = 10, array $filters = []): Collection
    {
        $query = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->where('sample_headers.isactive', 1);

        $this->applyDateRange($query, $filters);

        return $query
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get Sunburst Testing Matrix data (Sample Type -> Lab Section).
     */
    public function getTestingMatrixData($year = null, array $filters = []): array
    {
        $year = $year ?? date('Y');

        // lab_section_ids is null in sample_headers — use sample_details→labs join instead
        $query = DB::table('sample_headers')
            ->join('sample_details', 'sample_details.sample_header_id', '=', 'sample_headers.id')
            ->join('labs', 'labs.id', '=', 'sample_details.lab_id')
            ->join('sample_types', 'sample_types.id', '=', 'sample_headers.sample_type_id')
            ->where('sample_headers.isactive', 1)
            ->whereNotNull('sample_headers.sample_type_id')
            ->when(empty($filters), fn($query) => $query->whereYear('sample_headers.created_at', $year));

        $this->applyDateRange($query, $filters);

        $rows = $query
            ->select(
                'sample_types.name as type_name',
                'labs.name as lab_name',
                DB::raw('COUNT(*) as cnt')
            )
            ->groupBy('sample_types.id', 'sample_types.name', 'labs.id', 'labs.name')
            ->orderByDesc('cnt')
            ->get();

        // Group into [ type => [ lab => count ] ]
        $matrix = [];
        foreach ($rows as $row) {
            if (!isset($matrix[$row->type_name])) {
                $matrix[$row->type_name] = [];
            }
            $matrix[$row->type_name][$row->lab_name] = ($matrix[$row->type_name][$row->lab_name] ?? 0) + $row->cnt;
        }

        $formatted = [];
        foreach ($matrix as $type => $labArray) {
            $children = [];
            $typeTotal = 0;
            foreach ($labArray as $lab => $count) {
                $children[] = ['name' => $lab, 'value' => $count];
                $typeTotal += $count;
            }
            usort($children, fn($a, $b) => $b['value'] <=> $a['value']);
            $formatted[] = ['name' => $type, 'children' => $children, 'total' => $typeTotal];
        }

        usort($formatted, fn($a, $b) => $b['total'] <=> $a['total']);

        return $formatted;
    }

    private function applyDateRange($query, array $filters): void
    {
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            return;
        }

        $range = $this->normalizeDateRange($filters);

        if (!$range['start_date'] || !$range['end_date']) {
            return;
        }

        $query->whereBetween('sample_headers.receipt_date', [
            $range['start_date'] . ' 00:00:00',
            $range['end_date'] . ' 23:59:59',
        ]);
    }

    private function normalizeDateRange(array $filters = [], $year = null): array
    {
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            return [
                'start_date' => $filters['start_date'] ?? now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString(),
                'end_date' => $filters['end_date'] ?? now()->toDateString(),
            ];
        }

        if ($year) {
            return [
                'start_date' => Carbon::create((int) $year, 1, 1)->toDateString(),
                'end_date' => Carbon::create((int) $year, 12, 31)->toDateString(),
            ];
        }

        return [
            'start_date' => Carbon::now()->startOfYear()->toDateString(),
            'end_date' => Carbon::now()->endOfYear()->toDateString(),
        ];
    }
}
