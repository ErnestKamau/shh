<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class SampleConditionTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'sample_type_code*',
            'condition_name*',
            'short_name',
            'reporting_time',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['ST-001', 'Room Temperature', 'RT', ''],
            ['ST-001', 'Refrigerated', 'REF', ''],
        ];
    }
}
