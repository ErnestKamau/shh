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
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

abstract class BaseImporter implements
    ToCollection,
    SkipsOnFailure,
    WithMultipleSheets,
    SkipsUnknownSheets,
    WithTitle,
    WithEvents,
    WithColumnLimit,
    WithReadFilter
{
    use Importable;

    protected BulkImportBatch $batch;
    protected int $rowNumber = 1;
    protected array $errors = [];
    protected array $detectedHeaders = [];
    protected int $sheetCount = 1;
    protected string $sheetTitle = '';
    protected int $headerRowIndex = -1;
    protected static array $cachedTemplateDefinitions = [];
    protected ?string $selectedZoneId = null;

    /** @var array<int, string>|null */
    protected ?array $resolvedHeaderMap = null;

    protected bool $headersResolved = false;

    /**
     * Constructor.
     */
    public function __construct(?BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        set_time_limit(600); // 10 minutes for large files
        ini_set('memory_limit', '1024M'); // 1GB memory
        $this->selectedZoneId = $selectedZoneId;

        if ($batch) {
            $this->batch = $batch;
        } else {
            // Attempt to get company/user safely
            $companyId = '00000000-0000-0000-0000-000000000000';
            try {
                if (function_exists('getUserCompany')) {
                    $companyId = getUserCompany() ?: $companyId;
                }
            } catch (\Throwable $t) {}

            $userId = '00000000-0000-0000-0000-000000000000';
            try {
                if (auth()->check()) {
                    $userId = auth()->id() ?: $userId;
                }
            } catch (\Throwable $t) {}

            $this->batch = new BulkImportBatch([
                'module' => 'generic',
                'form_type' => 'generic',
                'status' => 'processing',
                'company_id' => $companyId,
                'user_id' => $userId,
                'imported_rows' => 0,
                'total_rows' => 0,
                'error_rows' => 0,
            ]);
            $this->batch->save();
        }
    }

    public function endColumn(): string
    {
        return 'AZ';
    }

    /**
     * Cap how many worksheet rows PhpSpreadsheet will materialize.
     * Prevents formatted-but-empty AmSpec-style ranges from exhausting memory.
     */
    protected function maxReadableRows(): int
    {
        return 5000;
    }

    public function readFilter(): IReadFilter
    {
        $endColumn = $this->endColumn();
        $maxRows = $this->maxReadableRows();

        return new class($endColumn, $maxRows) implements IReadFilter {
            public function __construct(
                private string $endColumn,
                private int $maxRows
            ) {
            }

            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                if ($row < 1 || $row > $this->maxRows) {
                    return false;
                }

                return Coordinate::columnIndexFromString($columnAddress)
                    <= Coordinate::columnIndexFromString($this->endColumn);
            }
        };
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

    protected bool $isSheetInstance = false;

    /**
     * Multiple Sheets Support
     */
    public function sheets(): array
    {
        // Prevent infinite recursion: if this is already a sheet instance, don't return more sheets.
        if ($this->isSheetInstance) {
            return [];
        }

        $sheets = [];
        for ($i = 0; $i < 5; $i++) {
            $sheet = clone $this;
            $sheet->isSheetInstance = true;
            $sheet->resolvedHeaderMap = null;
            $sheet->headersResolved = false;
            $sheet->headerRowIndex = -1;
            $sheets[$i] = $sheet;
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
                \Log::info("Bulk Import Started", ['module' => $this->batch->module, 'form' => $this->batch->form_type]);
                $this->sheetCount = $event->reader->getSheetCount();
            },
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetTitle = $event->sheet->getTitle();
                $this->resolvedHeaderMap = null;
                $this->headersResolved = false;
                $this->headerRowIndex = -1;
                \Log::info("Processing Sheet: " . $this->sheetTitle);
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
        try {
            \Log::info("Processing " . count($rows) . " rows in sheet " . $this->sheetTitle);

            if ($this->headersResolved && !empty($this->resolvedHeaderMap)) {
                $headerMap = $this->resolvedHeaderMap;
                // Subsequent chunks contain data rows only (no header row).
                $this->headerRowIndex = -1;
            } else {
                $headerMap = $this->findHeaderRow($rows);

                if (empty($headerMap)) {
                    \Log::warning("No header row detected in sheet " . $this->sheetTitle . ". Falling back to first row.");
                    $headerMap = $this->mapHeadersFromRow($rows->first() ?? []);
                    $this->headerRowIndex = 0;
                } else {
                    \Log::info("Header row detected at index " . $this->headerRowIndex);
                    $this->afterHeaderRowDetected($headerMap, $rows);
                }

                $this->resolvedHeaderMap = $headerMap;
                $this->headersResolved = true;
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

                // Check for primary unique key blank conditions
                $formType = strtolower($this->batch->form_type ?? '');
                $hasMissingPrimaryKey = false;

                $checkFilled = function ($key) use ($rowData) {
                    $val = $this->fuzzyGet($rowData, is_array($key) ? $key : [$key]);
                    return !is_null($val) && trim((string)$val) !== '';
                };

                if ($formType === 'pricelist') {
                    if (!$checkFilled('pricelist_code') || !$checkFilled('sample_type') || !$checkFilled('parameter')) {
                        $hasMissingPrimaryKey = true;
                    }
                } elseif ($formType === 'sample_condition') {
                    if (!$checkFilled('sample_type_code') || !$checkFilled('condition_name')) {
                        $hasMissingPrimaryKey = true;
                    }
                } elseif ($formType === 'analysis_elements') {
                    $hasTypeAndAnalyte = $checkFilled('analysis_type_code') && $checkFilled('analyte_code');
                    $hasParamAndMethod = $checkFilled('parameter') && $checkFilled('method');
                    if (!$hasTypeAndAnalyte && !$hasParamAndMethod) {
                        $hasMissingPrimaryKey = true;
                    }
                } elseif ($formType === 'analysis_type') {
                    $hasSampleType = $checkFilled([
                        'sample_type_code',
                        'sample_type_name',
                        'sample_type',
                        'matrix_code',
                        'matrix_name',
                        'matrix',
                    ]);
                    $hasAnalysisType = $checkFilled([
                        'analysis_type_code',
                        'analysis_type_name',
                        'analysis_type',
                        'code',
                        'name',
                        'at_code',
                        'at_name',
                    ]);
                    if (! $hasSampleType || ! $hasAnalysisType) {
                        $hasMissingPrimaryKey = true;
                    }
                } elseif ($formType === 'sample_type') {
                    $hasCode = $checkFilled(['code', 'id', 'sample_type_code', 'matrix_code']);
                    $hasName = $checkFilled(['name', 'title', 'sample_type_name', 'matrix_name', 'matrix', 'description']);
                    if (!$hasCode && !$hasName) {
                        $hasMissingPrimaryKey = true;
                    }
                } elseif ($formType === 'equipment') {
                    if (!$checkFilled([
                        'gcla_code',
                        'equipment_number',
                        'equipment_no',
                        'equipment_id',
                        'asset_number',
                        'code',
                    ])) {
                        $hasMissingPrimaryKey = true;
                    }
                } else {
                    $keys = $this->getPrimaryKeysForFormType($formType);
                    if (!empty($keys)) {
                        if (!$checkFilled($keys)) {
                            $hasMissingPrimaryKey = true;
                        }
                    }
                }

                if ($hasMissingPrimaryKey) {
                    continue; // Skip the row gracefully!
                }

                $this->batch->total_rows++;

                try {
                    DB::transaction(function () use ($rowData) {
                        $this->beforeImport($rowData);

                        $validationErrors = $this->validateRow($rowData);
                        if (!empty($validationErrors)) {
                            // Filter out "required" errors
                            $validationErrors = array_filter($validationErrors, function($err) {
                                $errLower = strtolower($err);
                                return !str_contains($errLower, 'required');
                            });
                        }
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
                } catch (\Throwable $e) {
                    \Log::error("Error processing row {$this->rowNumber}: " . $e->getMessage());
                    $this->batch->addError($this->rowNumber, $e->getMessage(), $rowData);
                    $this->onRowFailed($rowData, $e);
                }
            }

            $this->batch->save();
        } catch (\Throwable $e) {
            \Log::error("Critical Import Error in sheet " . $this->sheetTitle . ": " . $e->getMessage());
            if ($this->batch) {
                $this->batch->addError(0, "Sheet " . $this->sheetTitle . " failed: " . $e->getMessage());
                $this->batch->save();
            }
            throw $e;
        }
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

        // Scan first 50 rows
        foreach ($rows->take(50) as $index => $row) {
            $row = $row instanceof Collection ? $row->toArray() : (array) $row;
            $matches = 0;
            $currentMap = [];

            foreach ($row as $colIndex => $value) {
                if (empty($value)) continue;

                $normalizedValue = $this->normalizeHeaderName((string)$value);
                $matched = false;
                foreach ($expectedHeaders as $expected) {
                    $normalizedExpected = $this->normalizeHeaderName($expected);
                    if ($normalizedValue === $normalizedExpected) {
                        $matches++;
                        $currentMap[$colIndex] = $normalizedExpected;
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    foreach ($expectedHeaders as $expected) {
                        $normalizedExpected = $this->normalizeHeaderName($expected);
                        if (strlen($normalizedExpected) >= 4 && str_contains($normalizedValue, $normalizedExpected)) {
                            $matches++;
                            $currentMap[$colIndex] = $normalizedExpected;
                            break;
                        }
                    }
                }
            }

            if ($matches > $maxMatches) {
                $maxMatches = $matches;
                $bestMatchRow = $index;
                $bestMap = $currentMap;
            }

            // If we match at least 3 headers, we assume this might be the header row.
            // We keep scanning to find the row with the maximum number of matches.
            if ($matches >= 3) {
                // Potential match found
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
                
                // 1. Try exact matches first
                $matched = false;
                foreach ($expectedHeaders as $expected) {
                    $normalizedExpected = $this->normalizeHeaderName($expected);
                    if ($normalizedValue === $normalizedExpected) {
                        $fullMap[$colIndex] = $normalizedExpected;
                        $matched = true;
                        break;
                    }
                }
                
                // 2. Try partial matches only if no exact match found and string is long enough
                if (!$matched) {
                    foreach ($expectedHeaders as $expected) {
                        $normalizedExpected = $this->normalizeHeaderName($expected);
                        if (strlen($normalizedExpected) >= 4 && str_contains($normalizedValue, $normalizedExpected)) {
                            $fullMap[$colIndex] = $normalizedExpected;
                            $matched = true;
                            break;
                        }
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
     * Hook called when a row's transaction failed and was rolled back.
     * Override to invalidate any caches holding models created inside the rolled-back transaction.
     */
    protected function onRowFailed(array $row, \Throwable $exception): void
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
        return [];
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
                $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
                if ($cleaned !== false) {
                    $value = $cleaned;
                }
                $value = $this->sanitizeImportedString($value);
            }

            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    /**
     * Strip underscores from imported cell values (replace with spaces).
     */
    protected function sanitizeImportedString(string $value): string
    {
        $value = str_replace('_', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * Plain report-display text: no underscores, no scream-case.
     * Preserves mixed-case and technical codes (e.g. AN-PH, ISO-4833).
     */
    protected function plainReportDisplayValue(string $value): string
    {
        $value = $this->sanitizeImportedString($value);

        if ($value === '') {
            return $value;
        }

        // Multi-word or single-word ALL CAPS → Title Case
        if (preg_match('/^[A-Z0-9]+(?: [A-Z0-9]+)*$/', $value) === 1) {
            return \Illuminate\Support\Str::title(strtolower($value));
        }

        return $value;
    }

    /**
     * Build a stable technical code from a name without underscores (uppercase).
     */
    protected function resolveCodeFromName(string $name): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', ' ', $name) ?? '';
        $slug = preg_replace('/\s+/', ' ', $slug) ?? '';
        $slug = trim($slug);

        if ($slug === '') {
            return 'CODE-'.uniqid();
        }

        return strtoupper(substr($slug, 0, 100));
    }

    /**
     * Resolve one equipment id by optional number/code and/or name.
     * Creates a stub equipment record when nothing matches.
     */
    protected function resolveEquipmentId(?string $equipmentCode = null, ?string $equipmentName = null): ?string
    {
        $ids = $this->resolveEquipmentIds($equipmentCode, $equipmentName);

        return $ids[0] ?? null;
    }

    /**
     * Resolve one or more equipment ids from code/name cells.
     * Name cells may list multiple items separated by commas, pipes, or semicolons.
     *
     * @return list<string>
     */
    protected function resolveEquipmentIds(?string $equipmentCode = null, ?string $equipmentName = null): array
    {
        $code = trim((string) $equipmentCode);
        $name = trim((string) $equipmentName);

        if ($code === '' && $name === '') {
            return [];
        }

        if (! class_exists(\App\Models\Equipments\Equipment::class)) {
            return [];
        }

        $nameTokens = $this->splitImportedEquipmentNames($name !== '' ? $name : null);

        if ($nameTokens === [] && $code !== '') {
            $id = $this->findOrCreateEquipment($code, null);

            return $id !== null ? [$id] : [];
        }

        $resolved = [];
        $singleNamed = count($nameTokens) === 1;

        foreach ($nameTokens as $tokenName) {
            $tokenCode = ($singleNamed && $code !== '') ? $code : null;
            $id = $this->findOrCreateEquipment($tokenCode, $tokenName);
            if ($id !== null) {
                $resolved[$id] = $id;
            }
        }

        return array_values($resolved);
    }

    /**
     * @return list<string>
     */
    protected function splitImportedEquipmentNames(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $raw = trim($raw);

        // Equipment names/numbers often contain slashes (LC-MS/MS, AMS/M/INS/037).
        // Split only on explicit list separators — not "/".
        $tokens = preg_split('/[,|;]+/', $raw) ?: [];

        $normalized = [];
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token !== '') {
                $normalized[] = $token;
            }
        }

        return $normalized;
    }

    protected function findOrCreateEquipment(?string $equipmentCode, ?string $equipmentName): ?string
    {
        $code = trim((string) $equipmentCode);
        $name = trim((string) $equipmentName);

        if ($code === '' && $name === '') {
            return null;
        }

        $companyId = $this->batch->company_id ?? null;
        $query = \App\Models\Equipments\Equipment::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        if ($code !== '') {
            $equip = (clone $query)
                ->where(function ($builder) use ($code): void {
                    $builder->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($code)])
                        ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower($code)]);
                })
                ->first();

            if ($equip) {
                return (string) $equip->id;
            }
        }

        if ($name !== '') {
            $equip = (clone $query)
                ->where(function ($builder) use ($name): void {
                    $builder->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                        ->orWhereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($name)]);
                })
                ->first();

            if ($equip) {
                return (string) $equip->id;
            }
        }

        $displayName = $name !== '' ? $name : $code;
        $equipmentNumber = $code !== '' ? $code : $this->generateImportedEquipmentNumber($displayName);

        // Ensure unique equipment_number within company.
        $baseNumber = $equipmentNumber;
        $suffix = 1;
        while (
            \App\Models\Equipments\Equipment::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->whereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($equipmentNumber)])
                ->exists()
        ) {
            $equipmentNumber = $baseNumber.'-'.$suffix;
            $suffix++;
        }

        try {
            $equip = \App\Models\Equipments\Equipment::create([
                'name' => $displayName,
                'equipment_number' => $equipmentNumber,
                'description' => $displayName,
                'make' => 'Unknown',
                'model' => 'Unknown',
                'manufacturer' => 'Unknown',
                'date_purchased' => now()->toDateString(),
                'maintainance_days' => 365,
                'calibration_days' => 365,
                'status' => 'In Use',
                'condition' => 'Good',
                'active' => true,
                'is_disposal' => false,
                'picture' => \App\Models\Equipments\Equipment::defaultPicturePath(),
                'company_id' => $companyId,
            ]);

            return (string) $equip->id;
        } catch (\Throwable $e) {
            \Log::warning('Bulk import could not create equipment: '.$e->getMessage(), [
                'name' => $displayName,
                'equipment_number' => $equipmentNumber,
            ]);

            return null;
        }
    }

    protected function generateImportedEquipmentNumber(string $name): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', strtoupper($name)) ?? '';
        $slug = trim($slug, '-');
        $slug = substr($slug !== '' ? $slug : 'EQUIP', 0, 40);

        return 'IMP-'.$slug;
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
        $cacheKey = ($this->batch->module ?? 'gen') . '_' . ($this->batch->form_type ?? 'gen');
        
        if (!isset(self::$cachedTemplateDefinitions[$cacheKey])) {
            self::$cachedTemplateDefinitions[$cacheKey] = app(BulkImportTemplateFactory::class)
                ->getTemplateDefinition($this->batch->module, $this->batch->form_type);
        }
        
        $definition = self::$cachedTemplateDefinitions[$cacheKey];
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

    /**
     * Get the primary unique database keys required to insert/update a row for a given form type.
     */
    protected function getPrimaryKeysForFormType(string $formType): array
    {
        $map = [
            'pricelist' => ['pricelist_code', 'sample_type', 'parameter'],
            'analyte' => [],
            'lab' => ['lab_code'],
            'sample_type' => [],
            'analysis_type' => [],
            'analysis_elements' => ['analysis_type_code', 'analyte_code', 'parameter', 'method'],
            'standard' => [],
            'sample_condition' => ['sample_type_code', 'condition_name'],
            'lab_hierarchy' => [],
            'asset_type' => ['code'],
            'asset_location' => ['code'],
            'equipment' => ['gcla_code', 'equipment_number', 'equipment_no', 'code'],
            'department' => ['name'],
            'user' => ['email'],
            'customer' => ['customer_code'],
            'inventory' => ['name'],
        ];
        return $map[strtolower($formType)] ?? [];
    }
}
