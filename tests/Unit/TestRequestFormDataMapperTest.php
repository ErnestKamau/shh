<?php

namespace Tests\Unit;

use App\Services\TestRequestForm\TestRequestFormDataMapper;
use Tests\TestCase;

class TestRequestFormDataMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    private TestRequestFormDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new TestRequestFormDataMapper;
    }

    public function test_normalizes_portal_phone_field_aliases_to_canonical_keys(): void
    {
        $normalized = $this->mapper->normalizeFormData([
            'customer_name' => 'Acme Labs',
            'customer_tel_fax' => '123456',
            'customer_mobile' => '7890',
        ]);

        $this->assertSame('Acme Labs', $normalized['customer_name']);
        $this->assertSame('123456', $normalized['customer_phone']);
        $this->assertSame('7890', $normalized['mobile_number']);
        $this->assertArrayNotHasKey('customer_tel_fax', $normalized);
        $this->assertArrayNotHasKey('customer_mobile', $normalized);
    }

    public function test_builds_sample_rows_from_indexed_row_fields(): void
    {
        $normalized = $this->mapper->normalizeFormData([
            'customer_name' => 'Acme',
            'sample_description' => ['Water A', 'Water B'],
            'location' => ['Tank 1', 'Tap 2'],
        ]);

        $this->assertCount(2, $normalized['sample_rows']);
        $this->assertSame('Water A', $normalized['sample_rows'][0]['sample_description']);
        $this->assertSame('Tank 1', $normalized['sample_rows'][0]['location']);
        $this->assertSame('Water B', $normalized['sample_rows'][1]['sample_description']);
        $this->assertSame('Tap 2', $normalized['sample_rows'][1]['location']);
    }

    public function test_preserves_existing_sample_rows_array(): void
    {
        $normalized = $this->mapper->normalizeFormData([
            'sample_rows' => [
                ['sample_description' => 'Line 1', 'sample_type_id' => 'type-1'],
            ],
        ]);

        $this->assertSame('Line 1', $normalized['sample_rows'][0]['sample_description']);
        $this->assertSame('type-1', $normalized['sample_rows'][0]['sample_type_id']);
    }

    public function test_to_submission_form_request_data_delegates_with_normalized_phone(): void
    {
        $requestData = $this->mapper->toSubmissionFormRequestData([
            'customer_name' => 'Acme',
            'customer_tel_fax' => '555-1000',
            'mobile_number' => '555-2000',
            'sampling_date' => '2026-06-16',
            'sample_rows' => [
                ['parameters' => 'Microbiology'],
            ],
        ]);

        $this->assertSame('Acme', $requestData['customer_name']);
        $this->assertSame('555-1000', $requestData['customer_tel']);
        $this->assertSame(1, $requestData['number_of_samples']);
    }
}
