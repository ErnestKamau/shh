<?php

namespace App\Livewire\Concerns;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\EnquiryPurchaseOrderService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * Shared PO capture state for the quotation-acceptance / Record PO modals
 * (workflow board and request view). Renders with
 * `livewire.commercial.partials.enquiry-po-capture`; the host keeps `$clientPoNumber`.
 */
trait CapturesEnquiryPurchaseOrder
{
    public string $poCaptureMode = '';

    public ?string $poCaptureBlanketId = null;

    #[Locked]
    public string $poCaptureRequirement = '';

    #[Locked]
    public bool $poCaptureBlanketOnly = false;

    #[Locked]
    public ?string $poCaptureEnquiryId = null;

    /** @var list<array{id: string, label: string, remaining: int}> */
    #[Locked]
    public array $poCaptureBlanketOptions = [];

    /** @var array{requested?: int, covered?: int, uncovered?: int, rows?: list<array{key: string, label: string, requested: int, covered: int, uncovered: int, reason: ?string}>} */
    public array $poCaptureCoverage = [];

    public function updatedPoCaptureMode(): void
    {
        if ($this->poCaptureBlanketOnly) {
            $this->poCaptureMode = EnquiryPurchaseOrderService::MODE_BLANKET;
        }

        $this->refreshPoCaptureCoverage();
    }

    public function updatedPoCaptureBlanketId(): void
    {
        $this->refreshPoCaptureCoverage();
    }

    /**
     * Only a single PO's own document is stored; blanket POs keep theirs on the registry record.
     */
    #[Computed]
    public function poCaptureAcceptsFile(): bool
    {
        return ! EnquiryPurchaseOrderService::enabled()
            || $this->poCaptureRequirement === ''
            || $this->poCaptureMode === EnquiryPurchaseOrderService::MODE_SINGLE;
    }

    protected function hydratePoCapture(SampleSubmissionRequest $enquiry, bool $blanketOnly = false): void
    {
        $this->resetPoCapture();

        if (! EnquiryPurchaseOrderService::enabled()) {
            return;
        }

        $service = app(EnquiryPurchaseOrderService::class);

        $this->poCaptureEnquiryId = (string) $enquiry->id;
        $this->poCaptureRequirement = $service->requirement($enquiry);
        $this->poCaptureBlanketOnly = $blanketOnly;
        $this->poCaptureBlanketOptions = $service->blanketOrdersFor((string) $enquiry->crm_customer_id)
            ->map(fn (CustomerPurchaseOrder $po): array => [
                'id' => (string) $po->id,
                'label' => $this->poCaptureBlanketLabel($po),
                'remaining' => (int) $po->remaining_total,
            ])
            ->values()
            ->all();

        $optionIds = array_column($this->poCaptureBlanketOptions, 'id');
        $withBalance = array_values(array_filter($this->poCaptureBlanketOptions, static fn (array $option): bool => $option['remaining'] > 0));
        $boundId = (string) ($enquiry->customer_purchase_order_id ?? '');

        if ($boundId !== '' && in_array($boundId, $optionIds, true)) {
            $this->poCaptureMode = EnquiryPurchaseOrderService::MODE_BLANKET;
            $this->poCaptureBlanketId = $boundId;
        } elseif (count($withBalance) === 1 && ($blanketOnly || blank($enquiry->client_po_number))) {
            $this->poCaptureMode = EnquiryPurchaseOrderService::MODE_BLANKET;
            $this->poCaptureBlanketId = $withBalance[0]['id'];
        } elseif ($blanketOnly) {
            $this->poCaptureMode = EnquiryPurchaseOrderService::MODE_BLANKET;
        } elseif (filled($enquiry->client_po_number)) {
            $this->poCaptureMode = EnquiryPurchaseOrderService::MODE_SINGLE;
        } else {
            $this->poCaptureMode = $optionIds !== [] ? EnquiryPurchaseOrderService::MODE_BLANKET : EnquiryPurchaseOrderService::MODE_SINGLE;
        }

        $this->refreshPoCaptureCoverage();
    }

    protected function resetPoCapture(): void
    {
        $this->poCaptureMode = '';
        $this->poCaptureBlanketId = null;
        $this->poCaptureRequirement = '';
        $this->poCaptureBlanketOnly = false;
        $this->poCaptureEnquiryId = null;
        $this->poCaptureBlanketOptions = [];
        $this->poCaptureCoverage = [];
    }

    /**
     * Payload for CustomerPurchaseOrderService::recordAndMarkReadyForReception().
     *
     * @return array{client_po_number: ?string, mode?: string, customer_purchase_order_id?: ?string}
     */
    protected function poCapturePayload(string $clientPoNumber): array
    {
        if (! EnquiryPurchaseOrderService::enabled() || $this->poCaptureRequirement === '') {
            return ['client_po_number' => $clientPoNumber];
        }

        $mode = $this->poCaptureBlanketOnly ? EnquiryPurchaseOrderService::MODE_BLANKET : $this->poCaptureMode;

        return [
            'mode' => $mode,
            'customer_purchase_order_id' => $mode === EnquiryPurchaseOrderService::MODE_BLANKET ? $this->poCaptureBlanketId : null,
            'client_po_number' => $mode === EnquiryPurchaseOrderService::MODE_SINGLE ? $clientPoNumber : null,
        ];
    }

    protected function poCaptureRuleMessage(string $legacyMessage): string
    {
        if (! EnquiryPurchaseOrderService::enabled() || $this->poCaptureRequirement === '') {
            return $legacyMessage;
        }

        return app(EnquiryPurchaseOrderService::class)->requirementMessage($this->poCaptureRequirement);
    }

    protected function refreshPoCaptureCoverage(): void
    {
        $this->poCaptureCoverage = [];

        if ($this->poCaptureMode !== EnquiryPurchaseOrderService::MODE_BLANKET
            || blank($this->poCaptureBlanketId)
            || blank($this->poCaptureEnquiryId)
            || ! in_array((string) $this->poCaptureBlanketId, array_column($this->poCaptureBlanketOptions, 'id'), true)) {
            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->poCaptureEnquiryId);
        $po = CustomerPurchaseOrder::query()->find($this->poCaptureBlanketId);

        if ($enquiry === null || $po === null) {
            return;
        }

        $preview = app(EnquiryPurchaseOrderService::class)->previewFor($enquiry, $po);

        $this->poCaptureCoverage = [
            'requested' => $preview['requested'],
            'covered' => $preview['covered'],
            'uncovered' => $preview['uncovered'],
            'rows' => $preview['rows'],
        ];
    }

    private function poCaptureBlanketLabel(CustomerPurchaseOrder $po): string
    {
        $parts = [(string) $po->po_number, number_format((int) $po->remaining_total).' left'];

        if ($po->valid_to !== null) {
            $parts[] = 'valid to '.$po->valid_to->format('d M Y');
        }

        return implode(' · ', $parts);
    }
}
