<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class HelpDeskPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'tickets.module.access',
            'helpdesk.components.dashboard.view',
            'helpdesk.components.categories.view',
            'helpdesk.components.categories.add',
            'helpdesk.components.categories.edit',
            'helpdesk.components.archived tickets.view',
            'helpdesk.components.tickets.view',
            'helpdesk.components.tickets.add',
            'helpdesk.components.tickets.edit',
            'helpdesk.components.tickets.delete',
            'helpdesk.components.chat.view',
            'helpdesk.components.chat.add',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'Admin'])
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $count = count($permissions);
        $this->command?->info("HelpDesk permissions ensured: {$count}");
    }
}
