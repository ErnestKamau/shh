<?php

namespace App\Imports\Lab;

use App\AnalysisMethod;
use App\Imports\BaseImporter;
use App\Services\Lab\MethodConfigurationResolver;
use Illuminate\Support\Collection;

class AnalysisMethodImporter extends BaseImporter
{
    protected bool $amspecFormat = false;

    /** @var array<string, true> */
    protected array $importedReferenceNames = [];

    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch, $selectedZoneId);

        app(MethodConfigurationResolver::class)->ensurePointerConfigurations();
    }

    /**
     * @param  array<int, string>  $headerMap
     */
    protected function afterHeaderRowDetected(array &$headerMap, Collection $rows): void
    {
        $headerRow = $rows->get($this->headerRowIndex);
        $headerRow = $headerRow instanceof Collection ? $headerRow->toArray() : (array) $headerRow;

        foreach ($headerRow as $colIndex => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $canonical = $this->canonicalAmspecHeader((string) $value);
            if ($canonical !== null) {
                $headerMap[$colIndex] = $canonical;
            }
        }

        $canonicalValues = array_values($headerMap);
        $this->amspecFormat = in_array('reference_method', $canonicalValues, true)
            && in_array('test_method_sop', $canonicalValues, true);
    }

    /**
     * Prefer AmSpec Parameters workbooks when Reference Method + Test Method columns are present.
     *
     * @return array<int, string>
     */
    protected function findHeaderRow(Collection $rows): array
    {
        foreach ($rows->take(50) as $index => $row) {
            $row = $row instanceof Collection ? $row->toArray() : (array) $row;
            $map = [];

            foreach ($row as $colIndex => $value) {
                if ($value === null || trim((string) $value) === '') {
                    continue;
                }

                $canonical = $this->canonicalAmspecHeader((string) $value);
                if ($canonical !== null) {
                    $map[$colIndex] = $canonical;
                }
            }

            $values = array_values($map);
            if (in_array('reference_method', $values, true) && in_array('test_method_sop', $values, true)) {
                $this->headerRowIndex = $index;
                $this->amspecFormat = true;

                return $map;
            }
        }

        return parent::findHeaderRow($rows);
    }

    protected function canonicalAmspecHeader(string $header): ?string
    {
        $normalized = strtolower(trim(str_replace("\xA0", ' ', $header)));

        if (str_contains($normalized, 'reference method')) {
            return 'reference_method';
        }

        if (str_contains($normalized, 'test method')) {
            return 'test_method_sop';
        }

        if (str_contains($normalized, 'method version')) {
            return 'method_version';
        }

        return null;
    }

    protected function validateRow(array $row): array
    {
        $row = $this->normalizeImporterRowKeys($row);
        $errors = [];

        if ($this->amspecFormat) {
            $reference = $this->normalizeMethodText($this->firstFilled($row, ['reference_method']));
            $sop = $this->normalizeMethodText($this->firstFilled($row, ['test_method_sop']));

            if ($reference === null && $sop === null) {
                $errors[] = 'Reference Method or Test Method (SOP) is required';
            }

            return $errors;
        }

        if ($this->firstFilled($row, ['method_name', 'name']) === null) {
            $errors[] = 'Method Name is required';
        }

        if ($this->firstFilled($row, ['method_code', 'code']) === null) {
            $errors[] = 'Method Code is required';
        }

        if ($this->normalizeCategory($this->firstFilled($row, ['method_category', 'category'])) === null) {
            $errors[] = 'Method Category is required (Reference Method, Laboratory Test Method, or Sampling Method)';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $row = $this->normalizeImporterRowKeys($row);

        if ($this->amspecFormat) {
            return [
                'mode' => 'amspec',
                'reference_method' => $this->normalizeMethodText($this->firstFilled($row, ['reference_method'])),
                'test_method_sop' => $this->normalizeMethodText($this->firstFilled($row, ['test_method_sop'])),
                'method_version' => $this->normalizeMethodText($this->firstFilled($row, ['method_version'])),
            ];
        }

        $name = $this->normalizeMethodText($this->firstFilled($row, ['method_name', 'name']));
        $code = $this->normalizeMethodText($this->firstFilled($row, ['method_code', 'code'])) ?? $name;

        return [
            'mode' => 'template',
            'name' => $name,
            'code' => $code,
            'description' => $this->normalizeMethodText($this->firstFilled($row, ['description'])) ?? $name,
            'category' => $this->normalizeCategory($this->firstFilled($row, ['method_category', 'category'])),
            'reference_method' => $this->normalizeMethodText($this->firstFilled($row, ['reference_method'])),
            'method_version' => $this->normalizeMethodText($this->firstFilled($row, ['method_version'])),
            'active' => $this->parseBooleanCell($row['active'] ?? null, 1),
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        if (($transformedData['mode'] ?? '') === 'amspec') {
            return $this->importAmspecRow($transformedData);
        }

        return $this->importTemplateRow($transformedData);
    }

    /**
     * @param  array<string, mixed>  $transformedData
     */
    protected function importAmspecRow(array $transformedData): bool
    {
        $imported = false;
        $referenceName = $transformedData['reference_method'] ?? null;
        $sopName = $transformedData['test_method_sop'] ?? null;
        $version = $transformedData['method_version'] ?? null;

        if ($referenceName !== null) {
            $reference = $this->upsertMethod(
                name: $referenceName,
                code: $referenceName,
                description: $referenceName,
                category: 'reference',
                referenceMethodName: null,
                active: true,
                version: null,
            );

            if ($reference) {
                $imported = true;
            }
        }

        if ($sopName !== null) {
            $ltm = $this->upsertMethod(
                name: $sopName,
                code: $sopName,
                description: $this->descriptionWithVersion($sopName, $version),
                category: 'ltm',
                referenceMethodName: $referenceName,
                active: true,
                version: $version,
            );

            if ($ltm) {
                $imported = true;
            }
        }

        return $imported;
    }

    /**
     * @param  array<string, mixed>  $transformedData
     */
    protected function importTemplateRow(array $transformedData): bool
    {
        $method = $this->upsertMethod(
            name: (string) $transformedData['name'],
            code: (string) $transformedData['code'],
            description: $this->descriptionWithVersion(
                (string) ($transformedData['description'] ?? $transformedData['name']),
                $transformedData['method_version'] ?? null
            ),
            category: (string) $transformedData['category'],
            referenceMethodName: $transformedData['reference_method'] ?? null,
            active: (bool) ($transformedData['active'] ?? true),
            version: $transformedData['method_version'] ?? null,
        );

        return $method !== null;
    }

    protected function upsertMethod(
        string $name,
        string $code,
        string $description,
        string $category,
        ?string $referenceMethodName,
        bool $active,
        ?string $version,
    ): ?AnalysisMethod {
        $resolver = app(MethodConfigurationResolver::class);
        $methodTypeId = $resolver->resolveTypeIdForCategory($category);

        if ($methodTypeId === null) {
            $this->batch->addError($this->rowNumber, "Method type configuration missing for category: {$category}");

            return null;
        }

        $flags = $resolver->legacyFlagsForTypeId($methodTypeId);
        $referenceTypeId = null;

        if ($resolver->normalizeCategoryLabel($category) === 'ltm' && $referenceMethodName) {
            $referenceTypeId = $this->resolveReferenceMethodId($referenceMethodName);
        }

        $companyId = $this->batch->company_id;

        $method = AnalysisMethod::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($name, $code): void {
                $query->where('name', $name)->orWhere('code', $code);
            })
            ->first();

        $payload = [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'method_type_id' => $methodTypeId,
            'reference_type_id' => $referenceTypeId,
            'active' => $active ? 1 : 0,
            'is_ltm' => $flags['is_ltm'],
            'is_sampling_method' => $flags['is_sampling_method'],
            'company_id' => $companyId,
        ];

        if ($method) {
            $method->update($payload);
            $this->recordUpsert($code, 'updated');
        } else {
            $method = AnalysisMethod::create($payload);
            $this->recordUpsert($code, 'inserted');
        }

        if ($resolver->normalizeCategoryLabel($category) === 'reference') {
            $this->importedReferenceNames[strtolower($name)] = true;
        }

        return $method;
    }

    protected function resolveReferenceMethodId(string $referenceMethodName): ?string
    {
        $reference = AnalysisMethod::query()
            ->where('company_id', $this->batch->company_id)
            ->where(function ($query) use ($referenceMethodName): void {
                $query->where('name', $referenceMethodName)
                    ->orWhere('code', $referenceMethodName);
            })
            ->first();

        if ($reference) {
            return (string) $reference->id;
        }

        $created = $this->upsertMethod(
            name: $referenceMethodName,
            code: $referenceMethodName,
            description: $referenceMethodName,
            category: 'reference',
            referenceMethodName: null,
            active: true,
            version: null,
        );

        return $created ? (string) $created->id : null;
    }

    protected function descriptionWithVersion(string $description, ?string $version): string
    {
        $description = trim($description);
        $version = trim((string) $version);

        if ($version === '') {
            return $description;
        }

        if (stripos($description, $version) !== false) {
            return $description;
        }

        return trim($description.' '.$version);
    }

    protected function normalizeCategory(mixed $value): ?string
    {
        $resolver = app(MethodConfigurationResolver::class);
        $normalized = $resolver->normalizeCategoryLabel(is_string($value) ? $value : null);

        return in_array($normalized, ['reference', 'ltm', 'sampling'], true) ? $normalized : null;
    }

    protected function normalizeMethodText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', str_replace('_', ' ', $value)) ?? $value;

        return trim($value);
    }

    protected function normalizeImporterRowKeys(array $row): array
    {
        $normalizedRow = [];

        foreach ($row as $key => $value) {
            $cleanKey = $this->normalizeHeaderName((string) $key);
            if ($cleanKey === '') {
                continue;
            }

            $normalizedRow[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalizedRow;
    }

    protected function firstFilled(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = $row[$key];
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return is_string($value) ? trim($value) : $value;
        }

        return null;
    }

    protected function parseBooleanCell(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return (int) $value > 0 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'yes', 'y', 'true', 'on'], true)) {
            return 1;
        }

        if (in_array($normalized, ['0', 'no', 'n', 'false', 'off'], true)) {
            return 0;
        }

        return $default;
    }
}
