<?php

namespace Tests\Unit\Commercial;

use App\QuotationHeader;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmSpecQuotationNumberGeneratorTest extends TestCase
{
    #[Test]
    public function it_formats_laboratory_reference_numbers(): void
    {
        $date = Carbon::parse('2026-06-02');

        $this->assertSame('AMSQ260602-001', AmSpecQuotationNumberGenerator::formatSequence($date, 1));
        $this->assertSame('AMSQ260602-042', AmSpecQuotationNumberGenerator::formatSequence($date, 42));
    }

    #[Test]
    public function it_detects_legacy_quote_numbers(): void
    {
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber(null));
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber(''));
        $this->assertTrue(AmSpecQuotationNumberGenerator::isLegacyNumber('QUOTE-019ed08f-9625-700d-87af-e098c2c300c9'));
        $this->assertFalse(AmSpecQuotationNumberGenerator::isLegacyNumber('AMSQ260620-001'));
    }
}
