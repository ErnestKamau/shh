<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

final class QuotationAcceptanceTatService
{
    public function stampFirstSentAt(SampleSubmissionRequest $enquiry, CarbonInterface $sentAt): void
    {
        if (! Schema::hasColumn('sample_submission_requests', 'quotation_first_sent_to_customer_at')) {
            return;
        }

        if ($enquiry->quotation_first_sent_to_customer_at !== null) {
            return;
        }

        $enquiry->quotation_first_sent_to_customer_at = $sentAt;
    }

    public function computeMinutes(SampleSubmissionRequest $enquiry, ?CarbonInterface $acceptedAt = null): int
    {
        $firstSentAt = $this->resolveFirstSentAt($enquiry);

        if ($firstSentAt === null) {
            return 0;
        }

        return $this->minutesBetween(
            Carbon::parse($firstSentAt),
            Carbon::parse($acceptedAt ?? now()),
        );
    }

    public function recalculateCustomerTat(string $crmCustomerId): ?int
    {
        if ($crmCustomerId === '') {
            return null;
        }

        $requests = DB::table('sample_submission_requests')
            ->where('crm_customer_id', $crmCustomerId)
            ->whereNotNull('quotation_accepted_at')
            ->get([
                'id',
                'quotation_first_sent_to_customer_at',
                'quotation_accepted_at',
                'current_quotation_header_id',
                'accepted_quotation_header_id',
            ]);

        $tatMinutes = [];

        foreach ($requests as $request) {
            $firstSentAt = $this->resolveFirstSentAtFromRow($request);

            if ($firstSentAt === null) {
                continue;
            }

            $minutes = $this->minutesBetween(
                Carbon::parse($firstSentAt),
                Carbon::parse($request->quotation_accepted_at),
            );

            if ($minutes > 0) {
                $tatMinutes[] = $minutes;
            }
        }

        $averageMinutes = $tatMinutes === []
            ? null
            : (int) round(array_sum($tatMinutes) / count($tatMinutes));

        if (! Schema::hasColumn('crm_customers', 'quotation_acceptance_tat_minutes')) {
            return $averageMinutes;
        }

        DB::table('crm_customers')
            ->where('id', $crmCustomerId)
            ->update([
                'quotation_acceptance_tat_minutes' => $averageMinutes,
                'updated_at' => now(),
            ]);

        return $averageMinutes;
    }

    private function resolveFirstSentAt(SampleSubmissionRequest $enquiry): ?CarbonInterface
    {
        return $this->resolveFirstSentAtFromRow((object) [
            'id' => (string) $enquiry->id,
            'quotation_first_sent_to_customer_at' => $enquiry->quotation_first_sent_to_customer_at,
            'current_quotation_header_id' => $enquiry->current_quotation_header_id,
            'accepted_quotation_header_id' => $enquiry->accepted_quotation_header_id,
        ]);
    }

    private function resolveFirstSentAtFromRow(stdClass $submissionRequest): ?CarbonInterface
    {
        if ($submissionRequest->quotation_first_sent_to_customer_at !== null) {
            return Carbon::parse($submissionRequest->quotation_first_sent_to_customer_at);
        }

        $earliestSentAt = DB::table('quotation_headers')
            ->where('sample_submission_request_id', (string) $submissionRequest->id)
            ->whereNotNull('sent_to_customer_at')
            ->min('sent_to_customer_at');

        if ($earliestSentAt !== null) {
            return Carbon::parse($earliestSentAt);
        }

        $quotationId = $submissionRequest->accepted_quotation_header_id
            ?? $submissionRequest->current_quotation_header_id;

        if ($quotationId === null) {
            return null;
        }

        $sentAt = DB::table('quotation_headers')
            ->where('id', (string) $quotationId)
            ->value('sent_to_customer_at');

        return $sentAt !== null ? Carbon::parse($sentAt) : null;
    }

    private function minutesBetween(CarbonInterface $start, CarbonInterface $end): int
    {
        $seconds = max(0, $start->diffInSeconds($end, false));

        if ($seconds === 0) {
            return 0;
        }

        return max(1, (int) round($seconds / 60));
    }
}
