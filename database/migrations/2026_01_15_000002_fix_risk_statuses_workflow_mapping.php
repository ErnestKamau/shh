<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix risk statuses to match the workflow steps defined in helpers.php
     * Workflow steps should be:
     * Step 1: All Risks (filter only, not a status)
     * Step 2: Identified
     * Step 3: Under Assessment
     * Step 4: Under Evaluation
     * Step 5: Treatment Planning
     * Step 6: Treatment Implementation
     * Step 7: Risk Monitoring
     * Step 8: Closed
     */
    public function up(): void
    {
        // First, soft delete all existing risk statuses to start fresh
        // Update deleted_at instead of hard delete to avoid foreign key issues
        DB::table('risk_statuses')->update(['deleted_at' => now()]);

        // Get all company IDs
        $companyIds = DB::table('risks')
            ->distinct()
            ->pluck('company_id')
            ->toArray();
        
        if (!in_array(0, $companyIds)) {
            $companyIds[] = 0;
        }
        
        if (empty($companyIds)) {
            $companyIds = [0];
        }

        foreach ($companyIds as $companyId) {
            // Define correct statuses matching helpers.php workflow steps
            // Only ONE status per workflow step as per helpers.php
            $correctStatuses = [
                // Step 2: Risk Identified (workflow_step = 2)
                [
                    'name' => 'Identified',
                    'code' => 'IDENT',
                    'description' => 'Risk has been identified',
                    'color_code' => '#17a2b8',
                    'order_index' => 1,
                    'workflow_step' => 2,
                ],
                
                // Step 3: Risk Assessment (workflow_step = 3)
                [
                    'name' => 'Under Assessment',
                    'code' => 'ASSESS',
                    'description' => 'Risk is being assessed',
                    'color_code' => '#ffc107',
                    'order_index' => 2,
                    'workflow_step' => 3,
                ],
                
                // Step 4: Risk Evaluation (workflow_step = 4)
                [
                    'name' => 'Under Evaluation',
                    'code' => 'EVAL',
                    'description' => 'Risk is being evaluated',
                    'color_code' => '#fd7e14',
                    'order_index' => 3,
                    'workflow_step' => 4,
                ],
                
                // Step 5: Treatment Planning (workflow_step = 5)
                [
                    'name' => 'Treatment Planning',
                    'code' => 'PLAN',
                    'description' => 'Treatment plan is being developed',
                    'color_code' => '#6f42c1',
                    'order_index' => 4,
                    'workflow_step' => 5,
                ],
                
                // Step 6: Treatment Implementation (workflow_step = 6)
                [
                    'name' => 'Treatment Implementation',
                    'code' => 'TREAT',
                    'description' => 'Treatment is being implemented',
                    'color_code' => '#20c997',
                    'order_index' => 5,
                    'workflow_step' => 6,
                ],
                
                // Step 7: Risk Monitoring (workflow_step = 7)
                [
                    'name' => 'Risk Monitoring',
                    'code' => 'MONITOR',
                    'description' => 'Risk is being monitored',
                    'color_code' => '#17a2b8',
                    'order_index' => 6,
                    'workflow_step' => 7,
                ],
                
                // Step 8: Risk Closed (workflow_step = 8)
                [
                    'name' => 'Closed',
                    'code' => 'CLOSED',
                    'description' => 'Risk has been closed',
                    'color_code' => '#28a745',
                    'order_index' => 7,
                    'workflow_step' => 8,
                ],
            ];

            // Insert or update all statuses
            foreach ($correctStatuses as $status) {
                // Use base code for company_id 0, add suffix for others if needed
                $code = $status['code'];
                if ($companyId > 0) {
                    // Check if code already exists globally, if so add company suffix
                    $existingGlobal = DB::table('risk_statuses')
                        ->where('code', $code)
                        ->where('company_id', 0)
                        ->exists();
                    if ($existingGlobal) {
                        $code = $status['code'] . '_' . $companyId;
                    }
                }
                
                // Check if status already exists (including soft deleted)
                $existing = DB::table('risk_statuses')
                    ->where('code', $code)
                    ->first();
                
                if ($existing) {
                    // Update existing status
                    DB::table('risk_statuses')
                        ->where('id', $existing->id)
                        ->update([
                            'name' => $status['name'],
                            'description' => $status['description'],
                            'color_code' => $status['color_code'],
                            'order_index' => $status['order_index'],
                            'workflow_step' => $status['workflow_step'],
                            'is_active' => true,
                            'company_id' => $companyId,
                            'deleted_at' => null, // Restore if soft deleted
                            'updated_at' => now(),
                        ]);
                } else {
                    // Insert new status
                    try {
                        DB::table('risk_statuses')->insert([
                            'name' => $status['name'],
                            'code' => $code,
                            'description' => $status['description'],
                            'color_code' => $status['color_code'],
                            'order_index' => $status['order_index'],
                            'workflow_step' => $status['workflow_step'],
                            'is_active' => true,
                            'company_id' => $companyId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        // If insert fails, try to update by name and workflow_step
                        $existingByName = DB::table('risk_statuses')
                            ->where('name', $status['name'])
                            ->where('workflow_step', $status['workflow_step'])
                            ->where('company_id', $companyId)
                            ->first();
                        
                        if ($existingByName) {
                            DB::table('risk_statuses')
                                ->where('id', $existingByName->id)
                                ->update([
                                    'code' => $code,
                                    'description' => $status['description'],
                                    'color_code' => $status['color_code'],
                                    'order_index' => $status['order_index'],
                                    'is_active' => true,
                                    'deleted_at' => null,
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                }
            }
        }

        // Update existing risks to match new workflow step mapping
        // Map old workflow_step to new workflow_step
        $statusMapping = [
            'Identified' => 2,
            'New Risk' => 2,
            'Under Assessment' => 3,
            'Assessment In Progress' => 3,
            'Under Evaluation' => 4,
            'Evaluation In Progress' => 4,
            'Treatment Planning' => 5,
            'Control Measures Planned' => 5,
            'Treatment In Progress' => 6,
            'Controls Implemented' => 6,
            'Monitored' => 7,
            'Under Monitoring' => 7,
            'Risk Monitoring' => 7,
            'Closed' => 8,
            'Resolved' => 8,
        ];

        foreach ($statusMapping as $statusName => $newWorkflowStep) {
            DB::table('risks')
                ->where('status_name', $statusName)
                ->update(['workflow_step' => $newWorkflowStep]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes data, so down() doesn't need to do anything
    }
};

