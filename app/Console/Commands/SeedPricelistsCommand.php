<?php

namespace App\Console\Commands;

use App\Services\Billing\PricelistSeedService;
use Illuminate\Console\Command;

class SeedPricelistsCommand extends Command
{
    private const PRODUCTION_CONFIRMATION = 'DELETE-AND-REBUILD-PRICELISTS';

    protected $signature = 'billing:seed-pricelists
                            {--currency=AED : Currency code for all generated pricelists}
                            {--force : Confirm deletion and replacement of every current pricelist}
                            {--production-confirm= : Required production safety phrase}';

    protected $description = 'Delete current pricelists and seed master, customer, and package pricing';

    public function handle(PricelistSeedService $service): int
    {
        if (! $this->option('force')) {
            $this->error('This command deletes and replaces every current pricelist.');
            $this->line('Run with --force to proceed.');

            return self::FAILURE;
        }

        if ($this->laravel->environment('production')
            && $this->option('production-confirm') !== self::PRODUCTION_CONFIRMATION) {
            $this->error('Production execution requires the explicit confirmation phrase.');
            $this->line('--production-confirm='.self::PRODUCTION_CONFIRMATION);

            return self::FAILURE;
        }

        $currency = mb_strtoupper(trim((string) $this->option('currency')));

        $this->warn('Deleting and rebuilding all current pricelists...');
        $this->line('Historical quotation, invoice, and acceptance-form references will move to the new master pricelist.');

        try {
            $result = $service->rebuild($currency);
        } catch (\Throwable $exception) {
            $this->error('Pricelist rebuild failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Pricelists rebuilt successfully.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Pricelists', (string) $result['pricelists']],
                ['Items', (string) $result['items']],
                ['Package items', (string) $result['packages']],
                ['Customer assignments', (string) $result['assignments']],
                ['Master parameters', (string) $result['master_elements']],
                ['Package analyses', implode(', ', $result['package_analysis_types'])],
            ],
        );

        $this->line('Limits:');
        $this->line('  Master / customer per-parameter → max 5 parameters');
        $this->line('  Package pricelist → 4 parameters per Water/Food package');
        $this->newLine();
        $this->line('Assignments:');
        $this->line('  ADNOC Group → customer per-parameter + package');
        $this->line('  Emirates National Oil Company (ENOC) → customer per-parameter');
        $this->line('  DP World UAE → customer per-parameter');
        $this->line('  Gulftainer Company → master');

        return self::SUCCESS;
    }
}
