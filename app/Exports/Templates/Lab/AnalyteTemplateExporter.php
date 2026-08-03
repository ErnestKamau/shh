<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalyteTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'code*',
            'name*',
            'decimal_places',
            'reporting_symbol',
            'reporting_unit',
            'equipment_code',
            'equipment',
            'reference_method',
            'test_method_sop',
            'method_version',
            'non_detectable',
            'non_accredited',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'ANALYTE-001',
                'Calcium',
                '2',
                'Ca',
                'mg/L',
                'EQ-001',
                'ICP-OES Analyzer',
                'APHA 3120 B',
                'AMS/C/SOP/055',
                'Rev.00',
                '0',
                '0',
            ],
            [
                'ANALYTE-002',
                'Magnesium',
                '2',
                'Mg',
                'mg/L',
                'EQ-002',
                'ICP-OES Analyzer',
                'APHA 3120 B',
                'AMS/C/SOP/056',
                'Rev.00',
                '0',
                '0',
            ],
        ];
    }
}
