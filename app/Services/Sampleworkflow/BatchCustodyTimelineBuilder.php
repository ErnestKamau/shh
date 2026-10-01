<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\ChainOfCustody;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\CRM\CustomerContact;
use App\Models\EnquiryQuotation;
use App\Models\QuotationApprovalLog;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\Models\Sampleworkflow\SampleWorkflowEvent;
use App\Models\SubmissionFormAuditLog;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\SampleHeader;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the batch Chain of Custody timeline, including receiving-pipeline
 * milestones (aligned with Samples Receiving tabs) before lab custody rows.
 */
class BatchCustodyTimelineBuilder
{
    /**
     * @return Collection<int, object{
     *     source: string,
     *     title: string,
     *     subtitle: ?string,
     *     user_name: ?string,
     *     occurred_at: Carbon,
     *     badge: ?string,
     *     comment: ?string,
     *     is_completed?: bool,
     *     completed_by?: ?string,
     *     completed_at?: mixed
     * }>
     */
    public function build(SampleHeader $batch, string $search = ''): Collection
    {
        $events = collect();
        $instance = $batch->submissionFormInstance;

        if ($instance) {
            $events = $events
                ->concat($this->auditEvents($instance, $search))
                ->concat($this->intrayEvents($instance, $search))
                ->concat($this->quotationCommercialPipelineEvents($instance, $search))
                ->concat($this->commercialEngagementEvents($instance, $search))
                ->concat($this->receivingPipelineEvents($instance, $batch, $search))
                ->concat($this->sampleWorkflowEventsForInstance($instance, $search))
                ->concat($this->labWorksheetTimelineEvents($instance, $batch, $search));
        }

        $events = $events
            ->concat($this->batchCustodyEvents($batch, $search))
            ->concat($this->sampleWorkflowEvents($batch, $search))
            ->concat($this->batchAssignmentTimelineEvents($batch, $search))
            ->concat($this->batchAnalysisTimingEvents($batch, $search))
            ->concat($this->batchRecordAuditEvents($batch, $search));

        return $this->finalizeTimeline($events);
    }

    /**
     * Pre-lab / request-view timeline (audit, intray, instance workflow events).
     *
     * @return Collection<int, object>
     */
    public function buildForInstance(
        SubmissionFormInstance $instance,
        string $search = '',
        ?Carbon $preLabCutoff = null,
    ): Collection {
        $batch = $this->resolvePrimaryBatchForInstance($instance);

        $events = collect()
            ->concat($this->auditEvents($instance, $search))
            ->concat($this->intrayEvents($instance, $search))
            ->concat($this->quotationCommercialPipelineEvents($instance, $search))
            ->concat($this->commercialEngagementEvents($instance, $search))
            ->concat($this->receivingPipelineEvents($instance, $batch, $search))
            ->concat($this->sampleWorkflowEventsForInstance($instance, $search))
            ->concat($this->labWorksheetTimelineEvents($instance, $batch, $search));

        if ($batch !== null) {
            $events = $events
                ->concat($this->batchCustodyEvents($batch, $search))
                ->concat($this->sampleWorkflowEvents($batch, $search))
                ->concat($this->batchAssignmentTimelineEvents($batch, $search))
                ->concat($this->batchAnalysisTimingEvents($batch, $search))
                ->concat($this->batchRecordAuditEvents($batch, $search));
        }

        if ($preLabCutoff !== null) {
            $events = $events->filter(
                fn ($event) => $event->occurred_at !== null && $event->occurred_at->lte($preLabCutoff)
            );
        }

        return $this->finalizeTimeline($events);
    }

    private function resolvePrimaryBatchForInstance(SubmissionFormInstance $instance): ?SampleHeader
    {
        return $instance->batches()
            ->orderByRaw('CASE WHEN split_from_sample_header_id IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * @return Collection<int, object>
     */
    private function sampleWorkflowEvents(
        SampleHeader $batch,
        string $search,
    ): Collection {
        $events = SampleWorkflowEvent::query()
            ->where('sample_header_id', (string) $batch->id)
            ->orderByDesc('occurred_at')
            ->get();

        return $this->mapSampleWorkflowEvents($events, $search);
    }

    /**
     * @return Collection<int, object>
     */
    private function sampleWorkflowEventsForInstance(SubmissionFormInstance $instance, string $search): Collection
    {
        $events = SampleWorkflowEvent::query()
            ->where('submission_form_instance_id', (string) $instance->id)
            ->whereNull('sample_header_id')
            ->orderByDesc('occurred_at')
            ->get();

        return $this->mapSampleWorkflowEvents($events, $search);
    }

    /**
     * @param  Collection<int, SampleWorkflowEvent>|iterable<int, SampleWorkflowEvent>  $records
     * @return Collection<int, object>
     */
    private function mapSampleWorkflowEvents(iterable $records, string $search): Collection
    {
        $events = collect();

        foreach ($records as $record) {
            $title = trim((string) ($record->what ?? 'Workflow event'));
            $subtitle = trim((string) ($record->workflow_stage ?? ''));
            if ($subtitle === '' && filled($record->event_type)) {
                $subtitle = ucwords(str_replace('_', ' ', (string) $record->event_type));
            }

            $userName = trim((string) ($record->who_name ?? 'System'));
            $searchable = strtolower(implode(' ', array_filter([
                $title,
                $subtitle,
                $userName,
                (string) ($record->how ?? ''),
                (string) ($record->why ?? ''),
                (string) ($record->where ?? ''),
                (string) ($record->event_type ?? ''),
            ])));

            if ($search !== '' && ! str_contains($searchable, strtolower($search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'workflow_event',
                'title' => $title,
                'subtitle' => $subtitle !== '' ? $subtitle : 'Workflow activity',
                'user_name' => $userName !== '' ? $userName : 'System',
                'occurred_at' => $record->occurred_at ? Carbon::parse($record->occurred_at) : null,
                'badge' => $this->badgeForWorkflowEvent((string) ($record->event_type ?? '')),
                'comment' => $this->buildWorkflowActionNarrative($record),
                'what' => $title,
                'how' => $record->how,
                'why' => $record->why,
                'where' => $record->where,
                'event_type' => $record->event_type,
                'metadata' => $this->normalizeWorkflowMetadata($record),
                'is_completed' => true,
            ]);
        }

        return $events;
    }

    private function badgeForWorkflowEvent(string $eventType): string
    {
        return match ($eventType) {
            'worksheet_issued', 'worksheet_imported', 'samples_accepted' => 'success',
            'result_captured', 'integrity_assignments_saved' => 'primary',
            'workflow_stage_changed' => 'warning',
            default => 'info',
        };
    }

    /**
     * @return Collection<int, object>
     */
    private function auditEvents(SubmissionFormInstance $instance, string $search): Collection
    {
        $events = collect();

        $auditLogs = $instance->auditLogs()->with('user')->latest()->get();
        foreach ($auditLogs as $log) {
            /** @var SubmissionFormAuditLog $log */
            $userName = $log->user ? $log->user->name : 'Customer (Portal)';
            $title = $log->getActionDisplayName();
            $fieldChanges = is_array($log->field_changes) ? $log->field_changes : [];
            $statusTo = data_get($fieldChanges, 'status.to', data_get($fieldChanges, 'status'));

            if ($log->action === 'submitted' || ($log->action === 'updated' && $statusTo === 'submitted')) {
                $title = 'Submitted Requests';
            } elseif ($log->action === 'created') {
                $title = 'Request Drafted';
            } elseif ($log->action === 'received') {
                $title = 'Received Request';
            } elseif ($log->action === 'sent_for_analyst_review') {
                $title = 'In Review';
            } elseif ($log->action === 'additional_info_requested') {
                $title = 'Request Additional Info';
            } elseif ($log->action === 'additional_info_provided') {
                $title = 'Customer Provided Additional Info';
            } elseif ($log->action === 'additional_info_resumed') {
                $title = 'Resumed Ready for Reception';
            } elseif ($log->action === 'approved') {
                $title = 'Accepted';
            }

            $subtitle = 'Status: '.(is_string($statusTo) && $statusTo !== ''
                ? $statusTo
                : (string) $instance->status);

            $searchable = strtolower($title.' '.$subtitle.' '.$userName.' '.(string) $log->notes);
            if ($search !== '' && ! str_contains($searchable, strtolower($search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'audit',
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'occurred_at' => Carbon::parse($log->created_at),
                'badge' => $log->getActionBadgeColor(),
                'comment' => $this->buildAuditActionNarrative($log, $instance, $statusTo),
                'what' => $title,
                'how' => 'Submission form audit log',
                'where' => 'Request / TRF instance',
                'metadata' => array_filter([
                    'status' => is_string($statusTo) ? $statusTo : (string) $instance->status,
                    'notes' => filled($log->notes) ? (string) $log->notes : null,
                ]),
            ]);
        }

        return $events;
    }

    /**
     * @return Collection<int, object>
     */
    private function intrayEvents(SubmissionFormInstance $instance, string $search): Collection
    {
        $events = collect();

        $intrays = $instance->intrays()
            ->with(['fromUser', 'toUser', 'assignedBy', 'completedByUser'])
            ->orderByDesc('created_at')
            ->get();

        foreach ($intrays as $intray) {
            $userName = $intray->assignedBy ? $intray->assignedBy->name : 'System';
            $title = $intray->status === 'completed' ? 'Intray completed' : 'Intray assigned';
            $subtitle = '';
            if ($intray->toUser) {
                $subtitle = $intray->fromUser
                    ? 'From '.$intray->fromUser->name.' → '.$intray->toUser->name
                    : 'Assigned to '.$intray->toUser->name;
            }

            $searchable = strtolower($title.' '.$subtitle.' '.$userName.' '.(string) $intray->comment);
            if ($search !== '' && ! str_contains($searchable, strtolower($search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'intray',
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'occurred_at' => Carbon::parse($intray->completed_at ?? $intray->created_at),
                'badge' => $intray->status === 'completed' ? 'success' : 'warning',
                'comment' => $intray->comment,
            ]);
        }

        return $events;
    }

    /**
     * Receiving board milestones when audit/CoC rows were never written
     * (walk-in / acceptance path). Labels follow Samples Receiving tabs.
     *
     * @return Collection<int, object>
     */
    private function receivingPipelineEvents(
        SubmissionFormInstance $instance,
        ?SampleHeader $batch,
        string $search,
    ): Collection {
        $events = collect();
        $enquiry = $instance->sampleSubmissionRequest;
        $tabs = WorkflowBoard::receivingRequestTabs();

        $milestones = [];

        $submittedAt = $instance->submitted_at ?? $instance->created_at;
        if ($submittedAt) {
            $summary = $this->resolveRequestSummary($instance);
            $milestones[] = [
                'key' => 'submitted',
                'title' => $tabs['submitted'] ?? 'Submitted Requests',
                'at' => Carbon::parse($submittedAt),
                'user' => $instance->submittedBy?->name ?? 'System',
                'comment' => sprintf(
                    'Request %s submitted for client %s via %s — %d sample(s), %d test(s).',
                    $summary['request_no'],
                    $summary['client'],
                    $summary['channel'],
                    $summary['sample_count'],
                    $summary['test_count'],
                ),
                'badge' => 'info',
                'metadata' => [
                    'request_no' => $summary['request_no'],
                    'client' => $summary['client'],
                    'channel' => $summary['channel'],
                    'number_of_samples' => $summary['sample_count'],
                    'number_of_tests' => $summary['test_count'],
                ],
            ];
        }

        if ($enquiry instanceof SampleSubmissionRequest) {
            $hasEngagementSent = EnquiryQuotation::query()
                ->where('sample_submission_request_id', $enquiry->id)
                ->whereNotNull('sent_to_customer_at')
                ->exists();
            $hasEngagementAccepted = EnquiryQuotation::query()
                ->where('sample_submission_request_id', $enquiry->id)
                ->whereNotNull('accepted_at')
                ->exists();

            if (! $hasEngagementSent && $enquiry->quotation_first_sent_to_customer_at) {
                $milestones[] = [
                    'key' => 'quotation_sent',
                    'title' => 'Quotation Sent',
                    'at' => Carbon::parse($enquiry->quotation_first_sent_to_customer_at),
                    'user' => 'System',
                    'comment' => 'Quotation dispatched to customer (legacy timestamp).',
                    'badge' => 'primary',
                ];
            }

            if (! $hasEngagementAccepted && $enquiry->quotation_accepted_at) {
                $readyEvent = $this->createReadyForReceptionEvent(
                    $instance,
                    $enquiry,
                    Carbon::parse($enquiry->quotation_accepted_at),
                    'System',
                    $search,
                );
                if ($readyEvent !== null && $this->matchesSearch($readyEvent, $search)) {
                    $events->push($readyEvent);
                }
            }

            if ($this->enquiryAtOrPastStatus($enquiry, SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK)) {
                $integrityAt = $enquiry->updated_at ?? $enquiry->quotation_accepted_at ?? $instance->submitted_at ?? $instance->created_at;
                if ($enquiry->quotation_accepted_at) {
                    $integrityAt = Carbon::parse($integrityAt)->max(
                        Carbon::parse($enquiry->quotation_accepted_at)->addSecond()
                    );
                }

                $milestones[] = [
                    'key' => 'sample_integrity_check',
                    'title' => $tabs['sample_integrity_check'] ?? 'Sample Integrity & Acceptance Check',
                    'at' => Carbon::parse($integrityAt),
                    'user' => 'System',
                    'comment' => 'Samples received — integrity and acceptance check in progress.',
                    'badge' => 'warning',
                ];
            }

            if ((string) $enquiry->status === SampleSubmissionRequest::STATUS_ACCEPTED) {
                $acceptedAt = $batch?->created_at ?? $enquiry->updated_at ?? $enquiry->quotation_accepted_at;
                if ($acceptedAt) {
                    $milestones[] = [
                        'key' => 'accepted',
                        'title' => $tabs['accepted'] ?? 'Accepted',
                        'at' => Carbon::parse($acceptedAt),
                        'user' => $batch?->receiving_officer_name ?: 'System',
                        'comment' => 'Commercial enquiry accepted at laboratory reception.',
                        'badge' => 'success',
                    ];
                }
            }
        }

        if ($batch?->created_at) {
            $milestones[] = [
                'key' => 'request_review',
                'title' => 'Samples Request Review',
                'at' => Carbon::parse($batch->created_at),
                'user' => $batch->receiving_officer_name ?: 'System',
                'comment' => 'Batch created for analyst / manager review.',
                'badge' => 'warning',
            ];
        }

        $existingTitles = $this->existingEventTitles($instance, $batch);

        foreach ($milestones as $milestone) {
            if (isset($existingTitles[strtolower($milestone['title'])])) {
                continue;
            }

            // Skip synthesized "Samples Request Review" when a real CoC row already covers review→lab.
            if ($milestone['key'] === 'request_review' && $batch !== null && $this->hasBatchCustodyStage($batch, 'Samples Request Review')) {
                continue;
            }

            $searchable = strtolower($milestone['title'].' '.$milestone['user'].' '.(string) $milestone['comment']);
            if ($search !== '' && ! str_contains($searchable, strtolower($search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'receiving',
                'title' => $milestone['title'],
                'subtitle' => 'Receiving: '.$milestone['title'],
                'user_name' => $milestone['user'],
                'occurred_at' => $milestone['at'],
                'badge' => $milestone['badge'],
                'comment' => $milestone['comment'],
                'what' => $milestone['title'],
                'how' => 'Samples Receiving workflow',
                'where' => 'Samples Receiving board',
                'metadata' => array_filter($milestone['metadata'] ?? [
                    'milestone' => $milestone['key'],
                ]),
                'is_completed' => true,
                'completed_by' => $milestone['user'],
                'completed_at' => $milestone['at'],
            ]);
        }

        return $events;
    }

    /**
     * @return list<string>
     */
    private function enquiryReceivingStatusOrder(): array
    {
        return SampleSubmissionRequest::receivingWorkflowStatuses();
    }

    private function enquiryAtOrPastStatus(SampleSubmissionRequest $enquiry, string $targetStatus): bool
    {
        $order = $this->enquiryReceivingStatusOrder();
        $current = array_search((string) $enquiry->status, $order, true);
        $target = array_search($targetStatus, $order, true);

        if ($current === false || $target === false) {
            return (string) $enquiry->status === $targetStatus;
        }

        return $current >= $target;
    }

    /**
     * @return Collection<int, object>
     */
    private function batchCustodyEvents(SampleHeader $batch, string $search): Collection
    {
        $events = collect();

        $custodyRecords = ChainOfCustody::query()
            ->with(['started_by', 'completed_by', 'tracking_stage'])
            ->where('sample_header_id', $batch->id)
            ->orderByDesc('created_at')
            ->get();

        foreach ($custodyRecords as $custody) {
            $userName = $custody->started_by ? $custody->started_by->name : 'System';
            $title = $custody->workflow_stage ?? 'Batch custody';
            $subtitle = 'Tracking Stage: '.($custody->tracking_stage->name ?? 'N/A');

            $searchable = strtolower($title.' '.$subtitle.' '.$userName.' '.(string) $custody->comments);
            if ($search !== '' && ! str_contains($searchable, strtolower($search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'batch',
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'occurred_at' => Carbon::parse($custody->created_at),
                'badge' => 'primary',
                'comment' => filled($custody->comments)
                    ? (string) $custody->comments
                    : sprintf('Batch custody opened at workflow stage "%s".', $title),
                'what' => 'Batch entered '.$title,
                'how' => 'Chain of custody transition',
                'where' => (string) ($custody->tracking_stage->name ?? $title),
                'metadata' => array_filter([
                    'batch_code' => (string) ($batch->batch_code ?? ''),
                    'tracking_stage' => (string) ($custody->tracking_stage->name ?? ''),
                    'moved_in_by' => $userName,
                    'moved_out_at' => $custody->moved_out_date
                        ? Carbon::parse($custody->moved_out_date)->format('Y-m-d H:i')
                        : null,
                ]),
                'is_completed' => ! empty($custody->moved_out_date),
                'completed_by' => $custody->completed_by ? $custody->completed_by->name : 'System',
                'completed_at' => $custody->moved_out_date,
            ]);
        }

        return $events;
    }

    /**
     * @return array<string, true>
     */
    private function existingEventTitles(SubmissionFormInstance $instance, ?SampleHeader $batch): array
    {
        $titles = [];

        foreach ($instance->auditLogs as $log) {
            $titles[strtolower($log->getActionDisplayName())] = true;
            if ($log->action === 'received') {
                $titles['received request'] = true;
            }
            if ($log->action === 'submitted') {
                $titles['submitted requests'] = true;
            }
        }

        if ($batch !== null) {
            foreach (ChainOfCustody::query()->where('sample_header_id', $batch->id)->pluck('workflow_stage') as $stage) {
                if (is_string($stage) && $stage !== '') {
                    $titles[strtolower($stage)] = true;
                }
            }
        }

        return $titles;
    }

    private function hasBatchCustodyStage(SampleHeader $batch, string $workflowStage): bool
    {
        return ChainOfCustody::query()
            ->where('sample_header_id', $batch->id)
            ->where('workflow_stage', $workflowStage)
            ->exists();
    }

    /**
     * Internal quotation workflow: pricing, approval, and dispatch before customer acceptance.
     *
     * @return Collection<int, object>
     */
    private function quotationCommercialPipelineEvents(SubmissionFormInstance $instance, string $search): Collection
    {
        $events = collect();
        $enquiry = $instance->sampleSubmissionRequest;
        if (! $enquiry instanceof SampleSubmissionRequest) {
            return $events;
        }

        $enquiry->loadMissing([
            'currentQuotation.preparedBy',
            'currentQuotation.approvedByUser',
            'currentQuotation.approvalRequestedByUser',
        ]);

        $header = $enquiry->currentQuotation;
        if ($header === null && filled($enquiry->current_quotation_header_id)) {
            $header = QuotationHeader::query()
                ->with(['preparedBy', 'approvedByUser', 'approvalRequestedByUser'])
                ->withCount('details')
                ->find($enquiry->current_quotation_header_id);
        } elseif ($header !== null) {
            $header->loadCount('details');
        }

        if ($header === null) {
            return $events;
        }

        $quoteNumber = trim((string) ($header->quote_number ?? ''));
        $quoteLabel = $quoteNumber !== '' ? $quoteNumber : ('Quotation '.substr((string) $header->id, 0, 8));
        $lineCount = (int) ($header->details_count ?? 0);

        if ($header->created_at) {
            $preparer = $header->preparedBy?->name
                ?? $this->resolveUserName($header->prepared_by_id)
                ?? 'Commercial team';

            $inProgressEvent = (object) [
                'source' => 'commercial',
                'title' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
                'subtitle' => $quoteLabel,
                'user_name' => $preparer,
                'occurred_at' => Carbon::parse($header->created_at),
                'badge' => 'info',
                'comment' => sprintf(
                    'Quotation %s opened for pricing configuration — %d line item(s) on the quote.',
                    $quoteLabel,
                    $lineCount,
                ),
                'what' => 'Commercial pricing configuration started',
                'how' => 'Process Enquiry / quotation builder',
                'where' => 'Commercial / Billing',
                'metadata' => array_filter([
                    'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                    'line_items' => $lineCount > 0 ? $lineCount : null,
                    'prepared_by' => $preparer,
                ]),
                'is_completed' => true,
            ];

            if ($this->matchesSearch($inProgressEvent, $search)) {
                $events->push($inProgressEvent);
            }
        }

        $logs = QuotationApprovalLog::query()
            ->with(['actor', 'assignee'])
            ->where(function ($query) use ($enquiry, $header): void {
                $query->where('sample_submission_request_id', $enquiry->id)
                    ->orWhere('quotation_header_id', $header->id);
            })
            ->orderBy('created_at')
            ->get();

        foreach ($logs as $log) {
            $actor = $log->actor?->name ?? $this->resolveUserName($log->actor_user_id) ?? 'System';
            $assignee = $log->assignee?->name ?? $this->resolveUserName($log->assignee_user_id);

            $pipelineEvent = match ($log->action) {
                QuotationApprovalLog::ACTION_SUBMITTED => (object) [
                    'source' => 'commercial',
                    'title' => SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL,
                    'subtitle' => $quoteLabel,
                    'user_name' => $actor,
                    'occurred_at' => Carbon::parse($log->created_at),
                    'badge' => 'warning',
                    'comment' => sprintf(
                        'Quotation %s submitted for internal approval%s.',
                        $quoteLabel,
                        $assignee !== null ? ' — assigned to '.$assignee : '',
                    ),
                    'what' => 'Quotation sent for approval',
                    'how' => 'Quotation approval workflow',
                    'where' => 'Commercial / Billing',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'submitted_by' => $actor,
                        'approver' => $assignee,
                        'comments' => filled($log->comments) ? (string) $log->comments : null,
                    ]),
                    'is_completed' => true,
                ],
                QuotationApprovalLog::ACTION_APPROVED => (object) [
                    'source' => 'commercial',
                    'title' => 'Quotation Approved',
                    'subtitle' => $quoteLabel,
                    'user_name' => $actor,
                    'occurred_at' => Carbon::parse($log->created_at),
                    'badge' => 'success',
                    'comment' => sprintf(
                        'Quotation %s approved for customer dispatch.',
                        $quoteLabel,
                    ),
                    'what' => 'Internal quotation approval recorded',
                    'how' => 'Quotation approval workflow',
                    'where' => 'Commercial / Billing',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'approved_by' => $actor,
                        'comments' => filled($log->comments) ? (string) $log->comments : null,
                    ]),
                    'is_completed' => true,
                ],
                QuotationApprovalLog::ACTION_SENT => $this->buildQuotationSentPipelineEvent(
                    $header,
                    $enquiry,
                    $quoteLabel,
                    $quoteNumber,
                    $actor,
                    Carbon::parse($log->created_at),
                    filled($log->comments) ? (string) $log->comments : null,
                ),
                default => null,
            };

            if ($pipelineEvent !== null && $this->matchesSearch($pipelineEvent, $search)) {
                $events->push($pipelineEvent);
            }
        }

        if ($logs->isEmpty()) {
            if ($header->approval_requested_at !== null
                && $this->enquiryAtOrPastStatus($enquiry, SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL)) {
                $requester = $header->approvalRequestedByUser?->name
                    ?? $this->resolveUserName($header->approval_requested_by)
                    ?? 'Commercial team';

                $pendingEvent = (object) [
                    'source' => 'commercial',
                    'title' => SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL,
                    'subtitle' => $quoteLabel,
                    'user_name' => $requester,
                    'occurred_at' => Carbon::parse($header->approval_requested_at),
                    'badge' => 'warning',
                    'comment' => sprintf(
                        'Quotation %s submitted for internal approval.',
                        $quoteLabel,
                    ),
                    'what' => 'Quotation sent for approval',
                    'how' => 'Quotation approval workflow',
                    'where' => 'Commercial / Billing',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'submitted_by' => $requester,
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($pendingEvent, $search)) {
                    $events->push($pendingEvent);
                }
            }

            if ($header->approval_decision_at !== null
                && $this->enquiryAtOrPastStatus($enquiry, SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND)) {
                $approver = $header->approvedByUser?->name
                    ?? $this->resolveUserName($header->approved_by)
                    ?? 'Approver';

                $approvedEvent = (object) [
                    'source' => 'commercial',
                    'title' => 'Quotation Approved',
                    'subtitle' => $quoteLabel,
                    'user_name' => $approver,
                    'occurred_at' => Carbon::parse($header->approval_decision_at),
                    'badge' => 'success',
                    'comment' => sprintf(
                        'Quotation %s approved for customer dispatch.',
                        $quoteLabel,
                    ),
                    'what' => 'Internal quotation approval recorded',
                    'how' => 'Quotation approval workflow',
                    'where' => 'Commercial / Billing',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'approved_by' => $approver,
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($approvedEvent, $search)) {
                    $events->push($approvedEvent);
                }
            }
        }

        return $events;
    }

    private function buildQuotationSentPipelineEvent(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
        string $quoteLabel,
        string $quoteNumber,
        string $sender,
        Carbon $at,
        ?string $logComments,
    ): object {
        $engagement = EnquiryQuotation::query()
            ->where('sample_submission_request_id', $enquiry->id)
            ->where('quotation_header_id', $header->id)
            ->first();

        $channels = array_values(array_filter([
            $engagement?->sent_via_email ? 'Email' : null,
            $engagement?->sent_via_portal ? 'Customer portal' : null,
        ]));
        $how = $channels !== [] ? implode(' + ', $channels) : 'LIMS dispatch';

        return (object) [
            'source' => 'commercial',
            'title' => 'Quotation Sent',
            'subtitle' => $quoteLabel,
            'user_name' => $sender,
            'occurred_at' => $at,
            'badge' => 'primary',
            'comment' => sprintf(
                'Quotation %s approved and sent to customer via %s.',
                $quoteLabel,
                $how,
            ),
            'what' => 'Quotation delivered to customer',
            'how' => $how,
            'where' => 'Commercial / Billing',
            'metadata' => array_filter([
                'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                'sent_by' => $sender,
                'sent_via_email' => $engagement?->sent_via_email ? 'Yes' : null,
                'sent_via_portal' => $engagement?->sent_via_portal ? 'Yes' : null,
                'comments' => $logComments,
            ]),
            'is_completed' => true,
        ];
    }

    private function quotationSentAlreadyLogged(SampleSubmissionRequest $enquiry): bool
    {
        return QuotationApprovalLog::query()
            ->where(function ($query) use ($enquiry): void {
                $query->where('sample_submission_request_id', $enquiry->id);
                if (filled($enquiry->current_quotation_header_id)) {
                    $query->orWhere('quotation_header_id', $enquiry->current_quotation_header_id);
                }
            })
            ->where('action', QuotationApprovalLog::ACTION_SENT)
            ->exists();
    }

    /**
     * @return Collection<int, object>
     */
    private function commercialEngagementEvents(SubmissionFormInstance $instance, string $search): Collection
    {
        $events = collect();
        $enquiry = $instance->sampleSubmissionRequest;
        if (! $enquiry instanceof SampleSubmissionRequest) {
            return $events;
        }

        $skipSentEvent = $this->quotationSentAlreadyLogged($enquiry);

        $engagements = EnquiryQuotation::query()
            ->with(['quotationHeader.preparedBy'])
            ->where('sample_submission_request_id', $enquiry->id)
            ->orderBy('sent_to_customer_at')
            ->get();

        $readyForReceptionEmitted = false;

        foreach ($engagements as $engagement) {
            $quote = $engagement->quotationHeader;
            $quoteNumber = trim((string) ($quote?->quote_number ?? ''));
            $quoteLabel = $quoteNumber !== '' ? $quoteNumber : ('Quotation '.substr((string) $engagement->quotation_header_id, 0, 8));

            if ($engagement->sent_to_customer_at && ! $skipSentEvent) {
                $sender = $this->resolveUserName($engagement->linked_by_user_id)
                    ?: ($quote?->preparedBy?->name ?? 'System');
                $channels = array_values(array_filter([
                    $engagement->sent_via_email ? 'Email' : null,
                    $engagement->sent_via_portal ? 'Customer portal' : null,
                ]));
                $how = $channels !== [] ? implode(' + ', $channels) : 'LIMS dispatch';

                $action = sprintf(
                    'Quotation %s sent to customer via %s.',
                    $quoteLabel,
                    $how,
                );

                $event = (object) [
                    'source' => 'commercial',
                    'title' => 'Quotation Sent',
                    'subtitle' => $quoteLabel,
                    'user_name' => $sender,
                    'occurred_at' => Carbon::parse($engagement->sent_to_customer_at),
                    'badge' => 'primary',
                    'comment' => $action,
                    'what' => 'Quotation delivered to customer',
                    'how' => $how,
                    'where' => 'Commercial / Billing',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'sent_by' => $sender,
                        'sent_via_email' => $engagement->sent_via_email ? 'Yes' : null,
                        'sent_via_portal' => $engagement->sent_via_portal ? 'Yes' : null,
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($event, $search)) {
                    $events->push($event);
                }
            }

            if ($engagement->accepted_at) {
                $signer = trim((string) ($engagement->customer_acceptance_signer_name ?? ''));
                $contactName = $this->resolveContactName($engagement->customer_acceptance_contact_id);
                $who = $signer !== '' ? $signer : ($contactName ?? 'Customer contact');
                $channel = $this->formatAcceptanceChannel((string) ($engagement->customer_acceptance_channel ?? ''));
                $signedAt = $engagement->customer_acceptance_signed_at ?? $engagement->accepted_at;

                $action = sprintf(
                    'Quotation %s accepted by %s via %s.',
                    $quoteLabel,
                    $who,
                    $channel,
                );

                if (filled($enquiry->client_po_number)) {
                    $action .= ' Client PO: '.$enquiry->client_po_number.'.';
                }

                $acceptEvent = (object) [
                    'source' => 'commercial',
                    'title' => SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
                    'subtitle' => $quoteLabel,
                    'user_name' => $who,
                    'occurred_at' => Carbon::parse($signedAt),
                    'badge' => 'success',
                    'comment' => $action,
                    'what' => 'Customer accepted quotation',
                    'how' => $channel,
                    'where' => 'Commercial acceptance',
                    'metadata' => array_filter([
                        'quote_number' => $quoteNumber !== '' ? $quoteNumber : null,
                        'customer_contact' => $contactName,
                        'acceptance_channel' => $channel,
                        'signed_at' => Carbon::parse($signedAt)->format('Y-m-d H:i'),
                        'client_po_number' => filled($enquiry->client_po_number) ? (string) $enquiry->client_po_number : null,
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($acceptEvent, $search)) {
                    $events->push($acceptEvent);
                }

                if ($enquiry->quotation_accepted_at && ! $readyForReceptionEmitted) {
                    $readyEvent = $this->createReadyForReceptionEvent(
                        $instance,
                        $enquiry,
                        Carbon::parse($enquiry->quotation_accepted_at),
                        $this->resolveUserName($engagement->linked_by_user_id) ?: 'System',
                        $search,
                        $quoteNumber !== '' ? $quoteNumber : null,
                    );
                    if ($readyEvent !== null && $this->matchesSearch($readyEvent, $search)) {
                        $events->push($readyEvent);
                        $readyForReceptionEmitted = true;
                    }
                }
            }
        }

        return $events;
    }

    private function createReadyForReceptionEvent(
        SubmissionFormInstance $instance,
        SampleSubmissionRequest $enquiry,
        Carbon $at,
        string $userName,
        string $search,
        ?string $quoteNumber = null,
    ): ?object {
        $tabs = WorkflowBoard::receivingRequestTabs();
        $summary = $this->resolveRequestSummary($instance);
        $scheduleMeta = $this->resolveScheduleContext($instance);
        $isPlannerChannel = $this->isPlannerChannel($summary['channel_raw']);

        $actionParts = [
            sprintf(
                'Request %s ready for physical sample reception (%d sample(s), %d test(s)).',
                $summary['request_no'],
                $summary['sample_count'],
                $summary['test_count'],
            ),
        ];

        if ($quoteNumber !== null && $quoteNumber !== '') {
            $actionParts[] = 'Quotation '.$quoteNumber.' accepted.';
        }

        if (filled($enquiry->client_po_number)) {
            $actionParts[] = 'Client PO: '.$enquiry->client_po_number.'.';
        }

        if ($isPlannerChannel) {
            $actionParts[] = 'Origin: System Planner — collection schedule linked to this request.';
        }

        $metadata = array_filter(array_merge([
            'request_no' => $summary['request_no'],
            'client' => $summary['client'],
            'channel' => $summary['channel'],
            'number_of_samples' => $summary['sample_count'] > 0 ? $summary['sample_count'] : null,
            'number_of_tests' => $summary['test_count'] > 0 ? $summary['test_count'] : null,
            'quote_number' => $quoteNumber,
            'client_po_number' => filled($enquiry->client_po_number) ? (string) $enquiry->client_po_number : null,
        ], $isPlannerChannel ? $scheduleMeta : [], $isPlannerChannel ? $this->resolveCollectionDataSummary($enquiry) : []));

        return (object) [
            'source' => 'commercial',
            'title' => $tabs['ready_for_reception'] ?? 'Ready for Reception',
            'subtitle' => $isPlannerChannel ? 'System Planner · Scheduled collection' : 'Receiving pipeline',
            'user_name' => $userName,
            'occurred_at' => $at,
            'badge' => 'success',
            'comment' => implode(' ', $actionParts),
            'what' => 'Ready for Reception',
            'how' => $isPlannerChannel ? 'System Planner schedule handoff' : 'LIMS reception readiness',
            'where' => $isPlannerChannel ? 'System Planner → Samples Receiving' : 'Samples Receiving board',
            'metadata' => $metadata,
            'is_completed' => true,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function labWorksheetTimelineEvents(
        SubmissionFormInstance $instance,
        ?SampleHeader $batch,
        string $search,
    ): Collection {
        $events = collect();

        $query = LabSectionWorksheet::query()
            ->with(['labSection', 'generatedBy'])
            ->where(function ($q) use ($instance, $batch): void {
                $q->where('submission_form_instance_id', $instance->id);
                if ($batch !== null) {
                    $q->orWhere('sample_header_id', $batch->id);
                }
            })
            ->orderByDesc('issued_at');

        foreach ($query->get() as $worksheet) {
            $sectionName = (string) ($worksheet->labSection?->name ?? 'Lab section');
            $issuer = $worksheet->generatedBy?->name ?? 'System';
            $analystIds = is_array($worksheet->assigned_analyst_ids) ? $worksheet->assigned_analyst_ids : [];
            $analystNames = User::query()->whereIn('id', $analystIds)->pluck('name')->implode(', ');
            $snapshot = is_array($worksheet->test_snapshot) ? $worksheet->test_snapshot : [];
            $sampleLabels = [];
            $testLabels = [];
            foreach ($snapshot as $row) {
                $sample = trim((string) ($row['sample_label'] ?? ''));
                $test = trim((string) ($row['test_label'] ?? ''));
                if ($sample !== '') {
                    $sampleLabels[$sample] = true;
                }
                if ($test !== '') {
                    $testLabels[$test] = true;
                }
            }
            $sampleSummary = $sampleLabels !== [] ? implode(', ', array_keys($sampleLabels)) : null;
            $testSummary = $testLabels !== [] ? implode(', ', array_slice(array_keys($testLabels), 0, 8)) : null;
            if ($testLabels !== [] && count($testLabels) > 8) {
                $testSummary .= ' (+'.(count($testLabels) - 8).' more)';
            }

            if ($worksheet->issued_at) {
                $issued = (object) [
                    'source' => 'worksheet',
                    'title' => 'Worksheet Issued',
                    'subtitle' => $worksheet->worksheet_number,
                    'user_name' => $issuer,
                    'occurred_at' => Carbon::parse($worksheet->issued_at),
                    'badge' => 'primary',
                    'comment' => sprintf(
                        'Worksheet %s issued for %s with %d test(s). Assigned analyst(s): %s.%s%s',
                        $worksheet->worksheet_number,
                        $sectionName,
                        count($snapshot),
                        $analystNames !== '' ? $analystNames : 'None assigned',
                        $sampleSummary ? ' Sample(s): '.$sampleSummary.'.' : '',
                        $testSummary ? ' Test(s): '.$testSummary.'.' : '',
                    ),
                    'what' => 'Lab section worksheet issued',
                    'how' => filled($worksheet->excel_path) ? 'Excel worksheet' : 'PDF worksheet',
                    'where' => $sectionName,
                    'metadata' => array_filter([
                        'worksheet_number' => $worksheet->worksheet_number,
                        'lab_section' => $sectionName,
                        'assigned_analysts' => $analystNames !== '' ? $analystNames : null,
                        'test_count' => count($snapshot),
                        'samples' => $sampleSummary,
                        'tests' => $testSummary,
                        'downloaded_at' => $worksheet->downloaded_at?->toDateTimeString(),
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($issued, $search)) {
                    $events->push($issued);
                }
            }

            if ($worksheet->imported_at) {
                $imported = (object) [
                    'source' => 'worksheet',
                    'title' => 'Worksheet Results Imported',
                    'subtitle' => $worksheet->worksheet_number,
                    'user_name' => $issuer,
                    'occurred_at' => Carbon::parse($worksheet->imported_at),
                    'badge' => 'success',
                    'comment' => sprintf(
                        'Results imported from worksheet %s (%s).%s%s',
                        $worksheet->worksheet_number,
                        $sectionName,
                        $sampleSummary ? ' Sample(s): '.$sampleSummary.'.' : '',
                        $testSummary ? ' Test(s): '.$testSummary.'.' : '',
                    ),
                    'what' => 'Worksheet results imported into LIMS',
                    'how' => 'Excel upload',
                    'where' => $sectionName,
                    'metadata' => array_filter([
                        'worksheet_number' => $worksheet->worksheet_number,
                        'lab_section' => $sectionName,
                        'samples' => $sampleSummary,
                        'tests' => $testSummary,
                        'imported_at' => $worksheet->imported_at?->toDateTimeString(),
                    ]),
                    'is_completed' => true,
                ];

                if ($this->matchesSearch($imported, $search)) {
                    $events->push($imported);
                }
            }
        }

        return $events;
    }

    /**
     * @return Collection<int, object>
     */
    private function batchAssignmentTimelineEvents(SampleHeader $batch, string $search): Collection
    {
        $events = collect();

        $assignments = SampleHeaderUserAssignment::query()
            ->with(['fromUser', 'toUser', 'assignedBy', 'completedByUser'])
            ->where('sample_header_id', $batch->id)
            ->orderByDesc('created_at')
            ->get();

        foreach ($assignments as $assignment) {
            $toName = $assignment->toUser?->name ?? 'Assignee';
            $fromName = $assignment->fromUser?->name;
            $assignedBy = $assignment->assignedBy?->name ?? 'System';
            $title = $assignment->status === SampleHeaderUserAssignment::STATUS_COMPLETED
                ? 'Batch assignment completed'
                : 'Batch assigned to personnel';

            $action = $fromName
                ? sprintf('Batch reassigned from %s to %s by %s.', $fromName, $toName, $assignedBy)
                : sprintf('Batch assigned to %s by %s.', $toName, $assignedBy);

            $event = (object) [
                'source' => 'assignment',
                'title' => $title,
                'subtitle' => (string) ($batch->batch_code ?? 'Batch'),
                'user_name' => $assignedBy,
                'occurred_at' => Carbon::parse($assignment->completed_at ?? $assignment->created_at),
                'badge' => $assignment->status === SampleHeaderUserAssignment::STATUS_COMPLETED ? 'success' : 'warning',
                'comment' => filled($assignment->comment) ? $assignment->comment.' · '.$action : $action,
                'what' => $title,
                'how' => 'Personnel assignment',
                'where' => (string) ($batch->status ?? 'Laboratory workflow'),
                'metadata' => array_filter([
                    'assignee' => $toName,
                    'assigned_by' => $assignedBy,
                    'from_user' => $fromName,
                    'status' => $assignment->status,
                ]),
                'is_completed' => $assignment->status === SampleHeaderUserAssignment::STATUS_COMPLETED,
                'completed_by' => $assignment->completedByUser?->name,
                'completed_at' => $assignment->completed_at,
            ];

            if ($this->matchesSearch($event, $search)) {
                $events->push($event);
            }
        }

        $batch->loadMissing(['specialist_analyst']);
        if (filled($batch->verify_user_id)) {
            $verifier = $this->resolveUserName($batch->verify_user_id);
            $verifyEvent = (object) [
                'source' => 'assignment',
                'title' => 'Verifier designated',
                'subtitle' => 'Sample Verification',
                'user_name' => $verifier ?? 'System',
                'occurred_at' => Carbon::parse($batch->updated_at ?? now()),
                'badge' => 'info',
                'comment' => sprintf('Batch %s designated for verification by %s.', (string) ($batch->batch_code ?? ''), $verifier ?? 'verifier'),
                'what' => 'Verification assignee recorded',
                'how' => 'Batch workflow configuration',
                'where' => 'Sample Verification',
                'metadata' => array_filter([
                    'batch_code' => (string) ($batch->batch_code ?? ''),
                    'verifier' => $verifier,
                ]),
                'is_completed' => (string) $batch->status !== 'Sample Verification',
            ];

            if ($this->matchesSearch($verifyEvent, $search)) {
                $events->push($verifyEvent);
            }
        }

        return $events;
    }

    /**
     * @return Collection<int, object>
     */
    private function batchAnalysisTimingEvents(SampleHeader $batch, string $search): Collection
    {
        $events = collect();

        $results = CapturedResult::query()
            ->with(['sample', 'my_analyte', 'labSection', 'operator'])
            ->where('sample_header_id', $batch->id)
            ->whereNotNull('result')
            ->where('result', '!=', '')
            ->get();

        if ($results->isEmpty()) {
            return $events;
        }

        $overallStart = $results->min(fn ($r) => $r->updated_at ?? $r->created_at);
        $overallEnd = $results->max(fn ($r) => $r->updated_at ?? $r->created_at);

        if ($overallStart && $overallEnd) {
            $jobEvent = (object) [
                'source' => 'analysis',
                'title' => 'Job analysis window',
                'subtitle' => (string) ($batch->batch_code ?? 'Batch'),
                'user_name' => 'Laboratory',
                'occurred_at' => Carbon::parse($overallEnd),
                'badge' => 'primary',
                'comment' => sprintf(
                    'First result captured %s · Last result captured %s · %d test result(s) recorded across the job.',
                    Carbon::parse($overallStart)->format('Y-m-d H:i'),
                    Carbon::parse($overallEnd)->format('Y-m-d H:i'),
                    $results->count(),
                ),
                'what' => 'Analysis activity summary for batch/job',
                'how' => 'Captured results aggregate',
                'where' => (string) ($batch->status ?? 'Laboratory'),
                'metadata' => array_filter([
                    'analysis_start' => Carbon::parse($overallStart)->format('Y-m-d H:i'),
                    'analysis_end' => Carbon::parse($overallEnd)->format('Y-m-d H:i'),
                    'results_count' => $results->count(),
                    'batch_code' => (string) ($batch->batch_code ?? ''),
                ]),
                'is_completed' => true,
            ];

            if ($this->matchesSearch($jobEvent, $search)) {
                $events->push($jobEvent);
            }
        }

        foreach ($results->groupBy('sample_detail_id') as $sampleId => $sampleResults) {
            $sample = $sampleResults->first()?->sample;
            $sampleCode = (string) ($sample?->sample_code ?? $sampleId);
            $start = $sampleResults->min(fn ($r) => $r->updated_at ?? $r->created_at);
            $end = $sampleResults->max(fn ($r) => $r->updated_at ?? $r->created_at);
            $operators = $sampleResults->map(fn ($r) => $r->operator?->name)->filter()->unique()->implode(', ');

            $sampleEvent = (object) [
                'source' => 'analysis',
                'title' => 'Sample analysis window',
                'subtitle' => $sampleCode,
                'user_name' => $operators !== '' ? $operators : 'Laboratory',
                'occurred_at' => Carbon::parse($end),
                'badge' => 'info',
                'comment' => sprintf(
                    'Sample %s: %d test(s) · analysis from %s to %s · personnel: %s.',
                    $sampleCode,
                    $sampleResults->count(),
                    Carbon::parse($start)->format('Y-m-d H:i'),
                    Carbon::parse($end)->format('Y-m-d H:i'),
                    $operators !== '' ? $operators : 'Not recorded',
                ),
                'what' => 'Sample-level analysis summary',
                'how' => 'Result capture timestamps',
                'where' => (string) ($batch->status ?? 'Laboratory'),
                'metadata' => array_filter([
                    'sample_code' => $sampleCode,
                    'analysis_start' => Carbon::parse($start)->format('Y-m-d H:i'),
                    'analysis_end' => Carbon::parse($end)->format('Y-m-d H:i'),
                    'tests_completed' => $sampleResults->count(),
                    'personnel' => $operators !== '' ? $operators : null,
                ]),
                'is_completed' => true,
            ];

            if ($this->matchesSearch($sampleEvent, $search)) {
                $events->push($sampleEvent);
            }
        }

        return $events;
    }

    /**
     * @return Collection<int, object>
     */
    private function batchRecordAuditEvents(SampleHeader $batch, string $search): Collection
    {
        $events = collect();

        if (! method_exists($batch, 'audits')) {
            return $events;
        }

        foreach ($batch->audits()->with('user')->latest()->limit(50)->get() as $audit) {
            $userName = $audit->user?->name ?? 'System';
            $changed = is_array($audit->new_values) ? array_keys($audit->new_values) : [];
            $fields = $changed !== [] ? implode(', ', array_slice($changed, 0, 8)) : 'record';

            $event = (object) [
                'source' => 'audit_batch',
                'title' => 'Job / batch record updated',
                'subtitle' => (string) ($batch->batch_code ?? 'Batch'),
                'user_name' => $userName,
                'occurred_at' => Carbon::parse($audit->created_at),
                'badge' => 'warning',
                'comment' => sprintf('Batch fields updated: %s.', $fields),
                'what' => ucfirst((string) ($audit->event ?? 'updated')).' batch record',
                'how' => 'LIMS audit trail',
                'where' => (string) ($batch->status ?? 'Batch record'),
                'metadata' => array_filter([
                    'fields_changed' => $fields,
                    'batch_code' => (string) ($batch->batch_code ?? ''),
                ]),
                'is_completed' => true,
            ];

            if ($this->matchesSearch($event, $search)) {
                $events->push($event);
            }
        }

        return $events;
    }

    /**
     * @param  Collection<int, object>  $events
     * @return Collection<int, object>
     */
    private function finalizeTimeline(Collection $events): Collection
    {
        return $events
            ->filter(fn ($event) => $event->occurred_at !== null)
            ->sortByDesc(fn ($event) => $event->occurred_at->getTimestamp())
            ->values();
    }

    private function matchesSearch(object $event, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $metadata = is_array($event->metadata ?? null) ? $event->metadata : [];
        $searchable = strtolower(implode(' ', array_filter([
            (string) ($event->title ?? ''),
            (string) ($event->subtitle ?? ''),
            (string) ($event->user_name ?? ''),
            (string) ($event->comment ?? ''),
            (string) ($event->what ?? ''),
            (string) ($event->how ?? ''),
            (string) ($event->why ?? ''),
            (string) ($event->where ?? ''),
            implode(' ', array_map('strval', $metadata)),
        ])));

        return str_contains($searchable, strtolower($search));
    }

    private function buildWorkflowActionNarrative(SampleWorkflowEvent $record): string
    {
        $meta = is_array($record->metadata) ? $record->metadata : [];
        $parts = [];

        switch ((string) ($record->event_type ?? '')) {
            case 'result_captured':
                $parts[] = sprintf(
                    'Result captured for sample %s · test %s · value %s.',
                    $meta['sample_code'] ?? '—',
                    $meta['test'] ?? '—',
                    $meta['result'] ?? '—',
                );
                break;
            case 'worksheet_issued':
                $parts[] = sprintf(
                    'Worksheet %s issued (%d tests).',
                    $meta['worksheet_number'] ?? '—',
                    (int) ($meta['test_count'] ?? 0),
                );
                break;
            case 'worksheet_imported':
                $parts[] = sprintf(
                    'Worksheet %s results imported (%d rows updated).',
                    $meta['worksheet_number'] ?? '—',
                    (int) ($meta['updated_rows'] ?? 0),
                );
                break;
            case 'samples_accepted':
                $parts[] = sprintf(
                    'Samples accepted into batch %s (%d sample(s)).',
                    $meta['batch_code'] ?? '—',
                    (int) ($meta['number_of_samples'] ?? 0),
                );
                break;
            case 'integrity_assignments_saved':
                $parts[] = sprintf(
                    'Integrity check assignments saved (%d sample(s), %d test row(s)).',
                    (int) ($meta['samples_touched'] ?? 0),
                    (int) ($meta['tests_touched'] ?? 0),
                );
                break;
            case 'workflow_stage_changed':
                $parts[] = sprintf(
                    'Workflow stage changed to %s.',
                    (string) ($record->where ?? $record->workflow_stage ?? 'next stage'),
                );
                break;
            default:
                $parts[] = (string) ($record->what ?? 'Workflow activity recorded.');
        }

        if (filled($record->how)) {
            $parts[] = 'Method: '.$record->how.'.';
        }

        if (filled($record->where)) {
            $parts[] = 'Location: '.$record->where.'.';
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * @return array<string, scalar|null>
     */
    private function normalizeWorkflowMetadata(SampleWorkflowEvent $record): array
    {
        $meta = is_array($record->metadata) ? $record->metadata : [];

        return array_filter([
            'event_type' => (string) ($record->event_type ?? ''),
            'workflow_stage' => (string) ($record->workflow_stage ?? ''),
            ...$meta,
        ], fn ($value) => filled($value));
    }

    private function buildAuditActionNarrative(
        SubmissionFormAuditLog $log,
        SubmissionFormInstance $instance,
        mixed $statusTo,
    ): string {
        $status = is_string($statusTo) && $statusTo !== ''
            ? $statusTo
            : (string) $instance->status;

        $base = match ($log->action) {
            'submitted' => 'Request submitted into Samples Receiving.',
            'created' => 'Request draft created.',
            'received' => 'Physical samples received at laboratory.',
            'approved' => 'Request accepted by laboratory.',
            'sent_for_analyst_review' => 'Request sent for analyst review.',
            default => 'Request activity recorded.',
        };

        $notes = filled($log->notes) ? ' Notes: '.$log->notes : '';

        return $base.' Status: '.$status.'.'.$notes;
    }

    private function formatAcceptanceChannel(string $channel): string
    {
        return match ($channel) {
            QuotationHeader::ACCEPTANCE_CHANNEL_WALK_IN => 'Recorded in LIMS (request view / walk-in)',
            QuotationHeader::ACCEPTANCE_CHANNEL_PORTAL => 'Customer portal',
            QuotationHeader::ACCEPTANCE_CHANNEL_EMAIL => 'Email acceptance link',
            default => $channel !== '' ? ucwords(str_replace('_', ' ', $channel)) : 'LIMS',
        };
    }

    private function resolveUserName(?string $userId): ?string
    {
        $userId = trim((string) ($userId ?? ''));
        if ($userId === '') {
            return null;
        }

        return User::query()->whereKey($userId)->value('name');
    }

    private function resolveContactName(?string $contactId): ?string
    {
        $contactId = trim((string) ($contactId ?? ''));
        if ($contactId === '') {
            return null;
        }

        $contact = CustomerContact::query()->find($contactId);
        if ($contact === null) {
            return null;
        }

        return trim(implode(' ', array_filter([
            $contact->first_name,
            $contact->middle_name,
            $contact->last_name,
        ]))) ?: (string) ($contact->email ?? null);
    }

    /**
     * @return array{
     *     request_no: string,
     *     client: string,
     *     channel: string,
     *     channel_raw: string,
     *     sample_count: int,
     *     test_count: int
     * }
     */
    private function resolveRequestSummary(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing(['crmCustomer', 'sampleSubmissionRequest', 'submittedBy']);
        $enquiry = $instance->sampleSubmissionRequest;

        $requestNo = trim((string) (
            $instance->getDocumentControlNumber()
            ?? $instance->form_number
            ?? ($enquiry !== null ? $enquiry->formatted_number : '')
            ?? ''
        ));

        if ($requestNo === '') {
            $requestNo = '—';
        }

        $client = trim((string) ($instance->crmCustomer?->name ?? ''));
        if ($client === '' && $enquiry !== null && filled($enquiry->crm_customer_id)) {
            $client = trim((string) (\App\Models\CRM\CRMCustomer::query()
                ->whereKey($enquiry->crm_customer_id)
                ->value('name') ?? ''));
        }
        if ($client === '') {
            $client = '—';
        }

        $channelRaw = $instance->receivingOriginChannel();
        $sampleCount = $this->resolveSampleCount($instance, $enquiry);
        $testCount = $this->resolveTestCount($enquiry);

        return [
            'request_no' => $requestNo,
            'client' => $client,
            'channel' => $this->formatReceivingChannel($channelRaw),
            'channel_raw' => $channelRaw,
            'sample_count' => $sampleCount,
            'test_count' => $testCount,
        ];
    }

    private function resolveSampleCount(SubmissionFormInstance $instance, ?SampleSubmissionRequest $enquiry): int
    {
        if ($enquiry !== null) {
            $configCount = count(is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : []);
            $lineCount = count(is_array($enquiry->sample_lines) ? $enquiry->sample_lines : []);
            $stored = (int) ($enquiry->number_of_samples ?? 0);

            return max($stored, $configCount, $lineCount, 0);
        }

        if ($instance->sampling_schedule_id) {
            $instance->loadMissing('samplingSchedule');
            $planned = (int) ($instance->samplingSchedule?->number_of_samples ?? 0);
            if ($planned > 0) {
                return $planned;
            }
        }

        return 0;
    }

    private function resolveTestCount(?SampleSubmissionRequest $enquiry): int
    {
        if ($enquiry === null) {
            return 0;
        }

        $lines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        $count = 0;

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            if (filled($line['analysis_element_id'] ?? null)) {
                $count++;

                continue;
            }

            $parameters = $line['parameters'] ?? $line['parameter_ids'] ?? $line['analysis_element_ids'] ?? [];
            if (is_array($parameters)) {
                $count += count(array_filter($parameters, fn ($value) => filled($value)));

                continue;
            }

            if (filled($line['analysis_type_id'] ?? null)) {
                $count++;
            }
        }

        if ($count > 0) {
            return $count;
        }

        $configs = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        foreach ($configs as $config) {
            if (! is_array($config)) {
                continue;
            }

            $tests = $config['tests'] ?? $config['analysis_elements'] ?? $config['parameters'] ?? [];
            if (is_array($tests)) {
                $count += count($tests);
            }
        }

        return $count;
    }

    private function formatReceivingChannel(string $channel): string
    {
        return match (strtolower(trim($channel))) {
            'portal' => 'Customer portal',
            'walk_in' => 'Walk-in / LIMS',
            'offline' => 'Offline intake',
            'scheduled', 'scheduled_sampling' => 'System Planner (scheduled sampling)',
            default => $channel !== '' ? ucwords(str_replace('_', ' ', $channel)) : 'LIMS',
        };
    }

    private function isPlannerChannel(string $channelRaw): bool
    {
        return in_array(strtolower(trim($channelRaw)), ['scheduled', 'scheduled_sampling'], true);
    }

    /**
     * @return array<string, scalar|null>
     */
    private function resolveScheduleContext(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing([
            'samplingSchedule.samplePoint',
            'samplingSchedule.sample_type',
            'samplingSchedule.analysis_type',
            'samplingSchedule.client',
        ]);

        $schedule = $instance->samplingSchedule;
        if ($schedule === null) {
            return [];
        }

        $progress = $schedule->collectionProgress();

        return array_filter([
            'schedule_title' => filled($schedule->title) ? (string) $schedule->title : null,
            'scheduled_datetime' => $schedule->sampling_datetime?->format('Y-m-d H:i'),
            'collection_location' => $schedule->locationDisplayName(),
            'sample_point' => $schedule->samplePoint?->display_name,
            'planned_samples' => (int) ($schedule->number_of_samples ?? 0) > 0
                ? (int) $schedule->number_of_samples
                : null,
            'frequency' => filled($schedule->frequency) ? (string) $schedule->frequency : null,
            'assigned_personnel' => $schedule->personnelNames() !== 'N/A' ? $schedule->personnelNames() : null,
            'sample_type' => $schedule->sample_type?->name,
            'analysis_type' => $schedule->analysis_type?->name,
            'collection_progress' => sprintf(
                '%d of %d collected (%s)',
                (int) ($progress['collected'] ?? 0),
                (int) ($progress['scheduled'] ?? 0),
                (string) ($progress['label'] ?? $schedule->collectionStatus()),
            ),
            'collection_status' => (string) ($progress['label'] ?? $schedule->collectionStatus()),
            'recurring_series' => $schedule->isPartOfRecurringSeries() ? 'Yes' : null,
            'schedule_notes' => filled($schedule->description) ? (string) $schedule->description : null,
        ]);
    }

    /**
     * @return array<string, scalar|null>
     */
    private function resolveCollectionDataSummary(?SampleSubmissionRequest $enquiry): array
    {
        if ($enquiry === null) {
            return [];
        }

        $data = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];
        if ($data === []) {
            return [];
        }

        $summary = [];
        $interestingKeys = [
            'sampling_date' => 'Planned sampling date',
            'sampling_time' => 'Planned sampling time',
            'sampling_location' => 'Sampling location',
            'collection_date' => 'Collection date',
            'collection_time' => 'Collection time',
            'collection_location' => 'Collection location',
            'sampler_name' => 'Sampler',
            'personnel_name' => 'Assigned personnel',
            'sample_point' => 'Sample point',
            'site_name' => 'Site',
            'vessel_name' => 'Vessel',
            'tank_number' => 'Tank / compartment',
        ];

        foreach ($interestingKeys as $key => $label) {
            $value = $data[$key] ?? null;
            if (is_scalar($value) && filled($value)) {
                $summary[str_replace(' ', '_', strtolower($label))] = (string) $value;
            }
        }

        return $summary;
    }
}
