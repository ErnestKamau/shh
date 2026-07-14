<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PricelistPackagePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_package_match_collapses_to_package_line(): void
    {
        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackagePricelist(
            coveredCount: 3,
            packagePrice: 250.0,
            parameterPrice: 40.0,
        );

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $lines = $this->buildElementLines($sampleTypeId, $analysisTypeId, $elementIds, 40.0);
        $result = app(AcceptanceFormPricingService::class)
            ->applyPackagePricingToLines($lines, $customerId, $pricelist);

        $this->assertCount(1, $result);
        $this->assertTrue((bool) ($result[0]['is_package'] ?? false));
        $this->assertSame(250.0, (float) $result[0]['unit_price']);
        $this->assertEqualsCanonicalizing($elementIds, $result[0]['package_element_ids']);
    }

    public function test_superset_keeps_extras_as_per_parameter_lines(): void
    {
        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackagePricelist(
            coveredCount: 2,
            packagePrice: 180.0,
            parameterPrice: 45.0,
            extraParameterCount: 1,
        );

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $allElementIds = array_values(array_unique(array_merge(
            $elementIds,
            $this->extraElementIdsFromPricelist($pricelist, $analysisTypeId)
        )));

        $lines = $this->buildElementLines($sampleTypeId, $analysisTypeId, $allElementIds, 45.0);
        $result = app(AcceptanceFormPricingService::class)
            ->applyPackagePricingToLines($lines, $customerId, $pricelist);

        $packageLines = array_values(array_filter($result, fn (array $line): bool => ! empty($line['is_package'])));
        $extraLines = array_values(array_filter($result, fn (array $line): bool => empty($line['is_package'])));

        $this->assertCount(1, $packageLines);
        $this->assertSame(180.0, (float) $packageLines[0]['unit_price']);
        $this->assertCount(1, $extraLines);
        $this->assertSame(45.0, (float) $extraLines[0]['unit_price']);
    }

    public function test_subset_does_not_apply_package(): void
    {
        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackagePricelist(
            coveredCount: 3,
            packagePrice: 250.0,
            parameterPrice: 40.0,
        );

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $subset = array_slice($elementIds, 0, 2);
        $lines = $this->buildElementLines($sampleTypeId, $analysisTypeId, $subset, 40.0);
        $result = app(AcceptanceFormPricingService::class)
            ->applyPackagePricingToLines($lines, $customerId, $pricelist);

        $this->assertCount(2, $result);
        $this->assertTrue(collect($result)->every(fn (array $line): bool => empty($line['is_package'])));
    }

    public function test_resolve_package_prefers_assigned_pricelist_order(): void
    {
        $sampleType = SampleType::query()->create(['name' => 'Package Sample Prefer']);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Prefer Analysis',
            'sample_type_id' => $sampleType->id,
        ]);
        $elements = collect([1, 2])->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Marker '.$level,
            'level' => $level,
            'active' => true,
        ]));
        $elementIds = $elements->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $older = $this->createActivePricelist(false);
        $newer = $this->createActivePricelist(false);

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $older->id,
            'customer_id' => $customerId,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $newer->id,
            'customer_id' => $customerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->createPackageItem($older, (string) $sampleType->id, (string) $analysisType->id, $elementIds, 100.0);
        $this->createPackageItem($newer, (string) $sampleType->id, (string) $analysisType->id, $elementIds, 200.0);

        $match = app(AcceptanceFormPricingService::class)->resolvePackageForGroup(
            $customerId,
            (string) $sampleType->id,
            (string) $analysisType->id,
            $elementIds,
        );

        $this->assertNotNull($match);
        $this->assertSame((string) $newer->id, (string) $match['pricelist']->id);
        $this->assertSame(200.0, (float) $match['item']->selling_price);
    }

    public function test_full_analysis_type_package_applies_when_all_elements_requested(): void
    {
        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackagePricelist(
            coveredCount: 4,
            packagePrice: 400.0,
            parameterPrice: 50.0,
        );

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $match = app(AcceptanceFormPricingService::class)->resolvePackageForGroup(
            $customerId,
            $sampleTypeId,
            $analysisTypeId,
            $elementIds,
            $pricelist,
        );

        $this->assertNotNull($match);
        $this->assertEqualsCanonicalizing($elementIds, $match['covered_element_ids']);
        $this->assertSame(400.0, (float) $match['item']->selling_price);
    }

    public function test_package_line_persists_and_reloads_as_package(): void
    {
        $header = QuotationHeader::query()->create([
            'id' => (string) Str::uuid(),
            'quote_number' => 'TestCust'.now()->format('Y').'001',
            'quote_date' => now()->toDateString(),
            'status' => 'Quote Complete',
            'quotation_type' => 'Analysis',
            'from_enquiry' => true,
            'show_unit_price_column' => true,
        ]);

        $sampleType = SampleType::query()->create(['name' => 'Persist Sample']);
        $analysisType = AnalysisType::query()->create([
            'name' => 'AI Monitoring',
            'sample_type_id' => $sampleType->id,
        ]);
        $elements = collect([1, 2])->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Persist '.$level,
            'level' => $level,
            'active' => true,
        ]));
        $elementIds = $elements->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $lines = [[
            'sample_type_id' => (string) $sampleType->id,
            'analysis_type_id' => (string) $analysisType->id,
            'analysis_type_name' => 'AI Monitoring',
            'analysis_element_id' => null,
            'parameter_label' => 'AI Monitoring package (2 parameters)',
            'physical_sample_count' => 2,
            'quantity' => 2,
            'unit_price' => 275.0,
            'tax' => 5.0,
            'subcontracted' => false,
            'is_package' => true,
            'package_element_ids' => $elementIds,
            'package_element_labels' => ['Param A', 'Param B'],
        ]];

        $service = app(QuotationFromEnquiryService::class);
        $service->persistInlineLines($header, $lines);

        $detail = QuotationDetails::query()
            ->where('quotation_header_id', $header->id)
            ->first();

        $this->assertNotNull($detail);
        $this->assertTrue((bool) $detail->is_package);
        $this->assertSame(275.0, (float) $detail->unit_price);
        $this->assertSame((string) $analysisType->id, (string) $detail->part_no);
        $this->assertSame(implode(',', $elementIds), (string) $detail->default_analytes);

        $reloaded = $service->buildInlineLinesFromQuotationHeader($header->fresh(['details']));
        $this->assertCount(1, $reloaded);
        $this->assertTrue((bool) ($reloaded[0]['is_package'] ?? false));
        $this->assertSame(275.0, (float) $reloaded[0]['unit_price']);
        $this->assertEqualsCanonicalizing($elementIds, $reloaded[0]['package_element_ids']);
        $this->assertArrayHasKey('package_element_metrics', $reloaded[0]);
        $this->assertCount(2, $reloaded[0]['package_element_metrics']);
        $this->assertEqualsCanonicalizing(
            $elementIds,
            collect($reloaded[0]['package_element_metrics'])->pluck('id')->all(),
        );
        $this->assertSame('', (string) ($reloaded[0]['loq'] ?? ''));
        $this->assertSame('', (string) ($reloaded[0]['mu_percent'] ?? ''));
    }

    public function test_enrich_lines_attaches_package_element_metrics(): void
    {
        $sampleType = SampleType::query()->create(['name' => 'Metrics Sample']);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Metrics Analysis',
            'sample_type_id' => $sampleType->id,
        ]);
        $elements = collect([1, 2])->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Metric '.$level,
            'level' => $level,
            'active' => true,
            'lod' => $level === 1 ? 0.1 : 0.2,
            'hod' => $level === 1 ? 0.1 : 0.2,
            'measurement_uncertainty' => $level === 1 ? 5.0 : 7.5,
        ]));
        $elementIds = $elements->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $lines = [[
            'sample_type_id' => (string) $sampleType->id,
            'analysis_type_id' => (string) $analysisType->id,
            'analysis_element_id' => null,
            'parameter_label' => 'Metrics Analysis package (2 parameters)',
            'quantity' => 1,
            'unit_price' => 100.0,
            'is_package' => true,
            'package_element_ids' => $elementIds,
            'package_element_labels' => ['Param A', 'Param B'],
        ]];

        $enriched = app(\App\Services\Lab\UncertaintyBudgetResolver::class)
            ->enrichLinesWithLabMetrics($lines);

        $this->assertCount(1, $enriched);
        $this->assertSame('', (string) ($enriched[0]['loq'] ?? ''));
        $this->assertSame('', (string) ($enriched[0]['mu_percent'] ?? ''));
        $this->assertArrayHasKey('package_element_metrics', $enriched[0]);
        $this->assertCount(2, $enriched[0]['package_element_metrics']);

        foreach ($enriched[0]['package_element_metrics'] as $metric) {
            $this->assertArrayHasKey('id', $metric);
            $this->assertArrayHasKey('label', $metric);
            $this->assertArrayHasKey('loq', $metric);
            $this->assertArrayHasKey('mu_percent', $metric);
            $this->assertArrayHasKey('test_method', $metric);
            $this->assertNotSame('', (string) $metric['loq']);
            $this->assertNotSame('', (string) $metric['mu_percent']);
        }
    }

    public function test_resolve_line_price_ignores_package_rows_for_parameter_lookup(): void
    {
        $sampleType = SampleType::query()->create(['name' => 'Ignore Package Sample']);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Ignore Package Analysis',
            'sample_type_id' => $sampleType->id,
        ]);
        $element = AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Ignore Marker',
            'level' => 1,
            'active' => true,
        ]);

        $pricelist = $this->createActivePricelist(true);

        PricelistItem::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleType->id,
            'analysis_id' => $analysisType->id,
            'analysis_element_id' => null,
            'selling_price' => 999.0,
            'changed_price' => 999.0,
            'active' => true,
            'is_package' => true,
        ]);

        PricelistItem::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleType->id,
            'analysis_id' => $analysisType->id,
            'analysis_element_id' => $element->id,
            'selling_price' => 55.0,
            'changed_price' => 55.0,
            'active' => true,
            'is_package' => false,
        ]);

        $price = app(AcceptanceFormPricingService::class)
            ->resolveLinePrice($pricelist, (string) $sampleType->id, (string) $analysisType->id, (string) $element->id);

        $this->assertSame(55.0, $price);
    }

    /**
     * @return array{0: Pricelist, 1: string, 2: string, 3: list<string>}
     */
    private function seedPackagePricelist(
        int $coveredCount,
        float $packagePrice,
        float $parameterPrice,
        int $extraParameterCount = 0,
    ): array {
        $pricelist = $this->createActivePricelist(true);
        $sampleType = SampleType::query()->create(['name' => 'Package Sample '.Str::random(5)]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Package Analysis '.Str::random(5),
            'sample_type_id' => $sampleType->id,
        ]);

        $elements = collect(range(1, $coveredCount))->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Covered '.$level,
            'level' => $level,
            'active' => true,
        ]));
        $elementIds = $elements->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $this->createPackageItem(
            $pricelist,
            (string) $sampleType->id,
            (string) $analysisType->id,
            $elementIds,
            $packagePrice
        );

        foreach ($elementIds as $elementId) {
            PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => $elementId,
                'selling_price' => $parameterPrice,
                'changed_price' => $parameterPrice,
                'active' => true,
                'is_package' => false,
            ]);
        }

        for ($i = 0; $i < $extraParameterCount; $i++) {
            $extra = AnalysisElements::query()->create([
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => null,
                'method' => 'Extra '.($i + 1),
                'level' => $coveredCount + $i + 1,
                'active' => true,
            ]);

            PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => $extra->id,
                'selling_price' => $parameterPrice,
                'changed_price' => $parameterPrice,
                'active' => true,
                'is_package' => false,
            ]);
        }

        return [$pricelist, (string) $sampleType->id, (string) $analysisType->id, $elementIds];
    }

    private function createActivePricelist(bool $isMaster): Pricelist
    {
        return Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-'.Str::upper(Str::random(6)),
            'description' => 'Package test pricelist',
            'active' => true,
            'is_master' => $isMaster,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-',
        ]);
    }

    /**
     * @param  list<string>  $elementIds
     */
    private function createPackageItem(
        Pricelist $pricelist,
        string $sampleTypeId,
        string $analysisTypeId,
        array $elementIds,
        float $packagePrice,
    ): PricelistItem {
        $item = PricelistItem::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'analysis_element_id' => null,
            'selling_price' => $packagePrice,
            'changed_price' => $packagePrice,
            'cost_price' => 0,
            'vat' => false,
            'active' => true,
            'is_package' => true,
            'internal_use' => false,
            'external_view' => true,
        ]);

        foreach ($elementIds as $elementId) {
            PricelistItemElement::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_item_id' => $item->id,
                'analysis_element_id' => $elementId,
            ]);
        }

        return $item->fresh('packageElements') ?? $item;
    }

    /**
     * @param  list<string>  $elementIds
     * @return list<array<string, mixed>>
     */
    private function buildElementLines(
        string $sampleTypeId,
        string $analysisTypeId,
        array $elementIds,
        float $unitPrice,
    ): array {
        $lines = [];
        foreach ($elementIds as $index => $elementId) {
            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => 'AI Monitoring',
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Param '.($index + 1),
                'physical_sample_count' => 1,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'tax' => 0,
                'subcontracted' => false,
            ];
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function extraElementIdsFromPricelist(Pricelist $pricelist, string $analysisTypeId): array
    {
        $packageElementIds = PricelistItemElement::query()
            ->whereIn('pricelist_item_id', PricelistItem::query()
                ->where('pricelist_id', $pricelist->id)
                ->where('is_package', true)
                ->pluck('id'))
            ->pluck('analysis_element_id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        return PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('analysis_id', $analysisTypeId)
            ->where('is_package', false)
            ->whereNotNull('analysis_element_id')
            ->pluck('analysis_element_id')
            ->map(fn ($id): string => (string) $id)
            ->reject(fn (string $id): bool => in_array($id, $packageElementIds, true))
            ->values()
            ->all();
    }
}
