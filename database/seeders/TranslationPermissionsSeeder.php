<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TranslationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'System.components.Translations.Bulk Import',
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
