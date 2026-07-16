<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class AmspecParametersTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'sample_type_code',
            'sample_type_name*',
            'analysis_type_code',
            'analysis_type_name*',
            'lab_code',
            'lab_section_code',
            'analyte_code',
            'analyte_name*',
            'reporting_unit',
            'decimal_places',
            'lod',
            'loq',
            'non_accredited',
            'equipment_code',
            'method',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'FOOD',
                'Food',
                'FOOD_AND_FEED',
                'Food and Feed',
                'LAB-AGF',
                'MICROBIOLOGY',
                'MB-MAP',
                'Mesophilic Aerobic Plate Count',
                'CFU/g',
                '1',
                '',
                '10',
                'FALSE',
                'INCUBATOR,BIOSAFETY_CABINET,COLONY_COUNTER',
                '',
            ],
            [
                'FOOD',
                'Food',
                'FOOD_AND_FEED',
                'Food and Feed',
                'LAB-AGF',
                'MICROBIOLOGY',
                'ENTEROB',
                'Enterobacteriaceae',
                'CFU/g',
                '1',
                '',
                '10',
                'FALSE',
                'INCUBATOR,BIOSAFETY_CABINET',
                '',
            ],
            [
                'FOOD',
                'Food',
                'FOOD_AND_FEED',
                'Food and Feed',
                'LAB-AGF',
                'MICROBIOLOGY',
                'SALMONELLA',
                'Salmonella',
                '/25g',
                '',
                '',
                '',
                'FALSE',
                'INCUBATOR,BIOSAFETY_CABINET',
                '',
            ],
        ];
    }
}
