<?php

namespace App\Services\Lab;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\Lab\TatCapturedView;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use App\Services\Dashboards\LabTatDashboardService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TatReportService
{
    use DashboardHelpers;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getBatchKpis(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $hasScopedFilters = collect($filters)->filter()->isNotEmpty();

        if (! $hasScopedFilters) {
            $board = app(LabTatDashboardService::class)->getLabTatBoard('active');
            $summary = $board['summary'] ?? [];
            $agingBuckets = collect($board['aging_buckets'] ?? []);

            return [
                'active_batches' => (int) ($summary['active_batches'] ?? 0),
                'overdue_batches' => (int) ($summary['overdue_batches'] ?? 0),
                'due_today_batches' => (int) ($summary['due_today_batches'] ?? 0),
                'sla_compliance_rate' => (int) ($summary['sla_compliance_rate'] ?? 0),
                'avg_completion_days' => $summary['avg_completion_days'] ?? null,
                'on_time_batches' => (int) $agingBuckets->firstWhere('aging_bucket', 'on_time')['batch_count'] ?? 0,
                'no_target_batches' => (int) $agingBuckets->firstWhere('aging_bucket', 'no_target')['batch_count'] ?? 0,
            ];
        }

        $baseQuery = $this->batchQuery($filters);
        $total = (clone $baseQuery)->count();

        $overdue = (clone $baseQuery)
            ->whereNotNull('sh.date_expected')
            ->whereRaw("CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours')")
            ->count();

        $dueToday = (clone $baseQuery)
            ->whereNotNull('sh.date_expected')
            ->whereRaw("CURRENT_TIMESTAMP BETWEEN (sh.date_expected - INTERVAL '12 hours') AND (sh.date_expected + INTERVAL '12 hours')")
            ->count();

        $onTime = (clone $baseQuery)
            ->whereNotNull('sh.date_expected')
            ->whereRaw("CURRENT_TIMESTAMP < (sh.date_expected - INTERVAL '12 hours')")
            ->count();

        $noTarget = (clone $baseQuery)->whereNull('sh.date_expected')->count();
        $slaRate = $total > 0 ? (int) round((($total - $overdue) / $total) * 100) : 100;

        return [
            'active_batches' => $total,
            'overdue_batches' => $overdue,
            'due_today_batches' => $dueToday,
            'sla_compliance_rate' => $slaRate,
            'avg_completion_days' => null,
            'on_time_batches' => $onTime,
            'no_target_batches' => $noTarget,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getParameterKpis(array $filters = []): array
    {
        $query = $this->parameterQuery($filters);

        $stats = (clone $query)
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN tat_overdue_days > 0 THEN 1 ELSE 0 END) as delayed_count'),
                DB::raw('SUM(CASE WHEN tat_overdue_days <= 0 THEN 1 ELSE 0 END) as on_time_count'),
                DB::raw('AVG(tat_overdue_days) as avg_offset'),
            ])
            ->first();

        $total = (int) ($stats->total ?? 0);
        $onTime = (int) ($stats->on_time_count ?? 0);
        $delayed = (int) ($stats->delayed_count ?? 0);

        return [
            'total_parameters' => $total,
            'on_time_count' => $onTime,
            'delayed_count' => $delayed,
            'on_time_rate' => $total > 0 ? (int) round(($onTime / $total) * 100) : 0,
            'avg_offset_days' => round(abs((float) ($stats->avg_offset ?? 0)), 1),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getBatchDeadlinePage(array $filters = [], int $page = 1, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->batchQuery($filters)
            ->select([
                'sh.id',
                'sh.batch_code',
                'sh.status',
                'sh.receipt_date',
                'sh.date_expected',
                'sh.priority',
                'c.name as client_name',
                'st.name as sample_type_name',
            ])
            ->orderByRaw('sh.date_expected ASC NULLS LAST')
            ->orderBy('sh.batch_code');

        $paginator = $query->paginate($perPage, ['*'], 'batchPage', $page);

        $paginator->getCollection()->transform(function ($row) {
            $statusDays = WorkflowBoard::statusDaysUntilTarget($row->date_expected);

            return [
                'id' => $row->id,
                'batch_code' => $row->batch_code,
                'client_name' => $row->client_name ?? '—',
                'sample_type_name' => $row->sample_type_name ?? '—',
                'status' => $row->status,
                'priority' => $row->priority,
                'receipt_date' => $this->formatDate($row->receipt_date),
                'target_date' => $this->formatDate($row->date_expected),
                'status_days' => $statusDays,
                'status_days_label' => WorkflowBoard::formatStatusDaysLabel($statusDays),
                'deadline_bucket' => $this->batchDeadlineBucket($row->date_expected),
            ];
        });

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getParameterPage(array $filters = [], int $page = 1, int $perPage = 25): LengthAwarePaginator
    {
        $paginator = $this->parameterQuery($filters)
            ->orderByDesc('finished_date')
            ->paginate($perPage, ['*'], 'parameterPage', $page);

        $paginator->getCollection()->transform(function ($row) {
            $signedOffset = self::computeSignedTatOffset($row->tat_date, $row->finished_date);

            return [
                'id' => $row->id,
                'analyte_name' => $row->analyte_name,
                'sample_code' => $row->sample_code,
                'sample_type_name' => $row->sample_type_name,
                'analysis_type_name' => $row->analysis_type_name,
                'receipt_date' => $this->formatDate($row->receipt_date),
                'start_date_analysis' => $this->formatDate($row->start_date_analysis),
                'tat_date' => $this->formatDate($row->tat_date),
                'finished_date' => $this->formatDate($row->finished_date),
                'signed_offset' => $signedOffset,
                'analyst_name' => $row->analyst_name ?? '—',
                'tat_remark' => $row->tat_remark,
                'tat_remark_label' => getTatRemark($row->tat_remark) ?? '—',
            ];
        });

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    public function getBatchExportRows(array $filters = []): Collection
    {
        return $this->batchQuery($filters)
            ->select([
                'sh.batch_code',
                'sh.status',
                'sh.receipt_date',
                'sh.date_expected',
                'sh.priority',
                'c.name as client_name',
                'st.name as sample_type_name',
            ])
            ->orderByRaw('sh.date_expected ASC NULLS LAST')
            ->orderBy('sh.batch_code')
            ->get()
            ->map(function ($row) {
                $statusDays = WorkflowBoard::statusDaysUntilTarget($row->date_expected);

                return (object) [
                    'batch_code' => $row->batch_code,
                    'client_name' => $row->client_name ?? '',
                    'sample_type_name' => $row->sample_type_name ?? '',
                    'status' => $row->status,
                    'priority' => $row->priority ?? '',
                    'receipt_date' => $row->receipt_date,
                    'target_date' => $row->date_expected,
                    'status_days_label' => WorkflowBoard::formatStatusDaysLabel($statusDays),
                    'deadline_bucket' => $this->batchDeadlineBucket($row->date_expected),
                ];
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    public function getParameterExportRows(array $filters = []): Collection
    {
        return $this->parameterQuery($filters)
            ->orderByDesc('finished_date')
            ->get()
            ->map(function ($row) {
                $row->signed_offset = self::computeSignedTatOffset($row->tat_date, $row->finished_date);
                $row->tat_remark_label = getTatRemark($row->tat_remark) ?? '';

                return $row;
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function normalizeFilters(array $filters): array
    {
        return [
            'date_from' => ! empty($filters['date_from']) ? (string) $filters['date_from'] : null,
            'date_to' => ! empty($filters['date_to']) ? (string) $filters['date_to'] : null,
            'user_id' => ! empty($filters['user_id']) && $filters['user_id'] !== 'All' ? $filters['user_id'] : null,
            'sample_type_id' => ! empty($filters['sample_type_id']) && $filters['sample_type_id'] !== 'All'
                ? $filters['sample_type_id']
                : null,
            'analysis_type_id' => ! empty($filters['analysis_type_id']) && $filters['analysis_type_id'] !== 'All'
                ? $filters['analysis_type_id']
                : null,
            'analyte_id' => ! empty($filters['analyte_id']) && $filters['analyte_id'] !== 'All'
                ? $filters['analyte_id']
                : null,
            'workflow_stage' => ! empty($filters['workflow_stage']) && $filters['workflow_stage'] !== 'All'
                ? (string) $filters['workflow_stage']
                : null,
            'deadline_status' => ! empty($filters['deadline_status']) && $filters['deadline_status'] !== 'all'
                ? (string) $filters['deadline_status']
                : null,
            'tat_remark' => ! empty($filters['tat_remark']) && $filters['tat_remark'] !== 'all'
                ? (string) $filters['tat_remark']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function batchQuery(array $filters)
    {
        $filters = $this->normalizeFilters($filters);

        $query = DB::table('sample_headers as sh')
            ->leftJoin('crm_customers as c', function ($join): void {
                $this->applyVarcharForeignKeyToUuidJoin($join, 'sh.crm_customer_id', 'c.id');
            })
            ->leftJoin('sample_types as st', function ($join): void {
                $this->applyVarcharForeignKeyToUuidJoin($join, 'sh.sample_type_id', 'st.id');
            })
            ->where('sh.isactive', true)
            ->whereNotNull('sh.status')
            ->where('sh.status', '!=', '')
            ->whereNotIn('sh.status', ['Received', 'Reports', 'Completed']);

        if ($filters['date_from']) {
            $query->whereDate('sh.receipt_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('sh.receipt_date', '<=', $filters['date_to']);
        }

        if ($filters['sample_type_id']) {
            $query->where('sh.sample_type_id', $filters['sample_type_id']);
        }

        if ($filters['workflow_stage']) {
            $query->where('sh.status', $filters['workflow_stage']);
        }

        $this->applyBatchDeadlineStatusFilter($query, $filters['deadline_status']);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function parameterQuery(array $filters)
    {
        $filters = $this->normalizeFilters($filters);

        $query = TatCapturedView::query()->where('is_complete', 1);

        if ($filters['date_from']) {
            $query->whereDate('receipt_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('receipt_date', '<=', $filters['date_to']);
        }

        if ($filters['user_id']) {
            $query->where('analyst_id', $filters['user_id']);
        }

        if ($filters['sample_type_id']) {
            $query->where('sample_type_id', $filters['sample_type_id']);
        }

        if ($filters['analysis_type_id']) {
            $query->where('analysis_type_id', $filters['analysis_type_id']);
        }

        if ($filters['analyte_id']) {
            $query->where('analyte_id', $filters['analyte_id']);
        }

        if ($filters['tat_remark']) {
            $query->where('tat_remark', $filters['tat_remark']);
        }

        return $query;
    }

    protected function applyBatchDeadlineStatusFilter($query, ?string $deadlineStatus): void
    {
        if (! $deadlineStatus) {
            return;
        }

        match ($deadlineStatus) {
            'overdue' => $query->whereNotNull('sh.date_expected')
                ->whereRaw("CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours')"),
            'due_today' => $query->whereNotNull('sh.date_expected')
                ->whereRaw("CURRENT_TIMESTAMP BETWEEN (sh.date_expected - INTERVAL '12 hours') AND (sh.date_expected + INTERVAL '12 hours')"),
            'on_time' => $query->whereNotNull('sh.date_expected')
                ->whereRaw("CURRENT_TIMESTAMP < (sh.date_expected - INTERVAL '12 hours')"),
            'no_target' => $query->whereNull('sh.date_expected'),
            default => null,
        };
    }

    protected function batchDeadlineBucket(?string $targetDate): string
    {
        if (! $targetDate) {
            return 'no_target';
        }

        $statusDays = WorkflowBoard::statusDaysUntilTarget($targetDate);

        if ($statusDays === null) {
            return 'no_target';
        }

        if ($statusDays < 0) {
            return 'overdue';
        }

        if ($statusDays === 0) {
            return 'due_today';
        }

        return 'on_time';
    }

    protected function formatDate(mixed $value): string
    {
        if (! $value) {
            return '—';
        }

        return Carbon::parse($value)->format('d M Y');
    }

    protected function applyVarcharForeignKeyToUuidJoin($join, string $varcharColumn, string $uuidColumn): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $join->whereRaw(
                sprintf(
                    "NULLIF(TRIM(%s::text), '')::uuid = %s",
                    $varcharColumn,
                    $uuidColumn,
                )
            );

            return;
        }

        $join->on($uuidColumn, '=', $varcharColumn);
    }
}
