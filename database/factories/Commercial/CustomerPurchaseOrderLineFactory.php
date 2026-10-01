<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\PurchaseOrderLedgerEntryType;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Creates a line whose cached balances match its ledger: each line gets an Order entry
 * for its ordered quantity. Application code must add lines through PurchaseOrderAllocationService.
 *
 * @extends Factory<CustomerPurchaseOrderLine>
 */
class CustomerPurchaseOrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ordered = 100;

        return [
            'customer_purchase_order_id' => CustomerPurchaseOrder::factory(),
            'line_no' => fake()->unique()->numberBetween(1, 60000),
            'description' => fake()->randomElement(['Potable Water package', 'Legionella', 'Swab package']),
            'sample_type_id' => (string) Str::uuid(),
            'analysis_type_ids' => [(string) Str::uuid()],
            'is_package' => true,
            'unit_price_gross' => 157.50,
            'ordered_qty' => $ordered,
            'reserved_qty' => 0,
            'committed_qty' => 0,
            'invoiced_qty' => 0,
            'remaining_qty' => $ordered,
            'notify_remaining_qty' => null,
        ];
    }

    public function ordered(int $quantity): static
    {
        return $this->state(fn (): array => [
            'ordered_qty' => $quantity,
            'remaining_qty' => $quantity,
        ]);
    }

    public function notifyAt(int $remainingQty): static
    {
        return $this->state(fn (): array => ['notify_remaining_qty' => $remainingQty]);
    }

    public function forSampleType(?string $sampleTypeId, array $analysisTypeIds = []): static
    {
        return $this->state(fn (): array => [
            'sample_type_id' => $sampleTypeId,
            'analysis_type_ids' => $analysisTypeIds,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CustomerPurchaseOrderLine $line): void {
            if ((int) $line->ordered_qty <= 0) {
                return;
            }

            CustomerPurchaseOrderLedgerEntry::query()->create([
                'customer_purchase_order_id' => $line->customer_purchase_order_id,
                'customer_purchase_order_line_id' => $line->id,
                'entry_type' => PurchaseOrderLedgerEntryType::Order,
                'quantity' => (int) $line->ordered_qty,
                'reason' => 'Factory opening quantity',
            ]);
        });
    }
}
