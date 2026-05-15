<?php

namespace App\Imports;

use App\Factories\BulkImportTemplateFactory;
use App\Models\BulkImportBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

abstract class BaseImporter implements ToCollection, SkipsOnFailure, WithMultipleSheets, SkipsUnknownSheets, WithTitle, WithEvents
{
    use Importable;

    protected BulkImportBatch $batch;
    protected int $rowNumber = 1;
    protected array $errors = [];
    protected array $detectedHeaders = [];
    protected int $sheetCount = 1;
    protected string $sheetTitle = '';
    protected int $headerRowIndex = -1;

    /**
     * Constructor.
     */
    public function __construct(?BulkImportBatch $batch = null)
    {
        if ($batch) {
            $this->batch = $batch;
        } else {
            // Create a dummy batch if none provided (for legacy compatibility)
            $this->batch = BulkImportBatch::create([
                'module' => 'generic',
                'status' => 'started',
                'company_id' => getUserCompany(),
                'user_id' => auth()->id(),
                'imported_rows' => 0,
                'total_rows' => 0,
                'error_rows' => 0,
            ]);
        }
    }

    /**
     * Reset batch counters.
     */
    public function resetBatchCounters(): void
    {
        $this->batch->total_rows = 0;
        $this->batch->imported_rows = 0;
        $this->batch->error_rows = 0;
        $this->batch->errors_json = [];
        $this->batch->upserted_summary = [];
        $this->batch->save();
    }

    /**
     * Multiple Sheets Support
     */
    public function sheets(): array
    {
        $sheets = [];
        // We use a range of 100 to support files with many tabs.
        // SkipsUnknownSheets will handle if there are fewer.
        for ($i = 0; $i < 100; $i++) {
            $sheets[$i] = clone $this;
        }
        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        // Do nothing
    }

    public function setTitle(string $title): void
    {
        $this->sheetTitle = $title;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                // This is called on the main instance.
                $this->sheetCount = $event->reader->getSheetCount();
            },
            BeforeSheet::class => function(BeforeSheet $event) {
                // This is called on each sheet instance.
                $this->sheetTitle = $event->sheet->getTitle();
                $this->onSheetLoaded($this->sheetTitle);
            },
        ];
    }

    /**
     * Hook called when a sheet is loaded. Override in subclass.
     */
    protected function onSheetLoaded(string $title): void
    {
        // Override in subclass
    }

    /**
     * Collection callback.
     */
    public function collection(Collection $rows)
    {
        $headerMap = $this->findHeaderRow($rows);
        
        if (empty($headerMap)) {
            // Fallback to row 0 if no match found
            $headerMap = $this->mapHeadersFromRow($rows->first() ?? []);
            $this->headerRowIndex = 0;
        } else {
            $this->afterHeaderRowDetected($headerMap, $rows);
        }

        $this->detectedHeaders = array_values($headerMap);

        foreach ($rows as $index => $row) {
            // Skip rows before and including the header row
            if ($index <= $this->headerRowIndex) {
                continue;
            }

            $this->rowNumber++;
            $rawRowData = $this->mapRowToHeaders($row, $headerMap);
            
            $rowData = $this->normalizeRow($rawRowData);

            if ($this->shouldSkipRow($rowData)) {
                continue;
            }

            $this->batch->total_rows++;

            try {
                DB::transaction(function () use ($rowData) {
                    $this->beforeImport($rowData);

                    $validationErrors = $this->validateRow($rowData);
                    if (!empty($validationErrors)) {
                        foreach ($validationErrors as $error) {
                            $this->batch->addError($this->rowNumber, $error, $rowData);
                        }

                        return;
                    }

                    $transformedData = $this->transformRow($rowData);
                    if ($transformedData === false) {
                        return;
                    }

                    $result = $this->importRow($transformedData, $rowData);

                    if ($result) {
                        $this->batch->imported_rows++;
                    }

                    $this->afterImport($rowData, $result);
                });
            } catch (\Exception $e) {
                $this->batch->addError($this->rowNumber, $e->getMessage(), $rowData);
            }
        }

        $this->batch->save();
    }

    /**
     * Find the best candidate for header row.
     */
    protected function findHeaderRow(Collection $rows): array
    {
        $template = app(BulkImportTemplateFactory::class)
            ->getTemplateDefinition($this->batch->module, $this->batch->form_type);
        
        $expectedHeaders = $template['headers'] ?? [];
        if (empty($expectedHeaders)) {
            return [];
        }

        $bestMatchRow = -1;
        $maxMatches = 0;
        $bestMap = [];

        // Scan first 20 rows
        foreach ($rows->take(20) as $index => $row) {
            $row = $row instanceof Collection ? $row->toArray() : (array) $row;
            $matches = 0;
            $currentMap = [];

            foreach ($row as $colIndex => $value) {
                if (empty($value)) continue;

                $normalizedValue = $this->normalizeHeaderName((string)$value);
                foreach ($expectedHeaders as $expected) {
                    $normalizedExpected = $this->normalizeHeaderName($expected);
                    if ($normalizedValue === $normalizedExpected || str_contains($normalizedValue, $normalizedExpected)) {
                        $matches++;
                        $currentMap[$colIndex] = $normalizedExpected;
                        break;
                    }
                }
            }

            if ($matches > $maxMatches) {
                $maxMatches = $matches;
                $bestMatchRow = $index;
                $bestMap = $currentMap;
            }

            // If we match more than 50% of headers, we assume this is the one
            if ($matches >= count($expectedHeaders) * 0.5) {
                break; 
            }
        }

        if ($maxMatches > 0) {
            $this->headerRowIndex = $bestMatchRow;
            
            // Re-map the entire row to get all headers, not just expected ones
            $bestRow = $rows->get($bestMatchRow);
            $bestRow = $bestRow instanceof Collection ? $bestRow->toArray() : (array)$bestRow;
            $fullMap = [];
            foreach ($bestRow as $colIndex => $value) {
                if (empty($value)) continue;
                
                $normalizedValue = $this->normalizeHeaderName((string)$value);
                
                // If it was one of our expected headers, use the expected name
                $matched = false;
                foreach ($expectedHeaders as $expected) {
                    $normalizedExpected = $this->normalizeHeaderName($expected);
                    if ($normalizedValue === $normalizedExpected || str_contains($normalizedValue, $normalizedExpected)) {
                        $fullMap[$colIndex] = $normalizedExpected;
                        $matched = true;
                        break;
                    }
                }
                
                // Otherwise use the normalized value from the cell
                if (!$matched) {
                    $fullMap[$colIndex] = $normalizedValue;
                }
            }
            return $fullMap;
        }

        return [];
    }

    /**
     * Map a numeric row to headers based on detected map.
     */
    protected function mapRowToHeaders($row, array $headerMap): array
    {
        $row = $row instanceof Collection ? $row->toArray() : (array) $row;
        $mapped = [];

        foreach ($headerMap as $colIndex => $headerName) {
            $mapped[$headerName] = $row[$colIndex] ?? null;
        }

        return $mapped;
    }

    /**
     * Get success count (legacy support).
     */
    public function getSuccessCount(): int
    {
        return $this->batch->imported_rows;
    }

    /**
     * Get error count (legacy support).
     */
    public function getErrorCount(): int
    {
        return $this->batch->error_rows;
    }

    /**
     * Get errors (legacy support).
     */
    public function getErrors(): array
    {
        return array_map(function($e) {
            return "Row {$e['row']}: {$e['message']}";
        }, $this->batch->errors_json ?? []);
    }

    /**
     * Helper: Map headers from a raw row.
     */
    protected function mapHeadersFromRow($row): array
    {
        $row = $row instanceof Collection ? $row->toArray() : (array) $row;
        $map = [];
        foreach ($row as $index => $value) {
            if ($value) {
                $map[$index] = $this->normalizeHeaderName((string)$value);
            }
        }
        return $map;
    }

    /**
     * Hook called when a single-value row is detected (potential category/header).
     */
    protected function onCategoryDetected(string $category): void
    {
        // Override in subclass
    }

    /**
     * Hook to refine header map after detection. Override in subclass.
     */
    protected function afterHeaderRowDetected(array &$headerMap, Collection $rows): void
    {
        // Override in subclass
    }

    /**
     * Hook before import processing begins. Override in subclass if needed.
     */
    protected function beforeImport(array $row): void
    {
        // Override in subclass
    }

    /**
     * Validate a single row. Return array of error messages (empty if valid).
     */
    abstract protected function validateRow(array $row): array;

    /**
     * Transform row data before import. Return false to skip row.
     */
    abstract protected function transformRow(array $row): mixed;

    /**
     * Import the transformed row. Return true if successful.
     */
    abstract protected function importRow(array $transformedData, array $originalRow): bool;

    /**
     * Hook after import processing. Override in subclass if needed.
     */
    protected function afterImport(array $row, bool $success): void
    {
        // Override in subclass
    }

    /**
     * Callback for failed rows (failures).
     */
    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $this->batch->addError(
                $failure->row(),
                $failure->errors()[0] ?? 'Unknown error',
                $failure->values()
            );
        }
    }

    protected function recordUpsert(?string $identifier, string $action = 'inserted'): void
    {
        $this->batch->addUpsertedRecord((string)$identifier, $action);
    }

    /**
     * Helper: Get unique identifier for a model by code or ID.
     */
    protected function getUniqueIdentifier(string $modelClass, string $identifierField, string $identifierValue, string $companyField = 'company_id')
    {
        $query = $modelClass::query();
        
        if ($modelClass::hasCompanyScoping()) {
            $query->where($companyField, $this->batch->company_id);
        }

        return $query->where($identifierField, $identifierValue)->first();
    }

    /**
     * Helper: Check if model class uses company scoping.
     */
    protected function modelHasCompanyScoping(string $modelClass): bool
    {
        $instance = new $modelClass();
        return isset($instance->company_id);
    }

    /**
     * Helper: Get value from array with dot notation support.
     */
    protected function getValue(array $data, string $key, $default = null)
    {
        return data_get($data, $key, $default);
    }

    /**
     * Helper: Get value from row by trying multiple possible column names.
     */
    protected function fuzzyGet(array $row, array $possibilities, $default = null)
    {
        foreach ($possibilities as $possibility) {
            $normalizedPossibility = $this->normalizeHeaderName($possibility);
            if (array_key_exists($normalizedPossibility, $row)) {
                return $row[$normalizedPossibility];
            }
        }
        return $default;
    }

    /**
     * Helper: Check if row has any of the possible column names.
     */
    protected function hasFuzzy(array $row, array $possibilities): bool
    {
        foreach ($possibilities as $possibility) {
            $normalizedPossibility = $this->normalizeHeaderName($possibility);
            if (array_key_exists($normalizedPossibility, $row)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper: Check if relationship exists.
     */
    protected function relationshipExists(string $modelClass, string $field, $value): bool
    {
        $query = $modelClass::query();
        
        if (method_exists($modelClass, 'hasCompanyScoping') && $modelClass::hasCompanyScoping()) {
            $query->where('company_id', $this->batch->company_id);
        }

        return $query->where($field, $value)->exists();
    }

    /**
     * Helper: Get relationship by field value.
     */
    protected function getRelatedRecord(string $modelClass, string $field, $value)
    {
        $query = $modelClass::query();
        
        if (method_exists($modelClass, 'hasCompanyScoping') && $modelClass::hasCompanyScoping()) {
            $query->where('company_id', $this->batch->company_id);
        }

        return $query->where($field, $value)->first();
    }

    /**
     * Helper: Create or update a model (upsert).
     */
    protected function upsert(string $modelClass, array $data, string $uniqueField): bool
    {
        try {
            $uniqueValue = $data[$uniqueField] ?? null;
            
            if (!$uniqueValue) {
                throw new \Exception("Unique field '{$uniqueField}' not provided");
            }

            $record = $modelClass::where($uniqueField, $uniqueValue)->first();
            $action = 'inserted';

            if ($record) {
                $record->update($data);
                $action = 'updated';
            } else {
                $record = $modelClass::create($data);
            }

            $this->recordUpsert($data[$uniqueField] ?? 'unknown', $action);
            $this->batch->imported_rows++;

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Upsert failed: {$e->getMessage()}");
        }
    }

    /**
     * Helper: Validate required fields.
     */
    protected function validateRequired(array $row, array $requiredFields): array
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field] ?? null)) {
                $errors[] = "Required field '{$field}' is empty";
            }
        }

        return $errors;
    }

    /**
     * Normalize spreadsheet row keys and values.
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            if (is_string($key)) {
                $normalizedKey = str_replace("\xA0", ' ', $key); // Handle Non-breaking spaces
                $normalizedKey = strtolower(trim($normalizedKey));
            } else {
                $normalizedKey = (string) $key;
            }

            $normalizedKey = preg_replace('/\*/', '', $normalizedKey);
            $normalizedKey = preg_replace('/\s+/', '_', $normalizedKey);
            $normalizedKey = preg_replace('/[^a-z0-9_]/', '', (string) $normalizedKey);

            if (is_string($value)) {
                $value = str_replace("\xA0", ' ', $value);
                $value = trim($value);
            }

            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    /**
     * Ignore empty and template metadata rows.
     */
    protected function shouldSkipRow(array $row): bool
    {
        $values = array_values($row);

        // Skip fully empty rows.
        $nonEmpty = array_filter($values, static function ($value) {
            if (is_null($value)) {
                return false;
            }

            if (is_string($value) && trim($value) === '') {
                return false;
            }

            return true;
        });

        // Skip rows with only 1 value (usually titles or decorative cells)
        if (count($nonEmpty) === 1) {
            $categoryValue = reset($nonEmpty);
            if (is_string($categoryValue)) {
                $this->onCategoryDetected(trim($categoryValue));
            }
            return true;
        }

        if ($this->matchesTemplateExampleRow($row)) {
            return true;
        }

        // Skip rows containing template instruction text.
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $text = strtolower(trim($value));

            if (
                str_contains($text, 'instructions:')
                || str_contains($text, 'fields marked with')
                || str_contains($text, 'save the file and upload')
                || str_contains($text, 'do not modify headers')
                || str_contains($text, 'fill in the data below')
                || str_contains($text, 'report title')
                || str_contains($text, 'confidential')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the row exactly matches one of the template example rows.
     */
    protected function matchesTemplateExampleRow(array $row): bool
    {
        $definition = app(BulkImportTemplateFactory::class)
            ->getTemplateDefinition($this->batch->module, $this->batch->form_type);

        $headers = $definition['headers'] ?? [];
        $examples = $definition['examples'] ?? [];

        if ($headers === [] || $examples === []) {
            return false;
        }

        $normalizedRow = [];
        foreach ($headers as $index => $header) {
            $normalizedHeader = $this->normalizeHeaderName($header);
            $normalizedRow[$normalizedHeader] = $this->normalizeCellValue($row[$normalizedHeader] ?? null);

            if (!array_key_exists($index, $row)) {
                continue;
            }
        }

        foreach ($examples as $exampleRow) {
            $exampleSignature = [];

            foreach ($headers as $index => $header) {
                $normalizedHeader = $this->normalizeHeaderName($header);
                $exampleSignature[$normalizedHeader] = $this->normalizeCellValue($exampleRow[$index] ?? null);
            }

            if ($this->rowsMatch($normalizedRow, $exampleSignature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a header name to the same shape used by import rows.
     */
    protected function normalizeHeaderName(string $header): string
    {
        $header = str_replace("\xA0", ' ', $header); // Handle Non-breaking spaces
        $header = strtolower(trim($header));
        $header = str_replace('*', '', $header);
        $header = preg_replace('/\s+/', '_', $header);
        $header = preg_replace('/[^a-z0-9_]/', '', $header);

        return $header ?? '';
    }

    /**
     * Normalize a cell value for comparison.
     */
    protected function normalizeCellValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value) && !is_string($value)) {
            return (string) $value;
        }

        return trim((string) $value);
    }

    /**
     * Compare two normalized rows.
     */
    protected function rowsMatch(array $left, array $right): bool
    {
        foreach ($right as $key => $value) {
            if (($left[$key] ?? '') !== $value) {
                return false;
            }
        }

        return true;
    }
}
