<?php

namespace App\Console\Commands;

use App\Services\Billing\QuotationCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteQuotationsCommand extends Command
{
    private const PRODUCTION_CONFIRMATION = 'DELETE-ALL-QUOTATIONS';

    protected $signature = 'billing:delete-quotations
                            {--force : Confirm deletion of every quotation and related row}
                            {--production-confirm= : Required production safety phrase}';

    protected $description = 'Delete all quotation headers, lines, approval logs, and enquiry links (master data preserved)';

    public function handle(QuotationCleanupService $service): int
    {
        if (! $this->option('force')) {
            $this->error('This command permanently deletes all billing quotation data.');
            $this->line('Run with --force to proceed.');

            return self::FAILURE;
        }

        if ($this->laravel->environment('production')
            && $this->option('production-confirm') !== self::PRODUCTION_CONFIRMATION) {
            $this->error('Production execution requires the explicit confirmation phrase.');
            $this->line('--production-confirm='.self::PRODUCTION_CONFIRMATION);

            return self::FAILURE;
        }

        $this->warn('Deleting all quotation headers, lines, and related billing quote data…');
        $this->line('Customers, sample types, TRF templates, pricelists, and lab jobs are not touched.');

        try {
            $counts = DB::transaction(static fn (): array => $service->deleteAll());
        } catch (\Throwable $exception) {
            $this->error('Quotation cleanup failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Quotation data cleared.');
        $this->table(
            ['Target', 'Affected rows'],
            collect($counts)->map(fn (int $count, string $target): array => [$target, (string) $count])->values()->all(),
        );

        return self::SUCCESS;
    }
}
