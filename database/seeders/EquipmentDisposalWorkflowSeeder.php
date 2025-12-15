<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflow;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflowStep;
use App\User;

class EquipmentDisposalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Clear existing workflows (optional - comment out in production)
        // EquipmentDisposalApprovalWorkflow::truncate();
        // EquipmentDisposalApprovalWorkflowStep::truncate();

        $companyId = getUserCompany() ?? 1;
        $createdBy = auth()->id() ?? 1;

        // Workflow 1: Standard Equipment Disposal
        $standardWorkflow = EquipmentDisposalApprovalWorkflow::create([
            'name' => 'Standard Equipment Disposal',
            'description' => 'Default workflow for non-hazardous equipment disposal',
            'equipment_type_id' => null, // Applies to all types
            'location_id' => null, // Applies to all locations
            'company_id' => $companyId,
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Get users by role (you'll need to adjust based on your role system)
        // For now, using user IDs - in production, use role-based assignment
        $labManagerId = User::where('email', 'LIKE', '%manager%')->first()->id ?? 1;
        $qualityManagerId = User::where('email', 'LIKE', '%quality%')->first()->id ?? 1;

        // Step 1: Laboratory Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $standardWorkflow->id,
            'step_order' => 1,
            'step_name' => 'Laboratory Manager Approval',
            'assignee_type' => User::class,
            'assignee_id' => $labManagerId,
            'is_required' => true,
        ]);

        // Step 2: Quality Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $standardWorkflow->id,
            'step_order' => 2,
            'step_name' => 'Quality Manager Approval',
            'assignee_type' => User::class,
            'assignee_id' => $qualityManagerId,
            'is_required' => true,
        ]);

        $this->command->info('Standard workflow created.');

        // Workflow 2: Hazardous Equipment Disposal
        $hazardousWorkflow = EquipmentDisposalApprovalWorkflow::create([
            'name' => 'Hazardous Equipment Disposal',
            'description' => 'Workflow for hazardous or high-risk equipment (requires Safety Officer)',
            'equipment_type_id' => null,
            'location_id' => null,
            'company_id' => $companyId,
            'is_active' => true,
            'created_by' => $createdBy,
            'risk_level_trigger' => 'High', // Triggers when risk_level is High or Critical
        ]);

        $safetyOfficerId = User::where('email', 'LIKE', '%safety%')->first()->id ?? $labManagerId;

        // Step 1: Laboratory Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $hazardousWorkflow->id,
            'step_order' => 1,
            'step_name' => 'Laboratory Manager Review',
            'assignee_type' => User::class,
            'assignee_id' => $labManagerId,
            'is_required' => true,
        ]);

        // Step 2: Safety Officer
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $hazardousWorkflow->id,
            'step_order' => 2,
            'step_name' => 'Safety Officer Approval',
            'assignee_type' => User::class,
            'assignee_id' => $safetyOfficerId,
            'is_required' => true,
        ]);

        // Step 3: Quality Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $hazardousWorkflow->id,
            'step_order' => 3,
            'step_name' => 'Quality Manager Final Approval',
            'assignee_type' => User::class,
            'assignee_id' => $qualityManagerId,
            'is_required' => true,
        ]);

        $this->command->info('Hazardous workflow created.');

        // Workflow 3: High-Value Equipment Disposal
        $highValueWorkflow = EquipmentDisposalApprovalWorkflow::create([
            'name' => 'High-Value Equipment Disposal',
            'description' => 'Workflow for high-value equipment (requires Finance Manager approval)',
            'equipment_type_id' => null,
            'location_id' => null,
            'company_id' => $companyId,
            'is_active' => true,
            'created_by' => $createdBy,
            'value_threshold' => 10000, // Triggers when equipment value > $10,000
        ]);

        $financeManagerId = User::where('email', 'LIKE', '%finance%')->first()->id ?? $qualityManagerId;

        // Step 1: Equipment Custodian
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $highValueWorkflow->id,
            'step_order' => 1,
            'step_name' => 'Equipment Custodian Review',
            'assignee_type' => User::class,
            'assignee_id' => $labManagerId,
            'is_required' => true,
        ]);

        // Step 2: Laboratory Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $highValueWorkflow->id,
            'step_order' => 2,
            'step_name' => 'Laboratory Manager Approval',
            'assignee_type' => User::class,
            'assignee_id' => $labManagerId,
            'is_required' => true,
        ]);

        // Step 3: Finance Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $highValueWorkflow->id,
            'step_order' => 3,
            'step_name' => 'Finance Manager Approval',
            'assignee_type' => User::class,
            'assignee_id' => $financeManagerId,
            'is_required' => true,
        ]);

        // Step 4: Quality Manager
        EquipmentDisposalApprovalWorkflowStep::create([
            'workflow_id' => $highValueWorkflow->id,
            'step_order' => 4,
            'step_name' => 'Quality Manager Final Approval',
            'assignee_type' => User::class,
            'assignee_id' => $qualityManagerId,
            'is_required' => true,
        ]);

        $this->command->info('High-value workflow created.');

        $this->command->info('Equipment disposal workflows seeded successfully.');
        $this->command->info('Note: Please adjust user assignments based on your actual roles and permissions.');
    }
}


