<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleTypeCategory;

class SampleTypeCategoryImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        $name = trim((string) $this->fuzzyGet($row, [
            'category_name',
            'sample_type_category',
            'name',
            'category',
        ], ''));

        if ($name === '') {
            $errors[] = 'category_name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $name = trim((string) $this->fuzzyGet($row, [
            'category_name',
            'sample_type_category',
            'name',
            'category',
        ], ''));
        $active = $this->fuzzyGet($row, ['active', 'is_active', 'status'], 1);

        return [
            'sample_type_category' => $name,
            'active' => ! in_array(strtolower((string) $active), ['0', 'no', 'false', 'off', ''], true),
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        SampleTypeCategory::query()->updateOrCreate(
            ['sample_type_category' => $transformedData['sample_type_category']],
            ['active' => $transformedData['active']]
        );

        $this->recordUpsert($transformedData['sample_type_category'], 'inserted');

        return true;
    }

    protected function deleteRow(array $originalRow): bool
    {
        $name = trim((string) $this->fuzzyGet($originalRow, [
            'category_name',
            'sample_type_category',
            'name',
            'category',
        ], ''));

        if ($name === '') {
            $this->batch->addError($this->rowNumber, 'category_name is required to delete.', $originalRow);

            return false;
        }

        $category = SampleTypeCategory::query()
            ->whereRaw('LOWER(sample_type_category) = ?', [strtolower($name)])
            ->first();

        if ($category === null) {
            $this->batch->addWarning($this->rowNumber, 'Sample type category not found for delete.', $originalRow);

            return false;
        }

        $category->active = 0;
        $category->save();

        return true;
    }
}
