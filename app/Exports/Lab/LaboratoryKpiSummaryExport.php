<?php

namespace App\Exports\Lab;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaboratoryKpiSummaryExport implements FromArray, WithHeadings, WithTitle
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
            $row['jobs_received'],
            $row['jobs_completed'],
            $row['jobs_pending'],
            $row['data_entry_complete'],
            $row['data_entry_partial'],
            $row['data_entry_not_started'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Jobs Received',
            'Jobs Completed',
            'Jobs Pending',
            'Data Entry Complete',
            'Data Entry Partial',
            'Data Entry Not Started',
        ];
    }

    public function title(): string
    {
        return 'Laboratory KPIs';
    }
}
