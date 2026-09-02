<?php

namespace Tests\Unit\Services\Billing;

use App\Services\Billing\AmspecImportLabelMatcher;
use App\Services\Billing\AmspecPackagePdfParser;
use App\Services\Billing\PdfTextExtractor;
use App\Services\Billing\PricelistPackageImportService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class PricelistPackageImportExcelCollapseTest extends TestCase
{
    public function test_excel_merges_continuation_tests_under_sample_packages(): void
    {
        $path = $this->writeTempWorkbook([
            ['Sample', 'Test', 'Method', 'Qty', 'Unit Price (AED)'],
            ['Food', 'Mesophilic Aerobic plate count', 'CMMEF 5', '1', 20],
            ['', 'Enumeration of Yeast and Moulds', 'CMMEF 5', '', ''],
            ['', 'Detection of Salmonella', 'CMMEF 5', '', ''],
            ['Water', 'Heterotopic plate count', 'APHA', '1', 20],
            ['', 'Enumeration of E. coli', 'APHA', '', ''],
            ['Net Amount', '', '', '', 40],
            ['Grand Total', '', '', '', 42],
        ]);

        try {
            $file = new UploadedFile($path, 'hotel.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $rows = $this->service()->parseExcelRows($file);

            $this->assertCount(2, $rows);
            $this->assertSame('Food', $rows[0]['sample_type']);
            $this->assertSame(20.0, (float) $rows[0]['selling_price']);
            $this->assertSame(
                'Mesophilic Aerobic plate count; Enumeration of Yeast and Moulds; Detection of Salmonella',
                $rows[0]['parameters']
            );
            $this->assertTrue((bool) $rows[0]['is_package']);

            $this->assertSame('Water', $rows[1]['sample_type']);
            $this->assertSame(
                'Heterotopic plate count; Enumeration of E. coli',
                $rows[1]['parameters']
            );
        } finally {
            @unlink($path);
        }
    }

    public function test_excel_reads_separate_cost_and_selling_prices(): void
    {
        $path = $this->writeTempWorkbook([
            ['sample_type', 'parameters', 'cost_price', 'selling_price', 'tax'],
            ['Hand Swab', 'TPC;Yeast & Mould', 50, 85, 5],
        ]);

        try {
            $file = new UploadedFile($path, 'pricelist.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $rows = $this->service()->parseExcelRows($file);

            $this->assertCount(1, $rows);
            $this->assertSame('Hand Swab', $rows[0]['sample_type']);
            $this->assertSame(50.0, (float) $rows[0]['cost_price']);
            $this->assertSame(85.0, (float) $rows[0]['selling_price']);
        } finally {
            @unlink($path);
        }
    }

    public function test_no_of_samples_header_does_not_overwrite_sample_column(): void
    {
        $path = $this->writeTempWorkbook([
            ['Sample', 'Test', 'No. Of Samples', 'Unit Price (AED)'],
            ['Hand Swab', 'Enumeration of E. coli', '10', 20],
            ['', 'Enumeration of Enterobacteriaceae', '', ''],
        ]);

        try {
            $file = new UploadedFile($path, 'swab.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $rows = $this->service()->parseExcelRows($file);

            $this->assertCount(1, $rows);
            $this->assertSame('Hand Swab', $rows[0]['sample_type']);
            $this->assertSame(20.0, (float) $rows[0]['selling_price']);
            $this->assertSame(
                'Enumeration of E. coli; Enumeration of Enterobacteriaceae',
                $rows[0]['parameters']
            );
        } finally {
            @unlink($path);
        }
    }

    public function test_excel_merges_repeated_sample_type_labels_into_one_package(): void
    {
        $parameters = [
            'pH',
            'Electrical Conductivity @25°C',
            'Total Dissolved Solids',
            'HPC',
            'Legionella',
        ];

        $matrix = [
            ['Sample', 'Test', 'Cost Price (AED)', 'Unit Price (AED)', 'Tax %'],
        ];

        foreach ($parameters as $index => $parameter) {
            $matrix[] = [
                'Fresh Water',
                $parameter,
                $index === 0 ? 500 : '',
                $index === 0 ? 700 : '',
                $index === 0 ? 5 : '',
            ];
        }

        $path = $this->writeTempWorkbook($matrix);

        try {
            $file = new UploadedFile($path, 'fresh-water.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $rows = $this->service()->parseExcelRows($file);

            $this->assertCount(1, $rows);
            $this->assertSame('Fresh Water', $rows[0]['sample_type']);
            $this->assertSame(500.0, (float) $rows[0]['cost_price']);
            $this->assertSame(700.0, (float) $rows[0]['selling_price']);
            $this->assertSame('5', (string) $rows[0]['tax']);
            $this->assertTrue((bool) $rows[0]['is_package']);
            $this->assertSame(
                implode('; ', $parameters),
                $rows[0]['parameters']
            );
        } finally {
            @unlink($path);
        }
    }

    private function service(): PricelistPackageImportService
    {
        return new PricelistPackageImportService(
            new PdfTextExtractor(),
            new AmspecPackagePdfParser(),
            new AmspecImportLabelMatcher(),
        );
    }

    /**
     * @param  list<list<mixed>>  $matrix
     */
    private function writeTempWorkbook(array $matrix): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($matrix as $rowIndex => $cells) {
            foreach ($cells as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowIndex + 1], $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'pl-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}
