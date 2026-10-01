<?php

namespace App\Models\Commercial;

use App\Enums\Commercial\PurchaseOrderAmendmentType;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPurchaseOrderAmendment extends Model
{
    /** @use HasFactory<\Database\Factories\Commercial\CustomerPurchaseOrderAmendmentFactory> */
    use HasFactory;

    use HasUuids;

    protected $table = 'customer_purchase_order_amendments';

    protected $fillable = [
        'customer_purchase_order_id',
        'customer_purchase_order_line_id',
        'amendment_type',
        'changes',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amendment_type' => PurchaseOrderAmendmentType::class,
            'changes' => 'array',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class, 'customer_purchase_order_id');
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrderLine::class, 'customer_purchase_order_line_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
