<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AdminGroupPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->get();

        Role::query()->firstOrCreate(
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'description' => 'System administrator group role',
                'level' => 1,
                'active' => true,
            ]
        );

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['super admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['super-admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system-admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system admin group'])
                    ->orWhereRaw('LOWER(name) = ?', ['super admin group']);
            })
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Admin/admin-group roles synced with all permissions: ' . $adminRoles->count());
    }
}
