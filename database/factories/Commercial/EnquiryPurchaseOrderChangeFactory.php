<?php

namespace Database\Factories\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\EnquiryPurchaseOrderChange;
use App\Models\SampleSubmissionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnquiryPurchaseOrderChange>
 */
class EnquiryPurchaseOrderChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sample_submission_request_id' => fn (): string => (string) SampleSubmissionRequest::query()->create([
                'status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
                'source_channel' => 'walk_in',
            ])->id,
            'from_customer_purchase_order_id' => null,
            'to_customer_purchase_order_id' => CustomerPurchaseOrder::factory(),
            'from_po_number' => null,
            'to_po_number' => 'PO-'.fake()->numerify('######'),
            'enquiry_status' => SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
            'reason' => fake()->sentence(),
        ];
    }
}
