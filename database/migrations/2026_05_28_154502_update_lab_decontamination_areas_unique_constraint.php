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
        Schema::table('lab_decontamination_areas', function (Blueprint $table): void {
            $table->dropUnique('lab_decontamination_areas_lab_id_name_unique');
            $table->unique(
                ['lab_id', 'lab_section_id', 'name'],
                'lab_decontamination_areas_lab_section_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_decontamination_areas', function (Blueprint $table): void {
            $table->dropUnique('lab_decontamination_areas_lab_section_name_unique');
            $table->unique(
                ['lab_id', 'name'],
                'lab_decontamination_areas_lab_id_name_unique'
            );
        });
    }
};
