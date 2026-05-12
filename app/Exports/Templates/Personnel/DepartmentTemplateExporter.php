<?php

namespace App\Exports\Templates\Personnel;

use App\Exports\Templates\ExcelTemplateGenerator;

class DepartmentTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'name*',
            'is_active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Quality Assurance', '1'],
            ['Laboratory Operations', '1'],
        ];
    }
}
