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
            // Renaming columns
            $table->renameColumn('action', 'action_taken');
            $table->renameColumn('corrective_action', 'corrective_action_taken');
            $table->renameColumn('car_issued', 'car_required');

            // Adding new signature fields
            $table->unsignedBigInteger('action_taken_by')->nullable()->after('action_taken_date');
            $table->unsignedBigInteger('corrective_action_by')->nullable()->after('corrective_action_date');

            // Explicitly set foreign keys if possible, but for now simple nullable columns are fine for "signatures"
            // $table->foreign('action_taken_by')->references('id')->on('users');
            // $table->foreign('corrective_action_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->renameColumn('action_taken', 'action');
            $table->renameColumn('corrective_action_taken', 'corrective_action');
            $table->renameColumn('car_required', 'car_issued');

            $table->dropColumn(['action_taken_by', 'corrective_action_by']);
        });
    }
};
