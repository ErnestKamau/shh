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
        Schema::create('analysis_methods', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->index('name');
            $table->string('code');
            $table->uuid('company_id')->index('idx_analysis_methods_company_id_9ad46e74');
            $table->string('description')->nullable();
            $table->boolean('active');
            $table->timestamps();
            $table->boolean('is_sampling_method')->nullable()->default(false)->index('is_sampling_method');
            $table->boolean('is_ltm')->nullable()->default(false);
            $table->integer('reference_type_id')->nullable();
            $table->integer('method_type_id')->nullable();
            $table->string('validation_status')->default('pending');
            $table->uuid('sample_header_id')->nullable()->index('idx_analysis_methods_sample_header_id_466b010d');
            $table->foreign(['company_id'], 'fk_analysis_methods_company_id_800d5758')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_analysis_methods_sample_header_id_1ab3181c')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_methods');
    }
};
