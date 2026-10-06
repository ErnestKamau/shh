<?php

namespace App\Services\Commercial;

use App\Enums\Commercial\PurchaseOrderAmendmentType;
use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderAmendment;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\QuotationHeader;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Registry-side PO lifecycle: create blanket POs and apply audited amendments
 * (top-up, reduce, add line, validity change, detail edits, close, cancel).
 *
 * Quantity changes go through PurchaseOrderAllocationService so the ledger entry carries the amendment id.
 */
final class CustomerPurchaseOrderRegistryService
{
    public const FILE_DIRECTORY = 'customer-purchase-orders/registry';

    public function __construct(
        private readonly PurchaseOrderAllocationService $allocationService,
    ) {}

    /**
     * @param  array{
     *     po_number: string,
     *     customer_id: string,
     *     quotation_header_id?: ?string,
     *     currency_id?: ?string,
     *     valid_from: string,
     *     valid_to: string,
     *     invoicing_mode: string,
     *     invoicing_period?: ?string,
     *     expiry_notice_days?: ?int,
     *     notes?: ?string
     * }  $attributes
     * @param  list<array{
     *     description: string,
     *     ordered_qty: int|string,
     *     unit_price_gross: float|int|string,
     *     sample_type_id?: ?string,
     *     analysis_type_ids?: list<string>,
     *     analysis_element_ids?: list<string>,
     *     is_package?: bool,
     *     quotation_detail_id?: ?string,
     *     notify_remaining_qty?: int|string|null
     * }>  $lines
     */
    public function createBlanket(array $attributes, array $lines, ?UploadedFile $file = null, ?string $userId = null): CustomerPurchaseOrder
    {
        $userId = $this->userId($userId);
        $poNumber = trim((string) $attributes['po_number']);
        $customerId = (string) $attributes['customer_id'];
        $quotationId = filled($attributes['quotation_header_id'] ?? null) ? (string) $attributes['quotation_header_id'] : null;

        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'Add at least one line to the purchase order.']);
        }

        $this->assertThresholdsBelowOrdered($lines);
        $this->assertNoDuplicateBlanket($customerId, $poNumber);

        $currencyId = filled($attributes['currency_id'] ?? null) ? (string) $attributes['currency_id'] : null;
        if ($quotationId !== null) {
            $quotation = QuotationHeader::query()->find($quotationId);
            if ($quotation === null || (string) $quotation->crm_customer_id !== $customerId) {
                throw ValidationException::withMessages(['quotation_header_id' => 'The selected quotation does not belong to this customer.']);
            }
            $currencyId ??= filled($quotation->currency_id) ? (string) $quotation->currency_id : null;
        }

        $storedFile = $file !== null ? $this->storeFile($file) : null;

        try {
            return DB::transaction(function () use ($attributes, $lines, $poNumber, $customerId, $quotationId, $currencyId, $storedFile, $userId): CustomerPurchaseOrder {
                $mode = PurchaseOrderInvoicingMode::from((string) $attributes['invoicing_mode']);

                $po = CustomerPurchaseOrder::query()->create(array_merge([
                    'po_number' => $poNumber,
                    'po_skipped' => false,
                    'po_type' => PurchaseOrderType::Blanket,
                    'status' => PurchaseOrderStatus::Active,
                    'customer_id' => $customerId,
                    'quotation_header_id' => $quotationId,
                    'currency_id' => $currencyId,
                    'valid_from' => $attributes['valid_from'],
                    'valid_to' => $attributes['valid_to'],
                    'invoicing_mode' => $mode,
                    'invoicing_period' => $mode === PurchaseOrderInvoicingMode::Periodic ? ($attributes['invoicing_period'] ?? null) : null,
                    'expiry_notice_days' => (int) ($attributes['expiry_notice_days'] ?? config('purchase_orders.default_expiry_notice_days', 30)),
                    'notes' => filled($attributes['notes'] ?? null) ? trim((string) $attributes['notes']) : null,
                    'uploaded_by' => $userId,
                    'recorded_at' => now(),
                ], $storedFile ?? []));

                foreach (array_values($lines) as $index => $line) {
                    $this->allocationService->addLine($po, $this->lineAttributes($line, $index + 1), $userId);
                }

                return $po->fresh(['lines']) ?? $po;
            });
        } catch (Throwable $exception) {
            if ($storedFile !== null) {
                Storage::disk('public')->delete($storedFile['file_path']);
            }

            throw $exception;
        }
    }

    /**
     * Top up (positive delta) or reduce (negative delta) a line's ordered quantity.
     */
    public function adjustLineQuantity(CustomerPurchaseOrderLine $line, int $delta, string $reason, ?string $userId = null): CustomerPurchaseOrderAmendment
    {
        if ($delta === 0) {
            throw ValidationException::withMessages(['quantity' => 'Enter a quantity greater than zero.']);
        }

        $userId = $this->userId($userId);

        return DB::transaction(function () use ($line, $delta, $reason, $userId): CustomerPurchaseOrderAmendment {
            $po = $this->lockAmendable((string) $line->customer_purchase_order_id);

            $amendment = $this->recordAmendment(
                $po,
                $delta > 0 ? PurchaseOrderAmendmentType::TopUp : PurchaseOrderAmendmentType::Reduce,
                ['delta' => $delta],
                $reason,
                $userId,
                (string) $line->id,
            );

            try {
                $updated = $this->allocationService->adjustOrderedQuantity($line, $delta, $reason, $userId, (string) $amendment->id);
            } catch (PurchaseOrderLedgerException) {
                throw ValidationException::withMessages([
                    'quantity' => 'The line cannot be reduced below the quantity already reserved or committed ('
                        .((int) $line->reserved_qty + (int) $line->committed_qty).').',
                ]);
            }

            $amendment->changes = [
                'ordered_qty' => [
                    'from' => (int) $updated->ordered_qty - $delta,
                    'to' => (int) $updated->ordered_qty,
                ],
                'delta' => $delta,
            ];
            $amendment->save();

            return $amendment;
        });
    }

    /**
     * @param  array{
     *     description: string,
     *     ordered_qty: int|string,
     *     unit_price_gross: float|int|string,
     *     sample_type_id?: ?string,
     *     analysis_type_ids?: list<string>,
     *     analysis_element_ids?: list<string>,
     *     is_package?: bool,
     *     quotation_detail_id?: ?string,
     *     notify_remaining_qty?: int|string|null
     * }  $line
     */
    public function addLine(CustomerPurchaseOrder $purchaseOrder, array $line, string $reason, ?string $userId = null): CustomerPurchaseOrderLine
    {
        $this->assertThresholdsBelowOrdered([$line], 'line');
        $userId = $this->userId($userId);

        return DB::transaction(function () use ($purchaseOrder, $line, $reason, $userId): CustomerPurchaseOrderLine {
            $po = $this->lockAmendable((string) $purchaseOrder->id);

            $amendment = $this->recordAmendment($po, PurchaseOrderAmendmentType::AddLine, [], $reason, $userId);

            $created = $this->allocationService->addLine($po, $this->lineAttributes($line), $userId, (string) $amendment->id);

            $amendment->customer_purchase_order_line_id = $created->id;
            $amendment->changes = [
                'line_no' => (int) $created->line_no,
                'description' => (string) $created->description,
                'ordered_qty' => (int) $created->ordered_qty,
                'unit_price_gross' => (string) $created->unit_price_gross,
            ];
            $amendment->save();

            return $created;
        });
    }

    /**
     * Move the validity window. Reactivates an expired PO when the new end date is today or later.
     */
    public function changeValidity(CustomerPurchaseOrder $purchaseOrder, ?string $validFrom, string $validTo, string $reason, ?string $userId = null): CustomerPurchaseOrder
    {
        $userId = $this->userId($userId);

        return DB::transaction(function () use ($purchaseOrder, $validFrom, $validTo, $reason, $userId): CustomerPurchaseOrder {
            $po = $this->lockAmendable((string) $purchaseOrder->id);

            $newFrom = filled($validFrom) ? Carbon::parse($validFrom)->startOfDay() : $po->valid_from;
            $newTo = Carbon::parse($validTo)->startOfDay();

            if ($newFrom !== null && $newTo->lt($newFrom)) {
                throw ValidationException::withMessages(['valid_to' => 'The end date must be on or after the start date.']);
            }

            $changes = [];
            if ($newFrom?->toDateString() !== $po->valid_from?->toDateString()) {
                $changes['valid_from'] = ['from' => $po->valid_from?->toDateString(), 'to' => $newFrom?->toDateString()];
            }
            if ($newTo->toDateString() !== $po->valid_to?->toDateString()) {
                $changes['valid_to'] = ['from' => $po->valid_to?->toDateString(), 'to' => $newTo->toDateString()];
            }

            if ($changes === []) {
                throw ValidationException::withMessages(['valid_to' => 'The validity dates are unchanged.']);
            }

            $movedLater = $po->valid_to === null || $newTo->gt($po->valid_to);

            $po->valid_from = $newFrom;
            $po->valid_to = $newTo;

            if ($movedLater) {
                $po->expiry_notified_at = null;
            }

            if ($po->status === PurchaseOrderStatus::Expired && $newTo->gte(now()->startOfDay())) {
                $po->status = $this->balanceStatus($po);
                $changes['status'] = ['from' => PurchaseOrderStatus::Expired->value, 'to' => $po->status->value];
            }

            $po->save();

            $this->recordAmendment($po, PurchaseOrderAmendmentType::ExtendValidity, $changes, $reason, $userId);

            return $po;
        });
    }

    /**
     * Edit non-quantity details. Only fields that actually change are written to the amendment.
     *
     * @param  array{
     *     po_number?: string,
     *     invoicing_mode?: string,
     *     invoicing_period?: ?string,
     *     expiry_notice_days?: int|string,
     *     notes?: ?string
     * }  $details
     * @param  array<string, int|string|null>  $lineThresholds  line id => notify_remaining_qty
     */
    public function updateDetails(
        CustomerPurchaseOrder $purchaseOrder,
        array $details,
        array $lineThresholds,
        ?UploadedFile $file,
        string $reason,
        ?string $userId = null,
    ): ?CustomerPurchaseOrderAmendment {
        $userId = $this->userId($userId);
        $storedFile = $file !== null ? $this->storeFile($file) : null;

        try {
            return DB::transaction(function () use ($purchaseOrder, $details, $lineThresholds, $storedFile, $reason, $userId): ?CustomerPurchaseOrderAmendment {
                $po = $this->lockAmendable((string) $purchaseOrder->id);
                $changes = [];

                if (array_key_exists('po_number', $details)) {
                    $poNumber = trim((string) $details['po_number']);
                    if ($poNumber !== '' && $poNumber !== (string) $po->po_number) {
                        if ($po->isBlanket()) {
                            $this->assertNoDuplicateBlanket((string) $po->customer_id, $poNumber, (string) $po->id);
                        }
                        $changes['po_number'] = ['from' => $po->po_number, 'to' => $poNumber];
                        $po->po_number = $poNumber;
                    }
                }

                if (array_key_exists('invoicing_mode', $details)) {
                    $mode = PurchaseOrderInvoicingMode::from((string) $details['invoicing_mode']);
                    $period = $mode === PurchaseOrderInvoicingMode::Periodic ? ($details['invoicing_period'] ?? null) : null;

                    if ($mode !== $po->invoicing_mode) {
                        $changes['invoicing_mode'] = ['from' => $po->invoicing_mode?->value, 'to' => $mode->value];
                        $po->invoicing_mode = $mode;
                    }
                    if ($period !== $po->invoicing_period) {
                        $changes['invoicing_period'] = ['from' => $po->invoicing_period, 'to' => $period];
                        $po->invoicing_period = $period;
                    }
                }

                if (array_key_exists('expiry_notice_days', $details)) {
                    $days = (int) $details['expiry_notice_days'];
                    if ($days !== (int) $po->expiry_notice_days) {
                        $changes['expiry_notice_days'] = ['from' => (int) $po->expiry_notice_days, 'to' => $days];
                        $po->expiry_notice_days = $days;
                        $po->expiry_notified_at = null;
                    }
                }

                if (array_key_exists('notes', $details)) {
                    $notes = filled($details['notes']) ? trim((string) $details['notes']) : null;
                    if ($notes !== $po->notes) {
                        $changes['notes'] = ['from' => $po->notes, 'to' => $notes];
                        $po->notes = $notes;
                    }
                }

                $previousFile = null;
                if ($storedFile !== null) {
                    $changes['file'] = ['from' => $po->file_name, 'to' => $storedFile['file_name']];
                    $previousFile = $po->file_path;
                    $po->fill($storedFile);
                }

                $changes = array_merge($changes, $this->applyLineThresholds($po, $lineThresholds));

                if ($changes === []) {
                    return null;
                }

                $po->save();

                if (filled($previousFile)) {
                    DB::afterCommit(static fn () => Storage::disk('public')->delete((string) $previousFile));
                }

                return $this->recordAmendment($po, PurchaseOrderAmendmentType::UpdateDetails, $changes, $reason, $userId);
            });
        } catch (Throwable $exception) {
            if ($storedFile !== null) {
                Storage::disk('public')->delete($storedFile['file_path']);
            }

            throw $exception;
        }
    }

    /**
     * Close the PO: no further draws. Open reservations are released; committed jobs keep their cover.
     */
    public function close(CustomerPurchaseOrder $purchaseOrder, string $reason, ?string $userId = null): CustomerPurchaseOrder
    {
        return $this->finalise($purchaseOrder, PurchaseOrderStatus::Closed, PurchaseOrderAmendmentType::Close, $reason, $this->userId($userId));
    }

    /**
     * Cancel a PO recorded in error. Only allowed while nothing has been committed against it.
     */
    public function cancel(CustomerPurchaseOrder $purchaseOrder, string $reason, ?string $userId = null): CustomerPurchaseOrder
    {
        $committed = (int) CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $purchaseOrder->id)
            ->sum('committed_qty');

        if ($committed > 0) {
            throw ValidationException::withMessages([
                'reason' => "This PO already covers {$committed} received sample(s). Close it instead of cancelling.",
            ]);
        }

        return $this->finalise($purchaseOrder, PurchaseOrderStatus::Cancelled, PurchaseOrderAmendmentType::Cancel, $reason, $this->userId($userId));
    }

    private function finalise(
        CustomerPurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $target,
        PurchaseOrderAmendmentType $type,
        string $reason,
        ?string $userId,
    ): CustomerPurchaseOrder {
        return DB::transaction(function () use ($purchaseOrder, $target, $type, $reason, $userId): CustomerPurchaseOrder {
            $this->lockAmendable((string) $purchaseOrder->id);

            $released = $this->allocationService->releaseAllReservations($purchaseOrder, $type->label().': '.$reason, $userId);

            $po = $this->lockAmendable((string) $purchaseOrder->id);
            $from = $po->status ?? PurchaseOrderStatus::Active;

            $po->status = $target;
            $po->closed_at = now();
            $po->save();

            $this->recordAmendment($po, $type, [
                'status' => ['from' => $from->value, 'to' => $target->value],
                'reservations_released' => $released,
            ], $reason, $userId);

            return $po;
        });
    }

    /**
     * @param  array<string, int|string|null>  $lineThresholds
     * @return array<string, mixed>
     */
    private function applyLineThresholds(CustomerPurchaseOrder $po, array $lineThresholds): array
    {
        if ($lineThresholds === []) {
            return [];
        }

        $lines = CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $po->id)
            ->whereIn('id', array_keys($lineThresholds))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $changes = [];

        foreach ($lines as $line) {
            $raw = $lineThresholds[(string) $line->id] ?? null;
            $threshold = ($raw === null || $raw === '') ? null : (int) $raw;

            if ($threshold === $line->notify_remaining_qty) {
                continue;
            }

            if ($threshold !== null && $threshold >= (int) $line->ordered_qty) {
                throw ValidationException::withMessages([
                    "lineThresholds.{$line->id}" => "Line {$line->line_no}: the alert level must be below the ordered quantity ({$line->ordered_qty}).",
                ]);
            }

            $changes["line_{$line->line_no}_notify_remaining_qty"] = ['from' => $line->notify_remaining_qty, 'to' => $threshold];

            $line->notify_remaining_qty = $threshold;
            if ($threshold === null || (int) $line->remaining_qty > $threshold) {
                $line->threshold_notified_at = null;
            }
            $line->save();
        }

        return $changes;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    private function lineAttributes(array $line, ?int $lineNo = null): array
    {
        $threshold = $line['notify_remaining_qty'] ?? null;

        return array_filter([
            'line_no' => $lineNo,
            'description' => trim((string) $line['description']),
            'ordered_qty' => (int) $line['ordered_qty'],
            'unit_price_gross' => round((float) ($line['unit_price_gross'] ?? 0), 2),
            'sample_type_id' => filled($line['sample_type_id'] ?? null) ? (string) $line['sample_type_id'] : null,
            'analysis_type_ids' => array_values(array_filter(array_map('strval', (array) ($line['analysis_type_ids'] ?? [])))),
            'analysis_element_ids' => array_values(array_filter(array_map('strval', (array) ($line['analysis_element_ids'] ?? [])))),
            'is_package' => (bool) ($line['is_package'] ?? false),
            'quotation_detail_id' => filled($line['quotation_detail_id'] ?? null) ? (string) $line['quotation_detail_id'] : null,
            'notify_remaining_qty' => ($threshold === null || $threshold === '') ? null : (int) $threshold,
        ], static fn ($value, string $key): bool => $key !== 'line_no' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function assertThresholdsBelowOrdered(array $lines, string $errorPrefix = 'lines'): void
    {
        foreach (array_values($lines) as $index => $line) {
            $threshold = $line['notify_remaining_qty'] ?? null;
            if ($threshold === null || $threshold === '') {
                continue;
            }

            if ((int) $threshold >= (int) ($line['ordered_qty'] ?? 0)) {
                $key = $errorPrefix === 'lines' ? "lines.{$index}.notify_remaining_qty" : "{$errorPrefix}.notify_remaining_qty";

                throw ValidationException::withMessages([$key => 'The alert level must be below the ordered quantity.']);
            }
        }
    }

    private function assertNoDuplicateBlanket(string $customerId, string $poNumber, ?string $ignoreId = null): void
    {
        $exists = CustomerPurchaseOrder::query()
            ->where('customer_id', $customerId)
            ->where('po_type', PurchaseOrderType::Blanket->value)
            ->where('status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->whereRaw('LOWER(po_number) = ?', [Str::lower($poNumber)])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['po_number' => 'This customer already has a blanket PO with this number.']);
        }
    }

    private function lockAmendable(string $purchaseOrderId): CustomerPurchaseOrder
    {
        $po = CustomerPurchaseOrder::query()->whereKey($purchaseOrderId)->lockForUpdate()->firstOrFail();

        if (! $po->isAmendable()) {
            throw ValidationException::withMessages([
                'reason' => 'This purchase order is '.strtolower(($po->status ?? PurchaseOrderStatus::Active)->label()).' and can no longer be amended.',
            ]);
        }

        return $po;
    }

    private function balanceStatus(CustomerPurchaseOrder $po): PurchaseOrderStatus
    {
        $hasRemaining = CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('remaining_qty', '>', 0)
            ->exists();

        return $hasRemaining ? PurchaseOrderStatus::Active : PurchaseOrderStatus::Exhausted;
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function recordAmendment(
        CustomerPurchaseOrder $po,
        PurchaseOrderAmendmentType $type,
        array $changes,
        string $reason,
        ?string $userId,
        ?string $lineId = null,
    ): CustomerPurchaseOrderAmendment {
        return CustomerPurchaseOrderAmendment::query()->create([
            'customer_purchase_order_id' => $po->id,
            'customer_purchase_order_line_id' => $lineId,
            'amendment_type' => $type,
            'changes' => $changes,
            'reason' => trim($reason),
            'created_by' => $userId,
        ]);
    }

    /**
     * @return array{file_path: string, file_name: string, mime: ?string, size: int}
     */
    private function storeFile(UploadedFile $file): array
    {
        return [
            'file_path' => $file->store(self::FILE_DIRECTORY.'/'.Str::uuid(), 'public'),
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
        ];
    }

    private function userId(?string $userId): ?string
    {
        return $userId ?? (Auth::id() !== null ? (string) Auth::id() : null);
    }
}
