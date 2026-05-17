<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Directorate;
use App\Lab;
use App\User;
use App\Zone;

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

        if (empty($row['zone_name'] ?? null)) {
            $errors[] = 'Zone name is required';
        }

        if (empty($row['directorate_name'] ?? null)) {
            $errors[] = 'Directorate name is required';
        }

        if (!empty($row['manager_email'] ?? null) && !filter_var($row['manager_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Manager email must be a valid email address';
        }

        if (!empty($row['start_sample_no'] ?? null) && !is_numeric($row['start_sample_no'])) {
            $errors[] = 'Start sample no must be numeric';
        }

        if (!empty($row['internal_or_external'] ?? null) && !in_array(strtolower((string) $row['internal_or_external']), ['internal', 'external'], true)) {
            $errors[] = "internal_or_external must be either 'internal' or 'external'";
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $zoneName = trim((string) ($row['zone_name'] ?? ''));
        $directorateName = trim((string) ($row['directorate_name'] ?? ''));
        $inventoryLocationId = $this->resolveInventoryLocationId();

        $zone = $this->findZoneByNameOrCode($zoneName);
        if (!$zone && $zoneName !== '') {
            if (!$inventoryLocationId) {
                throw new \Exception('Unable to resolve a valid inventory location for zone creation');
            }

            $zone = Zone::create([
                'key' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $zoneName), 0, 12)) ?: strtoupper(substr(md5($zoneName), 0, 8)),
                'value' => $zoneName,
                'module' => 'organizational',
                'inventory_location_id' => $inventoryLocationId,
            ]);
        }

        $directorate = null;
        if ($directorateName !== '' && $zone?->id) {
            $directorate = Directorate::query()
                ->where('name', $directorateName)
                ->where('zone_id', $zone->id)
                ->first();

            if (!$directorate) {
                $directorate = Directorate::create([
                    'name' => $directorateName,
                    'zone_id' => $zone->id,
                    'code' => $this->generateUniqueDirectorateCode($directorateName, $zoneName),
                    'active' => 1,
                ]);
            }
        }

        $manager = null;
        if (!empty($row['manager_email'])) {
            $manager = User::where('email', $row['manager_email'])->first();
        }

        $isExternal = null;
        if (!empty($row['internal_or_external'])) {
            $isExternal = strtolower((string) $row['internal_or_external']) === 'external';
        }

        return [
            'code' => $row['lab_code'],
            'name' => $row['lab_name'] ?? 'Unnamed Lab',
            'address' => $row['address'] ?? null,
            'phone1' => $row['phone1'] ?? null,
            'zone_id' => $zone?->id,
            'directorate_id' => $directorate?->id,
            'manager_id' => $manager?->id,
            'start_sample_no' => !empty($row['start_sample_no']) ? (int) $row['start_sample_no'] : null,
            'is_external' => $isExternal,
            'company_id' => $this->batch->company_id,
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

    private function findZoneByNameOrCode(string $zoneInput): ?Zone
    {
        $zoneInput = trim($zoneInput);
        $inventoryLocationId = $this->resolveInventoryLocationId();

        if ($zoneInput === '') {
            return null;
        }

        if ($inventoryLocationId) {
            $zone = Zone::query()
                ->where(function ($query) use ($zoneInput) {
                    $query->where('value', $zoneInput)
                        ->orWhere('key', $zoneInput);
                })
                ->where('inventory_location_id', $inventoryLocationId)
                ->first();

            if ($zone) {
                return $zone;
            }
        }

        return Zone::query()
            ->where(function ($query) use ($zoneInput) {
                $query->where('value', $zoneInput)
                    ->orWhere('key', $zoneInput);
            })
            ->first();
    }

    private function resolveInventoryLocationId(): ?string
    {
        $location = function_exists('getCurrentUserLocation') ? getCurrentUserLocation() : null;

        if ($location && !empty($location->id)) {
            return (string) $location->id;
        }

        if (!empty($this->batch->user_id)) {
            $user = User::find($this->batch->user_id);
            if ($user && !empty($user->location_id)) {
                return (string) $user->location_id;
            }
        }

        if (!empty($this->batch->company_id)) {
            $companyLocation = \App\InventoryLocation::where('company_id', $this->batch->company_id)->first();
            if ($companyLocation) {
                return (string) $companyLocation->id;
            }
        }

        $anyLocation = \App\InventoryLocation::first();
        if ($anyLocation) {
            return (string) $anyLocation->id;
        }

        return null;
    }

    private function generateUniqueDirectorateCode(string $directorateName, string $zoneName): string
    {
        $baseName = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($directorateName . ' ' . $zoneName));
        $baseCode = substr($baseName ?: strtoupper(md5($directorateName . '|' . $zoneName)), 0, 20);
        $baseCode = rtrim($baseCode, '-');

        $candidate = $baseCode;
        $suffix = 1;

        while (Directorate::query()->where('code', $candidate)->exists()) {
            $suffixText = '-' . $suffix;
            $candidate = substr($baseCode, 0, max(1, 20 - strlen($suffixText))) . $suffixText;
            $suffix++;
        }

        return $candidate;
    }
}
