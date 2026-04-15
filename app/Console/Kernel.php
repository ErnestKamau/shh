<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Check stage timers every minute
        $schedule->command('check:stage-timers')->everyMinute();
        
        // Check for expiring documents daily at 9 AM
        $schedule->command('dms:check-expiry')->dailyAt('09:00');

        // ========== PHASE 0: ETL RUN ORPHAN CLEANUP ==========
        // Mark any RUNNING runs older than 2 hours as FAILED
        // Runs every minute to ensure stale runs don't block future ETL attempts
        $schedule->call(function () {
            try {
                $staleCount = \DB::table('reporting.sync_runs')
                    ->where('status', 'running')
                    ->where('started_at', '<', \Carbon\Carbon::now()->subHours(2))
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'Orphaned run detected on scheduler cleanup',
                        'stage' => 'system-cleanup',
                        'finished_at' => \Carbon\Carbon::now()
                    ]);
                
                if ($staleCount > 0) {
                    \Log::channel('etl')->warning("Cleaned up {$staleCount} orphaned ETL runs");
                }
            } catch (\Throwable $e) {
                \Log::channel('etl')->warning("Orphan cleanup failed: " . $e->getMessage());
            }
        })->everyMinute();
        
        // Sync tickets with developer app (polling fallback)
        if (config('developer_sync.enabled') && config('developer_sync.sync_method') === 'polling') {
            $interval = config('developer_sync.polling_interval', 300); // Default 5 minutes
            $intervalMinutes = max(1, intval($interval / 60));
            $schedule->command('tickets:sync')
                ->cron("*/{$intervalMinutes} * * * *")
                ->withoutOverlapping()
                ->runInBackground();
        }

        // Phase 2: Operational Reporting Mart Refresh
        // Hourly refresh for lab/ticketing data, daily for inventory/documents
        $reportingInterval = max(1, (int) config('imara_ai.reporting.refresh_interval_minutes', 60));
        $syncBeforeRefresh = config('imara_ai.reporting.sync_before_refresh', true);

        // Lab & Ticketing marts (hourly)
        $labTicketCommand = 'imara:refresh-reporting-marts lab_tat qc_stability ticket_sla';
        if ($syncBeforeRefresh) {
            $labTicketCommand .= ' --sync';
        }
        $schedule->command($labTicketCommand)
            ->hourly()
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Lab/Ticketing reporting marts refresh failed');
            })
            ->onSuccess(function() {
                \Log::info('Lab/Ticketing reporting marts refreshed successfully');
            });

        // Equipment & Inventory marts (every 6 hours for inventory risk)
        $equipmentInventoryCommand = 'imara:refresh-reporting-marts equipment_reliability inventory_risk';
        if ($syncBeforeRefresh) {
            $equipmentInventoryCommand .= ' --sync';
        }
        $schedule->command($equipmentInventoryCommand)
            ->everySixHours()
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Equipment/Inventory reporting marts refresh failed');
            })
            ->onSuccess(function() {
                \Log::info('Equipment/Inventory reporting marts refreshed successfully');
            });

        // Document Compliance mart (daily)
        $documentCommand = 'imara:refresh-reporting-marts document_compliance';
        if ($syncBeforeRefresh) {
            $documentCommand .= ' --sync';
        }
        $schedule->command($documentCommand)
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Document compliance reporting mart refresh failed');
            })
            ->onSuccess(function() {
                \Log::info('Document compliance reporting mart refreshed successfully');
            });

        // Monitor refresh job health (every 30 minutes)
        $schedule->command('imara:monitor-reporting-refresh')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        // Phase 5: Workflow Automation (Operational Chases)
        // Ticket SLA Chase (Hourly - Scans for approaching/passed deadlines)
        $schedule->command('workflow:ticket-sla-chase')
            ->hourly()
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Ticket SLA chase failed');
            });

        // Lab Overdue Chase (Every 4 hours)
        $schedule->command('workflow:lab-overdue-chase')
            ->everyFourHours()
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Lab overdue chase failed');
            });

        // Equipment Maintenance/Calibration Chase (Daily)
        $schedule->command('workflow:equipment-chase')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Equipment maintenance chase failed');
            });

        // Audit CAPA Chase (Daily)
        $schedule->command('workflow:audit-capa-chase')
            ->dailyAt('07:00')
            ->withoutOverlapping();

        // Invoice Collection Chase (Weekly)
        $schedule->command('workflow:invoice-chase')
            ->weeklyOn(1, '08:00')
            ->withoutOverlapping();

        // Work Order Chase (Daily)
        $schedule->command('workflow:workorder-chase')
            ->dailyAt('08:30')
            ->withoutOverlapping();

        // Inventory Risk Chase (Multi-Phase 6: Low Stock / Expiry alerts)
        $schedule->command('workflow:inventory-chase')
            ->everySixHours()
            ->withoutOverlapping()
            ->onFailure(function() {
                \Log::error('Inventory risk chase failed');
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
