<?php

namespace Database\Seeders;

use App\Services\Auth\LegacyPermissionSyncService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SpatieAuthorizationSeeder extends Seeder
{
    public function run(LegacyPermissionSyncService $syncService): void
    {
        $syncService->syncAll();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('Spatie roles, permissions, and user-role assignments synced.');
    }
}
