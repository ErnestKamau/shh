<?php

namespace Database\Seeders;

use Database\Seeders\Setup\PersonnelPermissionsSeeder;
use Database\Seeders\Setup\AdminGroupPermissionsSeeder;
use Database\Seeders\Setup\CRMPermissionsSeeder;
use Database\Seeders\Setup\EquipmentPermissionsSeeder;
use Database\Seeders\Setup\Languages\MasLanguageDatabaseSeeder;
use Database\Seeders\Setup\Languages\CRMLanguageSeeder;
use Database\Seeders\Setup\Languages\EquipmentLanguageSeeder;
use Database\Seeders\Setup\Languages\PersonnelLanguageSeeder;
use Database\Seeders\Setup\Languages\SystemTranslationsSeeder;
use Database\Seeders\Setup\LabModulePermissionsSeeder;
use Database\Seeders\Setup\SystemConfigPermissionsSeeder;
use Database\Seeders\Setup\SystemSetupSeeder;
use Database\Seeders\Setup\WorkflowResponsibilityConfigSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemSetupSeeder::class,
            WorkflowResponsibilityConfigSeeder::class,
            PersonnelPermissionsSeeder::class,
            CRMPermissionsSeeder::class,
            LabModulePermissionsSeeder::class,
            EquipmentPermissionsSeeder::class,
            ReportingUnitsSeeder::class,
            SystemConfigPermissionsSeeder::class,
            SystemTranslationsSeeder::class,
            PersonnelLanguageSeeder::class,
            CRMLanguageSeeder::class,
            EquipmentLanguageSeeder::class,
            // Keep MAS translations last so migrated MAS keys win on overlap.
            MasLanguageDatabaseSeeder::class,
            AdminGroupPermissionsSeeder::class,
        ]);
    }
}
