<?php

namespace App\Services\Commercial;

use App\QuotationHeader;
use Illuminate\Support\Carbon;

final class AmSpecQuotationNumberGenerator
{
    public static function generate(
        string $customerId,
        string $customerName,
        ?Carbon $date = null,
    ): string {
        $date ??= now();
        $namePart = self::sanitizeCustomerName($customerName);
        $prefix = $namePart.$date->format('Y');

        $lastNumber = QuotationHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('quote_number', 'like', $prefix.'%')
            ->orderByDesc('quote_number')
            ->value('quote_number');

        $sequence = 1;
        if (is_string($lastNumber) && str_starts_with($lastNumber, $prefix)) {
            $sequencePart = substr($lastNumber, strlen($prefix));
            if (ctype_digit($sequencePart)) {
                $sequence = (int) $sequencePart + 1;
            }
        }

        do {
            $candidate = self::formatSequence($namePart, $date, $sequence);
            $sequence++;
        } while (QuotationHeader::query()->where('quote_number', $candidate)->exists());

        return $candidate;
    }

    public static function formatSequence(string $customerNamePart, Carbon $date, int $sequence): string
    {
        return $customerNamePart
            .$date->format('Y')
            .str_pad((string) max(1, $sequence), 3, '0', STR_PAD_LEFT);
    }

    public static function sanitizeCustomerName(string $name): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9]/', '', $name) ?? '';

        return $sanitized !== '' ? $sanitized : 'Customer';
    }

    public static function isLegacyNumber(?string $quoteNumber): bool
    {
        if ($quoteNumber === null || $quoteNumber === '') {
            return true;
        }

        return str_starts_with($quoteNumber, 'QUOTE-');
    }

    public static function assignIfMissing(QuotationHeader $header): QuotationHeader
    {
        if (! self::isLegacyNumber($header->quote_number)) {
            return $header;
        }

        $header->loadMissing('customer');

        $header->quote_number = self::generate(
            (string) $header->crm_customer_id,
            (string) ($header->customer?->name ?? 'Customer'),
            $header->quote_date ? Carbon::parse($header->quote_date) : null,
        );
        $header->save();

        return $header;
    }
}
