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
            'Equipment.permission',
            'Equipment.components.Equipment-Disposal.View',
            'Equipment.components.Equipment-Disposal.Add',
            'Equipment.components.Equipment-Disposal.Edit',
            'Equipment.components.Equipment-Disposal.Delete',
            'Equipment.components.Equipment-Evaluation.Add',
            'Equipment.components.Equipment-Evaluation.View',
            'Equipment.components.Equipment-Evaluation.Edit',
            'Equipment.components.Equipment-Evaluation.Delete',
            'Equipment.components.Equipment-Decommission.Add',
            'Equipment.components.Equipment-Decommission.View',
            'Equipment.components.Equipment-Decommission.Edit',
            'Equipment.components.Equipment-Decommission.Delete',
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


