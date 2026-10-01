<?php

namespace App\Services\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\SampleSubmissionRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class CustomerPurchaseOrderService
{
    public function __construct(
        private readonly EnquiryAccountSettingsService $accountSettings,
        private readonly EnquiryReceptionReadinessService $receptionReadiness,
    ) {}

    /**
     * Upsert the 1:1 Customer PO for an enquiry and sync enquiry PO fields.
     * Does not change enquiry workflow status.
     *
     * @param  array{client_po_number?: ?string, po_skipped?: bool|null}  $payload
     */
    public function upsertForEnquiry(
        SampleSubmissionRequest $enquiry,
        array $payload = [],
        ?UploadedFile $file = null,
        ?string $quotationHeaderId = null,
        ?string $uploadedBy = null,
    ): CustomerPurchaseOrder {
        if ($file !== null) {
            $this->assertValidPoFile($file);
        }

        $poNumber = trim((string) ($payload['client_po_number'] ?? $enquiry->client_po_number ?? ''));
        $poNumber = $poNumber !== '' ? $poNumber : null;

        $poSkipped = array_key_exists('po_skipped', $payload)
            ? filter_var($payload['po_skipped'], FILTER_VALIDATE_BOOLEAN)
            : (bool) ($enquiry->po_skipped ?? false);

        if ($poNumber === null && ! $poSkipped) {
            $rules = $this->accountSettings->poRulesForCustomer($enquiry->customer);
            if ($rules['allows_po_skip']) {
                $poSkipped = true;
            }
        }

        if ($poNumber !== null) {
            $poSkipped = false;
        }

        $quotationId = $quotationHeaderId
            ?? $enquiry->accepted_quotation_header_id
            ?? $enquiry->current_quotation_header_id;

        $quotationId = filled($quotationId) ? (string) $quotationId : null;
        $uploaderId = $uploadedBy ?? (Auth::id() !== null ? (string) Auth::id() : null);

        /** @var CustomerPurchaseOrder $po */
        $po = CustomerPurchaseOrder::query()->firstOrNew([
            'enquiry_id' => (string) $enquiry->id,
        ]);

        $po->fill([
            'po_number' => $poNumber,
            'po_skipped' => $poSkipped,
            'quotation_header_id' => $quotationId,
            'customer_id' => filled($enquiry->crm_customer_id) ? (string) $enquiry->crm_customer_id : null,
            'uploaded_by' => $uploaderId ?? $po->uploaded_by,
            'recorded_at' => $po->recorded_at ?? now(),
        ]);

        if ($file !== null) {
            $this->storeFileOnPo($po, $file, (string) $enquiry->id);
        }

        $po->save();

        $enquiry->client_po_number = $poNumber;
        $enquiry->po_skipped = $poSkipped;

        if (config('purchase_orders.enabled')) {
            $boundPoId = (string) ($enquiry->customer_purchase_order_id ?? '');
            if ($boundPoId === '' || $boundPoId === (string) $po->id) {
                $enquiry->customer_purchase_order_id = $poSkipped ? null : (string) $po->id;
            }
        }

        $enquiry->save();

        return $po->fresh() ?? $po;
    }

    /**
     * Validate account PO rules, upsert the registry row, then mark Ready for Reception.
     * With PO ledger cover enabled this delegates to EnquiryPurchaseOrderService (blanket / single / none).
     *
     * @param  array{client_po_number?: ?string, po_skipped?: bool|null, mode?: ?string, customer_purchase_order_id?: ?string}  $payload
     */
    public function recordAndMarkReadyForReception(
        SampleSubmissionRequest $enquiry,
        array $payload = [],
        ?UploadedFile $file = null,
        ?string $quotationHeaderId = null,
        ?string $uploadedBy = null,
    ): SampleSubmissionRequest {
        if (config('purchase_orders.enabled')) {
            return app(EnquiryPurchaseOrderService::class)->captureAndMarkReady(
                $enquiry,
                $payload,
                $file,
                filled($quotationHeaderId) ? $quotationHeaderId : null,
                $uploadedBy,
            );
        }

        $enquiry->loadMissing('customer');

        $normalized = $this->normalizePayload($enquiry, $payload);

        $this->accountSettings->validateAcceptPayload($enquiry->customer, [
            'client_po_number' => $normalized['client_po_number'],
            'po_skipped' => $normalized['po_skipped'],
        ]);

        $this->upsertForEnquiry(
            $enquiry,
            $normalized,
            $file,
            $quotationHeaderId,
            $uploadedBy,
        );

        return $this->receptionReadiness->markReadyForReception(
            $enquiry->fresh() ?? $enquiry,
            $quotationHeaderId
                ?? (string) ($enquiry->accepted_quotation_header_id ?? $enquiry->current_quotation_header_id ?? ''),
            [
                'client_po_number' => $normalized['client_po_number'],
                'po_skipped' => $normalized['po_skipped'],
            ],
        );
    }

    /**
     * @param  array{client_po_number?: ?string, po_skipped?: bool|null}  $payload
     * @return array{client_po_number: ?string, po_skipped: bool}
     */
    public function normalizePayload(SampleSubmissionRequest $enquiry, array $payload): array
    {
        $poNumber = trim((string) ($payload['client_po_number'] ?? ''));
        $poNumber = $poNumber !== '' ? $poNumber : null;

        $explicitSkip = array_key_exists('po_skipped', $payload)
            ? filter_var($payload['po_skipped'], FILTER_VALIDATE_BOOLEAN)
            : null;

        if ($poNumber !== null) {
            return [
                'client_po_number' => $poNumber,
                'po_skipped' => false,
            ];
        }

        if ($explicitSkip === true) {
            return [
                'client_po_number' => null,
                'po_skipped' => true,
            ];
        }

        $rules = $this->accountSettings->poRulesForCustomer($enquiry->customer);

        return [
            'client_po_number' => null,
            'po_skipped' => (bool) $rules['allows_po_skip'],
        ];
    }

    private function assertValidPoFile(UploadedFile $file): void
    {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $size = (int) $file->getSize();

        if (! in_array($ext, $allowed, true)) {
            throw ValidationException::withMessages([
                'quotationAcceptanceAttachment' => ['The purchase order file must be a JPEG, PNG, or PDF.'],
            ]);
        }

        if ($size > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'quotationAcceptanceAttachment' => ['The purchase order file must not be greater than 10 MB.'],
            ]);
        }
    }

    private function storeFileOnPo(CustomerPurchaseOrder $po, UploadedFile $file, string $enquiryId): void
    {
        if (filled($po->file_path) && Storage::disk('public')->exists((string) $po->file_path)) {
            Storage::disk('public')->delete((string) $po->file_path);
        }

        $path = $file->store('customer-purchase-orders/'.$enquiryId, 'public');

        $po->file_path = $path;
        $po->file_name = $file->getClientOriginalName();
        $po->mime = $file->getClientMimeType();
        $po->size = (int) $file->getSize();
    }
}
