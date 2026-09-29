<?php

namespace Tests\Unit\Support;

use App\Support\ScientificNotation;
use PHPUnit\Framework\TestCase;

class ScientificNotationTest extends TestCase
{
    public function test_formats_a_plate_count_with_a_comma_and_superscript(): void
    {
        $this->assertSame('4,9 × 10⁵', ScientificNotation::format(490000));
        $this->assertSame('4,9 × 10⁵', ScientificNotation::format('490000'));
        $this->assertSame('1,0 × 10⁵', ScientificNotation::format(100000));
    }

    public function test_bumps_the_exponent_when_the_coefficient_rounds_to_ten(): void
    {
        $this->assertSame('1,0 × 10⁶', ScientificNotation::format(995000));
    }

    public function test_leaves_small_counts_and_text_unchanged(): void
    {
        $this->assertNull(ScientificNotation::format(9));
        $this->assertNull(ScientificNotation::format(0));
        $this->assertNull(ScientificNotation::format('ND'));
        $this->assertNull(ScientificNotation::format('4,9 x 10^5'));
    }

    public function test_keeps_a_negative_sign(): void
    {
        $this->assertSame('-4,9 × 10⁵', ScientificNotation::format(-490000));
    }
}
