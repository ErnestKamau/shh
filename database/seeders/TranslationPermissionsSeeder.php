<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TranslationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'system.components.translations.bulk import',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $this->command?->info("Ensured permission: {$permissionName}");
        }
    }
}
