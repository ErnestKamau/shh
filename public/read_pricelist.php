<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filepath = '/home/kaarr/polucon/pricelist.xlsx';

if (!file_exists($filepath)) {
    echo "File not found: $filepath\n";
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($filepath);
    $sheetNames = $spreadsheet->getSheetNames();
    echo "SHEET_NAMES: " . implode(', ', $sheetNames) . "\n\n";

    foreach ($sheetNames as $name) {
        $sheet = $spreadsheet->getSheetByName($name);
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        
        echo "=== Sheet '$name' ($highestRow rows, $highestCol columns) ===\n";
        
        // Let's get up to column index 15
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
        $limitColIndex = min($highestColIndex, 15);
        
        // Print column headers / letters first
        $colLetters = [];
        for ($c = 1; $c <= $limitColIndex; $c++) {
            $colLetters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        }
        echo "Cols: " . implode(" | ", $colLetters) . "\n";
        
        // Print first 40 rows
        $limitRow = min($highestRow, 40);
        for ($r = 1; $r <= $limitRow; $r++) {
            $rowData = [];
            for ($c = 1; $c <= $limitColIndex; $c++) {
                $cell = $sheet->getCellByColumnAndRow($c, $r);
                $val = $cell->getValue();
                
                // Convert RichText or other objects to string
                if (is_object($val)) {
                    if (method_exists($val, 'getPlainText')) {
                        $val = $val->getPlainText();
                    } else {
                        $val = (string)$val;
                    }
                }
                
                $rowData[] = $val;
            }
            
            // Check if row has any non-empty data
            $nonEmpty = array_filter($rowData, function($v) { return $v !== null && trim((string)$v) !== ''; });
            if (!empty($nonEmpty)) {
                $rowStr = [];
                foreach ($rowData as $idx => $v) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                    $rowStr[] = "$colLetter: " . ($v === null ? 'NULL' : '"' . addslashes($v) . '"');
                }
                echo "Row $r: [" . implode(", ", $rowStr) . "]\n";
            } else {
                echo "Row $r: (empty)\n";
            }
        }
        echo "========================================\n\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
