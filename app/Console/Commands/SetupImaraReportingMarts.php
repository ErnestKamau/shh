<?php

namespace App\Console\Commands;

use App\Services\AI\Repository\AiRepositorySetupService;
use App\Services\AI\Repository\ReportingMartSetupService;
use Illuminate\Console\Command;

class SetupImaraReportingMarts extends Command
{
    protected $signature = 'imara:setup-reporting-marts
                            {--skip-pgvector : Skip enabling the pgvector extension during setup}';

    protected $description = 'Provision the PostgreSQL reporting marts for Imara operational dashboards';

    public function handle(
        AiRepositorySetupService $foundationSetupService,
        ReportingMartSetupService $reportingMartSetupService
    ): int {
        try {
            $foundation = $foundationSetupService->setup(! $this->option('skip-pgvector'));
            $result = $reportingMartSetupService->setup();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Repository connection: {$result['connection']}");
        $this->info('Schemas ensured: ' . implode(', ', $foundation['schemas']));
        $this->info('Reporting schema: ' . $result['schema']);
        $this->info('Reporting marts ensured: ' . implode(', ', $result['marts']));

        return self::SUCCESS;
    }
}
