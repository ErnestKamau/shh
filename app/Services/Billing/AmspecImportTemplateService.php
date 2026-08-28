<?php

namespace App\Services\Billing;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AmspecImportTemplateService
{
    public function downloadExcel(string $context): BinaryFileResponse
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $isPricelist = $context === 'pricelist';
        $spreadsheet = $isPricelist
            ? $this->buildPricelistWorkbook()
            : $this->buildQuotationWorkbook();

        $filename = $isPricelist
            ? 'Amspec-Pricelist-import.xlsx'
            : 'Amspec-Quotation-prep-import.xlsx';

        $path = $this->writeWorkbookToTempFile($spreadsheet);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ])->deleteFileAfterSend(true);
    }

    private function writeWorkbookToTempFile(Spreadsheet $spreadsheet): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'amspec_tpl_');
        if ($tmp === false) {
            throw new \RuntimeException('Unable to create a temporary file for the Excel template.');
        }

        $path = $tmp.'.xlsx';
        if (! @rename($tmp, $path)) {
            @unlink($tmp);
            throw new \RuntimeException('Unable to prepare the Excel template download path.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    private function buildPricelistWorkbook(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $import = $spreadsheet->getActiveSheet();
        $import->setTitle('Import');

        $this->writeImportSheet(
            $import,
            ['sample_type', 'parameters', 'cost_price', 'selling_price', 'tax'],
            ['Hand Swab', 'TPC;Yeast & Mould;E.coli', 50, 85, 5],
            [22, 36, 14, 16, 8],
        );

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $this->writeInstructionLines($instructions, [
            'Amspec LIMS pricelist import template',
            '',
            'Required columns (row 1 on the Import sheet):',
            '• sample_type — sample type name in LIMS (must match Sample Types).',
            '• parameters — tests in this package, separated by semicolons (e.g. TPC;E.coli).',
            '• cost_price — the cost the lab incurs to perform this test or package.',
            '• selling_price — the price charged to clients on quotations.',
            '• tax — optional VAT % (e.g. 5). Leave blank to apply default VAT.',
            '',
            'Pricing rules:',
            '• Enter cost_price and selling_price on every row. Do not use one price for both.',
            '• cost_price is saved as the lab cost on the pricelist.',
            '• selling_price is saved as the client price on the pricelist and on quotations.',
            '• For packages: quotation line total = number of samples × selling_price (once), not × number of tests.',
            '• Sample type and test names must match LIMS. Rows that do not match are skipped with a warning.',
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildQuotationWorkbook(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $import = $spreadsheet->getActiveSheet();
        $import->setTitle('Import');

        $this->writeImportSheet(
            $import,
            ['sample_type', 'parameters', 'quantity_required', 'quantity', 'unit_price', 'tax'],
            ['Hand Swab', 'TPC;Yeast & Mould;E.coli', 'Per sample swab', 1, 85, 5],
            [22, 36, 22, 12, 14, 8],
        );

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $this->writeInstructionLines($instructions, [
            'Amspec LIMS quotation prep import template',
            '',
            'Required columns (row 1 on the Import sheet):',
            '• sample_type — LIMS sample type name.',
            '• parameters — semicolon-separated test/parameter names.',
            '• quantity_required — free text (e.g. Per sample swab).',
            '• quantity — number of samples for this package line.',
            '• unit_price — selling unit price for the package.',
            '• tax — optional VAT %.',
            '',
            'Package total = quantity × unit_price (once per package), not × number of tests.',
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<mixed>  $example
     * @param  list<int>  $widths
     */
    private function writeImportSheet(Worksheet $sheet, array $headers, array $example, array $widths): void
    {
        foreach ($headers as $index => $header) {
            $column = $index + 1;
            $sheet->setCellValue([$column, 1], $header);
            $sheet->setCellValue([$column, 2], $example[$index] ?? '');
            $sheet->getColumnDimensionByColumn($column)->setWidth($widths[$index] ?? 18);
        }

        $lastColumn = count($headers);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumn);
        $headerRange = 'A1:'.$lastColumnLetter.'1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8B1E2D'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->freezePane('A2');
    }

    /**
     * @param  list<string>  $lines
     */
    private function writeInstructionLines(Worksheet $sheet, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $row = $index + 1;
            $sheet->setCellValue([1, $row], $line);
            if ($index === 0) {
                $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13);
            }
        }

        $sheet->getColumnDimension('A')->setWidth(110);
        $sheet->getStyle('A1:A'.count($lines))->getAlignment()->setWrapText(true);
    }
}
