<?php

namespace App\Console\Commands;

use App\Services\AI\Repository\ReportingMartRefreshService;
use Illuminate\Console\Command;

class RefreshImaraReportingMarts extends Command
{
    protected $signature = 'imara:refresh-reporting-marts
                            {marts?* : Optional reporting marts to refresh}
                            {--sync : Sync configured ETL sources before refreshing marts}
                            {--chunk= : Override the ETL chunk size used with --sync}';

    protected $description = 'Refresh the PostgreSQL reporting marts for Imara operational dashboards';

    public function handle(ReportingMartRefreshService $refreshService): int
    {
        $configuredMarts = config('imara_ai.reporting.marts', []);
        $marts = $this->argument('marts') ?: $configuredMarts;
        $chunkSize = $this->option('chunk') ? (int) $this->option('chunk') : null;
        $hasFailures = false;

        $invalidMarts = array_values(array_diff($marts, $configuredMarts));
        if (!empty($invalidMarts)) {
            $this->error('Unsupported reporting marts: ' . implode(', ', $invalidMarts));
            $this->line('Configured marts: ' . implode(', ', $configuredMarts));

            return self::FAILURE;
        }

        try {
            $results = $refreshService->refresh($marts, (bool) $this->option('sync'), $chunkSize);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($results as $mart => $result) {
            $this->info("[{$mart}] {$result['status']} - {$result['rows_materialized']} row(s) materialized");

            if (!empty($result['message'])) {
                $this->line("  {$result['message']}");
            }

            if (($result['status'] ?? null) === 'failed') {
                $hasFailures = true;
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }
}
