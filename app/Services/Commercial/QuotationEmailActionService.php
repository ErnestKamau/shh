<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use Illuminate\Support\Facades\URL;

final class QuotationEmailActionService
{
    public const ACTION_APPROVE = 'approve';

    public const ACTION_ACCEPT = 'accept';

    public function approvalToken(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $approverUserId,
    ): string {
        return $this->token(self::ACTION_APPROVE, (string) $enquiry->id, (string) $quotation->id, $approverUserId);
    }

    public function acceptanceToken(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $contactId,
    ): string {
        return $this->token(self::ACTION_ACCEPT, (string) $enquiry->id, (string) $quotation->id, $contactId);
    }

    public function verifyApprovalToken(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $token,
    ): bool {
        $approverId = trim((string) ($quotation->approved_by ?? ''));

        return $approverId !== ''
            && hash_equals($this->approvalToken($enquiry, $quotation, $approverId), $token);
    }

    public function verifyAcceptanceToken(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $contactId,
        string $token,
    ): bool {
        $contactId = trim($contactId);

        return $contactId !== ''
            && hash_equals($this->acceptanceToken($enquiry, $quotation, $contactId), $token);
    }

    public function approvalUrl(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
    ): ?string {
        $approverId = trim((string) ($quotation->approved_by ?? ''));
        if ($approverId === '') {
            return null;
        }

        return URL::route('commercial.quotations.email-approve', [
            'enquiry' => $enquiry->id,
            'quotation' => $quotation->id,
            'token' => $this->approvalToken($enquiry, $quotation, $approverId),
        ]);
    }

    public function acceptanceUrl(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $contactId,
    ): string {
        return URL::route('commercial.quotations.email-accept', [
            'enquiry' => $enquiry->id,
            'quotation' => $quotation->id,
            'contact' => $contactId,
            'token' => $this->acceptanceToken($enquiry, $quotation, $contactId),
        ]);
    }

    private function token(string $action, string $enquiryId, string $quotationId, string $subjectId): string
    {
        return substr(
            hash('sha256', implode('|', [$action, $enquiryId, $quotationId, $subjectId, config('app.key')])),
            0,
            48,
        );
    }
}
