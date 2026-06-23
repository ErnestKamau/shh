<?php

namespace App\Exports\Billing;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class QuotationKpiExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly array $rows,
        private readonly string $sheetTitle = 'Quotation KPIs',
    ) {}

    public function array(): array
    {
        return array_map(fn (array $row): array => [
            $row['date'],
            $row['quotations_sent'],
            $row['quotations_accepted'],
            $row['success_rate_percent'],
            $row['total_quotation_value'],
            $row['accepted_value'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'No. of Quotations Sent',
            'No. of Quotations Accepted',
            'Success Rate (%)',
            'Total Quotation Value',
            'Accepted Value',
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
