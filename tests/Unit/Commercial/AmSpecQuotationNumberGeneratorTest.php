<?php

namespace Tests\Unit\Commercial;

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
}
