<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Standards;
use App\StandardAnalytes;
use App\Analyte;
use Illuminate\Support\Collection;

class ComplexStandardImporter extends BaseImporter
{
    protected array $standardColumns = [];

    protected function afterHeaderRowDetected(array &$headerMap, Collection $rows): void
    {
        // Find the "Allowable limit" header
        $allowableLimitCol = -1;
        foreach ($headerMap as $colIndex => $headerName) {
            if (str_contains($headerName, 'allowable_limit') || str_contains($headerName, 'specification')) {
                $allowableLimitCol = $colIndex;
                break;
            }
        }

        if ($allowableLimitCol !== -1) {
            // Look at the row below the header row (index + 1)
            $subHeaderRow = $rows->get($this->headerRowIndex + 1);
            if ($subHeaderRow) {
                $subHeaderRow = $subHeaderRow instanceof Collection ? $subHeaderRow->toArray() : (array)$subHeaderRow;
                
                // Columns from allowableLimitCol onwards are standards
                foreach ($subHeaderRow as $colIndex => $value) {
                    if ($colIndex >= $allowableLimitCol && !empty($value)) {
                        $this->standardColumns[$colIndex] = trim((string)$value);
                        // Add to headerMap so we capture it in collection()
                        $headerMap[$colIndex] = 'std_' . $this->normalizeHeaderName((string)$value);
                    }
                }
            }
            
            // Mark the sub-header row as something to skip
            $this->headerRowIndex++; 
        }
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        if (empty($this->fuzzyGet($row, ['chemical_name', 'analyte', 'parameter', 'name']))) {
            $errors[] = 'Chemical Name is required';
        }
        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $chemicalName = $this->fuzzyGet($row, ['chemical_name', 'analyte', 'parameter', 'name']);
        
        // Find or create analyte
        $analyte = Analyte::where('company_id', $this->batch->company_id)
            ->where(function($q) use ($chemicalName) {
                $q->where('name', 'like', "%$chemicalName%")
                  ->orWhere('code', 'like', "%$chemicalName%");
            })
            ->first();

        if (!$analyte) {
            $analyte = Analyte::create([
                'name' => $chemicalName,
                'code' => $this->normalizeHeaderName($chemicalName),
                'company_id' => $this->batch->company_id,
                'active' => 1,
            ]);
        }

        $limits = [];
        foreach ($row as $key => $value) {
            if (str_starts_with($key, 'std_') && !empty($value)) {
                $stdName = substr($key, 4); // Remove 'std_'
                $limits[$stdName] = $value;
            }
        }

        return [
            'analyte_id' => $analyte->id,
            'limits' => $limits,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $analyteId = $transformedData['analyte_id'];
        $limits = $transformedData['limits'];

        foreach ($limits as $stdKey => $value) {
            // Find or create Standard
            // stdKey is normalized, we might want the original name from standardColumns
            // But for now let's just use it.
            
            $standard = Standards::firstOrCreate([
                'code' => strtoupper($stdKey),
                'company_id' => $this->batch->company_id,
            ], [
                'name' => str_replace('_', ' ', $stdKey),
                'main_standard' => $this->sheetTitle ?: 'Imported Standards',
            ]);

            // Update or create StandardAnalytes
            StandardAnalytes::updateOrCreate([
                'standard_id' => $standard->id,
                'analyte_id' => $analyteId,
            ], [
                'expected_value' => (string)$value,
                'is_active' => 1,
            ]);
        }

        return true;
    }
}
