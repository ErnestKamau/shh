<?php

namespace Tests\Unit\Commercial;

use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmSpecQuotationNumberGeneratorTest extends TestCase
{
    #[Test]
    public function it_formats_amsq_lab_ref_numbers(): void
    {
        $date = Carbon::parse('2026-06-02');

        $this->assertSame(
            'AMSQ260602-001',
            AmSpecQuotationNumberGenerator::formatLabRef($date, 1)
        );
        $this->assertSame(
            'AMSQ260602-042',
            AmSpecQuotationNumberGenerator::formatLabRef($date, 42)
        );
        $this->assertSame(
            'AMSQ260602-042',
            AmSpecQuotationNumberGenerator::formatLabRef($date, 42, 'AMSQ')
        );
    }

    #[Test]
    public function it_detects_amsq_format(): void
    {
        $this->assertTrue(AmSpecQuotationNumberGenerator::isAmsqFormat('AMSQ260727-206'));
        $this->assertTrue(AmSpecQuotationNumberGenerator::isAmsqFormat('AMSQ260602-001'));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isAmsqFormat('MaerskOilTrading2026001'));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isAmsqFormat(''));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isAmsqFormat(null));
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
