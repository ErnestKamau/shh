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
        if (Schema::hasTable('analysis_types')) {
            return;
        }
        Schema::create('analysis_types', function (Blueprint $table) {
            $table->uuid('id')->index('idx_analysis_types_id_33cac020');
            $table->string('code')->index('idx_analysis_types_code_83b2d6d3');
            $table->string('name');
            $table->string('description')->nullable();
            $table->uuid('sample_type_id')->index('idx_analysis_types_sample_type_id_0411aa4f');
            $table->uuid('lab_id')->index('idx_analysis_types_lab_id_ff41a227');
            $table->uuid('company_id')->index('idx_analysis_types_company_id_c3253014');
            $table->boolean('active')->default(true);
            $table->boolean('has_no_result')->default(false);
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_analysis_types_procedure_worksheet_id_159bea77');
            $table->boolean('include_hygiene_score')->default(false);
            $table->boolean('include_sanitizer_efficiency')->default(false);
            $table->timestamps();
            $table->string('short_name', 100)->nullable()->default('n/a');
            $table->integer('reporting_time')->nullable()->default(0);
            $table->integer('level')->default(1);
            $table->integer('lab_section_id')->nullable()->default(0)->index('idx_analysis_types_lab_section_id_8512b0d2');
            $table->integer('brand_id')->nullable()->default(0);
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->string('zoho_id', 100)->nullable();
            $table->string('product_type', 100)->nullable();
            $table->boolean('result_expo')->nullable()->default(true);

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_types');
    }
};
