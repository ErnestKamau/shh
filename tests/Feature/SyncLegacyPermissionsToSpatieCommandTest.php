<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncLegacyPermissionsToSpatieCommandTest extends TestCase
{
    public function test_modules_only_sync_command_runs_successfully(): void
    {
        if (! Schema::hasTable('spatie_permissions')) {
            $this->markTestSkipped('Spatie permissions table is not available in the current test database.');
        }

        $this->artisan('app:sync-legacy-permissions-to-spatie --modules-only')
            ->assertExitCode(0);
    }

    public function test_sync_command_runs_successfully_and_is_idempotent(): void
    {
        if (! Schema::hasTable('roles')) {
            $this->markTestSkipped('Legacy roles table is not available in the current test database.');
        }

        $this->artisan('app:sync-legacy-permissions-to-spatie')
            ->assertExitCode(0);

        $this->artisan('app:sync-legacy-permissions-to-spatie')
            ->assertExitCode(0);
    }
}
