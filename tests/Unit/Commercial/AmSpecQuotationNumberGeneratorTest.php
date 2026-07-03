<?php

namespace Tests\Unit\Commercial;

use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmSpecQuotationNumberGeneratorTest extends TestCase
{
    #[Test]
    public function it_formats_customer_scoped_reference_numbers(): void
    {
        $date = Carbon::parse('2026-06-02');

        $this->assertSame(
            'MaerskOilTrading2026001',
            AmSpecQuotationNumberGenerator::formatSequence('MaerskOilTrading', $date, 1)
        );
        $this->assertSame(
            'MaerskOilTrading2026042',
            AmSpecQuotationNumberGenerator::formatSequence('MaerskOilTrading', $date, 42)
        );
    }

    #[Test]
    public function it_sanitizes_customer_names_for_quote_numbers(): void
    {
        $this->assertSame('MaerskOilTrading', AmSpecQuotationNumberGenerator::sanitizeCustomerName('Maersk Oil Trading'));
        $this->assertSame('Customer', AmSpecQuotationNumberGenerator::sanitizeCustomerName('   '));
    }

    #[Test]
    public function it_detects_legacy_quote_numbers(): void
    {
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber(null));
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber(''));
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber('QUOTE-019ed08f-9625-700d-87af-e098c2c300c9'));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isLegacyNumber('AMSQ260620-001'));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isLegacyNumber('MaerskOilTrading2026001'));
    }
}
