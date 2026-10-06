<?php

namespace Tests\Unit\Commercial;

use App\QuotationDetails;
use App\Services\Commercial\QuotationPurchaseOrderLineMapper;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationPurchaseOrderLineMapperTest extends TestCase
{
    public function test_parameters_come_from_default_analytes(): void
    {
        $moisture = (string) Str::uuid();
        $ash = (string) Str::uuid();

        $detail = (new QuotationDetails)->forceFill([
            'default_analytes' => " {$moisture}, {$ash},{$moisture}",
            'accredited_analytes' => (string) Str::uuid(),
        ]);

        $this->assertSame([$moisture, $ash], (new QuotationPurchaseOrderLineMapper)->analysisElementIds($detail));
    }

    public function test_parameters_fall_back_to_accredited_and_subcontracted_analytes(): void
    {
        $accredited = (string) Str::uuid();
        $subcontracted = (string) Str::uuid();
        $subAccredited = (string) Str::uuid();

        $detail = (new QuotationDetails)->forceFill([
            'default_analytes' => '',
            'accredited_analytes' => $accredited,
            'subcontracted_analytes' => $subcontracted,
            'sub_acc_analytes' => $subAccredited,
        ]);

        $this->assertSame([$accredited, $subcontracted, $subAccredited], (new QuotationPurchaseOrderLineMapper)->analysisElementIds($detail));
    }

    public function test_values_that_are_not_uuids_are_ignored(): void
    {
        $detail = (new QuotationDetails)->forceFill([
            'default_analytes' => 'Moisture, 12, ',
        ]);

        $this->assertSame([], (new QuotationPurchaseOrderLineMapper)->analysisElementIds($detail));
    }
}
