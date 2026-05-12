<?php

namespace App\Exports\Templates\Equipment;

use App\Exports\Templates\ExcelTemplateGenerator;

class AssetLocationTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'name*',
            'is_active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['LOC-001', 'Lab Building A', '1'],
            ['LOC-002', 'Lab Building B', '1'],
        ];
    }
}
