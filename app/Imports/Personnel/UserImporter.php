<?php

namespace App\Imports\Personnel;

use App\Imports\BaseImporter;
use App\InventoryDepartment;
use App\InventoryLocation;
use App\Lab;
use App\ModulePreConfigs;
use App\User;
use App\UserLabRelation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserImporter extends BaseImporter
{
    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch);
    }

    protected function onSheetLoaded(string $title): void
    {
        //
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

        if (empty($firstName) && ! empty($fullName)) {
            $parts = explode(' ', trim((string) $fullName));
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

        if (empty($fullName)) {
            $fullName = trim(($firstName ?? '').' '.($middleName ?? '').' '.($lastName ?? ''));
        }

        if (empty($fullName)) {
            $fullName = 'Imported User';
        }
        if (empty($firstName)) {
            $firstName = 'User';
        }
        if (empty($lastName)) {
            $lastName = 'User';
        }

        $email = $this->fuzzyGet($row, ['email', 'email_address', 'e-mail', 'official_email', 'work_email', 'mail', 'emailaddress', 'user_id', 'login', 'username', 'id_number']);
        $passwordRaw = Str::random(12);

        $deptName = $this->fuzzyGet($row, ['department_name', 'department', 'dept', 'unit', 'section']);
        $department = InventoryDepartment::where(function ($q) use ($deptName) {
            $q->where('name', (string) $deptName)->orWhere('name', 'like', "%$deptName%");
        })
            ->where('company_id', $this->batch->company_id)
            ->where('module', 'organizational')
            ->first();

        $positionName = $this->fuzzyGet($row, ['position', 'job_title', 'designation', 'role_name']);
        $position = null;
        if ($positionName) {
            $position = ModulePreConfigs::where('type', 'Job Description')
                ->where(function ($q) use ($positionName) {
                    $q->where('name', (string) $positionName)
                        ->orWhere('name', 'like', "%$positionName%");
                })
                ->first();
        }

        $phone = $this->fuzzyGet($row, ['phone', 'telephone', 'phone_number', 'contact_number', 'mobile', 'cell']);
        $gender = $this->fuzzyGet($row, ['gender', 'sex']);
        $designation = $this->fuzzyGet($row, ['designation', 'job_description', 'designation_name']);
        $dateOfBirth = $this->fuzzyGet($row, ['date_of_birth', 'dob', 'birth_date']);
        $idNumber = $this->fuzzyGet($row, ['id_number', 'national_id', 'passport_number', 'national_id_number', 'nida']);

        $labName = $this->fuzzyGet($row, ['lab_name', 'lab', 'laboratory']);
        $labCode = $this->fuzzyGet($row, ['lab_code']);
        $resolvedLabId = null;
        if ($labName || $labCode) {
            $lab = Lab::where(function ($q) use ($labName, $labCode) {
                if ($labCode) {
                    $q->where('code', $labCode);
                }
                if ($labName) {
                    $q->orWhere('name', $labName)->orWhere('name', 'like', "%$labName%");
                }
            })->where('company_id', $this->batch->company_id)->first();
            $resolvedLabId = $lab?->id;
        }

        $locationId = null;
        try {
            if ($this->batch->user?->location_id) {
                $locationId = $this->batch->user->location_id;
            }
        } catch (\Throwable $t) {
        }
        if (! $locationId) {
            try {
                if (auth()->check() && auth()->user()->location_id) {
                    $locationId = auth()->user()->location_id;
                }
            } catch (\Throwable $t) {
            }
        }
        if (! $locationId) {
            try {
                if (function_exists('getCurrentUserLocation')) {
                    $locationId = getCurrentUserLocation()?->id;
                }
            } catch (\Throwable $t) {
            }
        }
        if (! $locationId) {
            try {
                $locationId = InventoryLocation::first()?->id;
            } catch (\Throwable $t) {
            }
        }

        return [
            'name' => $fullName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($passwordRaw),
            'department_id' => $department?->id,
            'position' => $position?->id,
            'company_id' => $this->batch->company_id,
            'active' => 1,
            'email_verified_at' => now(),
            'phone' => $phone,
            'gender' => $gender,
            'designation' => $designation ?: ($position?->name ?? null),
            'date_of_birth' => ! empty($dateOfBirth) ? \Carbon\Carbon::parse($dateOfBirth)->toDateString() : null,
            'id_number' => $idNumber,
            'location_id' => $locationId,
            '_resolved_lab_id' => $resolvedLabId,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $resolvedLabId = $transformedData['_resolved_lab_id'] ?? null;
            unset($transformedData['_resolved_lab_id']);

            $user = User::updateOrCreate(
                ['email' => $transformedData['email']],
                $transformedData
            );

            if (! empty($resolvedLabId)) {
                UserLabRelation::updateOrCreate(
                    ['user_id' => $user->id, 'lab_id' => $resolvedLabId]
                );
            }

            $this->recordUpsert($transformedData['email'], 'inserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import user: {$e->getMessage()}");
        }
    }
}
