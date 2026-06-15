<?php

namespace App\Services\Commercial;

use App\QuotationHeader;
use Illuminate\Support\Carbon;

final class AmSpecQuotationNumberGenerator
{
    private const PREFIX = 'AMSQ';

    public static function generate(?Carbon $date = null): string
    {
        $date ??= now();
        $dateKey = self::PREFIX.$date->format('ymd');
        $pattern = $dateKey.'-%';

        $lastNumber = QuotationHeader::query()
            ->where('quote_number', 'like', $pattern)
            ->orderByDesc('quote_number')
            ->value('quote_number');

        $sequence = 1;
        if (is_string($lastNumber) && preg_match('/-(\d{3})$/', $lastNumber, $matches) === 1) {
            $sequence = (int) $matches[1] + 1;
        }

        do {
            $candidate = self::formatSequence($date, $sequence);
            $sequence++;
        } while (QuotationHeader::query()->where('quote_number', $candidate)->exists());

        return $candidate;
    }

    public static function formatSequence(Carbon $date, int $sequence): string
    {
        return self::PREFIX.$date->format('ymd').'-'.str_pad((string) max(1, $sequence), 3, '0', STR_PAD_LEFT);
    }

    public static function assignIfMissing(QuotationHeader $header): QuotationHeader
    {
        if (! empty($header->quote_number) && str_starts_with((string) $header->quote_number, self::PREFIX)) {
            return $header;
        }

        $header->quote_number = self::generate();
        $header->save();

        return $header;
    }
}
