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
            $row['samples_scheduled'] ?? 0,
            $row['samples_collected'] ?? 0,
            $row['sampler_name'] ?? ($row['sampler'] ?? '—'),
            $row['sampler_id'] ?? '—',
            $row['equipment_id'],
            $row['job_id'] ?? '—',
            $row['sample_id'] ?? '—',
            $row['sample_details'] ?? '—',
            $row['location'],
            $row['sampling_points'],
            $row['parameters'],
            $row['temperature'] ?? ($row['temp'] ?? '—'),
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
            'Client Name',
            'No. of Samples Scheduled',
            'No. of Samples Collected',
            'Sampler Name',
            'Sampler ID',
            'Equipment ID',
            'Job ID',
            'Sample ID',
            'Sample Details',
            'Location',
            'Sampling Points',
            'Test Parameters',
            'Temperature',
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
