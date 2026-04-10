<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sync workflow_step for existing risks based on their status_id
     */
    public function up(): void
    {
        // Update risks to have workflow_step matching their status's workflow_step
        DB::statement("
            UPDATE risks r
            INNER JOIN risk_statuses rs ON r.status_id = rs.id
            SET r.workflow_step = rs.workflow_step
            WHERE rs.workflow_step IS NOT NULL
            AND r.deleted_at IS NULL
        ");

        // For risks without a status_id or with invalid status_id, set workflow_step based on status_name
        // This handles edge cases where status_id might be null or pointing to deleted statuses
        $statusMappings = [
            'Identified' => 2,
            'Under Assessment' => 3,
            'Under Evaluation' => 4,
            'Treatment Planning' => 5,
            'Treatment Implementation' => 6,
            'Treatment In Progress' => 6, // Alternative name
            'Risk Monitoring' => 7,
            'Under Monitoring' => 7, // Alternative name
            'Pending Closure' => 7,
            'Closed' => 8,
        ];

        foreach ($statusMappings as $statusName => $workflowStep) {
            DB::table('risks')
                ->whereNull('workflow_step')
                ->orWhere('workflow_step', 0)
                ->where('status_name', $statusName)
                ->whereNull('deleted_at')
                ->update(['workflow_step' => $workflowStep]);
        }

        // For any remaining risks without a workflow_step, default to 2 (Identified)
        DB::table('risks')
            ->where(function($q) {
                $q->whereNull('workflow_step')
                  ->orWhere('workflow_step', 0);
            })
            ->whereNull('deleted_at')
            ->update(['workflow_step' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - workflow_step can remain synced
    }
};

