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
    echo "Sheets: " . implode(', ', $sheetNames) . "\n\n";

    foreach ($sheetNames as $name) {
        $sheet = $spreadsheet->getSheetByName($name);
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        
        echo "Sheet '$name' - Rows: $highestRow, Cols: $highestCol\n";
        echo "First 15 rows:\n";
        
        // Let's get up to column index 15
        $limitRow = min($highestRow, 15);
        
        for ($r = 1; $r <= $limitRow; $r++) {
            $rowData = [];
            // Let's read columns from A to Z
            $colRange = range('A', min($highestCol, 'Z'));
            if ($highestCol > 'Z') {
                // If it's wider, support AA, AB etc.
                // For simplicity, we just look at the first 10 columns for print
                $colRange = range('A', 'J');
            }
            foreach ($colRange as $col) {
                $rowData[$col] = $sheet->getCell($col . $r)->getValue();
            }
            // Print only if there is at least one non-empty value in the row
            $filtered = array_filter($rowData, function($v) { return $v !== null && $v !== ''; });
            if (!empty($filtered)) {
                echo "Row $r: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo "Row $r: (empty)\n";
            }
        }
        echo "----------------------------------------\n\n";
    }
} catch (\Exception $e) {
    echo "Error reading file: " . $e->getMessage() . "\n";
}
