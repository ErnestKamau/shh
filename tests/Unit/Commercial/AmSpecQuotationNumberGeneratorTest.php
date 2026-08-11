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

    #[Test]
    public function it_uses_year_scoped_sequence_not_day_scoped(): void
    {
        $prefix = 'AMSQ';
        $july21 = Carbon::parse('2026-07-21');
        $july22 = Carbon::parse('2026-07-22');

        $this->assertSame(1, $this->sequenceFromNumber(
            AmSpecQuotationNumberGenerator::formatLabRef($july21, 1, $prefix),
            $prefix
        ));

        $this->assertSame(2, $this->sequenceFromNumber(
            AmSpecQuotationNumberGenerator::formatLabRef($july22, 2, $prefix),
            $prefix
        ));

        $jan2027 = Carbon::parse('2027-01-01');
        $this->assertSame(1, $this->sequenceFromNumber(
            AmSpecQuotationNumberGenerator::formatLabRef($jan2027, 1, $prefix),
            $prefix
        ));
    }

    private function sequenceFromNumber(string $number, string $prefix): int
    {
        $pattern = '/^'.preg_quote($prefix, '/').'\d{6}-(\d+)$/';

        $this->assertMatchesRegularExpression($pattern, $number);

        return (int) preg_replace($pattern, '$1', $number);
    }
}
