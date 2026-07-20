<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AnalysisMethodTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'Method Name*',
            'Method Code*',
            'Description',
            'Method Category*',
            'Reference Method',
            'Method Version',
            'Active',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'CMMEF 5th Edition Chapter 8',
                'CMMEF 5th Edition Chapter 8',
                'Mesophilic aerobic plate count reference method',
                'Reference Method',
                '',
                '',
                'Yes',
            ],
            [
                'AMS/M/SOP/023',
                'AMS/M/SOP/023',
                'In house MAPC procedure',
                'Laboratory Test Method',
                'CMMEF 5th Edition Chapter 8',
                'Rev.00',
                'Yes',
            ],
            [
                'Random Sampling Plan',
                'Random Sampling Plan',
                'Field sampling procedure',
                'Sampling Method',
                '',
                '',
                'Yes',
            ],
        ];
    }
}
