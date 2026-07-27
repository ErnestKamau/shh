<?php

namespace Tests\Unit\Services\Billing;

use App\QuotationHeader;
use App\Services\Billing\QuotationPricingResolver;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationPricingResolverTest extends TestCase
{
    public function test_resolve_line_unit_price_prefers_stored_price_over_pricelist(): void
    {
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        $resolver = app(QuotationPricingResolver::class);

        $resolved = $resolver->resolveLineUnitPrice(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
            (string) Str::uuid(),
            2344.0,
            true,
        );

        $this->assertSame(2344.0, $resolved['unit_price']);
        $this->assertSame('manual', $resolved['source']);
    }

    public function test_normalize_manual_detail_rows_creates_one_row_per_selected_test(): void
    {
        $resolver = app(QuotationPricingResolver::class);
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
        ]);
        $elementA = (string) Str::uuid();
        $elementB = (string) Str::uuid();

        $rows = $resolver->normalizeManualDetailRows(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
            [$elementA, $elementB],
            1,
            0.0,
            12.0,
            $elementA.','.$elementB,
            '',
            $elementA.','.$elementB,
            '',
        );

        $this->assertCount(2, $rows);
        $this->assertSame($elementA, $rows[0]['default_analytes']);
        $this->assertSame($elementB, $rows[1]['default_analytes']);
        $this->assertFalse($rows[0]['is_package']);
    }

    public function test_normalize_manual_detail_rows_splits_manual_override_when_no_pricelist(): void
    {
        $resolver = app(QuotationPricingResolver::class);
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
        ]);
        $elementA = (string) Str::uuid();
        $elementB = (string) Str::uuid();

        $rows = $resolver->normalizeManualDetailRows(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
            [$elementA, $elementB],
            1,
            100.0,
            12.0,
            $elementA.','.$elementB,
            '',
            $elementA.','.$elementB,
            '',
        );

        $this->assertCount(2, $rows);
        $this->assertSame(50.0, (float) $rows[0]['unit_price']);
        $this->assertSame(50.0, (float) $rows[1]['unit_price']);
    }

    public function test_suggest_manual_line_pricing_requires_selected_tests(): void
    {
        $resolver = app(QuotationPricingResolver::class);
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        $suggestion = $resolver->suggestManualLinePricing(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
            [],
        );

        $this->assertSame(0.0, $suggestion['unit_price']);
        $this->assertStringContainsString('Select tests', $suggestion['hint']);
        $this->assertFalse($suggestion['is_package']);
    }

    public function test_resolve_line_unit_price_does_not_use_invoicable_fallback(): void
    {
        $header = new QuotationHeader([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) Str::uuid(),
            'currency_id' => (string) Str::uuid(),
        ]);

        $resolver = app(QuotationPricingResolver::class);

        $resolved = $resolver->resolveLineUnitPrice(
            $header,
            (string) Str::uuid(),
            (string) Str::uuid(),
            (string) Str::uuid(),
            null,
            true,
        );

        $this->assertSame(0.0, $resolved['unit_price']);
        $this->assertSame('none', $resolved['source']);
        $this->assertArrayNotHasKey('invoicable_item_id', $resolved);
    }
}
