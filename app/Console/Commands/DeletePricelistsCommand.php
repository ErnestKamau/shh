<?php

namespace App\Console\Commands;

use App\Services\Billing\PricelistCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeletePricelistsCommand extends Command
{
    private const PRODUCTION_CONFIRMATION = 'DELETE-ALL-PRICELISTS';

    protected $signature = 'billing:delete-pricelists
                            {--force : Confirm deletion of every pricelist and related row}
                            {--production-confirm= : Required production safety phrase}';

    protected $description = 'Delete all pricelists, items, customer assignments, and email logs (master data preserved)';

    public function handle(PricelistCleanupService $service): int
    {
        if (! $this->option('force')) {
            $this->error('This command permanently deletes all pricelist catalogue data.');
            $this->line('Run with --force to proceed.');

            return self::FAILURE;
        }

        if ($this->laravel->environment('production')
            && $this->option('production-confirm') !== self::PRODUCTION_CONFIRMATION) {
            $this->error('Production execution requires the explicit confirmation phrase.');
            $this->line('--production-confirm='.self::PRODUCTION_CONFIRMATION);

            return self::FAILURE;
        }

        $this->warn('Deleting all pricelists, items, assignments, and email logs…');
        $this->line('Sample types, analysis definitions, users, TRFs, and other master data are not touched.');

        try {
            $counts = DB::transaction(static fn (): array => $service->deleteAll());
        } catch (\Throwable $exception) {
            $this->error('Pricelist cleanup failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Pricelist catalogue cleared.');
        $this->table(
            ['Target', 'Affected rows'],
            collect($counts)->map(fn (int $count, string $target): array => [$target, (string) $count])->values()->all(),
        );

        return self::SUCCESS;
    }
}
