<?php

namespace App\Services\Commercial;

use App\Models\QuotationApprovalLog;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class QuotationApprovalService
{
    public const HEADER_STATUS_IN_PREPARATION = 'Quote In Preparation';

    public const HEADER_STATUS_IN_APPROVAL = 'Quote In Approval';

    public const HEADER_STATUS_COMPLETE = 'Quote Complete';

    /**
     * @return Collection<int, User>
     */
    public function eligibleLabManagers(?string $excludeUserId = null): Collection
    {
        $query = User::query()
            ->role('Lab Manager')
            ->where('active', 1)
            ->orderBy('name');

        if ($excludeUserId !== null && $excludeUserId !== '') {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->get();
    }

    public function submitForApproval(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        string $labManagerId,
        bool $notifyEmail = true,
        ?string $comments = null,
    ): SampleSubmissionRequest {
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to submit a quotation for approval.');
        }

        $manager = $this->resolveLabManager($labManagerId);
        if ((string) $manager->id === (string) $actor->id) {
            throw ValidationException::withMessages([
                'labManagerId' => 'You cannot assign yourself as the quotation approver.',
            ]);
        }

        if ($header->details()->count() < 1) {
            throw new RuntimeException('Add at least one quotation line before sending for approval.');
        }

        // Generate PDF before the status transaction so Dompdf cannot hold DB locks.
        if (empty($header->upload_url)) {
            try {
                $header = app(QuotationFromEnquiryService::class)->generatePdf($header);
            } catch (\Throwable $exception) {
                report($exception);
                throw new RuntimeException(
                    'Could not generate the quotation PDF before sending for approval. '.$exception->getMessage(),
                    0,
                    $exception,
                );
            }
        }

        $submitted = DB::transaction(function () use ($enquiry, $header, $manager, $actor, $comments): SampleSubmissionRequest {
            $now = now();
            $header->status = self::HEADER_STATUS_IN_APPROVAL;
            $header->approved_by = (string) $manager->id;
            $header->is_approved = 0;
            $header->is_complete = 0;
            $header->approval_requested_at = $now;
            $header->approval_requested_by = (string) $actor->id;
            $header->approval_decision_at = null;
            $header->approval_comments = $comments !== null && trim($comments) !== '' ? trim($comments) : null;
            $header->sample_submission_request_id = $enquiry->id;
            $header->from_enquiry = true;
            $header->save();

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL;
            $enquiry->save();

            $this->writeLog(
                $header,
                $enquiry,
                QuotationApprovalLog::ACTION_SUBMITTED,
                (string) $actor->id,
                (string) $manager->id,
                $header->approval_comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        if ($notifyEmail && filled($manager->email)) {
            try {
                $subject = 'Quotation '.$header->quote_number.' awaiting your approval';
                $message = 'Hi '.$manager->name.',<br><br>'
                    .$actor->name.' submitted quotation <strong>'.$header->quote_number.'</strong> for approval.<br>'
                    .'Open the request from your personal dashboard to preview and approve.';
                notify_user($message, $manager->email, $subject);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $submitted;
    }

    public function approve(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        ?string $comments = null,
    ): SampleSubmissionRequest {
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to approve a quotation.');
        }

        $this->assertAssignedApprover($header, (string) $actor->id);

        if ((string) $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL
            && (string) $header->status !== self::HEADER_STATUS_IN_APPROVAL) {
            throw new RuntimeException('This quotation is not awaiting approval.');
        }

        $approved = DB::transaction(function () use ($enquiry, $header, $actor, $comments): SampleSubmissionRequest {
            $header->status = self::HEADER_STATUS_COMPLETE;
            $header->is_approved = 1;
            $header->is_complete = 1;
            $header->approval_decision_at = now();
            $header->approval_comments = $comments !== null && trim($comments) !== '' ? trim($comments) : null;
            $header->save();

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND;
            $enquiry->save();

            $this->writeLog(
                $header,
                $enquiry,
                QuotationApprovalLog::ACTION_APPROVED,
                (string) $actor->id,
                (string) $header->approved_by,
                $header->approval_comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        // Notify outside the transaction so SMTP delays cannot block approval.
        // PDF is not regenerated here — preview/send regenerate when needed.
        $this->notifyRequesterOfDecision(
            $approved->currentQuotation ?? $header,
            $actor,
            approved: true,
        );

        return $approved;
    }

    public function reject(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        string $comments,
    ): SampleSubmissionRequest {
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to reject a quotation.');
        }

        $this->assertAssignedApprover($header, (string) $actor->id);

        $comments = trim($comments);
        if ($comments === '') {
            throw ValidationException::withMessages([
                'approvalComments' => 'Please provide a rejection comment.',
            ]);
        }

        $rejected = DB::transaction(function () use ($enquiry, $header, $actor, $comments): SampleSubmissionRequest {
            $previousAssignee = (string) ($header->approved_by ?? '');

            $header->status = self::HEADER_STATUS_IN_PREPARATION;
            $header->is_approved = 0;
            $header->is_complete = 0;
            $header->approval_decision_at = now();
            $header->approval_comments = $comments;
            $header->save();

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();

            $this->writeLog(
                $header,
                $enquiry,
                QuotationApprovalLog::ACTION_REJECTED,
                (string) $actor->id,
                $previousAssignee !== '' ? $previousAssignee : null,
                $comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        $this->notifyRequesterOfDecision(
            $rejected->currentQuotation ?? $header,
            $actor,
            approved: false,
            comments: $comments,
        );

        return $rejected;
    }

    public function assertReadyToSend(QuotationHeader $header): void
    {
        if ((int) $header->is_approved !== 1) {
            throw new RuntimeException('Quotation must be approved by a lab manager before sending to the customer.');
        }
    }

    public function isPendingApproval(SampleSubmissionRequest $enquiry, ?QuotationHeader $header = null): bool
    {
        $header ??= $enquiry->currentQuotation;

        return (string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL
            || ($header !== null && (string) $header->status === self::HEADER_STATUS_IN_APPROVAL && (int) $header->is_approved !== 1);
    }

    public function isApprovedReadyToSend(SampleSubmissionRequest $enquiry, ?QuotationHeader $header = null): bool
    {
        $header ??= $enquiry->currentQuotation;
        if ($header === null) {
            return false;
        }

        if ($header->sent_to_customer_at !== null) {
            return false;
        }

        return (int) $header->is_approved === 1
            && in_array((string) $enquiry->status, [
                SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
                SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            ], true);
    }

    /**
     * Name of the lab manager who approved the quotation.
     *
     * Returns an empty string for quotations that became approved without a
     * lab-manager decision (auto-completed inline quotes and reused existing
     * quotations), so callers do not claim a review that never happened.
     */
    public function resolveApproverName(?QuotationHeader $header): string
    {
        if ($header === null || (int) $header->is_approved !== 1) {
            return '';
        }

        if (! empty($header->approved_by)) {
            $header->loadMissing('approvedByUser');
            $name = trim((string) ($header->approvedByUser?->name ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        $approvedLog = QuotationApprovalLog::query()
            ->with('actor')
            ->where('quotation_header_id', $header->id)
            ->where('action', QuotationApprovalLog::ACTION_APPROVED)
            ->latest('created_at')
            ->first();

        return trim((string) ($approvedLog?->actor?->name ?? ''));
    }

    public function canCurrentUserApprove(QuotationHeader $header, ?User $user = null): bool
    {
        $user ??= Auth::user();
        if ($user === null || empty($header->approved_by)) {
            return false;
        }

        return (string) $header->approved_by === (string) $user->id
            && (string) $header->status === self::HEADER_STATUS_IN_APPROVAL
            && (int) $header->is_approved !== 1;
    }

    /**
     * @return SupportCollection<int, QuotationHeader>
     */
    public function pendingApprovalsForUser(string $userId): SupportCollection
    {
        return QuotationHeader::query()
            ->with(['sampleSubmissionRequest.submissionFormInstance.submissionForm', 'customer'])
            ->where('status', self::HEADER_STATUS_IN_APPROVAL)
            ->where('approved_by', $userId)
            ->where(function ($query): void {
                $query->where('is_approved', 0)->orWhereNull('is_approved');
            })
            ->whereNotNull('sample_submission_request_id')
            ->latest('approval_requested_at')
            ->get();
    }

    /**
     * @return Collection<int, QuotationApprovalLog>
     */
    public function logsForQuotation(string $quotationHeaderId): Collection
    {
        return QuotationApprovalLog::query()
            ->with(['actor', 'assignee'])
            ->where('quotation_header_id', $quotationHeaderId)
            ->latest('created_at')
            ->get();
    }

    private function resolveLabManager(string $labManagerId): User
    {
        $manager = User::query()
            ->role('Lab Manager')
            ->where('active', 1)
            ->where('id', $labManagerId)
            ->first();

        if ($manager === null) {
            throw ValidationException::withMessages([
                'labManagerId' => 'Select a valid lab manager.',
            ]);
        }

        return $manager;
    }

    private function assertAssignedApprover(QuotationHeader $header, string $userId): void
    {
        if ((string) $header->approved_by !== $userId) {
            throw ValidationException::withMessages([
                'approval' => 'Only the assigned lab manager can action this quotation approval.',
            ]);
        }
    }

    private function notifyRequesterOfDecision(
        QuotationHeader $header,
        User $actor,
        bool $approved,
        ?string $comments = null,
    ): void {
        try {
            $requesterId = (string) ($header->approval_requested_by ?: $header->prepared_by_id);
            if ($requesterId === '') {
                return;
            }

            $requester = User::query()->find($requesterId);
            if (! $requester?->email) {
                return;
            }

            if ($approved) {
                $subject = 'Quotation '.$header->quote_number.' approved';
                $message = 'Hi '.$requester->name.',<br><br>'
                    .$actor->name.' approved quotation <strong>'.$header->quote_number.'</strong>. '
                    .'You can now send it to the customer.';
            } else {
                $subject = 'Quotation '.$header->quote_number.' returned for revision';
                $message = 'Hi '.$requester->name.',<br><br>'
                    .$actor->name.' rejected quotation <strong>'.$header->quote_number.'</strong>.<br>'
                    .'Comments: '.e((string) $comments);
            }

            notify_user($message, $requester->email, $subject);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function writeLog(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
        string $action,
        ?string $actorUserId,
        ?string $assigneeUserId,
        ?string $comments,
    ): void {
        QuotationApprovalLog::query()->create([
            'quotation_header_id' => $header->id,
            'sample_submission_request_id' => $enquiry->id,
            'actor_user_id' => $actorUserId,
            'assignee_user_id' => $assigneeUserId,
            'action' => $action,
            'comments' => $comments,
        ]);
    }
}
