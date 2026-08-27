<?php

namespace App\Services\Lab;

use App\ChainOfCustody;
use App\Models\QuotationApprovalLog;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\Services\Commercial\QuotationApprovalService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PersonalDashboardHistoryService
{
    public const TAB_ASSIGNMENTS = 'assignments';

    public const TAB_VERIFICATIONS = 'verifications';

    public const TAB_APPROVALS = 'approvals';

    public const TAB_QUOTATIONS = 'quotations';

    public const STAGE_VERIFICATION = 'Sample Verification';

    public const STAGE_APPROVAL = 'Sample Approval';

    private const PER_PAGE = 15;

    private const FRAGMENT = 'my-tasks';

    public function __construct(
        private readonly QuotationApprovalService $quotationApprovalService,
    ) {
    }

    /**
     * @return array{assignments: int, verifications: int, approvals: int, quotations: int}
     */
    public function tabCounts(string $userId): array
    {
        $pendingAssignments = SampleHeaderUserAssignment::query()
            ->pending()
            ->forAssignee($userId)
            ->with('sampleHeader')
            ->get();

        $pendingLab = $pendingAssignments
            ->filter(function (SampleHeaderUserAssignment $assignment): bool {
                $batchStatus = (string) ($assignment->sampleHeader?->status ?? '');

                return ! in_array($batchStatus, [self::STAGE_VERIFICATION, self::STAGE_APPROVAL], true);
            })
            ->count();

        $pendingVerification = $pendingAssignments
            ->filter(fn (SampleHeaderUserAssignment $assignment): bool => (string) ($assignment->sampleHeader?->status ?? '') === self::STAGE_VERIFICATION)
            ->count();

        $pendingApproval = $pendingAssignments
            ->filter(fn (SampleHeaderUserAssignment $assignment): bool => (string) ($assignment->sampleHeader?->status ?? '') === self::STAGE_APPROVAL)
            ->count();

        $completedAssignments = SampleHeaderUserAssignment::query()
            ->forAssignee($userId)
            ->where('status', SampleHeaderUserAssignment::STATUS_COMPLETED)
            ->count();

        $completedVerifications = ChainOfCustody::query()
            ->where('moved_out_by', $userId)
            ->where('workflow_stage', self::STAGE_VERIFICATION)
            ->whereNotNull('moved_out_date')
            ->count();

        $completedApprovals = ChainOfCustody::query()
            ->where('moved_out_by', $userId)
            ->where('workflow_stage', self::STAGE_APPROVAL)
            ->whereNotNull('moved_out_date')
            ->count();

        $pendingQuotations = $this->quotationApprovalService->pendingApprovalsForUser($userId)->count();

        $completedQuotations = QuotationApprovalLog::query()
            ->where('actor_user_id', $userId)
            ->whereIn('action', [
                QuotationApprovalLog::ACTION_APPROVED,
                QuotationApprovalLog::ACTION_REJECTED,
            ])
            ->count();

        return [
            self::TAB_ASSIGNMENTS => $pendingLab + $completedAssignments,
            self::TAB_VERIFICATIONS => $pendingVerification + $completedVerifications,
            self::TAB_APPROVALS => $pendingApproval + $completedApprovals,
            self::TAB_QUOTATIONS => $pendingQuotations + $completedQuotations,
        ];
    }

    /**
     * @return LengthAwarePaginatorContract<int, array<string, mixed>>
     */
    public function tasksForTab(string $userId, string $tab): LengthAwarePaginatorContract
    {
        return match ($tab) {
            self::TAB_VERIFICATIONS => $this->paginateRows(
                $this->workflowTaskRows($userId, self::STAGE_VERIFICATION),
                'verification_tasks_page',
            ),
            self::TAB_APPROVALS => $this->paginateRows(
                $this->workflowTaskRows($userId, self::STAGE_APPROVAL),
                'approval_tasks_page',
            ),
            self::TAB_QUOTATIONS => $this->paginateRows(
                $this->quotationTaskRows($userId),
                'quotation_tasks_page',
            ),
            default => $this->paginateRows(
                $this->labAssignmentRows($userId),
                'assignment_tasks_page',
            ),
        };
    }

    /**
     * Sort unified task rows: pending first, then newest activity timestamp.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    public function sortUnifiedRows(Collection $rows): Collection
    {
        return $rows
            ->sort(function (array $left, array $right): int {
                $leftPending = ($left['status_tone'] ?? '') === 'pending' ? 0 : 1;
                $rightPending = ($right['status_tone'] ?? '') === 'pending' ? 0 : 1;

                if ($leftPending !== $rightPending) {
                    return $leftPending <=> $rightPending;
                }

                $leftStamp = (string) ($left['sort_at'] ?? '');
                $rightStamp = (string) ($right['sort_at'] ?? '');

                return strcmp($rightStamp, $leftStamp);
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function labAssignmentRows(string $userId): Collection
    {
        $assignments = SampleHeaderUserAssignment::query()
            ->forAssignee($userId)
            ->with([
                'sampleHeader.client',
                'assignedBy',
            ])
            ->latest('created_at')
            ->get();

        $rows = $assignments
            ->filter(function (SampleHeaderUserAssignment $assignment): bool {
                if ($assignment->status === SampleHeaderUserAssignment::STATUS_COMPLETED) {
                    return true;
                }

                $batchStatus = (string) ($assignment->sampleHeader?->status ?? '');

                return ! in_array($batchStatus, [self::STAGE_VERIFICATION, self::STAGE_APPROVAL], true);
            })
            ->map(fn (SampleHeaderUserAssignment $assignment): array => $this->mapAssignmentRow($assignment));

        return $this->sortUnifiedRows($rows);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function workflowTaskRows(string $userId, string $workflowStage): Collection
    {
        $pending = SampleHeaderUserAssignment::query()
            ->pending()
            ->forAssignee($userId)
            ->whereHas('sampleHeader', function ($query) use ($workflowStage): void {
                $query->where('status', $workflowStage);
            })
            ->with([
                'sampleHeader.client',
                'assignedBy',
            ])
            ->latest('created_at')
            ->get()
            ->map(fn (SampleHeaderUserAssignment $assignment): array => $this->mapAssignmentRow($assignment));

        $completed = ChainOfCustody::query()
            ->with([
                'sampleHeader.client',
            ])
            ->where('moved_out_by', $userId)
            ->where('workflow_stage', $workflowStage)
            ->whereNotNull('moved_out_date')
            ->latest('moved_out_date')
            ->get()
            ->map(fn (ChainOfCustody $custody): array => $this->mapCustodyRow($custody, $workflowStage));

        return $this->sortUnifiedRows($pending->concat($completed));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function quotationTaskRows(string $userId): Collection
    {
        $pending = $this->quotationApprovalService
            ->pendingApprovalsForUser($userId)
            ->map(function ($header): array {
                $status = (string) ($header->status ?? '');
                if (in_array($status, ['Quote In Approval', 'Quote Complete'], true)) {
                    $openUrl = route('view_quotation_final', [
                        'id' => $header->id,
                        'stage' => $status,
                    ]);
                } else {
                    $openUrl = route('add-qoute-details-view', [
                        'id' => $header->id,
                        'stage' => $status !== '' ? $status : 'Quote In Preparation',
                    ]);
                }

                $assignedAt = optional($header->approval_requested_at)->format('Y-m-d H:i') ?? '—';
                $sortAt = optional($header->approval_requested_at ?? $header->updated_at)->toDateTimeString() ?? '';

                return [
                    'kind' => 'quotation',
                    'reference' => (string) ($header->quote_number ?? 'N/A'),
                    'client' => (string) ($header->customer?->name ?? 'N/A'),
                    'status' => 'Pending',
                    'status_tone' => 'pending',
                    'detail' => '—',
                    'assigned_at' => $assignedAt,
                    'completed_at' => '—',
                    'batch_status' => null,
                    'open_url' => $openUrl,
                    'sort_at' => $sortAt,
                ];
            });

        $completed = QuotationApprovalLog::query()
            ->with([
                'quotationHeader.customer',
                'enquiry.submissionFormInstance.submissionForm',
            ])
            ->where('actor_user_id', $userId)
            ->whereIn('action', [
                QuotationApprovalLog::ACTION_APPROVED,
                QuotationApprovalLog::ACTION_REJECTED,
            ])
            ->latest('created_at')
            ->get()
            ->map(function (QuotationApprovalLog $log): array {
                $quote = $log->quotationHeader;
                $status = (string) ($quote?->status ?? '');
                $openUrl = null;
                if ($quote !== null) {
                    if (in_array($status, ['Quote In Approval', 'Quote Complete'], true)) {
                        $openUrl = route('view_quotation_final', [
                            'id' => $quote->id,
                            'stage' => $status,
                        ]);
                    } else {
                        $openUrl = route('add-qoute-details-view', [
                            'id' => $quote->id,
                            'stage' => $status !== '' ? $status : 'Quote In Preparation',
                        ]);
                    }
                }

                $isApproved = $log->action === QuotationApprovalLog::ACTION_APPROVED;

                return [
                    'kind' => 'quotation',
                    'reference' => (string) ($quote?->quote_number ?? 'N/A'),
                    'client' => (string) ($quote?->customer?->name ?? 'N/A'),
                    'status' => $isApproved ? 'Approved' : 'Rejected',
                    'status_tone' => $isApproved ? 'complete' : 'rejected',
                    'detail' => $log->comments ?: '—',
                    'assigned_at' => '—',
                    'completed_at' => optional($log->created_at)->format('Y-m-d H:i') ?? '—',
                    'batch_status' => null,
                    'open_url' => $openUrl,
                    'sort_at' => optional($log->created_at)->toDateTimeString() ?? '',
                ];
            });

        return $this->sortUnifiedRows($pending->concat($completed));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAssignmentRow(SampleHeaderUserAssignment $assignment): array
    {
        $batch = $assignment->sampleHeader;
        $isCompleted = $assignment->status === SampleHeaderUserAssignment::STATUS_COMPLETED;
        $assignedAt = optional($assignment->created_at)->format('Y-m-d H:i') ?? '—';
        $completedAt = optional($assignment->completed_at)->format('Y-m-d H:i') ?? '—';
        $sortAt = optional($isCompleted ? $assignment->completed_at : $assignment->created_at)->toDateTimeString()
            ?? optional($assignment->created_at)->toDateTimeString()
            ?? '';

        $detailParts = [];
        if (($assignment->assignedBy?->name ?? '') !== '') {
            $detailParts[] = 'Assigned by '.$assignment->assignedBy->name;
        }
        if (trim((string) ($assignment->comment ?? '')) !== '') {
            $detailParts[] = trim((string) $assignment->comment);
        }

        return [
            'kind' => 'assignment',
            'reference' => (string) ($batch?->batch_code ?? 'N/A'),
            'client' => (string) ($batch?->client?->name ?? 'N/A'),
            'status' => $isCompleted ? 'Completed' : 'Pending',
            'status_tone' => $isCompleted ? 'complete' : 'pending',
            'detail' => $detailParts !== [] ? implode(' · ', $detailParts) : '—',
            'assigned_at' => $assignedAt,
            'completed_at' => $isCompleted ? $completedAt : '—',
            'batch_status' => (string) ($batch?->status ?? 'N/A'),
            'open_url' => $batch
                ? route('view-batch-details', [
                    'batch' => $batch->id,
                    'client' => 0,
                    'portal' => 0,
                    'status' => $batch->status ?: 'Samples In Lab',
                ])
                : null,
            'sort_at' => $sortAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCustodyRow(ChainOfCustody $custody, string $workflowStage): array
    {
        $batch = $custody->sampleHeader;
        $completedAt = $custody->moved_out_date
            ? \Carbon\Carbon::parse((string) $custody->moved_out_date)->format('Y-m-d H:i')
            : '—';
        $sortAt = $custody->moved_out_date
            ? \Carbon\Carbon::parse((string) $custody->moved_out_date)->toDateTimeString()
            : '';

        return [
            'kind' => 'custody',
            'reference' => (string) ($batch?->batch_code ?? 'N/A'),
            'client' => (string) ($batch?->client?->name ?? 'N/A'),
            'status' => 'Completed',
            'status_tone' => 'complete',
            'detail' => $custody->comments ?: '—',
            'assigned_at' => optional($custody->created_at)->format('Y-m-d H:i') ?? '—',
            'completed_at' => $completedAt,
            'batch_status' => $workflowStage,
            'open_url' => $batch
                ? route('view-batch-details', [
                    'batch' => $batch->id,
                    'client' => 0,
                    'portal' => 0,
                    'status' => $batch->status ?: $workflowStage,
                ])
                : null,
            'sort_at' => $sortAt,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return LengthAwarePaginatorContract<int, array<string, mixed>>
     */
    private function paginateRows(Collection $rows, string $pageName): LengthAwarePaginatorContract
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $items = $rows->forPage($page, self::PER_PAGE)->values();

        return (new LengthAwarePaginator(
            $items,
            $rows->count(),
            self::PER_PAGE,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        ))
            ->withQueryString()
            ->fragment(self::FRAGMENT);
    }
}
