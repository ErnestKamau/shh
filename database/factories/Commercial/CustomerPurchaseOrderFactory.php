<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerPurchaseOrder>
 */
class CustomerPurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.fake()->unique()->numerify('######'),
            'po_skipped' => false,
            'po_type' => PurchaseOrderType::Single,
            'status' => PurchaseOrderStatus::Active,
            'valid_from' => now()->subMonth()->toDateString(),
            'valid_to' => now()->addMonths(11)->toDateString(),
            'invoicing_mode' => PurchaseOrderInvoicingMode::PerJob,
            'expiry_notice_days' => 30,
            'recorded_at' => now(),
        ];
    }

    public function blanket(): static
    {
        return $this->state(fn (): array => ['po_type' => PurchaseOrderType::Blanket]);
    }

    public function periodic(string $period = 'monthly'): static
    {
        return $this->state(fn (): array => [
            'invoicing_mode' => PurchaseOrderInvoicingMode::Periodic,
            'invoicing_period' => $period,
        ]);
    }

    public function validBetween(?string $from, ?string $to): static
    {
        return $this->state(fn (): array => [
            'valid_from' => $from,
            'valid_to' => $to,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'valid_from' => now()->subYear()->toDateString(),
            'valid_to' => now()->subDay()->toDateString(),
        ]);
    }

    public function withStatus(PurchaseOrderStatus $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }
}
