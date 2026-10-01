<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\PurchaseOrderAmendmentType;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderAmendment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerPurchaseOrderAmendment>
 */
class CustomerPurchaseOrderAmendmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_purchase_order_id' => CustomerPurchaseOrder::factory(),
            'amendment_type' => PurchaseOrderAmendmentType::TopUp,
            'changes' => ['ordered_qty' => ['from' => 100, 'to' => 200]],
            'reason' => fake()->sentence(),
        ];
    }
}
