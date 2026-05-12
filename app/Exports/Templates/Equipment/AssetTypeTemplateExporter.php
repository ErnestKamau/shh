<?php

namespace App\Exports\Templates\Equipment;

use App\Exports\Templates\ExcelTemplateGenerator;

class AssetTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'description*',
            'is_active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['AT-001', 'HPLC Machine', '1'],
            ['AT-002', 'GC Machine', '1'],
        ];
    }
}
