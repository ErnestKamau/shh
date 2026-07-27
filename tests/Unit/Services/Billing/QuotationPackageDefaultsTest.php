<?php

namespace Tests\Unit\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\TaxRegime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationPackageDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_package_for_analysis_type_returns_covered_elements(): void
    {
        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds, $customerId] = $this->seedCustomerPackage(
            vat: false,
            packagePrice: 250.0,
        );

        $match = app(AcceptanceFormPricingService::class)->findPackageForAnalysisType(
            $customerId,
            $sampleTypeId,
            $analysisTypeId,
            $pricelist,
        );

        $this->assertNotNull($match);
        $this->assertEqualsCanonicalizing($elementIds, $match['covered_element_ids']);
        $this->assertSame(250.0, (float) $match['item']->selling_price);
    }

    public function test_find_package_for_analysis_type_returns_null_when_missing(): void
    {
        $match = app(AcceptanceFormPricingService::class)->findPackageForAnalysisType(
            (string) Str::uuid(),
            (string) Str::uuid(),
            (string) Str::uuid(),
        );

        $this->assertNull($match);
    }

    public function test_resolve_package_defaults_returns_found_payload_with_pricing(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 10,
            'active' => true,
        ]);

        [$pricelist, $sampleTypeId, $analysisTypeId, $elementIds, $customerId] = $this->seedCustomerPackage(
            vat: true,
            packagePrice: 610.0,
        );

        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customerId,
        ]);

        $defaults = app(QuotationPricingResolver::class)->resolvePackageDefaults(
            $header,
            $sampleTypeId,
            $analysisTypeId,
        );

        $this->assertTrue($defaults['found']);
        $this->assertEqualsCanonicalizing($elementIds, $defaults['element_ids']);
        $this->assertSame([], array_intersect($defaults['accredited_ids'], $defaults['default_ids']));
        $this->assertEqualsCanonicalizing(
            $elementIds,
            array_values(array_unique(array_merge($defaults['accredited_ids'], $defaults['default_ids'])))
        );
        $this->assertCount(count($elementIds), $defaults['parameters']);
        $this->assertSame(610.0, (float) $defaults['unit_price']);
        $this->assertSame(10.0, (float) $defaults['tax']);
        $this->assertTrue($defaults['is_package']);
    }

    public function test_resolve_package_defaults_returns_not_found_without_package(): void
    {
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        $defaults = app(QuotationPricingResolver::class)->resolvePackageDefaults(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
        );

        $this->assertFalse($defaults['found']);
        $this->assertSame([], $defaults['element_ids']);
        $this->assertSame(0.0, (float) $defaults['unit_price']);
    }

    /**
     * @return array{0: Pricelist, 1: string, 2: string, 3: list<string>, 4: string}
     */
    private function seedCustomerPackage(bool $vat, float $packagePrice): array
    {
        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-'.Str::upper(Str::random(6)),
            'description' => 'Package defaults test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-',
        ]);

        $customerId = (string) Str::uuid();
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        $sampleType = SampleType::query()->create(['name' => 'Water '.Str::random(4)]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Bottle Water '.Str::random(4),
            'sample_type_id' => $sampleType->id,
        ]);

        $elements = collect(range(1, 3))->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Param '.$level,
            'level' => $level,
            'active' => true,
            'non_accredited' => false,
        ]));
        $elementIds = $elements->pluck('id')->map(fn ($id): string => (string) $id)->all();

        $item = PricelistItem::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleType->id,
            'analysis_id' => $analysisType->id,
            'analysis_element_id' => null,
            'selling_price' => $packagePrice,
            'changed_price' => $packagePrice,
            'cost_price' => 0,
            'vat' => $vat,
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

        return [
            $pricelist,
            (string) $sampleType->id,
            (string) $analysisType->id,
            $elementIds,
            $customerId,
        ];
    }
}
