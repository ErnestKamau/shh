<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;

final class EnquiryReceptionReadinessService
{
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

        return $enquiry->fresh();
    }

    public function isEligibleForPhysicalReceive(SampleSubmissionRequest $enquiry): bool
    {
        if ((string) $enquiry->status !== SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION) {
            return false;
        }

        return $enquiry->accepted_quotation_header_id !== null
            || $enquiry->current_quotation_header_id !== null
            || $enquiry->quotation_accepted_at !== null;
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
            $elementId = trim((string) ($detail->accredited_analytes ?? ''));
            if ($elementId === '') {
                continue;
            }

            $subcontractedIds = array_filter(array_map(
                'trim',
                explode(',', (string) ($detail->subcontracted_analytes ?? ''))
            ));

            $subcontracted = in_array($elementId, $subcontractedIds, true);

            $flags[$elementId] = [
                'accredited' => ! $subcontracted,
                'subcontracted' => $subcontracted,
            ];
        }

        return $flags;
    }

}
