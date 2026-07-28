<?php

namespace App\Console\Commands;

use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use Illuminate\Console\Command;

class RegenerateQuotationPdfCommand extends Command
{
    protected $signature = 'quotation:regenerate-pdf {headerId : Quotation header UUID}';

    protected $description = 'Regenerate and store the AmSpec quotation PDF for a quotation header';

    public function handle(QuotationReportService $quotationReportService): int
    {
        $headerId = (string) $this->argument('headerId');
        $header = QuotationHeader::query()->find($headerId);

        if ($header === null) {
            $this->error("Quotation header [{$headerId}] was not found.");

            return self::FAILURE;
        }

        $quotationReportService->storePdf($header->fresh());
        $this->info("Quotation PDF regenerated for [{$header->quote_number}].");

        return self::SUCCESS;
    }
}
