<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateSupportingDocuments extends Command
{
    protected $signature = 'supporting-documents:migrate {--run : Actually run the migrations (otherwise prints the file-level commands)}';

    protected $description = 'Run Supporting Documents migrations by file path order';

    public function handle(): int
    {
        $paths = [
            'database/migrations/2026_04_17_205911_create_supporting_document_templates_table.php',
            'database/migrations/2026_04_17_205911_create_supporting_document_sections_table.php',
            'database/migrations/2026_04_17_205911_create_supporting_document_elements_table.php',
            'database/migrations/2026_04_17_205912_create_supporting_document_instances_table.php',
            'database/migrations/2026_04_17_205912_create_supporting_document_instance_values_table.php',
        ];

        if (! $this->option('run')) {
            $this->info('Run these migrations by file path in this order:');
            foreach ($paths as $path) {
                $this->line('php artisan migrate --force --path=' . $path);
            }

            $this->newLine();
            $this->line('Or run: php artisan supporting-documents:migrate --run');

            return self::SUCCESS;
        }

        foreach ($paths as $path) {
            $this->info('Migrating: ' . $path);
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => $path,
            ]);

            $this->output->write(Artisan::output());
        }

        $this->info('Supporting Documents migrations complete.');

        return self::SUCCESS;
    }
}

