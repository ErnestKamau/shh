<?php

namespace Tests\Unit\Services\Billing;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\Services\Billing\QuotationLineTaxResolver;
use App\TaxRegime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationLineTaxResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_line_tax_percent_returns_active_regime_when_pricelist_item_has_vat(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 15,
            'active' => true,
        ]);

        $pricelist = Pricelist::query()->create([
            'name' => 'Contract PL',
            'active' => true,
        ]);

        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'selling_price' => 100,
            'vat' => true,
            'active' => true,
        ]);

        $resolver = app(QuotationLineTaxResolver::class);
        $tax = $resolver->resolveLineTaxPercent($pricelist, $sampleTypeId, $analysisTypeId);

        $this->assertSame(15.0, $tax);
    }

    public function test_resolve_line_tax_percent_returns_zero_when_pricelist_item_vat_is_false(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 15,
            'active' => true,
        ]);

        $pricelist = Pricelist::query()->create([
            'name' => 'Contract PL',
            'active' => true,
        ]);

        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'selling_price' => 100,
            'vat' => false,
            'active' => true,
        ]);

        $resolver = app(QuotationLineTaxResolver::class);
        $tax = $resolver->resolveLineTaxPercent($pricelist, $sampleTypeId, $analysisTypeId);

        $this->assertSame(0.0, $tax);
    }

    public function test_apply_tax_to_lines_overwrites_existing_tax_when_requested(): void
    {
        TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 5,
            'active' => true,
        ]);

        $pricelist = Pricelist::query()->create([
            'name' => 'PL',
            'active' => true,
        ]);

        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'selling_price' => 50,
            'vat' => true,
            'active' => true,
        ]);

        $lines = [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'tax' => 99,
            ],
        ];

        $resolver = app(QuotationLineTaxResolver::class);
        $updated = $resolver->applyTaxToLines($pricelist, $lines, true);

        $this->assertSame(5.0, $updated[0]['tax']);
    }
}
