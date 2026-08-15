<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class StandardTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'standard_code*',
            'standard_name*',
            'main_standard',
            'is_qc_standard',
            'qc_type',
            'analyte_code*',
            'standard_value_type',
            'standard_low',
            'standard_high',
            'standard_value',
            'standard_matrix_operator',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['STD-001', 'Drinking Water Spec', '1', '0', '', 'PH', 'is_range', '6.5', '8.5', '', ''],
            ['STD-001', '', '', '', '', 'TDS', 'is_standard_value', '', '', '500', 'max'],
            ['STD-002', 'Wastewater Spec', '1', '0', '', 'BOD', 'is_standard_value', '', '', '30', 'max'],
        ];
    }
}
