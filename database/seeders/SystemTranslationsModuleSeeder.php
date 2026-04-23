<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SystemTranslationsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SystemLanguagesSeeder::class,
            SpatieAuthorizationSeeder::class,
            TranslationPermissionsSeeder::class,
        ]);
    }
}
