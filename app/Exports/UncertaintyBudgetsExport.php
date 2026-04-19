<?php

namespace App\Exports;

use App\UncertaintyBudget;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UncertaintyBudgetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function __construct(protected Collection $budgets)
    {
    }

    public function collection(): Collection
    {
        return $this->budgets;
    }

    public function headings(): array
    {
        return [
            'Analyte Name',
            'Analyte Code',
            'Methods',
            'Coverage Factor (k)',
            'Confidence Level (%)',
            'Combined Standard Uncertainty',
            'Expanded Uncertainty',
            'Status',
            'Created Date',
        ];
    }

    /**
     * @param  UncertaintyBudget  $row
     */
    public function map($row): array
    {
        return [
            $row->analyte_name ?? '',
            $row->analyte_code ?? '',
            $row->method_name ?? '',
            $row->coverage_factor_k ?? '',
            $row->confidence_level ?? '',
            $row->formatted_combined_uncertainty ?? '',
            $row->formatted_expanded_uncertainty ?? '',
            $row->active ? 'Active' : 'Inactive',
            $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 18,
            'C' => 45,
            'D' => 18,
            'E' => 20,
            'F' => 30,
            'G' => 25,
            'H' => 14,
            'I' => 22,
        ];
    }
}
