<?php

namespace Tests\Feature\Lab;

use App\Models\Auth\Permission;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentUsageRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_requests_index_requires_view_permission(): void
    {
        Permission::firstOrCreate([
            'name' => 'laboratory.components.equipment-requests.view',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'No Access User',
            'email' => 'no-access-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('lab.equipment-requests.index'))
            ->assertForbidden();
    }

    public function test_equipment_requests_index_accessible_with_permission(): void
    {
        Permission::firstOrCreate([
            'name' => 'laboratory.components.equipment-requests.view',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'Viewer User',
            'email' => 'viewer-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);
        $user->givePermissionTo('laboratory.components.equipment-requests.view');

        $this->actingAs($user)
            ->get(route('lab.equipment-requests.index'))
            ->assertOk()
            ->assertSee('Equipment Requests');
    }
}
