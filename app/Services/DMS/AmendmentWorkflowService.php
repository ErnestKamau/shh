<?php

namespace App\Services\DMS;

use App\User;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentAmendment;
use App\Models\DMS\DocumentAuditLog;
use App\Notifications\DMS\AmendmentRequestedNotification;
use App\Notifications\DMS\AmendmentAuthorizedNotification;
use App\Notifications\DMS\AmendmentApprovedNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class AmendmentWorkflowService
{
    /**
     * Process an amendment request
     *
     * @param Document $document
     * @param string $reason
     * @param string|null $description
     * @param User $requester
     * @return DocumentAmendment
     */
    public function processAmendmentRequest(
        Document $document,
        string $reason,
        ?string $description,
        User $requester
    ): DocumentAmendment {
        $amendmentNumber = $document->amendments()->count() + 1;

        $amendment = DocumentAmendment::create([
            'document_id' => $document->id,
            'amendment_number' => $amendmentNumber,
            'amendment_reason' => $reason,
            'amendment_description' => $description,
            'requested_by' => $requester->id,
            'requested_at' => now(),
            'status' => 'requested',
        ]);

        // Log the action
        DocumentAuditLog::log(
            $amendment,
            'created',
            null,
            $amendment->toArray(),
            "Amendment request #{$amendmentNumber} created by {$requester->name}"
        );

        // Notify users with authorization permission
        $this->notifyStakeholders($amendment, 'requested');

        return $amendment;
    }

    /**
     * Execute a workflow step (authorize/approve)
     *
     * @param DocumentAmendment $amendment
     * @param string $step
     * @param User $user
     * @param bool $approved
     * @param string|null $comment
     * @return bool
     */
    public function executeWorkflowStep(
        DocumentAmendment $amendment,
        string $step,
        User $user,
        bool $approved,
        ?string $comment = null
    ): bool {
        if ($step === 'authorization') {
            if ($approved) {
                $result = $amendment->authorize($comment);
                if ($result) {
                    $this->notifyStakeholders($amendment, 'authorized');
                }
            } else {
                $result = $amendment->reject($comment, 'authorization');
                if ($result) {
                    $this->notifyStakeholders($amendment, 'rejected');
                }
            }
        } elseif ($step === 'approval') {
            if ($approved) {
                $result = $amendment->approve($comment);
                if ($result) {
                    $amendment->document->incrementAmendmentCount();
                    $this->notifyStakeholders($amendment, 'approved');
                }
            } else {
                $result = $amendment->reject($comment, 'approval');
                if ($result) {
                    $this->notifyStakeholders($amendment, 'rejected');
                }
            }
        } else {
            return false;
        }

        // Log the action
        DocumentAuditLog::log(
            $amendment,
            $approved ? 'approved' : 'rejected',
            null,
            $amendment->fresh()->toArray(),
            "{$step} " . ($approved ? 'approved' : 'rejected') . " by {$user->name}"
        );

        return $result;
    }

    /**
     * Upload amended file
     *
     * @param DocumentAmendment $amendment
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    public function uploadAmendedFile(DocumentAmendment $amendment, $file): bool
    {
        $document = $amendment->document;

        // Store the file before amendment
        $beforePath = $document->file_path;
        $amendment->update(['file_before_path' => $beforePath]);

        // Store the new file
        $path = Storage::disk('dms')->putFile(
            "documents/{$document->document_type_id}/{$document->id}",
            $file
        );

        $amendment->update([
            'file_after_path' => $path,
            'amended_by' => auth()->id(),
            'amended_at' => now(),
            'status' => 'amended',
        ]);

        // Update the document's file
        $document->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        // Log the action
        DocumentAuditLog::log(
            $amendment,
            'amended',
            ['file_path' => $beforePath],
            ['file_path' => $path],
            "File uploaded for amendment #{$amendment->amendment_number}"
        );

        return true;
    }

    /**
     * Notify stakeholders about amendment status changes
     *
     * @param DocumentAmendment $amendment
     * @param string $event
     * @return void
     */
    protected function notifyStakeholders(DocumentAmendment $amendment, string $event): void
    {
        $document = $amendment->document;

        switch ($event) {
            case 'requested':
                // Notify users with authorization permission
                $notification = new AmendmentRequestedNotification($amendment);
                // You would query users with 'authorize_amendment' permission here
                break;

            case 'authorized':
                // Notify requester and users who can approve
                $notification = new AmendmentAuthorizedNotification($amendment);
                Notification::send($amendment->requester, $notification);
                break;

            case 'approved':
                // Notify requester and document owner
                $notification = new AmendmentApprovedNotification($amendment);
                Notification::send($amendment->requester, $notification);
                if ($document->owner_id !== $amendment->requested_by) {
                    Notification::send($document->owner, $notification);
                }
                break;

            case 'rejected':
                // Notify requester
                Notification::send($amendment->requester, new AmendmentApprovedNotification($amendment));
                break;
        }
    }

    /**
     * Get amendment workflow status
     *
     * @param DocumentAmendment $amendment
     * @return array
     */
    public function getWorkflowStatus(DocumentAmendment $amendment): array
    {
        return [
            'current_step' => $amendment->status,
            'requested' => $amendment->requested_at !== null,
            'authorized' => $amendment->authorized_at !== null,
            'amended' => $amendment->amended_at !== null,
            'approved' => $amendment->approved_at !== null,
            'can_authorize' => $amendment->status === 'requested',
            'can_amend' => $amendment->status === 'authorized',
            'can_approve' => $amendment->status === 'amended',
        ];
    }
}

