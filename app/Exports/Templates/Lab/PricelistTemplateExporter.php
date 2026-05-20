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
            'sample_type*',
            'parameter*',
            'price*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'GCLA/P/7',
                'GCLA Price List v6',
                'Yes',
                'TZS',
                '2025-08-22',
                'Non Alcoholic Beverages',
                'Physical examination',
                '21200',
            ],
            [
                'GCLA/P/7',
                'GCLA Price List v6',
                'Yes',
                'TZS',
                '2025-08-22',
                'Non Alcoholic Beverages',
                'pH',
                '21200',
            ],
        ];
    }
}
