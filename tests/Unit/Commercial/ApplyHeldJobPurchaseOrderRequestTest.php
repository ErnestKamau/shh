<?php

namespace Tests\Unit\Commercial;

use App\Http\Requests\Commercial\ApplyHeldJobPurchaseOrderRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

class ApplyHeldJobPurchaseOrderRequestTest extends TestCase
{
    public function test_a_job_and_a_po_pass(): void
    {
        $validator = $this->validate([
            'sample_header_id' => (string) Str::uuid(),
            'customer_purchase_order_id' => (string) Str::uuid(),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_the_po_is_required(): void
    {
        $validator = $this->validate(['sample_header_id' => (string) Str::uuid()]);

        $this->assertTrue($validator->errors()->has('customer_purchase_order_id'));
        $this->assertSame('Select the purchase order that covers these samples.', $validator->errors()->first('customer_purchase_order_id'));
    }

    public function test_the_job_is_required(): void
    {
        $validator = $this->validate(['customer_purchase_order_id' => (string) Str::uuid()]);

        $this->assertSame('Select the held job.', $validator->errors()->first('sample_header_id'));
    }

    public function test_ids_must_be_uuids(): void
    {
        $validator = $this->validate([
            'sample_header_id' => 'job-1',
            'customer_purchase_order_id' => 'po-1',
        ]);

        $this->assertTrue($validator->errors()->has('sample_header_id'));
        $this->assertTrue($validator->errors()->has('customer_purchase_order_id'));
    }

    /**
     * Validates without the database: `exists` rules are dropped (covered by the feature tests).
     *
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data): ValidationValidator
    {
        $request = new ApplyHeldJobPurchaseOrderRequest;
        $rules = array_map(
            static fn (array $fieldRules): array => array_values(array_filter(
                $fieldRules,
                static fn ($rule): bool => ! is_string($rule) || ! str_starts_with($rule, 'exists:'),
            )),
            $request->rules(),
        );

        return Validator::make($data, $rules, $request->messages());
    }
}
