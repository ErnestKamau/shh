<?php

namespace Tests\Feature\System;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationPermissionsTest extends TestCase
{
    public function test_translation_bulk_import_permission_can_be_seeded(): void
    {
        if (!Schema::hasTable('spatie_permissions')) {
            $this->markTestSkipped('spatie_permissions table not available.');
        }

        $this->artisan('db:seed --class=TranslationPermissionsSeeder')
            ->assertExitCode(0);

        $exists = DB::table('spatie_permissions')
            ->where('name', 'System.components.Translations.Bulk Import')
            ->exists();

        $this->assertTrue($exists);
    }
}
