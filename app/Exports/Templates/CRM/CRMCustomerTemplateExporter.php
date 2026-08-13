<?php

namespace App\Exports\Templates\CRM;

use App\Exports\Templates\ExcelTemplateGenerator;

class CRMCustomerTemplateExporter extends ExcelTemplateGenerator
{
    protected function defineHeaders(): array
    {
        return [
            'name*',
            'customer_code*',
            'client_code',
            'physical_address',
            'email*',
            'phone1*',
            'phone2',
            'country_code*',
            'vat_no',
            'credit_days',
            'currency_code*',
            'lpos_required',
        ];
    }

    protected function defineExamples(): array
    {
        return [
            ['Acme Corporation', 'CUST-001', 'CUST-001', '123 Business St', 'contact@acme.com', '+1234567890', '+0987654321', 'US', '123456789', '30', 'USD', '1'],
            ['Tech Solutions Ltd', 'CUST-002', 'CUST-002', '456 Enterprise Ave', 'info@techsol.com', '+1111111111', '', 'GB', '987654321', '45', 'GBP', '0'],
        ];
    }
}
