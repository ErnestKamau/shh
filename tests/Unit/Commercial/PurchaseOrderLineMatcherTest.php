<?php

namespace Tests\Unit\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Services\Commercial\PurchaseOrderLineMatcher;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrderLineMatcherTest extends TestCase
{
    private PurchaseOrderLineMatcher $matcher;

    private string $water;

    private string $swab;

    private string $packageA;

    private string $packageB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new PurchaseOrderLineMatcher;
        $this->water = (string) Str::uuid();
        $this->swab = (string) Str::uuid();
        $this->packageA = (string) Str::uuid();
        $this->packageB = (string) Str::uuid();
    }

    public function test_exact_analysis_set_wins_over_a_superset(): void
    {
        $superset = $this->line(1, $this->water, [$this->packageA, $this->packageB]);
        $exact = $this->line(2, $this->water, [$this->packageA]);

        $match = $this->matcher->match([$superset, $exact], $this->demand($this->water, [$this->packageA]));

        $this->assertSame($exact, $match);
    }

    public function test_a_superset_line_covers_a_subset_demand(): void
    {
        $superset = $this->line(1, $this->water, [$this->packageA, $this->packageB]);

        $match = $this->matcher->match([$superset], $this->demand($this->water, [$this->packageB]));

        $this->assertSame($superset, $match);
    }

    public function test_a_line_missing_a_demanded_analysis_does_not_match(): void
    {
        $line = $this->line(1, $this->water, [$this->packageA]);

        $this->assertNull($this->matcher->match([$line], $this->demand($this->water, [$this->packageA, $this->packageB])));
    }

    public function test_a_different_sample_type_does_not_match(): void
    {
        $line = $this->line(1, $this->swab, [$this->packageA]);

        $this->assertNull($this->matcher->match([$line], $this->demand($this->water, [$this->packageA])));
    }

    public function test_same_sample_type_is_preferred_over_a_line_without_sample_type(): void
    {
        $anySampleType = $this->line(1, null, [$this->packageA]);
        $water = $this->line(2, $this->water, [$this->packageA]);

        $match = $this->matcher->match([$anySampleType, $water], $this->demand($this->water, [$this->packageA]));

        $this->assertSame($water, $match);
    }

    public function test_a_line_without_analysis_set_is_a_last_resort_wildcard(): void
    {
        $wildcard = $this->line(1, $this->water, []);
        $specific = $this->line(2, $this->water, [$this->packageA, $this->packageB]);

        $this->assertSame($specific, $this->matcher->match([$wildcard, $specific], $this->demand($this->water, [$this->packageA])));
        $this->assertSame($wildcard, $this->matcher->match([$wildcard], $this->demand($this->water, [$this->packageA])));
    }

    public function test_a_demand_without_analysis_only_matches_a_wildcard_line(): void
    {
        $specific = $this->line(1, $this->water, [$this->packageA]);
        $wildcard = $this->line(2, $this->water, []);

        $this->assertNull($this->matcher->match([$specific], $this->demand($this->water, [])));
        $this->assertSame($wildcard, $this->matcher->match([$specific, $wildcard], $this->demand($this->water, [])));
    }

    public function test_a_line_with_remaining_quantity_is_preferred_over_an_exhausted_one(): void
    {
        $exhausted = $this->line(1, $this->water, [$this->packageA], remaining: 0);
        $available = $this->line(2, $this->water, [$this->packageA], remaining: 5);

        $match = $this->matcher->match([$exhausted, $available], $this->demand($this->water, [$this->packageA]));

        $this->assertSame($available, $match);
    }

    public function test_an_exhausted_line_is_still_returned_when_it_is_the_only_match(): void
    {
        $exhausted = $this->line(1, $this->water, [$this->packageA], remaining: 0);

        $this->assertSame($exhausted, $this->matcher->match([$exhausted], $this->demand($this->water, [$this->packageA])));
    }

    public function test_ties_are_broken_by_lowest_line_number(): void
    {
        $second = $this->line(2, $this->water, [$this->packageA]);
        $first = $this->line(1, $this->water, [$this->packageA]);

        $this->assertSame($first, $this->matcher->match([$second, $first], $this->demand($this->water, [$this->packageA])));
    }

    public function test_analysis_order_and_duplicates_do_not_matter(): void
    {
        $line = $this->line(1, $this->water, [$this->packageB, $this->packageA]);

        $match = $this->matcher->match([$line], $this->demand($this->water, [$this->packageA, $this->packageB, $this->packageA]));

        $this->assertSame($line, $match);
    }

    public function test_no_lines_means_no_match(): void
    {
        $this->assertNull($this->matcher->match([], $this->demand($this->water, [$this->packageA])));
    }

    public function test_a_per_test_demand_matches_the_line_for_that_parameter(): void
    {
        $moisture = (string) Str::uuid();
        $ash = (string) Str::uuid();
        $moistureLine = $this->line(1, $this->water, [$this->packageA], elementIds: [$moisture]);
        $ashLine = $this->line(2, $this->water, [$this->packageA], elementIds: [$ash]);

        $this->assertSame($moistureLine, $this->matcher->match([$moistureLine, $ashLine], $this->demand($this->water, [$this->packageA], [$moisture])));
        $this->assertSame($ashLine, $this->matcher->match([$moistureLine, $ashLine], $this->demand($this->water, [$this->packageA], [$ash])));
    }

    public function test_a_per_test_demand_does_not_match_a_line_for_another_parameter(): void
    {
        $line = $this->line(1, $this->water, [$this->packageA], elementIds: [(string) Str::uuid()]);

        $this->assertNull($this->matcher->match([$line], $this->demand($this->water, [$this->packageA], [(string) Str::uuid()])));
    }

    public function test_a_per_test_demand_does_not_match_a_line_priced_per_analysis_type(): void
    {
        $analysisTypeLine = $this->line(1, $this->water, [$this->packageA]);

        $this->assertNull($this->matcher->match([$analysisTypeLine], $this->demand($this->water, [$this->packageA], [(string) Str::uuid()])));
    }

    public function test_an_analysis_type_demand_does_not_match_a_per_test_line(): void
    {
        $perTestLine = $this->line(1, $this->water, [$this->packageA], elementIds: [(string) Str::uuid()]);
        $analysisTypeLine = $this->line(2, $this->water, [$this->packageA]);

        $this->assertNull($this->matcher->match([$perTestLine], $this->demand($this->water, [$this->packageA])));
        $this->assertSame($analysisTypeLine, $this->matcher->match([$perTestLine, $analysisTypeLine], $this->demand($this->water, [$this->packageA])));
    }

    public function test_a_package_line_with_parameters_is_not_treated_as_per_test(): void
    {
        $package = $this->line(1, $this->water, [$this->packageA], elementIds: [(string) Str::uuid()], isPackage: true);

        $this->assertFalse($package->isPerTest());
        $this->assertSame($package, $this->matcher->match([$package], $this->demand($this->water, [$this->packageA])));
    }

    public function test_a_per_test_line_for_another_sample_type_does_not_match(): void
    {
        $cronobacter = (string) Str::uuid();
        $swabLine = $this->line(1, $this->swab, [$this->packageA], elementIds: [$cronobacter]);

        $this->assertNull($this->matcher->match([$swabLine], $this->demand($this->water, [$this->packageA], [$cronobacter])));
    }

    /**
     * @param  list<string>  $analysisTypeIds
     * @param  list<string>  $elementIds
     */
    private function line(int $lineNo, ?string $sampleTypeId, array $analysisTypeIds, int $remaining = 10, array $elementIds = [], bool $isPackage = false): CustomerPurchaseOrderLine
    {
        return (new CustomerPurchaseOrderLine)->forceFill([
            'id' => (string) Str::uuid(),
            'line_no' => $lineNo,
            'sample_type_id' => $sampleTypeId,
            'analysis_type_ids' => $analysisTypeIds,
            'analysis_element_ids' => $elementIds !== [] ? $elementIds : null,
            'is_package' => $isPackage,
            'ordered_qty' => 10,
            'remaining_qty' => $remaining,
        ]);
    }

    /**
     * @param  list<string>  $analysisTypeIds
     * @param  list<string>  $elementIds
     */
    private function demand(string $sampleTypeId, array $analysisTypeIds, array $elementIds = []): PurchaseOrderDemandItem
    {
        return new PurchaseOrderDemandItem('demand', $sampleTypeId, $analysisTypeIds, 1, $elementIds);
    }
}
