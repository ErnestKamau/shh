<?php

namespace App\Services\SubmissionForm;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceIntray;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmissionFormIntrayService
{
    public function assignToUser(
        string $instanceId,
        string $toUserId,
        ?string $comment,
        User $actor
    ): SubmissionFormInstanceIntray {
        $instance = $this->resolveIntrayInstance($instanceId);
        $assignee = $this->resolveAssignableUser($toUserId);

        if ((string) $actor->id === (string) $assignee->id) {
            throw ValidationException::withMessages([
                'assigneeUserId' => 'You cannot move a request to your own intray.',
            ]);
        }

        return DB::transaction(function () use ($instance, $assignee, $comment, $actor) {
            $existingPending = SubmissionFormInstanceIntray::query()
                ->where('submission_form_instance_id', $instance->id)
                ->pending()
                ->latest('created_at')
                ->first();

            if ($existingPending !== null) {
                $existingPending->update([
                    'status' => SubmissionFormInstanceIntray::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'completed_by' => $actor->id,
                ]);
            }

            $fromUserId = $existingPending !== null
                ? $existingPending->to_user_id
                : $actor->id;

            $intray = SubmissionFormInstanceIntray::query()->create([
                'submission_form_instance_id' => $instance->id,
                'from_user_id' => $fromUserId,
                'to_user_id' => $assignee->id,
                'assigned_by_user_id' => $actor->id,
                'comment' => $comment !== null && trim($comment) !== '' ? trim($comment) : null,
                'status' => SubmissionFormInstanceIntray::STATUS_PENDING,
            ]);

            $instance->logAction(
                'intray_assigned',
                $actor,
                [
                    'to_user_id' => $assignee->id,
                    'to_user_name' => $assignee->name,
                ],
                $intray->comment
            );

            return $intray;
        });
    }

    public function completePending(string $instanceId, User $actor): SubmissionFormInstanceIntray
    {
        $instance = $this->resolveIntrayInstance($instanceId);

        $pending = SubmissionFormInstanceIntray::query()
            ->where('submission_form_instance_id', $instance->id)
            ->pending()
            ->forAssignee((string) $actor->id)
            ->latest('created_at')
            ->first();

        if ($pending === null) {
            throw ValidationException::withMessages([
                'intray' => 'No pending intray assignment found for you on this request.',
            ]);
        }

        $pending->update([
            'status' => SubmissionFormInstanceIntray::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $actor->id,
        ]);

        $instance->logAction('intray_completed', $actor, null, $pending->comment);

        return $pending->fresh(['toUser', 'fromUser', 'assignedBy']);
    }

    /**
     * @return Collection<int, SubmissionFormInstanceIntray>
     */
    public function getPendingForUser(string $userId): Collection
    {
        return SubmissionFormInstanceIntray::query()
            ->pending()
            ->forAssignee($userId)
            ->with([
                'submissionFormInstance.submissionForm',
                'submissionFormInstance.crmCustomer',
                'submissionFormInstance.submittedBy',
                'fromUser',
                'toUser',
                'assignedBy',
            ])
            ->latest('created_at')
            ->get();
    }

    private function resolveIntrayInstance(string $instanceId): SubmissionFormInstance
    {
        $instance = SubmissionFormInstance::query()
            ->with('submissionForm')
            ->find($instanceId);

        if ($instance === null) {
            throw ValidationException::withMessages([
                'instance' => 'The selected submission request was not found.',
            ]);
        }

        if (($instance->submissionForm->form_type ?? '') !== 'template') {
            throw ValidationException::withMessages([
                'instance' => 'Only template submission requests can be moved to an intray.',
            ]);
        }

        $onReceivingQueue = in_array($instance->status, array_keys(WorkflowBoard::receivingRequestTabs()), true);
        $onRequestReviewQueue = SubmissionFormInstance::query()
            ->whereKey($instance->id)
            ->inRequestReviewQueue()
            ->exists();

        if (! $onReceivingQueue && ! $onRequestReviewQueue) {
            throw ValidationException::withMessages([
                'instance' => 'This request is not in the Samples Receiving or Samples Request Review workflow queue.',
            ]);
        }

        return $instance;
    }

    private function resolveAssignableUser(string $userId): User
    {
        $user = User::query()
            ->where('id', $userId)
            ->where('active', 1)
            ->where('is_client', 0)
            ->whereNull('supplier_id')
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'assigneeUserId' => 'Select a valid active lab user.',
            ]);
        }

        return $user;
    }
}
