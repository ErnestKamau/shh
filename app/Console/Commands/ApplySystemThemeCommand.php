<?php

namespace App\Console\Commands;

use App\Services\System\ThemeService;
use Illuminate\Console\Command;

class ApplySystemThemeCommand extends Command
{
    protected $signature = 'theme:apply
                            {--show : Print resolved theme variables after applying}';

    protected $description = 'Apply the AmSpec maroon theme to system configuration';

    public function handle(): int
    {
        $applied = ThemeService::applyTheme();

        $this->info('Applied AmSpec theme:');

        foreach ($applied as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        $this->line('  sys_quotation_primary_color: '.ThemeService::QUOTATION_PRIMARY);

        if ($this->option('show')) {
            $this->newLine();
            $this->info('Resolved CSS variables:');

            foreach (ThemeService::resolvedVariables() as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
        }

        $this->newLine();
        $this->comment('Theme cache cleared. Hard-refresh the browser if colors do not update immediately.');

        return self::SUCCESS;
    }
}
