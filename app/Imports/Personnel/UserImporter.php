<?php

namespace App\Imports\Personnel;

use App\Imports\BaseImporter;
use App\User;
use App\Zone;
use App\InventoryDepartment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['first_name'] ?? null)) {
            $errors[] = 'First name is required';
        }

        if (empty($row['last_name'] ?? null)) {
            $errors[] = 'Last name is required';
        }

        if (empty($row['email'] ?? null)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email format is invalid';
        }

        if (empty($row['password'] ?? null)) {
            $errors[] = 'Password is required';
        } elseif (strlen($row['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $zone = null;
        if (!empty($row['zone_code'])) {
            $zone = Zone::where('code', $row['zone_code'])->where('company_id', $this->batch->company_id)->first();
        }

        $department = null;
        if (!empty($row['department_code'])) {
            $department = InventoryDepartment::where('name', $row['department_code'])->where('module', 'organizational')->first();
        }

        return [
            'name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'email' => $row['email'],
            'password' => Hash::make($row['password']),
            'zone_id' => $zone?->id,
            'department_id' => $department?->id,
            'company_id' => $this->batch->company_id,
            'active' => 1,
            'email_verified_at' => now(),
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            User::updateOrCreate(
                ['email' => $transformedData['email']],
                $transformedData
            );

            $this->recordUpsert($transformedData['email'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import user: {$e->getMessage()}");
        }
    }
}
