<?php

namespace App\Console\Commands;

use App\Jobs\Equipment\ProcessPeriodicDepreciationJob;
use Illuminate\Console\Command;

class ProcessEquipmentDepreciationCommand extends Command
{
    protected $signature = 'equipment:process-depreciation {--config= : Optional depreciation config UUID}';

    protected $description = 'Post due depreciation ledger entries for active equipment assets';

    public function handle(): int
    {
        $configId = $this->option('config');

        ProcessPeriodicDepreciationJob::dispatch($configId ?: null);

        $this->info('Depreciation processing job dispatched.');

        return self::SUCCESS;
    }
}
