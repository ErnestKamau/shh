<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class SampleTypeCategoryTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'category_name*',
            'active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Food', '1'],
            ['Water', '1'],
        ];
    }
}
