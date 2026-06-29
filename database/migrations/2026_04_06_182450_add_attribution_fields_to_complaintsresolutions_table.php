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
            $table->text('root_cause_by')->nullable()->after('root_cause_analysis');
            $table->date('root_cause_date')->nullable()->after('root_cause_by');
            
            // Change existing ID-based fields to text to support multiple names (Select2)
            $table->text('action_taken_by')->nullable()->change();
            $table->text('corrective_action_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->dropColumn(['root_cause_by', 'root_cause_date']);
            // Reverting types is tricky and usually unnecessary for this small task
        });
    }
};
