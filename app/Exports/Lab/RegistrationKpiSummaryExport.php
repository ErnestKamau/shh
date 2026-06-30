<?php

namespace App\Exports\Lab;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RegistrationKpiSummaryExport implements FromArray, WithHeadings, WithTitle
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
            $row['samples_scheduled'],
            $row['samples_collected'],
            $row['registration_rate_percent'],
            $row['clients_registered'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Samples Scheduled',
            'Samples Collected',
            'Registration Rate (%)',
            'Clients Registered',
        ];
    }

    public function title(): string
    {
        return 'Registration KPIs';
    }
}
