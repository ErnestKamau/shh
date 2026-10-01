<?php

namespace App\Models\Commercial;

use App\Models\Billing\PricelistItem;
use App\QuotationDetails;
use App\SampleType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One quantity allowance on a customer PO (e.g. "Potable Water package × 800 @ 157.50").
 *
 * ordered_qty / reserved_qty / committed_qty / invoiced_qty / remaining_qty cache the
 * ledger sums. Only PurchaseOrderAllocationService may change them.
 */
class CustomerPurchaseOrderLine extends Model
{
    /** @use HasFactory<\Database\Factories\Commercial\CustomerPurchaseOrderLineFactory> */
    use HasFactory;

    use HasUuids;

    protected $table = 'customer_purchase_order_lines';

    protected $fillable = [
        'customer_purchase_order_id',
        'line_no',
        'description',
        'quotation_detail_id',
        'pricelist_item_id',
        'sample_type_id',
        'analysis_type_ids',
        'is_package',
        'unit_price_gross',
        'ordered_qty',
        'reserved_qty',
        'committed_qty',
        'invoiced_qty',
        'remaining_qty',
        'notify_remaining_qty',
        'threshold_notified_at',
        'exhausted_at',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'analysis_type_ids' => 'array',
            'is_package' => 'boolean',
            'unit_price_gross' => 'decimal:2',
            'ordered_qty' => 'integer',
            'reserved_qty' => 'integer',
            'committed_qty' => 'integer',
            'invoiced_qty' => 'integer',
            'remaining_qty' => 'integer',
            'notify_remaining_qty' => 'integer',
            'threshold_notified_at' => 'datetime',
            'exhausted_at' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class, 'customer_purchase_order_id');
    }

    public function quotationDetail(): BelongsTo
    {
        return $this->belongsTo(QuotationDetails::class, 'quotation_detail_id');
    }

    public function pricelistItem(): BelongsTo
    {
        return $this->belongsTo(PricelistItem::class, 'pricelist_item_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    /**
     * @return HasMany<CustomerPurchaseOrderLedgerEntry, $this>
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CustomerPurchaseOrderLedgerEntry::class, 'customer_purchase_order_line_id');
    }

    /**
     * Normalised analysis type ids this line covers (empty = any analysis for the sample type).
     *
     * @return list<string>
     */
    public function analysisTypeIdList(): array
    {
        $ids = $this->analysis_type_ids ?? [];

        return array_values(array_unique(array_filter(
            array_map(static fn ($id): string => trim((string) $id), is_array($ids) ? $ids : []),
            static fn (string $id): bool => $id !== '',
        )));
    }

    public function isExhausted(): bool
    {
        return (int) $this->remaining_qty <= 0;
    }
}
