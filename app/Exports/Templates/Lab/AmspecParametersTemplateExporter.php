<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AmspecParametersTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'Lab Section*',
            'Sample Type*',
            'Analysis Type*',
            'Parameters*',
            'Method*',
            'Reporting Unit*',
            'Decimal Places*',
            'Accreditation*',
            'Equipment*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'Chemical',
                'Food',
                'Food and feed',
                'Moisture and volatile matter',
                'AMS/C/SOP/024',
                'g/100g',
                '2',
                'Accredited',
                'Balance/Hot Air Oven',
            ],
            [
                'Chemical',
                'Food',
                'Food and feed',
                'Total Ash',
                'AMS/C/SOP/025',
                'g/100g',
                '2',
                'Accredited',
                'Muffle Furnace',
            ],
            [
                'Microbiology',
                'Air monitoring',
                'Air Testing',
                'Total Plate Count',
                'AMS/M/SOP/046',
                'CFU/m3',
                '1',
                'Accredited',
                'Incubator/Biosafety Cabinet/Colony Counter/Air Sampler',
            ],
        ];
    }
}