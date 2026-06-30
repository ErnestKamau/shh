<?php

namespace App\Exports\Lab;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RegistrationKpiDetailExport implements FromArray, WithHeadings, WithTitle
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
            $row['samples_scheduled_collected'],
            $row['sampler'],
            $row['equipment_id'],
            $row['job_sample_id'],
            $row['location'],
            $row['sampling_points'],
            $row['parameters'],
            $row['temp'],
            $row['units'],
            $row['volume'],
            $row['registered_by'],
            $row['due_date'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Client',
            'Samples Scheduled/Collected',
            'Sampler',
            'Equipment ID',
            'Job/Sample ID',
            'Location',
            'Sampling Points',
            'Parameters',
            'Temp',
            'Units',
            'Volume',
            'Registered By',
            'Due Date',
        ];
    }

    public function title(): string
    {
        return 'Registration Detail';
    }
}
