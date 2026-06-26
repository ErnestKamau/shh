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
            if (!Schema::hasColumn('complaintsresolutions', 'findings')) {
                $table->longText('findings')->nullable()->after('action');
                $table->longText('root_cause_analysis')->nullable()->after('findings');
                $table->longText('corrective_action')->nullable()->after('root_cause_analysis');
                $table->longText('preventive_action')->nullable()->after('corrective_action');
            }
            
            if (Schema::hasColumn('complaintsresolutions', 'resolved_by_user_id')) {
                $table->dropColumn('resolved_by_user_id');
            }
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->uuid('resolved_by_user_id')->nullable()->after('officer_responsible');
            $table->foreign('resolved_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->dropForeign(['resolved_by_user_id']);
            $table->dropColumn(['findings', 'root_cause_analysis', 'corrective_action', 'preventive_action', 'resolved_by_user_id']);
        });
    }
};
