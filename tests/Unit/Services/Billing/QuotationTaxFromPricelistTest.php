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
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Billing\QuotationPricingResolver;
use App\TaxRegime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationTaxFromPricelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_manual_detail_rows_ignores_posted_tax_and_uses_pricelist_vat(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 10,
            'active' => true,
        ]);

        [$header, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackageQuoteContext(
            vat: true,
            packagePrice: 100.0,
        );

        $rows = app(QuotationPricingResolver::class)->normalizeManualDetailRows(
            $header,
            $sampleTypeId,
            $analysisTypeId,
            $elementIds,
            1,
            0.0,
            99.0,
            implode(',', $elementIds),
            '',
            implode(',', $elementIds),
            '',
        );

        $this->assertCount(1, $rows);
        $this->assertTrue((bool) ($rows[0]['is_package'] ?? false));
        $this->assertSame(10.0, (float) $rows[0]['tax']);
        $this->assertSame(100.0, (float) $rows[0]['unit_price']);

        $accredited = array_filter(array_map('trim', explode(',', (string) $rows[0]['accredited_analytes'])));
        $default = array_filter(array_map('trim', explode(',', (string) $rows[0]['default_analytes'])));
        $this->assertSame([], array_intersect($accredited, $default));
    }

    public function test_normalize_manual_detail_rows_sets_zero_tax_when_package_vat_false(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 10,
            'active' => true,
        ]);

        [$header, $sampleTypeId, $analysisTypeId, $elementIds] = $this->seedPackageQuoteContext(
            vat: false,
            packagePrice: 100.0,
        );

        $rows = app(QuotationPricingResolver::class)->normalizeManualDetailRows(
            $header,
            $sampleTypeId,
            $analysisTypeId,
            $elementIds,
            1,
            0.0,
            99.0,
            implode(',', $elementIds),
            '',
            implode(',', $elementIds),
            '',
        );

        $this->assertCount(1, $rows);
        $this->assertSame(0.0, (float) $rows[0]['tax']);
    }

    public function test_apply_tax_to_lines_resolves_package_vat(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 12,
            'active' => true,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-'.Str::upper(Str::random(6)),
            'description' => 'Tax package',
            'active' => true,
            'is_master' => true,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-',
        ]);

        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $packageItemId = (string) Str::uuid();

        PricelistItem::query()->create([
            'id' => $packageItemId,
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'analysis_element_id' => null,
            'selling_price' => 50,
            'vat' => true,
            'active' => true,
            'is_package' => true,
        ]);

        $lines = [[
            'is_package' => true,
            'sample_type_id' => $sampleTypeId,
            'analysis_type_id' => $analysisTypeId,
            'package_pricelist_item_id' => $packageItemId,
            'tax' => 99,
        ]];

        $updated = app(QuotationLineTaxResolver::class)->applyTaxToLines($pricelist, $lines, true);

        $this->assertSame(12.0, (float) $updated[0]['tax']);
    }

    /**
     * @return array{0: QuotationHeader, 1: string, 2: string, 3: list<string>}
     */
    private function seedPackageQuoteContext(bool $vat, float $packagePrice): array
    {
        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-'.Str::upper(Str::random(6)),
            'description' => 'Tax from pricelist',
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

        $sampleType = SampleType::query()->create(['name' => 'Tax Sample '.Str::random(4)]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Tax Analysis '.Str::random(4),
            'sample_type_id' => $sampleType->id,
        ]);

        $elements = collect(range(1, 2))->map(fn (int $level) => AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => null,
            'method' => 'Tax Param '.$level,
            'level' => $level,
            'active' => true,
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

        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customerId,
        ]);

        return [$header, (string) $sampleType->id, (string) $analysisType->id, $elementIds];
    }
}
