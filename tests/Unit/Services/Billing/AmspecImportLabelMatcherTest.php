<?php

namespace Tests\Unit\Services\Billing;

use App\Services\Billing\AmspecImportLabelMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AmspecImportLabelMatcherTest extends TestCase
{
    private AmspecImportLabelMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new AmspecImportLabelMatcher();
    }

    /**
     * @return list<array{0: string, 1: string, 2: bool}>
     */
    public static function matchCases(): array
    {
        return [
            ['Enumeration of E. coli', 'E. coli', true],
            ['Enumeration of Enterobacteriaceae', 'Enterobacteriaceae', true],
            ['Yeasts and Moulds*', 'Yeast and Molds', true],
            ['Total Plate Count*', 'Total Plate Count', true],
            ['Heterotopic plate count', 'Heterotropic Plate Count', true],
            ['Electrical Conductivity @25°C', 'Electrical conductivity', true],
            ['Aerobic plate count', 'Aerobic Plate Count', true],
            ['Potable Water', 'Potable Water', true],
            ['Enumeration of E. coli', 'Coliforms', false],
            ['Condensate Water', 'Water', false],
            ['Condensate Water', 'Ice Water', false],
            ['Drinking Water', 'Water', false],
            ['Waste Water', 'Water', false],
        ];
    }

    #[DataProvider('matchCases')]
    public function test_matches_amspec_pdf_labels_to_lims_names(string $import, string $lims, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->matches($import, $lims));
    }

    /**
     * @return list<array{0: string, 1: string, 2: bool}>
     */
    public static function methodCodeCases(): array
    {
        return [
            ['AMS/C/SOP/054', 'AMS-C-SOP-054', true],
            ['AMS/C/SOP/054', 'AMS_C_SOP_054', true],
            ['AMS/M/SOP/038', 'AMS/M/SOP/038', true],
            ['AMS/C/SOP/054', 'AMS/C/SOP/055', false],
            ['', 'AMS/C/SOP/054', false],
        ];
    }

    #[DataProvider('methodCodeCases')]
    public function test_method_codes_match_slash_and_hyphen_variants(string $import, string $lims, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->methodCodesMatch($import, $lims));
    }
}
