<?php

namespace App\Services\Lab;

use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\ChainOfCustody;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SubmissionFormInstance;
use App\SampleDate;
use App\SampleHeader;
use App\SamplesCategory;
use App\AnalysisType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SampleWorkflowKpiStatisticsService
{
    private const REGISTRATION_STATUSES = ['Samples Receiving', 'Samples Request Review'];

    private const LAB_STATUS = 'Samples In Lab';

    /**
     * @return array<string, int|string>
     */
    public function getOverviewMetrics(?Carbon $asOf = null): array
    {
        $asOf ??= now();
        $monthStart = $asOf->copy()->startOfMonth()->toDateString();
        $monthEnd = $asOf->copy()->endOfMonth()->toDateString();

        $active = SampleHeader::query()->where('isactive', 1);

        $receiving = (clone $active)->where('status', 'Samples Receiving')->count();
        $requestReview = (clone $active)->where('status', 'Samples Request Review')->count();
        $inLab = (clone $active)->where('status', self::LAB_STATUS)->count();
        $verification = (clone $active)->where('status', 'Sample Verification')->count();
        $approval = (clone $active)->where('status', 'Sample Approval')->count();
        $completed = (clone $active)->where('status', 'Completed Sample')->count();
        $totalActive = (clone $active)->count();

        $portalSubmitted = (int) WorkflowBoard::receivingSubmissionFormsQuery(['submitted', 'Submitted'])->count();
        $readyForReception = (int) SubmissionFormInstance::query()
            ->whereHas('submissionForm', fn ($q) => $q->where('form_type', 'template'))
            ->whereIn('status', ['submitted', 'Submitted'])
            ->count();

        $thisMonthRegistered = (clone $active)
            ->whereNotNull('receipt_date')
            ->whereBetween('receipt_date', [$monthStart, $monthEnd])
            ->count();

        return [
            'total' => $totalActive,
            'samples_receiving' => $receiving,
            'request_review' => $requestReview,
            'samples_in_lab' => $inLab,
            'verification' => $verification,
            'approval' => $approval,
            'completed' => $completed,
            'portal_submitted' => $portalSubmitted,
            'ready_for_reception' => $readyForReception,
            'this_month_registered' => $thisMonthRegistered,
            'as_of' => $asOf->format('l, F j, Y'),
        ];
    }

    /**
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     samples_scheduled: int,
     *     samples_collected: int,
     *     registration_rate_percent: float,
     *     clients_registered: int
     * }
     */
    public function getRegistrationPeriodMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $scheduled = $this->scheduledFormsInRange($start, $end);
        $collected = $this->collectedBatchesInRange($start, $end);

        $scheduledCount = $scheduled->count();
        $collectedCount = $collected->count();
        $clientsRegistered = $collected->pluck('crm_customer_id')->filter()->unique()->count();

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'samples_scheduled' => $scheduledCount,
            'samples_collected' => $collectedCount,
            'registration_rate_percent' => $this->calculateRate($collectedCount, $scheduledCount + $collectedCount),
            'clients_registered' => $clientsRegistered,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRegistrationDailyRows(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $scheduledByDate = $this->scheduledFormsInRange($start, $end)
            ->groupBy(fn (SubmissionFormInstance $form): string => $form->submitted_at?->toDateString() ?? $form->created_at->toDateString());

        $collectedByDate = $this->collectedBatchesInRange($start, $end)
            ->groupBy(fn (SampleHeader $header): string => Carbon::parse($header->receipt_date)->toDateString());

        $rows = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            /** @var Collection<int, SubmissionFormInstance> $dayScheduled */
            $dayScheduled = $scheduledByDate->get($dateKey, collect());
            /** @var Collection<int, SampleHeader> $dayCollected */
            $dayCollected = $collectedByDate->get($dateKey, collect());

            $scheduledCount = $dayScheduled->count();
            $collectedCount = $dayCollected->count();

            $rows[] = [
                'date' => $dateKey,
                'samples_scheduled' => $scheduledCount,
                'samples_collected' => $collectedCount,
                'registration_rate_percent' => $this->formatRate($collectedCount, $scheduledCount + $collectedCount),
                'clients_registered' => $dayCollected->pluck('crm_customer_id')->filter()->unique()->count(),
            ];

            $cursor->addDay();
        }

        $period = $this->getRegistrationPeriodMetrics($startDate, $endDate);
        $rows[] = [
            'date' => 'TOTAL',
            'samples_scheduled' => $period['samples_scheduled'],
            'samples_collected' => $period['samples_collected'],
            'registration_rate_percent' => $this->formatRate(
                $period['samples_collected'],
                $period['samples_scheduled'] + $period['samples_collected']
            ),
            'clients_registered' => $period['clients_registered'],
        ];

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRegistrationDetailRows(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $samples = SamplesCategory::query()
            ->where(function ($query) use ($start, $end): void {
                $query->whereIn('workflow_stage', self::REGISTRATION_STATUSES)
                    ->orWhereBetween('receipt_date', [$start->toDateString(), $end->toDateString()]);
            })
            ->get();

        if ($samples->isEmpty()) {
            return [];
        }

        $headerIds = $samples->pluck('sample_header_id')->filter()->unique()->values();
        $sampleDetailIds = $samples->pluck('id')->filter()->unique()->values();

        $headers = SampleHeader::query()->whereIn('id', $headerIds)->get()->keyBy('id');
        $dueDates = SampleDate::query()
            ->whereIn('sample_header_id', $headerIds)
            ->where('name', 'Target Date')
            ->get()
            ->keyBy('sample_header_id');

        $capturedBySample = $this->capturedResultsGroupedBySampleDetail($sampleDetailIds);
        $equipmentBySample = $this->equipmentIdsBySampleDetail($sampleDetailIds);

        return $samples->map(function ($row) use ($headers, $dueDates, $capturedBySample, $equipmentBySample): array {
            $header = $headers->get($row->sample_header_id);
            $detailId = (string) $row->id;
            $parameters = $capturedBySample->get($detailId, collect())->pluck('analyte_code')->filter()->unique()->implode(', ');

            $scheduledCollected = $this->resolveScheduledCollectedLabel($header, $row);

            return [
                'date' => $row->receipt_date ?: ($row->date_collected ?: '—'),
                'client' => $row->crm_name ?? '—',
                'samples_scheduled_collected' => $scheduledCollected,
                'sampler' => $row->sampling_officer_name ?? ($header?->sampling_officer_name ?? '—'),
                'equipment_id' => $equipmentBySample->get($detailId, '—'),
                'job_sample_id' => $this->formatJobSampleId($row),
                'location' => $row->gps ?? '—',
                'sampling_points' => $row->sample_point_name ?? '—',
                'parameters' => $parameters !== '' ? $parameters : '—',
                'temp' => '—',
                'units' => $row->reporting_unit_name ?? '—',
                'volume' => $row->quantity ?? '—',
                'registered_by' => $row->submit_by ?? ($header?->receiving_officer_name ?? '—'),
                'due_date' => $dueDates->get($row->sample_header_id)?->date ?? ($header?->date_expected ?? '—'),
            ];
        })->values()->all();
    }

    /**
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     jobs_received: int,
     *     jobs_completed: int,
     *     jobs_pending: int,
     *     data_entry_complete: int,
     *     data_entry_partial: int,
     *     data_entry_not_started: int,
     *     review_pending: int,
     *     review_approved: int
     * }
     */
    public function getLaboratoryPeriodMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $received = $this->labEntryEventsInRange($start, $end);
        $completed = $this->labExitEventsInRange($start, $end);
        $pending = SampleHeader::query()
            ->where('isactive', 1)
            ->where('status', self::LAB_STATUS)
            ->count();

        $dataEntry = $this->summarizeDataEntryForHeaders(
            SampleHeader::query()
                ->where('isactive', 1)
                ->where('status', self::LAB_STATUS)
                ->pluck('id')
        );

        $review = $this->summarizeReviewStatus(
            SampleHeader::query()
                ->where('isactive', 1)
                ->whereIn('status', [self::LAB_STATUS, 'Sample Verification', 'Sample Approval'])
                ->pluck('id')
        );

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'jobs_received' => $received->unique('sample_header_id')->count(),
            'jobs_completed' => $completed->unique('sample_header_id')->count(),
            'jobs_pending' => $pending,
            'data_entry_complete' => $dataEntry['complete'],
            'data_entry_partial' => $dataEntry['partial'],
            'data_entry_not_started' => $dataEntry['not_started'],
            'review_pending' => $review['pending'],
            'review_approved' => $review['approved'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getLaboratoryDailyRows(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $receivedByDate = $this->labEntryEventsInRange($start, $end)
            ->groupBy(fn (ChainOfCustody $c): string => $c->created_at->toDateString());

        $completedByDate = $this->labExitEventsInRange($start, $end)
            ->groupBy(fn (ChainOfCustody $c): string => $c->created_at->toDateString());

        $rows = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $receivedCount = $receivedByDate->get($dateKey, collect())->unique('sample_header_id')->count();
            $completedCount = $completedByDate->get($dateKey, collect())->unique('sample_header_id')->count();

            $rows[] = [
                'date' => $dateKey,
                'jobs_received' => $receivedCount,
                'jobs_completed' => $completedCount,
                'jobs_pending' => SampleHeader::query()
                    ->where('isactive', 1)
                    ->where('status', self::LAB_STATUS)
                    ->whereDate('updated_at', '<=', $dateKey)
                    ->count(),
                'data_entry_complete' => 0,
                'data_entry_partial' => 0,
                'data_entry_not_started' => 0,
            ];

            $cursor->addDay();
        }

        $period = $this->getLaboratoryPeriodMetrics($startDate, $endDate);
        $rows[] = [
            'date' => 'TOTAL',
            'jobs_received' => $period['jobs_received'],
            'jobs_completed' => $period['jobs_completed'],
            'jobs_pending' => $period['jobs_pending'],
            'data_entry_complete' => $period['data_entry_complete'],
            'data_entry_partial' => $period['data_entry_partial'],
            'data_entry_not_started' => $period['data_entry_not_started'],
        ];

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getLaboratoryDetailRows(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $headerIdsInRange = $this->labEntryEventsInRange($start, $end)
            ->pluck('sample_header_id')
            ->filter()
            ->unique();

        $samples = SamplesCategory::query()
            ->where('workflow_stage', self::LAB_STATUS)
            ->where(function ($query) use ($start, $end, $headerIdsInRange): void {
                if ($headerIdsInRange->isNotEmpty()) {
                    $query->whereIn('sample_header_id', $headerIdsInRange);
                }
                $query->orWhereBetween('receipt_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('date_collected', [$start->toDateString(), $end->toDateString()]);
            })
            ->get();

        if ($samples->isEmpty()) {
            $samples = SamplesCategory::query()
                ->where('workflow_stage', self::LAB_STATUS)
                ->get();
        }

        if ($samples->isEmpty()) {
            return [];
        }

        $headerIds = $samples->pluck('sample_header_id')->filter()->unique()->values();
        $sampleDetailIds = $samples->pluck('id')->filter()->unique()->values();

        $headers = SampleHeader::query()->whereIn('id', $headerIds)->get()->keyBy('id');
        $labEntryDates = $this->labEntryDatesByHeader($headerIds);
        $dataEntryByHeader = $this->dataEntryStatusByHeader($headerIds);
        $reviewByHeader = $this->reviewStatusByHeader($headerIds);
        $analysisNames = $this->analysisTypeNamesById();

        $batchJobCounts = $this->batchJobCountsForLab();

        return $samples->map(function ($row) use (
            $headers,
            $labEntryDates,
            $dataEntryByHeader,
            $reviewByHeader,
            $analysisNames,
            $batchJobCounts
        ): array {
            $header = $headers->get($row->sample_header_id);
            $batchCode = $row->batch_code ?? '—';
            $jobCounts = $batchJobCounts->get($batchCode, [
                'received' => 0,
                'completed' => 0,
                'pending' => 0,
            ]);

            $analysisLabel = $this->resolveAnalysisTypeLabel($row->analysis_type_id ?? null, $analysisNames);

            return [
                'date' => $labEntryDates->get($row->sample_header_id) ?? ($row->receipt_date ?? '—'),
                'client' => $row->crm_name ?? '—',
                'sample_details' => trim(($row->sample_code ?? '—').' / '.($row->sample_type_name ?? '—').' / '.$analysisLabel),
                'jobs_received_completed_pending' => sprintf(
                    'Job %s — received: %d, completed: %d, pending: %d',
                    $batchCode,
                    $jobCounts['received'],
                    $jobCounts['completed'],
                    $jobCounts['pending']
                ),
                'data_entry_status' => $dataEntryByHeader->get($row->sample_header_id, '—'),
                'review_approval_status' => $reviewByHeader->get($row->sample_header_id, $row->workflow_stage ?? '—'),
                'final_reports' => $this->formatFinalReports($header),
            ];
        })->values()->all();
    }

    /**
     * @return Collection<int, SubmissionFormInstance>
     */
    private function scheduledFormsInRange(Carbon $start, Carbon $end): Collection
    {
        return SubmissionFormInstance::query()
            ->whereHas('submissionForm', fn ($q) => $q->where('form_type', 'template'))
            ->whereIn('status', ['submitted', 'Submitted', 'pending_reception'])
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('submitted_at', [$start, $end])
                    ->orWhereBetween('created_at', [$start, $end]);
            })
            ->get();
    }

    /**
     * @return Collection<int, SampleHeader>
     */
    private function collectedBatchesInRange(Carbon $start, Carbon $end): Collection
    {
        return SampleHeader::query()
            ->where('isactive', 1)
            ->whereNotNull('receipt_date')
            ->whereBetween('receipt_date', [$start->toDateString(), $end->toDateString()])
            ->get();
    }

    /**
     * @return Collection<int, ChainOfCustody>
     */
    private function labEntryEventsInRange(Carbon $start, Carbon $end): Collection
    {
        if (! Schema::hasColumn('chain_of_custodies', 'workflow_stage')) {
            return SampleHeader::query()
                ->where('isactive', 1)
                ->where('status', self::LAB_STATUS)
                ->whereBetween('updated_at', [$start, $end])
                ->get()
                ->map(function (SampleHeader $header): ChainOfCustody {
                    $custody = new ChainOfCustody;
                    $custody->sample_header_id = $header->id;
                    $custody->created_at = $header->updated_at;

                    return $custody;
                });
        }

        return ChainOfCustody::query()
            ->where('workflow_stage', self::LAB_STATUS)
            ->whereBetween('created_at', [$start, $end])
            ->get();
    }

    /**
     * @return Collection<int, ChainOfCustody>
     */
    private function labExitEventsInRange(Carbon $start, Carbon $end): Collection
    {
        if (! Schema::hasColumn('chain_of_custodies', 'workflow_stage')) {
            return collect();
        }

        return ChainOfCustody::query()
            ->whereIn('workflow_stage', ['Sample Verification', 'Sample Approval'])
            ->whereBetween('created_at', [$start, $end])
            ->get();
    }

    /**
     * @param  Collection<int, string>  $headerIds
     * @return Collection<string, string>
     */
    private function labEntryDatesByHeader(Collection $headerIds): Collection
    {
        if ($headerIds->isEmpty()) {
            return collect();
        }

        if (! Schema::hasColumn('chain_of_custodies', 'workflow_stage')) {
            return SampleHeader::query()
                ->whereIn('id', $headerIds)
                ->pluck('receipt_date', 'id');
        }

        return ChainOfCustody::query()
            ->whereIn('sample_header_id', $headerIds)
            ->where('workflow_stage', self::LAB_STATUS)
            ->orderBy('created_at')
            ->get()
            ->groupBy('sample_header_id')
            ->map(fn (Collection $rows): string => $rows->first()->created_at->toDateString());
    }

    /**
     * @param  Collection<int, string>  $sampleDetailIds
     * @return Collection<string, Collection<int, CapturedResult>>
     */
    private function capturedResultsGroupedBySampleDetail(Collection $sampleDetailIds): Collection
    {
        if ($sampleDetailIds->isEmpty()) {
            return collect();
        }

        return CapturedResult::query()
            ->whereIn('sample_detail_id', $sampleDetailIds)
            ->get()
            ->groupBy(fn (CapturedResult $result): string => (string) $result->sample_detail_id);
    }

    /**
     * @param  Collection<int, string>  $sampleDetailIds
     * @return Collection<string, string>
     */
    private function equipmentIdsBySampleDetail(Collection $sampleDetailIds): Collection
    {
        if ($sampleDetailIds->isEmpty()) {
            return collect();
        }

        return CapturedResult::query()
            ->whereIn('sample_detail_id', $sampleDetailIds)
            ->whereNotNull('equipment_id')
            ->select('sample_detail_id', 'equipment_id')
            ->distinct()
            ->get()
            ->groupBy(fn ($row): string => (string) $row->sample_detail_id)
            ->map(fn (Collection $rows): string => $rows->pluck('equipment_id')->filter()->unique()->implode(', '));
    }

    /**
     * @param  Collection<int, string>  $headerIds
     * @return array{complete: int, partial: int, not_started: int}
     */
    private function summarizeDataEntryForHeaders(Collection $headerIds): array
    {
        $summary = ['complete' => 0, 'partial' => 0, 'not_started' => 0];

        foreach ($headerIds as $headerId) {
            $status = $this->dataEntryStatusForHeader((string) $headerId);
            if (str_starts_with($status, 'Complete')) {
                $summary['complete']++;
            } elseif (str_starts_with($status, 'Partial')) {
                $summary['partial']++;
            } else {
                $summary['not_started']++;
            }
        }

        return $summary;
    }

    /**
     * @param  Collection<int, string>  $headerIds
     * @return Collection<string, string>
     */
    private function dataEntryStatusByHeader(Collection $headerIds): Collection
    {
        return $headerIds->mapWithKeys(fn (string $id): array => [
            $id => $this->dataEntryStatusForHeader($id),
        ]);
    }

    private function dataEntryStatusForHeader(string $headerId): string
    {
        $total = CapturedResult::query()->where('sample_header_id', $headerId)->count();
        if ($total === 0) {
            return 'Not started';
        }

        $captured = CapturedResult::query()
            ->where('sample_header_id', $headerId)
            ->whereNotNull('result')
            ->where('result', '!=', '')
            ->count();

        if ($captured >= $total) {
            return "Complete ({$captured}/{$total})";
        }

        if ($captured > 0) {
            return "Partial ({$captured}/{$total})";
        }

        return "Not started (0/{$total})";
    }

    /**
     * @param  Collection<int, string>  $headerIds
     * @return array{pending: int, approved: int}
     */
    private function summarizeReviewStatus(Collection $headerIds): array
    {
        $pending = 0;
        $approved = 0;

        foreach ($headerIds as $headerId) {
            $status = $this->reviewStatusForHeader((string) $headerId);
            if (str_contains(strtolower($status), 'pending') || str_contains(strtolower($status), 'verification')) {
                $pending++;
            } elseif (str_contains(strtolower($status), 'approved') || str_contains(strtolower($status), 'approval complete')) {
                $approved++;
            }
        }

        return ['pending' => $pending, 'approved' => $approved];
    }

    /**
     * @param  Collection<int, string>  $headerIds
     * @return Collection<string, string>
     */
    private function reviewStatusByHeader(Collection $headerIds): Collection
    {
        return $headerIds->mapWithKeys(fn (string $id): array => [
            $id => $this->reviewStatusForHeader($id),
        ]);
    }

    private function reviewStatusForHeader(string $headerId): string
    {
        $header = SampleHeader::query()->find($headerId);
        if ($header === null) {
            return '—';
        }

        $verificationPending = BatchLabSectionApprover::query()
            ->where('batch_id', $headerId)
            ->where('batch_status', 'Sample Verification')
            ->where(function ($q): void {
                $q->where('status', false)->orWhereNull('status');
            })
            ->count();

        $approvalPending = BatchLabSectionApprover::query()
            ->where('batch_id', $headerId)
            ->where('batch_status', 'Sample Approval')
            ->where(function ($q): void {
                $q->where('status', false)->orWhereNull('status');
            })
            ->count();

        if ($verificationPending > 0) {
            return "Verification pending ({$verificationPending})";
        }

        if ($approvalPending > 0) {
            return "Approval pending ({$approvalPending})";
        }

        return $header->status ?? '—';
    }

    /**
     * @return Collection<string, array{received: int, completed: int, pending: int}>
     */
    private function batchJobCountsForLab(): Collection
    {
        $received = SampleHeader::query()
            ->where('isactive', 1)
            ->select('batch_code', DB::raw('count(*) as cnt'))
            ->groupBy('batch_code')
            ->pluck('cnt', 'batch_code');

        $inLab = SampleHeader::query()
            ->where('isactive', 1)
            ->where('status', self::LAB_STATUS)
            ->select('batch_code', DB::raw('count(*) as cnt'))
            ->groupBy('batch_code')
            ->pluck('cnt', 'batch_code');

        $completed = SampleHeader::query()
            ->where('isactive', 1)
            ->whereIn('status', ['Sample Verification', 'Sample Approval', 'Completed Sample'])
            ->select('batch_code', DB::raw('count(*) as cnt'))
            ->groupBy('batch_code')
            ->pluck('cnt', 'batch_code');

        $codes = $received->keys()->merge($inLab->keys())->merge($completed->keys())->unique();

        return $codes->mapWithKeys(fn (string $code): array => [
            $code => [
                'received' => (int) ($received[$code] ?? 0),
                'completed' => (int) ($completed[$code] ?? 0),
                'pending' => (int) ($inLab[$code] ?? 0),
            ],
        ]);
    }

    /**
     * @return Collection<string, string>
     */
    private function analysisTypeNamesById(): Collection
    {
        return AnalysisType::query()->pluck('name', 'id');
    }

    private function resolveAnalysisTypeLabel(?string $analysisTypeId, Collection $names): string
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return '—';
        }

        $ids = array_filter(array_map('trim', explode(',', $analysisTypeId)));

        $labels = collect($ids)
            ->map(fn (string $id): string => (string) ($names[$id] ?? $id))
            ->filter()
            ->values();

        return $labels->isNotEmpty() ? $labels->implode(', ') : '—';
    }

    private function resolveScheduledCollectedLabel(?SampleHeader $header, object $row): string
    {
        $count = $row->no_of_samples ?? 1;

        if (! empty($row->receipt_date)) {
            return "Collected ({$count})";
        }

        if ($header !== null && in_array($header->status, self::REGISTRATION_STATUSES, true)) {
            return "Scheduled ({$count})";
        }

        return (string) $count;
    }

    private function formatJobSampleId(object $row): string
    {
        $parts = array_filter([
            $row->batch_code ?? null,
            $row->sample_code ?? null,
            $row->reference_number ?? null,
        ]);

        return $parts !== [] ? implode(' / ', $parts) : '—';
    }

    private function formatFinalReports(?SampleHeader $header): string
    {
        if ($header === null) {
            return '—';
        }

        $parts = array_filter([
            $header->batch_report_url ? 'Report URL' : null,
            $header->batch_report_online_url ? 'Online URL' : null,
            isset($header->report_status) ? 'Status: '.$header->report_status : null,
            isset($header->prelim_report_status) ? 'Prelim: '.$header->prelim_report_status : null,
        ]);

        return $parts !== [] ? implode('; ', $parts) : '—';
    }

    private function calculateRate(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function formatRate(int $numerator, int $denominator): float|string
    {
        if ($denominator === 0) {
            return '—';
        }

        return $this->calculateRate($numerator, $denominator);
    }
}
