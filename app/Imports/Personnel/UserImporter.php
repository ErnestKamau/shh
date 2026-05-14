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
        $headerList = implode(', ', array_slice($this->detectedHeaders, 0, 15));

        $hasFirstName = $this->hasFuzzy($row, ['first_name', 'fname', 'given_name', 'first_names', 'names', 'firstname']);
        $hasLastName = $this->hasFuzzy($row, ['last_name', 'lname', 'surname', 'family_name', 'lastname']);
        $hasFullName = $this->hasFuzzy($row, ['name', 'full_name', 'employee_name', 'person_name', 'staff_name', 'employee', 'staff', 'user_name', 'user']);

        if (!$hasFirstName && !$hasFullName) {
            $errors[] = "First name or Full name is required. (Found headers: {$headerList})";
        }

        if (!$this->hasFuzzy($row, ['email', 'email_address', 'e-mail', 'official_email', 'work_email', 'mail', 'emailaddress', 'user_id', 'login', 'username', 'id_number'])) {
            $errors[] = "Email is required. (Found headers: {$headerList})";
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $firstName = $this->fuzzyGet($row, ['first_name', 'fname', 'given_name', 'first_names', 'names', 'firstname']);
        $lastName = $this->fuzzyGet($row, ['last_name', 'lname', 'surname', 'family_name', 'lastname']);
        $fullName = $this->fuzzyGet($row, ['name', 'full_name', 'employee_name', 'person_name', 'staff_name', 'employee', 'staff', 'user_name', 'user']);

        if (empty($firstName) && !empty($fullName)) {
            $parts = explode(' ', trim((string)$fullName));
            $firstName = $parts[0];
            $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
        }

        $email = $this->fuzzyGet($row, ['email', 'email_address', 'e-mail', 'official_email', 'work_email', 'mail', 'emailaddress', 'user_id', 'login', 'username', 'id_number']);
        $passwordRaw = $this->fuzzyGet($row, ['password', 'pass', 'pwd'], Str::random(12));

        $zoneCode = $this->fuzzyGet($row, ['zone_code', 'zone', 'location_code', 'location', 'site', 'branch']);
        $zone = Zone::where(function($q) use ($zoneCode) {
                $q->where('code', (string)$zoneCode)->orWhere('name', 'like', "%$zoneCode%");
            })
            ->where('company_id', $this->batch->company_id)
            ->first();

        $deptCode = $this->fuzzyGet($row, ['department_code', 'department', 'dept', 'unit', 'section']);
        $department = InventoryDepartment::where(function($q) use ($deptCode) {
                $q->where('name', (string)$deptCode)->orWhere('name', 'like', "%$deptCode%");
            })
            ->where('module', 'organizational')
            ->first();

        return [
            'name' => trim($firstName . ' ' . $lastName),
            'email' => $email,
            'password' => Hash::make($passwordRaw),
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
