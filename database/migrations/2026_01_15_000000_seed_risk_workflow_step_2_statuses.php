<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure workflow step 2 statuses exist for risk management
     */
    public function up(): void
    {
        // Get all company IDs (including 0 for global)
        $companyIds = DB::table('risks')
            ->distinct()
            ->pluck('company_id')
            ->toArray();
        
        // Always include company_id 0 for global statuses
        if (!in_array(0, $companyIds)) {
            $companyIds[] = 0;
        }
        
        // If no companies exist yet, just use 0
        if (empty($companyIds)) {
            $companyIds = [0];
        }

        foreach ($companyIds as $companyId) {
            // Check if step 2 statuses already exist for this company
            $existingStep2Statuses = DB::table('risk_statuses')
                ->where('workflow_step', 2)
                ->where(function($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                          ->orWhere('company_id', 0);
                })
                ->where('is_active', true)
                ->count();

            // If no step 2 statuses exist, create them
            if ($existingStep2Statuses == 0) {
                // Check if statuses with these names already exist for this company
                $underAssessmentExists = DB::table('risk_statuses')
                    ->where('name', 'Under Assessment')
                    ->where('workflow_step', 2)
                    ->where(function($query) use ($companyId) {
                        $query->where('company_id', $companyId)
                              ->orWhere('company_id', 0);
                    })
                    ->exists();
                
                $assessmentInProgressExists = DB::table('risk_statuses')
                    ->where('name', 'Assessment In Progress')
                    ->where('workflow_step', 2)
                    ->where(function($query) use ($companyId) {
                        $query->where('company_id', $companyId)
                              ->orWhere('company_id', 0);
                    })
                    ->exists();

                if (!$underAssessmentExists) {
                    DB::table('risk_statuses')->insert([
                        'name' => 'Under Assessment',
                        'code' => 'ASSESS_' . ($companyId > 0 ? $companyId : 'GLOBAL') . '_' . uniqid(),
                        'description' => 'Risk is being assessed',
                        'color_code' => '#ffc107',
                        'order_index' => 3,
                        'workflow_step' => 2,
                        'is_active' => true,
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (!$assessmentInProgressExists) {
                    DB::table('risk_statuses')->insert([
                        'name' => 'Assessment In Progress',
                        'code' => 'ASSESSING_' . ($companyId > 0 ? $companyId : 'GLOBAL') . '_' . uniqid(),
                        'description' => 'Assessment in progress',
                        'color_code' => '#ffc107',
                        'order_index' => 4,
                        'workflow_step' => 2,
                        'is_active' => true,
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Also ensure global statuses (company_id = 0) exist
        $globalStep2Statuses = DB::table('risk_statuses')
            ->where('workflow_step', 2)
            ->where('company_id', 0)
            ->where('is_active', true)
            ->count();

        if ($globalStep2Statuses == 0) {
            $underAssessmentExists = DB::table('risk_statuses')
                ->where('name', 'Under Assessment')
                ->where('workflow_step', 2)
                ->where('company_id', 0)
                ->exists();
            
            $assessmentInProgressExists = DB::table('risk_statuses')
                ->where('name', 'Assessment In Progress')
                ->where('workflow_step', 2)
                ->where('company_id', 0)
                ->exists();

            if (!$underAssessmentExists) {
                DB::table('risk_statuses')->insert([
                    'name' => 'Under Assessment',
                    'code' => 'ASSESS_GLOBAL',
                    'description' => 'Risk is being assessed',
                    'color_code' => '#ffc107',
                    'order_index' => 3,
                    'workflow_step' => 2,
                    'is_active' => true,
                    'company_id' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!$assessmentInProgressExists) {
                DB::table('risk_statuses')->insert([
                    'name' => 'Assessment In Progress',
                    'code' => 'ASSESSING_GLOBAL',
                    'description' => 'Assessment in progress',
                    'color_code' => '#ffc107',
                    'order_index' => 4,
                    'workflow_step' => 2,
                    'is_active' => true,
                    'company_id' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the seeded statuses
        // Note: This will only remove statuses created by this migration
        // (identified by the code pattern)
        DB::table('risk_statuses')
            ->where('workflow_step', 2)
            ->where(function($query) {
                $query->where('code', 'like', 'ASSESS_%')
                      ->orWhere('code', 'like', 'ASSESSING_%');
            })
            ->delete();
    }
};

