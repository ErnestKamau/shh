<?php

namespace App\Exports\Lab;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaboratoryKpiDetailExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly array $rows,
    ) {}

    public function array(): array
    {
        return array_map(fn (array $row): array => [
            $row['date'],
            $row['client'],
            $row['sample_details'],
            $row['jobs_received_completed_pending'],
            $row['data_entry_status'],
            $row['review_approval_status'],
            $row['final_reports'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Client',
            'No./Details of Samples',
            'Jobs Received/Completed/Pending',
            'Data Entry Status',
            'Review/Approval Status',
            'Final Reports',
        ];
    }

    public function title(): string
    {
        return 'Laboratory Detail';
    }
}
