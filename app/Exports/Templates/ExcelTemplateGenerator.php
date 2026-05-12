<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

abstract class ExcelTemplateGenerator implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected array $headers = [];
    protected array $examples = [];
    protected int $columnWidthDefault = 20;

    /**
     * Get the headers for the template.
     * Override in subclass to mark required fields with *.
     */
    abstract protected function defineHeaders(): array;

    /**
     * Get example rows for the template.
     * Override in subclass with realistic examples.
     */
    abstract protected function defineExamples(): array;

    /**
     * Get the array representation of the template.
     */
    public function array(): array
    {
        $this->headers = $this->defineHeaders();
        $this->examples = $this->defineExamples();

        return [];
    }

    /**
     * Get headings with required field markers.
     */
    public function headings(): array
    {
        return $this->defineHeaders();
    }

    /**
     * Apply styles to the template.
     */
    public function styles($sheet)
    {
        // Style header row
        $headerRow = 1;
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $cell = $column . $headerRow;
            
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
        }

        // Row 2 is intentionally left blank for user data entry.
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $cell = $column . '2';
            $sheet->getStyle($cell)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        return $sheet;
    }

    /**
     * Get column widths.
     */
    public function columnWidths(): array
    {
        $headers = $this->defineHeaders();
        $widths = [];
        
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $widths[$column] = $this->columnWidthDefault;
        }
        
        return $widths;
    }
}
