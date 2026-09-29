<?php

namespace Tests\Unit\SubmissionForm;

use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Tests\TestCase;

class SubmissionFormValueNormalizerTest extends TestCase
{
    public function test_to_request_payload_preserves_additional_details_rows(): void
    {
        $normalizer = new SubmissionFormValueNormalizer;

        $payload = $normalizer->toRequestPayload([
            'sample_description' => ['Chicken', 'Beef'],
            'additional_details' => [
                [
                    ['label' => 'TestField', 'value' => 'Value 1'],
                    ['label' => 'TestField2', 'value' => 'Value2'],
                ],
                [],
            ],
            'sample_type_id' => [
                ['019ff07d-d157-72ce-842d-81b87a41677c'],
                ['019ff07d-d14b-7241-87d0-04fb3004beae'],
            ],
            'parameters' => [
                ['param-a', 'param-b'],
                ['param-c'],
            ],
        ]);

        $this->assertSame(
            [
                ['label' => 'TestField', 'value' => 'Value 1'],
                ['label' => 'TestField2', 'value' => 'Value2'],
            ],
            $payload['sample_rows'][0]['additional_details']
        );
        $this->assertSame([], $payload['sample_rows'][1]['additional_details']);
        $this->assertSame('019ff07d-d157-72ce-842d-81b87a41677c', $payload['sample_type_id'][0]);
        $this->assertSame('param-a,param-b', $payload['parameters'][0]);
    }

    public function test_to_request_payload_flattens_nested_multi_select_ids(): void
    {
        $normalizer = new SubmissionFormValueNormalizer;

        $payload = $normalizer->toRequestPayload([
            'sample_description' => ['A'],
            'sample_type_id' => [[['uuid-nested']]],
            'analysis_type_id' => [[['analysis-1', 'analysis-2']]],
        ]);

        $this->assertSame('uuid-nested', $payload['sample_type_id'][0]);
        $this->assertSame('analysis-1,analysis-2', $payload['analysis_type_id'][0]);
    }
}
