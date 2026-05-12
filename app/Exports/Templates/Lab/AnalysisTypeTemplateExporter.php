<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalysisTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'name*',
            'sample_type_code*',
            'lab_code*',
            'has_no_result',
            'reporting_time',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['AT-001', 'Water Analysis', 'ST-001', 'LAB-001', '0', '2'],
            ['AT-002', 'Soil Analysis', 'ST-002', 'LAB-001', '0', '3'],
        ];
    }
}
