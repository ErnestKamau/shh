<?php

require 'vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HeaderExtractor implements ToCollection, WithHeadingRow
{
    public $headers = [];
    public function collection(Collection $rows)
    {
        if ($rows->count() > 0) {
            $this->headers = array_keys($rows->first()->toArray());
        }
    }
}

$files = [
    '/home/kaarr/Downloads/TZS standards.xls',
    '/home/kaarr/Downloads/EQUIPMENT MASTER LIST 2025-2026(1).xlsx',
    '/home/kaarr/Downloads/EMPLOYEES BY LOCATION - APRIL, 2026 (1).xlsx'
];

// Initialize Laravel app for Facades
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ($files as $file) {
    echo "File: $file\n";
    if (!file_exists($file)) {
        echo "  Error: File does not exist.\n";
        continue;
    }
    try {
        $extractor = new HeaderExtractor();
        Excel::import($extractor, $file);
        echo "  Headers: " . implode(', ', $extractor->headers) . "\n";
        
        // Also show first row to see data types
        $rows = Excel::toCollection(new HeaderExtractor(), $file);
        if ($rows->count() > 0 && $rows->first()->count() > 0) {
             echo "  First Row: " . json_encode($rows->first()->first()->toArray()) . "\n";
        }

    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
