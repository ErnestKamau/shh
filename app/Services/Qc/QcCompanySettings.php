<?php

namespace App\Services\Qc;

use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Str;

class QcCompanySettings
{
    public const KEY_CUSTOMER_ID = 'qc_customer_id';

    public const KEY_CUSTOMER_UNIT = 'qc_customer_unit';

    public const KEY_PERCENTAGE = 'qc_percentage_config';

    public function customerId(): ?string
    {
        $value = $this->get(self::KEY_CUSTOMER_ID);
        if ($value === null || $value === '' || ! Str::isUuid($value)) {
            return null;
        }

        return $value;
    }

    public function resolvedCustomer(): ?CRMCustomer
    {
        $id = $this->customerId();
        if ($id === null) {
            return null;
        }

        return CRMCustomer::query()->find($id);
    }

    public function customerUnitName(): ?string
    {
        $value = $this->get(self::KEY_CUSTOMER_UNIT);

        return $value !== null && $value !== '' ? $value : null;
    }

    public function repeatTolerancePercent(): float
    {
        $value = $this->get(self::KEY_PERCENTAGE);
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }

    /**
     * @param  array{customer_id?: string|null, customer_unit?: string|null, percentage?: string|float|null}  $values
     */
    public function save(array $values): void
    {
        if (array_key_exists('customer_id', $values)) {
            $customerId = $values['customer_id'];
            $this->put(
                self::KEY_CUSTOMER_ID,
                $customerId !== null && $customerId !== '' ? (string) $customerId : null
            );
        }

        if (array_key_exists('customer_unit', $values)) {
            $unit = $values['customer_unit'];
            $this->put(
                self::KEY_CUSTOMER_UNIT,
                $unit !== null && trim((string) $unit) !== '' ? trim((string) $unit) : null
            );
        }

        if (array_key_exists('percentage', $values)) {
            $percentage = $values['percentage'];
            $this->put(
                self::KEY_PERCENTAGE,
                $percentage !== null && $percentage !== '' ? (string) $percentage : '0'
            );
        }
    }

    public function get(string $key): ?string
    {
        $config = SystemConfiguration::query()->where('key', $key)->first();
        if (! $config) {
            return null;
        }

        $value = $config->value;

        return $value !== null ? (string) $value : null;
    }

    public function put(string $key, ?string $value): void
    {
        $config = SystemConfiguration::query()->firstOrNew(['key' => $key]);
        $config->value = $value;
        if (! $config->exists) {
            $config->status = 1;
        }
        $config->save();
    }
}
