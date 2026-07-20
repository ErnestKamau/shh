<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Lab;
use App\User;

class LabImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['lab_code'] ?? null)) {
            $errors[] = 'Lab code is required';
        }

        if (empty($row['lab_name'] ?? null)) {
            $errors[] = 'Lab name is required';
        }

        if (! empty($row['manager_email'] ?? null) && ! filter_var($row['manager_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Manager email must be a valid email address';
        }

        if (! empty($row['start_sample_no'] ?? null) && ! is_numeric($row['start_sample_no'])) {
            $errors[] = 'Start sample no must be numeric';
        }

        if (! empty($row['internal_or_external'] ?? null) && ! in_array(strtolower((string) $row['internal_or_external']), ['internal', 'external'], true)) {
            $errors[] = "internal_or_external must be either 'internal' or 'external'";
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $manager = null;
        if (! empty($row['manager_email'])) {
            $manager = User::where('email', $row['manager_email'])->first();
        }

        $isExternal = null;
        if (! empty($row['internal_or_external'])) {
            $isExternal = strtolower((string) $row['internal_or_external']) === 'external';
        }

        return [
            'code' => $row['lab_code'],
            'name' => $row['lab_name'] ?? 'Unnamed Lab',
            'address' => $row['address'] ?? null,
            'phone1' => $row['phone1'] ?? 'N/A',
            'manager_id' => $manager?->id,
            'start_sample_no' => ! empty($row['start_sample_no']) ? (string) $row['start_sample_no'] : null,
            'is_external' => $isExternal,
            'company_id' => $this->batch->company_id,
            'active' => true,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            Lab::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['code'], 'inserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import lab: {$e->getMessage()}");
        }
    }
}
