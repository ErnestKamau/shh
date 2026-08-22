<?php

namespace App\Exports\Sampleworkflow;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BatchResultsTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    /** Internal matching key — column is hidden in the downloaded workbook. */
    public const HEADING_ROW_KEY = 'Row Key';

    public const HEADING_BATCH_CODE = 'Batch Code';

    public const HEADING_LAB_SECTION = 'Lab Section';

    public const HEADING_SAMPLE = 'Sample';

    public const HEADING_TEST = 'Test';

    public const HEADING_ANALYSTS = 'Analysts';

    public const HEADING_RESULT = 'Result';

    public const HEADING_WORKSHEET_NUMBER = 'Worksheet Number';

    /**
     * @param  list<array<string, mixed>>  $flatRows
     */
    public function __construct(
        private readonly array $flatRows,
        private readonly string $sheetTitle = 'Results',
        private readonly bool $includeWorksheetNumber = false,
    ) {}

    public function array(): array
    {
        return array_map(function (array $row): array {
            $base = [
                (string) ($row['captured_result_id'] ?? ''),
                (string) ($row['batch_code'] ?? ''),
                (string) ($row['lab_section_name'] ?? ''),
                (string) ($row['sample_code'] ?? $row['sample_label'] ?? ''),
                (string) ($row['test_label'] ?? ''),
                (string) ($row['analysts'] ?? ''),
                (string) ($row['result'] ?? ''),
            ];

            if ($this->includeWorksheetNumber) {
                array_splice($base, 1, 0, [(string) ($row['worksheet_number'] ?? '')]);
            }

            return $base;
        }, $this->flatRows);
    }

    public function headings(): array
    {
        $headings = [
            self::HEADING_ROW_KEY,
            self::HEADING_BATCH_CODE,
            self::HEADING_LAB_SECTION,
            self::HEADING_SAMPLE,
            self::HEADING_TEST,
            self::HEADING_ANALYSTS,
            self::HEADING_RESULT,
        ];

        if ($this->includeWorksheetNumber) {
            array_splice($headings, 1, 0, [self::HEADING_WORKSHEET_NUMBER]);
        }

        return $headings;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                // Keep Row Key for import matching, but do not show UUIDs to users.
                $sheet->getColumnDimension('A')->setVisible(false);
                $sheet->getColumnDimension('A')->setCollapsed(true);

                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                for ($i = 2; $i <= $highestColumnIndex; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }
            },
        ];
    }
}
