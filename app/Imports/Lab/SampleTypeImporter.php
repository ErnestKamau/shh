<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleType;
use App\SampleTypeCategory;

class SampleTypeImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        $code = $this->fuzzyGet($row, ['code', 'id', 'sample_type_code', 'matrix_code']);
        $name = $this->fuzzyGet($row, ['name', 'title', 'sample_type_name', 'matrix_name', 'matrix', 'description']);
        $categoryName = trim((string) $this->fuzzyGet($row, [
            'category_name',
            'sample_type_category_name',
            'sample_type_category',
            'category',
        ], ''));

        if (empty($code) && empty($name)) {
            $errors[] = 'Either sample type code or name is required';
        }

        if ($categoryName === '') {
            $errors[] = 'category_name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = trim((string) $this->fuzzyGet($row, ['code', 'id', 'sample_type_code', 'matrix_code', 'parameter_code'], ''));
        $name = trim((string) $this->fuzzyGet($row, ['name', 'title', 'sample_type_name', 'matrix_name', 'matrix', 'description', 'parameter_name'], ''));
        $isAttachable = $this->fuzzyGet($row, ['is_results_attachable', 'attachable'], 0);
        $disposalCount = $this->fuzzyGet($row, ['disposal_count', 'disposal'], 0);
        $active = $this->fuzzyGet($row, ['active', 'is_active', 'status'], 1);
        $categoryName = trim((string) $this->fuzzyGet($row, [
            'category_name',
            'sample_type_category_name',
            'sample_type_category',
            'category',
        ], ''));

        return [
            'code' => $code ?: ($name ?: 'ST-'.uniqid()),
            'name' => $name ?: $code,
            'is_results_attachable' => ! in_array(strtolower((string) $isAttachable), ['0', 'no', 'false', 'off', ''], true),
            'disposal_count' => is_numeric($disposalCount) ? (int) $disposalCount : 0,
            'active' => ! in_array(strtolower((string) $active), ['0', 'no', 'false', 'off', ''], true),
            'category_name' => $categoryName,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $category = SampleTypeCategory::query()
            ->whereRaw('LOWER(sample_type_category) = ?', [strtolower($transformedData['category_name'])])
            ->first();

        if (! $category) {
            throw new \Exception("Sample type category not found: {$transformedData['category_name']}");
        }

        unset($transformedData['category_name']);
        $transformedData['sample_type_category'] = $category->id;

        SampleType::updateOrCreate(
            ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
            $transformedData
        );

        $this->recordUpsert($transformedData['code'], 'inserted');

        return true;
    }

    protected function deleteRow(array $originalRow): bool
    {
        $code = trim((string) $this->fuzzyGet($originalRow, ['code', 'id', 'sample_type_code', 'matrix_code'], ''));
        $name = trim((string) $this->fuzzyGet($originalRow, ['name', 'title', 'sample_type_name', 'matrix_name', 'matrix'], ''));

        $query = SampleType::query()->where('company_id', $this->batch->company_id);
        if ($code !== '') {
            $query->where('code', $code);
        } elseif ($name !== '') {
            $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)]);
        } else {
            $this->batch->addError($this->rowNumber, 'Sample type code or name is required to delete.', $originalRow);

            return false;
        }

        $type = $query->first();
        if ($type === null) {
            $this->batch->addWarning($this->rowNumber, 'Sample type not found for delete.', $originalRow);

            return false;
        }

        $type->active = 0;
        $type->save();

        return true;
    }
}
