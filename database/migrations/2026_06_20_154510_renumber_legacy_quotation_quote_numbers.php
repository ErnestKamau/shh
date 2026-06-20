<?php

use App\QuotationHeader;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_headers')) {
            return;
        }

        QuotationHeader::query()
            ->where(function ($query): void {
                $query->whereNull('quote_number')
                    ->orWhere('quote_number', '')
                    ->orWhere('quote_number', 'like', 'QUOTE-%');
            })
            ->orderBy('created_at')
            ->each(function (QuotationHeader $header): void {
                AmSpecQuotationNumberGenerator::assignIfMissing($header);
            });
    }

    public function down(): void
    {
        // Legacy quote numbers cannot be restored.
    }
};
