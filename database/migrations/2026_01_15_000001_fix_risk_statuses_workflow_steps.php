<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix risk statuses - remove duplicates and ensure correct workflow step statuses
     */
    public function up(): void
    {
        // First, remove duplicate statuses created by the previous migration
        // Remove statuses with codes that match the pattern from the previous migration
        DB::table('risk_statuses')
            ->where(function($query) {
                $query->where('code', 'like', 'ASSESS_%')
                      ->orWhere('code', 'like', 'ASSESSING_%');
            })
            ->where('code', '!=', 'ASSESS')
            ->where('code', '!=', 'ASSESSING')
            ->delete();

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
            // Define correct statuses for each workflow step
            $correctStatuses = [
                // Step 1: Risk Identified
                [
                    'name' => 'Identified',
                    'code' => 'IDENT',
                    'description' => 'Risk has been identified',
                    'color_code' => '#17a2b8',
                    'order_index' => 1,
                    'workflow_step' => 1,
                ],
                [
                    'name' => 'New Risk',
                    'code' => 'NEW',
                    'description' => 'New risk identified',
                    'color_code' => '#17a2b8',
                    'order_index' => 2,
                    'workflow_step' => 1,
                ],
                
                // Step 2: Risk Assessment
                [
                    'name' => 'Under Assessment',
                    'code' => 'ASSESS',
                    'description' => 'Risk is being assessed',
                    'color_code' => '#ffc107',
                    'order_index' => 3,
                    'workflow_step' => 2,
                ],
                [
                    'name' => 'Assessment In Progress',
                    'code' => 'ASSESSING',
                    'description' => 'Assessment in progress',
                    'color_code' => '#ffc107',
                    'order_index' => 4,
                    'workflow_step' => 2,
                ],
                
                // Step 3: Risk Evaluation
                [
                    'name' => 'Under Evaluation',
                    'code' => 'EVAL',
                    'description' => 'Risk is being evaluated',
                    'color_code' => '#fd7e14',
                    'order_index' => 5,
                    'workflow_step' => 3,
                ],
                [
                    'name' => 'Evaluation In Progress',
                    'code' => 'EVALUATING',
                    'description' => 'Evaluation in progress',
                    'color_code' => '#fd7e14',
                    'order_index' => 6,
                    'workflow_step' => 3,
                ],
                
                // Step 4: Treatment Planning
                [
                    'name' => 'Treatment Planning',
                    'code' => 'PLAN',
                    'description' => 'Treatment plan is being developed',
                    'color_code' => '#6f42c1',
                    'order_index' => 7,
                    'workflow_step' => 4,
                ],
                [
                    'name' => 'Control Measures Planned',
                    'code' => 'PLANNED',
                    'description' => 'Control measures have been planned',
                    'color_code' => '#6f42c1',
                    'order_index' => 8,
                    'workflow_step' => 4,
                ],
                
                // Step 5: Treatment Implementation
                [
                    'name' => 'Treatment In Progress',
                    'code' => 'TREAT',
                    'description' => 'Treatment is being implemented',
                    'color_code' => '#20c997',
                    'order_index' => 9,
                    'workflow_step' => 5,
                ],
                [
                    'name' => 'Controls Implemented',
                    'code' => 'IMPL',
                    'description' => 'Controls have been implemented',
                    'color_code' => '#20c997',
                    'order_index' => 10,
                    'workflow_step' => 5,
                ],
                
                // Step 6: Risk Monitoring
                [
                    'name' => 'Monitored',
                    'code' => 'MONITOR',
                    'description' => 'Risk is being monitored',
                    'color_code' => '#17a2b8',
                    'order_index' => 11,
                    'workflow_step' => 6,
                ],
                [
                    'name' => 'Under Monitoring',
                    'code' => 'MONITORING',
                    'description' => 'Risk under ongoing monitoring',
                    'color_code' => '#17a2b8',
                    'order_index' => 12,
                    'workflow_step' => 6,
                ],
                
                // Step 7: Risk Closed
                [
                    'name' => 'Closed',
                    'code' => 'CLOSED',
                    'description' => 'Risk has been closed',
                    'color_code' => '#28a745',
                    'order_index' => 13,
                    'workflow_step' => 7,
                ],
                [
                    'name' => 'Resolved',
                    'code' => 'RESOLVED',
                    'description' => 'Risk has been resolved',
                    'color_code' => '#28a745',
                    'order_index' => 14,
                    'workflow_step' => 7,
                ],
            ];

            // Insert or update each status
            foreach ($correctStatuses as $status) {
                // First check if status exists with this code (globally, as code is unique)
                $existingByCode = DB::table('risk_statuses')
                    ->where('code', $status['code'])
                    ->first();

                if ($existingByCode) {
                    // Update existing status to ensure correct values
                    DB::table('risk_statuses')
                        ->where('id', $existingByCode->id)
                        ->update([
                            'name' => $status['name'],
                            'description' => $status['description'],
                            'color_code' => $status['color_code'],
                            'order_index' => $status['order_index'],
                            'workflow_step' => $status['workflow_step'],
                            'is_active' => true,
                            'updated_at' => now(),
                        ]);
                } else {
                    // Check if status exists with same name and workflow_step for this company
                    $existingByName = DB::table('risk_statuses')
                        ->where('name', $status['name'])
                        ->where('workflow_step', $status['workflow_step'])
                        ->where('company_id', $companyId)
                        ->first();

                    if ($existingByName) {
                        // Update existing status to match correct code
                        DB::table('risk_statuses')
                            ->where('id', $existingByName->id)
                            ->update([
                                'code' => $status['code'],
                                'description' => $status['description'],
                                'color_code' => $status['color_code'],
                                'order_index' => $status['order_index'],
                                'is_active' => true,
                                'updated_at' => now(),
                            ]);
                    } else {
                        // Insert new status only if it doesn't exist
                        try {
                            DB::table('risk_statuses')->insert([
                                'name' => $status['name'],
                                'code' => $status['code'],
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
                            // If insert fails due to unique constraint, try to update instead
                            $existing = DB::table('risk_statuses')
                                ->where('code', $status['code'])
                                ->first();
                            
                            if ($existing) {
                                DB::table('risk_statuses')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'name' => $status['name'],
                                        'description' => $status['description'],
                                        'color_code' => $status['color_code'],
                                        'order_index' => $status['order_index'],
                                        'workflow_step' => $status['workflow_step'],
                                        'is_active' => true,
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }
                }
            }
        }

        // Remove any duplicate statuses (same name, workflow_step, and company_id)
        $duplicates = DB::select("
            SELECT name, workflow_step, company_id, COUNT(*) as count
            FROM risk_statuses
            WHERE deleted_at IS NULL
            GROUP BY name, workflow_step, company_id
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $duplicate) {
            // Keep the first one, delete the rest
            $statuses = DB::table('risk_statuses')
                ->where('name', $duplicate->name)
                ->where('workflow_step', $duplicate->workflow_step)
                ->where('company_id', $duplicate->company_id)
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();

            // Delete all except the first one
            if ($statuses->count() > 1) {
                $idsToDelete = $statuses->skip(1)->pluck('id')->toArray();
                DB::table('risk_statuses')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes data, so down() doesn't need to do anything
        // The original migration handles table creation/dropping
    }
};

