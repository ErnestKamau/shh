<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class StandardTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'main_standard*',
            'is_qc_standard',
            'qc_type',
            'analyte_codes*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['STD-001', 'ISO-17043', '1', 'external', 'ANALYTE-001,ANALYTE-002'],
            ['STD-002', 'ISO-6997', '1', 'internal', 'ANALYTE-003'],
        ];
    }
}
