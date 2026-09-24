<?php

namespace App\Services\Sampleworkflow;

/**
 * TRF "Sampled by" party: Client vs laboratory company.
 *
 * Stored values are stable keys (`client` / `company`); labels resolve at display time.
 */
final class SampledByParty
{
    public const CLIENT = 'client';

    public const COMPANY = 'company';

    /**
     * @return self::CLIENT|self::COMPANY|null
     */
    public static function normalize(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $raw = strtolower(trim((string) $value));
        if ($raw === '') {
            return null;
        }

        if (in_array($raw, [self::CLIENT, 'customer'], true)) {
            return self::CLIENT;
        }

        if (in_array($raw, [self::COMPANY, 'lab', 'laboratory', 'amspec'], true)) {
            return self::COMPANY;
        }

        return null;
    }

    /**
     * @return 0|1|null Null when value is unrecognized (legacy free text).
     */
    public static function companyPersonnelFlag(mixed $value): ?int
    {
        return match (self::normalize($value)) {
            self::CLIENT => 0,
            self::COMPANY => 1,
            default => null,
        };
    }

    public static function displayLabel(mixed $value, ?string $companyName = null): string
    {
        $party = self::normalize($value);
        if ($party === self::CLIENT) {
            return 'Client';
        }

        if ($party === self::COMPANY) {
            $name = trim((string) ($companyName ?? ''));
            if ($name === '') {
                if (function_exists('currentCompanyForFeatures')) {
                    $name = trim((string) (currentCompanyForFeatures()?->name ?? ''));
                }
                if ($name === '' && function_exists('getActiveCompany')) {
                    $name = trim((string) (getActiveCompany()?->name ?? ''));
                }
            }

            return $name !== '' ? $name : 'Laboratory';
        }

        return trim((string) ($value ?? ''));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function selectOptions(?string $companyName = null): array
    {
        return [
            ['value' => self::CLIENT, 'label' => 'Client'],
            ['value' => self::COMPANY, 'label' => self::displayLabel(self::COMPANY, $companyName)],
        ];
    }
}
