<?php

namespace App\Services\Sampleworkflow;

use App\ChainOfCustody;
use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormAuditLog;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
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
                ->concat($this->receivingPipelineEvents($instance, $batch, $search));
        }

        $events = $events->concat($this->batchCustodyEvents($batch, $search));

        return $events
            ->filter(fn ($event) => $event->occurred_at !== null)
            ->sortByDesc(fn ($event) => $event->occurred_at->getTimestamp())
            ->values();
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
                'comment' => $log->notes,
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
        SampleHeader $batch,
        string $search,
    ): Collection {
        $events = collect();
        $enquiry = $instance->sampleSubmissionRequest;
        $tabs = WorkflowBoard::receivingRequestTabs();

        $milestones = [];

        if ($instance->created_at) {
            $milestones[] = [
                'key' => 'submitted',
                'title' => $tabs['submitted'] ?? 'Submitted Requests',
                'at' => Carbon::parse($instance->created_at),
                'user' => $instance->submittedBy?->name ?? 'System',
                'comment' => 'Request entered Samples Receiving.',
                'badge' => 'info',
            ];
        }

        if ($enquiry instanceof SampleSubmissionRequest) {
            if ($enquiry->quotation_first_sent_to_customer_at) {
                $milestones[] = [
                    'key' => 'quotation_sent',
                    'title' => 'Quotation Sent',
                    'at' => Carbon::parse($enquiry->quotation_first_sent_to_customer_at),
                    'user' => 'System',
                    'comment' => null,
                    'badge' => 'primary',
                ];
            }

            if ($enquiry->quotation_accepted_at) {
                $milestones[] = [
                    'key' => 'ready_for_reception',
                    'title' => $tabs['ready_for_reception'] ?? 'Ready for Reception',
                    'at' => Carbon::parse($enquiry->quotation_accepted_at),
                    'user' => 'System',
                    'comment' => 'Quotation accepted — ready for laboratory reception.',
                    'badge' => 'success',
                ];

                $milestones[] = [
                    'key' => 'accepted',
                    'title' => $tabs['accepted'] ?? 'Accepted',
                    'at' => Carbon::parse($enquiry->quotation_accepted_at)->addSecond(),
                    'user' => 'System',
                    'comment' => 'Commercial enquiry accepted.',
                    'badge' => 'success',
                ];
            }
        }

        if ($batch->created_at) {
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
            if ($milestone['key'] === 'request_review' && $this->hasBatchCustodyStage($batch, 'Samples Request Review')) {
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
                'is_completed' => true,
                'completed_by' => $milestone['user'],
                'completed_at' => $milestone['at'],
            ]);
        }

        return $events;
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
                'comment' => $custody->comments,
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
    private function existingEventTitles(SubmissionFormInstance $instance, SampleHeader $batch): array
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

        foreach (ChainOfCustody::query()->where('sample_header_id', $batch->id)->pluck('workflow_stage') as $stage) {
            if (is_string($stage) && $stage !== '') {
                $titles[strtolower($stage)] = true;
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
}
