<?php

namespace Tests\Unit\Commercial;

use App\Http\Requests\Commercial\ChangeEnquiryPurchaseOrderRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ChangeEnquiryPurchaseOrderRequestTest extends TestCase
{
    public function test_a_single_po_change_with_a_reason_passes(): void
    {
        $validator = $this->validate([
            'mode' => 'single',
            'client_po_number' => 'PO-7781',
            'reason' => 'Customer sent a corrected PO',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_removing_the_po_only_needs_a_reason(): void
    {
        $validator = $this->validate([
            'mode' => 'none',
            'reason' => 'PO withdrawn by the customer',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_a_reason_is_required(): void
    {
        $validator = $this->validate(['mode' => 'single', 'client_po_number' => 'PO-1']);

        $this->assertTrue($validator->errors()->has('reason'));
    }

    public function test_a_blanket_change_needs_the_po(): void
    {
        $validator = $this->validate(['mode' => 'blanket', 'reason' => 'Move to the annual PO']);

        $this->assertTrue($validator->errors()->has('customer_purchase_order_id'));
        $this->assertSame('Select the purchase order.', $validator->errors()->first('customer_purchase_order_id'));
    }

    public function test_a_single_change_needs_the_po_number(): void
    {
        $validator = $this->validate(['mode' => 'single', 'reason' => 'Corrected PO']);

        $this->assertTrue($validator->errors()->has('client_po_number'));
    }

    public function test_an_unknown_mode_is_rejected(): void
    {
        $validator = $this->validate(['mode' => 'other', 'reason' => 'Corrected PO']);

        $this->assertTrue($validator->errors()->has('mode'));
    }

    public function test_the_blanket_po_id_must_be_a_uuid(): void
    {
        $validator = $this->validate(['mode' => 'blanket', 'customer_purchase_order_id' => 'not-a-uuid', 'reason' => 'Corrected PO']);

        $this->assertTrue($validator->errors()->has('customer_purchase_order_id'));
    }

    /**
     * Validates without the database: the `exists` rule is dropped (covered by the feature tests).
     *
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data): \Illuminate\Validation\Validator
    {
        $request = new ChangeEnquiryPurchaseOrderRequest;
        $rules = $request->rules();
        $rules['customer_purchase_order_id'] = array_values(array_filter(
            $rules['customer_purchase_order_id'],
            static fn ($rule): bool => ! is_string($rule) || ! str_starts_with($rule, 'exists:'),
        ));

        return Validator::make($data, $rules, $request->messages());
    }
}
