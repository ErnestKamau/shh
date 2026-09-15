<?php

namespace App\Services\Billing;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

        $this->writePackageLayoutSheet(
            $import,
            ['Sample', 'Test', 'Method', 'cost_price', 'selling_price', 'tax'],
            [
                [
                    'sample' => 'Hand Swab',
                    'values' => [null, null, null, 50, 85, 5],
                    'tests' => [
                        ['TPC', 'AMS/M/SOP/046'],
                        ['Yeast & Mould', 'AMS/M/SOP/046'],
                        ['E.coli', 'AMS/M/SOP/050'],
                    ],
                ],
                [
                    'sample' => 'AIR',
                    'values' => [null, null, null, 20, 20, 5],
                    'tests' => [
                        ['Heterotopic plate count', 'AMS/M/SOP/038'],
                        ['Enumeration Of Legionella', 'AMS/M/SOP/045'],
                    ],
                ],
            ],
            [22, 36, 20, 14, 16, 8],
            [1, 4, 5, 6],
        );

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $this->writeInstructionLines($instructions, [
            'Amspec LIMS pricelist import template',
            '',
            'Layout (row 1 headers on the Import sheet):',
            '• Sample — sample type name in LIMS (must match Sample Types). Write it once per package.',
            '• Test — one analysis / parameter name per row (must match LIMS).',
            '• Method — existing LIMS method code for that test (e.g. AMS/M/SOP/046). Used to pick the correct analysis element; methods are not created.',
            '• cost_price — lab cost for the whole package (enter on the first row of the package).',
            '• selling_price — client price for the whole package (enter on the first row of the package).',
            '• tax — optional VAT % (e.g. 5). Leave blank on continuation rows.',
            '',
            'How to arrange rows (see the example):',
            '• One package = one Sample block spanning several Test rows (merged Sample / price cells in the example).',
            '• Put cost_price, selling_price, and tax on the first row of each Sample block only.',
            '• Leave Sample and price cells blank on follow-on Test rows (or keep them merged like the example).',
            '• Do not put every test on one cell with semicolons — use one Test (+ Method) per row.',
            '',
            'Pricing rules:',
            '• Enter cost_price and selling_price on the package (first) row. Do not use one price for both.',
            '• For packages: quotation line total = number of samples × selling_price (once), not × number of tests.',
            '• Sample type, test, and method codes must match LIMS. Unmatched rows are skipped with a warning.',
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildQuotationWorkbook(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $import = $spreadsheet->getActiveSheet();
        $import->setTitle('Import');

        $this->writePackageLayoutSheet(
            $import,
            ['Sample', 'Test', 'Method', 'quantity_required', 'quantity', 'unit_price', 'tax'],
            [
                [
                    'sample' => 'Hand Swab',
                    'values' => [null, null, null, 'Per sample swab', 1, 85, 5],
                    'tests' => [
                        ['TPC', 'AMS/M/SOP/046'],
                        ['Yeast & Mould', 'AMS/M/SOP/046'],
                        ['E.coli', 'AMS/M/SOP/050'],
                    ],
                ],
                [
                    'sample' => 'AIR',
                    'values' => [null, null, null, 'Per Plate Sample', 4, 20, 5],
                    'tests' => [
                        ['Heterotopic plate count', 'AMS/M/SOP/038'],
                        ['Enumeration Of Legionella', 'AMS/M/SOP/045'],
                    ],
                ],
            ],
            [22, 36, 20, 22, 12, 14, 8],
            [1, 4, 5, 6, 7],
        );

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $this->writeInstructionLines($instructions, [
            'Amspec LIMS quotation prep import template',
            '',
            'Layout (row 1 headers on the Import sheet):',
            '• Sample — LIMS sample type name. Write it once per package.',
            '• Test — one analysis / parameter name per row.',
            '• Method — existing LIMS method code for that test (used to pick the correct analysis element).',
            '• quantity_required — free text (e.g. Per sample swab). Enter on the first row of the package.',
            '• quantity — number of samples for this package line. Enter on the first row.',
            '• unit_price — selling unit price for the package. Enter on the first row.',
            '• tax — optional VAT %.',
            '',
            'How to arrange rows (see the example):',
            '• One package = one Sample block with several Test rows (merged Sample / commercial cells in the example).',
            '• Put quantity_required, quantity, unit_price, and tax on the first row of each Sample block only.',
            '• Leave Sample and commercial cells blank on follow-on Test rows.',
            '• Do not list all tests in one cell with semicolons — one Test (+ Method) per row.',
            '',
            'Package total = quantity × unit_price (once per package), not × number of tests.',
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Write AmSpec-style package blocks: Sample (+ commercial columns) merge across tests.
     *
     * @param  list<string>  $headers
     * @param  list<array{sample: string, values: list<mixed>, tests: list<array{0: string, 1: string}>}>  $packages
     * @param  list<int>  $widths
     * @param  list<int>  $mergeColumnIndexes  1-based column indexes to merge within each package block
     */
    private function writePackageLayoutSheet(
        Worksheet $sheet,
        array $headers,
        array $packages,
        array $widths,
        array $mergeColumnIndexes,
    ): void {
        $lastColumn = count($headers);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumn);

        foreach ($headers as $index => $header) {
            $column = $index + 1;
            $sheet->setCellValue([$column, 1], $header);
            $sheet->getColumnDimensionByColumn($column)->setWidth($widths[$index] ?? 18);
        }

        $sheet->getStyle('A1:'.$lastColumnLetter.'1')->applyFromArray([
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

        $row = 2;
        /** @var list<array{start: int, end: int}> $packageRanges */
        $packageRanges = [];

        foreach ($packages as $package) {
            $tests = $package['tests'];
            if ($tests === []) {
                continue;
            }

            $startRow = $row;
            $endRow = $row + count($tests) - 1;
            $values = $package['values'];
            $packageRanges[] = ['start' => $startRow, 'end' => $endRow];

            foreach ($tests as $testIndex => $test) {
                $sheet->setCellValue([1, $row], $testIndex === 0 ? $package['sample'] : '');
                $sheet->setCellValue([2, $row], $test[0]);
                $sheet->setCellValue([3, $row], $test[1]);

                foreach ($values as $valueIndex => $value) {
                    $column = $valueIndex + 1;
                    if ($column <= 3) {
                        continue;
                    }
                    $sheet->setCellValue([$column, $row], $testIndex === 0 ? ($value ?? '') : '');
                }

                $row++;
            }

            if ($endRow > $startRow) {
                foreach ($mergeColumnIndexes as $columnIndex) {
                    $letter = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->mergeCells($letter.$startRow.':'.$letter.$endRow);
                    $sheet->getStyle($letter.$startRow)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(
                            $columnIndex === 1
                                ? Alignment::HORIZONTAL_LEFT
                                : Alignment::HORIZONTAL_CENTER
                        );
                }
            }
        }

        $dataEnd = max(2, $row - 1);
        $sheet->getStyle('A2:'.$lastColumnLetter.$dataEnd)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D5DD'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Dark outer outline per Sample package so blocks read clearly apart.
        foreach ($packageRanges as $range) {
            $block = 'A'.$range['start'].':'.$lastColumnLetter.$range['end'];
            $sheet->getStyle($block)->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '1F2937'],
                    ],
                ],
            ]);
        }

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
