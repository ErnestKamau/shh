<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class AnalyticsDTO
{
    use ArrayableDto;

    /**
     * @param  list<array{month: string, counts: array<string, int>}>  $monthlySubmissionTrends
     * @param  list<array{label: string, value: int}>  $submissionStatusDistribution
     * @param  list<array{month: string, invoices: float, payments: float, outstanding: float}>  $invoiceTrends
     * @param  list<array{request_type: string, count: int}>  $requestTypeAnalytics
     * @param  array<string, mixed>  $workflowTurnaround
     * @param  array<string, mixed>  $complaintInsights
     * @param  list<array{month: string, average_rating: ?float, satisfaction_score: ?float}>  $feedbackAnalytics
     */
    public function __construct(
        public readonly string $customerId,
        public readonly array $monthlySubmissionTrends,
        public readonly array $submissionStatusDistribution,
        public readonly array $invoiceTrends,
        public readonly array $requestTypeAnalytics,
        public readonly array $workflowTurnaround,
        public readonly array $complaintInsights,
        public readonly array $feedbackAnalytics,
    ) {}
}
