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
        Schema::create('analysis_types', function (Blueprint $table) {
            $table->uuid('id')->index('id');
            $table->string('code')->index('code');
            $table->string('name');
            $table->string('description')->nullable();
            $table->uuid('sample_type_id')->index('sample_type_id');
            $table->uuid('lab_id')->index('lab_id');
            $table->uuid('company_id')->index('idx_analysis_types_company_id_5a20196d');
            $table->boolean('active')->default(true);
            $table->boolean('has_no_result')->default(false);
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_analysis_types_procedure_worksheet_id_755f29ac');
            $table->boolean('include_hygiene_score')->default(false);
            $table->boolean('include_sanitizer_efficiency')->default(false);
            $table->timestamps();
            $table->string('short_name', 100)->nullable()->default('n/a');
            $table->integer('reporting_time')->nullable()->default(0);
            $table->integer('level')->default(1);
            $table->integer('lab_section_id')->nullable()->default(0)->index('lab_section_id');
            $table->integer('brand_id')->nullable()->default(0);
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->string('zoho_id', 100)->nullable();
            $table->string('product_type', 100)->nullable();
            $table->boolean('result_expo')->nullable()->default(true);
            $table->foreign(['company_id'], 'fk_analysis_types_company_id_1ce9c588')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lab_id'], 'fk_analysis_types_lab_id_fd913615')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_analysis_types_procedure_worksheet_id_0710fe93')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_type_id'], 'fk_analysis_types_sample_type_id_b9228794')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');









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
