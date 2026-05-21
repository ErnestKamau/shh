<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class DashboardSummaryDTO
{
    use ArrayableDto;

    /**
     * @param  array<string, int>  $counts
     */
    public function __construct(
        public readonly SubmissionSummaryDTO $submissions,
        public readonly InvoiceSummaryDTO $invoices,
        public readonly FeedbackSummaryDTO $feedback,
        public readonly array $counts = [],
    ) {}
}
