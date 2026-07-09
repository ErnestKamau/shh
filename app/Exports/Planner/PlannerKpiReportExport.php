<?php

namespace App\Exports\Planner;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlannerKpiReportExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Client Name',
            'Contract Validity',
            'Contact Details',
            'Sample Categories (Scheduled)',
            'Sample Categories (Collected)',
            'Sample Details (Scheduled)',
            'Sample Details (Collected)',
            'No. Samples Scheduled',
            'No. Samples Collected',
            'Frequency',
            'Parameters (Scheduled)',
            'Parameters (Collected)',
            'Collection Status',
            'Collected At',
            'Location',
            'Personnel',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function map($row): array
    {
        return [
            $row['date'],
            $row['client_name'],
            $row['contract_validity'],
            $row['contact_details'],
            $row['scheduled_categories'],
            $row['collected_categories'],
            $row['scheduled_details'],
            $row['collected_details'],
            $row['scheduled_samples'],
            $row['collected_samples'],
            $row['frequency'],
            $row['scheduled_parameters'],
            $row['collected_parameters'],
            ucfirst($row['status']),
            $row['collected_at'] ?? '',
            $row['location'],
            $row['personnel'],
        ];
    }
}
