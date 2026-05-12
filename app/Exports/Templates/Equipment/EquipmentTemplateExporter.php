<?php

namespace App\Exports\Templates\Equipment;

use App\Exports\Templates\ExcelTemplateGenerator;

class EquipmentTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'equipment_number*',
            'make*',
            'model*',
            'serial_number*',
            'asset_type_code*',
            'asset_location_code*',
            'calibration_days',
            'maintenance_days',
            'requires_daily_log',
            'daily_log_value_type',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['EQ-001', 'Shimadzu', 'HPLC-2030', 'SN-12345', 'AT-001', 'LOC-001', '365', '180', '1', 'numeric'],
            ['EQ-002', 'PerkinElmer', 'GC-2000', 'SN-67890', 'AT-002', 'LOC-001', '180', '90', '0', 'text'],
        ];
    }
}
