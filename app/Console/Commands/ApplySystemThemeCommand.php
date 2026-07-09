<?php

namespace App\Console\Commands;

use App\Services\System\ThemeService;
use Illuminate\Console\Command;

class ApplySystemThemeCommand extends Command
{
    protected $signature = 'theme:apply
                            {preset : Theme preset to apply (amspec or kdb)}
                            {--show : Print resolved theme variables after applying}';

    protected $description = 'Apply a branded theme preset to system configuration (use instead of System Theming UI)';

    public function handle(): int
    {
        $preset = strtolower((string) $this->argument('preset'));

        try {
            $applied = ThemeService::applyPreset($preset);
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Applied [{$preset}] theme preset:");

        foreach ($applied as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        if ($preset === 'amspec') {
            $this->line('  sys_quotation_primary_color: '.ThemeService::AMSPEC_QUOTATION_PRIMARY);
        } else {
            $this->line('  sys_quotation_primary_color: '.ThemeService::QUOTATION_PRIMARY);
        }

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
