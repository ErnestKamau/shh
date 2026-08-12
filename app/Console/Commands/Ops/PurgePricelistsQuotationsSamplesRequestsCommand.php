<?php

namespace App\Console\Commands\Ops;

use App\Services\Ops\OperationalDataPurgeService;
use Illuminate\Console\Command;

class PurgePricelistsQuotationsSamplesRequestsCommand extends Command
{
    protected $signature = 'ops:purge-pricelists-quotations-samples-requests
                            {--force : Actually delete operational rows}
                            {--dry-run : Describe scope without deleting}';

    protected $description = 'Delete ONLY pricelists, quotations, sample batches, and sample submission requests (+ owned children). No CASCADE into other domains.';

    public function handle(OperationalDataPurgeService $purge): int
    {
        config(['database.default' => env('DB__PSQL_CONNECTION', config('database.default'))]);

        $this->warn('Domains (order): Samples → Requests → Quotations → Pricelists');
        $this->line('Never touches: users, CRM, companies, labs, form definitions, sample taxonomy catalog, standards, invoices headers.');

        if ($this->option('dry-run')) {
            $this->comment('Dry-run only. Pass --force to execute.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to purge without --force (or preview with --dry-run).');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->confirm('PRODUCTION: purge pricelists/quotations/samples/requests now?', false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $result = $purge->purge();

        $this->info('Nulled columns (non-zero):');
        foreach ($result['nulled'] as $key => $count) {
            if ($count > 0) {
                $this->line("  {$key}: {$count}");
            }
        }

        $this->info('Deleted rows (non-zero):');
        foreach ($result['deleted'] as $table => $count) {
            if ($count > 0) {
                $this->line("  {$table}: {$count}");
            }
        }

        $this->info('Operational data purge complete.');

        return self::SUCCESS;
    }
}
