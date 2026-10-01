<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\PurchaseOrderLedgerEntryType;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Raw ledger rows for reporting / reconciliation tests. Does not touch the line's cached balances.
 *
 * @extends Factory<CustomerPurchaseOrderLedgerEntry>
 */
class CustomerPurchaseOrderLedgerEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_purchase_order_line_id' => CustomerPurchaseOrderLine::factory(),
            'customer_purchase_order_id' => fn (array $attributes): string => (string) CustomerPurchaseOrderLine::query()
                ->findOrFail($attributes['customer_purchase_order_line_id'])
                ->customer_purchase_order_id,
            'entry_type' => PurchaseOrderLedgerEntryType::Reserve,
            'quantity' => 1,
        ];
    }
}
