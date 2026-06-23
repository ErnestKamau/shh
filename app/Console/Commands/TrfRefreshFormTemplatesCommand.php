<?php

namespace App\Console\Commands;

use App\Models\TestRequestForm;
use Illuminate\Console\Command;

class TrfRefreshFormTemplatesCommand extends Command
{
    protected $signature = 'trf:refresh-form-templates';

    protected $description = 'Refresh walk-in Test Request Form templates (sampling date optional, signature field, quantity/unit row schema)';

    public function handle(): int
    {
        $this->info('Refreshing Test Request Form templates…');

        TestRequestForm::seedDefaults();

        $count = TestRequestForm::query()->where('is_active', true)->count();

        $this->info("Updated {$count} active template(s).");

        return self::SUCCESS;
    }
}
