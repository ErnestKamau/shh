<?php

namespace App\Services\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use Illuminate\Validation\ValidationException;

final class EnquiryAccountSettingsService
{
    public const TYPE_CREDIT = 'credit';

    public const TYPE_ADVANCE = 'advance';

    public const TYPE_WALK_IN = 'walk_in';

    public function resolveType(?CRMCustomer $customer): string
    {
        if ($customer === null || empty($customer->account_status)) {
            return self::TYPE_WALK_IN;
        }

        $config = SystemConfiguration::query()->find($customer->account_status);
        if ($config === null) {
            return self::TYPE_WALK_IN;
        }

        $meta = is_array($config->meta) ? $config->meta : [];
        $billingType = $meta['billing_type'] ?? null;

        // Preserve legacy overdue behaviour (not treated as credit for PO rules).
        $haystack = strtolower((string) ($config->key ?? '').' '.(string) ($config->value ?? ''));
        if (str_contains($haystack, 'overdue')) {
            return self::TYPE_WALK_IN;
        }

        if ($billingType === self::TYPE_ADVANCE) {
            return self::TYPE_ADVANCE;
        }

        if ($billingType === self::TYPE_CREDIT) {
            return self::TYPE_CREDIT;
        }

        if ($billingType === 'other') {
            return ((int) ($customer->credit_days ?? 0) > 0)
                ? self::TYPE_CREDIT
                : self::TYPE_WALK_IN;
        }

        // Legacy configs without meta: fall back to key/value string matching.
        $key = strtolower((string) ($config->key ?? ''));
        $value = strtolower((string) ($config->value ?? ''));

        if (str_contains($key, 'pay upfront') || str_contains($value, 'pay upfront')
            || str_contains($key, 'advance') || str_contains($value, 'advance')) {
            return self::TYPE_ADVANCE;
        }

        if ((str_contains($key, 'account holder') || str_contains($value, 'account holder'))
            && ! str_contains($key, 'overdue') && ! str_contains($value, 'overdue')) {
            return self::TYPE_CREDIT;
        }

        if (str_contains($key, 'days after') || str_contains($value, 'test report')) {
            return self::TYPE_CREDIT;
        }

        return self::TYPE_WALK_IN;
    }

    /**
     * @return array{
     *     type: string,
     *     requires_po: bool,
     *     requires_advance_reference: bool,
     *     allows_po_skip: bool
     * }
     */
    public function poRulesForCustomer(?CRMCustomer $customer): array
    {
        $type = $this->resolveType($customer);

        return match ($type) {
            self::TYPE_CREDIT => [
                'type' => $type,
                'requires_po' => true,
                'requires_advance_reference' => false,
                'allows_po_skip' => false,
            ],
            self::TYPE_ADVANCE => [
                'type' => $type,
                'requires_po' => false,
                'requires_advance_reference' => false,
                'allows_po_skip' => false,
            ],
            default => [
                'type' => self::TYPE_WALK_IN,
                'requires_po' => false,
                'requires_advance_reference' => false,
                'allows_po_skip' => true,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function validateAcceptPayload(?CRMCustomer $customer, array $payload): void
    {
        $rules = $this->poRulesForCustomer($customer);
        $poNumber = trim((string) ($payload['client_po_number'] ?? ''));
        $poSkipped = filter_var($payload['po_skipped'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($rules['requires_po'] && $poNumber === '') {
            throw ValidationException::withMessages([
                'client_po_number' => ['Purchase order number is required for credit account customers.'],
            ]);
        }

        if ($poSkipped && ! $rules['allows_po_skip']) {
            throw ValidationException::withMessages([
                'po_skipped' => ['Skipping PO is not allowed for this customer account.'],
            ]);
        }
    }
}
