<?php

namespace App\Services\Billing;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class QuotationStatisticsService
{
    /**
     * @return array<string, int|float|string>
     */
    public function getOverviewMetrics(?Carbon $asOf = null): array
    {
        $asOf ??= now();
        $monthStart = $asOf->copy()->startOfMonth();
        $monthEnd = $asOf->copy()->endOfMonth();
        $expiryCutoff = $asOf->copy()->addDays(7)->toDateString();

        $active = QuotationHeader::query()->where('is_draft', 0);

        $inPreparation = (clone $active)->where('status', 'Quote In Preparation')->count();
        $finalised = (clone $active)->where('status', 'Quote Complete')->count();
        $inApproval = (clone $active)->where('status', 'Quote In Approval')->count();
        $drafts = QuotationHeader::query()->where('is_draft', 1)->count();
        $totalActive = (clone $active)->count();

        $analysis = (clone $active)->where('quotation_type', 'Analysis')->count();
        $general = (clone $active)->where('quotation_type', 'General')->count();

        $pdfGenerated = (clone $active)->where('is_print', 1)->count();
        $sentToCustomer = $this->countSentToCustomer();
        $fromEnquiry = $this->countFromEnquiry();

        $totalValue = (float) ((clone $active)
            ->where('status', 'Quote Complete')
            ->sum('total_amount') ?? 0);

        $thisMonth = (clone $active)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $expiringSoon = (clone $active)
            ->where('status', '!=', 'Quote Complete')
            ->whereNotNull('expiring_date')
            ->whereDate('expiring_date', '<=', $expiryCutoff)
            ->whereDate('expiring_date', '>=', $asOf->toDateString())
            ->count();

        return [
            'total' => $totalActive,
            'drafts' => $drafts,
            'in_preparation' => $inPreparation,
            'finalised' => $finalised,
            'in_approval' => $inApproval,
            'from_enquiry' => $fromEnquiry,
            'sent_to_customer' => $sentToCustomer,
            'pdf_generated' => $pdfGenerated,
            'total_value' => $totalValue,
            'total_value_formatted' => number_format($totalValue, 2),
            'this_month' => $thisMonth,
            'expiring_soon' => $expiringSoon,
            'analysis' => $analysis,
            'general' => $general,
            'as_of' => $asOf->format('l, F j, Y'),
        ];
    }

    private function countFromEnquiry(): int
    {
        if (! Schema::hasColumn('quotation_headers', 'from_enquiry')) {
            return 0;
        }

        return QuotationHeader::query()
            ->where('is_draft', 0)
            ->where('from_enquiry', true)
            ->count();
    }

    private function countSentToCustomer(): int
    {
        $query = QuotationHeader::query()->where('is_draft', 0);

        if (Schema::hasColumn('quotation_headers', 'sent_to_customer_at')) {
            return $query->where(function ($builder): void {
                $builder->whereNotNull('sent_to_customer_at')
                    ->orWhereNotNull('email_to_customer');
            })->count();
        }

        return $query->whereNotNull('email_to_customer')->count();
    }

    /**
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     quotations_sent: int,
     *     quotations_accepted: int,
     *     success_rate_percent: float,
     *     total_quotation_value: float,
     *     accepted_value: float,
     *     total_quotation_value_formatted: string,
     *     accepted_value_formatted: string
     * }
     */
    public function getKpiPeriodMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $sentHeaders = $this->sentHeadersInRange($start, $end);
        $acceptedRequests = $this->acceptedRequestsInRange($start, $end);

        $sentCount = $sentHeaders->count();
        $acceptedCount = $acceptedRequests->count();
        $totalValue = (float) $sentHeaders->sum('total_amount');
        $acceptedValue = $this->sumAcceptedQuotationValue($acceptedRequests);

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'quotations_sent' => $sentCount,
            'quotations_accepted' => $acceptedCount,
            'success_rate_percent' => $this->calculateSuccessRate($acceptedCount, $sentCount),
            'total_quotation_value' => $totalValue,
            'accepted_value' => $acceptedValue,
            'total_quotation_value_formatted' => number_format($totalValue, 2),
            'accepted_value_formatted' => number_format($acceptedValue, 2),
        ];
    }

    /**
     * Daily KPI rows for Excel export (one row per day in range).
     *
     * @return list<array{
     *     date: string,
     *     quotations_sent: int,
     *     quotations_accepted: int,
     *     success_rate_percent: float|string,
     *     total_quotation_value: float,
     *     accepted_value: float
     * }>
     */
    public function getKpiDailyRows(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();
        $sentHeaders = $this->sentHeadersInRange($start, $end);
        $acceptedRequests = $this->acceptedRequestsInRange($start, $end);

        $sentByDate = $sentHeaders->groupBy(fn (QuotationHeader $header): string => $this->resolveSentDate($header));
        $acceptedByDate = $acceptedRequests->groupBy(
            fn (SampleSubmissionRequest $request): string => $request->quotation_accepted_at?->toDateString() ?? ''
        );

        $rows = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            /** @var Collection<int, QuotationHeader> $daySent */
            $daySent = $sentByDate->get($dateKey, collect());
            /** @var Collection<int, SampleSubmissionRequest> $dayAccepted */
            $dayAccepted = $acceptedByDate->get($dateKey, collect());

            $sentCount = $daySent->count();
            $acceptedCount = $dayAccepted->count();
            $totalValue = (float) $daySent->sum('total_amount');
            $acceptedValue = $this->sumAcceptedQuotationValue($dayAccepted);

            $rows[] = [
                'date' => $dateKey,
                'quotations_sent' => $sentCount,
                'quotations_accepted' => $acceptedCount,
                'success_rate_percent' => $this->formatSuccessRate($acceptedCount, $sentCount),
                'total_quotation_value' => round($totalValue, 2),
                'accepted_value' => round($acceptedValue, 2),
            ];

            $cursor->addDay();
        }

        $period = $this->getKpiPeriodMetrics($startDate, $endDate);
        $rows[] = [
            'date' => 'TOTAL',
            'quotations_sent' => $period['quotations_sent'],
            'quotations_accepted' => $period['quotations_accepted'],
            'success_rate_percent' => $this->formatSuccessRate(
                $period['quotations_accepted'],
                $period['quotations_sent']
            ),
            'total_quotation_value' => round($period['total_quotation_value'], 2),
            'accepted_value' => round($period['accepted_value'], 2),
        ];

        return $rows;
    }

    /**
     * @return Collection<int, QuotationHeader>
     */
    private function sentHeadersInRange(Carbon $start, Carbon $end): Collection
    {
        $query = QuotationHeader::query()->where('is_draft', 0);

        if (Schema::hasColumn('quotation_headers', 'sent_to_customer_at')) {
            $query->where(function ($builder) use ($start, $end): void {
                $builder->whereBetween('sent_to_customer_at', [$start, $end])
                    ->orWhere(function ($fallback) use ($start, $end): void {
                        $fallback->whereNull('sent_to_customer_at')
                            ->whereNotNull('email_to_customer')
                            ->whereBetween('email_to_customer', [$start->toDateString(), $end->toDateString()]);
                    });
            });
        } else {
            $query->whereNotNull('email_to_customer')
                ->whereBetween('email_to_customer', [$start->toDateString(), $end->toDateString()]);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, SampleSubmissionRequest>
     */
    private function acceptedRequestsInRange(Carbon $start, Carbon $end): Collection
    {
        if (! Schema::hasTable('sample_submission_requests')
            || ! Schema::hasColumn('sample_submission_requests', 'quotation_accepted_at')) {
            return collect();
        }

        return SampleSubmissionRequest::query()
            ->whereNotNull('quotation_accepted_at')
            ->whereBetween('quotation_accepted_at', [$start, $end])
            ->get();
    }

    /**
     * @param  Collection<int, SampleSubmissionRequest>  $acceptedRequests
     */
    private function sumAcceptedQuotationValue(Collection $acceptedRequests): float
    {
        $quotationIds = $acceptedRequests
            ->pluck('accepted_quotation_header_id')
            ->filter()
            ->unique()
            ->values();

        if ($quotationIds->isEmpty()) {
            return 0.0;
        }

        return (float) QuotationHeader::query()
            ->whereIn('id', $quotationIds)
            ->sum('total_amount');
    }

    private function resolveSentDate(QuotationHeader $header): string
    {
        if ($header->sent_to_customer_at !== null) {
            return $header->sent_to_customer_at->toDateString();
        }

        if (! empty($header->email_to_customer)) {
            return Carbon::parse($header->email_to_customer)->toDateString();
        }

        return $header->created_at?->toDateString() ?? now()->toDateString();
    }

    private function calculateSuccessRate(int $accepted, int $sent): float
    {
        if ($sent === 0) {
            return 0.0;
        }

        return round(($accepted / $sent) * 100, 2);
    }

    private function formatSuccessRate(int $accepted, int $sent): float|string
    {
        if ($sent === 0) {
            return '—';
        }

        return $this->calculateSuccessRate($accepted, $sent);
    }
}
