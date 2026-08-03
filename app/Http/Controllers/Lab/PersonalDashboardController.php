<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\Services\Lab\PersonalDashboardHistoryService;
use App\Services\Sampleworkflow\SampleHeaderAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalDashboardController extends Controller
{
    public function __construct(
        private readonly PersonalDashboardHistoryService $historyService,
        private readonly SampleHeaderAssignmentService $assignmentService,
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

        $batchAssignments = SampleHeaderUserAssignment::query()
            ->pending()
            ->forAssignee((string) $user->id)
            ->with([
                'sampleHeader.client',
                'sampleHeader.get_target_date',
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->get();

        $historyTab = (string) $request->input('history_tab', PersonalDashboardHistoryService::TAB_ASSIGNMENTS);
        if (! in_array($historyTab, [
            PersonalDashboardHistoryService::TAB_ASSIGNMENTS,
            PersonalDashboardHistoryService::TAB_VERIFICATIONS,
            PersonalDashboardHistoryService::TAB_APPROVALS,
            PersonalDashboardHistoryService::TAB_QUOTATIONS,
        ], true)) {
            $historyTab = PersonalDashboardHistoryService::TAB_ASSIGNMENTS;
        }

        // Complete lab assignments whose integrity-assigned tests are already fully saved.
        $this->assignmentService->syncCompletionsForAssignee((string) $user->id);

        $taskCounts = $this->historyService->tabCounts((string) $user->id);
        $taskRows = $this->historyService->tasksForTab((string) $user->id, $historyTab);

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
            'chartSeries' => $chartSeries,
            'historyTab' => $historyTab,
            'taskCounts' => $taskCounts,
            'taskRows' => $taskRows,
        ]);
    }
}
