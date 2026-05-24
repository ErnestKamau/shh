<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('skills_induction_checklist_items')) {
            return;
        }

        Schema::table('skills_induction_checklist_items', function (Blueprint $table) {
            $table->dropColumn('inventory_location_id');
        });

        Schema::table('skills_induction_checklist_items', function (Blueprint $table) {
            $table->uuid('inventory_location_id')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('skills_induction_checklist_items')) {
            return;
        }

        Schema::table('skills_induction_checklist_items', function (Blueprint $table) {
            $table->dropColumn('inventory_location_id');
        });

        Schema::table('skills_induction_checklist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_location_id')->nullable();
        });
    }
};
