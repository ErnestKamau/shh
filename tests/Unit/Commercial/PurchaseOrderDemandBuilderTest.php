<?php

namespace Tests\Unit\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\Commercial\CustomerPurchaseOrderLine;
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

    public function test_without_lines_parameters_stay_on_one_analysis_type_item(): void
    {
        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->metals, ['lead', 'zinc'], 2),
        ]);

        $this->assertCount(1, $items);
        $this->assertSame(PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals), $items[0]->key);
        $this->assertSame([], $items[0]->analysisElementIds);
    }

    public function test_each_parameter_with_a_per_test_line_becomes_its_own_item(): void
    {
        $lines = [
            $this->line(1, $this->water, [$this->metals], ['lead']),
            $this->line(2, $this->water, [$this->metals], ['zinc']),
        ];

        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->metals, ['lead', 'zinc'], 3),
        ], $lines);

        $this->assertSame([
            PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals, 'lead'),
            PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals, 'zinc'),
        ], array_map(fn (PurchaseOrderDemandItem $item): string => $item->key, $items));
        $this->assertSame([3, 3], array_map(fn (PurchaseOrderDemandItem $item): int => $item->quantity, $items));
        $this->assertSame(['lead'], $items[0]->analysisElementIds);
        $this->assertTrue($items[0]->isPerTest());
    }

    public function test_parameters_without_a_per_test_line_roll_up_into_the_analysis_type_item(): void
    {
        $lines = [$this->line(1, $this->water, [$this->metals], ['lead'])];

        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->metals, ['lead', 'zinc', 'copper'], 1),
        ], $lines);

        $this->assertSame([
            PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals, 'lead'),
            PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals),
        ], array_map(fn (PurchaseOrderDemandItem $item): string => $item->key, $items));
        $this->assertFalse($items[1]->isPerTest());
    }

    public function test_a_package_line_keeps_one_item_per_analysis_type(): void
    {
        $lines = [
            $this->line(1, $this->water, [$this->metals], ['lead'], isPackage: true),
            $this->line(2, $this->water, [$this->metals], ['zinc']),
        ];

        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->metals, ['lead', 'zinc'], 2),
        ], $lines);

        $this->assertCount(1, $items);
        $this->assertSame(PurchaseOrderDemandBuilder::keyFor($this->water, $this->metals), $items[0]->key);
        $this->assertSame(2, $items[0]->quantity);
    }

    public function test_per_test_items_are_summed_across_rows(): void
    {
        $lines = [$this->line(1, $this->water, [$this->metals], ['lead'])];

        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->metals, ['lead'], 2),
            $this->rowWithElements($this->water, $this->metals, ['lead'], 5),
        ], $lines);

        $this->assertCount(1, $items);
        $this->assertSame(7, $items[0]->quantity);
    }

    public function test_per_test_lines_for_another_sample_type_do_not_split_the_demand(): void
    {
        $lines = [$this->line(1, $this->swab, [$this->micro], ['cronobacter'])];

        $items = $this->builder->fromRows([
            $this->rowWithElements($this->water, $this->micro, ['cronobacter'], 1),
        ], $lines);

        $this->assertCount(1, $items);
        $this->assertSame(PurchaseOrderDemandBuilder::keyFor($this->water, $this->micro), $items[0]->key);
    }

    public function test_units_for_one_sample_split_by_per_test_line_and_roll_up_the_rest(): void
    {
        $lines = [$this->line(1, $this->water, [$this->micro], ['ecoli'])];

        $units = $this->builder->unitsFor($this->water, $this->micro, ['ecoli', 'ecoli', 'listeria'], $lines);

        $this->assertSame([
            ['key' => PurchaseOrderDemandBuilder::keyFor($this->water, $this->micro, 'ecoli'), 'sample_type_id' => $this->water, 'analysis_type_id' => $this->micro, 'element_ids' => ['ecoli']],
            ['key' => PurchaseOrderDemandBuilder::keyFor($this->water, $this->micro), 'sample_type_id' => $this->water, 'analysis_type_id' => $this->micro, 'element_ids' => []],
        ], $units);
    }

    public function test_units_for_without_parameters_or_lines_is_one_analysis_type_unit(): void
    {
        $lines = [$this->line(1, $this->water, [$this->micro], ['ecoli'])];
        $analysisTypeKey = PurchaseOrderDemandBuilder::keyFor($this->water, $this->micro);

        $this->assertSame($analysisTypeKey, $this->builder->unitsFor($this->water, $this->micro, [], $lines)[0]['key']);
        $this->assertSame($analysisTypeKey, $this->builder->unitsFor($this->water, $this->micro, ['ecoli'], null)[0]['key']);
    }

    public function test_an_enquiry_reads_parameters_from_line_attributes(): void
    {
        $enquiry = new SampleSubmissionRequest;
        $enquiry->sample_lines = [
            [
                'sort_order' => 1,
                'sample_type_id' => $this->water,
                'analysis_type_id' => $this->metals,
                'analysis_element_id' => null,
                'number_of_samples' => 1,
                'attributes' => ['analysis_type_ids' => [$this->metals], 'analysis_element_ids' => ['lead', 'zinc']],
            ],
        ];

        $items = $this->builder->fromEnquiry($enquiry, [
            $this->line(1, $this->water, [$this->metals], ['lead']),
            $this->line(2, $this->water, [$this->metals], ['zinc']),
        ]);

        $this->assertSame([['lead'], ['zinc']], array_map(fn (PurchaseOrderDemandItem $item): array => $item->analysisElementIds, $items));
    }

    /**
     * @param  list<string>  $elementIds
     * @return array<string, mixed>
     */
    private function rowWithElements(string $sampleTypeId, string $analysisTypeId, array $elementIds, int $samples): array
    {
        return [
            'sample_type_id' => $sampleTypeId,
            'analysis_type_id' => $analysisTypeId,
            'analysis_element_id' => implode(',', $elementIds),
            'number_of_samples' => $samples,
        ];
    }

    /**
     * @param  list<string>  $analysisTypeIds
     * @param  list<string>  $elementIds
     */
    private function line(int $lineNo, string $sampleTypeId, array $analysisTypeIds, array $elementIds, bool $isPackage = false): CustomerPurchaseOrderLine
    {
        return (new CustomerPurchaseOrderLine)->forceFill([
            'id' => (string) Str::uuid(),
            'line_no' => $lineNo,
            'sample_type_id' => $sampleTypeId,
            'analysis_type_ids' => $analysisTypeIds,
            'analysis_element_ids' => $elementIds,
            'is_package' => $isPackage,
            'ordered_qty' => 10,
            'remaining_qty' => 10,
        ]);
    }
}
