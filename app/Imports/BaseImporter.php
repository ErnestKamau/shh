<?php

namespace App\Imports;

use App\Factories\BulkImportTemplateFactory;
use App\Models\BulkImportBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

abstract class BaseImporter implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use Importable;

    protected BulkImportBatch $batch;
    protected int $rowNumber = 1;
    protected array $errors = [];
    protected array $detectedHeaders = [];

    /**
     * Constructor.
     */
    public function __construct(BulkImportBatch $batch)
    {
        $this->batch = $batch;
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
     * Collection callback.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowNumber++;
            $rawRowData = $row instanceof Collection ? $row->toArray() : (array) $row;
            
            if (empty($this->detectedHeaders)) {
                $this->detectedHeaders = array_keys($rawRowData);
            }

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

    /**
     * Record an upserted record.
     */
    protected function recordUpsert(string $identifier, string $action = 'inserted'): void
    {
        $this->batch->addUpsertedRecord($identifier, $action);
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
        if (count($nonEmpty) <= 1) {
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
