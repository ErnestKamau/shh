<?php

namespace App\Exports\Registry;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistryRequestsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $rows,
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            $row->reference_no,
            $row->category?->name,
            $row->subject,
            $row->status,
            $row->priority,
            $row->direction,
            $row->current_stage,
            $row->submitting_party,
            $row->received_at?->toDateTimeString(),
            $row->created_at?->toDateTimeString(),
        ]);
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Category',
            'Subject',
            'Status',
            'Priority',
            'Direction',
            'Stage',
            'Submitting Party',
            'Received At',
            'Created At',
        ];
    }
}
