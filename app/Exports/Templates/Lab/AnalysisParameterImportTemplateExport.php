<?php

namespace App\Exports\Templates\Lab;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalysisParameterImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'parameter',
            'reporting_unit',
            'method',
            'accredited',
            'lab_section',
            'operator',
            'tat',
            'equipment',
        ];
    }

    public function array(): array
    {
        return [
            [
                'pH',
                'pH Units',
                'APHA 4500-H+',
                '1',
                'Chemistry',
                'Jane Doe',
                '2',
                'pH Meter',
            ],
            [
                'Iron as Fe',
                'mg/L',
                'APHA 3111B',
                '0',
                'Instrumental',
                '',
                '3',
                'ICP-OES',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = 'H';

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }
}
