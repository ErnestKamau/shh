<?php

namespace App\Imports\CRM;

use App\Imports\BaseImporter;
use App\Models\CRM\CRMCustomer;
use App\Country;

class CRMCustomerImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Customer name is required';
        }

        if (empty($row['customer_code'] ?? null)) {
            $errors[] = 'Customer code is required';
        }

        if (empty($row['email'] ?? null)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email format is invalid';
        }

        if (empty($row['phone1'] ?? null)) {
            $errors[] = 'Phone 1 is required';
        }

        if (empty($row['country_code'] ?? null)) {
            $errors[] = 'Country code is required';
        } else {
            if (!Country::where('iso_3166_1_alpha_2', $row['country_code'])->exists()) {
                $errors[] = "Country code '{$row['country_code']}' does not exist";
            }
        }

        if (empty($row['currency_code'] ?? null)) {
            $errors[] = 'Currency code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $countryCode = $row['country_code'] ?? 'TZ';
        $country = Country::where('iso_3166_1_alpha_2', $countryCode)->first();
        if (!$country) {
            $country = Country::first();
        }

        $currencyCode = $row['currency_code'] ?? 'TZS';

        return [
            'name' => $row['name'] ?? ('Customer ' . $row['customer_code']),
            'code' => encrypt($row['customer_code']),
            'physical_address' => $row['physical_address'] ?? null,
            'postal_address' => $row['physical_address'] ?? null,
            'email' => encrypt($row['email'] ?? ($row['customer_code'] . '@example.com')),
            'phone1' => encrypt($row['phone1'] ?? '0000000000'),
            'phone2' => !empty($row['phone2']) ? encrypt($row['phone2']) : null,
            'country_id' => $country?->id,
            'vat_no' => $row['vat_no'] ?? null,
            'credit_day' => $row['credit_days'] ?? 0,
            'lpos_required' => $row['lpos_required'] ?? 0,
            'currency_id' => $this->getCurrencyId($currencyCode),
            'company_id' => $this->batch->company_id,
            'active' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            CRMCustomer::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($originalRow['customer_code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import customer: {$e->getMessage()}");
        }
    }

    protected function getCurrencyId(string $code): ?string
    {
        // This would fetch from currencies table, returning ID or null
        // Placeholder - adjust based on your currency model
        return null;
    }
}
