<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class DashboardDTO
{
    use ArrayableDto;

    /**
     * @param  list<RecentSubmissionDTO>  $recentSubmissions
     * @param  list<RecentReportDTO>  $recentReports
     * @param  list<NotificationDTO>  $notifications
     * @param  list<ComplaintDTO>  $recentComplaints
     * @param  list<FeedbackDTO>  $recentFeedback
     */
    public function __construct(
        public readonly string $customerId,
        public readonly DashboardSummaryDTO $summary,
        public readonly array $recentSubmissions,
        public readonly array $recentReports,
        public readonly array $notifications,
        public readonly array $recentComplaints,
        public readonly array $recentFeedback,
    ) {}
}
