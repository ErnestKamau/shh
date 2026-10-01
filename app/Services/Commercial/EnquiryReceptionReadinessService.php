<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;

final class EnquiryReceptionReadinessService
{
    public function __construct(
        private ContractCustomerService $contractCustomerService,
    ) {}

    /** @var list<string> */
    public const RECEPTION_READY_STATUSES = [
        SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
        SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
    ];

    /**
     * @param  array{client_po_number?: ?string, advance_payment_reference?: ?string, po_skipped?: bool}  $payload
     */
    public function markReadyForReception(
        SampleSubmissionRequest $enquiry,
        ?string $acceptedQuotationHeaderId = null,
        array $payload = [],
    ): SampleSubmissionRequest {
        $quotationId = $acceptedQuotationHeaderId
            ?? $enquiry->accepted_quotation_header_id
            ?? $enquiry->current_quotation_header_id;

        if ($quotationId !== null) {
            $enquiry->accepted_quotation_header_id = $quotationId;
        }

        $enquiry->status = SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION;
        $enquiry->quotation_accepted_at = $enquiry->quotation_accepted_at ?? now();

        if (array_key_exists('client_po_number', $payload)) {
            $enquiry->client_po_number = trim((string) ($payload['client_po_number'] ?? '')) ?: null;
        }

        if (array_key_exists('advance_payment_reference', $payload)) {
            $enquiry->advance_payment_reference = trim((string) ($payload['advance_payment_reference'] ?? '')) ?: null;
        }

        if (array_key_exists('po_skipped', $payload)) {
            $enquiry->po_skipped = filter_var($payload['po_skipped'], FILTER_VALIDATE_BOOLEAN);
        }

        $enquiry->save();

        if (EnquiryPurchaseOrderService::enabled()) {
            app(EnquiryPurchaseOrderService::class)->syncReservation($enquiry);
        }

        return $enquiry->fresh();
    }

    /**
     * Move a Ready-for-Reception enquiry into Sample Integrity Check (no job creation).
     */
    public function markSampleIntegrityCheck(SampleSubmissionRequest $enquiry): SampleSubmissionRequest
    {
        $enquiry->status = SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK;
        $enquiry->save();

        return $enquiry->fresh() ?? $enquiry;
    }

    public function isEligibleForPhysicalReceive(SampleSubmissionRequest $enquiry): bool
    {
        if ((string) $enquiry->status !== SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION) {
            return false;
        }

        // Subcontract dispatch is enforced at Integrity Accept, not at physical receive handoff.
        if ($this->contractCustomerService->bypassesCommercialQuotationGate($enquiry)) {
            return true;
        }

        return $enquiry->accepted_quotation_header_id !== null
            || $enquiry->current_quotation_header_id !== null
            || $enquiry->quotation_accepted_at !== null;
    }

    /**
     * True when the enquiry can open the Receive Samples wizard (status-only handoff
     * from Ready for Reception into Sample Integrity Check).
     */
    public function isEligibleForReceiveHandoff(
        SampleSubmissionRequest $enquiry,
        ?SubmissionFormInstance $instance = null,
    ): bool {
        if (in_array((string) $enquiry->status, [
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            SampleSubmissionRequest::STATUS_IN_REVIEW,
        ], true)) {
            return true;
        }

        return $instance !== null
            && in_array(strtolower((string) $instance->status), ['in_review', 'received'], true);
    }

    /**
     * True when the enquiry is ready for dual-signature sample acceptance
     * (Sample Integrity Check → Accept creates the batch/job and samples).
     *
     * Subcontract dispatch is independent and does not block Accept.
     */
    public function isEligibleForSampleAcceptance(
        SampleSubmissionRequest $enquiry,
        ?SubmissionFormInstance $instance = null,
    ): bool {
        if (in_array((string) $enquiry->status, [
            SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
            // Legacy: records already in Ready for Reception before Integrity split.
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            SampleSubmissionRequest::STATUS_IN_REVIEW,
        ], true)) {
            return true;
        }

        return $instance !== null
            && in_array(strtolower((string) $instance->status), ['in_review', 'received'], true);
    }

    public function resolveAcceptedQuotation(SampleSubmissionRequest $enquiry): ?QuotationHeader
    {
        $quotationId = $enquiry->accepted_quotation_header_id
            ?? $enquiry->current_quotation_header_id;

        if ($quotationId === null) {
            return null;
        }

        return QuotationHeader::query()
            ->with('details')
            ->find($quotationId);
    }

    /**
     * @return array<string, array{accredited: bool, subcontracted: bool}>
     */
    public function resolveElementFlagsFromQuotation(?QuotationHeader $quotation): array
    {
        if ($quotation === null) {
            return [];
        }

        $flags = [];

        foreach ($quotation->details as $detail) {
            foreach (['accredited_analytes', 'default_analytes', 'sub_acc_analytes'] as $field) {
                foreach (array_filter(array_map('trim', explode(',', (string) ($detail->{$field} ?? '')))) as $elementId) {
                    if ($elementId === '' || isset($flags[$elementId])) {
                        continue;
                    }

                    $flags[$elementId] = [
                        'accredited' => $field === 'accredited_analytes' || $field === 'sub_acc_analytes',
                        'subcontracted' => false,
                    ];
                }
            }
        }

        return $flags;
    }

    /**
     * @return array<string, array{accredited: bool, subcontracted: bool}>
     */
    public function resolveElementFlagsFromEnquiry(SampleSubmissionRequest $enquiry): array
    {
        return app(\App\Services\Sampleworkflow\SubcontractingAssignmentService::class)
            ->resolveElementFlagsFromEnquiry($enquiry);
    }
}
