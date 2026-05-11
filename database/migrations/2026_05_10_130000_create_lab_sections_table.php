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
        Schema::create('lab_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('does_environmental_analysis')->default(false);
            $table->uuid('equipment_id')->nullable();
            $table->string('expected_value_type')->nullable(); // constant or range
            $table->decimal('expected_value', 14, 4)->nullable();
            $table->decimal('expected_min', 14, 4)->nullable();
            $table->decimal('expected_max', 14, 4)->nullable();
            $table->string('optimum_level')->nullable();
            $table->text('result_nature')->nullable();
            $table->string('reporting_unit', 120)->nullable();
            $table->boolean('active')->default(true);
            $table->uuid('company_id')->nullable();
            $table->timestamps();

            $table->unique(['lab_id', 'code'], 'lab_sections_lab_id_code_unique');
            $table->index('lab_id');
            $table->index('equipment_id');
            $table->index('company_id');

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->foreign('equipment_id')->references('id')->on('equipment')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_sections');
    }
};
