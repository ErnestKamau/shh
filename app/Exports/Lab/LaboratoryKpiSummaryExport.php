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
            $row['data_entry_partial'] ?? 0,
            $row['data_entry_not_started'] ?? 0,
            $row['review_pending'] ?? 0,
            $row['review_approved'] ?? 0,
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
            'Review Pending',
            'Review Approved',
        ];
    }

    public function title(): string
    {
        return 'Laboratory KPIs';
    }
}
