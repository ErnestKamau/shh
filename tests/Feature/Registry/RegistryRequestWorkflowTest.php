<?php

namespace Tests\Feature\Registry;

use App\Models\Auth\Permission;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use App\Models\Registry\WorkflowDefinition;
use App\Models\Registry\WorkflowStep;
use App\Models\Registry\WorkflowTransition;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistryRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'registry.module.access', 'guard_name' => 'web']);
        Permission::create(['name' => 'registry.components.requests.add', 'guard_name' => 'web']);
        Permission::create(['name' => 'registry.components.requests.view', 'guard_name' => 'web']);

        $role = Role::create(['name' => 'registry-tester', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'registry.module.access',
            'registry.components.requests.add',
            'registry.components.requests.view',
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_registry_dashboard_requires_authentication(): void
    {
        $this->get(route('registry.dashboard'))->assertRedirect();
    }

    public function test_authenticated_user_can_access_registry_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('registry.dashboard'))
            ->assertOk();
    }

    public function test_workflow_transition_advances_stage(): void
    {
        $definition = WorkflowDefinition::create([
            'name' => 'Test Flow',
            'code' => 'test_flow',
            'is_active' => true,
            'company_id' => 0,
        ]);

        $step1 = WorkflowStep::create([
            'workflow_definition_id' => $definition->id,
            'step_name' => 'Step 1',
            'step_code' => 'step1',
            'sequence' => 1,
            'is_final' => false,
        ]);

        $step2 = WorkflowStep::create([
            'workflow_definition_id' => $definition->id,
            'step_name' => 'Step 2',
            'step_code' => 'step2',
            'sequence' => 2,
            'is_final' => true,
        ]);

        WorkflowTransition::create([
            'workflow_definition_id' => $definition->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'approve',
        ]);

        $category = RegistryRequestCategory::create([
            'name' => 'Test',
            'code' => 'test',
            'workflow_definition_id' => $definition->id,
            'is_active' => true,
            'company_id' => 0,
        ]);

        $request = RegistryRequest::create([
            'reference_no' => 'REG-2026-00001',
            'request_category_id' => $category->id,
            'workflow_definition_id' => $definition->id,
            'subject' => 'Test subject',
            'status' => RegistryRequest::STATUS_OPEN,
            'current_stage' => 'step1',
            'company_id' => 0,
            'received_at' => now(),
        ]);

        $engine = app(\App\Services\Registry\WorkflowEngineService::class);
        $updated = $engine->transition(new \App\DTOs\Registry\WorkflowTransitionDTO(
            registryRequestId: $request->id,
            actionName: 'approve',
            performedBy: $this->user->id,
        ));

        $this->assertSame('step2', $updated->current_stage);
        $this->assertSame(RegistryRequest::STATUS_CLOSED, $updated->status);
    }
}
