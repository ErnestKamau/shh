<?php

namespace App\Imports\Personnel;

use App\Imports\BaseImporter;
use App\User;
use App\Zone;
use App\InventoryDepartment;
use App\ModulePreConfigs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserImporter extends BaseImporter
{
    protected ?Zone $defaultZone = null;

    protected function onSheetLoaded(string $title): void
    {
        // Try to find a zone that matches the sheet title
        $this->defaultZone = Zone::where(function($q) use ($title) {
                $q->where('value', 'like', "%$title%")
                  ->orWhere('key', 'like', "%$title%");
            })
            ->first();
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        $headerList = implode(', ', array_slice($this->detectedHeaders, 0, 15));

        $firstName = $this->fuzzyGet($row, ['first_name', 'fname', 'given_name', 'first_names', 'names', 'firstname']);
        $fullName = $this->fuzzyGet($row, ['name', 'full_name', 'employee_name', 'person_name', 'staff_name', 'employee', 'staff', 'user_name', 'user']);

        if (empty($firstName) && empty($fullName)) {
            $errors[] = "First name or Full name is required. (Found headers: {$headerList})";
        }

        if (empty($this->fuzzyGet($row, ['email', 'email_address', 'e-mail', 'official_email', 'work_email', 'mail', 'emailaddress', 'user_id', 'login', 'username', 'id_number']))) {
            $errors[] = "Email is required. (Found headers: {$headerList})";
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $firstName = $this->fuzzyGet($row, ['first_name', 'fname', 'given_name', 'firstname']);
        $middleName = $this->fuzzyGet($row, ['middle_name', 'mname', 'middlename']);
        $lastName = $this->fuzzyGet($row, ['last_name', 'lname', 'surname', 'family_name', 'lastname']);
        $fullName = $this->fuzzyGet($row, ['name', 'full_name', 'employee_name', 'person_name', 'staff_name', 'employee', 'staff', 'user_name', 'user']);

        // Logic for splitting full name if components are missing
        if (empty($firstName) && !empty($fullName)) {
            $parts = explode(' ', trim((string)$fullName));
            if (count($parts) === 1) {
                $firstName = $parts[0];
            } elseif (count($parts) === 2) {
                $firstName = $parts[0];
                $lastName = $parts[1];
            } else {
                $firstName = $parts[0];
                $middleName = $parts[1];
                $lastName = implode(' ', array_slice($parts, 2));
            }
        }

        // Logic for joining components to create a display name if only components are provided
        if (empty($fullName)) {
            $fullName = trim(($firstName ?? '') . ' ' . ($middleName ?? '') . ' ' . ($lastName ?? ''));
        }

        $email = $this->fuzzyGet($row, ['email', 'email_address', 'e-mail', 'official_email', 'work_email', 'mail', 'emailaddress', 'user_id', 'login', 'username', 'id_number']);
        $passwordRaw = Str::random(12);

        $zoneCode = $this->fuzzyGet($row, ['zone_code', 'zone', 'location_code', 'location', 'site', 'branch']);
        $zoneName = $this->fuzzyGet($row, ['zone_name']);
        $zone = $this->defaultZone;

        if ($zoneCode || $zoneName) {
            $zone = Zone::where(function($q) use ($zoneCode, $zoneName) {
                    if ($zoneCode) {
                        $q->where('key', (string)$zoneCode)->orWhere('value', 'like', "%$zoneCode%");
                    }
                    if ($zoneName) {
                        $q->orWhere('value', (string)$zoneName)->orWhere('value', 'like', "%$zoneName%");
                    }
                })
                ->first() ?: $this->defaultZone;
        }

        $deptName = $this->fuzzyGet($row, ['department_name', 'department', 'dept', 'unit', 'section']);
        $department = InventoryDepartment::where(function($q) use ($deptName) {
                $q->where('name', (string)$deptName)->orWhere('name', 'like', "%$deptName%");
            })
            ->where('company_id', $this->batch->company_id)
            ->where('module', 'organizational')
            ->first();

        $positionName = $this->fuzzyGet($row, ['position', 'job_title', 'designation', 'role_name']);
        $position = null;
        if ($positionName) {
            $position = ModulePreConfigs::where('type', 'Job Description')
                ->where(function($q) use ($positionName) {
                    $q->where('name', (string)$positionName)
                      ->orWhere('name', 'like', "%$positionName%");
                })
                ->first();
        }

        return [
            'name' => $fullName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($passwordRaw),
            'zone_id' => $zone?->id,
            'department_id' => $department?->id,
            'position' => $position?->id,
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
