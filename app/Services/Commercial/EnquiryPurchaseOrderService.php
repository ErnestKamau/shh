<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\DTOs\Commercial\PurchaseOrderAllocationLine;
use App\DTOs\Commercial\PurchaseOrderAllocationResult;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\EnquiryPurchaseOrderChange;
use App\Models\EnquiryQuotation;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\SampleType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Links enquiries to customer POs: capture (blanket / single / none), reservation at
 * Ready for Reception, coverage previews, and the audited PO change at Integrity Check.
 *
 * Reception is never blocked for a missing PO. The coverage requirement only decides
 * whether uncovered samples will be held as Awaiting PO when the job is created.
 */
final class EnquiryPurchaseOrderService
{
    public const MODE_BLANKET = 'blanket';

    public const MODE_SINGLE = 'single';

    public const MODE_NONE = 'none';

    /** Credit account or sampling contract: uncovered samples are held as Awaiting PO. */
    public const REQUIREMENT_REQUIRED = 'required';

    /** Advance payment: a PO is welcome but nothing is held without one. */
    public const REQUIREMENT_OPTIONAL = 'optional';

    /** Walk-in: the PO may be skipped outright. */
    public const REQUIREMENT_SKIPPABLE = 'skippable';

    /** @var list<string> */
    public const CHANGEABLE_STATUSES = [
        SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
        SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
        SampleSubmissionRequest::STATUS_IN_REVIEW,
    ];

    /** @var list<string> Enquiries that can skip the quotation step by drawing on a blanket PO. */
    public const BLANKET_SHORTCUT_STATUSES = [
        SampleSubmissionRequest::STATUS_REQUESTED,
        SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
    ];

    public function __construct(
        private readonly PurchaseOrderAllocationService $allocation,
        private readonly PurchaseOrderDemandBuilder $demandBuilder,
        private readonly QuotationPurchaseOrderLineMapper $lineMapper,
        private readonly EnquiryAccountSettingsService $accountSettings,
        private readonly ContractCustomerService $contracts,
        private readonly EnquiryQuotationService $enquiryQuotations,
        private readonly CustomerPurchaseOrderService $purchaseOrders,
        private readonly EnquiryReceptionReadinessService $readiness,
    ) {}

    public static function enabled(): bool
    {
        return (bool) config('purchase_orders.enabled');
    }

    public function requirement(SampleSubmissionRequest $enquiry): string
    {
        $enquiry->loadMissing('customer');

        if ($this->contracts->bypassesCommercialQuotationGate($enquiry)) {
            return self::REQUIREMENT_REQUIRED;
        }

        $rules = $this->accountSettings->poRulesForCustomer($enquiry->customer);

        return match (true) {
            $rules['requires_po'] => self::REQUIREMENT_REQUIRED,
            $rules['allows_po_skip'] => self::REQUIREMENT_SKIPPABLE,
            default => self::REQUIREMENT_OPTIONAL,
        };
    }

    public function requirementMessage(string $requirement): string
    {
        return match ($requirement) {
            self::REQUIREMENT_REQUIRED => 'This customer needs PO cover. You can still mark the request ready; samples without cover will be held as Awaiting PO when the job is created.',
            self::REQUIREMENT_OPTIONAL => 'A PO is optional for this customer (advance payment).',
            default => 'This customer may proceed without a PO.',
        };
    }

    /**
     * Blanket POs the customer can draw on today (active or exhausted, inside their validity window).
     *
     * @return Collection<int, CustomerPurchaseOrder>
     */
    public function blanketOrdersFor(?string $customerId): Collection
    {
        if ($customerId === null || trim($customerId) === '') {
            return new Collection;
        }

        return $this->drawableBlanketQuery()
            ->where('customer_id', $customerId)
            ->withSum('lines as remaining_total', 'remaining_qty')
            ->orderBy('valid_to')
            ->orderBy('po_number')
            ->get();
    }

    /**
     * The blanket PO to pick automatically: only when exactly one has balance left.
     */
    public function autoSelectBlanket(?string $customerId): ?CustomerPurchaseOrder
    {
        $withBalance = $this->blanketOrdersFor($customerId)
            ->filter(fn (CustomerPurchaseOrder $po): bool => (int) $po->remaining_total > 0)
            ->values();

        return $withBalance->count() === 1 ? $withBalance->first() : null;
    }

    /**
     * @param  list<string>|null  $customerIds  Null for every customer.
     * @return array<string, true>
     */
    public function customerIdsWithBlanketOrders(?array $customerIds = null): array
    {
        if ($customerIds !== null) {
            $customerIds = array_values(array_unique(array_filter(array_map('strval', $customerIds))));
            if ($customerIds === []) {
                return [];
            }
        }

        return $this->drawableBlanketQuery()
            ->whereNotNull('customer_id')
            ->when($customerIds !== null, fn ($query) => $query->whereIn('customer_id', $customerIds))
            ->distinct()
            ->pluck('customer_id')
            ->mapWithKeys(fn ($id): array => [(string) $id => true])
            ->all();
    }

    public function canUseBlanketShortcut(SampleSubmissionRequest $enquiry): bool
    {
        return self::enabled()
            && in_array((string) $enquiry->status, self::BLANKET_SHORTCUT_STATUSES, true)
            && filled($enquiry->crm_customer_id)
            && $this->blanketOrdersFor((string) $enquiry->crm_customer_id)->isNotEmpty();
    }

    public function canChangeAtReception(SampleSubmissionRequest $enquiry): bool
    {
        return self::enabled()
            && in_array((string) $enquiry->status, self::CHANGEABLE_STATUSES, true)
            && blank($enquiry->sample_header_id);
    }

    /**
     * @param  array{mode?: ?string, customer_purchase_order_id?: ?string, client_po_number?: ?string, po_skipped?: mixed}  $payload
     * @return array{mode: string, customer_purchase_order_id: ?string, client_po_number: ?string, po_skipped: bool}
     */
    public function normaliseCapture(SampleSubmissionRequest $enquiry, array $payload): array
    {
        $purchaseOrderId = trim((string) ($payload['customer_purchase_order_id'] ?? ''));
        $poNumber = trim((string) ($payload['client_po_number'] ?? ''));
        $mode = trim((string) ($payload['mode'] ?? ''));
        $requirement = $this->requirement($enquiry);

        if (! in_array($mode, [self::MODE_BLANKET, self::MODE_SINGLE, self::MODE_NONE], true)) {
            $mode = match (true) {
                $purchaseOrderId !== '' => self::MODE_BLANKET,
                $poNumber !== '' => self::MODE_SINGLE,
                default => self::MODE_NONE,
            };
        }

        if ($mode === self::MODE_SINGLE && $poNumber === '') {
            $mode = self::MODE_NONE;
        }

        $explicitSkip = array_key_exists('po_skipped', $payload)
            && filter_var($payload['po_skipped'], FILTER_VALIDATE_BOOLEAN);

        if ($mode === self::MODE_NONE && $explicitSkip && $requirement !== self::REQUIREMENT_SKIPPABLE) {
            throw ValidationException::withMessages([
                'po_skipped' => 'Skipping PO is not allowed for this customer account.',
            ]);
        }

        return match ($mode) {
            self::MODE_BLANKET => [
                'mode' => self::MODE_BLANKET,
                'customer_purchase_order_id' => $purchaseOrderId !== '' ? $purchaseOrderId : null,
                'client_po_number' => null,
                'po_skipped' => false,
            ],
            self::MODE_SINGLE => [
                'mode' => self::MODE_SINGLE,
                'customer_purchase_order_id' => null,
                'client_po_number' => $poNumber,
                'po_skipped' => false,
            ],
            default => [
                'mode' => self::MODE_NONE,
                'customer_purchase_order_id' => null,
                'client_po_number' => null,
                'po_skipped' => $requirement === self::REQUIREMENT_SKIPPABLE,
            ],
        };
    }

    /**
     * Record the PO choice on the enquiry without changing its status. Any reservation held on
     * a PO the enquiry moves away from is released.
     *
     * @param  array{mode?: ?string, customer_purchase_order_id?: ?string, client_po_number?: ?string, po_skipped?: mixed}  $payload
     */
    public function capture(
        SampleSubmissionRequest $enquiry,
        array $payload,
        ?UploadedFile $file = null,
        ?string $quotationHeaderId = null,
        ?string $userId = null,
    ): SampleSubmissionRequest {
        $enquiry->loadMissing('customer');
        $capture = $this->normaliseCapture($enquiry, $payload);
        $blanket = $capture['mode'] === self::MODE_BLANKET
            ? $this->resolveDrawableBlanket($enquiry, $capture['customer_purchase_order_id'])
            : null;
        $userId = $this->userId($userId);
        $quotationHeaderId = filled($quotationHeaderId) ? (string) $quotationHeaderId : null;

        return DB::transaction(function () use ($enquiry, $capture, $blanket, $file, $quotationHeaderId, $userId): SampleSubmissionRequest {
            $previousPurchaseOrderId = filled($enquiry->customer_purchase_order_id) ? (string) $enquiry->customer_purchase_order_id : null;

            if ($blanket !== null) {
                $this->bindBlanket($enquiry, $blanket, $userId);
            } elseif ($capture['mode'] === self::MODE_SINGLE) {
                $po = $this->purchaseOrders->upsertForEnquiry(
                    $enquiry,
                    ['client_po_number' => $capture['client_po_number'], 'po_skipped' => false],
                    $file,
                    $quotationHeaderId,
                    $userId,
                );
                $this->ensureSingleOrderLines($po, $quotationHeaderId ?? $this->enquiryQuotationId($enquiry), $userId);

                $enquiry->customer_purchase_order_id = (string) $po->id;
                $enquiry->save();
            } else {
                if ($capture['po_skipped']) {
                    $enquiry->client_po_number = null;
                    $this->purchaseOrders->upsertForEnquiry($enquiry, ['client_po_number' => null, 'po_skipped' => true], null, $quotationHeaderId, $userId);
                }

                $enquiry->customer_purchase_order_id = null;
                $enquiry->client_po_number = null;
                $enquiry->po_skipped = $capture['po_skipped'];
                $enquiry->save();
            }

            $currentPurchaseOrderId = filled($enquiry->customer_purchase_order_id) ? (string) $enquiry->customer_purchase_order_id : null;
            if ($previousPurchaseOrderId !== null && $previousPurchaseOrderId !== $currentPurchaseOrderId) {
                $previous = CustomerPurchaseOrder::query()->find($previousPurchaseOrderId);
                if ($previous !== null) {
                    $this->allocation->releaseReservation($previous, (string) $enquiry->id, 'Enquiry moved to another PO', $userId);
                }
            }

            return $enquiry->fresh(['customer']) ?? $enquiry;
        });
    }

    /**
     * Capture the PO, then mark the enquiry Ready for Reception (which reserves PO cover).
     *
     * @param  array{mode?: ?string, customer_purchase_order_id?: ?string, client_po_number?: ?string, po_skipped?: mixed}  $payload
     */
    public function captureAndMarkReady(
        SampleSubmissionRequest $enquiry,
        array $payload,
        ?UploadedFile $file = null,
        ?string $quotationHeaderId = null,
        ?string $userId = null,
    ): SampleSubmissionRequest {
        return DB::transaction(function () use ($enquiry, $payload, $file, $quotationHeaderId, $userId): SampleSubmissionRequest {
            $captured = $this->capture($enquiry, $payload, $file, $quotationHeaderId, $userId);
            $quotationId = filled($quotationHeaderId) ? (string) $quotationHeaderId : $this->enquiryQuotationId($captured);

            return $this->readiness->markReadyForReception(
                $captured,
                $quotationId,
                [
                    'client_po_number' => $captured->client_po_number,
                    'po_skipped' => (bool) $captured->po_skipped,
                ],
            );
        });
    }

    /**
     * Blanket-PO enquiry: skip the quotation step, bind the PO and go straight to Ready for Reception.
     */
    public function useBlanketPurchaseOrder(SampleSubmissionRequest $enquiry, string $purchaseOrderId, ?string $userId = null): SampleSubmissionRequest
    {
        if (! in_array((string) $enquiry->status, self::BLANKET_SHORTCUT_STATUSES, true)) {
            throw ValidationException::withMessages([
                'customer_purchase_order_id' => 'A purchase order can only replace the quotation step before a quotation has been sent.',
            ]);
        }

        return $this->captureAndMarkReady($enquiry, [
            'mode' => self::MODE_BLANKET,
            'customer_purchase_order_id' => $purchaseOrderId,
        ], null, null, $userId);
    }

    /**
     * Reserve PO cover for the enquiry's requested samples (Ready for Reception). Replaces any earlier
     * reservation. Never throws for ledger problems: reception must not be blocked.
     */
    public function syncReservation(SampleSubmissionRequest $enquiry, ?string $userId = null): ?PurchaseOrderAllocationResult
    {
        if (! self::enabled() || blank($enquiry->customer_purchase_order_id)) {
            return null;
        }

        $po = CustomerPurchaseOrder::query()->find((string) $enquiry->customer_purchase_order_id);
        if ($po === null) {
            return null;
        }

        try {
            $demand = $this->demandBuilder->fromEnquiry($enquiry, $po->lines()->get());

            if ($demand === []) {
                $this->allocation->releaseReservation($po, (string) $enquiry->id, 'No samples requested', $userId);

                return null;
            }

            return $this->allocation->reserve($po, $demand, (string) $enquiry->id, now(), $this->userId($userId));
        } catch (PurchaseOrderLedgerException $exception) {
            Log::warning('Could not reserve PO cover for enquiry.', [
                'enquiry_id' => (string) $enquiry->id,
                'customer_purchase_order_id' => (string) $po->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function releaseReservation(SampleSubmissionRequest $enquiry, string $reason, ?string $userId = null): void
    {
        if (! self::enabled() || blank($enquiry->customer_purchase_order_id)) {
            return;
        }

        $po = CustomerPurchaseOrder::query()->find((string) $enquiry->customer_purchase_order_id);
        if ($po !== null) {
            $this->allocation->releaseReservation($po, (string) $enquiry->id, $reason, $this->userId($userId));
        }
    }

    /**
     * Change the enquiry's PO after it is ready for reception (Integrity Check). Audited with a reason.
     *
     * @param  array{mode?: ?string, customer_purchase_order_id?: ?string, client_po_number?: ?string, po_skipped?: mixed}  $payload
     */
    public function changeAtReception(
        SampleSubmissionRequest $enquiry,
        array $payload,
        ?UploadedFile $file,
        string $reason,
        ?string $userId = null,
    ): EnquiryPurchaseOrderChange {
        if (! $this->canChangeAtReception($enquiry)) {
            throw ValidationException::withMessages([
                'reason' => 'The PO can only be changed while the samples are at reception, before the job is created.',
            ]);
        }

        $userId = $this->userId($userId);
        $fromId = filled($enquiry->customer_purchase_order_id) ? (string) $enquiry->customer_purchase_order_id : null;
        $fromNumber = filled($enquiry->client_po_number) ? (string) $enquiry->client_po_number : null;
        $capture = $this->normaliseCapture($enquiry, $payload);

        if ($capture['mode'] === self::MODE_BLANKET && $capture['customer_purchase_order_id'] === $fromId) {
            throw ValidationException::withMessages(['customer_purchase_order_id' => 'The enquiry is already on this PO.']);
        }

        if ($capture['mode'] === self::MODE_SINGLE && $fromNumber !== null && strcasecmp($capture['client_po_number'], $fromNumber) === 0) {
            throw ValidationException::withMessages(['client_po_number' => 'The enquiry already has this PO number.']);
        }

        if ($capture['mode'] === self::MODE_NONE && $fromId === null && $fromNumber === null) {
            throw ValidationException::withMessages(['mode' => 'The enquiry has no PO to remove.']);
        }

        return DB::transaction(function () use ($enquiry, $payload, $file, $reason, $userId, $fromId, $fromNumber): EnquiryPurchaseOrderChange {
            $updated = $this->capture($enquiry, $payload, $file, null, $userId);
            $this->syncReservation($updated, $userId);

            return EnquiryPurchaseOrderChange::query()->create([
                'sample_submission_request_id' => (string) $updated->id,
                'from_customer_purchase_order_id' => $fromId,
                'to_customer_purchase_order_id' => filled($updated->customer_purchase_order_id) ? (string) $updated->customer_purchase_order_id : null,
                'from_po_number' => $fromNumber,
                'to_po_number' => filled($updated->client_po_number) ? (string) $updated->client_po_number : null,
                'enquiry_status' => (string) $updated->status,
                'reason' => trim($reason),
                'changed_by' => $userId,
            ]);
        });
    }

    /**
     * Coverage of the enquiry's requested samples by its bound PO.
     *
     * @return array{
     *     requirement: string,
     *     purchase_order: ?CustomerPurchaseOrder,
     *     requested: int,
     *     covered: int,
     *     uncovered: int,
     *     reserved: int,
     *     rows: list<array{key: string, label: string, requested: int, covered: int, uncovered: int, reason: ?string}>
     * }
     */
    public function coverage(SampleSubmissionRequest $enquiry): array
    {
        $po = filled($enquiry->customer_purchase_order_id)
            ? CustomerPurchaseOrder::query()->find((string) $enquiry->customer_purchase_order_id)
            : null;

        return $this->previewFor($enquiry, $po);
    }

    /**
     * Forecast how a candidate PO would cover the enquiry's requested samples. Writes nothing.
     *
     * @return array{
     *     requirement: string,
     *     purchase_order: ?CustomerPurchaseOrder,
     *     requested: int,
     *     covered: int,
     *     uncovered: int,
     *     reserved: int,
     *     rows: list<array{key: string, label: string, requested: int, covered: int, uncovered: int, reason: ?string}>
     * }
     */
    public function previewFor(SampleSubmissionRequest $enquiry, ?CustomerPurchaseOrder $po): array
    {
        $demand = $this->demandBuilder->fromEnquiry($enquiry, $po?->lines()->get());
        $labels = $this->demandLabels($demand);
        $result = $po !== null ? $this->allocation->preview($po, $demand, (string) $enquiry->id) : null;

        $rows = [];
        foreach ($demand as $item) {
            $line = $result?->forDemand($item->key);
            $covered = $line?->covered ?? 0;

            $rows[] = [
                'key' => $item->key,
                'label' => $labels[$item->key] ?? 'Samples',
                'requested' => $item->quantity,
                'covered' => $covered,
                'uncovered' => $item->quantity - $covered,
                'reason' => $po === null
                    ? 'No PO on this enquiry.'
                    : ($covered < $item->quantity ? $this->reasonLabel($line?->uncoveredReason) : null),
            ];
        }

        $requested = array_sum(array_column($rows, 'requested'));
        $covered = array_sum(array_column($rows, 'covered'));

        return [
            'requirement' => $this->requirement($enquiry),
            'purchase_order' => $po,
            'requested' => $requested,
            'covered' => $covered,
            'uncovered' => $requested - $covered,
            'reserved' => $po !== null ? $this->allocation->openReservationTotal($po, (string) $enquiry->id) : 0,
            'rows' => $rows,
        ];
    }

    public function reasonLabel(?string $reason): string
    {
        return match ($reason) {
            PurchaseOrderAllocationLine::REASON_NO_MATCHING_LINE => 'No line on the PO for this sample type / analysis.',
            PurchaseOrderAllocationLine::REASON_PO_NOT_ACTIVE => 'The PO is not active.',
            PurchaseOrderAllocationLine::REASON_OUTSIDE_VALIDITY => 'The PO is outside its validity dates.',
            PurchaseOrderAllocationLine::REASON_INSUFFICIENT_BALANCE => 'Not enough balance left on the PO line.',
            default => 'Not covered.',
        };
    }

    private function bindBlanket(SampleSubmissionRequest $enquiry, CustomerPurchaseOrder $po, ?string $userId): void
    {
        $enquiry->customer_purchase_order_id = (string) $po->id;
        $enquiry->client_po_number = (string) $po->po_number;
        $enquiry->po_skipped = false;

        if (filled($po->quotation_header_id)) {
            $quotation = QuotationHeader::query()->find((string) $po->quotation_header_id);

            if ($quotation !== null) {
                $this->enquiryQuotations->linkEnquiryToQuotation($enquiry, $quotation, EnquiryQuotation::LINK_SOURCE_PURCHASE_ORDER, $userId);

                if (blank($enquiry->accepted_quotation_header_id)) {
                    $enquiry->accepted_quotation_header_id = (string) $quotation->id;
                }
            }
        }

        $enquiry->save();
    }

    /**
     * A single PO recorded at acceptance covers what was quoted: give it lines from the quotation
     * the first time it is captured.
     */
    private function ensureSingleOrderLines(CustomerPurchaseOrder $po, ?string $quotationHeaderId, ?string $userId): void
    {
        if (! self::enabled() || $quotationHeaderId === null || $po->lines()->exists()) {
            return;
        }

        $quotation = QuotationHeader::query()->with('details.sampletype')->find($quotationHeaderId);
        if ($quotation === null) {
            return;
        }

        foreach ($this->lineMapper->draftsForQuotation($quotation) as $draft) {
            if ((int) $draft['ordered_qty'] <= 0) {
                continue;
            }

            $this->allocation->addLine($po, [
                'description' => $draft['description'],
                'ordered_qty' => (int) $draft['ordered_qty'],
                'unit_price_gross' => $draft['unit_price_gross'],
                'sample_type_id' => $draft['sample_type_id'],
                'analysis_type_ids' => $draft['analysis_type_ids'],
                'is_package' => $draft['is_package'],
                'quotation_detail_id' => $draft['quotation_detail_id'],
            ], $userId);
        }
    }

    private function resolveDrawableBlanket(SampleSubmissionRequest $enquiry, ?string $purchaseOrderId): CustomerPurchaseOrder
    {
        $po = $purchaseOrderId !== null ? CustomerPurchaseOrder::query()->find($purchaseOrderId) : null;

        $error = match (true) {
            $po === null => 'Select a purchase order.',
            (string) $po->customer_id !== (string) $enquiry->crm_customer_id => 'The selected PO belongs to another customer.',
            ! $po->isBlanket() => 'The selected PO is not a blanket PO.',
            ! $po->acceptsDrawsOn(now()) => 'The selected PO is '.strtolower($po->effectiveStatus()->label()).' and cannot cover new samples.',
            default => null,
        };

        if ($error !== null) {
            throw ValidationException::withMessages(['customer_purchase_order_id' => $error]);
        }

        return $po;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<CustomerPurchaseOrder>
     */
    private function drawableBlanketQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $today = now()->toDateString();

        return CustomerPurchaseOrder::query()
            ->where('po_type', PurchaseOrderType::Blanket->value)
            ->whereIn('status', [PurchaseOrderStatus::Active->value, PurchaseOrderStatus::Exhausted->value])
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn ($query) => $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today));
    }

    /**
     * @param  list<\App\DTOs\Commercial\PurchaseOrderDemandItem>  $demand
     * @return array<string, string>
     */
    private function demandLabels(array $demand): array
    {
        $sampleTypeIds = array_values(array_unique(array_filter(array_map(fn ($item): ?string => $item->sampleTypeId, $demand))));
        $analysisTypeIds = array_values(array_unique(array_merge([], ...array_map(fn ($item): array => $item->analysisTypeIds, $demand))));

        $elementIds = array_values(array_unique(array_merge([], ...array_map(fn ($item): array => $item->analysisElementIds, $demand))));

        $sampleTypes = $sampleTypeIds !== [] ? SampleType::query()->whereIn('id', $sampleTypeIds)->pluck('name', 'id') : collect();
        $analysisTypes = $analysisTypeIds !== [] ? AnalysisType::query()->whereIn('id', $analysisTypeIds)->pluck('name', 'id') : collect();
        $elements = $elementIds !== []
            ? AnalysisElements::query()->with('analyte:id,name')->whereIn('id', $elementIds)->get(['id', 'analyte_id', 'report_display_name'])
                ->mapWithKeys(fn (AnalysisElements $element): array => [(string) $element->id => $element->analyte?->name ?: $element->report_display_name])
            : collect();

        $labels = [];
        foreach ($demand as $item) {
            $parts = array_filter([
                $item->sampleTypeId !== null ? ($sampleTypes[$item->sampleTypeId] ?? null) : null,
                implode(', ', array_filter(array_map(fn (string $id): ?string => $analysisTypes[$id] ?? null, $item->analysisTypeIds))),
                implode(', ', array_filter(array_map(fn (string $id): ?string => $elements[$id] ?? null, $item->analysisElementIds))),
            ]);

            $labels[$item->key] = $parts !== [] ? implode(' — ', $parts) : 'Samples';
        }

        return $labels;
    }

    private function enquiryQuotationId(SampleSubmissionRequest $enquiry): ?string
    {
        $id = $enquiry->accepted_quotation_header_id ?? $enquiry->current_quotation_header_id;

        return filled($id) ? (string) $id : null;
    }

    private function userId(?string $userId): ?string
    {
        return $userId ?? (Auth::id() !== null ? (string) Auth::id() : null);
    }
}
