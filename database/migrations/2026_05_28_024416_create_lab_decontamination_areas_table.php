<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_decontamination_areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->uuid('lab_section_id');
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->uuid('company_id')->nullable();
            $table->timestamps();

            $table->unique(['lab_id', 'name'], 'lab_decontamination_areas_lab_id_name_unique');
            $table->index('lab_section_id');

            $table->foreign('lab_id')
                ->references('id')
                ->on('labs')
                ->cascadeOnDelete();

            $table->foreign('lab_section_id')
                ->references('id')
                ->on('lab_sections')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_decontamination_areas');
    }
};
