<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class SampleTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'name*',
            'is_results_attachable',
            'disposal_count',
            'report_template_code',
            'default_product_code',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['ST-001', 'Water Material', '1', '30', '', ''],
            ['ST-002', 'Soil Material', '0', '60', '', ''],
        ];
    }
}
