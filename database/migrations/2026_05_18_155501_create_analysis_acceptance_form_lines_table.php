<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_acceptance_form_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_acceptance_form_id')->index();
            $table->unsignedSmallInteger('line_no')->default(1);
            $table->uuid('sample_type_id')->nullable();
            $table->uuid('analysis_type_id')->nullable();
            $table->uuid('analysis_element_id')->nullable();
            $table->string('parameter_label');
            $table->decimal('unit_amount', 14, 2)->default(0);
            $table->unsignedInteger('number_of_samples')->default(1);
            $table->boolean('is_approved')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('analysis_acceptance_form_id')
                ->references('id')
                ->on('analysis_acceptance_forms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_acceptance_form_lines');
    }
};
