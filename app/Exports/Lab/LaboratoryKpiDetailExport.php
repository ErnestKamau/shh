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
            $row['no_of_samples'] ?? 1,
            $row['sample_details'],
            $row['jobs_received'] ?? 0,
            $row['analysis_completed'] ?? 0,
            $row['analysis_pending'] ?? 0,
            $row['data_entry_status'],
            $row['review_for_approval'] ?? ($row['review_approval_status'] ?? '—'),
            $row['pending_approval_count'] ?? 0,
            $row['final_reports_issued'] ?? ($row['final_reports'] ?? '—'),
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Client Name',
            'No. of Samples',
            'Sample Details',
            'Jobs Received',
            'Analysis Completed',
            'Analysis Pending/Ongoing',
            'Data Entry Status',
            'Review for Approval',
            'Pending Approval Count',
            'Final Reports Issued',
        ];
    }

    public function title(): string
    {
        return 'Laboratory Detail';
    }
}
