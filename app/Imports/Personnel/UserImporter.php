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
    protected ?string $selectedZoneId = null;

    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch);
        $this->selectedZoneId = $selectedZoneId;
    }

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

        $zoneCode = $this->fuzzyGet($row, ['zone_code', 'zone', 'location_code', 'location', 'site', 'branch']);
        $zoneName = $this->fuzzyGet($row, ['zone_name']);
        $zone = $this->defaultZone;

        if ($this->selectedZoneId) {
            $zone = Zone::find($this->selectedZoneId) ?: $this->defaultZone;
        } elseif ($zoneCode || $zoneName) {
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

        $phone = $this->fuzzyGet($row, ['phone', 'telephone', 'phone_number', 'contact_number', 'mobile', 'cell']);
        $gender = $this->fuzzyGet($row, ['gender', 'sex']);
        $designation = $this->fuzzyGet($row, ['designation', 'job_description', 'designation_name']);
        $dateOfBirth = $this->fuzzyGet($row, ['date_of_birth', 'dob', 'birth_date']);
        $idNumber = $this->fuzzyGet($row, ['id_number', 'national_id', 'passport_number', 'national_id_number', 'nida']);

        $directorateName = $this->fuzzyGet($row, ['directorate_name', 'directorate', 'dir']);
        $directorateCode = $this->fuzzyGet($row, ['directorate_code']);
        $resolvedDirId = null;
        if ($directorateName || $directorateCode) {
            $directorate = \App\Directorate::where(function($q) use ($directorateName, $directorateCode) {
                if ($directorateCode) {
                    $q->where('code', $directorateCode);
                }
                if ($directorateName) {
                    $q->orWhere('name', $directorateName)->orWhere('name', 'like', "%$directorateName%");
                }
            })->first();
            $resolvedDirId = $directorate?->id;
        }

        if (!$resolvedDirId && $zone) {
            $resolvedDirId = \App\Directorate::where('zone_id', $zone->id)->first()?->id;
        }

        $labName = $this->fuzzyGet($row, ['lab_name', 'lab', 'laboratory']);
        $labCode = $this->fuzzyGet($row, ['lab_code']);
        $resolvedLabId = null;
        if ($labName || $labCode) {
            $lab = \App\Lab::where(function($q) use ($labName, $labCode) {
                if ($labCode) {
                    $q->where('code', $labCode);
                }
                if ($labName) {
                    $q->orWhere('name', $labName)->orWhere('name', 'like', "%$labName%");
                }
            })->where('company_id', $this->batch->company_id)->first();
            $resolvedLabId = $lab?->id;
        }

        if (!$resolvedLabId && $resolvedDirId) {
            $resolvedLabId = \App\Lab::where('directorate_id', $resolvedDirId)->where('company_id', $this->batch->company_id)->first()?->id;
        }

        if (!$resolvedLabId && $zone) {
            $resolvedLabId = \App\Lab::where('zone_id', $zone->id)->where('company_id', $this->batch->company_id)->first()?->id;
        }

        $locationId = $zone?->inventory_location_id;
        if (!$locationId) {
            try {
                if ($this->batch->user?->location_id) {
                    $locationId = $this->batch->user->location_id;
                }
            } catch (\Throwable $t) {}
        }
        if (!$locationId) {
            try {
                if (auth()->check() && auth()->user()->location_id) {
                    $locationId = auth()->user()->location_id;
                }
            } catch (\Throwable $t) {}
        }
        if (!$locationId) {
            try {
                if (function_exists('getCurrentUserLocation')) {
                    $locationId = getCurrentUserLocation()?->id;
                }
            } catch (\Throwable $t) {}
        }
        if (!$locationId) {
            try {
                $locationId = \App\InventoryLocation::first()?->id;
            } catch (\Throwable $t) {}
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
            'phone' => $phone,
            'gender' => $gender,
            'designation' => $designation ?: ($position?->name ?? null),
            'date_of_birth' => !empty($dateOfBirth) ? \Carbon\Carbon::parse($dateOfBirth)->toDateString() : null,
            'id_number' => $idNumber,
            'location_id' => $locationId,
            '_resolved_directorate_id' => $resolvedDirId,
            '_resolved_lab_id' => $resolvedLabId,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $resolvedDirId = $transformedData['_resolved_directorate_id'] ?? null;
            $resolvedLabId = $transformedData['_resolved_lab_id'] ?? null;

            unset($transformedData['_resolved_directorate_id']);
            unset($transformedData['_resolved_lab_id']);

            $user = User::updateOrCreate(
                ['email' => $transformedData['email']],
                $transformedData
            );

            if (!empty($user->zone_id)) {
                \App\UserZoneRelation::updateOrCreate(
                    ['user_id' => $user->id, 'zone_id' => $user->zone_id]
                );
            }

            if (!empty($resolvedDirId)) {
                \App\UserDirectorateRelation::updateOrCreate(
                    ['user_id' => $user->id, 'directorate_id' => $resolvedDirId]
                );
            }

            if (!empty($resolvedLabId)) {
                \App\UserLabRelation::updateOrCreate(
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
