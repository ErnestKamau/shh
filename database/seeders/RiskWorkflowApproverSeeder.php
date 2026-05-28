<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;

class RiskWorkflowApproverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = \App\Company::query()->where('active', true)->orderBy('name')->first()
            ?? \App\Company::query()->orderBy('name')->first();
        $companyId = auth()->check()
            ? (auth()->user()->company_id ?? $company?->id)
            : ($company?->id ?? 0);
        $defaultUserId = User::query()->orderBy('id')->value('id');

        if (!$defaultUserId) {
            return;
        }

        // Define Risk Workflow Steps (Simplified for demo)
        // Step 1: Risk Identification (No approval needed to start)
        // Step 2: Risk Assessment (Approver needed to verify assessment)
        // Step 3: Risk Evaluation (Approver needed to verify evaluation)
        // Step 4: Treatment Plan (Approver needed to approve plan)
        // Step 5: Implementation (Verifier needed to verify implementation)
        // Step 6: Monitoring & Review (Approver needed to sign off reviews)
        // Step 7: Closure (Approver needed to close risk)

        $approvers = [
            // Step 2: Assessment Approval
            [
                'workflow_step' => 2,
                'role_type' => 'approver', // Reviewer/Approver
                'user_id' => $defaultUserId,
                'is_required' => true,
                'approval_type' => 'sequential',
                'module' => 'risk',
                'company_id' => $companyId,
            ],
            // Step 3: Evaluation Approval
            [
                'workflow_step' => 3,
                'role_type' => 'approver',
                'user_id' => $defaultUserId,
                'is_required' => true,
                'approval_type' => 'sequential',
                'module' => 'risk',
                'company_id' => $companyId,
            ],
             // Step 4: Treatment Plan Approval
            [
                'workflow_step' => 4,
                'role_type' => 'approver',
                'user_id' => $defaultUserId,
                'is_required' => true,
                'approval_type' => 'sequential',
                'module' => 'risk',
                'company_id' => $companyId,
            ],
            // Step 7: Closure Approval
            [
                'workflow_step' => 7,
                'role_type' => 'approver', // Final Approver
                'user_id' => $defaultUserId,
                'is_required' => true,
                'approval_type' => 'sequential',
                'module' => 'risk',
                'company_id' => $companyId,
            ],
        ];

        foreach ($approvers as $approver) {
            \App\Models\AuditModule\AuditWorkflowApprover::updateOrCreate(
                [
                    'workflow_step' => $approver['workflow_step'],
                    'role_type' => $approver['role_type'],
                    'user_id' => $approver['user_id'],
                    'module' => $approver['module'],
                    'company_id' => $approver['company_id'],
                ],
                $approver
            );
        }
    }
}
