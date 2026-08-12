<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalysisTypeTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'sample_type_name*',
            'sample_type_code',
            'analysis_type_code',
            'analysis_type_name*',
            'active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Drinking Water', 'Drinking Water', 'Chemical', 'Chemical', '1'],
            ['Seafood', 'Sea Food', 'Microbiological', 'Microbiological', '1'],
        ];
    }
}
