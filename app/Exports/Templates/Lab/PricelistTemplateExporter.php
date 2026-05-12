<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class PricelistTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'pricelist_code*',
            'pricelist_name*',
            'is_master',
            'currency_code*',
            'valid_till',
            'item_code*',
            'item_description',
            'analyte_code*',
            'unit_price*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'PL-001',
                'Standard Pricelist',
                '1',
                'USD',
                '2027-12-31',
                'ITEM-001',
                'Water Analysis Item',
                'ANALYTE-001',
                '100.00',
            ],
            [
                'PL-001',
                'Standard Pricelist',
                '1',
                'USD',
                '2027-12-31',
                'ITEM-002',
                'Soil Analysis Item',
                'ANALYTE-002',
                '150.00',
            ],
        ];
    }
}
