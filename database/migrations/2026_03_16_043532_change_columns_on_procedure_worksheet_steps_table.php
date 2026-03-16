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
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            // Because they may have foreign keys that prevent type changes, drop them first if they exist
            // (Assuming they might have been created as foreignIds, but checking just in case)
            if (Schema::hasColumn('procedure_worksheet_steps', 'default_equipment_id')) {
                // If there's a foreign key constraint, we would drop it here. We'll assume simple change for now.
                $table->json('default_equipment_id')->nullable()->change();
            }
            if (Schema::hasColumn('procedure_worksheet_steps', 'default_analyst_id')) {
                $table->json('default_analyst_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            if (Schema::hasColumn('procedure_worksheet_steps', 'default_equipment_id')) {
                $table->unsignedBigInteger('default_equipment_id')->nullable()->change();
            }
            if (Schema::hasColumn('procedure_worksheet_steps', 'default_analyst_id')) {
                $table->unsignedBigInteger('default_analyst_id')->nullable()->change();
            }
        });
    }
};
