<?php

namespace Tests\Unit\Commercial;

use App\Http\Requests\Commercial\CancelHeldJobRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

class CancelHeldJobRequestTest extends TestCase
{
    public function test_a_job_and_a_reason_pass(): void
    {
        $validator = $this->validate([
            'sample_header_id' => (string) Str::uuid(),
            'reason' => 'Customer will not issue a PO',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_a_reason_is_required(): void
    {
        $validator = $this->validate(['sample_header_id' => (string) Str::uuid(), 'reason' => '']);

        $this->assertTrue($validator->errors()->has('reason'));
        $this->assertStringStartsWith('Give a reason for cancelling the held job', $validator->errors()->first('reason'));
    }

    public function test_a_reason_that_is_too_short_is_rejected(): void
    {
        $validator = $this->validate(['sample_header_id' => (string) Str::uuid(), 'reason' => 'no']);

        $this->assertSame('The reason must be at least 3 characters.', $validator->errors()->first('reason'));
    }

    public function test_a_reason_that_is_too_long_is_rejected(): void
    {
        $validator = $this->validate(['sample_header_id' => (string) Str::uuid(), 'reason' => str_repeat('a', 1001)]);

        $this->assertTrue($validator->errors()->has('reason'));
    }

    public function test_the_job_must_be_a_uuid(): void
    {
        $validator = $this->validate(['sample_header_id' => 'job-1', 'reason' => 'No PO coming']);

        $this->assertTrue($validator->errors()->has('sample_header_id'));
    }

    /**
     * Validates without the database: `exists` rules are dropped (covered by the feature tests).
     *
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data): ValidationValidator
    {
        $request = new CancelHeldJobRequest;
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
