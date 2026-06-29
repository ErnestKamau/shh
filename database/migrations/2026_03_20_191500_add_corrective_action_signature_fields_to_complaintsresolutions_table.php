<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            // Renaming columns safely
            if (Schema::hasColumn('complaintsresolutions', 'action') && !Schema::hasColumn('complaintsresolutions', 'action_taken')) {
                $table->renameColumn('action', 'action_taken');
            }
            if (Schema::hasColumn('complaintsresolutions', 'corrective_action') && !Schema::hasColumn('complaintsresolutions', 'corrective_action_taken')) {
                $table->renameColumn('corrective_action', 'corrective_action_taken');
            }
            if (Schema::hasColumn('complaintsresolutions', 'car_issued') && !Schema::hasColumn('complaintsresolutions', 'car_required')) {
                $table->renameColumn('car_issued', 'car_required');
            }

            // Adding new signature fields
            if (!Schema::hasColumn('complaintsresolutions', 'action_taken_by')) {
                $table->unsignedBigInteger('action_taken_by')->nullable()->after('action_taken_date');
            }
            if (!Schema::hasColumn('complaintsresolutions', 'corrective_action_by')) {
                $table->unsignedBigInteger('corrective_action_by')->nullable()->after('corrective_action_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            if (Schema::hasColumn('complaintsresolutions', 'action_taken') && !Schema::hasColumn('complaintsresolutions', 'action')) {
                $table->renameColumn('action_taken', 'action');
            }
            if (Schema::hasColumn('complaintsresolutions', 'corrective_action_taken') && !Schema::hasColumn('complaintsresolutions', 'corrective_action')) {
                $table->renameColumn('corrective_action_taken', 'corrective_action');
            }
            if (Schema::hasColumn('complaintsresolutions', 'car_required') && !Schema::hasColumn('complaintsresolutions', 'car_issued')) {
                $table->renameColumn('car_required', 'car_issued');
            }

            if (Schema::hasColumn('complaintsresolutions', 'action_taken_by')) {
                $table->dropColumn('action_taken_by');
            }
            if (Schema::hasColumn('complaintsresolutions', 'corrective_action_by')) {
                $table->dropColumn('corrective_action_by');
            }
        });
    }
};
