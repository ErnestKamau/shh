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
            'lab_name*',
            'lab_section_name*',
            'analyte_name*',
            'reporting_unit',
            'decimal_places',
            'lod',
            'loq',
            'non_accredited',
            'equipment',
            'equipment_number',
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
                'Microbiology Lab',
                'Microbiology',
                'Mesophilic Aerobic Plate Count',
                'CFU/g',
                '1',
                '',
                '10',
                'FALSE',
                'Incubator, Biosafety Cabinet, Colony counter',
                'AMS/M/INS/037, AMS/M/INS/051, AMS/M/INS/034',
                'AMS/M/SOP/023',
            ],
            [
                'FOOD',
                'Food',
                'FOOD_AND_FEED',
                'Food and Feed',
                'Microbiology Lab',
                'Microbiology',
                'Enterobacteriaceae',
                'CFU/g',
                '1',
                '',
                '10',
                'FALSE',
                'Incubator, Biosafety Cabinet',
                'AMS/M/INS/038, AMS/M/INS/052',
                'AMS/M/SOP/021',
            ],
            [
                'FOOD',
                'Food',
                'FOOD_AND_FEED',
                'Food and Feed',
                'Microbiology Lab',
                'Microbiology',
                'Listeria monocytogenes',
                '/25g',
                '',
                '',
                '',
                'FALSE',
                'Incubator, Biosafety Cabinet, REAL TIME PCR',
                'AMS/M/INS/039, AMS/M/INS/051, AMS/M/INS/049',
                '',
            ],
        ];
    }
}
