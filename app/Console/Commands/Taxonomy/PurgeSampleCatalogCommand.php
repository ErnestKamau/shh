<?php

namespace App\Console\Commands\Taxonomy;

use App\Services\Taxonomy\SampleCatalogPurgeService;
use Illuminate\Console\Command;

class PurgeSampleCatalogCommand extends Command
{
    protected $signature = 'taxonomy:purge-sample-catalog
                            {--force : Actually delete catalog rows}
                            {--dry-run : List allowlisted tables without deleting}';

    protected $description = 'Delete sample type categories, sample types, analysis types, analysis elements and owned pivots only (no CASCADE)';

    public function handle(SampleCatalogPurgeService $purge): int
    {
        config(['database.default' => env('DB__PSQL_CONNECTION', config('database.default'))]);

        $this->warn('Allowlisted DELETE tables:');
        foreach ($purge->deleteAllowlist() as $table) {
            $this->line("  - {$table}");
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry-run only. Pass --force to execute.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to purge without --force (or preview with --dry-run).');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->confirm('PRODUCTION: purge sample taxonomy catalog now?', false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $result = $purge->purge();

        $this->info('Nulled columns:');
        foreach ($result['nulled'] as $key => $count) {
            if ($count > 0) {
                $this->line("  {$key}: {$count}");
            }
        }

        $this->info('Deleted rows:');
        foreach ($result['deleted'] as $table => $count) {
            $this->line("  {$table}: {$count}");
        }

        $this->info('Taxonomy catalog purge complete.');

        return self::SUCCESS;
    }
}
