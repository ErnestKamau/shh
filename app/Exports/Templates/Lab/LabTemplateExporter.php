<?php

namespace App\Exports\Templates\Lab;

use App\Exports\Templates\ExcelTemplateGenerator;

class LabTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'directorate_name*',
            'zone_name*',
            'lab_code*',
            'lab_name*',
            'address',
            'phone1',
            'manager_email',
            'start_sample_no',
            'internal_or_external',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['MAIN DIRECTORATE', 'Zone A', 'LAB-001', 'Lab A', '123 Main St', '+1234567890', 'manager@company.com', '1000', 'internal'],
            ['MAIN DIRECTORATE', 'Zone B', 'LAB-002', 'Lab B', '456 Secondary St', '+0987654321', 'manager2@company.com', '2000', 'external'],
        ];
    }
}
