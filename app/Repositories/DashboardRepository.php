<?php

namespace App\Repositories;

use App\DTOs\Dashboard\AnalyticsDTO;
use App\DTOs\Dashboard\ComplaintDTO;
use App\DTOs\Dashboard\DashboardDTO;
use App\DTOs\Dashboard\DashboardSummaryDTO;
use App\DTOs\Dashboard\FeedbackDTO;
use App\DTOs\Dashboard\FeedbackSummaryDTO;
use App\DTOs\Dashboard\InvoiceSummaryDTO;
use App\DTOs\Dashboard\NotificationDTO;
use App\DTOs\Dashboard\RecentReportDTO;
use App\DTOs\Dashboard\ReportLanguageDownloadDTO;
use App\DTOs\Dashboard\RecentSubmissionDTO;
use App\DTOs\Dashboard\SubmissionSummaryDTO;
use App\Invoice;
use App\Models\CRM\Complaint;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\CustomerNotification;
use App\Models\TestRequestReportLanguageFile;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardRepository
{
    public function __construct(
        private readonly PortalSubmissionFormAccess $portalAccess,
    ) {}

    public function buildDashboard(string $customerId): DashboardDTO
    {
        $limit = (int) config('dashboard.recent_items_limit', 5);

        $summary = $this->buildSummary($customerId);

        return new DashboardDTO(
            customerId: $customerId,
            summary: $summary,
            recentSubmissions: $this->fetchRecentSubmissions($customerId, $limit),
            recentReports: $this->fetchRecentReports($customerId, $limit),
            notifications: $this->fetchRecentNotifications($customerId, $limit),
            recentComplaints: $this->fetchRecentComplaints($customerId, $limit),
            recentFeedback: $this->fetchRecentFeedback($customerId, $limit),
        );
    }

    public function buildSummary(string $customerId): DashboardSummaryDTO
    {
        $submissions = $this->aggregateSubmissionSummary($customerId);
        $invoices = $this->aggregateInvoiceSummary($customerId);
        $feedback = $this->aggregateFeedbackSummary($customerId);

        return new DashboardSummaryDTO(
            submissions: $submissions,
            invoices: $invoices,
            feedback: $feedback,
            counts: [
                'notifications_unread' => $this->countUnreadNotifications($customerId),
                'open_complaints' => $this->countOpenComplaints($customerId),
            ],
        );
    }

    public function buildAnalytics(string $customerId): AnalyticsDTO
    {
        return new AnalyticsDTO(
            customerId: $customerId,
            monthlySubmissionTrends: $this->monthlySubmissionTrends($customerId),
            submissionStatusDistribution: $this->submissionStatusDistribution($customerId),
            invoiceTrends: $this->invoiceTrends($customerId),
            requestTypeAnalytics: $this->requestTypeAnalytics($customerId),
            workflowTurnaround: $this->workflowTurnaroundInsights($customerId),
            complaintInsights: $this->complaintInsights($customerId),
            feedbackAnalytics: $this->feedbackAnalyticsTrend($customerId),
        );
    }

    public function paginateNotifications(string $customerId, int $perPage): LengthAwarePaginator
    {
        return CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn (CustomerNotification $row) => $this->mapNotification($row));
    }

    public function paginateReports(string $customerId, int $perPage): LengthAwarePaginator
    {
        $reportStatus = (string) config('dashboard.report_status', 'Completed');
        $base = SampleHeader::query()->where('crm_customer_id', $customerId);
        $totalByCustomer = (clone $base)->count();
        $completedByCustomer = (clone $base)->where('status', $reportStatus)->count();
        $withUrlByCustomer = (clone $base)->where(function (Builder $query): void {
            $query->whereNotNull('batch_report_url')
                ->orWhereNotNull('batch_report_online_url');
        })->count();
        $eligible = (clone $base)
            ->where('status', $reportStatus)
            ->where(function (Builder $query): void {
                $query->whereNotNull('batch_report_url')
                    ->orWhereNotNull('batch_report_online_url');
            })->count();

        Log::info('portal.dashboard.reports.query', [
            'customer_id' => $customerId,
            'report_status' => $reportStatus,
            'total_by_customer' => $totalByCustomer,
            'completed_by_customer' => $completedByCustomer,
            'with_report_url_by_customer' => $withUrlByCustomer,
            'eligible_reports' => $eligible,
            'per_page' => $perPage,
        ]);

        return $this->releasedReportsQuery($customerId)
            ->paginate($perPage)
            ->through(fn (SampleHeader $header) => $this->mapReport($header));
    }

    public function paginateComplaints(string $customerId, int $perPage): LengthAwarePaginator
    {
        return Complaint::query()
            ->where('client_id', $customerId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn (Complaint $complaint) => $this->mapComplaint($complaint));
    }

    private function portalInstancesBase(string $customerId): Builder
    {
        return $this->portalAccess->portalInstancesQuery($customerId);
    }

    private function aggregateSubmissionSummary(string $customerId): SubmissionSummaryDTO
    {
        $buckets = config('dashboard.submission_status_buckets', []);
        $rows = $this->portalInstancesBase($customerId)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $rows->sum();
        $awaiting = $this->sumStatuses($rows, $buckets['awaiting_samples'] ?? []);
        $inLab = $this->sumStatuses($rows, $buckets['in_lab_workflow'] ?? []);
        $moreInfo = $this->sumStatuses($rows, $buckets['more_info_requested'] ?? []);
        $completed = $this->sumStatuses($rows, $buckets['completed'] ?? []);

        $completed += (int) SampleHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('status', config('dashboard.report_status', 'Completed'))
            ->count();

        return new SubmissionSummaryDTO(
            totalSubmissions: $total,
            awaitingSamples: $awaiting,
            inLabWorkflow: $inLab,
            moreInfoRequested: $moreInfo,
            completed: $completed,
        );
    }

    /**
     * @param  Collection<string, int|string>  $rows
     * @param  list<string>  $statuses
     */
    private function sumStatuses(Collection $rows, array $statuses): int
    {
        $sum = 0;

        foreach ($statuses as $status) {
            $sum += (int) ($rows[$status] ?? 0);
        }

        return $sum;
    }

    private function aggregateInvoiceSummary(string $customerId): InvoiceSummaryDTO
    {
        $invoices = Invoice::query()
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->get(['id', 'total', 'total_tax']);

        $totalInvoices = $invoices->count();

        if ($totalInvoices === 0) {
            return new InvoiceSummaryDTO(0, 0, 0, 0, 0.0, 0.0, 0.0);
        }

        $invoiceIds = $invoices->pluck('id')->all();
        $paymentsByInvoice = DB::table('invoice_payment_details')
            ->select('invoice_id', DB::raw('SUM(CAST(NULLIF(amount, \'\') AS DECIMAL(18,2))) as paid'))
            ->whereIn('invoice_id', $invoiceIds)
            ->where('is_delete', false)
            ->groupBy('invoice_id')
            ->pluck('paid', 'invoice_id');

        $fullyPaid = 0;
        $unpaid = 0;
        $partial = 0;
        $totalInvoiced = 0.0;
        $totalPaid = 0.0;

        foreach ($invoices as $invoice) {
            $invoiceTotal = round((float) $invoice->total + (float) ($invoice->total_tax ?? 0), 2);
            $paid = round((float) ($paymentsByInvoice[$invoice->id] ?? 0), 2);
            $totalInvoiced += $invoiceTotal;
            $totalPaid += $paid;

            if ($invoiceTotal <= 0) {
                $fullyPaid++;

                continue;
            }

            if ($paid <= 0) {
                $unpaid++;
            } elseif ($paid >= $invoiceTotal) {
                $fullyPaid++;
            } else {
                $partial++;
            }
        }

        return new InvoiceSummaryDTO(
            totalInvoices: $totalInvoices,
            fullyPaidInvoices: $fullyPaid,
            unpaidInvoices: $unpaid,
            partiallyPaidInvoices: $partial,
            totalInvoicedAmount: round($totalInvoiced, 2),
            totalPaidAmount: round($totalPaid, 2),
            outstandingBalance: round(max($totalInvoiced - $totalPaid, 0), 2),
        );
    }

    private function aggregateFeedbackSummary(string $customerId): FeedbackSummaryDTO
    {
        $threshold = (int) config('dashboard.positive_feedback_rating_threshold', 3);

        $row = CustomerFeedback::query()
            ->where('customer_id', $customerId)
            ->where('is_submitted', true)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(rating_overall) as average_rating')
            ->selectRaw(
                'SUM(CASE WHEN rating_overall >= ? THEN 1 ELSE 0 END) as positive_count',
                [$threshold]
            )
            ->first();

        $total = (int) ($row->total ?? 0);
        $positive = (int) ($row->positive_count ?? 0);

        return new FeedbackSummaryDTO(
            totalFeedbackSubmitted: $total,
            averageRating: $row->average_rating !== null ? round((float) $row->average_rating, 2) : null,
            positiveFeedbackPercentage: $total > 0 ? round(($positive / $total) * 100, 1) : null,
        );
    }

    /**
     * @return list<RecentSubmissionDTO>
     */
    private function fetchRecentSubmissions(string $customerId, int $limit): array
    {
        return $this->portalInstancesBase($customerId)
            ->with(['submissionForm:id,name,form_type'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (SubmissionFormInstance $instance): RecentSubmissionDTO {
                $sampleStatus = null;

                if ($instance->target_record_type === SampleHeader::class && $instance->target_record_id) {
                    $sampleStatus = SampleHeader::query()
                        ->where('id', $instance->target_record_id)
                        ->value('status');
                }

                return new RecentSubmissionDTO(
                    requestNumber: (string) ($instance->form_number ?? $instance->id),
                    requestType: $instance->submissionForm?->form_type ?? $instance->submissionForm?->name,
                    submittedDate: $instance->submitted_at?->toIso8601String(),
                    workflowStage: $this->publicWorkflowStage($instance->status),
                    status: $this->publicStatusLabel($instance->status),
                    priority: $instance->priority,
                    sampleStatus: $sampleStatus ? $this->publicStatusLabel((string) $sampleStatus) : null,
                );
            })
            ->all();
    }

    /**
     * @return list<RecentReportDTO>
     */
    private function fetchRecentReports(string $customerId, int $limit): array
    {
        return $this->releasedReportsQuery($customerId)
            ->limit($limit)
            ->get()
            ->map(fn (SampleHeader $header) => $this->mapReport($header))
            ->all();
    }

    private function releasedReportsQuery(string $customerId): Builder
    {
        return SampleHeader::query()
            ->with(['sample_type:id,name'])
            ->where('crm_customer_id', $customerId)
            ->where('status', config('dashboard.report_status', 'Completed'))
            ->where(function (Builder $query): void {
                $query->whereNotNull('batch_report_url')
                    ->orWhereNotNull('batch_report_online_url');
            })
            ->orderByDesc('updated_at');
    }

    private function mapReport(SampleHeader $header): RecentReportDTO
    {
        $revisionNo = (int) ($header->test_request_report_sequence ?? 1);

        $languageFiles = TestRequestReportLanguageFile::query()
            ->where('batch_id', $header->id)
            ->where('revision_no', $revisionNo)
            ->orderBy('language')
            ->get();

        $availableLanguages = $languageFiles
            ->map(function (TestRequestReportLanguageFile $file): ReportLanguageDownloadDTO {
                $downloadUrl = $file->report_online_url
                    ?: ($file->report_url ? url('/storage'.$file->report_url) : null);

                return new ReportLanguageDownloadDTO(
                    code: $file->language,
                    label: $file->label(),
                    downloadUrl: $downloadUrl,
                );
            })
            ->values()
            ->all();

        $downloadUrl = $header->batch_report_online_url
            ?: ($header->batch_report_url ? url('/storage'.$header->batch_report_url) : null);

        if ($availableLanguages !== []) {
            $preferred = collect($availableLanguages)->first(fn (ReportLanguageDownloadDTO $lang) => $lang->code === 'en')
                ?? $availableLanguages[0];
            $downloadUrl = $preferred->downloadUrl ?? $downloadUrl;
        }

        return new RecentReportDTO(
            reportNumber: $header->document_number ?? $header->batch_code,
            submissionRequestNumber: $header->reference_number ?? $header->batch_code,
            releasedDate: $header->updated_at?->toIso8601String(),
            reportType: $header->relationLoaded('sample_type') ? $header->sample_type?->name : null,
            downloadUrl: $downloadUrl,
            releaseStatus: $this->publicStatusLabel((string) $header->status),
            availableLanguages: $availableLanguages,
        );
    }

    /**
     * @return list<NotificationDTO>
     */
    private function fetchRecentNotifications(string $customerId, int $limit): array
    {
        return CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (CustomerNotification $row) => $this->mapNotification($row))
            ->all();
    }

    private function mapNotification(CustomerNotification $notification): NotificationDTO
    {
        return new NotificationDTO(
            id: (string) $notification->id,
            title: (string) $notification->notification_type,
            message: (string) $notification->notification_description,
            severity: $this->notificationSeverity((string) $notification->notification_type),
            createdAt: $notification->created_at?->toIso8601String(),
            isRead: $notification->read_at !== null,
            actionUrl: $this->notificationActionUrl($notification),
        );
    }

    /**
     * @return list<ComplaintDTO>
     */
    private function fetchRecentComplaints(string $customerId, int $limit): array
    {
        return Complaint::query()
            ->where('client_id', $customerId)
            ->orderByDesc('date')
            ->limit($limit)
            ->get()
            ->map(fn (Complaint $complaint) => $this->mapComplaint($complaint))
            ->all();
    }

    private function mapComplaint(Complaint $complaint): ComplaintDTO
    {
        return new ComplaintDTO(
            id: (string) $complaint->id,
            complaintNumber: $complaint->complaint_id,
            title: $complaint->description,
            complaintType: $complaint->type,
            status: $complaint->is_closed ? 'closed' : 'open',
            createdAt: $complaint->date?->toIso8601String(),
            resolutionStatus: $complaint->is_closed ? 'resolved' : 'open',
        );
    }

    /**
     * @return list<FeedbackDTO>
     */
    private function fetchRecentFeedback(string $customerId, int $limit): array
    {
        return CustomerFeedback::query()
            ->where('customer_id', $customerId)
            ->where('is_submitted', true)
            ->orderByDesc('submitted_at')
            ->limit($limit)
            ->get(['id', 'rating_overall', 'specific_feedback', 'submitted_at'])
            ->map(fn (CustomerFeedback $feedback) => new FeedbackDTO(
                id: (string) $feedback->id,
                rating: $feedback->rating_overall !== null ? (float) $feedback->rating_overall : null,
                comment: $feedback->specific_feedback,
                submittedAt: $feedback->submitted_at?->toIso8601String(),
            ))
            ->all();
    }

    private function countUnreadNotifications(string $customerId): int
    {
        return CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->whereNull('read_at')
            ->count();
    }

    private function countOpenComplaints(string $customerId): int
    {
        return Complaint::query()
            ->where('client_id', $customerId)
            ->where('is_closed', false)
            ->count();
    }

    /**
     * @return list<array{month: string, counts: array<string, int>}>
     */
    private function monthExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    private function monthlySubmissionTrends(string $customerId): array
    {
        $monthSql = $this->monthExpression('COALESCE(submitted_at, created_at)');

        $rows = $this->portalInstancesBase($customerId)
            ->selectRaw("{$monthSql} as month")
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->whereNotNull('submitted_at')
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row->month]['month'] = $row->month;
            $grouped[$row->month]['counts'][$row->status] = (int) $row->aggregate;
        }

        return array_values($grouped);
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function submissionStatusDistribution(string $customerId): array
    {
        return $this->portalInstancesBase($customerId)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->orderByDesc('aggregate')
            ->get()
            ->map(fn ($row) => [
                'label' => $this->publicStatusLabel((string) $row->status),
                'value' => (int) $row->aggregate,
            ])
            ->all();
    }

    /**
     * @return list<array{month: string, invoices: float, payments: float, outstanding: float}>
     */
    private function invoiceTrends(string $customerId): array
    {
        $invoiceMonthSql = $this->monthExpression('created_at');

        $invoiceRows = Invoice::query()
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->selectRaw("{$invoiceMonthSql} as month")
            ->selectRaw('SUM(total + COALESCE(total_tax, 0)) as invoiced')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('invoiced', 'month');

        $paymentRows = DB::table('invoice_payment_details as ipd')
            ->join('customer_invoice as ci', 'ci.id', '=', 'ipd.invoice_id')
            ->where('ci.customer_id', $customerId)
            ->whereNull('ci.deleted_at')
            ->where('ipd.is_delete', false)
            ->selectRaw($this->monthExpression('ipd.created_at').' as month')
            ->selectRaw('SUM(CAST(NULLIF(ipd.amount, \'\') AS DECIMAL(18,2))) as paid')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('paid', 'month');

        $months = collect($invoiceRows->keys())->merge($paymentRows->keys())->unique()->sort()->values();

        return $months->map(function (string $month) use ($invoiceRows, $paymentRows): array {
            $invoiced = round((float) ($invoiceRows[$month] ?? 0), 2);
            $paid = round((float) ($paymentRows[$month] ?? 0), 2);

            return [
                'month' => $month,
                'invoices' => $invoiced,
                'payments' => $paid,
                'outstanding' => round(max($invoiced - $paid, 0), 2),
            ];
        })->all();
    }

    /**
     * @return list<array{request_type: string, count: int}>
     */
    private function requestTypeAnalytics(string $customerId): array
    {
        return $this->portalInstancesBase($customerId)
            ->join('submission_forms as sf', 'sf.id', '=', 'submission_form_instances.submission_form_id')
            ->selectRaw('COALESCE(sf.form_type, sf.name) as request_type')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('request_type')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'request_type' => (string) $row->request_type,
                'count' => (int) $row->aggregate,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowTurnaroundInsights(string $customerId): array
    {
        $row = $this->portalInstancesBase($customerId)
            ->whereNotNull('submitted_at')
            ->whereNotNull('reviewed_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (reviewed_at - submitted_at)) / 86400) as avg_days')
            ->first();

        $delayed = $this->portalInstancesBase($customerId)
            ->whereNotIn('status', ['approved', 'rejected', 'cancelled', 'completed', 'released'])
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $completedWithinSla = $this->portalInstancesBase($customerId)
            ->whereIn('status', ['approved', 'completed', 'released'])
            ->where(function (Builder $query): void {
                $query->whereNull('due_date')
                    ->orWhereColumn('reviewed_at', '<=', 'due_date');
            })
            ->count();

        $completedTotal = $this->portalInstancesBase($customerId)
            ->whereIn('status', ['approved', 'completed', 'released'])
            ->count();

        return [
            'average_processing_days' => $row?->avg_days !== null ? round((float) $row->avg_days, 1) : null,
            'delayed_requests' => $delayed,
            'completed_within_sla' => $completedWithinSla,
            'completed_total' => $completedTotal,
            'sla_compliance_rate' => $completedTotal > 0
                ? round(($completedWithinSla / $completedTotal) * 100, 1)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function complaintInsights(string $customerId): array
    {
        $byCategory = Complaint::query()
            ->where('client_id', $customerId)
            ->selectRaw('COALESCE(type, nature_of_complaint, \'Uncategorized\') as category')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('category')
            ->orderByDesc('aggregate')
            ->get()
            ->map(fn ($row) => [
                'category' => (string) $row->category,
                'count' => (int) $row->aggregate,
            ])
            ->all();

        $total = Complaint::query()->where('client_id', $customerId)->count();
        $resolved = Complaint::query()
            ->where('client_id', $customerId)
            ->where('is_closed', true)
            ->count();

        return [
            'by_category' => $byCategory,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : null,
        ];
    }

    /**
     * @return list<array{month: string, average_rating: ?float, satisfaction_score: ?float}>
     */
    private function feedbackAnalyticsTrend(string $customerId): array
    {
        return CustomerFeedback::query()
            ->where('customer_id', $customerId)
            ->where('is_submitted', true)
            ->whereNotNull('submitted_at')
            ->selectRaw($this->monthExpression('submitted_at').' as month')
            ->selectRaw('AVG(rating_overall) as average_rating')
            ->selectRaw("AVG(CASE WHEN will_recommend = 'Yes' THEN 100 ELSE 0 END) as satisfaction_score")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => (string) $row->month,
                'average_rating' => $row->average_rating !== null ? round((float) $row->average_rating, 2) : null,
                'satisfaction_score' => $row->satisfaction_score !== null ? round((float) $row->satisfaction_score, 1) : null,
            ])
            ->all();
    }

    private function publicStatusLabel(string $status): string
    {
        if (is_numeric($status)) {
            $workflow = getComplaintWorkflow();

            return $workflow[(int) $status] ?? 'Open Complaints';
        }

        return str($status)->replace('_', ' ')->title()->toString();
    }

    private function publicWorkflowStage(string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'submitted', 'received' => 'Intake',
            'in_additional_info' => 'Additional Information',
            'in_review', 'approved' => 'Laboratory Processing',
            'rejected', 'cancelled' => 'Closed',
            default => $this->publicStatusLabel($status),
        };
    }

    private function notificationSeverity(string $type): string
    {
        return match (true) {
            str_contains(strtolower($type), 'urgent'),
            str_contains(strtolower($type), 'overdue') => 'high',
            str_contains(strtolower($type), 'sign') => 'medium',
            default => 'info',
        };
    }

    private function notificationActionUrl(CustomerNotification $notification): ?string
    {
        return match ($notification->entity_type) {
            \App\Models\Sampleworkflow\AnalysisAcceptanceForm::class => '/portal/acceptance-forms/'.$notification->entity_id,
            default => null,
        };
    }
}
