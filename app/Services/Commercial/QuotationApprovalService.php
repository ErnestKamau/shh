<?php

namespace App\Services\Commercial;

use App\Jobs\Commercial\NotifyQuotationApproversJob;
use App\Models\QuotationApprovalLog;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Lab\LabSystemNotificationService;
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
        return $this->eligibleApprovers($excludeUserId);
    }

    /**
     * @return Collection<int, User>
     */
    public function eligibleApprovers(?string $excludeUserId = null): Collection
    {
        $role = app(QuotationApprovalConfigService::class)->approverRoleName();

        $query = User::query()
            ->role($role)
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
        bool $notifyInApp = true,
    ): SampleSubmissionRequest {
        // Email approval link: approve only (no customer send). Requester is notified
        // in-app + email to log into LIMS and send via the approve/send modal.
        // In-app approval: lab manager picks CRM contacts (name, company unit, sampling location).
        // Portal send is automatic for selected portal-eligible contacts; email send is optional.
        // Customer email carries the PDF attachment only — no Accept/View links.
        // Acceptance is via customer portal or LIMS "Record quotation acceptance".
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to submit a quotation for approval.');
        }

        if (! $notifyEmail && ! $notifyInApp) {
            throw ValidationException::withMessages([
                'approvalNotify' => 'Choose at least one notification channel (email or in-app).',
            ]);
        }

        $manager = $this->resolveLabManager($labManagerId);

        if ($header->details()->count() < 1) {
            throw new RuntimeException('Add at least one quotation line before sending for approval.');
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

        $freshHeader = $submitted->currentQuotation ?? $header->fresh() ?? $header;
        $approvers = $this->eligibleApprovers();

        if ($notifyInApp) {
            try {
                app(LabSystemNotificationService::class)->notifyQuotationApprovalRequired(
                    $freshHeader,
                    $approvers,
                    (string) $actor->name,
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($notifyEmail) {
            NotifyQuotationApproversJob::dispatch(
                (string) $freshHeader->id,
                (string) $submitted->id,
                (string) $actor->id,
                true,
            )->afterResponse();
        }

        return $submitted;
    }

    /**
     * Generate the quotation PDF (if needed) and email all eligible approvers.
     * Intended to run after the HTTP response via NotifyQuotationApproversJob.
     */
    public function deliverApproverNotifications(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
        User $actor,
        bool $notifyEmail = true,
    ): void {
        if (empty($header->upload_url)) {
            try {
                $header = app(QuotationFromEnquiryService::class)->generatePdf($header);
            } catch (\Throwable $exception) {
                report($exception);
                throw new RuntimeException(
                    'Could not generate the quotation PDF for approver notifications. '.$exception->getMessage(),
                    0,
                    $exception,
                );
            }
        }

        if (! $notifyEmail) {
            return;
        }

        $approvers = $this->eligibleApprovers();

        foreach ($approvers as $approver) {
            if (! filled($approver->email)) {
                continue;
            }

            try {
                $approvalUrl = app(QuotationEmailActionService::class)->approvalUrl($enquiry, $header);
                $subject = 'Quotation '.$header->quote_number.' awaiting your approval';
                $message = 'Hi '.$approver->name.',<br><br>'
                    .$actor->name.' submitted quotation <strong>'.$header->quote_number.'</strong> for approval.';
                if ($approvalUrl !== null) {
                    $message .= '<br><br><a href="'.e($approvalUrl).'" '
                        .'style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">'
                        .'Approve quotation</a>';
                } else {
                    $message .= '<br>Open the request from your personal dashboard to preview and approve.';
                }
                $message .= '<br><br>The quotation PDF is attached for your review.';
                notify_user($message, $approver->email, $subject, $this->resolveQuotationAttachmentPath($header), false, [], [
                    'eyebrow' => 'Quotation Approval',
                ]);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    /**
     * Change the lab manager assigned to approve an enquiry quotation.
     *
     * Only allowed while the quotation is pending approval. After approval,
     * the reviewer cannot be changed.
     */
    public function reassignLabManager(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        string $labManagerId,
        bool $notifyEmail = true,
        ?string $comments = null,
    ): SampleSubmissionRequest {
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to change the quotation approver.');
        }

        if ($header->sent_to_customer_at !== null) {
            throw new RuntimeException('Cannot change the lab manager after the quotation has been sent to the customer.');
        }

        if (! $this->isPendingApproval($enquiry, $header)) {
            throw new RuntimeException('The lab manager can only be changed while the quotation is pending approval.');
        }

        $manager = $this->resolveLabManager($labManagerId);

        if ((string) $header->approved_by === (string) $manager->id) {
            throw ValidationException::withMessages([
                'labManagerId' => 'Select a different lab manager to reassign approval.',
            ]);
        }

        $reassigned = DB::transaction(function () use ($enquiry, $header, $manager, $actor, $comments): SampleSubmissionRequest {
            $now = now();
            $header->status = self::HEADER_STATUS_IN_APPROVAL;
            $header->approved_by = (string) $manager->id;
            $header->is_approved = 0;
            $header->is_complete = 0;
            $header->approval_requested_at = $now;
            $header->approval_requested_by = (string) ($header->approval_requested_by ?: $actor->id);
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
                QuotationApprovalLog::ACTION_REASSIGNED,
                (string) $actor->id,
                (string) $manager->id,
                $header->approval_comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        if ($notifyEmail && filled($manager->email)) {
            try {
                $approvalUrl = app(QuotationEmailActionService::class)->approvalUrl($reassigned, $header);
                $subject = 'Quotation '.$header->quote_number.' awaiting your approval';
                $message = 'Hi '.$manager->name.',<br><br>'
                    .$actor->name.' assigned quotation <strong>'.$header->quote_number.'</strong> to you for approval.';
                if ($approvalUrl !== null) {
                    $message .= '<br><br><a href="'.e($approvalUrl).'" '
                        .'style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">'
                        .'Approve quotation</a>';
                } else {
                    $message .= '<br>Open the request from your personal dashboard to preview and approve.';
                }
                $message .= '<br><br>The quotation PDF is attached for your review.';
                notify_user($message, $manager->email, $subject, $this->resolveQuotationAttachmentPath($header), false, [], [
                    'eyebrow' => 'Quotation Approval',
                ]);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $reassigned;
    }

    public function approve(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        ?string $comments = null,
        bool $notifyRequester = true,
    ): SampleSubmissionRequest {
        $actor = Auth::user();
        if ($actor === null) {
            throw new RuntimeException('You must be signed in to approve a quotation.');
        }

        $this->assertUserCanApprove($header, $actor);

        if ((string) $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL
            && (string) $header->status !== self::HEADER_STATUS_IN_APPROVAL) {
            throw new RuntimeException('This quotation is not awaiting approval.');
        }

        if ((int) $header->is_approved === 1) {
            throw new RuntimeException($this->alreadyApprovedMessage($header));
        }

        $approved = DB::transaction(function () use ($enquiry, $header, $actor, $comments): SampleSubmissionRequest {
            $header->status = self::HEADER_STATUS_COMPLETE;
            $header->is_approved = 1;
            $header->is_complete = 1;
            $header->approved_by = (string) $actor->id;
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
                (string) $actor->id,
                $header->approval_comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        // Skip when the in-app modal will immediately send to the customer.
        if ($notifyRequester) {
            $this->notifyRequesterOfDecision(
                $approved->currentQuotation ?? $header,
                $actor,
                approved: true,
            );
        }

        return $approved;
    }

    /**
     * Approve a quotation via a signed email link (no interactive login required).
     */
    public function approveViaEmailToken(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        ?string $approverUserId = null,
    ): SampleSubmissionRequest {
        if ((int) $header->is_approved === 1) {
            throw new RuntimeException($this->alreadyApprovedMessage($header));
        }

        $approverId = trim((string) ($approverUserId ?: $header->approved_by ?? ''));
        if ($approverId === '') {
            $firstApprover = $this->eligibleApprovers()->first();
            $approverId = $firstApprover ? (string) $firstApprover->id : '';
        }

        if ($approverId === '') {
            throw new RuntimeException('No approver is available for this quotation.');
        }

        if ((string) $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL
            && (string) $header->status !== self::HEADER_STATUS_IN_APPROVAL) {
            throw new RuntimeException('This quotation is not awaiting approval.');
        }

        $approved = DB::transaction(function () use ($enquiry, $header, $approverId): SampleSubmissionRequest {
            $header->status = self::HEADER_STATUS_COMPLETE;
            $header->is_approved = 1;
            $header->is_complete = 1;
            $header->approved_by = $approverId;
            $header->approval_decision_at = now();
            $header->save();

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND;
            $enquiry->save();

            $this->writeLog(
                $header,
                $enquiry,
                QuotationApprovalLog::ACTION_APPROVED,
                $approverId,
                $approverId,
                $header->approval_comments,
            );

            return $enquiry->fresh(['currentQuotation', 'customer', 'contact']) ?? $enquiry;
        });

        $freshHeader = $approved->currentQuotation ?? $header->fresh() ?? $header;
        $approver = User::query()->find($approverId);
        if ($approver === null) {
            $approver = new User;
            $approver->name = 'An approver';
        }

        // Email link only approves — never sends to the customer. Requester must send from LIMS.
        $this->notifyRequesterOfDecision(
            $freshHeader,
            $approver,
            approved: true,
            fromEmailLink: true,
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
        if ($user === null) {
            return false;
        }

        if ((string) $header->status !== self::HEADER_STATUS_IN_APPROVAL || (int) $header->is_approved === 1) {
            return false;
        }

        $role = app(QuotationApprovalConfigService::class)->approverRoleName();

        return $user->hasRole($role);
    }

    /**
     * @return SupportCollection<int, QuotationHeader>
     */
    public function pendingApprovalsForUser(string $userId): SupportCollection
    {
        $user = User::query()->find($userId);
        if ($user === null) {
            return collect();
        }

        $role = app(QuotationApprovalConfigService::class)->approverRoleName();
        if (! $user->hasRole($role)) {
            return collect();
        }

        return QuotationHeader::query()
            ->with(['sampleSubmissionRequest.submissionFormInstance.submissionForm', 'customer'])
            ->where('status', self::HEADER_STATUS_IN_APPROVAL)
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
        $role = app(QuotationApprovalConfigService::class)->approverRoleName();

        $manager = User::query()
            ->role($role)
            ->where('active', 1)
            ->where('id', $labManagerId)
            ->first();

        if ($manager === null) {
            throw ValidationException::withMessages([
                'labManagerId' => 'Select a valid approver with the configured quotation approval role.',
            ]);
        }

        return $manager;
    }

    private function assertUserCanApprove(QuotationHeader $header, User $user): void
    {
        if (! $this->canCurrentUserApprove($header, $user)) {
            throw ValidationException::withMessages([
                'approval' => 'Only users with the quotation approval role can action this quotation.',
            ]);
        }
    }

    public function alreadyApprovedMessage(QuotationHeader $header): string
    {
        $header->loadMissing('approvedByUser');
        $name = trim((string) ($header->approvedByUser?->name ?? $this->resolveApproverName($header)));
        $when = $header->approval_decision_at
            ? $header->approval_decision_at->format('Y-m-d H:i')
            : '';

        $quote = (string) ($header->quote_number ?? '');

        if ($name !== '' && $when !== '') {
            return 'Quotation '.$quote.' was approved by '.$name.' on '.$when.'.';
        }

        if ($name !== '') {
            return 'Quotation '.$quote.' was approved by '.$name.'.';
        }

        return 'Quotation '.$quote.' was already approved.';
    }

    private function assertAssignedApprover(QuotationHeader $header, string $userId): void
    {
        $user = User::query()->find($userId);
        if ($user === null || ! $this->canCurrentUserApprove($header, $user)) {
            throw ValidationException::withMessages([
                'approval' => 'Only users with the quotation approval role can action this quotation approval.',
            ]);
        }
    }

    private function notifyRequesterOfDecision(
        QuotationHeader $header,
        User $actor,
        bool $approved,
        ?string $comments = null,
        bool $fromEmailLink = false,
    ): void {
        try {
            $requesterId = (string) ($header->approval_requested_by ?: $header->prepared_by_id);
            if ($requesterId === '') {
                return;
            }

            $requester = User::query()->find($requesterId);
            if ($requester === null) {
                return;
            }

            if ($approved) {
                try {
                    app(LabSystemNotificationService::class)->notifyQuotationApprovedReadyToSend(
                        $header,
                        $requester,
                        (string) $actor->name,
                    );
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            if (! filled($requester->email)) {
                return;
            }

            if ($approved) {
                $subject = 'Quotation '.$header->quote_number.' approved';
                $message = 'Hi '.$requester->name.',<br><br>'
                    .$actor->name.' approved quotation <strong>'.$header->quote_number.'</strong>. ';
                if ($fromEmailLink) {
                    $message .= 'Please log into LIMS and send the quotation to the customer from the request view. '
                        .'Sending is not done from this email.';
                } else {
                    $message .= 'You can now send it to the customer from LIMS if it was not sent automatically.';
                }
            } else {
                $subject = 'Quotation '.$header->quote_number.' returned for revision';
                $message = 'Hi '.$requester->name.',<br><br>'
                    .$actor->name.' rejected quotation <strong>'.$header->quote_number.'</strong>.<br>'
                    .'Comments: '.e((string) $comments);
            }

            notify_user($message, $requester->email, $subject, false, false, [], [
                'eyebrow' => 'Quotation Approval',
            ]);
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

    private function resolveQuotationAttachmentPath(QuotationHeader $header): ?string
    {
        $uploadUrl = trim((string) ($header->upload_url ?? ''));
        if ($uploadUrl === '') {
            try {
                $header = app(QuotationFromEnquiryService::class)->generatePdf($header->fresh() ?? $header);
                $uploadUrl = trim((string) ($header->upload_url ?? ''));
            } catch (\Throwable $exception) {
                report($exception);

                return null;
            }
        }

        if ($uploadUrl === '') {
            return null;
        }

        $path = storage_path('app'.(str_starts_with($uploadUrl, '/') ? $uploadUrl : '/'.$uploadUrl));

        return is_file($path) ? $path : null;
    }
}
