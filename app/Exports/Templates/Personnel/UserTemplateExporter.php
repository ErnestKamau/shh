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
            'department_code',
            'password*',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['John', 'Doe', 'john@example.com', 'ZONE-001', 'QA', 'SecurePassword123!'],
            ['Jane', 'Smith', 'jane@example.com', 'ZONE-002', 'LAB-OPS', 'SecurePassword456!'],
        ];
    }
}
