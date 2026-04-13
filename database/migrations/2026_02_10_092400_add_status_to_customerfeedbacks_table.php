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
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            // Check if status column exists, if not add it
            if (!Schema::hasColumn('customerfeedbacks', 'status')) {
                $table->tinyInteger('status')->default(0)->after('preferred_contact_method')->comment('0=Pending, 1=Reviewed, 2=Archived');
            }
            
            // Explicit submitted flag if needed (though presence of record implies it)
            if (!Schema::hasColumn('customerfeedbacks', 'is_submitted')) {
                $table->boolean('is_submitted')->default(true)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_submitted']);
        });
    }
};
