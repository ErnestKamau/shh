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
        \App\Console\Commands\GenerateVapidKeys::class,
        \App\Console\Commands\RegenerateQuotationPdfCommand::class,
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

        // Sync tickets with developer app (polling fallback)
        if (config('developer_sync.enabled') && config('developer_sync.sync_method') === 'polling') {
            $interval = config('developer_sync.polling_interval', 300); // Default 5 minutes
            $intervalMinutes = max(1, intval($interval / 60));
            $schedule->command('tickets:sync')
                ->cron("*/{$intervalMinutes} * * * *")
                ->withoutOverlapping()
                ->runInBackground();
        }

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

        $schedule->command('equipment:process-depreciation')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onFailure(function (): void {
                \Log::error('Equipment depreciation processing failed');
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

        // Customer PO ledger reconciliation (Nightly, flags drift only)
        $schedule->command('purchase-orders:reconcile')
            ->dailyAt('02:30')
            ->withoutOverlapping();
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
