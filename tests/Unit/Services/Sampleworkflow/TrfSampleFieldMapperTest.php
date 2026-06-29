<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use PHPUnit\Framework\TestCase;

class TrfSampleFieldMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    private TrfSampleFieldMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new TrfSampleFieldMapper();
    }

    public function test_map_to_sample_header_maps_contact_email_and_sampled_by(): void
    {
        $mapped = $this->mapper->mapToSampleHeader([
            'crm_contact_id' => 'contact-uuid-1',
            'customer_email' => 'client@example.test',
            'sampled_by' => 'Thomas Mueller',
            'sampling_time' => '14:30',
            'sampling_location' => 'Jebel Ali Plant',
            'sample_rows' => [
                ['sample_description' => 'Chocolate spread batch A'],
            ],
        ]);

        $this->assertSame('contact-uuid-1', $mapped['crm_contact_id']);
        $this->assertSame('client@example.test', $mapped['schedule_customer_email']);
        $this->assertSame('Thomas Mueller', $mapped['sampling_officer_name']);
        $this->assertSame('14:30', $mapped['radio_active_levels']);
        $this->assertSame('Jebel Ali Plant', $mapped['crm_unit_name']);
        $this->assertSame('Chocolate spread batch A', $mapped['description']);
    }

    public function test_map_to_sample_detail_maps_food_row_fields(): void
    {
        $mapped = $this->mapper->mapToSampleDetail([
            'sample_description' => 'Ready meal',
            'sample_quantity' => '500',
            'sample_quantity_unit' => 'g',
            'production_date' => '2026-06-01',
            'expiration_date' => '2026-12-01',
            'batch_number' => 'LOT-99',
            'sampling_point' => 'Production line 2',
        ], 0);

        $this->assertSame('Ready meal', $mapped['comments']);
        $this->assertSame('500 g', $mapped['quantity']);
        $this->assertSame('2026-06-01', $mapped['mfg_date']);
        $this->assertSame('2026-12-01', $mapped['expiry_date']);
        $this->assertSame('LOT-99', $mapped['batch_lot_no']);
    }

    public function test_format_row_quantity_supports_legacy_qty_key(): void
    {
        $this->assertSame('2 kg', $this->mapper->formatRowQuantity([
            'sample_quantity' => '2',
            'sample_quantity_unit' => 'kg',
        ]));

        $this->assertSame('3', $this->mapper->formatRowQuantity([
            'qty' => '3',
        ]));
    }

    public function test_merge_fill_gaps_only_fills_empty_target_fields(): void
    {
        $merged = $this->mapper->mergeFillGaps([
            'sampling_officer_name' => 'Existing Officer',
            'description' => '',
            'schedule_customer_email' => null,
        ], [
            'sampling_officer_name' => 'TRF Officer',
            'description' => 'From TRF',
            'schedule_customer_email' => 'trf@example.test',
        ]);

        $this->assertSame('Existing Officer', $merged['sampling_officer_name']);
        $this->assertSame('From TRF', $merged['description']);
        $this->assertSame('trf@example.test', $merged['schedule_customer_email']);
    }

    public function test_sample_rows_from_form_data_returns_indexed_rows(): void
    {
        $rows = $this->mapper->sampleRowsFromFormData([
            'sample_rows' => [
                ['sample_no' => '1'],
                ['sample_no' => '2'],
            ],
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('1', $rows[0]['sample_no']);
        $this->assertSame('2', $rows[1]['sample_no']);
    }
}
