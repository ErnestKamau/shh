<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EquipmentDisposalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $permissions = [
            'equipment.permission',
            'equipment.components.equipment-disposal.view',
            'equipment.components.equipment-disposal.add',
            'equipment.components.equipment-disposal.edit',
            'equipment.components.equipment-disposal.delete',
            'equipment.components.equipment-evaluation.add',
            'equipment.components.equipment-evaluation.view',
            'equipment.components.equipment-evaluation.edit',
            'equipment.components.equipment-evaluation.delete',
            'equipment.components.equipment-decommission.add',
            'equipment.components.equipment-decommission.view',
            'equipment.components.equipment-decommission.edit',
            'equipment.components.equipment-decommission.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
            $this->command->info("Ensured permission: {$permissionName}");
        }

        $this->command->info('Equipment disposal permissions seeded successfully.');
    }
}


