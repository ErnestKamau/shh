<?php

namespace Tests\Unit\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\PurchaseOrderDemandBuilder;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrderDemandBuilderTest extends TestCase
{
    private PurchaseOrderDemandBuilder $builder;

    private string $water;

    private string $swab;

    private string $metals;

    private string $micro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new PurchaseOrderDemandBuilder;
        $this->water = (string) Str::uuid();
        $this->swab = (string) Str::uuid();
        $this->metals = (string) Str::uuid();
        $this->micro = (string) Str::uuid();
    }

    public function test_rows_for_the_same_sample_type_and_analysis_are_summed(): void
    {
        $items = $this->builder->fromRows([
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'number_of_samples' => 4],
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'number_of_samples' => 6],
        ]);

        $this->assertCount(1, $items);
        $this->assertSame(10, $items[0]->quantity);
        $this->assertSame($this->water, $items[0]->sampleTypeId);
        $this->assertSame([$this->metals], $items[0]->analysisTypeIds);
        $this->assertSame(PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals), $items[0]->key);
    }

    public function test_different_analyses_or_sample_types_stay_separate(): void
    {
        $items = $this->builder->fromRows([
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'number_of_samples' => 2],
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->micro, 'number_of_samples' => 3],
            ['sample_type_id' => $this->swab, 'analysis_type_id' => $this->micro, 'number_of_samples' => 5],
        ]);

        $this->assertSame([2, 3, 5], array_map(fn (PurchaseOrderDemandItem $item): int => $item->quantity, $items));
    }

    public function test_quantity_is_accepted_in_place_of_number_of_samples(): void
    {
        $items = $this->builder->fromRows([
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'quantity' => 7],
        ]);

        $this->assertSame(7, $items[0]->quantity);
    }

    public function test_rows_without_a_positive_quantity_are_ignored(): void
    {
        $items = $this->builder->fromRows([
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'number_of_samples' => 0],
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'number_of_samples' => -3],
            ['sample_type_id' => $this->water, 'analysis_type_id' => $this->metals],
        ]);

        $this->assertSame([], $items);
    }

    public function test_a_row_without_analysis_has_an_empty_analysis_set(): void
    {
        $items = $this->builder->fromRows([
            ['sample_type_id' => $this->water, 'analysis_type_id' => '', 'number_of_samples' => 3],
        ]);

        $this->assertSame([], $items[0]->analysisTypeIds);
        $this->assertSame($this->water.'|-', $items[0]->key);
    }

    public function test_key_uses_a_dash_for_missing_parts(): void
    {
        $this->assertSame('-|-', PurchaseOrderDemandBuilder::keyFor(null, null));
        $this->assertSame('-|'.$this->metals, PurchaseOrderDemandBuilder::keyFor(null, $this->metals));
    }

    public function test_an_enquiry_uses_its_sample_lines(): void
    {
        $enquiry = new SampleSubmissionRequest;
        $enquiry->sample_lines = [
            ['sort_order' => 1, 'sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'analysis_element_id' => null, 'number_of_samples' => 3],
            ['sort_order' => 2, 'sample_type_id' => $this->water, 'analysis_type_id' => $this->metals, 'analysis_element_id' => null, 'number_of_samples' => 2],
            ['sort_order' => 3, 'sample_type_id' => $this->swab, 'analysis_type_id' => $this->micro, 'analysis_element_id' => null, 'number_of_samples' => 4],
        ];

        $items = $this->builder->fromEnquiry($enquiry);

        $this->assertCount(2, $items);
        $this->assertSame(5, $items[0]->quantity);
        $this->assertSame(4, $items[1]->quantity);
    }
}
