<?php

namespace Tests\Unit\Standards;

use App\Standards;
use Tests\TestCase;

class QcSchemeNamesAttributeTest extends TestCase
{
    public function test_returns_empty_string_when_qc_scheme_ids_is_empty(): void
    {
        $standard = new Standards(['qc_scheme_ids' => '']);

        $this->assertSame('', $standard->qcschemenames);
    }

    public function test_returns_empty_string_when_qc_scheme_ids_is_null(): void
    {
        $standard = new Standards(['qc_scheme_ids' => null]);

        $this->assertSame('', $standard->qcschemenames);
    }

    public function test_returns_empty_string_when_qc_scheme_ids_contains_only_commas(): void
    {
        $standard = new Standards(['qc_scheme_ids' => ' , , ']);

        $this->assertSame('', $standard->qcschemenames);
    }
}
