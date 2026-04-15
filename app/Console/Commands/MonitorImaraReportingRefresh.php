<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorImaraReportingRefresh extends Command
{
    protected $signature = 'imara:monitor-reporting-refresh
                            {--hours=2 : Check refresh jobs from the last N hours}';

    protected $description = 'Monitor the health and status of reporting mart refresh jobs';

    public function handle(): int
    {
        $connection = config('imara_ai.repository_connection', 'pgsql_ai');
        $schema = config('imara_ai.schemas.reporting', 'reporting');
        $hoursBack = (int) $this->option('hours');
        $sinceTime = Carbon::now()->subHours($hoursBack);

        // Get recent sync runs
        $syncRuns = DB::connection($connection)->table("{$schema}.sync_runs")
            ->where('created_at', '>=', $sinceTime)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($syncRuns->isEmpty()) {
            $this->warn("No refresh jobs found in the last {$hoursBack} hours");
            return self::SUCCESS;
        }

        $this->info("📊 Reporting Mart Refresh Status (Last {$hoursBack} hours)");
        $this->line('');

        $status = ['completed' => 0, 'failed' => 0, 'running' => 0];
        $totalRows = 0;
        $lastCompleted = null;

        foreach ($syncRuns as $run) {
            $status[$run->status]++;
            $totalRows += $run->rows_synced ?? 0;

            if ($run->status === 'completed' && $lastCompleted === null) {
                $lastCompleted = $run;
            }

            $icon = match ($run->status) {
                'completed' => '✅',
                'failed' => '❌',
                'running' => '⏳',
                default => '⚪',
            };

            $sourceTable = $run->source_table ?? 'N/A';
            $rowsText = $run->rows_synced > 0 ? "({$run->rows_synced} rows)" : '';
            $timeAgo = Carbon::parse($run->updated_at)->diffForHumans();

            $this->line("{$icon} [{$run->status}] {$sourceTable} → {$run->target_table} {$rowsText} - {$timeAgo}");

            if ($run->status === 'failed' && $run->error_message) {
                $this->error("   Error: " . substr($run->error_message, 0, 100) . '...');
            }
        }

        $this->line('');
        $this->info("Summary:");
        $this->line("  ✅ Completed: {$status['completed']}");
        $this->line("  ❌ Failed: {$status['failed']}");
        $this->line("  ⏳ Running: {$status['running']}");
        $this->line("  📦 Total rows synced: " . number_format($totalRows));

        if ($lastCompleted) {
            $lastTime = Carbon::parse($lastCompleted->updated_at);
            $minutesAgo = $lastTime->diffInMinutes(Carbon::now());
            $this->line("  ⏱️  Last successful refresh: {$minutesAgo} minutes ago");
        }

        $this->line('');

        // Check for stale data
        if ($lastCompleted && $lastCompleted->updated_at < Carbon::now()->subHours(4)) {
            $this->warn('⚠️  Warning: Reporting marts not refreshed in the last 4 hours!');
            return self::FAILURE;
        }

        // Check for failures
        if ($status['failed'] > 0) {
            $this->warn("⚠️  {$status['failed']} refresh job(s) failed in the last {$hoursBack} hours");
            return self::FAILURE;
        }

        $this->info('✅ All reporting marts are healthy!');
        return self::SUCCESS;
    }
}
