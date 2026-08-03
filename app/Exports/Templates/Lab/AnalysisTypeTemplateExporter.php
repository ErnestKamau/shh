<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalysisTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'sample_type_code*',
            'sample_type_name*',
            'analysis_type_code*',
            'analysis_type_name*',
            'lab_code*',
            'equipment',
            'reference_method',
            'test_method',
            'has_no_result',
            'reporting_time',
        ];
    }

    protected function defineExamples(): array
    {
        // Intentionally fake placeholders — real rows matching these are skipped on import.
        return [
            ['EXAMPLE-FOOD', 'Example Food Matrix', 'EXAMPLE-CHEM', 'Example Chemical', 'LAB-001', 'Balance/Hot Air Oven', 'AOAC Example', 'AMS/C/SOP/EXAMPLE', '0', ''],
            ['EXAMPLE-WATER', 'Example Water Matrix', 'EXAMPLE-PHYS', 'Example Physical', 'LAB-001', 'ICP-OES', 'APHA Example', 'AMS/C/SOP/EXAMPLE-2', '0', '2'],
        ];
    }
}
