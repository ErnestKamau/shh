<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class SampleTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'is_results_attachable',
            'disposal_count',
            'report_template_code',
            'default_product_code',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['ST-001', '1', '30', '', ''],
            ['ST-002', '0', '60', '', ''],
        ];
    }
}
