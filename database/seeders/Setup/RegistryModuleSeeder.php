<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Registry\RegistryRequestCategory;
use App\Models\Registry\WorkflowDefinition;
use App\Models\Registry\WorkflowStep;
use App\Models\Registry\WorkflowTransition;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RegistryModuleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'registry.module.access',
            'registry.components.dashboard.view',
            'registry.components.requests.view',
            'registry.components.requests.add',
            'registry.components.requests.edit',
            'registry.components.requests.delete',
            'registry.components.correspondence register.view',
            'registry.components.correspondence register.edit',
            'registry.components.approval queue.view',
            'registry.components.approval queue.edit',
            'registry.components.assignments.view',
            'registry.components.assignments.edit',
            'registry.components.documents.view',
            'registry.components.documents.add',
            'registry.components.documents.delete',
            'registry.components.workflow configuration.view',
            'registry.components.workflow configuration.edit',
            'registry.components.reports.view',
            'registry.components.audit trail.view',
        ];

        $permissions = [];
        foreach ($permissionNames as $name) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'Admin'])
            ->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        $this->seedWorkflows();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Registry module seeded.');
    }

    protected function seedWorkflows(): void
    {
        $workflows = [
            'amendment' => [
                'name' => 'Amendment Workflow',
                'steps' => [
                    ['registry', 'Registry', 'registry_officer', false],
                    ['sro', 'SRO Review', 'sro_officer', false],
                    ['director', 'Director Approval', 'director', false],
                    ['lab_manager', 'Lab Manager', 'lab_manager', false],
                    ['analyst', 'Analyst', 'analyst', false],
                    ['qa', 'QA Review', 'qa_officer', false],
                    ['completed', 'Completed', null, true],
                ],
            ],
            'complaint' => [
                'name' => 'Complaint Workflow',
                'steps' => [
                    ['registry', 'Registry', 'registry_officer', false],
                    ['sro', 'SRO Review', 'sro_officer', false],
                    ['investigation', 'Investigation', 'investigator', false],
                    ['resolution', 'Resolution', 'resolver', false],
                    ['closed', 'Closed', null, true],
                ],
            ],
            'inquiry' => [
                'name' => 'Inquiry Workflow',
                'steps' => [
                    ['registry', 'Registry', 'registry_officer', false],
                    ['customer_service', 'Customer Service', 'customer_service', false],
                    ['closed', 'Closed', null, true],
                ],
            ],
            'analysis' => [
                'name' => 'Analysis Request Workflow',
                'steps' => [
                    ['registry', 'Registry', 'registry_officer', false],
                    ['sro', 'SRO Review', 'sro_officer', false],
                    ['director', 'Director Approval', 'director', false],
                    ['lab_receiving', 'Lab Receiving', 'lab_manager', false],
                    ['closed', 'Closed', null, true],
                ],
            ],
        ];

        $categories = [
            'amendment' => 'Amendment Request',
            'analysis' => 'Analysis Request',
            'complaint' => 'Complaint',
            'inquiry' => 'Inquiry',
            'general' => 'General Correspondence',
            'investigation' => 'Investigation',
            'appeal' => 'Appeal',
            'service' => 'Service Request',
            'regulatory' => 'Regulatory Request',
            'internal' => 'Internal Memo',
        ];

        foreach ($workflows as $code => $config) {
            $definition = WorkflowDefinition::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $config['name'],
                    'description' => $config['name'],
                    'is_active' => true,
                    'company_id' => null,
                ]
            );

            $stepIds = [];
            foreach ($config['steps'] as $index => [$stepCode, $stepName, $roleName, $isFinal]) {
                $step = WorkflowStep::query()->updateOrCreate(
                    [
                        'workflow_definition_id' => $definition->id,
                        'step_code' => $stepCode,
                    ],
                    [
                        'step_name' => $stepName,
                        'sequence' => $index + 1,
                        'role_name' => $roleName,
                        'is_final' => $isFinal,
                    ]
                );
                $stepIds[$stepCode] = $step->id;
            }

            $stepCodes = array_keys($stepIds);
            for ($i = 0; $i < count($stepCodes) - 1; $i++) {
                WorkflowTransition::query()->updateOrCreate(
                    [
                        'workflow_definition_id' => $definition->id,
                        'from_step_id' => $stepIds[$stepCodes[$i]],
                        'action_name' => 'approve',
                    ],
                    [
                        'to_step_id' => $stepIds[$stepCodes[$i + 1]],
                    ]
                );
                WorkflowTransition::query()->updateOrCreate(
                    [
                        'workflow_definition_id' => $definition->id,
                        'from_step_id' => $stepIds[$stepCodes[$i]],
                        'action_name' => 'reject',
                    ],
                    [
                        'to_step_id' => $stepIds[$stepCodes[0]],
                    ]
                );
            }

            if (isset($categories[$code])) {
                RegistryRequestCategory::query()->firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $categories[$code],
                        'workflow_definition_id' => $definition->id,
                        'default_priority' => 'normal',
                        'is_active' => true,
                        'company_id' => null,
                    ]
                );
            }
        }

        foreach ($categories as $code => $name) {
            if (in_array($code, array_keys($workflows), true)) {
                continue;
            }

            $inquiryDef = WorkflowDefinition::query()->where('code', 'inquiry')->first();
            RegistryRequestCategory::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'workflow_definition_id' => $inquiryDef?->id,
                    'default_priority' => 'normal',
                    'is_active' => true,
                    'company_id' => null,
                ]
            );
        }
    }
}
