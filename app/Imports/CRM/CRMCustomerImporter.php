<?php

namespace App\Imports\CRM;

use App\Imports\BaseImporter;
use App\Models\CRM\CRMCustomer;
use App\Country;
use Illuminate\Support\Collection;

class CRMCustomerImporter extends BaseImporter
{
    protected function getPrimaryKeysForFormType(string $formType): array
    {
        if (strtolower($formType) === 'customer') {
            return ['customer_code', 'client_code', 'code'];
        }

        return parent::getPrimaryKeysForFormType($formType);
    }

    protected function afterHeaderRowDetected(array &$headerMap, Collection $rows): void
    {
        foreach ($headerMap as $columnIndex => $headerName) {
            if (in_array($headerName, ['client_code', 'clientcode', 'code'], true)) {
                $headerMap[$columnIndex] = 'customer_code';
            }
        }
    }

    protected function normalizeRow(array $row): array
    {
        $rawCustomerCode = $this->fuzzyGet($row, ['customer_code', 'client_code', 'code']);
        $normalized = parent::normalizeRow($row);

        if ($rawCustomerCode !== null && trim((string) $rawCustomerCode) !== '') {
            $normalized['customer_code'] = $this->normalizeCustomerCode($rawCustomerCode);
        }

        return $normalized;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        $customerCode = $this->resolveCustomerCode($row);

        if ($customerCode === '') {
            $errors[] = 'Customer code is required';
        }

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Customer name is required';
        }

        if (empty($row['email'] ?? null)) {
            $errors[] = 'Email is required';
        } elseif (! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email format is invalid';
        }

        if (empty($row['phone1'] ?? null)) {
            $errors[] = 'Phone 1 is required';
        }

        if (empty($row['country_code'] ?? null)) {
            $errors[] = 'Country code is required';
        } else {
            $countryCode = $this->normalizeCountryCode($row['country_code']);

            if ($countryCode === null) {
                $errors[] = "Country code '{$row['country_code']}' must be a 2-letter ISO code (e.g. BR, KE)";
            } elseif (! Country::query()->where('iso_code_2', $countryCode)->exists()) {
                $errors[] = "Country code '{$countryCode}' does not exist";
            }
        }

        if (empty($row['currency_code'] ?? null)) {
            $errors[] = 'Currency code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $customerCode = $this->resolveCustomerCode($row);
        $countryCode = $this->normalizeCountryCode($row['country_code'] ?? null) ?? 'TZ';
        $country = Country::query()->where('iso_code_2', $countryCode)->first();
        if (! $country) {
            $country = Country::query()->first();
        }

        $currencyCode = $row['currency_code'] ?? 'TZS';

        return [
            'name' => $row['name'] ?? ('Customer '.$customerCode),
            'code' => $customerCode,
            'physical_address' => $row['physical_address'] ?? null,
            'postal_address' => $row['physical_address'] ?? null,
            'email' => $row['email'] ?? ($customerCode.'@example.com'),
            'telephone1' => $row['phone1'] ?? '0000000000',
            'telephone2' => ! empty($row['phone2']) ? $row['phone2'] : null,
            'country_id' => $country?->id,
            'vat_no' => $row['vat_no'] ?? null,
            'credit_days' => $row['credit_days'] ?? 0,
            'lpos_required' => $row['lpos_required'] ?? 0,
            'currency_id' => $this->getCurrencyId($currencyCode),
            'company_id' => $this->batch->company_id,
            'active' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $customerCode = $this->resolveCustomerCode($originalRow);

            $existing = CRMCustomer::query()
                ->where('company_id', $this->batch->company_id)
                ->get()
                ->first(fn (CRMCustomer $customer): bool => (string) $customer->code === $customerCode);

            if ($existing !== null) {
                $existing->fill($transformedData);
                $existing->save();
                $this->recordUpsert($customerCode, 'updated');

                return true;
            }

            CRMCustomer::query()->create($transformedData);
            $this->recordUpsert($customerCode, 'inserted');

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

    protected function resolveCustomerCode(array $row): string
    {
        $rawCode = $this->fuzzyGet($row, ['customer_code', 'client_code', 'code']);

        return $this->normalizeCustomerCode($rawCode);
    }

    protected function normalizeCustomerCode(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if ((float) (int) $value === $value) {
                return (string) (int) $value;
            }

            return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
        }

        $customerCode = str_replace("\xA0", ' ', (string) $value);

        return trim($customerCode);
    }

    protected function normalizeCountryCode(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $countryCode = strtoupper(trim((string) $value));

        if (strlen($countryCode) !== 2 || ! ctype_alpha($countryCode)) {
            return null;
        }

        return $countryCode;
    }
}
