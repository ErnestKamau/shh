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
}
