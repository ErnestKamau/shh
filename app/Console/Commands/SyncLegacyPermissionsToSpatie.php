<?php

namespace App\Console\Commands;

use App\Services\Auth\LegacyPermissionSyncService;
use Illuminate\Console\Command;

class SyncLegacyPermissionsToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-legacy-permissions-to-spatie {--modules-only : Sync only module/component/action permission catalog}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[DEPRECATED] Sync legacy roles, permissions, and user assignments into Spatie tables. This command is no longer part of the standard authentication flow and will be removed in a future release.';

    /**
     * Execute the console command.
     */
    public function handle(LegacyPermissionSyncService $syncService): int
    {
        $this->warn('DEPRECATION WARNING: This command is deprecated and will be removed in a future release.');
        $this->warn('The application has been migrated to use Spatie/laravel-permission exclusively.');
        $this->line('');
        
        if ((bool) $this->option('modules-only')) {
            $this->info('Syncing module permission catalog to Spatie...');
            $syncService->syncModulePermissions();
            $this->info('Module permission catalog sync completed.');

            return self::SUCCESS;
        }

        $this->info('Syncing legacy authorization data to Spatie...');
        $syncService->syncAll();
        $this->info('Legacy authorization sync completed.');

        return self::SUCCESS;
    }
}
