<?php

namespace App\Exports\Templates\Equipment;

use App\Exports\Templates\ExcelTemplateGenerator;

class EquipmentTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'Equipment /Instrument*',
            'Model*',
            'Serial No.',
            'Operating Software',
            'GCLA code*',
            'Lab./Office Name*',
            'Country of origin',
            'Installation Year',
            'Power requirement',
            'Manual Availability',
            'STATUS*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                '3500xl Genetic Analyzer',
                'Applied Biosystems(622-0015)',
                '31397-071',
                '3500 Series Data Collection Software',
                'TR242*0002028',
                'DNA Lab',
                'Japan',
                '2019',
                '100-240V',
                'NO',
                'Working'
            ],
            [
                'Real Time PCR System',
                '7500 AB-Applied Biosystems',
                '275006291',
                'HID Real time PCR analysis',
                'TR242*0001855',
                'DNA Lab',
                'Singapore',
                '2010',
                '1080 W',
                'NO',
                'Working'
            ],
        ];
    }
}
