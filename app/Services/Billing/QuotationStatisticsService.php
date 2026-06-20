<?php

namespace App\Services\Billing;

use App\QuotationHeader;
use Illuminate\Support\Carbon;
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
}
