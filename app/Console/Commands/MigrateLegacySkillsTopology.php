<?php

namespace App\Console\Commands;

use App\Services\SkillsMatrix\LegacyTopologyMigrationService;
use Illuminate\Console\Command;

class MigrateLegacySkillsTopology extends Command
{
    protected $signature = 'skills-matrix:migrate-legacy-topology {matrix_id?} {--dry-run : Report counts without writing}';

    protected $description = 'Migrate legacy skills_matrix_configurations topology into skills_matrix_detail tables';

    public function handle(LegacyTopologyMigrationService $service): int
    {
        $matrixId = $this->argument('matrix_id');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no data will be written.');
        }

        $stats = $service->migrate($matrixId, $dryRun);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Details migrated', $stats['migrated_details']],
                ['Role proficiencies migrated', $stats['migrated_roles']],
                ['Skipped (already exist)', $stats['skipped']],
                ['Errors', count($stats['errors'])],
            ]
        );

        foreach ($stats['errors'] as $error) {
            $this->error($error);
        }

        return count($stats['errors']) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
