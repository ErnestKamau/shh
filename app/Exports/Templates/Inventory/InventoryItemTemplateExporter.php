<?php

namespace App\Exports\Templates\Inventory;

use App\Exports\Templates\ExcelTemplateGenerator;

class InventoryItemTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'name*',
            'description',
            'category*',
            'volume_unit*',
            'qty*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Calcium Sulphate', 'Calcium Sulphate Reagent', 'Chemical', '500gms', '1'],
            ['Beaker 250ml', 'Borosilicate glass beaker 250ml', 'Glassware', 'Piece', '10'],
        ];
    }
}
