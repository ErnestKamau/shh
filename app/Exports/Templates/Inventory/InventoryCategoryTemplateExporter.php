<?php

namespace App\Exports\Templates\Inventory;

use App\Exports\Templates\ExcelTemplateGenerator;

class InventoryCategoryTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'name*',
            'description',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Chemical', 'Laboratory Chemicals and Reagents'],
            ['Glassware', 'Laboratory Glassware and Consumables'],
        ];
    }
}
