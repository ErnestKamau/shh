<?php

namespace App\Exports\Templates\Personnel;

use App\Exports\Templates\ExcelTemplateGenerator;

class UserTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'first_name*',
            'last_name*',
            'email*',
            'zone_code',
            'zone_name',
            'department_name',
            'position',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['John', 'Doe', 'john@example.com', 'ZONE-001', 'Main Zone', 'Quality Assurance', 'Analyst'],
            ['Jane', 'Smith', 'jane@example.com', 'ZONE-002', 'Secondary Zone', 'Lab Operations', 'Manager'],
        ];
    }
}
