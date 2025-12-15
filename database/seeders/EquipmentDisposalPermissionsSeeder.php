<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\DB;

class EquipmentDisposalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Disposal permissions
        $permissions = [
            'Equipment.components.Equipment-Disposal.View',
            'Equipment.components.Equipment-Disposal.Create',
            'Equipment.components.Equipment-Disposal.Approve',
            'Equipment.components.Equipment-Disposal.Execute',
            'Equipment.components.Equipment-Disposal.Report.Download',
            'Equipment.components.Equipment-Disposal.Report.Annual-Review',
            'Equipment.components.Equipment-Evaluation.Create',
            'Equipment.components.Equipment-Evaluation.View',
            'Equipment.components.Equipment-Decommission.Perform',
        ];

        foreach ($permissions as $permission) {
            // Check if permission already exists
            $exists = DB::table('permissions')->where('name', $permission)->exists();
            
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Created permission: {$permission}");
            } else {
                $this->command->info("Permission already exists: {$permission}");
            }
        }

        $this->command->info('Equipment disposal permissions seeded successfully.');
    }
}


