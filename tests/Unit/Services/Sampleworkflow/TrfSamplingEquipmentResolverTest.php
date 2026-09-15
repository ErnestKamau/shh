<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\TrfSamplingEquipmentResolver;
use PHPUnit\Framework\TestCase;

class TrfSamplingEquipmentResolverTest extends TestCase
{
    public function test_decode_ids_handles_json_csv_and_legacy_text(): void
    {
        $resolver = new TrfSamplingEquipmentResolver();

        $this->assertSame([], $resolver->decodeIds(null));
        $this->assertSame([], $resolver->decodeIds(''));
        $this->assertSame(['a', 'b'], $resolver->decodeIds('["a","b"]'));
        $this->assertSame(['a', 'b'], $resolver->decodeIds('a, b'));
        $this->assertSame(['AMS/C/INS/116'], $resolver->decodeIds('AMS/C/INS/116'));
        $this->assertSame(['uuid-1'], $resolver->decodeIds(['uuid-1', '']));
    }

    public function test_encode_ids_filters_blanks_and_returns_json(): void
    {
        $resolver = new TrfSamplingEquipmentResolver();

        $this->assertNull($resolver->encodeIds(['', null]));
        $this->assertSame('["one","two"]', $resolver->encodeIds(['one', '', 'two', 'one']));
    }

    public function test_rows_for_form_always_has_one_slot(): void
    {
        $resolver = new TrfSamplingEquipmentResolver();

        $this->assertSame([''], $resolver->rowsForForm(null));
        $this->assertSame(['a', 'b'], $resolver->rowsForForm('["a","b"]'));
    }

    public function test_format_for_pdf_prints_free_text_as_entered(): void
    {
        $resolver = new TrfSamplingEquipmentResolver();

        $this->assertSame('AMS/C/INS/116', $resolver->formatForPdf('AMS/C/INS/116'));
        $this->assertSame('', $resolver->formatForPdf(null));
        $this->assertSame('A, B', $resolver->formatForPdf('["A","B"]'));
    }
}
