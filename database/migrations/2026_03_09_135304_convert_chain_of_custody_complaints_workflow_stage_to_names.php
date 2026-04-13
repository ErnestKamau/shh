<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert numeric workflow_stage values (e.g. "1", "2") to stage names
     * (e.g. "Open Complaints") in chain_of_custody_complaints.
     */
    public function up(): void
    {
        $stageNames = getComplaintWorkflow();

        foreach ($stageNames as $id => $name) {
            DB::table('chain_of_custody_complaints')
                ->where('workflow_stage', (string) $id)
                ->update(['workflow_stage' => $name]);
        }
    }

    /**
     * Reverse: convert stage names back to numeric strings (best-effort).
     */
    public function down(): void
    {
        $stageNames = getComplaintWorkflow();

        foreach ($stageNames as $id => $name) {
            DB::table('chain_of_custody_complaints')
                ->where('workflow_stage', $name)
                ->update(['workflow_stage' => (string) $id]);
        }
    }
};
