<?php

namespace App\Models\Commercial;

use App\Enums\Commercial\PurchaseOrderLedgerEntryType;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Insert-only PO ledger entry. Reversals are new rows with a negative quantity.
 */
class CustomerPurchaseOrderLedgerEntry extends Model
{
    /** @use HasFactory<\Database\Factories\Commercial\CustomerPurchaseOrderLedgerEntryFactory> */
    use HasFactory;

    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'customer_purchase_order_ledger_entries';

    protected $fillable = [
        'customer_purchase_order_id',
        'customer_purchase_order_line_id',
        'entry_type',
        'quantity',
        'enquiry_id',
        'sample_header_id',
        'invoice_id',
        'credit_note_id',
        'amendment_id',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_type' => PurchaseOrderLedgerEntryType::class,
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Purchase order ledger entries are insert-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Purchase order ledger entries are insert-only.');
        });
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrderLine::class, 'customer_purchase_order_line_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class, 'customer_purchase_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
