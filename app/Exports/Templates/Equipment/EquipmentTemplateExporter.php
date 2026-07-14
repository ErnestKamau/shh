<?php

namespace App\Exports\Templates\Equipment;

use App\Exports\Templates\ExcelTemplateGenerator;

class EquipmentTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'Equipment Name*',
            'Equipment ID*',
            'Serial',
            'Model*',
            'Manufacturer',
            'Department*',
            'Calibration Duration (Months)',
            'Calibration Date',
            'Calibration Due Date',
            'Operational Status',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            [
                'Example GC Analyzer',
                'AMS/C/INS/000',
                'SN-EXAMPLE-001',
                'MODEL-001',
                'Example Manufacturer',
                'Chemistry',
                '12',
                '2025-01-15',
                '2026-01-15',
                'In Use',
            ],
            [
                'Example Hot Air Oven',
                'AMS/C/INS/001',
                'SN-EXAMPLE-002',
                'MODEL-002',
                'Example Manufacturer',
                'Microbiology',
                '6',
                '2025-06-01',
                '2025-12-01',
                'In Use',
            ],
        ];
    }
}
