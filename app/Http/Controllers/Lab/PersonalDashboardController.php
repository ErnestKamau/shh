<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\Services\Commercial\QuotationApprovalService;
use App\Services\Lab\PersonalDashboardHistoryService;
use App\Services\SubmissionForm\SubmissionFormIntrayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalDashboardController extends Controller
{
    public function __construct(
        private readonly SubmissionFormIntrayService $intrayService,
        private readonly QuotationApprovalService $quotationApprovalService,
        private readonly PersonalDashboardHistoryService $historyService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'history_tab' => ['nullable', 'in:quotations,assignments,verifications,approvals'],
        ]);

        $startDate = $request->filled('start_date')
            ? Carbon::parse((string) $request->input('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse((string) $request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $batchAssignmentQuery = SampleHeaderUserAssignment::query()
            ->pending()
            ->forAssignee((string) $user->id)
            ->with([
                'sampleHeader.client',
                'sampleHeader.get_target_date',
                'sampleHeader.activePendingUserAssignment.toUser',
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at');

        $batchAssignments = (clone $batchAssignmentQuery)->get();
        $batchAssignmentsPage = (clone $batchAssignmentQuery)->paginate(15)->withQueryString();

        $requestAssignments = $this->intrayService
            ->getPendingForUser((string) $user->id)
            ->filter(function ($assignment) use ($startDate, $endDate): bool {
                if ($assignment->created_at === null) {
                    return false;
                }

                return $assignment->created_at->gte($startDate) && $assignment->created_at->lte($endDate);
            })
            ->values();

        $quotationApprovals = $this->quotationApprovalService
            ->pendingApprovalsForUser((string) $user->id)
            ->filter(function ($header) use ($startDate, $endDate): bool {
                $stamp = $header->approval_requested_at ?? $header->updated_at;
                if ($stamp === null) {
                    return true;
                }

                return $stamp->gte($startDate) && $stamp->lte($endDate);
            })
            ->values()
            ->map(function ($header): array {
                $enquiry = $header->sampleSubmissionRequest;
                $openUrl = $enquiry?->staffViewUrl() ?? route('add-qoute-details-view', ['id' => $header->id]);
                if ($enquiry?->submissionFormInstance?->submissionForm) {
                    $openUrl .= (str_contains($openUrl, '?') ? '&' : '?').'tab=quotation_approvals';
                }

                return [
                    'quote_number' => (string) ($header->quote_number ?? '—'),
                    'customer' => (string) ($header->customer?->name ?? '—'),
                    'requested_at' => optional($header->approval_requested_at)->format('Y-m-d H:i') ?? '—',
                    'open_url' => $openUrl,
                ];
            });

        $historyTab = (string) $request->input('history_tab', 'quotations');
        $quotationApprovalHistory = $this->historyService->quotationApprovals((string) $user->id);
        $labAssignmentHistory = $this->historyService->labAssignments((string) $user->id);
        $sampleVerificationHistory = $this->historyService->sampleVerifications((string) $user->id);
        $sampleApprovalHistory = $this->historyService->sampleApprovals((string) $user->id);

        $assignedHeaders = $batchAssignments
            ->pluck('sampleHeader')
            ->filter()
            ->values();

        $statusCounts = [
            'assigned_total' => $assignedHeaders->count(),
            'in_lab' => $assignedHeaders->where('status', 'Samples In Lab')->count(),
            'verification' => $assignedHeaders->where('status', 'Sample Verification')->count(),
            'approval' => $assignedHeaders->where('status', 'Sample Approval')->count(),
            'complete' => $assignedHeaders->filter(function ($header): bool {
                return in_array((string) $header->status, ['Completed', 'Finished Sample', 'Completed Sample'], true);
            })->count(),
        ];

        $tatStats = [
            'met' => 0,
            'not_met' => 0,
        ];

        $completionRows = [];

        foreach ($assignedHeaders as $header) {
            $isComplete = in_array((string) $header->status, ['Completed', 'Finished Sample', 'Completed Sample'], true);
            if (! $isComplete) {
                continue;
            }

            $targetDate = $header->get_target_date?->date
                ? Carbon::parse((string) $header->get_target_date->date)->endOfDay()
                : null;

            $completionDate = $header->approval_date
                ? Carbon::parse((string) $header->approval_date)->endOfDay()
                : ($header->report_verified_date
                    ? Carbon::parse((string) $header->report_verified_date)->endOfDay()
                    : Carbon::parse((string) $header->updated_at)->endOfDay());

            if ($targetDate !== null) {
                if ($completionDate->lte($targetDate)) {
                    $tatStats['met']++;
                } else {
                    $tatStats['not_met']++;
                }
            }

            $completionRows[] = [
                'date' => $completionDate->toDateString(),
                'met' => $targetDate !== null ? $completionDate->lte($targetDate) : null,
            ];
        }

        $period = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $period[$cursor->toDateString()] = [
                'assigned' => 0,
                'completed' => 0,
                'tat_met' => 0,
                'tat_not_met' => 0,
            ];
            $cursor->addDay();
        }

        foreach ($batchAssignments as $assignment) {
            $dateKey = optional($assignment->created_at)->toDateString();
            if ($dateKey !== null && isset($period[$dateKey])) {
                $period[$dateKey]['assigned']++;
            }
        }

        foreach ($completionRows as $row) {
            if (! isset($period[$row['date']])) {
                continue;
            }

            $period[$row['date']]['completed']++;
            if ($row['met'] === true) {
                $period[$row['date']]['tat_met']++;
            }
            if ($row['met'] === false) {
                $period[$row['date']]['tat_not_met']++;
            }
        }

        $chartSeries = [
            'labels' => array_keys($period),
            'assigned' => array_map(fn (array $row): int => $row['assigned'], array_values($period)),
            'completed' => array_map(fn (array $row): int => $row['completed'], array_values($period)),
            'tat_met' => array_map(fn (array $row): int => $row['tat_met'], array_values($period)),
            'tat_not_met' => array_map(fn (array $row): int => $row['tat_not_met'], array_values($period)),
            'status_breakdown' => [
                'Samples In Lab' => $statusCounts['in_lab'],
                'Sample Verification' => $statusCounts['verification'],
                'Sample Approval' => $statusCounts['approval'],
                'Complete' => $statusCounts['complete'],
            ],
        ];

        return view('layouts.lab.personal-dashboard', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'statusCounts' => $statusCounts,
            'tatStats' => $tatStats,
            'requestAssignments' => $requestAssignments,
            'quotationApprovals' => $quotationApprovals,
            'batchAssignmentsPage' => $batchAssignmentsPage,
            'chartSeries' => $chartSeries,
            'historyTab' => $historyTab,
            'quotationApprovalHistory' => $quotationApprovalHistory,
            'labAssignmentHistory' => $labAssignmentHistory,
            'sampleVerificationHistory' => $sampleVerificationHistory,
            'sampleApprovalHistory' => $sampleApprovalHistory,
        ]);
    }
}
