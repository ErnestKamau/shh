<?php

namespace App\Console\Commands;

use App\Services\SkillsMatrix\SkillsMatrixDemoSeedService;
use Illuminate\Console\Command;

class SeedSkillsMatrixDemo extends Command
{
    protected $signature = 'skills-matrix:seed-demo
                            {--location-id= : Inventory location UUID for proficiencies and demo users}
                            {--fresh : Remove existing demo records before seeding}';

    protected $description = 'Seed skills matrix demo data (matrix, capability, training, dashboard-ready)';

    public function handle(SkillsMatrixDemoSeedService $service): int
    {
        $locationId = $this->option('location-id');
        $locationId = is_string($locationId) && $locationId !== '' ? $locationId : null;
        $fresh = (bool) $this->option('fresh');

        if ($fresh) {
            $this->warn('Removing existing demo skills-matrix records...');
        }

        try {
            $result = $service->run($locationId, $fresh);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Skills matrix demo data seeded successfully.');
        $this->table(
            ['Key', 'Value'],
            [
                ['Location ID', $result['location_id']],
                ['Skills matrix ID', $result['skills_matrix_id']],
                ['Capability matrix ID', $result['capability_id']],
                ['Training need ID', $result['training_need_id']],
                ['Training plan ID', $result['training_plan_id']],
                ['Demo users', (string) $result['users']],
                ['Competencies', (string) $result['competencies']],
                ['Training gap rows', (string) $result['training_details']],
            ],
        );
        $this->newLine();
        $this->line('Open /matrix/dashboard and select "Lab Team Capability 2026" (or refresh if auto-selected).');
        $this->line('Demo user password: Demo@2026!');

        return self::SUCCESS;
    }
}
