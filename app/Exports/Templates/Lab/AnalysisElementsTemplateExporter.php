<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalysisElementsTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'analysis_type_code*',
            'analyte_code*',
            'lab_section_code*',
            'equipment_code',
            'equipment',
            'lod',
            'hod',
            'level',
            'method_sequence_name',
            'procedure_worksheet_name',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['AT-001', 'ANALYTE-001', 'LS-001', 'EQ-001', 'ICP-OES Analyzer', '0.01', '100', 'high', '', ''],
            ['AT-001', 'ANALYTE-002', 'LS-001', 'EQ-002', 'Spectrophotometer', '0.05', '50', 'medium', '', ''],
        ];
    }
}
