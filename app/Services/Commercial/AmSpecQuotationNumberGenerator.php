<?php

namespace App\Services\Commercial;

use App\Models\System\SystemConfiguration;
use App\QuotationHeader;
use Illuminate\Support\Carbon;

final class AmSpecQuotationNumberGenerator
{
    public static function generate(?Carbon $date = null, ?string $prefix = null): string
    {
        $date ??= now();
        $prefix ??= self::resolvePrefix();

        $sequence = self::maxExistingSequenceForYear($prefix, $date) + 1;

        do {
            $candidate = self::formatLabRef($date, $sequence, $prefix);
            $sequence++;
        } while (self::numberExists($candidate));

        return $candidate;
    }

    public static function formatLabRef(Carbon $date, int $sequence, string $prefix = 'AMSQ'): string
    {
        return $prefix
            .$date->format('ymd')
            .'-'
            .str_pad((string) max(1, $sequence), 3, '0', STR_PAD_LEFT);
    }

    public static function isAmsqFormat(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return (bool) preg_match('/^[A-Z]{2,10}\d{6}-\d{3,}$/', $value);
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
        $dirty = false;

        if (self::isLegacyNumber($header->quote_number)) {
            $header->quote_number = self::resolveQuoteNumberForHeader($header);
            $dirty = true;
        }

        if (empty($header->laboratory_ref)) {
            $header->laboratory_ref = $header->quote_number;
            $dirty = true;
        }

        if ($dirty) {
            $header->save();
        }

        return $header;
    }

    private static function resolveQuoteNumberForHeader(QuotationHeader $header): string
    {
        $labRef = trim((string) ($header->laboratory_ref ?? ''));
        $date = $header->quote_date ? Carbon::parse($header->quote_date) : null;

        if ($labRef !== '' && self::isAmsqFormat($labRef) && ! self::numberExists($labRef, (string) $header->id)) {
            return $labRef;
        }

        return self::generate($date);
    }

    private static function maxExistingSequenceForYear(string $prefix, Carbon $date): int
    {
        $year = $date->format('y');
        $yearPrefix = $prefix.$year;
        $pattern = '/^'.preg_quote($prefix, '/').'(\d{6})-(\d+)$/';

        $values = QuotationHeader::query()
            ->where(function ($query) use ($yearPrefix): void {
                $query->where('quote_number', 'like', $yearPrefix.'%')
                    ->orWhere('laboratory_ref', 'like', $yearPrefix.'%');
            })
            ->get(['quote_number', 'laboratory_ref']);

        $max = 0;

        foreach ($values as $row) {
            foreach ([$row->quote_number, $row->laboratory_ref] as $value) {
                if (! is_string($value) || ! preg_match($pattern, $value, $matches)) {
                    continue;
                }

                if (substr($matches[1], 0, 2) !== $year) {
                    continue;
                }

                $max = max($max, (int) $matches[2]);
            }
        }

        return $max;
    }

    private static function numberExists(string $candidate, ?string $exceptId = null): bool
    {
        return QuotationHeader::query()
            ->when($exceptId !== null && $exceptId !== '', function ($query) use ($exceptId): void {
                $query->where('id', '!=', $exceptId);
            })
            ->where(function ($query) use ($candidate): void {
                $query->where('quote_number', $candidate)
                    ->orWhere('laboratory_ref', $candidate);
            })
            ->exists();
    }

    private static function resolvePrefix(): string
    {
        $raw = SystemConfiguration::query()
            ->where('key', 'quotation_lab_ref_prefix')
            ->value('value');

        if (! filled($raw)) {
            return 'AMSQ';
        }

        $plain = trim(html_entity_decode(strip_tags((string) $raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = preg_replace('/[^A-Za-z0-9]/', '', $plain) ?? '';

        return $plain !== '' ? strtoupper($plain) : 'AMSQ';
    }
}
