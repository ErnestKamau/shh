<?php

namespace App\Services\Billing;

use App\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    private const LOCK_KEY = 'invoice-number-generator';

    private const LOCK_SECONDS = 15;

    /**
     * Next customer invoice number in format INV0001 (incrementing numeric suffix).
     */
    public function next(): string
    {
        return Cache::lock(self::LOCK_KEY, self::LOCK_SECONDS)->block(self::LOCK_SECONDS, function (): string {
            return DB::transaction(function (): string {
                $sequences = Invoice::query()
                    ->whereNotNull('invoice_number')
                    ->where('invoice_number', 'like', 'INV%')
                    ->lockForUpdate()
                    ->pluck('invoice_number');

                $max = 0;
                foreach ($sequences as $invoiceNumber) {
                    $sequence = self::parseSequence($invoiceNumber);
                    if ($sequence !== null && $sequence > $max) {
                        $max = $sequence;
                    }
                }

                return 'INV' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
            });
        });
    }

    /**
     * Extract the numeric sequence from INV0001 / INV-0001 style numbers.
     */
    public static function parseSequence(?string $invoiceNumber): ?int
    {
        if ($invoiceNumber === null || trim($invoiceNumber) === '') {
            return null;
        }

        if (preg_match('/^INV-?(\d+)$/i', trim($invoiceNumber), $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
